<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplatesController extends Controller
{
    // Library templates (static data — replace with DB later)
    private function libraryTemplates(): array
    {
        return [
            ['id' => 'tl1', 'name' => 'Brand Identity Proposal',      'cat' => 'design',      'color' => '#1A56F0', 'desc' => 'Full branding project scope with deliverables, timeline, and pricing table.', 'uses' => 1240, 'sections' => 6, 'pro' => false],
            ['id' => 'tl2', 'name' => 'Website Redesign Proposal',    'cat' => 'design',      'color' => '#7C3AED', 'desc' => 'End-to-end web design proposal including UX audit, design, and development phases.', 'uses' => 980, 'sections' => 7, 'pro' => false],
            ['id' => 'tl3', 'name' => 'Mobile App Development',       'cat' => 'development', 'color' => '#0891B2', 'desc' => 'Complete mobile app proposal with discovery, MVP, and launch phases.', 'uses' => 762, 'sections' => 8, 'pro' => true],
            ['id' => 'tl4', 'name' => 'SEO & Content Strategy',       'cat' => 'marketing',   'color' => '#059669', 'desc' => 'Quarterly SEO audit, content calendar, and performance tracking scope.', 'uses' => 634, 'sections' => 5, 'pro' => false],
            ['id' => 'tl5', 'name' => 'Social Media Campaign',        'cat' => 'marketing',   'color' => '#D97706', 'desc' => 'Multi-channel social campaign with creative strategy, posting schedule, and KPIs.', 'uses' => 521, 'sections' => 5, 'pro' => false],
            ['id' => 'tl6', 'name' => 'Business Consulting Retainer', 'cat' => 'consulting',  'color' => '#DC2626', 'desc' => 'Monthly advisory retainer with deliverables and meeting cadence.', 'uses' => 389, 'sections' => 4, 'pro' => true],
            ['id' => 'tl7', 'name' => 'E-commerce Development',       'cat' => 'development', 'color' => '#0D0F14', 'desc' => 'Full e-commerce build proposal covering platform, integrations, and launch.', 'uses' => 445, 'sections' => 7, 'pro' => true],
            ['id' => 'tl8', 'name' => 'PR & Media Relations',         'cat' => 'marketing',   'color' => '#DB2777', 'desc' => 'PR campaign and ongoing media relations proposal for brand awareness.', 'uses' => 298, 'sections' => 4, 'pro' => false],
            ['id' => 'tl9', 'name' => 'Logo & Visual Identity',       'cat' => 'design',      'color' => '#1A56F0', 'desc' => 'Logo design and full visual identity system including brand guidelines.', 'uses' => 867, 'sections' => 5, 'pro' => false],
        ];
    }

    public function index()
    {
        $myTemplates     = Template::where('user_id', Auth::id())->latest()->get();
        $libraryTemplates = $this->libraryTemplates();

        return view('client-dashboard.templates', compact('myTemplates', 'libraryTemplates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:200',
            'category'    => 'required|in:design,development,marketing,consulting',
            'description' => 'nullable|string|max:500',
        ]);

        $template = Template::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'category'    => $request->category,
            'description' => $request->description,
            'color'       => '#1A56F0',
        ]);

        return redirect()->route('editor', ['template' => $template->id])
            ->with('success', 'Template created — now editing.');
    }

    public function duplicate(Template $template)
    {
        $copy = $template->replicate();
        $copy->name       = $template->name . ' (Copy)';
        $copy->user_id    = Auth::id();
        $copy->save();

        return redirect()->route('templates')->with('success', 'Template duplicated.');
    }

    public function duplicateLibrary(Request $request)
    {
        $lib = collect($this->libraryTemplates())->firstWhere('id', $request->template_id);
        if (!$lib) return back()->with('error', 'Template not found.');

        Template::create([
            'user_id'     => Auth::id(),
            'name'        => $lib['name'],
            'category'    => $lib['cat'],
            'description' => $lib['desc'],
            'color'       => $lib['color'],
        ]);

        return redirect()->route('templates')->with('success', '"' . $lib['name'] . '" added to My Templates.');
    }

    public function destroy(Template $template)
    {
        $this->authorize('delete', $template);
        $template->delete();
        return redirect()->route('templates')->with('success', 'Template deleted.');
    }
}
