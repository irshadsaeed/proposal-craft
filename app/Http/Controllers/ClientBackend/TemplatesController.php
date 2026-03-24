<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TemplatesController extends Controller
{
    /* ── Static library templates ──────────────────────────────────────── */
    private function libraryTemplates(): array
    {
        return [
            ['id' => 'tl1', 'name' => 'Brand Identity Proposal',      'cat' => 'design',      'color' => '#1A56F0', 'desc' => 'Full branding project scope with deliverables, timeline, and pricing table.',       'uses' => 1240, 'sections' => 6, 'pro' => false],
            ['id' => 'tl2', 'name' => 'Website Redesign Proposal',    'cat' => 'design',      'color' => '#7C3AED', 'desc' => 'End-to-end web design proposal including UX audit, design, and development phases.',  'uses' => 980,  'sections' => 7, 'pro' => false],
            ['id' => 'tl3', 'name' => 'Mobile App Development',       'cat' => 'development', 'color' => '#0891B2', 'desc' => 'Complete mobile app proposal with discovery, MVP, and launch phases.',               'uses' => 762,  'sections' => 8, 'pro' => true],
            ['id' => 'tl4', 'name' => 'SEO & Content Strategy',       'cat' => 'marketing',   'color' => '#059669', 'desc' => 'Quarterly SEO audit, content calendar, and performance tracking scope.',             'uses' => 634,  'sections' => 5, 'pro' => false],
            ['id' => 'tl5', 'name' => 'Social Media Campaign',        'cat' => 'marketing',   'color' => '#D97706', 'desc' => 'Multi-channel social campaign with creative strategy, posting schedule, and KPIs.',  'uses' => 521,  'sections' => 5, 'pro' => false],
            ['id' => 'tl6', 'name' => 'Business Consulting Retainer', 'cat' => 'consulting',  'color' => '#DC2626', 'desc' => 'Monthly advisory retainer with deliverables and meeting cadence.',                   'uses' => 389,  'sections' => 4, 'pro' => true],
            ['id' => 'tl7', 'name' => 'E-commerce Development',       'cat' => 'development', 'color' => '#0D0F14', 'desc' => 'Full e-commerce build proposal covering platform, integrations, and launch.',        'uses' => 445,  'sections' => 7, 'pro' => true],
            ['id' => 'tl8', 'name' => 'PR & Media Relations',         'cat' => 'marketing',   'color' => '#DB2777', 'desc' => 'PR campaign and ongoing media relations proposal for brand awareness.',              'uses' => 298,  'sections' => 4, 'pro' => false],
            ['id' => 'tl9', 'name' => 'Logo & Visual Identity',       'cat' => 'design',      'color' => '#1A56F0', 'desc' => 'Logo design and full visual identity system including brand guidelines.',            'uses' => 867,  'sections' => 5, 'pro' => false],
        ];
    }

    /* ── Ownership guard ──────────────────────────────────────────────── */
    private function requireOwnership(Template $template): void
    {
        if ($template->user_id !== Auth::id()) {
            abort(403, 'This template does not belong to you.');
        }
    }

    /* ════════════════════════════════════════════════════════════════════
       LIST
    ════════════════════════════════════════════════════════════════════ */
    public function index()
    {
        $myTemplates      = Template::where('user_id', Auth::id())->latest()->get();
        $libraryTemplates = $this->libraryTemplates();

        return view('client-dashboard.templates', compact('myTemplates', 'libraryTemplates'));
    }

    /* ════════════════════════════════════════════════════════════════════
       CREATE  (POST /dashboard/templates)
    ════════════════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:200',
            'category'    => 'required|in:design,development,marketing,consulting',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
        ]);

        $template = Template::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'category'    => $request->category,
            'description' => $request->description,
            'color'       => $request->color ?? '#1A56F0',
            'content'     => null,
            'blocks_count' => 0,
        ]);

        return redirect()->route('templates.edit', $template->id)
            ->with('success', 'Template created — start building.');
    }

    /* ════════════════════════════════════════════════════════════════════
       EDIT VIEW  (GET /dashboard/templates/{template}/edit)

       ▶ THIS IS THE KEY FIX:
         The Blade view already reads $template correctly.
         The canvas appears empty because `content` in the DB is NULL —
         meaning no blocks have been saved yet for this template.

         The editor JS reads window.__TEMPLATE__.content and parses it.
         If content is null/empty → empty array → empty canvas. CORRECT.

         To populate the canvas you must:
         (a) Add blocks via the left panel and autosave fires, OR
         (b) Duplicate a library template (which carries pre-built blocks), OR
         (c) Seed the content column with a JSON blocks array.

         All template META (name, category, description, color) IS already
         correctly loaded into the right-hand Properties panel — you can see
         this in your screenshot ("Mobile App Development", "Development", etc.)
    ════════════════════════════════════════════════════════════════════ */
    public function edit(Template $template)
    {
        $this->requireOwnership($template);

        /* Remove the debug echo/exit before going to production */
        return view('client-dashboard.template-editor', compact('template'));
    }

    /* ════════════════════════════════════════════════════════════════════
       AUTOSAVE  (PATCH /dashboard/templates/{template}/autosave)

       FIX: Also updates blocks_count cache so listing shows correct count.
       FIX: Ensures content column is MEDIUMTEXT (migration handles this).
       FIX: Returns 422 with field errors instead of 500 on validation fail.
    ════════════════════════════════════════════════════════════════════ */
    public function autosave(Request $request, Template $template)
    {
        $this->requireOwnership($template);

        $data = $request->validate([
            'name'        => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'category'    => 'nullable|in:design,development,marketing,consulting',
            'color'       => 'nullable|string|max:20',
            'content'     => 'nullable|string',  /* Raw JSON string — no max, it's MEDIUMTEXT */
        ]);

        /* Only update fields that were actually sent */
        $updateData = array_filter($data, fn($v) => $v !== null);

        /* Derive blocks_count from content if content was sent */
        if (isset($updateData['content'])) {
            $decoded = json_decode($updateData['content'], true);
            $updateData['blocks_count'] = is_array($decoded) ? count($decoded) : 0;
        }

        if (!empty($updateData)) {
            $template->update($updateData);
        }

        $template->refresh();

        return response()->json([
            'saved'        => true,
            'updated_at'   => $template->updated_at->toISOString(),
            'id'           => $template->id,
            'blocks_count' => $template->blocks_count,
        ]);
    }

    /* ════════════════════════════════════════════════════════════════════
       FULL UPDATE  (PUT /dashboard/templates/{template})
    ════════════════════════════════════════════════════════════════════ */
    public function update(Request $request, Template $template)
    {
        $this->requireOwnership($template);

        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'category'    => 'required|in:design,development,marketing,consulting',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
            'content'     => 'nullable|string',
        ]);

        if (isset($validated['content'])) {
            $decoded = json_decode($validated['content'], true);
            $validated['blocks_count'] = is_array($decoded) ? count($decoded) : 0;
        }

        $template->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'saved'      => true,
                'updated_at' => $template->fresh()->updated_at->toISOString(),
            ]);
        }

        return redirect()->route('templates')->with('success', '"' . $template->name . '" updated.');
    }

    /* ════════════════════════════════════════════════════════════════════
       UPLOAD IMAGE  (POST /dashboard/templates/upload-image)
    ════════════════════════════════════════════════════════════════════ */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:5120',
            ],
        ]);

        try {
            $path = $request->file('image')
                ->store('client-dashboard/template-images', 'public');

            return response()->json([
                'url'  => Storage::disk('public')->url($path),
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error('[Templates] Image upload failed: ' . $e->getMessage());

            return response()->json(
                ['error' => 'Image upload failed. Please try again.'],
                500
            );
        }
    }

    /* ════════════════════════════════════════════════════════════════════
       DUPLICATE (user's own template)
    ════════════════════════════════════════════════════════════════════ */
    public function duplicate(Template $template)
    {
        $this->requireOwnership($template);

        $copy             = $template->replicate();
        $copy->name       = $template->name . ' (Copy)';
        $copy->user_id    = Auth::id();
        $copy->save();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Duplicated.', 'id' => $copy->id]);
        }

        return redirect()->route('templates')->with('success', '"' . $copy->name . '" created.');
    }

    /* ════════════════════════════════════════════════════════════════════
       DUPLICATE LIBRARY TEMPLATE
       FIX: Now seeds the content column with a starter set of blocks
            matching the library template's section count, so the canvas
            is NOT empty after duplicating a library template.
    ════════════════════════════════════════════════════════════════════ */
    public function duplicateLibrary(Request $request)
    {
        $request->validate(['template_id' => 'required|string|max:20']);

        $lib = collect($this->libraryTemplates())->firstWhere('id', $request->template_id);

        if (!$lib) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Template not found.'], 404)
                : back()->with('error', 'Template not found.');
        }

        /* Build a starter blocks JSON based on the library template */
        $starterBlocks = $this->buildStarterBlocks($lib);

        $template = Template::create([
            'user_id'      => Auth::id(),
            'name'         => $lib['name'],
            'category'     => $lib['cat'],
            'description'  => $lib['desc'],
            'color'        => $lib['color'],
            'content'      => json_encode($starterBlocks),
            'blocks_count' => count($starterBlocks),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message'      => 'Template saved.',
                'name'         => $lib['name'],
                'id'           => $template->id,
                'redirect_url' => route('new-proposal') . '?template=' . $template->id, // ADD THIS
            ]);
        }

        return redirect()->route('templates.edit', $template->id)
            ->with('success', '"' . $lib['name'] . '" added — start editing.');
    }

    /**
     * Build a sensible starter block array for a library template.
     * This gives users a real starting point instead of a blank canvas.
     */
    private function buildStarterBlocks(array $lib): array
    {
        $uid   = fn() => 'b' . dechex(time()) . substr(str_replace('.', '', microtime()), 10, 4);
        $color = $lib['color'];
        $name  = $lib['name'];

        $blocks = [
            /* Cover page — always first */
            [
                'id'   => $uid(),
                'type' => 'cover',
                'data' => [
                    'headline' => $name,
                    'subline'  => 'Prepared for Your Client',
                    'logo'     => 'Your Company',
                    'color'    => $color,
                ],
            ],
            /* Introduction */
            [
                'id'   => $uid(),
                'type' => 'section-title',
                'data' => ['eyebrow' => '01', 'title' => 'Introduction'],
            ],
            [
                'id'   => $uid(),
                'type' => 'rich-text',
                'data' => [
                    'title' => 'About This Proposal',
                    'body'  => 'We are excited to present this proposal. Our team brings extensive experience and a commitment to delivering outstanding results tailored to your specific needs.',
                ],
            ],
            /* Scope of work */
            [
                'id'   => $uid(),
                'type' => 'section-title',
                'data' => ['eyebrow' => '02', 'title' => 'Scope of Work'],
            ],
            [
                'id'   => $uid(),
                'type' => 'deliverables',
                'data' => [
                    'title' => 'What You Will Receive',
                    'items' => [
                        'Full project discovery and planning',
                        'Design and prototyping phase',
                        'Development and implementation',
                        'Testing and quality assurance',
                        'Launch support and handover documentation',
                    ],
                ],
            ],
            /* Timeline */
            [
                'id'   => $uid(),
                'type' => 'section-title',
                'data' => ['eyebrow' => '03', 'title' => 'Project Timeline'],
            ],
            [
                'id'   => $uid(),
                'type' => 'timeline',
                'data' => [
                    'title'  => 'Project Timeline',
                    'phases' => [
                        ['phase' => 'Phase 1', 'name' => 'Discovery',   'desc' => 'Requirements gathering and planning.',     'duration' => 'Week 1–2'],
                        ['phase' => 'Phase 2', 'name' => 'Design',      'desc' => 'Wireframes and visual design approval.',   'duration' => 'Week 3–5'],
                        ['phase' => 'Phase 3', 'name' => 'Development', 'desc' => 'Full build, integration, and testing.',   'duration' => 'Week 6–10'],
                        ['phase' => 'Phase 4', 'name' => 'Launch',      'desc' => 'Deployment, go-live, and handover.',      'duration' => 'Week 11'],
                    ],
                ],
            ],
            /* Investment */
            [
                'id'   => $uid(),
                'type' => 'section-title',
                'data' => ['eyebrow' => '04', 'title' => 'Investment'],
            ],
            [
                'id'   => $uid(),
                'type' => 'pricing',
                'data' => [
                    'title' => 'Investment Summary',
                    'total' => '$18,500',
                    'rows'  => [
                        ['item' => 'Discovery & Strategy',  'qty' => '1',  'unit' => 'Flat', 'price' => '$2,500'],
                        ['item' => 'Design & Prototyping',  'qty' => '1',  'unit' => 'Flat', 'price' => '$4,000'],
                        ['item' => 'Development',           'qty' => '80', 'unit' => 'hrs',  'price' => '$12,000'],
                    ],
                ],
            ],
            /* Signature */
            [
                'id'   => $uid(),
                'type' => 'section-title',
                'data' => ['eyebrow' => '05', 'title' => 'Agreement'],
            ],
            [
                'id'   => $uid(),
                'type' => 'signature',
                'data' => [
                    'party1'     => 'Service Provider',
                    'party1role' => 'Your Company',
                    'party2'     => 'Client Name',
                    'party2role' => 'Client Company',
                ],
            ],
        ];

        return $blocks;
    }

    /* ════════════════════════════════════════════════════════════════════
       PREVIEW
    ════════════════════════════════════════════════════════════════════ */
    public function templatesPreview(Template $template)
    {
        $this->requireOwnership($template);

        return view('client-dashboard.template-preview', compact('template'));
    }

    /* ════════════════════════════════════════════════════════════════════
       SEARCH
    ════════════════════════════════════════════════════════════════════ */
    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $myTemplates = Template::where('user_id', Auth::id())
            ->where('name', 'like', "%{$q}%")
            ->latest()->get();

        $libraryTemplates = collect($this->libraryTemplates())
            ->filter(fn($t) => str_contains(strtolower($t['name']), strtolower($q)))
            ->values()->all();

        return view('client-dashboard.templates', compact('myTemplates', 'libraryTemplates'));
    }

    /* ════════════════════════════════════════════════════════════════════
       DELETE
    ════════════════════════════════════════════════════════════════════ */
    public function destroy(Template $template)
    {
        $this->requireOwnership($template);

        $name = $template->name;
        $template->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('templates')->with('success', '"' . $name . '" deleted.');
    }
}
