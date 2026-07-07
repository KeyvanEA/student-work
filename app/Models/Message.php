<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public function conversation():belongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
    public function user():belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
