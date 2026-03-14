{{-- ============================================================
     PROBLEM → SOLUTION
     ============================================================ --}}
<section id="problem" class="section-padding-lg" aria-labelledby="problem-heading">

  {{-- Background --}}
  <div class="problem-bg" aria-hidden="true">
    <div class="problem-bg-grid"></div>
    <div class="problem-bg-orb problem-bg-orb-1"></div>
    <div class="problem-bg-orb problem-bg-orb-2"></div>
  </div>

  <div class="container">

    {{-- ── Section Header ──────────────────────────────────────── --}}
    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">The Problem</span>
      <h2 class="section-heading reveal reveal-delay-1" id="problem-heading">
        Proposals shouldn't be<br />this painful.
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        Most professionals waste hours building proposals that look unprofessional and fail to convert.
      </p>
    </div>

    {{-- ── Problem Cards ────────────────────────────────────────── --}}
    <div class="row g-4 mb-5">
      @foreach ($problems as $index => $problem)
      <div class="col-md-4 reveal reveal-delay-{{ ($index % 3) + 1 }}">
        <article class="problem-card" aria-label="{{ $problem['title'] }}">
          <div class="problem-icon" aria-hidden="true">{{ $problem['icon'] }}</div>
          <h4>{{ $problem['title'] }}</h4>
          <p>{{ $problem['description'] }}</p>
        </article>
      </div>
      @endforeach
    </div>

    {{-- ── Bridge Arrow ─────────────────────────────────────────── --}}
    <div class="text-center" aria-hidden="true">
      <div class="solution-bridge">
        <div class="bridge-line"></div>
        <div class="bridge-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <polyline points="19 12 12 19 5 12"/>
          </svg>
        </div>
        <div class="bridge-label">The Solution</div>
      </div>
    </div>

    {{-- ── Solution Card ────────────────────────────────────────── --}}
    <div class="row justify-content-center">
      <div class="col-lg-11">
        <div class="solution-card reveal" role="region" aria-label="ProposalCraft solution overview">
          <div class="solution-card-inner">
            <div class="row align-items-center g-5">

              {{-- Copy --}}
              <div class="col-md-7">
                <h3>ProposalCraft: From draft to&nbsp;deal in minutes.</h3>
                <p>A complete proposal platform that helps you build, send, track, and sign proposals — beautifully. Turn your workflow from chaos to confidence.</p>

                <ul class="solution-list" aria-label="Solution benefits">
                  <li>
                    <span class="solution-check" aria-hidden="true">✓</span>
                    Build polished proposals <strong>10× faster</strong> with smart templates
                  </li>
                  <li>
                    <span class="solution-check" aria-hidden="true">✓</span>
                    Know exactly when clients open, scroll, and engage
                  </li>
                  <li>
                    <span class="solution-check" aria-hidden="true">✓</span>
                    Close deals on the spot with <strong>built-in e-signatures</strong>
                  </li>
                  <li>
                    <span class="solution-check" aria-hidden="true">✓</span>
                    Impress clients with interactive, web-based proposals
                  </li>
                </ul>
              </div>

              {{-- Visual panel --}}
              <div class="col-md-5">
                <div class="solution-visual" aria-hidden="true">
                  <div class="solution-visual-icon">🚀</div>
                  <div class="solution-tagline">
                    <div class="headline">Win more deals.</div>
                    <div class="sub">Every proposal. Every time.</div>
                  </div>
                  <div class="solution-stats-mini">
                    <div class="solution-stat">
                      <div class="val">10×</div>
                      <div class="lbl">Faster</div>
                    </div>
                    <div class="solution-stat">
                      <div class="val">94%</div>
                      <div class="lbl">Close Rate</div>
                    </div>
                    <div class="solution-stat">
                      <div class="val">25K</div>
                      <div class="lbl">Users</div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>