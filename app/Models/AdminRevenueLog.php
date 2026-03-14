<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRevenueLog extends Model
{
    protected $fillable = [
        'user_id', 'stripe_payment_intent', 'stripe_subscription_id',
        'plan_slug', 'billing_period', 'amount', 'currency', 'status', 'paid_at',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    /** Amount in dollars */
    public function getAmountDollarsAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }

    public function scopeSucceeded($q) { return $q->where('status', 'succeeded'); }
    public function scopeThisMonth($q) { return $q->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year); }
    public function scopeThisYear($q)  { return $q->whereYear('paid_at', now()->year); }
}