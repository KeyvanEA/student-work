<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    /**
     * جدول otp_codes ستون updated_at ندارد، بنابراین فقط created_at مدیریت می‌شود.
     * created_at اینجا یعنی «آخرین باری که کد ارسال شده» و صریحاً مقداردهی می‌شود.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'mobile',
        'code',
        'created_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }
}
