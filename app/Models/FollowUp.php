<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'child_id', 'risk_prediction_id', 'risk_level_at_scheduling',
        'scheduled_date', 'status', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}