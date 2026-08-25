<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasUuids;

    protected $fillable = [
        'child_id', 'risk_prediction_id', 'intervention_type', 'status',
        'is_recommended', 'start_date', 'end_date', 'effectiveness_notes', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'is_recommended' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}