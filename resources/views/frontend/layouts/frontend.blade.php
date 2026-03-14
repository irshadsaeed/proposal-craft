<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- SEO --}}
  <title>@yield('title', 'ProposalCraft — Beautiful Proposals That Close Deals Faster')</title>
  <meta name="description" content="@yield('description', 'ProposalCraft helps freelancers, agencies, and businesses create stunning, interactive proposals in minutes.')" />
  <meta name="keywords"    content="@yield('keywords', 'proposal builder, online proposal software, client proposals, e-signature')" />
  <meta name="author"      content="ProposalCraft" />
  <meta name="robots"      content="@yield('robots', 'index, follow')" />

  {{-- Open Graph --}}
  <meta property="og:site_name"    content="ProposalCraft" />
  <meta property="og:type"         content="@yield('og_type', 'website')" />
  <meta property="og:title"        content="@yield('og_title', 'ProposalCraft — Beautiful Proposals That Close Deals Faster')" />
  <meta property="og:description"  content="@yield('og_description', 'Create stunning, interactive proposals in minutes. Used by 25,000+ professionals worldwide.')" />
  <meta property="og:url"          content="{{ url()->current() }}" />
  <meta property="og:image"        content="@yield('og_image', asset('images/og-cover.jpg'))" />
  <meta property="og:image:width"  content="1200" />
  <meta property="og:image:height" content="630" />

  {{-- Twitter Card — @@ outputs a literal @ in Blade --}}
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:site"        content="@@proposalcraft" />
  <meta name="twitter:title"       content="@yield('og_title', 'ProposalCraft — Beautiful Proposals That Close Deals Faster')" />
  <meta name="twitter:description" content="@yield('og_description', 'Create stunning, interactive proposals in minutes.')" />
  <meta name="twitter:image"       content="@yield('og_image', asset('images/og-cover.jpg'))" />

  {{-- Canonical --}}
  <link rel="canonical" href="{{ url()->current() }}" />

  {{-- Preconnect --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

  {{-- Favicons --}}
  <link rel="icon"             type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="icon"             type="image/png"    href="{{ asset('images/favicon-32.png') }}" sizes="32x32" />
  <link rel="apple-touch-icon"                     href="{{ asset('images/apple-touch-icon.png') }}" />
  <link rel="manifest"                             href="{{ asset('site.webmanifest') }}" />
  <meta name="theme-color" content="#1847F0" />

  {{-- Bootstrap 5 --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

  {{-- Custom Styles --}}
  <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/contact.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/cta.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/faq.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/features.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/footer.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/hero.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/how-it-works.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/header.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/pricingg.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/pricing.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/problem-solution.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/testimonials.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/footer.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/blog-index.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/blog-post.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/privacy-terms-cookies.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/templates.css') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/css/about-us.css') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/css/security.css') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/css/social-proof.css') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/css/comparison.css') }}" />




  @stack('structured_data')

  @stack('styles')
</head>
<body>

  <a href="#main-content" class="skip-link">Skip to main content</a>

  @include('frontend.partials.header')
  @include('frontend.partials.header-mobile')

  <main id="main-content" tabindex="-1">
    @yield('content')
  </main>

  @include('frontend.partials.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="{{ asset('frontend/js/main.js') }}" defer></script>

  @stack('scripts')
</body>
</html>