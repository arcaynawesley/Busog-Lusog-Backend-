<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    private function rules(): array
    {
        return [
            'barangay_id' => 'required|uuid|exists:barangays,id',
            'residence_type' => 'required|in:urban,rural',
            'household_size' => 'required|integer|min:1',
            'num_children_under5' => 'nullable|integer|min:0',
            'economic_status' => 'required|in:low,middle,high',
            'water_source' => 'required|in:improved,unimproved',
            'latrine_facility' => 'required|in:available,not_available',
            'participates_feeding_program' => 'nullable|boolean',
            'maternal_literacy' => 'required|in:can_read,cannot_read',
            'anc_attendance' => 'required|in:adequate,inadequate,none',
        ];
    }

    public function index(Request $request)
    {
        $query = Family::query();
        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }
        return $query->orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        return response()->json(Family::create($data), 201);
    }

    public function show(Family $family)
    {
        return $family;
    }

    public function update(Request $request, Family $family)
    {
        $data = $request->validate($this->rules());
        $family->update($data);
        return $family;
    }
}