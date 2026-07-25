<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'user_skills');
    }

    public function tasks():belongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
}
