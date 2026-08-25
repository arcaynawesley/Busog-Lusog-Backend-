<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\Intervention;
use App\Models\RecoveryPrediction;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class RecoveryPredictionController extends Controller
{
    public function __construct(private TimelineService $timeline) {}

    public function index(Request $request)
    {
        $request->validate(['child_id' => 'required|uuid']);
        return RecoveryPrediction::where('child_id', $request->child_id)->orderByDesc('predicted_at')->get();
    }

    public function run(ChildRecord $child)
    {
        $completedCount = Intervention::where('child_id', $child->id)->where('status', 'completed')->count();

        $latestRisk = $child->riskPredictions()->first();
        abort_if(!$latestRisk, 400, 'No risk prediction on record yet — run a risk prediction first');

        $base = ['very_high' => 20, 'high' => 35, 'moderate' => 55, 'low' => 80][$latestRisk->risk_level];
        $likelihood = min(97, $base + $completedCount * 8);

        $prediction = RecoveryPrediction::create([
            'child_id' => $child->id,
            'likelihood_percent' => $likelihood,
            'model_version' => 'heuristic-stub-v0',
        ]);

        $this->timeline->log(
            $child->id, 'recovery_prediction', $prediction->id,
            "Recovery likelihood estimated at {$likelihood}%."
        );

        return response()->json($prediction, 201);
    }
}