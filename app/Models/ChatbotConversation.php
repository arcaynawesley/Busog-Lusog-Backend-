<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'child_id', 'messages'];

    protected function casts(): array
    {
        return ['messages' => 'array'];
    }
}