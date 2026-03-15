<?php
/* ============================================================
   BillingController.php
   ============================================================ */

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /* ── Stub invoice data (swap with DB/Stripe later) ──────── */
    private function allInvoices(): array
    {
        return [
            ['date' => 'Mar 1, 2025',  'id' => 'INV-2025-012', 'amount' => '$29.00', 'status' => 'paid'],
            ['date' => 'Feb 1, 2025',  'id' => 'INV-2025-008', 'amount' => '$29.00', 'status' => 'paid'],
            ['date' => 'Jan 1, 2025',  'id' => 'INV-2025-004', 'amount' => '$29.00', 'status' => 'paid'],
            ['date' => 'Dec 1, 2024',  'id' => 'INV-2024-048', 'amount' => '$29.00', 'status' => 'paid'],
            ['date' => 'Nov 1, 2024',  'id' => 'INV-2024-044', 'amount' => '$29.00', 'status' => 'paid'],
        ];
    }

    /* ── INDEX ───────────────────────────────────────────────── */
    public function index()
    {
        $all = Proposal::where('user_id', Auth::id())->get();

        $subscription = [
            'plan'      => 'Pro Plan',
            'price'     => 29,
            'renews_at' => 'April 1, 2025',
            'features'  => ['Unlimited Proposals', 'Custom Branding', 'Full Tracking', 'Priority Support'],
        ];

        $usage = [
            ['label' => 'Proposals Sent',  'used' => $all->whereIn('status', ['sent','viewed','accepted','declined'])->count(), 'total' => 'Unlimited', 'pct' => 20],
            ['label' => 'Templates Used',  'used' => 3,                   'total' => 10,            'pct' => 30],
            ['label' => 'Tracking Events', 'used' => $all->sum('views'),  'total' => 'Unlimited',   'pct' => 15],
            ['label' => 'Team Seats',      'used' => 1,                   'total' => 1,             'pct' => 100],
        ];

        $invoices      = $this->allInvoices();
        $paymentMethod = ['brand' => 'Visa', 'last4' => '4242', 'expires' => '12/27'];

        return view('client-dashboard.billing', compact('subscription', 'usage', 'invoices', 'paymentMethod'));
    }

    /* ── AJAX SEARCH — GET /dashboard/billing/search ────────── */
    public function search(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $q = strtolower(trim($request->input('q', '')));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect($this->allInvoices())
            ->filter(fn($inv) =>
                str_contains(strtolower($inv['id']),     $q) ||
                str_contains(strtolower($inv['date']),   $q) ||
                str_contains(strtolower($inv['amount']), $q) ||
                str_contains(strtolower($inv['status']), $q)
            )
            ->values()
            ->map(fn($inv) => [
                'type'     => 'invoice',
                'icon'     => 'invoice',
                'id'       => $inv['id'],
                'title'    => $inv['id'],
                'subtitle' => $inv['date'],
                'meta'     => $inv['amount'],
                'badge'    => $inv['status'],
                'date'     => $inv['date'],
                'initials' => 'IN',
                'url'      => route('billing.invoice', $inv['id']),
            ]);

        return response()->json(['results' => $results]);
    }

    /* ── INVOICE DOWNLOAD ────────────────────────────────────── */
    public function invoice($id)
    {
        // TODO: Generate and return PDF invoice via Stripe or custom PDF
        return redirect()->route('billing')->with('success', "Invoice {$id} downloaded.");
    }

    /* ── ADD CARD ────────────────────────────────────────────── */
    public function addCard(Request $request)
    {
        $request->validate([
            'card_number'  => 'required',
            'expiry'       => 'required',
            'cvc'          => 'required',
            'name_on_card' => 'required|string|max:100',
        ]);
        // TODO: Stripe — tokenize and attach
        return redirect()->route('billing')->with('success', 'Payment method updated.');
    }

    /* ── REMOVE CARD ─────────────────────────────────────────── */
    public function removeCard()
    {
        // TODO: Stripe — detach payment method
        return redirect()->route('billing')->with('success', 'Card removed.');
    }

    /* ── CHANGE PLAN ─────────────────────────────────────────── */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan'  => 'required|in:free,pro,team',
            'cycle' => 'required|in:monthly,yearly',
        ]);
        // TODO: Stripe subscription update
        return redirect()->route('billing')
            ->with('success', 'Plan changed to ' . ucfirst($request->plan) . '.');
    }

    /* ── CANCEL ──────────────────────────────────────────────── */
    public function cancel()
    {
        // TODO: Stripe — cancel at period end
        return redirect()->route('billing')
            ->with('success', 'Subscription cancelled — access until end of billing period.');
    }
}