<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'client_users';

    protected $fillable = [
        'name',
        'email',
        'password',

        // Profile
        'job_title',
        'company',
        'website',
        'bio',
        'avatar',

        // Branding
        'brand_name',
        'brand_tagline',
        'brand_color',
        'footer_text',

        // Preferences
        'currency',
        'date_format',
        'language',
        'timezone',

        // Plan & status (admin managed)
        'plan_slug',
        'is_active',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'last_active_at'    => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'user_id');
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isPro(): bool
    {
        return $this->plan_slug === 'pro';
    }

    public function isAgency(): bool
    {
        return $this->plan_slug === 'agency';
    }

    public function isFree(): bool
    {
        return is_null($this->plan_slug) || $this->plan_slug === 'free';
    }

    
}
