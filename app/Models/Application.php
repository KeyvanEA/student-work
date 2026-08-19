<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'description',
        'status',
    ];
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function task():BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
    public function files():HasMany
    {
        return $this->hasMany(ApplicationFile::class);
    }
    public function conversations():HasOne
    {
        return $this->hasOne(Conversation::class);
    }
    public function project():HasOne
    {
        return $this->hasOne(Project::class);
    }
}
