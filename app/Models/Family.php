<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasUuids;

    protected $fillable = [
        'barangay_id', 'residence_type', 'household_size', 'num_children_under5',
        'economic_status', 'water_source', 'latrine_facility',
        'participates_feeding_program', 'maternal_literacy', 'anc_attendance',
    ];

    protected function casts(): array
    {
        return ['participates_feeding_program' => 'boolean'];
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function children()
    {
        return $this->hasMany(ChildRecord::class, 'family_id');
    }
}