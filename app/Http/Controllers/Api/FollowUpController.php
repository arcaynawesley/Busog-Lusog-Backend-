<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function __construct(private TimelineService $timeline) {}

    public function index(Request $request)
    {
        $query = FollowUp::query();

        if ($request->filled('child_id')) {
            $query->where('child_id', $request->child_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('upcoming_only')) {
            $query->where('status', 'scheduled')->whereDate('scheduled_date', '>=', now()->toDateString());
        }

        return $query->orderBy('scheduled_date')->get();
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $data = $request->validate([
            'status' => 'required|in:scheduled,completed,missed,rescheduled',
            'notes' => 'nullable|string',
        ]);

        if ($data['status'] === 'completed') {
            $data['completed_at'] = now();
        }

        $followUp->update($data);

        $this->timeline->log(
            $followUp->child_id, 'follow_up', $followUp->id,
            "Follow-up on {$followUp->scheduled_date->toDateString()} marked {$data['status']}."
        );

        return $followUp;
    }
}