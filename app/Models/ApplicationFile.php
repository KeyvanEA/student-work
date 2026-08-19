<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationFile extends Model
{
    protected $fillable = [
        'application_id',
        'file_path',
    ];
    public function application():belongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
