{{-- ============================================================
     FEATURES
     ============================================================ --}}
<section id="features" class="section-padding-lg" aria-labelledby="features-heading">
  <div class="container">

    {{-- Header --}}
    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">Features</span>
      <h2 class="section-heading reveal reveal-delay-1" id="features-heading">
        Everything you need<br />to close more deals.
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        A complete proposal toolkit — from beautiful design to real-time analytics and legally binding e-signatures.
      </p>
    </div>

    {{-- Feature Grid --}}
    <div class="features-grid">
      @foreach ($features as $index => $feature)
        <article
          class="feature-card {{ isset($feature['featured']) && $feature['featured'] ? 'featured' : '' }} {{ isset($feature['wide']) && $feature['wide'] ? 'feature-card-featured' : '' }} reveal reveal-delay-{{ ($index % 3) + 1 }}"
          aria-label="{{ $feature['title'] }}">

          <div class="feature-icon" aria-hidden="true">{{ $feature['icon'] }}</div>
          <h4>{{ $feature['title'] }}</h4>
          <p>{{ $feature['description'] }}</p>

          @if (isset($feature['tag']))
          <span class="feature-tag {{ $feature['tag_color'] ?? '' }}">{{ $feature['tag'] }}</span>
          @endif

        </article>
      @endforeach
    </div>

  </div>
</section>