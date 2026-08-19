<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    protected $fillable = ['description',
        'files',
        'project_id',
        'submitted_at',
        'rejection_reason',
        ];
    public function project():belongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function files():HasMany
    {
        return $this->hasMany(DeliveryFile::class);
    }

    public function complaints():HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
