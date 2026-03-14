<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Support\Facades\Cache;

class PricingController extends Controller
{
    /**
     * GET /pricing
     * Route: Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
     */
    public function index()
    {
        // Cached for 10 minutes — run php artisan cache:clear after updating plans in DB
        $plans = Cache::remember('pricing_plans_with_features', 600, function () {
            return PricingPlan::active()->with('features')->get();
        });

        return view('frontend.partials.pricing', [
            'plans' => $plans,
            'faqs'  => $this->faqs(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Also used by FrontendController to pass $plans to homepage.
    //  Call: PricingController::getPlans()
    // ─────────────────────────────────────────────────────────────
    public static function getPlans()
    {
        return Cache::remember('pricing_plans_with_features', 600, function () {
            return PricingPlan::active()->with('features')->get();
        });
    }

    private function faqs(): array
    {
        return [
            ['q' => 'Is there a free trial on paid plans?',         'a' => 'Yes — both Pro and Agency come with a 14-day free trial. No credit card required. You\'ll only be charged if you decide to upgrade after the trial ends.'],
            ['q' => 'Can I change plans later?',                     'a' => 'Absolutely. Upgrade or downgrade at any time. If you upgrade mid-cycle we pro-rate the difference. If you downgrade, the change takes effect at the end of your billing period.'],
            ['q' => 'What happens to my proposals if I cancel?',     'a' => 'Your proposals are never deleted. If you downgrade to Free, you\'ll keep read-only access to all past proposals. Clients with existing links can still view and sign them.'],
            ['q' => 'How does the yearly billing discount work?',    'a' => 'When you choose yearly billing, you pay for 10 months and get 2 months free — a 30% saving versus monthly. You\'re billed once per year as a single payment.'],
            ['q' => 'Can I add more seats on the Agency plan?',      'a' => 'Yes. Agency includes 5 seats. Add unlimited additional seats for $8/month each (or $67/year on annual billing).'],
            ['q' => 'Do you offer refunds?',                         'a' => 'We offer a full 30-day money-back guarantee on all paid plans — no questions asked. Email support and we\'ll process your refund within 48 hours.'],
        ];
    }
}