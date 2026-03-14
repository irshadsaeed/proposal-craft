<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" data-theme="admin" class="no-js">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport"    content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token"  content="{{ csrf_token() }}" />
  <meta name="robots"      content="noindex, nofollow" />
  <meta name="description" content="ProposalCraft Admin Panel" />
  <title>@yield('title', 'Dashboard') · ProposalCraft Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700;800;900&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet" />


  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/dashboard.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/admin-users-detail.css') }}"/>
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/blog-add.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/blog-detail.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/client-users-view.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/plans-detail.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/plans-view.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/plans-edit.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/plans-create.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/revenue.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-dashboard/css/settings.css') }}" />

  @stack('styles')

  <script>document.documentElement.classList.replace('no-js','js');</script>
</head>
<body class="admin-body">

  <a href="#adminContent" class="skip-to-content">Skip to content</a>

  @include('admin-dashboard.partials.sidebar')

  <div class="admin-main" id="adminMain">
    @include('admin-dashboard.partials.topbar')
    <main class="admin-content" id="adminContent" role="main"
          aria-label="@yield('page-title', 'Dashboard')">
      @yield('content')
    </main>
  </div>

  <div class="toast-stack" id="toastStack"
       role="status" aria-live="polite" aria-atomic="false" aria-relevant="additions"></div>

  @if(session('success'))
    <div data-flash="success" data-flash-msg="{{ session('success') }}" hidden></div>
  @endif
  @if(session('error'))
    <div data-flash="error"   data-flash-msg="{{ session('error') }}"   hidden></div>
  @endif
  @if(session('warning'))
    <div data-flash="warning" data-flash-msg="{{ session('warning') }}" hidden></div>
  @endif

  <script src="{{ asset('admin-dashboard/js/dashboard.js') }}" defer></script>
  @stack('scripts')

</body>
</html>