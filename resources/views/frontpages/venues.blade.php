<style>
  /* ══════════════════════════════════════
     VENUES SECTION
  ══════════════════════════════════════ */
  .venues-section {
    padding: 6.5rem 1.5rem;
    background: var(--bg-alt);
  }

  .venues-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
  }

  @media (max-width: 1100px) { .venues-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .venues-grid { grid-template-columns: 1fr; } }

  /* Card */
  .venue-card {
    background: #fff;
    border-radius: 1.5rem;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(26,31,94,.06);
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: transform .32s cubic-bezier(.4,0,.2,1), box-shadow .32s;
  }
  .venue-card:hover {
    transform: translateY(-9px);
    box-shadow: var(--shadow-lg);
  }

  /* Image */
  .venue-img-wrap {
    position: relative;
    overflow: hidden;
    height: 220px;
    flex-shrink: 0;
  }
  .venue-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .65s cubic-bezier(.4,0,.2,1);
  }
  .venue-card:hover .venue-img-wrap img { transform: scale(1.08); }

  /* Dark gradient so badges are always readable */
  .venue-img-wrap::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(8,10,50,.55) 0%, transparent 55%);
    pointer-events: none;
  }

  /* Capacity badge — bottom-left over the image */
  .venue-capacity {
    position: absolute;
    bottom: 1rem; left: 1rem;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.35);
    backdrop-filter: blur(8px);
    border-radius: 2rem;
    padding: 0.38rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #fff;
    letter-spacing: 0.03em;
  }
  .venue-capacity i { font-size: 0.82rem; }

  /* Optional popular badge — top-right */
  .venue-badge {
    position: absolute;
    top: 1rem; right: 1rem;
    z-index: 2;
    background: var(--accent);
    color: #fff;
    border-radius: 2rem;
    padding: 0.3rem 0.85rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    box-shadow: 0 4px 14px rgba(91,103,232,.4);
  }

  /* Body */
  .venue-card-body {
    padding: 1.6rem 1.75rem 1.85rem;
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  .venue-card-body h3 {
    font-family: 'DM Sans', sans-serif;
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--navy-deep);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 0.55rem;
  }
  .venue-card-body p {
    font-size: 0.9rem;
    color: var(--muted);
    line-height: 1.65;
    flex: 1;
  }

  /* "View Details" link at bottom */
  .venue-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 1.25rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--accent);
    text-decoration: none;
    transition: gap .2s, color .2s;
  }
  .venue-link:hover {
    color: var(--navy);
    gap: 0.65rem;
  }
</style>

{{-- ══════ VENUES ══════ --}}
<section class="venues-section" id="venues">
  <div class="container-max">

    <div class="section-header" style="text-align:center; margin-bottom:4rem;">
      <p class="section-eyebrow">Facilities</p>
      <h2 class="section-title">Our Venues</h2>
      <p class="section-sub">Perfect spaces for every occasion</p>
    </div>

    <div class="venues-grid">

      {{-- Conference Room --}}
      <div class="venue-card">
        <div class="venue-img-wrap">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}"
               alt="Conference Room" loading="lazy">
          <div class="venue-capacity">
            <i class="ri-group-line"></i>
            <span>Up to 200 pax</span>
          </div>
        </div>
        <div class="venue-card-body">
          <h3>Conference Room</h3>
          <p>Spacious hall perfect for conferences, seminars, and corporate events with modern AV facilities.</p>
          <a href="#" class="venue-link">View Details <i class="ri-arrow-right-line"></i></a>
        </div>
      </div>

      {{-- Ballroom --}}
      <div class="venue-card">
        <div class="venue-img-wrap">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}"
               alt="Ballroom" loading="lazy">
          <div class="venue-capacity">
            <i class="ri-group-line"></i>
            <span>Up to 100 pax</span>
          </div>
        </div>
        <div class="venue-card-body">
          <h3>Ballroom</h3>
          <p>Grand venue for weddings, celebrations, and large-scale social and corporate events.</p>
          <a href="#" class="venue-link">View Details <i class="ri-arrow-right-line"></i></a>
        </div>
      </div>

      {{-- Cafeteria --}}
      <div class="venue-card">
        <div class="venue-img-wrap">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}"
               alt="Cafeteria" loading="lazy">
          <div class="venue-capacity">
            <i class="ri-group-line"></i>
            <span>Up to 300 pax</span>
          </div>
        </div>
        <div class="venue-card-body">
          <h3>Cafeteria</h3>
          <p>Elegant dining space ideal for meetings, workshops, and intimate group gatherings.</p>
          <a href="#" class="venue-link">View Details <i class="ri-arrow-right-line"></i></a>
        </div>
      </div>

      {{-- Coffee Prose --}}
      <div class="venue-card">
        <div class="venue-img-wrap">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}"
               alt="Coffee Prose" loading="lazy">
          <div class="venue-capacity">
            <i class="ri-group-line"></i>
            <span>Up to 30 pax</span>
          </div>
        </div>
        <div class="venue-card-body">
          <h3>Coffee Prose</h3>
          <p>Cozy café space equipped for board meetings, small group sessions, and casual catch-ups.</p>
          <a href="#" class="venue-link">View Details <i class="ri-arrow-right-line"></i></a>
        </div>
      </div>

    </div>
  </div>
</section>