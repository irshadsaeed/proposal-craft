<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'client',           // string column — no relationship with same name
        'client_email',
        'status',
        'amount',
        'currency',
        'views',            // fixed: was views_count
        'avg_time_open',
        'last_seen',
        'sent_at',
        'token',
        'share_token',
        'first_viewed_at',
        'accepted_by',
        'accepted_email',
        'accepted_at',
        'accepted_ip',
        'signature_path',
        'declined_at',
        'decline_reason',
        'notes',
    ];

    protected $casts = [
        'sent_at'         => 'datetime',
        'accepted_at'     => 'datetime',
        'declined_at'     => 'datetime',
        'first_viewed_at' => 'datetime',
        'last_seen'       => 'datetime',
        'amount'          => 'decimal:2',
        'views'           => 'integer',
    ];

    /* ── Boot: auto-generate tokens ── */
    protected static function booted(): void
    {
        static::creating(function (self $proposal) {
            if (empty($proposal->token)) {
                $proposal->token = Str::uuid();
            }
            if (empty($proposal->share_token)) {
                $proposal->share_token = Str::random(32);
            }
        });
    }

    /* ── Relationships ── */
    public function sender()
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function sections()
    {
        return $this->hasMany(ProposalSection::class)->orderBy('order');
    }

    public function comments()
    {
        return $this->hasMany(ProposalComment::class)->latest();
    }

    public function proposalViews()
    {
        return $this->hasMany(ProposalView::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(ProposalTrackingEvent::class);
    }

    /* ── Helpers ── */
    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function markAsSent(): void
    {
        $this->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function publicUrl(): string
    {
        return url('/p/' . $this->share_token);
    }
}