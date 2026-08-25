<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\Intervention;
use App\Models\NutritionRecommendation;
use App\Models\RiskPrediction;
use App\Services\RiskPredictionService;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class RiskPredictionController extends Controller
{
    private const MEAL_TIPS = [
        'very_high' => 'Prioritize energy- and protein-dense local foods (malunggay, monggo, egg, '
            . 'fish) at every meal; consider referral for therapeutic/supplementary feeding.',
        'high' => 'Increase meal frequency to 5–6x/day with a protein source at each meal; '
            . 'include iron-rich foods (liver, dark leafy vegetables).',
        'moderate' => 'Maintain 3 balanced meals + 2 snacks; add a fruit or vegetable serving daily.',
        'low' => 'Continue current diet; reinforce dietary diversity and continued breastfeeding '
            . 'if under 24 months.',
    ];

    private const BUDGET_TIPS = [
        'very_high' => 'Explore feeding-program enrollment and barangay nutrition subsidies to '
            . 'reduce cost barriers to protein-rich food.',
        'high' => 'Suggest low-cost protein swaps (monggo, eggs, dried fish) and backyard '
            . 'vegetable gardening where feasible.',
    ];

    public function __construct(
        private RiskPredictionService $riskModel,
        private TimelineService $timeline,
    ) {}

    public function index(Request $request)
    {
        $request->validate(['child_id' => 'required|uuid']);

        return RiskPrediction::where('child_id', $request->child_id)
            ->orderByDesc('predicted_at')
            ->get();
    }

    public function run(Request $request, ChildRecord $child)
    {
        $family = $child->family;
        abort_if(!$family, 400, 'Child has no linked family profile — required to run prediction');

        $latestRecord = $child->healthRecords()->first();

        $result = $this->riskModel->score([
            'birth_date' => $child->birth_date,
            'birth_size' => $child->birth_size,
            'breastfed' => $child->breastfed,
            'residence_type' => $family->residence_type,
            'household_size' => $family->household_size,
            'num_children_under5' => $family->num_children_under5,
            'maternal_literacy' => $family->maternal_literacy,
            'anc_attendance' => $family->anc_attendance,
            'water_source' => $family->water_source,
            'latrine_facility' => $family->latrine_facility,
            'diarrhea_past_2weeks' => $latestRecord->diarrhea_past_2weeks ?? false,
            'febrile_illness_past_2weeks' => $latestRecord->febrile_illness_past_2weeks ?? false,
            'vitamin_a_received' => $latestRecord->vitamin_a_received ?? false,
        ]);

        $prediction = RiskPrediction::create([
            'child_id' => $child->id,
            'health_record_id' => $latestRecord?->id,
            'risk_level' => $result['risk_level'],
            'risk_score' => $result['risk_score'],
            'model_version' => $result['model_version'],
            'top_factors' => $result['top_factors'],
        ]);

        $this->timeline->log(
            $child->id, 'risk_prediction', $prediction->id,
            "Risk assessed as " . strtoupper($result['risk_level']) . " (score {$result['risk_score']})."
        );

        $this->scheduleFollowUp($child->id, $prediction);
        $this->draftNutritionRecommendations($child->id, $prediction);
        $this->draftSmartInterventions($child->id, $prediction);

        return response()->json($prediction, 201);
    }

    private function scheduleFollowUp(string $childId, RiskPrediction $prediction): void
    {
        $days = $this->riskModel->followUpIntervalDays($prediction->risk_level);
        $scheduled = now()->addDays($days)->toDateString();

        $followUp = \App\Models\FollowUp::create([
            'child_id' => $childId,
            'risk_prediction_id' => $prediction->id,
            'risk_level_at_scheduling' => $prediction->risk_level,
            'scheduled_date' => $scheduled,
            'status' => 'scheduled',
        ]);

        $this->timeline->log(
            $childId, 'follow_up', $followUp->id,
            "Follow-up auto-scheduled for {$scheduled} ({$prediction->risk_level} risk → {$days} days)."
        );
    }

    private function draftNutritionRecommendations(string $childId, RiskPrediction $prediction): void
    {
        $level = $prediction->risk_level;
        $recs = [['category' => 'meal_plan', 'recommendation_text' => self::MEAL_TIPS[$level]]];

        if (isset(self::BUDGET_TIPS[$level])) {
            $recs[] = ['category' => 'budgeting', 'recommendation_text' => self::BUDGET_TIPS[$level]];
        }

        foreach ($recs as $rec) {
            $saved = NutritionRecommendation::create([
                'child_id' => $childId,
                'risk_prediction_id' => $prediction->id,
                'category' => $rec['category'],
                'recommendation_text' => $rec['recommendation_text'],
            ]);
            $this->timeline->log(
                $childId, 'recommendation', $saved->id,
                "Nutrition recommendation ({$rec['category']}) generated."
            );
        }
    }

    /** Feature 8: Smart Intervention Recommendation. */
    private function draftSmartInterventions(string $childId, RiskPrediction $prediction): void
    {
        $level = $prediction->risk_level;
        $factorNames = array_column($prediction->top_factors, 'feature');
        $suggested = [];

        if (in_array($level, ['high', 'very_high'], true)) {
            $suggested[] = 'home_visit';
        }
        if ($level === 'very_high' || in_array('anc_attendance', $factorNames, true)) {
            $suggested[] = 'feeding_program';
        }
        if (in_array('recent_diarrheal_illness', $factorNames, true) || in_array('water_source', $factorNames, true)) {
            $suggested[] = 'water_sanitation_education';
        }
        if (in_array('not_breastfed', $factorNames, true) || in_array($level, ['high', 'very_high'], true)) {
            $suggested[] = 'nutrition_counseling';
        }
        if (!in_array('vitamin_a_received', $factorNames, true) && in_array($level, ['high', 'very_high'], true)) {
            $suggested[] = 'vitamin_a';
        }

        foreach (array_unique($suggested) as $type) {
            $iv = Intervention::create([
                'child_id' => $childId,
                'risk_prediction_id' => $prediction->id,
                'intervention_type' => $type,
                'status' => 'planned',
                'is_recommended' => true,
            ]);
            $this->timeline->log(
                $childId, 'intervention', $iv->id,
                'System-suggested intervention: ' . str_replace('_', ' ', $type) . '.'
            );
        }
    }
}