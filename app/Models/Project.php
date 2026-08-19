<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['amount', 'deadline','started_at'];
    public function application():BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function deliveries():hasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function complaints():HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function reviews():HasMany
    {
        return $this->hasMany(Review::class);
    }
}
