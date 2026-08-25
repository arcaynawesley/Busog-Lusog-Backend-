<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'child_id', 'record_date', 'age_months', 'weight_kg', 'height_cm', 'muac_cm',
        'weight_for_age_z', 'height_for_age_z', 'weight_for_height_z',
        'diarrhea_past_2weeks', 'febrile_illness_past_2weeks', 'vitamin_a_received',
        'nutritional_status', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'diarrhea_past_2weeks' => 'boolean',
            'febrile_illness_past_2weeks' => 'boolean',
            'vitamin_a_received' => 'boolean',
        ];
    }

    public function child()
    {
        return $this->belongsTo(ChildRecord::class, 'child_id');
    }
}