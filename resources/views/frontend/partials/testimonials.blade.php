{{-- ============================================================
     TESTIMONIALS
     ============================================================ --}}
<section id="testimonials" class="section-padding-lg" aria-labelledby="testimonials-heading">
  <div class="container">

    {{-- Background pattern --}}
    <div class="testimonials-bg-pattern" aria-hidden="true"></div>

    {{-- Header --}}
    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">Testimonials</span>
      <h2 class="section-heading reveal reveal-delay-1" id="testimonials-heading">
        Loved by professionals<br />worldwide.
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        Join over 25,000 freelancers, agencies, and businesses already closing more deals with ProposalCraft.
      </p>
    </div>

    {{-- Aggregate Rating Bar --}}
    <div class="row justify-content-center mb-5 reveal reveal-delay-2">
      <div class="col-lg-10">
        <div class="testimonial-summary">
          <div class="rating-big" aria-label="Overall rating: 4.9 out of 5">
            <div class="rating-number">4.9</div>
            <div class="rating-stars-row" aria-hidden="true">★★★★★</div>
            <div class="rating-count">2,400+ reviews</div>
          </div>
          <div class="rating-divider" aria-hidden="true"></div>
          <div class="rating-breakdown" aria-label="Rating breakdown">
            @php
              $ratingRows = [
                ['stars' => '5 stars', 'pct' => 84],
                ['stars' => '4 stars', 'pct' => 11],
                ['stars' => '3 stars', 'pct' => 3],
                ['stars' => '2 stars', 'pct' => 1],
                ['stars' => '1 star',  'pct' => 1],
              ];
            @endphp
            @foreach ($ratingRows as $row)
            <div class="rating-row">
              <span style="flex-shrink:0;width:3.5rem">{{ $row['stars'] }}</span>
              <div class="rating-bar-track" role="progressbar" aria-valuenow="{{ $row['pct'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $row['stars'] }}: {{ $row['pct'] }}%">
                <div class="rating-bar-fill" style="width:{{ $row['pct'] }}%"></div>
              </div>
              <span class="rating-pct">{{ $row['pct'] }}%</span>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Testimonial Cards --}}
    <div class="row g-4" role="list" aria-label="Customer testimonials">
      @foreach ($testimonials as $index => $testimonial)
      <div class="col-md-6 col-lg-4 reveal reveal-delay-{{ ($index % 3) + 1 }}" role="listitem">

        <article
          class="testimonial-card {{ isset($testimonial['featured']) && $testimonial['featured'] ? 'featured' : '' }}"
          aria-label="Testimonial from {{ $testimonial['name'] }}">

          {{-- Stars --}}
          <div class="stars" aria-label="{{ $testimonial['rating'] ?? 5 }} out of 5 stars">
            @for ($s = 0; $s < ($testimonial['rating'] ?? 5); $s++)★@endfor
          </div>

          {{-- Quote --}}
          <blockquote class="testimonial-quote">
            "{{ $testimonial['quote'] }}"
          </blockquote>

          {{-- Author --}}
          <footer class="testimonial-author">
            <div
              class="author-avatar"
              aria-hidden="true"
              style="background:{{ $testimonial['avatar_bg'] ?? 'var(--accent-dim)' }};color:{{ $testimonial['avatar_color'] ?? 'var(--accent)' }}">
              {{ strtoupper(substr($testimonial['name'], 0, 1)) }}
            </div>
            <div class="author-info">
              <div class="author-name">{{ $testimonial['name'] }}</div>
              <div class="author-role">{{ $testimonial['role'] }}</div>
            </div>
            @if (isset($testimonial['company']))
            <div class="author-company-badge" aria-label="Company: {{ $testimonial['company'] }}">
              {{ $testimonial['company'] }}
            </div>
            @endif
          </footer>

        </article>
      </div>
      @endforeach
    </div>

  </div>
</section>