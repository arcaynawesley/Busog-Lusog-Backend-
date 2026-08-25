<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildTimelineEvent;

class TimelineController extends Controller
{
    public function show(string $childId)
    {
        return ChildTimelineEvent::where('child_id', $childId)
            ->orderByDesc('event_date')
            ->get();
    }
}