<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Models\Proposal;
use App\Models\AdminRevenueLog;
use App\Models\PricingPlan;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── Stats ───────────────────────────────────────────────
        $totalUsers       = ClientUser::count();
        $usersLastMonth   = ClientUser::whereMonth('created_at', now()->subMonth()->month)->count();
        $usersThisMonth   = ClientUser::whereMonth('created_at', now()->month)->count();
        $usersChange      = $usersLastMonth > 0
            ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100, 1)
            : 0;

        $mrr              = AdminRevenueLog::succeeded()->thisMonth()->sum('amount');
        $mrrLastMonth     = AdminRevenueLog::succeeded()
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');
        $mrrChange        = $mrrLastMonth > 0
            ? round((($mrr - $mrrLastMonth) / $mrrLastMonth) * 100, 1)
            : 0;

        $proposalsSent    = Proposal::whereMonth('created_at', now()->month)->count();
        $proposalsLast    = Proposal::whereMonth('created_at', now()->subMonth()->month)->count();
        $proposalsChange  = $proposalsLast > 0
            ? round((($proposalsSent - $proposalsLast) / $proposalsLast) * 100, 1)
            : 0;

        $activeSubs       = ClientUser::whereNotNull('plan_slug')
            ->where('plan_slug', '!=', 'free')->count();
        $subsLastMonth    = 0; // extend with subscription history if needed
        $subsChange       = 0;

        // ── Recent users ────────────────────────────────────────
        $recentUsers = ClientUser::latest()->limit(8)->get();

        // ── Recent transactions ──────────────────────────────────
        $recentTransactions = AdminRevenueLog::with('user')->latest()->limit(8)->get();

        // ── Plan distribution ────────────────────────────────────
        $plans     = PricingPlan::active()->withCount('users')->get();
        $total     = max($plans->sum('users_count'), 1);
        $planStats = $plans->map(fn($p) => [
            'slug'  => $p->slug,
            'name'  => $p->name,
            'count' => $p->users_count,
            'pct'   => round($p->users_count / $total * 100),
        ]);

        return view('admin-dashboard.dashboard', [
            'stats' => [
                'total_users'          => $totalUsers,
                'users_change'         => $usersChange,
                'mrr'                  => $mrr,
                'mrr_change'           => $mrrChange,
                'proposals_sent'       => $proposalsSent,
                'proposals_change'     => $proposalsChange,
                'active_subscriptions' => $activeSubs,
                'subs_change'          => $subsChange,
            ],
            'recentUsers'        => $recentUsers,
            'recentTransactions' => $recentTransactions,
            'planStats'          => $planStats,
        ]);
    }
}