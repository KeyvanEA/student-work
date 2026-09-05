<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintFile extends Model
{
    protected $fillable = [
        'delivery_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
