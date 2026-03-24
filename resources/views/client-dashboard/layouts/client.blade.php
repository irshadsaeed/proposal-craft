<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8" />
  <meta name="robots"      content="noindex, nofollow" />
  <meta name="description" content="ProposalCraft Client Panel" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — ProposalCraft Client</title>
   <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700;800;900&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <link rel="stylesheet" href="{{ asset('client-dashboard/css/app.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/billing.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/dashboard.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/new-proposal.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/proposals.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/settings.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/templates.css') }}" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/template-editor.css') }}">
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/tracking.css') }}" />

  <link rel="stylesheet" href="{{ asset('client-dashboard/css/client-dashboard-whole-pagination.css') }}" />

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