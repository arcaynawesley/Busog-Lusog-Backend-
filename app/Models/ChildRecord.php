<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ChildRecord extends Model
{
    use HasUuids;

    protected $table = 'children';

    protected $fillable = ['family_id', 'sex', 'birth_date', 'birth_size', 'breastfed', 'created_by'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'breastfed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function family()
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'child_id')->orderByDesc('record_date');
    }

    public function riskPredictions()
    {
        return $this->hasMany(RiskPrediction::class, 'child_id')->orderByDesc('predicted_at');
    }
}