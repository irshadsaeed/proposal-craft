<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    /* ── Icon palette ── */
    private const ICON_PALETTE = [
        'A'=>['bg'=>'#EEF2FF','color'=>'#4F46E5'],'B'=>['bg'=>'#FEF3C7','color'=>'#D97706'],
        'C'=>['bg'=>'#ECFDF5','color'=>'#059669'],'D'=>['bg'=>'#FEE2E2','color'=>'#DC2626'],
        'E'=>['bg'=>'#EFF6FF','color'=>'#2563EB'],'F'=>['bg'=>'#FDF4FF','color'=>'#9333EA'],
        'G'=>['bg'=>'#ECFDF5','color'=>'#16A34A'],'H'=>['bg'=>'#FFF7ED','color'=>'#EA580C'],
        'I'=>['bg'=>'#EEF2FF','color'=>'#6366F1'],'J'=>['bg'=>'#FEF9C3','color'=>'#CA8A04'],
        'K'=>['bg'=>'#FCE7F3','color'=>'#DB2777'],'L'=>['bg'=>'#ECFDF5','color'=>'#059669'],
        'M'=>['bg'=>'#EFF6FF','color'=>'#1D4ED8'],'N'=>['bg'=>'#FEF3C7','color'=>'#B45309'],
        'O'=>['bg'=>'#FFF7ED','color'=>'#C2410C'],'P'=>['bg'=>'#EEF2FF','color'=>'#4338CA'],
        'Q'=>['bg'=>'#FDF4FF','color'=>'#7C3AED'],'R'=>['bg'=>'#FEE2E2','color'=>'#B91C1C'],
        'S'=>['bg'=>'#ECFDF5','color'=>'#047857'],'T'=>['bg'=>'#EFF6FF','color'=>'#1A56F0'],
        'U'=>['bg'=>'#FFF7ED','color'=>'#D97706'],'V'=>['bg'=>'#EEF2FF','color'=>'#7C3AED'],
        'W'=>['bg'=>'#ECFDF5','color'=>'#059669'],'X'=>['bg'=>'#FEE2E2','color'=>'#DC2626'],
        'Y'=>['bg'=>'#FEF9C3','color'=>'#A16207'],'Z'=>['bg'=>'#FDF4FF','color'=>'#9333EA'],
    ];

    private function iconStyle(string $title): array
    {
        $letter = strtoupper(substr(trim($title), 0, 1));
        return self::ICON_PALETTE[$letter] ?? ['bg' => 'var(--accent-dim)', 'color' => 'var(--accent)'];
    }

    /* ════════════════════════════════════════════════════════════
       LIST
    ════════════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $allForCounts = Proposal::where('user_id', $userId)->get();
        $counts = [
            'all'      => $allForCounts->count(),
            'draft'    => $allForCounts->where('status', 'draft')->count(),
            'sent'     => $allForCounts->where('status', 'sent')->count(),
            'viewed'   => $allForCounts->where('status', 'viewed')->count(),
            'accepted' => $allForCounts->where('status', 'accepted')->count(),
            'declined' => $allForCounts->where('status', 'declined')->count(),
        ];

        $query = Proposal::where('user_id', $userId);

        if ($request->filled('filter') && $request->filter !== 'all') {
            $query->where('status', $request->filter);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('title',  'like', "%{$s}%")
                  ->orWhere('client', 'like', "%{$s}%")
            );
        }

        match ($request->input('sort', 'date')) {
            'amount' => $query->orderByDesc('amount'),
            'views'  => $query->orderByDesc('views'),
            default  => $query->orderByDesc('created_at'),
        };

        $proposals = $query->paginate(10)->withQueryString();

        $proposals->getCollection()->transform(function ($p) {
            $style         = $this->iconStyle($p->title ?? '');
            $p->icon_bg    = $style['bg'];
            $p->icon_color = $style['color'];
            return $p;
        });

        return view('client-dashboard.proposals', compact('proposals', 'counts'));
    }

    /* ════════════════════════════════════════════════════════════
       NEW / EDIT VIEW
    ════════════════════════════════════════════════════════════ */
    public function newProposal(Request $request)
    {
        $proposal = null;
        if ($request->filled('id')) {
            $proposal = Proposal::where('user_id', Auth::id())
                ->with(['sections' => fn($q) => $q->orderBy('order')])
                ->findOrFail($request->id);
        }
        return view('client-dashboard.new-proposal', compact('proposal'));
    }

    /* ════════════════════════════════════════════════════════════
       STORE  (POST /dashboard/proposals)
       FIX: client is now optional (nullable) so a first-save
       with only a title doesn't fail validation.
    ════════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'client'       => 'nullable|string|max:255',   // ← was required, caused silent 422
            'client_email' => 'nullable|email|max:255',
            'amount'       => 'nullable|numeric|min:0',
            'currency'     => 'nullable|string|max:3',
            'notes'        => 'nullable|string|max:5000',
            'status'       => 'nullable|in:draft,sent,viewed,accepted,declined',
            'sections'     => 'nullable|array',
            'sections.*.title'   => 'nullable|string|max:255',
            'sections.*.type'    => 'nullable|string|max:50',
            'sections.*.content' => 'nullable|string',
            'sections.*.order'   => 'nullable|integer',
        ]);

        $proposalId = null;
        $sectionIds = [];

        DB::transaction(function () use ($validated, &$proposalId, &$sectionIds) {
            $proposal = Proposal::create([
                'user_id'      => Auth::id(),
                'title'        => $validated['title'],
                'client'       => $validated['client']       ?? '',
                'client_email' => $validated['client_email'] ?? null,
                'amount'       => $validated['amount']       ?? 0,
                'currency'     => $validated['currency']     ?? 'USD',
                'notes'        => $validated['notes']        ?? null,
                'status'       => $validated['status']       ?? 'draft',
                'views'        => 0,
            ]);

            $proposalId = $proposal->id;

            foreach ($validated['sections'] ?? [] as $i => $section) {
                $s = $proposal->sections()->create([
                    'title'   => $section['title']   ?? '',
                    'type'    => $section['type']     ?? 'text',
                    'content' => $section['content']  ?? '',
                    'order'   => $section['order']    ?? $i,
                ]);
                $sectionIds[] = ['type' => $s->type, 'id' => $s->id];
            }
        });

        return response()->json([
            'message'     => 'Proposal created.',
            'id'          => $proposalId,
            'section_ids' => $sectionIds,
        ]);
    }

    /* ════════════════════════════════════════════════════════════
       AUTOSAVE  (PATCH /dashboard/proposals/{proposal}/autosave)
       FIX 1: now saves the full `sections` array (was only
               handling singular `section`, so sections were
               NEVER saved via autosave).
       FIX 2: updateOrCreate uses type+proposal_id as the key,
               NOT id=null which corrupts data.
    ════════════════════════════════════════════════════════════ */
    public function autosave(Request $request, Proposal $proposal)
    {
        abort_if($proposal->user_id !== Auth::id(), 403);

        /* ── 1. Update scalar fields ── */
        $scalar = $request->only(['title', 'client', 'client_email', 'amount', 'currency', 'notes', 'status']);
        if (!empty($scalar)) {
            $proposal->update($scalar);
        }

        /* ── 2. Sync sections array ── */
        $sectionIds = [];

        if ($request->has('sections') && is_array($request->sections)) {
            // Collect IDs that are being sent (only existing ones, i.e. > 0)
            $incomingIds = collect($request->sections)
                ->pluck('id')
                ->filter(fn($id) => is_numeric($id) && $id > 0)
                ->map(fn($id) => (int) $id)
                ->values();

            // Delete sections that are no longer in the payload
            $proposal->sections()
                ->whereNotIn('id', $incomingIds->toArray())
                ->delete();

            foreach ($request->sections as $i => $section) {
                $existingId = isset($section['id']) && is_numeric($section['id']) && $section['id'] > 0
                    ? (int) $section['id']
                    : null;

                if ($existingId) {
                    // Update existing — ensure it belongs to this proposal
                    $proposal->sections()
                        ->where('id', $existingId)
                        ->update([
                            'title'   => $section['title']   ?? '',
                            'type'    => $section['type']    ?? 'text',
                            'content' => $section['content'] ?? '',
                            'order'   => $section['order']   ?? $i,
                        ]);
                    $sectionIds[] = ['type' => $section['type'] ?? 'text', 'id' => $existingId];
                } else {
                    // Create new section
                    $s = $proposal->sections()->create([
                        'title'   => $section['title']   ?? '',
                        'type'    => $section['type']    ?? 'text',
                        'content' => $section['content'] ?? '',
                        'order'   => $section['order']   ?? $i,
                    ]);
                    $sectionIds[] = ['type' => $s->type, 'id' => $s->id];
                }
            }
        }

        $proposal->refresh();

        return response()->json([
            'saved'       => true,
            'updated_at'  => $proposal->updated_at->format('H:i:s'),
            'section_ids' => $sectionIds,
        ]);
    }

    /* ════════════════════════════════════════════════════════════
       FULL UPDATE  (PUT /dashboard/proposals/{proposal})
    ════════════════════════════════════════════════════════════ */
    public function update(Request $request, Proposal $proposal)
    {
        abort_if($proposal->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'client'       => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'amount'       => 'nullable|numeric|min:0',
            'currency'     => 'nullable|string|max:3',
            'notes'        => 'nullable|string|max:5000',
            'status'       => 'nullable|in:draft,sent,viewed,accepted,declined',
            'sections'     => 'nullable|array',
            'sections.*.id'      => 'nullable|integer',
            'sections.*.title'   => 'nullable|string|max:255',
            'sections.*.type'    => 'nullable|string|max:50',
            'sections.*.content' => 'nullable|string',
            'sections.*.order'   => 'nullable|integer',
        ]);

        DB::transaction(function () use ($validated, $proposal) {
            $proposal->update([
                'title'        => $validated['title'],
                'client'       => $validated['client']       ?? $proposal->client,
                'client_email' => $validated['client_email'] ?? $proposal->client_email,
                'amount'       => $validated['amount']       ?? $proposal->amount,
                'currency'     => $validated['currency']     ?? $proposal->currency,
                'notes'        => $validated['notes']        ?? null,
                'status'       => $validated['status']       ?? $proposal->status,
            ]);

            if (isset($validated['sections'])) {
                $incomingIds = collect($validated['sections'])
                    ->pluck('id')
                    ->filter(fn($id) => is_numeric($id) && $id > 0)
                    ->map(fn($id) => (int) $id)
                    ->values();

                $proposal->sections()->whereNotIn('id', $incomingIds->toArray())->delete();

                foreach ($validated['sections'] as $i => $section) {
                    $existingId = isset($section['id']) && is_numeric($section['id']) && $section['id'] > 0
                        ? (int) $section['id'] : null;

                    if ($existingId) {
                        $proposal->sections()->where('id', $existingId)->update([
                            'title'   => $section['title']   ?? '',
                            'type'    => $section['type']    ?? 'text',
                            'content' => $section['content'] ?? '',
                            'order'   => $section['order']   ?? $i,
                        ]);
                    } else {
                        $proposal->sections()->create([
                            'title'   => $section['title']   ?? '',
                            'type'    => $section['type']    ?? 'text',
                            'content' => $section['content'] ?? '',
                            'order'   => $section['order']   ?? $i,
                        ]);
                    }
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Proposal saved.']);
        }
        return redirect()->route('proposals')->with('success', '"' . $proposal->title . '" updated.');
    }

    /* ════════════════════════════════════════════════════════════
       EDIT VIEW
    ════════════════════════════════════════════════════════════ */
    public function edit(Proposal $proposal)
    {
        abort_if($proposal->user_id !== Auth::id(), 403);
        $proposal->load(['sections' => fn($q) => $q->orderBy('order')]);
        return view('client-dashboard.new-proposal', compact('proposal'));
    }

    /* ════════════════════════════════════════════════════════════
       PREVIEW
    ════════════════════════════════════════════════════════════ */
    public function proposalPreview(Request $request)
    {
        $proposal = $request->filled('id')
            ? Proposal::where('user_id', Auth::id())
                ->with(['sections' => fn($q) => $q->orderBy('order')])
                ->findOrFail($request->id)
            : null;
        return view('client-dashboard.proposal-preview', compact('proposal'));
    }

    /* ════════════════════════════════════════════════════════════
       SEND
    ════════════════════════════════════════════════════════════ */
    public function send(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'proposal_id' => 'required|integer',
        ]);

        $proposal = Proposal::where('user_id', Auth::id())->findOrFail($request->proposal_id);
        $proposal->markAsSent();

        return redirect()->route('proposals')->with('success', 'Proposal sent to ' . $request->email);
    }

    /* ════════════════════════════════════════════════════════════
       DELETE
    ════════════════════════════════════════════════════════════ */
    public function destroy(Proposal $proposal)
    {
        abort_if($proposal->user_id !== Auth::id(), 403);
        $title = $proposal->title;
        $proposal->sections()->delete();
        $proposal->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }
        return redirect()->route('proposals')->with('success', '"' . $title . '" deleted.');
    }

    /* ════════════════════════════════════════════════════════════
       AJAX SEARCH
    ════════════════════════════════════════════════════════════ */
    public function search(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Proposal::where('user_id', Auth::id())
            ->where(fn($query) =>
                $query->where('title',  'like', "%{$q}%")
                      ->orWhere('client', 'like', "%{$q}%")
                      ->orWhere('status',  'like', "%{$q}%")
            )
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(fn($p) => [
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
            ]);

        return response()->json(['results' => $results]);
    }
}