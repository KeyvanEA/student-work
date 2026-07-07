<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFile extends Model
{
    public function delivery():BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
