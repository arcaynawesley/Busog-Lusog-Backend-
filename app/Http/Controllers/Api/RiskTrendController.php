<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskPrediction;

class RiskTrendController extends Controller
{
    public function show(string $childId)
    {
        $points = RiskPrediction::where('child_id', $childId)
            ->orderBy('predicted_at')
            ->get(['predicted_at', 'risk_score', 'risk_level']);

        $direction = 'insufficient_data';
        if ($points->count() >= 2) {
            $delta = $points->last()->risk_score - $points->slice(-2, 1)->first()->risk_score;
            $direction = $delta > 0.03 ? 'worsening' : ($delta < -0.03 ? 'improving' : 'stable');
        }

        return response()->json([
            'child_id' => $childId,
            'points' => $points,
            'trend_direction' => $direction,
        ]);
    }
}