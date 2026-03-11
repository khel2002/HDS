@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Hotel De SLSU')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/materio.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">

  <style>
    /* ── RESET ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── TOKENS ── */
    :root {
      --navy:        #1a1f5e;
      --navy-dark:   #12175a;
      --navy-deep:   #0d1147;
      --accent:      #5b67e8;
      --accent-lite: #7c86f0;
      --green:       #27ae60;
      --bg-alt:      #f0f3fb;
      --card-bg:     #f7f8fd;
      --muted:       #64748b;
      --white:       #ffffff;
      --shadow-sm:   0 2px 12px rgba(26,31,94,.07);
      --shadow-md:   0 8px 32px rgba(26,31,94,.13);
      --shadow-lg:   0 16px 56px rgba(26,31,94,.18);
    }

    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      color: var(--navy-deep);
      background: #fff;
      overflow-x: hidden;
    }
    h1, h2 { font-family: 'DM Serif Display', serif; }

    /* ══════════════════════════════════════
       HERO
    ══════════════════════════════════════ */
    .hero {
      position: relative;
      min-height: 100vh;
      background: url('{{ asset('assets/img/frontpages/img/header-bg.jpg') }}') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 6rem 1.5rem 5rem;
    }

    /* STRONG overlay — white text is always visible */
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(
        160deg,
        rgba(6, 8, 50, 0.82) 0%,
        rgba(6, 8, 50, 0.65) 55%,
        rgba(6, 8, 50, 0.85) 100%
      );
    }

    .hero-content {
      position: relative;
      z-index: 2;
      color: #fff;
      max-width: 840px;
      width: 100%;
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(255,255,255,.13);
      border: 1px solid rgba(255,255,255,.28);
      backdrop-filter: blur(8px);
      border-radius: 2rem;
      padding: 0.45rem 1.2rem;
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.09em;
      text-transform: uppercase;
      color: rgba(255,255,255,.95);
      margin-bottom: 1.6rem;
    }

    .hero-content h1 {
      font-size: clamp(3.2rem, 7.5vw, 5.8rem);
      font-weight: 400;
      line-height: 1.06;
      margin-bottom: 1.25rem;
      color: #ffffff;
      text-shadow: 0 3px 28px rgba(0,0,0,.5);
      letter-spacing: -0.01em;
    }

    .hero-content p {
      font-size: clamp(1.05rem, 2.2vw, 1.25rem);
      font-weight: 300;
      color: rgba(255,255,255,.88);
      max-width: 560px;
      margin: 0 auto 3rem;
      line-height: 1.7;
    }

    /* ── SEARCH BOX ── */
    .hero-search {
      background: rgba(255,255,255,.97);
      backdrop-filter: blur(24px);
      border-radius: 1.35rem;
      padding: 2.25rem 2.5rem;
      box-shadow: 0 28px 80px rgba(0,0,0,.3);
      max-width: 820px;
      margin: 0 auto;
    }

    .search-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr auto;
      gap: 1.25rem;
      align-items: end;
    }
    @media (max-width: 740px) {
      .search-row { grid-template-columns: 1fr 1fr; }
      .btn-search { grid-column: span 2; width: 100%; justify-content: center; }
    }
    @media (max-width: 480px) {
      .search-row { grid-template-columns: 1fr; }
      .btn-search { grid-column: span 1; }
      .hero-search { padding: 1.75rem 1.5rem; }
    }

    .search-field label {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 0.55rem;
    }
    .search-field label i { font-size: 0.88rem; color: var(--accent); }

    .search-field input,
    .search-field select {
      width: 100%;
      border: 1.5px solid #e2e5f0;
      border-radius: 0.7rem;
      padding: 0 1rem;
      height: 50px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      color: var(--navy-deep);
      background: #fafbff;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .search-field input:focus,
    .search-field select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(91,103,232,.13);
      background: #fff;
    }

    .btn-search {
      display: inline-flex;
      align-items: center;
      gap: 0.55rem;
      background: var(--navy);
      color: #fff;
      border: none;
      border-radius: 0.7rem;
      padding: 0 2.2rem;
      height: 50px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: background .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 6px 22px rgba(13,17,71,.28);
    }
    .btn-search:hover {
      background: var(--accent);
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(91,103,232,.38);
    }

    /* ══════════════════════════════════════
       SHARED SECTION
    ══════════════════════════════════════ */
    .section-wrap { padding: 6.5rem 1.5rem; }
    .section-wrap.alt { background: var(--bg-alt); }

    .section-header { text-align: center; margin-bottom: 4rem; }
    .section-eyebrow {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 0.7rem;
    }
    .section-title {
      font-size: clamp(2.1rem, 4.5vw, 2.9rem);
      color: var(--navy-deep);
      margin-bottom: 0.65rem;
      line-height: 1.12;
    }
    .section-sub {
      font-size: 1.05rem;
      color: var(--muted);
      font-weight: 300;
    }
    .container-max { max-width: 1200px; margin: 0 auto; }

    /* ══════════════════════════════════════
       AMENITIES
    ══════════════════════════════════════ */
    .amenities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }
    .amenity-card {
      background: #fff;
      border-radius: 1.3rem;
      padding: 2.5rem 2.25rem;
      box-shadow: var(--shadow-sm);
      transition: transform .28s, box-shadow .28s;
      border: 1px solid rgba(91,103,232,.07);
    }
    .amenity-card:hover {
      transform: translateY(-7px);
      box-shadow: var(--shadow-md);
    }
    .amenity-icon {
      width: 58px; height: 58px;
      background: linear-gradient(135deg, #eaedfd 0%, #dde1fb 100%);
      border-radius: 1.1rem;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.55rem;
      color: var(--navy);
      margin-bottom: 1.5rem;
    }
    .amenity-card h4 {
      font-family: 'DM Sans', sans-serif;
      font-size: 1.08rem;
      font-weight: 700;
      color: var(--navy-deep);
      margin-bottom: 0.6rem;
    }
    .amenity-card p { font-size: 0.92rem; color: var(--muted); line-height: 1.65; }

    /* ══════════════════════════════════════
       ROOMS
    ══════════════════════════════════════ */
    .rooms-carousel-wrapper {
      position: relative;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 3.5rem;
    }

    .rooms-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2.25rem;
      align-items: stretch;   /* force all cells to same row height */
    }
    @media (max-width: 1024px) { .rooms-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 620px)  { .rooms-grid { grid-template-columns: 1fr; } }

    /* Every anchor wrapper must also stretch */
    .rooms-grid > a {
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .room-card {
      background: #fff;
      border-radius: 1.5rem;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: transform .32s, box-shadow .32s;
      border: 1px solid rgba(26,31,94,.06);
      display: flex;
      flex-direction: column;
      height: 100%;          /* fill the grid cell completely */
    }
    .room-card:hover {
      transform: translateY(-9px);
      box-shadow: var(--shadow-lg);
    }
    .room-card-img-wrap {
      position: relative;
      overflow: hidden;
      height: 250px;
      flex-shrink: 0;
    }
    .room-card-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform .65s cubic-bezier(.4,0,.2,1);
    }
    .room-card:hover .room-card-img-wrap img { transform: scale(1.08); }

    .room-rating {
      position: absolute; top: 1rem; right: 1rem;
      background: #fff;
      border-radius: 2rem;
      padding: 0.32rem 0.85rem;
      font-size: 0.83rem;
      font-weight: 700;
      color: var(--navy-deep);
      display: flex; align-items: center; gap: 0.3rem;
      box-shadow: 0 3px 14px rgba(0,0,0,.13);
    }
    .room-rating i { color: #f59e0b; font-size: 0.83rem; }

    .room-card-body {
      padding: 1.65rem 1.85rem 1.85rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .room-card-body h3 {
      font-family: 'DM Sans', sans-serif;
      font-size: 1.22rem;
      font-weight: 700;
      color: var(--navy-deep);
      margin-bottom: 0.4rem;
    }
    .room-meta {
      font-size: 0.83rem;
      color: var(--muted);
      margin-bottom: 1.1rem;
    }
    .room-meta span + span::before { content: ' · '; color: #c4c9e0; }

    .room-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1.35rem;
    }
    .room-tag {
      border: 1.5px solid #d4d8f5;
      border-radius: 2rem;
      padding: 0.3rem 0.85rem;
      font-size: 0.76rem;
      font-weight: 500;
      color: var(--navy);
      background: #f4f5fd;
    }
    .room-footer {
      margin-top: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 1.1rem;
      border-top: 1px solid #eef0f8;
    }
    .room-price .amount {
      font-size: 1.65rem;
      font-weight: 800;
      color: var(--navy-deep);
    }
    .room-price .per { font-size: 0.8rem; color: var(--muted); }

    .btn-book {
      background: var(--navy);
      color: #fff;
      border: none;
      border-radius: 0.65rem;
      padding: 0.68rem 1.5rem;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: background .2s, transform .15s;
      box-shadow: 0 4px 16px rgba(13,17,71,.2);
    }
    .btn-book:hover { background: var(--accent); color: #fff; transform: translateY(-2px); }

    /* Carousel nav */
    .carousel-nav {
      position: absolute;
      top: 50%; transform: translateY(-50%);
      z-index: 10;
      width: 46px; height: 46px;
      background: var(--navy);
      border: none; border-radius: 50%;
      color: #fff; font-size: 1.25rem;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 20px rgba(13,17,71,.25);
      transition: background .2s, transform .15s;
    }
    .carousel-nav:hover { background: var(--accent); transform: translateY(calc(-50% - 2px)); }
    .carousel-nav.prev { left: 0; }
    .carousel-nav.next { right: 0; }

    /* ══════════════════════════════════════
       WHY CHOOSE US
    ══════════════════════════════════════ */
    .why-section {
      background: #0e1250;
      padding: 7rem 1.5rem;
      color: #fff;
    }
    .why-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5.5rem;
      align-items: center;
    }
    @media (max-width: 860px) { .why-inner { grid-template-columns: 1fr; gap: 3.5rem; } }

    .why-left h2 { font-size: clamp(2.2rem, 5vw, 3.2rem); margin-bottom: 1.2rem; line-height: 1.12; color: #ffffff; }
    .why-left > p { color: rgba(255,255,255,.82); font-size: 1.05rem; line-height: 1.72; margin-bottom: 2.5rem; }
    .why-list { list-style: none; display: flex; flex-direction: column; gap: 1.1rem; }
    .why-list li { display: flex; align-items: center; gap: 1rem; font-size: 1rem; font-weight: 500; color: #ffffff; }
    .why-check {
      width: 32px; height: 32px; min-width: 32px;
      background: var(--green);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.82rem; color: #fff;
      box-shadow: 0 3px 12px rgba(39,174,96,.42);
    }
    .why-photos {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto auto;
      gap: 1rem;
    }
    .why-photo { border-radius: 1.1rem; overflow: hidden; height: 195px; }
    .why-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .55s; }
    .why-photo:hover img { transform: scale(1.05); }
    .why-photo:first-child { grid-row: span 2; height: auto; min-height: 405px; }

    /* ══════════════════════════════════════
       CONTACT
    ══════════════════════════════════════ */
    .contact-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
      gap: 2rem;
      max-width: 980px;
      margin: 0 auto;
    }
    .contact-card {
      background: var(--card-bg);
      border-radius: 1.35rem;
      padding: 3rem 2.25rem;
      text-align: center;
      border: 1px solid rgba(91,103,232,.09);
      transition: transform .26s, box-shadow .26s;
    }
    .contact-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }
    .contact-icon {
      width: 68px; height: 68px;
      background: linear-gradient(135deg, #dde1fb 0%, #c8cef7 100%);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.55rem; color: var(--navy);
      margin: 0 auto 1.5rem;
    }
    .contact-card h4 {
      font-family: 'DM Sans', sans-serif;
      font-size: 1.08rem; font-weight: 700;
      color: var(--navy-deep); margin-bottom: 0.6rem;
    }
    .contact-card p { font-size: 0.93rem; color: var(--muted); line-height: 1.72; }

    /* ══════════════════════════════════════
       FOOTER
    ══════════════════════════════════════ */
    .site-footer { background: var(--navy-deep); color: rgba(255,255,255,.72); padding: 5rem 1.5rem 2.25rem; }
    .footer-inner { max-width: 1200px; margin: 0 auto; }
    .footer-grid {
      display: grid;
      grid-template-columns: 2.2fr 1fr 1fr 1fr;
      gap: 3rem;
      padding-bottom: 3rem;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    @media (max-width: 900px) { .footer-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 480px) { .footer-grid { grid-template-columns: 1fr; } }

    .footer-brand-row { display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1rem; }
    .footer-brand-row i { font-size: 1.55rem; color: var(--accent-lite); }
    .footer-brand-row span { font-size: 1.3rem; font-weight: 700; color: #fff; }
    .footer-desc { font-size: 0.9rem; line-height: 1.72; max-width: 280px; }
    .footer-socials { display: flex; gap: 0.7rem; margin-top: 1.6rem; }
    .footer-social {
      width: 38px; height: 38px;
      background: rgba(255,255,255,.1);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 0.9rem; text-decoration: none;
      transition: background .2s, transform .15s;
    }
    .footer-social:hover { background: var(--accent); color: #fff; transform: translateY(-2px); }

    .footer-col h5 {
      font-family: 'DM Sans', sans-serif;
      font-weight: 700; font-size: 0.88rem;
      color: #fff; letter-spacing: 0.06em;
      margin-bottom: 1.2rem; text-transform: uppercase;
    }
    .footer-links { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; }
    .footer-links a { color: rgba(255,255,255,.62); text-decoration: none; font-size: 0.9rem; transition: color .2s; }
    .footer-links a:hover { color: #fff; }
    .footer-bottom { padding-top: 2rem; text-align: center; font-size: 0.83rem; color: rgba(255,255,255,.35); }
  </style>
@endsection

@section('content')

  {{-- ══════ HERO ══════ --}}
  <section class="hero" id="home">
    <div class="hero-content">
      <div class="hero-eyebrow">
        <i class="ri-map-pin-line"></i>
        San Roque Sogod, Southern Leyte
      </div>
      <h1>Hotel De<br> SLSU</h1>
      <p>Discover the perfect blend of elegance, comfort,<br>and world-class hospitality</p>

      <div class="hero-search">
        <form action="{{ route('frontpage.available-rooms') }}" method="GET">
          <div class="search-row">
            <div class="search-field">
              <label><i class="ri-calendar-line"></i> Check-in</label>
              <input type="date" name="check_in" value="{{ old('check_in') }}">
            </div>
            <div class="search-field">
              <label><i class="ri-calendar-check-line"></i> Check-out</label>
              <input type="date" name="check_out" value="{{ old('check_out') }}">
            </div>
            <div class="search-field">
              <label><i class="ri-user-3-line"></i> Guests</label>
              <select name="guests">
                <option value="1">1 Guest</option>
                <option value="2" selected>2 Guests</option>
                <option value="3">3 Guests</option>
                <option value="4">4 Guests</option>
                <option value="5">5+ Guests</option>
              </select>
            </div>
            <button type="submit" class="btn-search">Search &nbsp;<i class="ri-arrow-right-line"></i></button>
          </div>
        </form>
      </div>
    </div>
  </section>

  {{-- ══════ AMENITIES ══════ --}}
  <section class="section-wrap alt" id="amenities">
    <div class="container-max">
      <div class="section-header">
        <p class="section-eyebrow">What We Offer</p>
        <h2 class="section-title">World-Class Amenities</h2>
        <p class="section-sub">Everything you need for an unforgettable stay</p>
      </div>
      <div class="amenities-grid">
        <div class="amenity-card">
          <div class="amenity-icon"><i class="ri-wifi-line"></i></div>
          <h4>High-Speed WiFi</h4>
          <p>Stay connected with complimentary high-speed internet throughout the entire property</p>
        </div>
        <div class="amenity-card">
          <div class="amenity-icon"><i class="ri-restaurant-2-line"></i></div>
          <h4>Gourmet Dining</h4>
          <p>Experience world-class cuisine at our award-winning restaurants and bars</p>
        </div>
        <div class="amenity-card">
          <div class="amenity-icon"><i class="ri-riding-line"></i></div>
          <h4>Fitness Center</h4>
          <p>State-of-the-art gym with personal trainers and modern equipment</p>
        </div>
        <div class="amenity-card">
          <div class="amenity-icon"><i class="ri-seedling-line"></i></div>
          <h4>Luxury Spa</h4>
          <p>Indulge in rejuvenating treatments and comprehensive wellness programs</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ══════ ROOMS ══════ --}}
  <section class="section-wrap" id="rooms">
    <div class="container-max">
      <div class="section-header">
        <p class="section-eyebrow">Accommodations</p>
        <h2 class="section-title">Our Luxury Rooms</h2>
        <p class="section-sub">Choose from our carefully designed rooms and suites</p>
      </div>
    </div>

    <div class="rooms-carousel-wrapper">
      {{-- Desktop (3-up) --}}
      <div class="d-none d-lg-block">
        <button class="carousel-nav prev" type="button" data-bs-target="#roomsCarouselDesktop" data-bs-slide="prev">
          <i class="ri-arrow-left-s-line"></i>
        </button>
        <button class="carousel-nav next" type="button" data-bs-target="#roomsCarouselDesktop" data-bs-slide="next">
          <i class="ri-arrow-right-s-line"></i>
        </button>
        <div id="roomsCarouselDesktop" class="carousel slide">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <div class="rooms-grid">
                @foreach ($roomsFirst as $room)
                  @include('frontpages.room-card', [
                    'room_id'        => $room->room_id,
                    'room_type_name' => $room->roomType->room_type_name,
                    'description'    => $room->roomType->description,
                    'image'          => $room->image_path,
                  ])
                @endforeach
              </div>
            </div>
            <div class="carousel-item">
              <div class="rooms-grid">
                @foreach ($roomsSecond as $room)
                  @include('frontpages.room-card', [
                    'room_id'        => $room->room_id,
                    'room_type_name' => $room->roomType->room_type_name,
                    'description'    => $room->roomType->description,
                    'image'          => $room->image_path,
                  ])
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Mobile (1-up) --}}
      <div class="d-block d-lg-none">
        <button class="carousel-nav prev" type="button" data-bs-target="#roomsCarouselMobile" data-bs-slide="prev">
          <i class="ri-arrow-left-s-line"></i>
        </button>
        <button class="carousel-nav next" type="button" data-bs-target="#roomsCarouselMobile" data-bs-slide="next">
          <i class="ri-arrow-right-s-line"></i>
        </button>
        <div id="roomsCarouselMobile" class="carousel slide">
          <div class="carousel-inner">
            <div class="carousel-item active">
              @foreach ($roomsMobFirst as $room)
                @include('frontpages.room-card', [
                  'room_id'        => $room->room_id,
                  'room_type_name' => $room->roomType->room_type_name,
                  'description'    => $room->roomType->description,
                  'image'          => $room->image_path,
                ])
              @endforeach
            </div>
            @foreach ($roomsMobSecond as $room)
              <div class="carousel-item">
                @include('frontpages.room-card', [
                  'room_id'        => $room->room_id,
                  'room_type_name' => $room->roomType->room_type_name,
                  'description'    => $room->roomType->description,
                  'image'          => $room->image_path,
                ])
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ══════ WHY CHOOSE US ══════ --}}
  <section class="why-section">
    <div class="why-inner">
      <div class="why-left">
        <h2>Why Choose Hotel De SLSU?</h2>
        <p>Experience unparalleled service and hospitality at the heart of Southern Leyte, where every detail is crafted for your comfort and satisfaction.</p>
        <ul class="why-list">
          <li><div class="why-check"><i class="ri-check-line"></i></div> 24/7 Concierge Service</li>
          <li><div class="why-check"><i class="ri-check-line"></i></div> Award-Winning Dining Experience</li>
          <li><div class="why-check"><i class="ri-check-line"></i></div> Function Halls &amp; Event Facilities</li>
          <li><div class="why-check"><i class="ri-check-line"></i></div> Business &amp; Corporate Packages</li>
          <li><div class="why-check"><i class="ri-check-line"></i></div> Spa &amp; Wellness Center</li>
          <li><div class="why-check"><i class="ri-check-line"></i></div> Complimentary Airport Transfer</li>
        </ul>
      </div>
      <div class="why-photos">
        <div class="why-photo">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Hotel exterior">
        </div>
        <div class="why-photo">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Pool">
        </div>
        <div class="why-photo">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Lounge">
        </div>
      </div>
    </div>
  </section>

  {{-- ══════ VENUES ══════ --}}
  @include('frontpages.venues')

  {{-- ══════ CONTACT ══════ --}}
  <section class="section-wrap" id="contact">
    <div class="container-max">
      <div class="section-header">
        <p class="section-eyebrow">Reach Us</p>
        <h2 class="section-title">Get In Touch</h2>
        <p class="section-sub">We're here to help make your stay exceptional</p>
      </div>
      <div class="contact-cards">
        <div class="contact-card">
          <div class="contact-icon"><i class="ri-phone-line"></i></div>
          <h4>Phone</h4>
          <p>0966-984-7577<br>24/7 Support Available</p>
        </div>
        <div class="contact-card">
          <div class="contact-icon"><i class="ri-mail-send-line"></i></div>
          <h4>Email</h4>
          <p>hotel@southernleytestateu.edu.ph</p>
        </div>
        <div class="contact-card">
          <div class="contact-icon"><i class="ri-map-pin-2-line"></i></div>
          <h4>Location</h4>
          <p>San Roque Sogod<br>Southern Leyte 6606</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ══════ FOOTER ══════ --}}
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-grid">
        <div>
          <div class="footer-brand-row">
            <i class="ri-building-line"></i>
            <span>Hotel De SLSU</span>
          </div>
          <p class="footer-desc">Experience luxury and comfort at its finest, nestled in the heart of Southern Leyte.</p>
          <div class="footer-socials">
            <a href="#" class="footer-social" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="footer-social" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="footer-social" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
        <div class="footer-col">
          <h5>Quick Links</h5>
          <ul class="footer-links">
            <li><a href="#">Home</a></li>
            <li><a href="#rooms">Rooms</a></li>
            <li><a href="#amenities">Amenities</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5>Services</h5>
          <ul class="footer-links">
            <li><a href="#">Room Service</a></li>
            <li><a href="#">Spa &amp; Wellness</a></li>
            <li><a href="#">Restaurant &amp; Bar</a></li>
            <li><a href="#">Event Spaces</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5>Follow Us</h5>
          <ul class="footer-links">
            <li><a href="#">Facebook</a></li>
            <li><a href="#">Instagram</a></li>
            <li><a href="#">Twitter</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 Hotel De SLSU. Developed by Michael &amp; Renz. All rights reserved.</p>
      </div>
    </div>
  </footer>

@endsection