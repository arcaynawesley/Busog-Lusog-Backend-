<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\Family;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    public function __construct(private TimelineService $timeline) {}

    private function generateChildCode(): string
    {
        $year = now()->year;
        for ($i = 0; $i < 5; $i++) {
            $candidate = sprintf('TC-%d-%06d', $year, random_int(100000, 999999));
            if (!ChildRecord::where('child_code', $candidate)->exists()) {
                return $candidate;
            }
        }
        abort(500, 'Could not generate a unique Child ID, try again');
    }

    public function index(Request $request)
    {
        $query = ChildRecord::query();

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }
        if ($request->filled('barangay_id')) {
            $familyIds = Family::where('barangay_id', $request->barangay_id)->pluck('id');
            $query->whereIn('family_id', $familyIds);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'family_id' => 'required|uuid|exists:families,id',
            'sex' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'birth_size' => 'required|in:average,large,small,very_small,dont_know',
            'breastfed' => 'required|boolean',
        ]);

        $data['child_code'] = $this->generateChildCode();
        $data['created_by'] = $request->user()->id;

        $child = ChildRecord::create($data);

        $this->timeline->log(
            $child->id, 'note', null,
            "Child {$child->child_code} registered in the system."
        );

        return response()->json($child, 201);
    }

    public function show(ChildRecord $child)
    {
        return $child;
    }

    public function deactivate(ChildRecord $child)
    {
        $child->update(['is_active' => false]);
        return $child;
    }
}