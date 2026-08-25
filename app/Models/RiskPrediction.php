<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RiskPrediction extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'child_id', 'health_record_id', 'risk_level', 'risk_score', 'model_version', 'top_factors',
    ];

    protected function casts(): array
    {
        return [
            'top_factors' => 'array',
            'predicted_at' => 'datetime',
            'risk_score' => 'float',
        ];
    }

    public function child()
    {
        return $this->belongsTo(ChildRecord::class, 'child_id');
    }
}