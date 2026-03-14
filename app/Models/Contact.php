<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name', 'email', 'subject', 'message',
        'status', 'ip', 'read_at', 'replied_at', 'admin_note',
    ];

    protected $casts = [
        'read_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function scopeUnread($q) { return $q->where('status', 'unread'); }
    public function scopeRead($q)   { return $q->where('status', 'read'); }

    public function markAsRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);
    }

    public function isUnread(): bool
    {
        return $this->status === 'unread';
    }
}