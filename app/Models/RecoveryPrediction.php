<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RecoveryPrediction extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = ['child_id', 'likelihood_percent', 'based_on_intervention_id', 'model_version'];

    protected function casts(): array
    {
        return ['predicted_at' => 'datetime'];
    }
}