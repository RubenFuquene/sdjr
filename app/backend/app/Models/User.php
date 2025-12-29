<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SanitizesTextAttributes, SoftDeletes;

    // Sanctum guard name
    protected string $guard_name = 'sanctum';

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'phone' => 'string',
        ];
    }

    /**
     * Sanitize last_name before saving.
     */
    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = $this->capitalizeText($value);
    }

    /**
     * Sanitize phone before saving.
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->sanitizePhone($value);
    }

    /**
     * Sanitize email before saving.
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $this->sanitizeEmail($value);
    }

    /**
     * Sanitize name before saving.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->capitalizeText($value);
    }
}
