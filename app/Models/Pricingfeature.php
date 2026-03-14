<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingFeature extends Model
{
    protected $fillable = [
        'pricing_plan_id',
        'text',
        'tooltip',
        'is_muted',
        'sort_order',
    ];

    protected $casts = [
        'is_muted' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }
}