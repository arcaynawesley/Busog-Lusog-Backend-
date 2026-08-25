<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GrowthForecast extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'child_id', 'forecast_horizon_days', 'forecast_date', 'predicted_weight_kg',
        'predicted_height_cm', 'predicted_status', 'confidence', 'model_version',
    ];

    protected function casts(): array
    {
        return ['forecast_date' => 'date'];
    }
}