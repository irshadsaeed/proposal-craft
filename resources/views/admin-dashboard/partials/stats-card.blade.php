{{--
 STATS CARD PARTIAL · ProposalCraft Admin · Supreme Edition
 ─────────────────────────────────────────────────────────
 Usage:
   @include('admin-dashboard.partials.stats-card', [
       'label'   => 'Total Users',
       'value'   => '2,841',
       'change'  => '12.4%',        // optional
       'up'      => true,           // true=green, false=red
       'icon'    => 'users',        // users|revenue|proposals|plans
       'color'   => 'blue',         // blue|green|orange|rose|gold|violet
       'context' => 'vs last month',// optional
       'index'   => 0,              // stagger delay (0,1,2,3)
   ])
--}}

@php
    $icons = [
        'users' => '<circle cx="6" cy="5" r="2.5" stroke="currentColor" stroke-width="1.35"/>
            <path d="M1 13c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
            <path d="M11 3.5c1.1.4 1.9 1.5 1.9 2.7S12.1 8.5 11 8.9M12.8 13c0-1.4-.6-2.7-1.6-3.5"
                  stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>',

        'revenue' => '<path d="M2 10.5l3-3.5 2.5 2L11 5l2 1.5" stroke="currentColor" stroke-width="1.35"
                  stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M1.5 14h13" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
            <circle cx="13" cy="6.5" r=".9" fill="currentColor"/>',

        'proposals' => '<rect x="2.5" y="1.5" width="11" height="13" rx="1.75" stroke="currentColor" stroke-width="1.35"/>
            <path d="M5 5.5h6M5 8h6M5 10.5h3.5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>',

        'plans' => '<rect x="1.5" y="3.5" width="13" height="9" rx="1.75" stroke="currentColor" stroke-width="1.35"/>
            <path d="M5 8h6M8 5.5v5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>',
    ];

    $iconSvg  = $icons[$icon ?? 'users'] ?? $icons['users'];
    $colorCls = 'stats-card--' . ($color ?? 'blue');
    $isUp     = $up ?? true;
    $ctx      = $context ?? 'vs last month';
    $delay    = (int)($index ?? 0) * 80;
@endphp

<div class="stats-card {{ $colorCls }}"
     style="--stagger: {{ $delay }}ms"
     role="article"
     aria-label="{{ $label }}: {{ $value }}">

    <div class="stats-glow" aria-hidden="true"></div>

    <div class="stats-card-header">
        <span class="stats-card-label">{{ $label }}</span>
        <span class="stats-card-icon" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                {!! $iconSvg !!}
            </svg>
        </span>
    </div>

    <div class="stats-card-value" data-counter="{{ $value }}">{{ $value }}</div>

    @if(isset($change))
    <div class="stats-card-footer">
        <span class="stats-card-change {{ $isUp ? 'is-up' : 'is-down' }}">
            <svg width="9" height="9" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                @if($isUp)
                    <path d="M5 8V2M2 5l3-3 3 3" stroke="currentColor" stroke-width="1.7"
                          stroke-linecap="round" stroke-linejoin="round"/>
                @else
                    <path d="M5 2v6M2 5l3 3 3-3" stroke="currentColor" stroke-width="1.7"
                          stroke-linecap="round" stroke-linejoin="round"/>
                @endif
            </svg>
            {{ $change }}
        </span>
        <span class="stats-card-context">{{ $ctx }}</span>
    </div>
    @endif

</div>