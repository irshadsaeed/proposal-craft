<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalTrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    /* ════════════════════════════════════════════════════════════
       MAIN TRACKING DASHBOARD
    ════════════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $range  = (int) $request->input('range', 30);

        // Clamp range to allowed values
        $range = in_array($range, [7, 30, 90, 365]) ? $range : 30;

        $now   = Carbon::now();
        $start = $now->copy()->subDays($range);
        $prev  = $now->copy()->subDays($range * 2); // previous period for % change

        /* ── Base query helpers ── */
        $base        = fn() => Proposal::where('user_id', $userId);
        $inRange     = fn($q) => $q->whereBetween('created_at', [$start, $now]);
        $inPrevRange = fn($q) => $q->whereBetween('created_at', [$prev, $start]);

        /* ── Stat counts — current period ── */
        $sentNow     = $base()->where('status', '!=', 'draft')->whereBetween('created_at', [$start, $now])->count();
        $viewedNow   = $base()->where('views', '>', 0)->whereBetween('created_at', [$start, $now])->count();
        $acceptedNow = $base()->where('status', 'accepted')->whereBetween('created_at', [$start, $now])->count();
        $declinedNow = $base()->where('status', 'declined')->whereBetween('created_at', [$start, $now])->count();
        $revenueNow  = $base()->where('status', 'accepted')->whereBetween('accepted_at', [$start, $now])->sum('amount');

        /* ── Stat counts — previous period (for % delta) ── */
        $sentPrev     = $base()->where('status', '!=', 'draft')->whereBetween('created_at', [$prev, $start])->count();
        $viewedPrev   = $base()->where('views', '>', 0)->whereBetween('created_at', [$prev, $start])->count();
        $acceptedPrev = $base()->where('status', 'accepted')->whereBetween('created_at', [$prev, $start])->count();
        $revenuePrev  = $base()->where('status', 'accepted')->whereBetween('accepted_at', [$prev, $start])->sum('amount');

        /* ── % change helper ── */
        $pct = fn($cur, $prev) => $prev > 0
            ? round((($cur - $prev) / $prev) * 100)
            : ($cur > 0 ? 100 : 0);

        $stats = [
            'sent'            => $sentNow,
            'viewed'          => $viewedNow,
            'accepted'        => $acceptedNow,
            'declined'        => $declinedNow,
            'revenue'         => (float) $revenueNow,
            'sent_change'     => $pct($sentNow, $sentPrev),
            'viewed_change'   => $pct($viewedNow, $viewedPrev),
            'accepted_change' => $pct($acceptedNow, $acceptedPrev),
            'revenue_change'  => $pct($revenueNow, $revenuePrev),
        ];

        /* ── Sparkbar data (7 sub-intervals of the range) ── */
        $stats['sparkbars'] = $this->buildSparkbars($userId, $range);

        /* ── Proposals table ── */
        $query = Proposal::where('user_id', $userId)
            ->with('sections');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $s = $request->q;
            $query->where(fn($q) =>
                $q->where('title',  'like', "%{$s}%")
                  ->orWhere('client', 'like', "%{$s}%")
            );
        }

        $proposals = $query
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        // Max views for bar width calculation
        $maxViews = Proposal::where('user_id', $userId)->max('views') ?: 1;

        /* ── Activity feed ── */
        $activity = $this->buildActivityFeed($userId, 12);

        /* ── Monthly bar chart — last 7 months ── */
        $chartData = $this->buildMonthlyChart($userId, 7);

        /* ── Top clients ── */
        $clients = $this->buildTopClients($userId, 5);

        return view('client-dashboard.tracking', compact(
            'stats', 'proposals', 'maxViews',
            'activity', 'chartData', 'clients', 'range'
        ));
    }

    /* ════════════════════════════════════════════════════════════
       SPARKBAR DATA
       7 evenly-spaced intervals within the selected range
    ════════════════════════════════════════════════════════════ */
    private function buildSparkbars(int $userId, int $rangeDays): array
    {
        $intervals = 7;
        $step      = max(1, (int) ceil($rangeDays / $intervals));
        $now       = Carbon::now();

        $sent = $views = $accepted = $revenue = [];

        for ($i = $intervals; $i >= 1; $i--) {
            $end   = $now->copy()->subDays(($i - 1) * $step);
            $start = $end->copy()->subDays($step);

            $sent[]     = Proposal::where('user_id', $userId)
                ->where('status', '!=', 'draft')
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $views[]    = Proposal::where('user_id', $userId)
                ->where('views', '>', 0)
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $accepted[] = Proposal::where('user_id', $userId)
                ->where('status', 'accepted')
                ->whereBetween('accepted_at', [$start, $end])
                ->count();

            $revenue[]  = (int) Proposal::where('user_id', $userId)
                ->where('status', 'accepted')
                ->whereBetween('accepted_at', [$start, $end])
                ->sum('amount');
        }

        return [
            'sent'     => implode(',', $sent),
            'viewed'   => implode(',', $views),
            'accepted' => implode(',', $accepted),
            'revenue'  => implode(',', $revenue),
        ];
    }

    /* ════════════════════════════════════════════════════════════
       ACTIVITY FEED
       Combines tracking events + proposal status changes
    ════════════════════════════════════════════════════════════ */
    private function buildActivityFeed(int $userId, int $limit): \Illuminate\Support\Collection
    {
        // Real tracking events if table exists
        $events = collect();

        try {
            $events = ProposalTrackingEvent::whereHas('proposal', fn($q) =>
                    $q->where('user_id', $userId)
                )
                ->with('proposal')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($evt) {
                    $title  = $evt->proposal->title ?? 'a proposal';
                    $client = $evt->proposal->client ?? '';
                    $who    = $client ? "<strong>{$client}</strong>" : 'Someone';

                    $text = match ($evt->event_type) {
                        'view'    => "{$who} viewed <em>{$title}</em>",
                        'accept'  => "{$who} accepted <em>{$title}</em>",
                        'decline' => "{$who} declined <em>{$title}</em>",
                        'open'    => "{$who} opened <em>{$title}</em>",
                        'sign'    => "{$who} signed <em>{$title}</em>",
                        default   => "Activity on <em>{$title}</em>",
                    };

                    return (object) [
                        'type'        => $evt->event_type,
                        'description' => $text,
                        'created_at'  => $evt->created_at,
                    ];
                });
        } catch (\Exception $e) {
            // Table may not exist yet — fall back to proposal status history
        }

        // Fallback: synthesise feed from recent proposal status changes
        if ($events->isEmpty()) {
            $recent = Proposal::where('user_id', $userId)
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get();

            $events = $recent->map(function ($p) {
                $who  = $p->client ? "<strong>{$p->client}</strong>" : 'A client';
                $name = "<em>{$p->title}</em>";

                [$type, $text] = match ($p->status) {
                    'accepted' => ['accept', "{$who} accepted {$name}"],
                    'declined' => ['decline', "{$who} declined {$name}"],
                    'viewed'   => ['view',    "{$who} viewed {$name}"],
                    'sent'     => ['send',    "You sent {$name} to {$who}"],
                    default    => ['open',    "Draft created: {$name}"],
                };

                return (object) [
                    'type'        => $type,
                    'description' => $text,
                    'created_at'  => $p->updated_at,
                ];
            });
        }

        return $events->take($limit);
    }

    /* ════════════════════════════════════════════════════════════
       MONTHLY BAR CHART
    ════════════════════════════════════════════════════════════ */
    private function buildMonthlyChart(int $userId, int $months): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $end   = $month->copy()->endOfMonth();

            $sent = Proposal::where('user_id', $userId)
                ->where('status', '!=', 'draft')
                ->whereBetween('created_at', [$month, $end])
                ->count();

            $accepted = Proposal::where('user_id', $userId)
                ->where('status', 'accepted')
                ->whereBetween('created_at', [$month, $end])
                ->count();

            $data[] = [
                'label'    => $month->format('M'),
                'sent'     => $sent,
                'accepted' => $accepted,
            ];
        }

        return $data;
    }

    /* ════════════════════════════════════════════════════════════
       TOP CLIENTS
    ════════════════════════════════════════════════════════════ */
    private function buildTopClients(int $userId, int $limit): \Illuminate\Support\Collection
    {
        return Proposal::where('user_id', $userId)
            ->where('status', 'accepted')
            ->whereNotNull('client')
            ->where('client', '!=', '')
            ->select('client', DB::raw('COUNT(*) as proposals_count'), DB::raw('SUM(amount) as total_value'))
            ->groupBy('client')
            ->orderByDesc('total_value')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $parts    = explode(' ', trim($row->client));
                $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                return (object) [
                    'name'             => $row->client,
                    'initials'         => $initials,
                    'proposals_count'  => $row->proposals_count,
                    'total_value'      => (float) $row->total_value,
                ];
            });
    }

    /* ════════════════════════════════════════════════════════════
       EXPORT (CSV)
    ════════════════════════════════════════════════════════════ */
    public function export(Request $request)
    {
        $userId = Auth::id();

        $proposals = Proposal::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['title', 'client', 'client_email', 'status', 'amount', 'currency', 'views', 'sent_at', 'accepted_at', 'created_at']);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="proposals-export-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($proposals) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, ['Title', 'Client', 'Email', 'Status', 'Amount', 'Currency', 'Views', 'Sent At', 'Accepted At', 'Created At']);

            foreach ($proposals as $p) {
                fputcsv($handle, [
                    $p->title,
                    $p->client,
                    $p->client_email,
                    $p->status,
                    $p->amount,
                    $p->currency,
                    $p->views,
                    $p->sent_at?->format('Y-m-d H:i'),
                    $p->accepted_at?->format('Y-m-d H:i'),
                    $p->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}