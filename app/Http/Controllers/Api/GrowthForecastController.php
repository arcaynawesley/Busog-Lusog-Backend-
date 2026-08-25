<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\GrowthForecast;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class GrowthForecastController extends Controller
{
    public function __construct(private TimelineService $timeline) {}

    public function index(Request $request)
    {
        $request->validate(['child_id' => 'required|uuid']);
        return GrowthForecast::where('child_id', $request->child_id)->orderByDesc('created_at')->get();
    }

    public function run(Request $request, ChildRecord $child)
    {
        $horizonDays = (int) $request->input('horizon_days', 90);

        $records = $child->healthRecords()->orderByDesc('record_date')->limit(2)->get();
        abort_if($records->count() < 2, 400, 'Need at least 2 health records to forecast growth trend');

        [$latest, $prior] = $records;
        $daysBetween = max($prior->record_date->diffInDays($latest->record_date), 1);

        $weightRate = ($latest->weight_kg - $prior->weight_kg) / $daysBetween;
        $heightRate = ($latest->height_cm - $prior->height_cm) / $daysBetween;

        $forecastDate = $latest->record_date->copy()->addDays($horizonDays);
        $predictedWeight = round($latest->weight_kg + $weightRate * $horizonDays, 2);
        $predictedHeight = round($latest->height_cm + $heightRate * $horizonDays, 2);
        $status = ($weightRate >= 0 && $heightRate >= 0) ? 'on_track' : 'at_risk_of_faltering';

        $forecast = GrowthForecast::create([
            'child_id' => $child->id,
            'forecast_horizon_days' => $horizonDays,
            'forecast_date' => $forecastDate,
            'predicted_weight_kg' => $predictedWeight,
            'predicted_height_cm' => $predictedHeight,
            'predicted_status' => $status,
            'confidence' => 0.4,
            'model_version' => 'linear-trend-stub-v0',
        ]);

        $this->timeline->log(
            $child->id, 'growth_forecast', $forecast->id,
            "Growth forecast for {$forecastDate->toDateString()}: ~{$predictedWeight}kg, ~{$predictedHeight}cm ({$status})."
        );

        return response()->json($forecast, 201);
    }
}