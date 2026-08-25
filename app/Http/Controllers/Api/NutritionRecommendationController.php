<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NutritionRecommendation;
use Illuminate\Http\Request;

class NutritionRecommendationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['child_id' => 'required|uuid']);
        return NutritionRecommendation::where('child_id', $request->child_id)
            ->orderByDesc('created_at')
            ->get();
    }
}