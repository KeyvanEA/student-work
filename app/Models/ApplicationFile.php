<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationFile extends Model
{
    public function application():belongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
