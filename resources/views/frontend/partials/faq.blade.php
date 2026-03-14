{{-- ============================================================
     FAQ
     ============================================================ --}}
<section id="faq" class="section-padding-lg" aria-labelledby="faq-heading">
  <div class="container">

    {{-- Header --}}
    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">FAQ</span>
      <h2 class="section-heading reveal reveal-delay-1" id="faq-heading">
        Frequently asked<br />questions.
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        Everything you need to know about ProposalCraft. Can't find the answer?
        <a href="#contact" style="color:var(--accent);font-weight:600;text-decoration:underline;text-underline-offset:3px">Reach out to us.</a>
      </p>
    </div>

    {{-- FAQ Accordion --}}
    <div class="faq-list" role="list" aria-label="Frequently asked questions">
      @foreach ($faqs as $index => $faq)
      <div class="faq-item reveal reveal-delay-{{ ($index % 3) + 1 }}" role="listitem">

        <button
          class="faq-question"
          id="faq-btn-{{ $index }}"
          aria-expanded="false"
          aria-controls="faq-answer-{{ $index }}"
          type="button">

          {{ $faq['q'] }}

          <span class="faq-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </span>
        </button>

        <div
          class="faq-answer"
          id="faq-answer-{{ $index }}"
          role="region"
          aria-labelledby="faq-btn-{{ $index }}"
          hidden>
          <div class="faq-answer-inner">
            {!! $faq['a'] !!}
          </div>
        </div>

      </div>
      @endforeach
    </div>

  </div>
</section>