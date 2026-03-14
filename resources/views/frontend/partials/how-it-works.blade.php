<!-- ============================================================
     HOW IT WORKS
     ============================================================ -->
<section id="how-it-works" class="section-padding-lg">
  <div class="container">

    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">How It Works</span>
      <h2 class="section-heading reveal reveal-delay-1">
        From idea to signed deal<br />in three steps.
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        ProposalCraft removes every obstacle between your idea and a signed contract.
      </p>
    </div>

    <div class="steps-container">
      <!-- Connecting line (decorative) -->
      <div class="steps-connector" aria-hidden="true"></div>

      <div class="row g-4 justify-content-center">
        @foreach ($steps as $index => $step)
        <div class="col-md-4 reveal reveal-delay-{{ $index + 1 }}">
          <div class="step-card">
            <div class="step-number" aria-label="Step {{ $index + 1 }}">{{ $index + 1 }}</div>
            <h4>{{ $step['title'] }}</h4>
            <p>{{ $step['description'] }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    <!-- CTA after steps -->
    <div class="text-center mt-5 pt-2 reveal">
      <a href="#" class="btn-primary" style="padding:1rem 2.5rem;font-size:1rem;">
        Try It Free — No Credit Card
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

  </div>
</section>