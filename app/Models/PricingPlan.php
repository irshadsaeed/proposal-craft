<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'description',
        'monthly_price',
        'yearly_price',
        'yearly_saving',   // already added ✔
        'cta_label',
        'badge',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'is_active'  => 'boolean',
    ];

    // ── RELATIONS ──────────────────────────────────────────────

    public function features(): HasMany
    {
        return $this->hasMany(PricingFeature::class)->orderBy('sort_order');
    }

    // ── ACCESSORS ──────────────────────────────────────────────

    /** Monthly price in dollars (e.g. 19) */
    public function getMonthlyPriceDollarsAttribute(): int
    {
        return (int) ($this->monthly_price / 100);
    }

    /** Yearly price per month in dollars (e.g. 13) */
    public function getYearlyPriceDollarsAttribute(): int
    {
        return (int) ($this->yearly_price / 100);
    }

    /** Yearly saving in dollars (e.g. 72) */
    public function getYearlySavingDollarsAttribute(): int
    {
        return (int) ($this->yearly_saving / 100);
    }

    /** Annual total billed (yearly_price * 12) in dollars */
    public function getYearlyTotalAttribute(): int
    {
        return $this->getYearlyPriceDollarsAttribute() * 12;
    }

    // ── SCOPES ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientUser::class, 'plan_slug', 'slug');
    }

    protected $attributes = [
        'yearly_saving' => 0,
    ];
}
