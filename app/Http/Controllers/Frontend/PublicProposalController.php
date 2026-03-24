<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\ProposalView;
use App\Models\ProposalTrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicProposalController extends Controller
{
    /* ════════════════════════════════════════════════════════════
       SHOW — public proposal page
       Records view, updates status, logs ProposalView
    ════════════════════════════════════════════════════════════ */
    public function show(string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->with(['sender', 'sections' => fn($q) => $q->orderBy('order')])
            ->firstOrFail();

        // Mark expired
        if ($proposal->expires_at && $proposal->expires_at->isPast() && $proposal->status === 'pending') {
            $proposal->update(['status' => 'expired']);
        }

        // First view — update status + timestamp
        if ($proposal->status === 'sent') {
            $proposal->update([
                'status'          => 'viewed',
                'first_viewed_at' => now(),
            ]);
        }

        // Always log the view
        ProposalView::create([
            'proposal_id' => $proposal->id,
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'viewed_at'   => now(),
        ]);

        // Increment views (column is 'views' not 'views_count')
        $proposal->increment('views');

        // Update last_seen
        $proposal->update(['last_seen' => now()]);

        // Log tracking event
        ProposalTrackingEvent::create([
            'proposal_id' => $proposal->id,
            'event_type'  => 'view',
            'ip'          => request()->ip(),
            'meta'        => json_encode([
                'user_agent' => request()->userAgent(),
                'referer'    => request()->headers->get('referer'),
            ]),
            'tracked_at'  => now(),
        ]);

        return view('frontend.proposals.show', compact('proposal'));
    }

    /* ════════════════════════════════════════════════════════════
       PING — JS calls this every 30s to track time spent
       Also called by sendBeacon on tab close
    ════════════════════════════════════════════════════════════ */
    public function ping(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)->firstOrFail();

        $validated = $request->validate([
            'seconds' => 'required|integer|min:1|max:120',
            'section' => 'nullable|string|max:100',
        ]);

        ProposalTrackingEvent::create([
            'proposal_id' => $proposal->id,
            'event_type'  => 'ping',
            'value'       => $validated['seconds'],
            'ip'          => $request->ip(),
            'meta'        => json_encode([
                'section'    => $validated['section'] ?? null,
                'user_agent' => $request->userAgent(),
            ]),
            'tracked_at'  => now(),
        ]);

        // Running total of seconds spent on this proposal
        $totalSeconds = ProposalTrackingEvent::where('proposal_id', $proposal->id)
            ->where('event_type', 'ping')
            ->sum('value');

        $proposal->update([
            'avg_time_open' => $totalSeconds,
            'last_seen'     => now(),
        ]);

        return response()->noContent(); // 204
    }

    /* ════════════════════════════════════════════════════════════
       TRACK — section views, scroll depth, etc.
    ════════════════════════════════════════════════════════════ */
    public function track(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)->firstOrFail();

        $request->validate([
            'event'      => 'required|in:section_view,scroll_depth,time_on_page,link_click',
            'section_id' => 'nullable|integer',
            'value'      => 'nullable|numeric',
            'meta'       => 'nullable|string|max:255',
        ]);

        ProposalTrackingEvent::create([
            'proposal_id' => $proposal->id,
            'event_type'  => $request->event,
            'section_id'  => $request->section_id,
            'value'       => $request->value,
            'meta'        => $request->meta,
            'ip'          => $request->ip(),
            'tracked_at'  => now(),
        ]);

        return response()->noContent();
    }

    /* ════════════════════════════════════════════════════════════
       ACCEPT — client signs the proposal
    ════════════════════════════════════════════════════════════ */
    public function accept(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->whereIn('status', ['sent', 'viewed'])
            ->firstOrFail();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'signature' => 'nullable|string', // base64 canvas PNG
        ]);

        // Save signature image
        $signaturePath = null;
        if ($request->filled('signature') && Str::startsWith($request->signature, 'data:image')) {
            $image         = base64_decode(Str::after($request->signature, 'base64,'));
            $signaturePath = 'signatures/' . $token . '_' . time() . '.png';
            \Storage::disk('public')->put($signaturePath, $image);
        }

        $proposal->update([
            'status'          => 'accepted',
            'accepted_by'     => $request->name,
            'accepted_email'  => $request->email,
            'accepted_at'     => now(),
            'accepted_ip'     => $request->ip(),
            'signature_path'  => $signaturePath,
        ]);

        // Log tracking event
        ProposalTrackingEvent::create([
            'proposal_id' => $proposal->id,
            'event_type'  => 'accept',
            'ip'          => $request->ip(),
            'meta'        => json_encode([
                'name'  => $request->name,
                'email' => $request->email,
            ]),
            'tracked_at'  => now(),
        ]);

        // Notify sender
        try {
            $proposal->sender->notify(new \App\Notifications\ProposalAccepted($proposal));
        } catch (\Exception $e) {
            \Log::warning('ProposalAccepted notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'  => true,
            'redirect' => route('proposals.accepted', $token),
        ]);
    }

    /* ════════════════════════════════════════════════════════════
       DECLINE
    ════════════════════════════════════════════════════════════ */
    public function decline(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->whereIn('status', ['sent', 'viewed'])
            ->firstOrFail();

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $proposal->update([
            'status'         => 'declined',
            'declined_at'    => now(),
            'decline_reason' => $request->reason,
        ]);

        ProposalTrackingEvent::create([
            'proposal_id' => $proposal->id,
            'event_type'  => 'decline',
            'ip'          => $request->ip(),
            'meta'        => json_encode(['reason' => $request->reason ?? '']),
            'tracked_at'  => now(),
        ]);

        try {
            $proposal->sender->notify(new \App\Notifications\ProposalDeclined($proposal));
        } catch (\Exception $e) {
            \Log::warning('ProposalDeclined notification failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /* ════════════════════════════════════════════════════════════
       COMMENT
    ════════════════════════════════════════════════════════════ */
    public function comment(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
        ]);

        $comment = ProposalComment::create([
            'proposal_id' => $proposal->id,
            'author_name' => $request->name,
            'body'        => $request->body,
            'is_sender'   => false,
            'ip'          => $request->ip(),
        ]);

        try {
            $proposal->sender->notify(new \App\Notifications\NewProposalComment($proposal, $comment));
        } catch (\Exception $e) {
            \Log::warning('NewProposalComment notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'author_name' => $comment->author_name,
                'body'        => $comment->body,
                'created_at'  => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    /* ════════════════════════════════════════════════════════════
       ACCEPTED — confirmation page after signing
    ════════════════════════════════════════════════════════════ */
    public function accepted(string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->where('status', 'accepted')
            ->with('sender')
            ->firstOrFail();

        return view('frontend.proposals.accepted', compact('proposal'));
    }

    /* ════════════════════════════════════════════════════════════
       PDF — generate downloadable PDF
    ════════════════════════════════════════════════════════════ */
    public function pdf(string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->with(['sender', 'sections'])
            ->firstOrFail();

        $pdf = Pdf::loadView('frontend.proposals.pdf', compact('proposal'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 150,
                'margin_top'           => 0,
                'margin_bottom'        => 0,
                'margin_left'          => 0,
                'margin_right'         => 0,
            ]);

        return $pdf->stream(Str::slug($proposal->title) . '.pdf');
    }
}