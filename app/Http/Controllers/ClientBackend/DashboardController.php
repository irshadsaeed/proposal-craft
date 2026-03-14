<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $all = Proposal::where('user_id', Auth::id())->get();
        $recentProposals = $all->sortByDesc('created_at')->take(5);

        $stats = [
            'total'    => $all->count(),
            'accepted' => $all->where('status', 'accepted')->count(),
            'views'    => $all->sum('views'),
            'revenue'  => $all->where('status', 'accepted')->sum('amount'),
        ];

        return view('client-dashboard.dashboard', compact('recentProposals', 'stats'));
    }

    public function tracking(Request $request)
    {
        $query = Proposal::where('user_id', Auth::id());

        if ($request->status && $request->status !== 'all')
            $query->where('status', $request->status);

        if ($request->search)
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')->orWhere('client', 'like', '%' . $request->search . '%'));

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

    public function exportTracking()
    {
        $proposals = Proposal::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        $filename = 'proposals-tracking-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($proposals) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Proposal Title',
                'Client',
                'Status',
                'Views',
                'Avg. Time Open',
                'Last Viewed',
                'Sent At',
                'Value ($)',
            ]);

            foreach ($proposals as $p) {
                fputcsv($handle, [
                    $p->title,
                    $p->client_name          ?? '—',
                    ucfirst($p->status       ?? 'draft'),
                    $p->view_count           ?? 0,
                    $p->avg_time_open        ?? '—',
                    $p->last_viewed_at       ? $p->last_viewed_at->format('Y-m-d H:i') : '—',
                    $p->sent_at              ? \Carbon\Carbon::parse($p->sent_at)->format('Y-m-d') : '—',
                    $p->total                ?? 0,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
