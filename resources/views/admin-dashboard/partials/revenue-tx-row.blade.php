{{-- admin-dashboard/partials/revenue-tx-row.blade.php --}}
<tr class="rvn-tx-row">
    <td class="rvn-td">
        <div class="rvn-user-cell">
            <div class="rvn-avatar" aria-hidden="true">
                {{ strtoupper(substr($tx->user->name ?? $tx->user_name ?? 'U', 0, 1)) }}
            </div>
            <div class="rvn-user-info">
                <div class="rvn-user-name">{{ $tx->user->name ?? $tx->user_name ?? '—' }}</div>
                <div class="rvn-user-email">{{ $tx->user->email ?? $tx->user_email ?? '—' }}</div>
            </div>
        </div>
    </td>
    <td class="rvn-td">
        <span class="plan-badge plan-badge--{{ $tx->plan_slug ?? 'free' }}">
            {{ ucfirst($tx->plan_slug ?? 'Free') }}
        </span>
    </td>
    <td class="rvn-td rvn-td--muted">{{ ucfirst($tx->billing_period ?? 'Monthly') }}</td>
    <td class="rvn-td rvn-td--right rvn-td--amount {{ ($tx->status ?? '') === 'refunded' ? 'rvn-td--refund' : '' }}">
        @if(($tx->status ?? '') === 'refunded')−@endif${{ number_format(($tx->amount ?? 0) / 100, 2) }}
    </td>
    <td class="rvn-td rvn-td--muted">
        @if($tx->paid_at ?? $tx->created_at ?? false)
        <time datetime="{{ ($tx->paid_at ?? $tx->created_at)->toDateString() }}">
            {{ ($tx->paid_at ?? $tx->created_at)->format('M d, Y') }}
        </time>
        @else
        <span aria-label="No date">—</span>
        @endif
    </td>
    <td class="rvn-td">
        <code class="rvn-stripe-id" title="{{ $tx->stripe_payment_intent ?? '—' }}">
            {{ $tx->stripe_payment_intent ? '…' . substr($tx->stripe_payment_intent, -12) : '—' }}
        </code>
    </td>
    <td class="rvn-td">
        @php
            $st = $tx->status ?? 'unknown';
            $color = match($st) {
                'succeeded' => 'green',
                'refunded'  => 'orange',
                'failed'    => 'red',
                default     => 'neutral',
            };
        @endphp
        <span class="rvn-status-badge rvn-status-badge--{{ $color }}">
            {{ ucfirst($st) }}
        </span>
    </td>
</tr>