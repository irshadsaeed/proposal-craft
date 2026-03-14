<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — ProposalCraft</title>
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/app.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/billing.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/dashboard.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/new-proposal.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/proposals.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/settings.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/templates.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/tracking.css') }}" />

  @stack('styles')
</head>
<body>
<div class="app-layout {{ $bodyClass ?? '' }}">

  @include('client-dashboard.partials.sidebar')
  <div class="sidebar-overlay"></div>

  <main class="app-main">
    @include('client-dashboard.partials.topbar')
    <div class="app-content">
      @yield('content')
    </div>
  </main>

</div>

<div class="toast-container"></div>
<script src="{{ asset('client-dashboard/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>