<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function __construct(private TimelineService $timeline) {}

    public function index(Request $request)
    {
        $request->validate(['child_id' => 'required|uuid']);
        return Intervention::where('child_id', $request->child_id)->orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'child_id' => 'required|uuid|exists:children,id',
            'intervention_type' => 'required|in:home_visit,feeding_program,vitamin_a,nutrition_counseling,water_sanitation_education,medical_referral,other',
            'status' => 'nullable|in:planned,ongoing,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'effectiveness_notes' => 'nullable|string',
        ]);

        $data['status'] = $data['status'] ?? 'planned';
        $data['is_recommended'] = false;
        $data['assigned_to'] = $request->user()->id;

        $iv = Intervention::create($data);

        $this->timeline->log(
            $data['child_id'], 'intervention', $iv->id,
            'Intervention logged: ' . str_replace('_', ' ', $data['intervention_type']) . " ({$data['status']})."
        );

        return response()->json($iv, 201);
    }

    public function updateStatus(Request $request, Intervention $intervention)
    {
        $data = $request->validate([
            'status' => 'required|in:planned,ongoing,completed,cancelled',
            'effectiveness_notes' => 'nullable|string',
        ]);

        $intervention->update($data);

        $note = isset($data['effectiveness_notes']) ? " Notes: {$data['effectiveness_notes']}" : '';
        $this->timeline->log(
            $intervention->child_id, 'intervention', $intervention->id,
            'Intervention ' . str_replace('_', ' ', $intervention->intervention_type)
                . " marked {$data['status']}.{$note}"
        );

        return $intervention;
    }

    public function acceptSuggestion(Request $request, Intervention $intervention)
    {
        $intervention->update(['status' => 'ongoing', 'assigned_to' => $request->user()->id]);
        return $intervention;
    }
}