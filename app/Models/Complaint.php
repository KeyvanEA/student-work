<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $fillable = ['title', 'description', 'project_id', 'user_id','files'];
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files():HasMany
    {
        return $this->hasMany(ComplaintFile::class);
    }
}
