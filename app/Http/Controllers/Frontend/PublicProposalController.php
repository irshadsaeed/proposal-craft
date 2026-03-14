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
    // ── Show public proposal ──────────────────────────────────
    public function show(string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->with(['sender', 'comments' => fn($q) => $q->latest()])
            ->firstOrFail();

        // Abort if expired
        if ($proposal->expires_at && $proposal->expires_at->isPast() && $proposal->status === 'pending') {
            $proposal->update(['status' => 'expired']);
        }

        // Record first view
        if ($proposal->status === 'pending') {
            $proposal->update([
                'status'         => 'viewed',
                'first_viewed_at' => now(),
            ]);
        }

        // Always log every view
        ProposalView::create([
            'proposal_id' => $proposal->id,
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'viewed_at'   => now(),
        ]);

        // Increment view count
        $proposal->increment('views_count');

        return view('frontend.proposals.show', compact('proposal'));
    }

    // ── Track scroll / section events (JS beacon) ────────────
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
            'proposal_id'  => $proposal->id,
            'event_type'   => $request->event,
            'section_id'   => $request->section_id,
            'value'        => $request->value,
            'meta'         => $request->meta,
            'ip'           => $request->ip(),
            'tracked_at'   => now(),
        ]);

        return response()->noContent(); // 204
    }

    // ── Accept proposal ───────────────────────────────────────
    public function accept(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->whereIn('status', ['pending', 'viewed'])
            ->firstOrFail();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'signature' => 'required|string', // base64 canvas PNG
        ]);

        // Store signature image to disk
        $signatureData = $request->signature;
        $signaturePath = null;

        if (Str::startsWith($signatureData, 'data:image')) {
            $image        = base64_decode(Str::after($signatureData, 'base64,'));
            $signaturePath = 'signatures/' . $token . '_' . time() . '.png';
            \Storage::disk('public')->put($signaturePath, $image);
        }

        $proposal->update([
            'status'        => 'accepted',
            'accepted_by'   => $request->name,
            'accepted_email' => $request->email,
            'accepted_at'   => now(),
            'accepted_ip'   => $request->ip(),
            'signature_path' => $signaturePath,
        ]);

        // Notify the proposal sender
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

    // ── Decline proposal ──────────────────────────────────────
    public function decline(Request $request, string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->whereIn('status', ['pending', 'viewed'])
            ->firstOrFail();

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $proposal->update([
            'status'         => 'declined',
            'declined_at'    => now(),
            'decline_reason' => $request->reason,
        ]);

        // Notify sender
        try {
            $proposal->sender->notify(new \App\Notifications\ProposalDeclined($proposal));
        } catch (\Exception $e) {
            \Log::warning('ProposalDeclined notification failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    // ── Comment on proposal ───────────────────────────────────
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

        // Notify sender
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

    // ── Accepted confirmation page ────────────────────────────
    public function accepted(string $token)
    {
        $proposal = Proposal::where('token', $token)
            ->where('status', 'accepted')
            ->with('sender')
            ->firstOrFail();

        return view('frontend.proposals.accepted', compact('proposal'));
    }

    // ── Generate PDF ──────────────────────────────────────────
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
                'defaultPaperSize'     => 'a4',
                'margin_top'           => 0,
                'margin_bottom'        => 0,
                'margin_left'          => 0,
                'margin_right'         => 0,
            ]);

        return $pdf->stream(Str::slug($proposal->title) . '.pdf');
    }
}
