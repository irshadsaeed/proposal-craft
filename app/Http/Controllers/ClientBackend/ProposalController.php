<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    private const ICON_PALETTE = [
        'A' => ['bg' => '#EEF2FF', 'color' => '#4F46E5'],
        'B' => ['bg' => '#FEF3C7', 'color' => '#D97706'],
        'C' => ['bg' => '#ECFDF5', 'color' => '#059669'],
        'D' => ['bg' => '#FEE2E2', 'color' => '#DC2626'],
        'E' => ['bg' => '#EFF6FF', 'color' => '#2563EB'],
        'F' => ['bg' => '#FDF4FF', 'color' => '#9333EA'],
        'G' => ['bg' => '#ECFDF5', 'color' => '#16A34A'],
        'H' => ['bg' => '#FFF7ED', 'color' => '#EA580C'],
        'I' => ['bg' => '#EEF2FF', 'color' => '#6366F1'],
        'J' => ['bg' => '#FEF9C3', 'color' => '#CA8A04'],
        'K' => ['bg' => '#FCE7F3', 'color' => '#DB2777'],
        'L' => ['bg' => '#ECFDF5', 'color' => '#059669'],
        'M' => ['bg' => '#EFF6FF', 'color' => '#1D4ED8'],
        'N' => ['bg' => '#FEF3C7', 'color' => '#B45309'],
        'O' => ['bg' => '#FFF7ED', 'color' => '#C2410C'],
        'P' => ['bg' => '#EEF2FF', 'color' => '#4338CA'],
        'Q' => ['bg' => '#FDF4FF', 'color' => '#7C3AED'],
        'R' => ['bg' => '#FEE2E2', 'color' => '#B91C1C'],
        'S' => ['bg' => '#ECFDF5', 'color' => '#047857'],
        'T' => ['bg' => '#EFF6FF', 'color' => '#1A56F0'],
        'U' => ['bg' => '#FFF7ED', 'color' => '#D97706'],
        'V' => ['bg' => '#EEF2FF', 'color' => '#7C3AED'],
        'W' => ['bg' => '#ECFDF5', 'color' => '#059669'],
        'X' => ['bg' => '#FEE2E2', 'color' => '#DC2626'],
        'Y' => ['bg' => '#FEF9C3', 'color' => '#A16207'],
        'Z' => ['bg' => '#FDF4FF', 'color' => '#9333EA'],
    ];

    private function iconStyle(string $title): array
    {
        $letter = strtoupper(substr(trim($title), 0, 1));
        return self::ICON_PALETTE[$letter] ?? ['bg' => 'var(--accent-dim)', 'color' => 'var(--accent)'];
    }

    /* ─────────────────────────────────────────────────────────────
       SANITISE SECTION CONTENT
    ───────────────────────────────────────────────────────────── */
    private function sanitiseSectionContent(?string $content, string $type): string
    {
        if (empty($content)) return $content ?? '';

        if ($type === 'image') {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && isset($decoded['src'])) {
                if (str_starts_with((string)$decoded['src'], 'data:')) {
                    $decoded['src'] = '';
                    return json_encode($decoded);
                }
            }
        }

        return $content;
    }

    /* ════════════════════════════════════════════════════════════
       CONVERT TEMPLATE BLOCKS → PROPOSAL SECTIONS
       ─────────────────────────────────────────────────────────
       Template blocks use the template-editor format:
         { id, type, data: { title, body, items, rows, … } }

       Proposal sections use:
         { id, type, title, content (JSON string), order }

       This method bridges the two formats so opening a template
       in the proposal editor gives you a fully pre-populated canvas.
    ════════════════════════════════════════════════════════════ */
    private function convertTemplateBlocksToSections(array $blocks): array
    {
        $sections = [];

        foreach ($blocks as $i => $block) {
            $type = $block['type'] ?? 'text';
            $data = $block['data'] ?? [];

            switch ($type) {

                /* ── Cover ──────────────────────────────────────── */
                case 'cover':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'cover',
                        'title'   => 'Cover',
                        'order'   => $i,
                        'content' => json_encode([
                            'title'         => $data['headline']    ?? '',
                            'subtitle'      => $data['subline']     ?? '',
                            'logo'          => $data['logo']        ?? '',
                            'coverBg'       => $data['color']       ?? '#0c0e13',
                            'accentColor'   => $data['color']       ?? '#1a56f0',
                            'coverLayout'   => 'Midnight',
                            'fontStyle'     => 'Playfair Display',
                            'showDateOnCover'   => true,
                            'showProposalNum'   => false,
                        ]),
                    ];
                    break;

                /* ── Section Title → Introduction ───────────────── */
                case 'section-title':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'intro',
                        'title'   => $data['title'] ?? 'Section',
                        'order'   => $i,
                        'content' => '',
                    ];
                    break;

                /* ── Rich Text → Introduction body ──────────────── */
                case 'rich-text':
                    /* If previous section was intro with no body, merge into it */
                    $last = end($sections);
                    if ($last && $last['type'] === 'intro' && empty($last['content'])) {
                        $sections[count($sections) - 1]['content'] = $data['body'] ?? '';
                        /* Also update the intro title if the rich-text has one */
                        if (!empty($data['title'])) {
                            $sections[count($sections) - 1]['title'] = $data['title'];
                        }
                    } else {
                        $sections[] = [
                            'id'      => null,
                            'type'    => 'intro',
                            'title'   => $data['title'] ?? 'About This Proposal',
                            'order'   => $i,
                            'content' => $data['body'] ?? '',
                        ];
                    }
                    break;

                /* ── Text Block → Introduction ───────────────────── */
                case 'text':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'intro',
                        'title'   => 'Notes',
                        'order'   => $i,
                        'content' => $data['content'] ?? '',
                    ];
                    break;

                /* ── Pricing ────────────────────────────────────── */
                case 'pricing':
                    /* Convert template pricing rows to proposal format */
                    $rows = collect($data['rows'] ?? [])->map(function ($row) {
                        $price = (float) preg_replace('/[^0-9.]/', '', $row['price'] ?? '0');
                        $qty   = (float) ($row['qty'] ?? 1);
                        return [
                            'service' => $row['item']  ?? '',
                            'qty'     => $qty,
                            'price'   => $price,
                        ];
                    })->values()->toArray();

                    $sections[] = [
                        'id'      => null,
                        'type'    => 'pricing',
                        'title'   => 'Pricing',
                        'order'   => $i,
                        'content' => json_encode([
                            'rows'        => $rows,
                            'currency'    => 'USD',
                            'showQty'     => true,
                            'showSubtotal'=> false,
                        ]),
                    ];
                    break;

                /* ── Timeline ───────────────────────────────────── */
                case 'timeline':
                    $milestones = collect($data['phases'] ?? [])->map(fn($p) => [
                        'week'  => $p['duration'] ?? '',
                        'title' => $p['name']     ?? '',
                        'desc'  => $p['desc']     ?? '',
                    ])->values()->toArray();

                    $sections[] = [
                        'id'      => null,
                        'type'    => 'timeline',
                        'title'   => $data['title'] ?? 'Project Timeline',
                        'order'   => $i,
                        'content' => json_encode(['milestones' => $milestones]),
                    ];
                    break;

                /* ── Deliverables ───────────────────────────────── */
                case 'deliverables':
                    $items = collect($data['items'] ?? [])->map(fn($item) => [
                        'text'    => is_string($item) ? $item : ($item['text'] ?? ''),
                        'checked' => true,
                    ])->values()->toArray();

                    $sections[] = [
                        'id'      => null,
                        'type'    => 'deliverables',
                        'title'   => $data['title'] ?? 'What You Will Receive',
                        'order'   => $i,
                        'content' => json_encode(['items' => $items]),
                    ];
                    break;

                /* ── Bullet List → Scope ────────────────────────── */
                case 'bullet-list':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'scope',
                        'title'   => $data['title'] ?? 'Scope of Work',
                        'order'   => $i,
                        'content' => json_encode([
                            'items' => $data['items'] ?? [],
                        ]),
                    ];
                    break;

                /* ── Team ───────────────────────────────────────── */
                case 'team':
                    $members = collect($data['members'] ?? [])->map(fn($m) => [
                        'name'     => $m['name']     ?? '',
                        'role'     => $m['role']     ?? '',
                        'initials' => strtoupper(
                            collect(explode(' ', $m['name'] ?? 'XX'))
                                ->map(fn($w) => substr($w, 0, 1))
                                ->implode('')
                        ),
                    ])->values()->toArray();

                    $sections[] = [
                        'id'      => null,
                        'type'    => 'team',
                        'title'   => $data['title'] ?? 'Meet the Team',
                        'order'   => $i,
                        'content' => json_encode(['members' => $members]),
                    ];
                    break;

                /* ── Quote → Testimonial ────────────────────────── */
                case 'quote':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'testimonial',
                        'title'   => 'Testimonial',
                        'order'   => $i,
                        'content' => json_encode([
                            'quote'    => $data['text']   ?? '',
                            'author'   => $data['author'] ?? '',
                            'role'     => '',
                            'company'  => '',
                            'initials' => strtoupper(substr($data['author'] ?? 'C', 0, 1)),
                        ]),
                    ];
                    break;

                /* ── Image ──────────────────────────────────────── */
                case 'image':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'image',
                        'title'   => $data['caption'] ?? '',
                        'order'   => $i,
                        'content' => json_encode([
                            'url'     => $data['url']     ?? '',
                            'src'     => $data['url']     ?? '',
                            'caption' => $data['caption'] ?? '',
                            'alt'     => $data['alt']     ?? '',
                        ]),
                    ];
                    break;

                /* ── Two Columns ────────────────────────────────── */
                case 'two-col':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'columns',
                        'title'   => '',
                        'order'   => $i,
                        'content' => json_encode([
                            'leftTitle'  => '',
                            'rightTitle' => '',
                            'left'       => $data['left']  ?? '',
                            'right'      => $data['right'] ?? '',
                        ]),
                    ];
                    break;

                /* ── Signature ──────────────────────────────────── */
                case 'signature':
                    $sections[] = [
                        'id'      => null,
                        'type'    => 'signature',
                        'title'   => 'Signature',
                        'order'   => $i,
                        'content' => json_encode([
                            'instructions' => 'By signing, you agree to the terms of this proposal.',
                            'requireEmail' => true,
                            'requireName'  => true,
                        ]),
                    ];
                    break;

                /* ── Divider — skip (no proposal equivalent) ─────── */
                case 'divider':
                    break;

                /* ── Anything else → intro ──────────────────────── */
                default:
                    if (!empty($data['title']) || !empty($data['body']) || !empty($data['content'])) {
                        $sections[] = [
                            'id'      => null,
                            'type'    => 'intro',
                            'title'   => $data['title'] ?? ucfirst(str_replace('-', ' ', $type)),
                            'order'   => $i,
                            'content' => $data['body'] ?? $data['content'] ?? '',
                        ];
                    }
                    break;
            }
        }

        return $sections;
    }

    /* ════════════════════════════════════════════════════════════
       NEW / EDIT VIEW
       ─────────────────────────────────────────────────────────
       FIX: Now reads ?template=ID and converts template blocks
            into proposal sections so the editor canvas is
            pre-populated with all the template data.
    ════════════════════════════════════════════════════════════ */
    public function newProposal(Request $request)
    {
        /* ── Case 1: editing an existing proposal ── */
        if ($request->filled('id')) {
            $proposal = Proposal::where('user_id', Auth::id())
                ->with(['sections' => fn($q) => $q->orderBy('order')])
                ->findOrFail($request->id);

            return view('client-dashboard.new-proposal', compact('proposal'));
        }

        /* ── Case 2: creating from a template ── */
        $templateSections = [];
        $templateName     = '';
        $templateColor    = '#1a56f0';

        if ($request->filled('template')) {
            $template = Template::where('user_id', Auth::id())
                ->find($request->template);

            if ($template && $template->content) {
                $blocks = json_decode($template->content, true);

                if (is_array($blocks) && count($blocks) > 0) {
                    $templateSections = $this->convertTemplateBlocksToSections($blocks);
                    $templateName     = $template->name;
                    $templateColor    = $template->color ?? '#1a56f0';
                }
            }
        }

        /* ── Case 3: blank new proposal ── */
        return view('client-dashboard.new-proposal', [
            'proposal'         => null,
            'templateSections' => $templateSections,   /* pre-filled sections from template */
            'templateName'     => $templateName,        /* pre-fill the title field */
            'templateColor'    => $templateColor,       /* pre-apply the cover colour */
        ]);
    }

    /* ════════════════════════════════════════════════════════════
       UPLOAD IMAGE
    ════════════════════════════════════════════════════════════ */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ]);

        try {
            $path = $request->file('image')
                ->store('client-dashboard/new-proposal-pic', 'public');

            return response()->json([
                'url'  => Storage::disk('public')->url($path),
                'path' => $path,
            ]);

        } catch (\Exception $e) {
            Log::error('[ProposalCraft] Image upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Image upload failed. Please try again.'], 500);
        }
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
            $query->where(
                fn($q) =>
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
       STORE  (POST /dashboard/proposals)
    ════════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'client'       => 'nullable|string|max:255',
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
                $type    = $section['type']    ?? 'text';
                $content = $this->sanitiseSectionContent($section['content'] ?? '', $type);

                $s = $proposal->sections()->create([
                    'title'   => $section['title']  ?? '',
                    'type'    => $type,
                    'content' => $content,
                    'order'   => $section['order']  ?? $i,
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
    ════════════════════════════════════════════════════════════ */
    public function autosave(Request $request, Proposal $proposal)
    {
        abort_if($proposal->user_id !== Auth::id(), 403);

        $scalar = $request->only(['title', 'client', 'client_email', 'amount', 'currency', 'notes', 'status']);
        if (!empty($scalar)) {
            $proposal->update($scalar);
        }

        $sectionIds = [];

        if ($request->has('sections') && is_array($request->sections)) {
            $incomingIds = collect($request->sections)
                ->pluck('id')
                ->filter(fn($id) => is_numeric($id) && $id > 0)
                ->map(fn($id) => (int) $id)
                ->values();

            $proposal->sections()
                ->whereNotIn('id', $incomingIds->toArray())
                ->delete();

            foreach ($request->sections as $i => $section) {
                $existingId = isset($section['id']) && is_numeric($section['id']) && $section['id'] > 0
                    ? (int) $section['id']
                    : null;

                $type    = $section['type']    ?? 'text';
                $content = $this->sanitiseSectionContent($section['content'] ?? '', $type);

                if ($existingId) {
                    $proposal->sections()
                        ->where('id', $existingId)
                        ->update([
                            'title'   => $section['title'] ?? '',
                            'type'    => $type,
                            'content' => $content,
                            'order'   => $section['order'] ?? $i,
                        ]);
                    $sectionIds[] = ['type' => $type, 'id' => $existingId];
                } else {
                    $s = $proposal->sections()->create([
                        'title'   => $section['title'] ?? '',
                        'type'    => $type,
                        'content' => $content,
                        'order'   => $section['order'] ?? $i,
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

                    $type    = $section['type']    ?? 'text';
                    $content = $this->sanitiseSectionContent($section['content'] ?? '', $type);

                    if ($existingId) {
                        $proposal->sections()->where('id', $existingId)->update([
                            'title'   => $section['title'] ?? '',
                            'type'    => $type,
                            'content' => $content,
                            'order'   => $section['order'] ?? $i,
                        ]);
                    } else {
                        $proposal->sections()->create([
                            'title'   => $section['title'] ?? '',
                            'type'    => $type,
                            'content' => $content,
                            'order'   => $section['order'] ?? $i,
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
            ->where(
                fn($query) =>
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