<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminUser extends Authenticatable
{
    protected $table = 'admin_users'; 

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'avatar', 'last_login_at', 'last_login_ip', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}