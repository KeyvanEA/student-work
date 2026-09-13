<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['full_name',
    'mobile',
    'email',
    'student_number',
    'field_of_study',
    'university_name',
    'bio',
    'avatar',
    'resume_file',
    'is_active',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable,HasApiTokens,HasRoles;



    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class,'user_skills');
    }
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function isProfileCompleted(): bool
    {
        foreach ([
                     'full_name',
                     'student_number',
                     'field_of_study',
                     'university_name',
                 ] as $field) {

            if (blank($this->{$field})) {
                return false;
            }
        }

        return true;

    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
