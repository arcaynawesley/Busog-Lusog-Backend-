<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Rule-based placeholder for BusogLusog's malnutrition risk model.
 *
 * The manuscript specifies a Scikit-learn model trained on City Health
 * Office records. That trained model and its feature pipeline don't
 * exist yet — training data hasn't been collected/cleaned. This class
 * implements a transparent, weighted-scoring stand-in over the same
 * features so every other feature (nutrition recs, interventions,
 * follow-up scheduling, timeline, heat map, recovery/growth forecasts)
 * can be built and demoed against a real (if simplistic) signal.
 *
 * Swap score() for a call to a trained model + SHAP explainer once
 * it's ready — the return shape (risk_score, risk_level, top_factors)
 * is what the rest of the system consumes, so nothing else changes.
 *
 * Weights below are directional guesses based on the SHAP importance
 * ranking in the project's own feature-analysis document, NOT fitted
 * coefficients. Treat all outputs as illustrative, not clinical.
 */
class RiskPredictionService
{
    public function score(array $input): array
    {
        $factors = [];
        $score = 0.0;

        $add = function (string $feature, $value, float $weight) use (&$factors, &$score) {
            $score += $weight;
            $factors[] = [
                'feature' => $feature,
                'value' => $value,
                'contribution' => round($weight, 4),
                'direction' => $weight > 0 ? 'increases_risk' : 'decreases_risk',
            ];
        };

        $residenceType = $input['residence_type'];
        $add('place_of_residence', $residenceType, $residenceType === 'rural' ? 0.10 : -0.03);

        $householdSize = (int) $input['household_size'];
        if ($householdSize >= 6) {
            $add('family_size', $householdSize, 0.08);
        } elseif ($householdSize <= 2) {
            $add('family_size', $householdSize, -0.02);
        }

        $under5 = (int) $input['num_children_under5'];
        if ($under5 >= 3) {
            $add('number_of_under5_children', $under5, 0.10);
        } elseif ($under5 >= 2) {
            $add('number_of_under5_children', $under5, 0.05);
        }

        $birthSize = $input['birth_size'];
        if (in_array($birthSize, ['small', 'very_small'], true)) {
            $add('birth_size', $birthSize, 0.15);
        } elseif ($birthSize === 'dont_know') {
            $add('birth_size', $birthSize, 0.05);
        }

        $ageMonths = Carbon::parse($input['birth_date'])->diffInMonths(Carbon::now());
        if ($ageMonths <= 6 || $ageMonths >= 48) {
            $add('child_age_months', $ageMonths, 0.04);
        }

        if ($input['maternal_literacy'] === 'cannot_read') {
            $add('maternal_literacy', $input['maternal_literacy'], 0.09);
        }

        $anc = $input['anc_attendance'];
        if ($anc === 'none') {
            $add('anc_attendance', $anc, 0.10);
        } elseif ($anc === 'inadequate') {
            $add('anc_attendance', $anc, 0.05);
        }

        if ($input['water_source'] === 'unimproved') {
            $add('water_source', $input['water_source'], 0.08);
        }

        if ($input['latrine_facility'] === 'not_available') {
            $add('latrine_facility', $input['latrine_facility'], 0.08);
        }

        if (!empty($input['diarrhea_past_2weeks'])) {
            $add('recent_diarrheal_illness', true, 0.12);
        }

        if (!empty($input['febrile_illness_past_2weeks'])) {
            $add('febrile_illness_past_2weeks', true, 0.07);
        }

        if (empty($input['breastfed'])) {
            $add('not_breastfed', true, 0.06);
        }

        if (!empty($input['vitamin_a_received'])) {
            $add('vitamin_a_received', true, -0.05);
        }

        $riskScore = max(0.0, min(1.0, $score));

        $riskLevel = match (true) {
            $riskScore >= 0.55 => 'very_high',
            $riskScore >= 0.35 => 'high',
            $riskScore >= 0.18 => 'moderate',
            default => 'low',
        };

        usort($factors, fn ($a, $b) => abs($b['contribution']) <=> abs($a['contribution']));

        return [
            'risk_score' => round($riskScore, 4),
            'risk_level' => $riskLevel,
            'top_factors' => array_slice($factors, 0, 10),
            'model_version' => 'rule-based-v0',
        ];
    }

    public function followUpIntervalDays(string $riskLevel): int
    {
        return match ($riskLevel) {
            'very_high' => 7,
            'high' => 14,
            'moderate' => 30,
            default => 90,
        };
    }
}