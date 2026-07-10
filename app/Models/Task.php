<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Task extends Model
{
    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function user():belongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files():hasMany
    {
        return $this->hasMany(TaskFile::class);
    }

    public function applications():HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function skills():belongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }
}
