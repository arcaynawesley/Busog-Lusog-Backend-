<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = ['name', 'city', 'latitude', 'longitude'];

    public function families()
    {
        return $this->hasMany(Family::class);
    }
}