<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $all = Proposal::where('user_id', Auth::id())->get();

        $subscription = [
            'plan'       => 'Pro Plan',
            'price'      => 29,
            'renews_at'  => 'April 1, 2025',
            'features'   => ['Unlimited Proposals', 'Custom Branding', 'Full Tracking', 'Priority Support'],
        ];

        $usage = [
            ['label' => 'Proposals Sent',  'used' => $all->whereIn('status',['sent','viewed','accepted','declined'])->count(), 'total' => 'Unlimited', 'pct' => 20],
            ['label' => 'Templates Used',  'used' => 3,  'total' => 10,           'pct' => 30],
            ['label' => 'Tracking Events', 'used' => $all->sum('views'), 'total' => 'Unlimited', 'pct' => 15],
            ['label' => 'Team Seats',      'used' => 1,  'total' => 1,            'pct' => 100],
        ];

        $invoices = [
            ['date' => 'Mar 1, 2025', 'id' => 'INV-2025-012', 'amount' => '$29.00'],
            ['date' => 'Feb 1, 2025', 'id' => 'INV-2025-008', 'amount' => '$29.00'],
            ['date' => 'Jan 1, 2025', 'id' => 'INV-2025-004', 'amount' => '$29.00'],
        ];

        $paymentMethod = [
            'brand'   => 'Visa',
            'last4'   => '4242',
            'expires' => '12/27',
        ];

        return view('client-dashboard.billing', compact('subscription', 'usage', 'invoices', 'paymentMethod'));
    }

    public function invoice($id)
    {
        // TODO: Generate and return PDF invoice
        return redirect()->route('billing')->with('success', "Invoice {$id} downloaded.");
    }

    public function addCard(Request $request)
    {
        $request->validate([
            'card_number'  => 'required',
            'expiry'       => 'required',
            'cvc'          => 'required',
            'name_on_card' => 'required|string|max:100',
        ]);

        // TODO: Stripe integration — tokenize and attach card
        return redirect()->route('billing')->with('success', 'Payment method updated.');
    }

    public function removeCard()
    {
        // TODO: Stripe — detach payment method
        return redirect()->route('billing')->with('success', 'Card removed.');
    }

    public function changePlan(Request $request)
    {
        $request->validate(['plan' => 'required|in:free,pro,team', 'cycle' => 'required|in:monthly,yearly']);
        // TODO: Stripe subscription update
        return redirect()->route('billing')->with('success', 'Plan changed to ' . ucfirst($request->plan) . '.');
    }

    public function cancel(Request $request)
    {
        // TODO: Stripe — cancel at period end
        return redirect()->route('billing')->with('success', 'Subscription cancelled — access until end of billing period.');
    }
}