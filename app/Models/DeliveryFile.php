<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFile extends Model
{
    protected $fillable = [
        'delivery_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];
    public function delivery():BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
