<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::where('user_id', Auth::id());

        if ($request->filter && $request->filter !== 'all')
            $query->where('status', $request->filter);

        if ($request->search)
            $query->where(fn($q) => $q
                ->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('client', 'like', '%' . $request->search . '%'));

        $proposals = $request->sort === 'amount'
            ? $query->orderByDesc('amount')->paginate(10)->withQueryString()
            : $query->latest()->paginate(10)->withQueryString();

        return view('client-dashboard.proposals', compact('proposals'));
    }

    public function newProposal()
    {
        return view('client-dashboard.new-proposal');
    }

    public function proposalPreview(Request $request)
    {
        $proposal = $request->id
            ? Proposal::where('user_id', auth()->id())->findOrFail($request->id)
            : null;
        return view('client-dashboard.proposal-preview', compact('proposal'));
    }

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        return redirect()->route('proposals')->with('success', 'Proposal sent to ' . $request->email);
    }


    public function destroy(Proposal $proposal)
    {
        abort_if($proposal->user_id !== Auth::id(), 403);
        $proposal->delete();
        return redirect()->route('proposals')->with('success', 'Proposal deleted.');
    }
}
