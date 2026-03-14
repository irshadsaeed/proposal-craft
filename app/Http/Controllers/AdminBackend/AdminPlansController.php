<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\AdminRevenueLog;
use App\Models\ClientUser;
use App\Models\PricingFeature;
use App\Models\PricingPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPlansController extends Controller
{
    /* ═══════════════════════════════════════════════════════════
       INDEX  —  plans list page
    ═══════════════════════════════════════════════════════════ */

    public function index()
    {
        /*
        ╔══════════════════════════════════════════════════════════════════╗
        ║  BUG FIX — SQLSTATE[42S22]: Unknown column 'billing_period'     ║
        ║                                                                  ║
        ║  Root cause: old code called                                     ║
        ║    $plan->users()->where('billing_period', 'monthly')->count()   ║
        ║  `billing_period` does NOT exist on `client_users`.             ║
        ║  It lives on `admin_revenue_logs` (the Stripe event log).       ║
        ║                                                                  ║
        ║  Fix: withCount('users') counts client_users.plan_slug only.    ║
        ║  MRR is summed from AdminRevenueLog — never from client_users.  ║
        ╚══════════════════════════════════════════════════════════════════╝
        */
        $plans = PricingPlan::with('features')
            ->withCount('users')                // → $plan->users_count
            ->orderBy('sort_order')
            ->get()
            ->map(function (PricingPlan $plan) {
                // MRR from revenue log — billing_period lives HERE, not on client_users
                $plan->mrr = AdminRevenueLog::succeeded()
                    ->thisMonth()
                    ->where('plan_slug', $plan->slug)
                    ->sum('amount');

                return $plan;
            });

        // Subscriptions for the table section — paginated from client_users
        // (no billing_period filter — that column doesn't exist on client_users)
        $subscriptions = ClientUser::whereNotNull('plan_slug')
            ->where('plan_slug', '!=', 'free')
            ->latest()
            ->paginate(15);

        return view('admin-dashboard.plans-view', compact('plans', 'subscriptions'));
    }


    /* ═══════════════════════════════════════════════════════════
       SHOW  —  plan detail page  (metrics only, read-only)
    ═══════════════════════════════════════════════════════════ */

    public function show(PricingPlan $plan)
    {
        $plan->load('features');

        $plan->mrr         = AdminRevenueLog::succeeded()->thisMonth()->where('plan_slug', $plan->slug)->sum('amount');
        $plan->users_count = $plan->users()->count();

        // ── Billing breakdown — from AdminRevenueLog, NOT from client_users ──
        // If AdminRevenueLog has no billing_period column, remove these two lines.
        $plan->monthly_count = AdminRevenueLog::succeeded()
            ->where('plan_slug', $plan->slug)
            ->where('billing_period', 'monthly')
            ->distinct('user_id')
            ->count('user_id');

        $plan->yearly_count = AdminRevenueLog::succeeded()
            ->where('plan_slug', $plan->slug)
            ->where('billing_period', 'yearly')
            ->distinct('user_id')
            ->count('user_id');

        $plan->conversion_rate = $this->conversionRate($plan);
        $plan->churn_rate      = $this->churnRate($plan);
        $plan->avg_ltv         = $this->avgLtv($plan);
        $plan->cancels_30d     = $this->cancels30d($plan);

        $recentSubscriptions = ClientUser::where('plan_slug', $plan->slug)
            ->whereNotNull('plan_slug')
            ->latest()
            ->take(10)
            ->get();

        return view('admin-dashboard.plans-detail', compact('plan', 'recentSubscriptions'));
    }


    /* ═══════════════════════════════════════════════════════════
       EDIT  —  edit page
    ═══════════════════════════════════════════════════════════ */

    public function edit(PricingPlan $plan)
    {
        $plan->load('features');

        $plan->mrr         = AdminRevenueLog::succeeded()->thisMonth()->where('plan_slug', $plan->slug)->sum('amount');
        $plan->users_count = $plan->users()->count();

        return view('admin-dashboard.plans-edit', compact('plan'));
    }


    /* ═══════════════════════════════════════════════════════════
       UPDATE  —  save all plan fields + sync features
    ═══════════════════════════════════════════════════════════ */

    public function update(Request $request, PricingPlan $plan): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:64'],
            'slug'                    => [
                'required',
                'string',
                'max:32',
                'regex:/^[a-z0-9\-]+$/',
                'unique:pricing_plans,slug,' . $plan->id
            ],
            'description'             => ['nullable', 'string', 'max:200'],
            'is_active'               => ['sometimes', 'boolean'],
            'is_popular'              => ['sometimes', 'boolean'],
            'monthly_price'           => ['required', 'integer', 'min:0'],
            'yearly_price'            => ['nullable', 'integer', 'min:0'],
            'stripe_monthly_price_id' => ['nullable', 'string', 'max:255'],
            'stripe_yearly_price_id'  => ['nullable', 'string', 'max:255'],
            'limit_proposals'         => ['nullable', 'integer', 'min:-1'],
            'limit_clients'           => ['nullable', 'integer', 'min:-1'],
            'limit_team'              => ['nullable', 'integer', 'min:1'],
            'limit_storage'           => ['nullable', 'integer', 'min:-1'],
            'features'                => ['nullable', 'array'],
            'features.*.text'         => ['required', 'string', 'max:120'],
            'features.*.is_muted'     => ['sometimes', 'boolean'],
            'features.*.sort_order'   => ['sometimes', 'integer', 'min:0'],
        ]);

        // ── Update plan columns ────────────────────────────────────────
        // Only update $fillable columns that exist on pricing_plans table.
        // Columns not in PricingPlan::$fillable (limit_*) will be silently
        // ignored by ->update() — add them to $fillable if your migration
        // has those columns, or remove them from here if it doesn't.
        $plan->update([
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active'   => (bool) ($validated['is_active']  ?? $plan->is_active),
            'is_popular'  => (bool) ($validated['is_popular'] ?? $plan->is_popular),
            'monthly_price'           => $validated['monthly_price'],
            'yearly_price'            => $validated['yearly_price'] ?? 0,
            'stripe_monthly_price_id' => $validated['stripe_monthly_price_id'] ?? null,
            'stripe_yearly_price_id'  => $validated['stripe_yearly_price_id']  ?? null,
            'limit_proposals'         => $validated['limit_proposals'] ?? -1,
            'limit_clients'           => $validated['limit_clients']   ?? -1,
            'limit_team'              => $validated['limit_team']      ?? 1,
            'limit_storage'           => $validated['limit_storage']   ?? -1,
        ]);

        // ── Sync features ──────────────────────────────────────────────
        // PricingFeature FK is `pricing_plan_id` (from your model).
        // $plan->features()->delete() scopes to pricing_plan_id = $plan->id ✓
        // $plan->features()->create([...]) auto-sets pricing_plan_id       ✓
        if (array_key_exists('features', $validated)) {
            $plan->features()->delete();

            foreach (($validated['features'] ?? []) as $i => $feat) {
                $plan->features()->create([
                    'text'       => $feat['text'],
                    'is_muted'   => (bool) ($feat['is_muted']   ?? false),
                    'sort_order' => (int)  ($feat['sort_order'] ?? $i),
                ]);
            }
        }

        return redirect()
            ->route('admin.plans.index')
            ->with('flash',     'success')
            ->with('flash_msg', '"' . $plan->name . '" updated successfully.');
    }


    /* ═══════════════════════════════════════════════════════════
       TOGGLE  —  flip is_active via AJAX (used by plans.js)
    ═══════════════════════════════════════════════════════════ */

    public function toggle(Request $request, PricingPlan $plan): JsonResponse
    {
        $active = $request->boolean('is_active', !$plan->is_active);

        $plan->update(['is_active' => $active]);

        return response()->json([
            'ok'        => true,
            'success'   => true,           // plans.js checks data.success
            'is_active' => $plan->is_active,
            'message'   => $plan->name . ' ' . ($plan->is_active ? 'activated' : 'deactivated') . '.',
        ]);
    }


    /* ═══════════════════════════════════════════════════════════
       DESTROY  —  hard delete (blocks if active users)
    ═══════════════════════════════════════════════════════════ */

    public function destroy(PricingPlan $plan): JsonResponse
    {
        if ($plan->users()->exists()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Cannot delete ' . $plan->name . ' — it has active subscribers. Archive it instead.',
            ], 422);
        }

        $name = $plan->name;
        $plan->features()->delete();   // pricing_plan_id scoped ✓
        $plan->delete();

        return response()->json([
            'ok'      => true,
            'message' => $name . ' deleted permanently.',
        ]);
    }


    /* ═══════════════════════════════════════════════════════════
       ARCHIVE  —  hide from new sign-ups, keep existing users
    ═══════════════════════════════════════════════════════════ */

    public function archive(PricingPlan $plan): JsonResponse
    {
        $plan->update([
            'is_active'       => false,
            'show_on_pricing' => false,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => $plan->name . ' archived — hidden from new sign-ups.',
        ]);
    }


    /* ═══════════════════════════════════════════════════════════
       PRIVATE HELPERS  (unchanged from original — use your real
       column names if trial_ends_at / cancelled_at differ)
    ═══════════════════════════════════════════════════════════ */

    private function conversionRate(PricingPlan $plan): float
    {
        // TODO: implement once trial_ends_at column exists on client_users
        return 0.0;
    }

    private function churnRate(PricingPlan $plan): float
    {
        // TODO: implement once cancelled_at column exists on client_users
        return 0.0;
    }

    private function avgLtv(PricingPlan $plan): float
    {
        return (float) (AdminRevenueLog::succeeded()
            ->where('plan_slug', $plan->slug)
            ->avg('amount') ?? 0.0);
    }

    private function cancels30d(PricingPlan $plan): int
    {
        // TODO: implement once cancelled_at column exists on client_users
        return 0;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:64',
            'slug'                    => 'required|string|max:64|unique:pricing_plans,slug|regex:/^[a-z0-9\-]+$/',
            'monthly_price'           => 'required|integer|min:0',
            'yearly_price'            => 'nullable|integer|min:0',
            'stripe_monthly_price_id' => 'nullable|string|max:128',
            'stripe_yearly_price_id'  => 'nullable|string|max:128',
            'description'             => 'nullable|string|max:500',
            'is_active'               => 'boolean',
            'is_popular'              => 'boolean',
            'features'                => 'nullable|array',
            'features.*.text'         => 'required|string|max:140',
            'features.*.is_muted'     => 'nullable|boolean',
        ]);

        $plan = PricingPlan::create($validated);

        if (!empty($validated['features'])) {
            foreach ($validated['features'] as $i => $feature) {
                $plan->features()->create([
                    'text'       => $feature['text'],
                    'is_muted'   => $feature['is_muted'] ?? false,
                    'sort_order' => $i,
                ]);
            }
        }

        return response()->json(['plan' => $plan->load('features')]);
    }
}
