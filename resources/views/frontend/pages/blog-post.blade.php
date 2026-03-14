@extends('frontend.layouts.frontend')

@section('title', $post->seo_title ?? ($post->title . ' | ProposalCraft Blog'))
@section('description', $post->meta_description ?? $post->excerpt)
@section('keywords', $post->tags->pluck('name')->implode(', '))
@section('og_type', 'article')
@section('og_title', $post->title)
@section('og_description', $post->excerpt)
@section('og_image', $post->cover_image ?? asset('images/og-cover.jpg'))

@section('content')

{{-- ── ARTICLE HEADER ────────────────────────────────────────── --}}
<header class="bp-header">
  <div class="container">
    <div class="bp-header-inner">

      {{-- Breadcrumb --}}
      <nav class="bp-breadcrumb" aria-label="Breadcrumb">
        <ol class="bp-bc-list">
          <li><a href="{{ route('home') }}">Home</a></li>
          <li aria-hidden="true">›</li>
          <li><a href="{{ route('blog.index') }}">Blog</a></li>
          <li aria-hidden="true">›</li>
          <li><a href="{{ route('blog.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a></li>
          <li aria-hidden="true">›</li>
          <li aria-current="page">{{ Str::limit($post->title, 40) }}</li>
        </ol>
      </nav>

      {{-- Category --}}
      <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="bl-cat-pill bp-cat">{{ $post->category->name }}</a>

      {{-- Title --}}
      <h1 class="bp-title">{{ $post->title }}</h1>

      {{-- Meta --}}
      <div class="bp-meta">
        <div class="bp-author-info">
          @if($post->author->avatar)
            <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" class="bp-author-avatar" />
          @else
            <div class="bp-author-avatar bp-author-initials">{{ strtoupper(substr($post->author->name, 0, 1)) }}</div>
          @endif
          <div>
            <span class="bp-author-name">{{ $post->author->name }}</span>
            <span class="bp-author-role">{{ $post->author->title ?? 'ProposalCraft Team' }}</span>
          </div>
        </div>
        <div class="bp-meta-right">
          <time datetime="{{ $post->published_at->toISOString() }}" class="bp-date">{{ $post->published_at->format('F j, Y') }}</time>
          <span class="bl-dot">·</span>
          <span class="bp-read-time">{{ $post->read_time }} min read</span>
        </div>
      </div>
    </div>
  </div>
</header>

{{-- ── COVER IMAGE ───────────────────────────────────────────── --}}
@if($post->cover_image)
<div class="bp-cover">
  <div class="container">
    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="bp-cover-img" loading="eager" />
  </div>
</div>
@endif

{{-- ── ARTICLE BODY ──────────────────────────────────────────── --}}
<div class="bp-body">
  <div class="container">
    <div class="bp-layout">

      {{-- ToC sidebar --}}
      <aside class="bp-toc-col" aria-label="Table of contents">
        <div class="bp-toc-sticky" id="bpToc">
          <div class="bp-toc-header">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
              <path d="M2 3.5h10M2 7h8M2 10.5h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            Table of Contents
          </div>
          <nav id="bpTocNav" aria-label="Article sections"></nav>
        </div>
      </aside>

      {{-- Main article --}}
      <article class="bp-article" id="bpArticle">
        <div class="bp-prose" id="bpContent">
          {!! $post->content_html !!}
        </div>

        {{-- Tags --}}
        @if($post->tags->count())
        <div class="bp-tags">
          <span class="bp-tags-label">Topics:</span>
          @foreach($post->tags as $tag)
          <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="bp-tag">{{ $tag->name }}</a>
          @endforeach
        </div>
        @endif

        {{-- Social share --}}
        <div class="bp-share">
          <span class="bp-share-label">Share this article</span>
          <div class="bp-share-btns">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}"
               class="bp-share-btn bp-share-twitter" target="_blank" rel="noopener" aria-label="Share on Twitter">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M13.44 1H15.5L10.86 6.37L16.25 15H12L8.64 10.19L4.8 15H2.74L7.7 9.27L2.5 1H6.85L9.9 5.4L13.44 1ZM12.73 13.77H13.89L5.08 2.19H3.83L12.73 13.77Z"/></svg>
              Twitter
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}"
               class="bp-share-btn bp-share-linkedin" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>
              LinkedIn
            </a>
            <button class="bp-share-btn bp-share-copy" onclick="copyUrl()" aria-label="Copy link">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="5" y="5" width="8" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M3 11V3.5A1.5 1.5 0 014.5 2H11" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
              <span id="bpCopyText">Copy link</span>
            </button>
          </div>
        </div>

        {{-- Author bio --}}
        <div class="bp-author-card">
          @if($post->author->avatar)
            <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" class="bp-author-card-avatar" />
          @else
            <div class="bp-author-card-avatar bp-author-initials-lg">{{ strtoupper(substr($post->author->name, 0, 1)) }}</div>
          @endif
          <div class="bp-author-card-body">
            <div class="bp-author-card-name">{{ $post->author->name }}</div>
            <div class="bp-author-card-role">{{ $post->author->title ?? 'ProposalCraft Team' }}</div>
            @if($post->author->bio)
              <p class="bp-author-card-bio">{{ $post->author->bio }}</p>
            @endif
          </div>
        </div>
      </article>

    </div>{{-- /.bp-layout --}}
  </div>
</div>

{{-- ── RELATED POSTS ─────────────────────────────────────────── --}}
@if($related->count())
<section class="bp-related section-padding">
  <div class="container">
    <div class="bp-related-header">
      <span class="section-eyebrow">Keep Reading</span>
      <h2 class="section-heading" style="font-size:1.75rem">Related articles</h2>
    </div>
    <div class="bl-grid">
      @foreach($related as $relPost)
      <article class="bl-card reveal reveal-delay-{{ $loop->index + 1 }}">
        <a href="{{ route('blog.show', $relPost->slug) }}" class="bl-card-img-link" tabindex="-1" aria-hidden="true">
          @if($relPost->cover_image)
            <img src="{{ $relPost->cover_image }}" alt="{{ $relPost->title }}" class="bl-card-img" loading="lazy" />
          @else
            <div class="bl-card-img bl-card-img-placeholder"></div>
          @endif
        </a>
        <div class="bl-card-body">
          <div class="bl-card-meta">
            <span class="bl-cat-pill bl-cat-pill-sm">{{ $relPost->category->name }}</span>
            <span class="bl-dot">·</span>
            <span class="bl-read-time">{{ $relPost->read_time }} min</span>
          </div>
          <h3 class="bl-card-title"><a href="{{ route('blog.show', $relPost->slug) }}">{{ $relPost->title }}</a></h3>
          <p class="bl-card-excerpt">{{ $relPost->excerpt }}</p>
        </div>
      </article>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── CTA ─────────────────────────────────────────────────────── --}}
<section class="bp-cta section-padding">
  <div class="container">
    <div class="bp-cta-card reveal">
      <div class="bp-cta-content text-center">
        <span class="section-eyebrow" style="justify-content:center">Try ProposalCraft Free</span>
        <h2 class="section-heading">Stop guessing.<br>Start closing.</h2>
        <p class="lead mx-auto">Create your first beautiful proposal in under 5 minutes. See exactly when clients open it — and close the deal while it's fresh.</p>
        <div class="bp-cta-btns">
          <a href="{{ route('signup') }}" class="btn-primary">Start Free — No Card Needed</a>
          <a href="{{ route('demo') }}" class="btn-outline">View Live Demo</a>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('frontend/js/blog-post.js') }}" defer></script>
@endpush