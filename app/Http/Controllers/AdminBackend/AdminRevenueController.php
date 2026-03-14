<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\AdminRevenueLog;
use Illuminate\Http\Request;

class AdminRevenueController extends Controller
{
    public function index(Request $request)
    {
        // ── Summary stats ────────────────────────────────────────
        $mrr       = AdminRevenueLog::succeeded()->thisMonth()->sum('amount');
        $mrrLast   = AdminRevenueLog::succeeded()
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');
        $mrrChange = $mrrLast > 0 ? round((($mrr - $mrrLast) / $mrrLast) * 100, 1) : 0;

        $arr       = AdminRevenueLog::succeeded()->thisYear()->sum('amount');
        $arrChange = 0;

        $thisMonth      = $mrr;
        $lastMonth      = $mrrLast;
        $monthChange    = $mrrChange;

        $refunds        = AdminRevenueLog::where('status', 'refunded')->thisMonth()->sum('amount');
        $refundChange   = 0;

        // ── Chart data (last 30 days default) ────────────────────
        $chartData = $this->buildChartData(30);

        // ── Transactions ─────────────────────────────────────────
        $query = AdminRevenueLog::with('user')->latest('paid_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($plan = $request->get('plan')) {
            $query->where('plan_slug', $plan);
        }

        $transactions = $query->paginate(20);

        return view('admin-dashboard.revenue', [
            'revenue' => [
                'mrr'          => $mrr,
                'mrr_change'   => $mrrChange,
                'arr'          => $arr,
                'arr_change'   => $arrChange,
                'this_month'   => $thisMonth,
                'month_change' => $monthChange,
                'refunds'      => $refunds,
                'refund_change'=> $refundChange,
            ],
            'chartData'    => $chartData,
            'transactions' => $transactions,
        ]);
    }

    public function chartData(Request $request)
    {
        $period = (int) $request->get('period', 30);
        return response()->json($this->buildChartData($period));
    }

    public function export()
    {
        $transactions = AdminRevenueLog::with('user')->succeeded()->latest('paid_at')->get();

        $csv = "Date,User,Email,Plan,Billing,Amount,Status\n";
        foreach ($transactions as $tx) {
            $csv .= implode(',', [
                $tx->paid_at?->format('Y-m-d') ?? '',
                $tx->user->name ?? '',
                $tx->user->email ?? '',
                $tx->plan_slug,
                $tx->billing_period,
                '$' . $tx->amount_dollars,
                $tx->status,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="revenue-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function buildChartData(int $days): array
    {
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = now()->subDays($i);
            $labels[] = $date->format('M d');
            $values[] = AdminRevenueLog::succeeded()
                ->whereDate('paid_at', $date->toDateString())
                ->sum('amount') / 100;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}