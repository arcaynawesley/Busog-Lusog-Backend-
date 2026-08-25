<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ChildTimelineEvent extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $fillable = ['child_id', 'event_type', 'related_id', 'summary'];

    protected function casts(): array
    {
        return ['event_date' => 'datetime'];
    }
}