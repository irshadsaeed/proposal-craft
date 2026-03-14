@extends('frontend.layouts.frontend')

@section('title', 'Blog — Proposal Tips, Templates & Business Growth | ProposalCraft')
@section('description', 'Learn how to write winning proposals, close more deals, and grow your freelance or agency business. Practical guides, templates, and expert tips from the ProposalCraft team.')
@section('keywords', 'proposal tips, proposal templates, how to write a proposal, freelance proposals, agency proposals, close deals faster')
@section('og_title', 'ProposalCraft Blog — Win More Deals With Better Proposals')
@section('og_description', 'Practical guides and templates to help freelancers, agencies, and businesses write proposals that close.')

@section('content')

{{-- ── HERO ──────────────────────────────────────────────────── --}}
<section class="bl-hero">
  <div class="bl-hero-bg" aria-hidden="true">
    <div class="bl-hero-orb"></div>
  </div>
  <div class="container">
    <div class="bl-hero-inner text-center">
      <span class="section-eyebrow">ProposalCraft Blog</span>
      <h1 class="display-heading">Proposals that <em>close</em></h1>
      <p class="lead mx-auto">Practical guides, templates, and expert advice for freelancers, agencies, and businesses who want to win more deals.</p>

      {{-- Search --}}
      <form action="{{ route('blog.search') }}" method="GET" class="bl-search-form" role="search" id="blSearchForm">
        <svg class="bl-search-icon" width="18" height="18" viewBox="0 0 18 18" fill="none">
          <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6" />
          <path d="M13 13l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
        </svg>
        <input
          type="search"
          name="q"
          id="blSearch"
          class="bl-search-input"
          placeholder="Search articles, templates, tips…"
          value="{{ request('q') }}"
          autocomplete="off" />
        <button type="submit" class="bl-search-btn">Search</button>

        {{-- Suggestions dropdown --}}
        <div class="bl-suggestions" id="blSuggestions" hidden>
          <div class="bl-suggestions-list" id="blSuggestionsList"></div>
          <div class="bl-suggestions-footer" id="blSuggestionsFooter" hidden>
            <span id="blSuggestionsQuery"></span>
            <button type="submit" class="bl-suggestions-all">See all results →</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

{{-- ── CATEGORY TABS ─────────────────────────────────────────── --}}
<section class="bl-cats section-padding" style="padding-bottom:0">
  <div class="container">
    <nav class="bl-cat-nav" aria-label="Post categories">
      <a href="{{ route('blog.index') }}" class="bl-cat-tab {{ !request('category') ? 'active' : '' }}">All</a>
      @foreach($categories as $cat)
      <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
        class="bl-cat-tab {{ request('category') === $cat->slug ? 'active' : '' }}">
        {{ $cat->name }}
        <span class="bl-cat-count">{{ $cat->posts_count }}</span>
      </a>
      @endforeach
    </nav>
  </div>
</section>

{{-- ── FEATURED POST ─────────────────────────────────────────── --}}
@if($featured)
<section class="bl-featured section-padding">
  <div class="container">
    <a href="{{ route('blog.show', $featured->slug) }}" class="bl-featured-card reveal">
      <div class="bl-featured-img-wrap">
        @if($featured->cover_image)
        <img src="{{ $featured->cover_image }}" alt="{{ $featured->title }}" class="bl-featured-img" loading="eager" />
        @else
        <div class="bl-featured-img bl-featured-img-placeholder"></div>
        @endif
        <span class="bl-featured-badge">✦ Featured</span>
      </div>
      <div class="bl-featured-body">
        <div class="bl-featured-meta">
          <span class="bl-cat-pill">{{ $featured->category->name }}</span>
          <span class="bl-dot">·</span>
          <span class="bl-read-time">{{ $featured->read_time }} min read</span>
          <span class="bl-dot">·</span>
          <time datetime="{{ $featured->published_at->toISOString() }}">{{ $featured->published_at->format('M j, Y') }}</time>
        </div>
        <h2 class="bl-featured-title">{{ $featured->title }}</h2>
        <p class="bl-featured-excerpt">{{ $featured->excerpt }}</p>
        <div class="bl-featured-author">
          @if($featured->author->avatar)
          <img src="{{ $featured->author->avatar }}" alt="{{ $featured->author->name }}" class="bl-author-avatar" />
          @else
          <div class="bl-author-avatar bl-author-avatar-init">{{ strtoupper(substr($featured->author->name, 0, 1)) }}</div>
          @endif
          <span class="bl-author-name">{{ $featured->author->name }}</span>
          <span class="bl-read-cta">Read article →</span>
        </div>
      </div>
    </a>
  </div>
</section>
@endif

{{-- ── POST GRID ─────────────────────────────────────────────── --}}
<section class="bl-grid-section section-padding" style="padding-top:0">
  <div class="container">

    @if($posts->count())
    <div class="bl-grid">
      @foreach($posts as $post)
      <article class="bl-card reveal reveal-delay-{{ ($loop->index % 3) + 1 }}">
        <a href="{{ route('blog.show', $post->slug) }}" class="bl-card-img-link" tabindex="-1" aria-hidden="true">
          @if($post->cover_image)
          <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="bl-card-img" loading="lazy" />
          @else
          <div class="bl-card-img bl-card-img-placeholder"></div>
          @endif
          @if($post->is_template)
          <span class="bl-card-template-badge">Template</span>
          @endif
        </a>
        <div class="bl-card-body">
          <div class="bl-card-meta">
            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="bl-cat-pill bl-cat-pill-sm">{{ $post->category->name }}</a>
            <span class="bl-dot">·</span>
            <span class="bl-read-time">{{ $post->read_time }} min</span>
          </div>
          <h3 class="bl-card-title">
            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
          </h3>
          <p class="bl-card-excerpt">{{ $post->excerpt }}</p>
          <div class="bl-card-footer">
            <div class="bl-card-author">
              @if($post->author->avatar)
              <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" class="bl-author-avatar bl-author-avatar-sm" />
              @else
              <div class="bl-author-avatar bl-author-avatar-sm bl-author-avatar-init">{{ strtoupper(substr($post->author->name, 0, 1)) }}</div>
              @endif
              <span>{{ $post->author->name }}</span>
            </div>
            <time datetime="{{ $post->published_at->toISOString() }}" class="bl-card-date">{{ $post->published_at->format('M j') }}</time>
          </div>
        </div>
      </article>
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($posts->hasPages())
    <div class="bl-pagination">
      {{ $posts->appends(request()->query())->links('vendor.pagination.blog') }}
    </div>
    @endif

    @else
    <div class="bl-empty">
      <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
        <rect x="8" y="10" width="40" height="36" rx="5" stroke="currentColor" stroke-width="1.5" />
        <path d="M18 22h20M18 29h14M18 36h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
      </svg>
      <h3>No posts found</h3>
      <p>Try adjusting your search or browse a different category.</p>
      <a href="{{ route('blog.index') }}" class="btn-outline">View all posts</a>
    </div>
    @endif

  </div>
</section>

{{-- ── NEWSLETTER CTA ────────────────────────────────────────── --}}
<section class="bl-newsletter section-padding">
  <div class="container">
    <div class="bl-newsletter-card reveal">
      <div class="bl-newsletter-content">
        <span class="section-eyebrow">Newsletter</span>
        <h2 class="section-heading">Get proposal tips in your inbox</h2>
        <p class="lead">Weekly tips, templates, and insights to help you write better proposals and close more deals. No spam, unsubscribe anytime.</p>
      </div>
      <form class="bl-newsletter-form" id="blNewsletterForm" novalidate>
        @csrf
        <div class="bl-nf-group">
          <input type="email" name="email" class="bl-nf-input" placeholder="your@email.com" autocomplete="email" required />
          <button type="submit" class="btn-primary">Subscribe Free</button>
        </div>
        <p class="bl-nf-note">Join 8,200+ subscribers. Sent every Tuesday.</p>
        <div class="bl-nf-success" id="blNfSuccess" aria-live="polite" hidden>
          🎉 You're subscribed! Check your inbox to confirm.
        </div>
      </form>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('frontend/js/blog-index.js') }}" defer></script>
@endpush