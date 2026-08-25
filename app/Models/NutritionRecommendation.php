<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NutritionRecommendation extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = ['child_id', 'risk_prediction_id', 'category', 'recommendation_text'];
}