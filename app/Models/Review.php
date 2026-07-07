<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer():BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser():BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
}
