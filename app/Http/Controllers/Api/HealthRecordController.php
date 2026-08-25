<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\HealthRecord;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class HealthRecordController extends Controller
{
    public function __construct(private TimelineService $timeline) {}

    private function muacStatus(?float $muac): ?string
    {
        if ($muac === null) return null;
        if ($muac < 11.5) return 'severely_wasted';
        if ($muac < 12.5) return 'wasted';
        return 'normal';
    }

    public function index(Request $request)
    {
        $request->validate(['child_id' => 'required|uuid']);

        return HealthRecord::where('child_id', $request->child_id)
            ->orderByDesc('record_date')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'child_id' => 'required|uuid|exists:children,id',
            'record_date' => 'required|date',
            'weight_kg' => 'required|numeric',
            'height_cm' => 'required|numeric',
            'muac_cm' => 'nullable|numeric',
            'diarrhea_past_2weeks' => 'nullable|boolean',
            'febrile_illness_past_2weeks' => 'nullable|boolean',
            'vitamin_a_received' => 'nullable|boolean',
        ]);

        $child = ChildRecord::findOrFail($data['child_id']);

        $data['age_months'] = $child->birth_date->diffInMonths($data['record_date']);
        $data['nutritional_status'] = $this->muacStatus($data['muac_cm'] ?? null);
        $data['recorded_by'] = $request->user()->id;

        $record = HealthRecord::create($data);

        $statusNote = $record->nutritional_status ? ", status: {$record->nutritional_status}" : '';
        $this->timeline->log(
            $data['child_id'], 'health_record', $record->id,
            "Visit recorded on {$data['record_date']}: {$data['weight_kg']}kg, {$data['height_cm']}cm{$statusNote}"
        );

        return response()->json($record, 201);
    }
}