<?php
/* ============================================================
   DashboardController.php — global search across all entities
   ============================================================ */

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /* ── INDEX ──────────────────────────────────────────────── */
    public function index()
    {
        $userId         = Auth::id();
        $now            = Carbon::now();
        $startThisMonth = $now->copy()->startOfMonth();
        $startLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endLastMonth   = $now->copy()->subMonth()->endOfMonth();
        $startThisWeek  = $now->copy()->startOfWeek();
        $startLastWeek  = $now->copy()->subWeek()->startOfWeek();
        $endLastWeek    = $now->copy()->subWeek()->endOfWeek();

        $all = Proposal::where('user_id', $userId)->get();

        $acceptedAll = $all->where('status', 'accepted');

        $totalNow    = $all->where('created_at', '>=', $startThisMonth)->count();
        $totalLast   = $all->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();
        $accNow      = $all->where('status', 'accepted')->where('created_at', '>=', $startThisMonth)->count();
        $accLast     = $all->where('status', 'accepted')->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();
        $viewsNow    = $all->where('updated_at', '>=', $startThisWeek)->sum('views');
        $viewsLast   = $all->whereBetween('updated_at', [$startLastWeek, $endLastWeek])->sum('views');
        $revNow      = $all->where('status', 'accepted')->where('created_at', '>=', $startThisMonth)->sum('amount');
        $revLast     = $all->where('status', 'accepted')->whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('amount');

        $pct = fn($n, $l) => $l == 0 ? ($n > 0 ? 100 : 0) : (int) round((($n - $l) / $l) * 100);

        $stats = [
            'total'               => $all->count(),
            'accepted'            => $acceptedAll->count(),
            'views'               => $all->sum('views'),
            'revenue'             => $acceptedAll->sum('amount'),
            'total_change'        => $totalNow - $totalLast,
            'accepted_change_pct' => $pct($accNow, $accLast),
            'views_change'        => $viewsNow - $viewsLast,
            'revenue_change_pct'  => $pct($revNow, $revLast),
        ];

        $recentProposals = $all->sortByDesc('created_at')->take(5)->values();

        $chartDays  = [];
        $chartViews = [];
        for ($i = 6; $i >= 0; $i--) {
            $day          = $now->copy()->subDays($i);
            $chartDays[]  = $day->format('D');
            $chartViews[] = Proposal::where('user_id', $userId)
                ->whereDate('updated_at', $day->toDateString())
                ->sum('views');
        }

        $recentActivity = $this->buildActivityFeed($userId);

        return view('client-dashboard.dashboard', compact(
            'recentProposals', 'stats', 'chartDays', 'chartViews', 'recentActivity'
        ));
    }

    /* ── GLOBAL AJAX SEARCH ─────────────────────────────────── */
    public function search(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $userId = Auth::id();
        $results = collect();

        /* ── Proposals ── */
        Proposal::where('user_id', $userId)
            ->where(fn($query) =>
                $query->where('title',  'like', "%{$q}%")
                      ->orWhere('client', 'like', "%{$q}%")
            )
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->each(fn($p) => $results->push([
                'type'     => 'proposal',
                'icon'     => 'document',
                'id'       => $p->id,
                'title'    => $p->title,
                'subtitle' => $p->client ?? '—',
                'meta'     => '$' . number_format($p->amount),
                'badge'    => $p->status,
                'date'     => Carbon::parse($p->created_at)->format('M d, Y'),
                'initials' => strtoupper(substr($p->title, 0, 2)),
                'url'      => route('proposals') . '?id=' . $p->id,
            ]));

        /* ── Templates ── */
        Template::where('user_id', $userId)
            ->where(fn($query) =>
                $query->where('name',        'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhere('category',    'like', "%{$q}%")
            )
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->each(fn($t) => $results->push([
                'type'     => 'template',
                'icon'     => 'template',
                'id'       => $t->id,
                'title'    => $t->name,
                'subtitle' => ucfirst($t->category ?? 'template'),
                'meta'     => null,
                'badge'    => null,
                'date'     => Carbon::parse($t->created_at)->format('M d, Y'),
                'initials' => strtoupper(substr($t->name, 0, 2)),
                'url'      => route('templates'),
            ]));

        return response()->json(['results' => $results->values()]);
    }

    /* ── TRACKING ───────────────────────────────────────────── */
    public function tracking(Request $request)
    {
        $query = Proposal::where('user_id', Auth::id());

        if ($request->status && $request->status !== 'all')
            $query->where('status', $request->status);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('title',  'like', "%{$s}%")
                  ->orWhere('client', 'like', "%{$s}%")
            );
        }

        $proposals = $query->latest()->paginate(10)->withQueryString();
        $all       = Proposal::where('user_id', Auth::id())->get();
        $maxViews  = $all->max('views') ?: 1;

        $stats = [
            'totalViews' => $all->sum('views'),
            'avgTime'    => '3m 26s',
            'accepted'   => $all->where('status', 'accepted')->count(),
            'revenue'    => $all->where('status', 'accepted')->sum('amount'),
        ];

        return view('client-dashboard.tracking', compact('proposals', 'stats', 'maxViews'));
    }

    /* ── EXPORT CSV ─────────────────────────────────────────── */
    public function exportTracking()
    {
        $proposals = Proposal::where('user_id', auth()->id())
            ->orderByDesc('created_at')->get();

        $filename = 'proposals-tracking-' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($proposals) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Title', 'Client', 'Status', 'Views', 'Avg Time', 'Last Viewed', 'Sent At', 'Value ($)']);
            foreach ($proposals as $p) {
                fputcsv($h, [
                    $p->title,
                    $p->client                  ?? '—',
                    ucfirst($p->status          ?? 'draft'),
                    $p->views                   ?? 0,
                    $p->avg_time_open           ?? '—',
                    optional($p->last_viewed_at)->format('Y-m-d H:i') ?? '—',
                    optional($p->sent_at ? Carbon::parse($p->sent_at) : null)?->format('Y-m-d') ?? '—',
                    $p->amount                  ?? 0,
                ]);
            }
            fclose($h);
        }, 200, $headers);
    }

    /* ── PRIVATE: ACTIVITY FEED ─────────────────────────────── */
    private function buildActivityFeed(int $userId): \Illuminate\Support\Collection
    {
        $proposals = Proposal::where('user_id', $userId)->orderByDesc('updated_at')->take(20)->get();
        $events    = collect();

        $icons = [
            'accepted' => ['green',  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'],
            'declined' => ['red',    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'],
            'viewed'   => ['orange', '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'],
            'sent'     => ['blue',   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>'],
            'draft'    => ['blue',   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'],
        ];

        $descriptions = [
            'accepted' => fn($p) => '<strong>' . e($p->title) . '</strong> was accepted &amp; signed',
            'declined' => fn($p) => '<strong>' . e($p->title) . '</strong> was declined',
            'viewed'   => fn($p) => '<strong>' . e($p->client ?? 'A client') . '</strong> viewed <strong>' . e($p->title) . '</strong>',
            'sent'     => fn($p) => 'You sent <strong>' . e($p->title) . '</strong> to ' . e($p->client ?? 'client'),
            'draft'    => fn($p) => 'New proposal <strong>' . e($p->title) . '</strong> created',
        ];

        foreach ($proposals as $p) {
            $status = $p->status;
            if (isset($icons[$status])) {
                [$color, $svg] = $icons[$status];
                $events->push((object)[
                    'type_color'  => $color,
                    'icon_svg'    => $svg,
                    'description' => $descriptions[$status]($p),
                    'created_at'  => $p->updated_at,
                ]);
            }
            // Also push "sent" event for viewed/accepted/declined
            if (in_array($status, ['viewed', 'accepted', 'declined'])) {
                [$color, $svg] = $icons['sent'];
                $events->push((object)[
                    'type_color'  => $color,
                    'icon_svg'    => $svg,
                    'description' => $descriptions['sent']($p),
                    'created_at'  => $p->created_at,
                ]);
            }
        }

        return $events->sortByDesc('created_at')->take(8)->values();
    }
}