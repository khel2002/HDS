@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Hotel De SLSU')

@section('vendor-style')
  {{-- Materio core CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/materio.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  {{-- Fonts --}}
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">

  <style>
    :root {
      --primary-blue: #0A2463;
      --accent-azure: #247BA0;
      --light-azure: #3891A6;
      --gold-accent: #C1A34F;
      --navy-deep: #001233;
      --cream-light: #F8F9FA;
      --text-dark: #1e293b;
      --text-muted: #64748b;
      --shadow-sm: 0 2px 8px rgba(10, 36, 99, 0.08);
      --shadow-md: 0 4px 16px rgba(10, 36, 99, 0.12);
      --shadow-lg: 0 8px 32px rgba(10, 36, 99, 0.16);
      --shadow-blue-glow: 0 8px 32px rgba(36, 123, 160, 0.25);
      --border-radius: 0.5rem;
      --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Manrope', sans-serif;
      color: var(--text-dark);
      overflow-x: hidden;
    }

    /* ========== HERO SECTION ========== */
    .masthead {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      isolation: isolate;
      overflow: hidden;
    }

    .masthead::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(0, 18, 51, 0.85) 0%, rgba(10, 36, 99, 0.7) 50%, rgba(36, 123, 160, 0.6) 100%);
      z-index: 1;
    }

    .masthead::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 50% 50%, transparent 0%, rgba(0, 18, 51, 0.4) 100%);
      z-index: 2;
    }

    .masthead .container {
      position: relative;
      z-index: 3;
      text-align: center;
      padding: 2rem;
      animation: fadeInUp 1s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .masthead-subheading {
      color: var(--light-azure);
      font-size: clamp(0.875rem, 2vw, 1.125rem);
      font-weight: 500;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 1.5rem;
      animation: fadeInUp 1s ease-out 0.2s both;
      text-shadow: 0 2px 8px rgba(56, 145, 166, 0.3);
    }

    .masthead-subheading i {
      margin-right: 0.5rem;
      font-size: 1.2em;
    }

    .masthead-heading {
      font-family: 'Playfair Display', serif;
      color: #ffffff;
      font-size: clamp(2.5rem, 8vw, 5rem);
      font-weight: 900;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      letter-spacing: -0.02em;
      text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
      animation: fadeInUp 1s ease-out 0.4s both;
    }

    .masthead p {
      color: rgba(255, 255, 255, 0.95);
      font-size: clamp(1rem, 2.5vw, 1.375rem);
      max-width: 700px;
      margin: 0 auto 3rem;
      line-height: 1.6;
      font-weight: 400;
      animation: fadeInUp 1s ease-out 0.6s both;
    }

    .masthead p b {
      color: var(--gold-accent);
      font-weight: 700;
      text-shadow: 0 2px 8px rgba(193, 163, 79, 0.4);
    }

    .btn-modern {
      padding: 1rem 2.5rem;
      font-size: 0.95rem;
      font-weight: 600;
      letter-spacing: 1px;
      border-radius: var(--border-radius);
      transition: var(--transition-smooth);
      border: 2px solid transparent;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .btn-modern::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
      opacity: 0;
      transition: opacity 0.3s;
      z-index: -1;
    }

    .btn-modern:hover::before {
      opacity: 1;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--accent-azure) 0%, var(--light-azure) 100%);
      color: white;
      box-shadow: var(--shadow-blue-glow);
      border: 2px solid transparent;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, var(--light-azure) 0%, var(--accent-azure) 100%);
      transform: translateY(-2px);
      box-shadow: 0 12px 40px rgba(36, 123, 160, 0.4);
      color: white;
    }

    .btn-outline-light {
      background: transparent;
      color: white;
      border-color: rgba(255, 255, 255, 0.5);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      border-color: white;
      color: white;
      transform: translateY(-2px);
    }

    .masthead .btn-container {
      animation: fadeInUp 1s ease-out 0.8s both;
    }

    /* ========== ROOMS SECTION ========== */
    .rooms-section {
      padding: 6rem 0;
      background: linear-gradient(to bottom, #f0f4f8 0%, white 50%, #f8f9fa 100%);
      position: relative;
    }

    .rooms-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 200px;
      background: linear-gradient(to bottom, rgba(10, 36, 99, 0.03), transparent);
      pointer-events: none;
    }

    .section-heading {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2rem, 5vw, 3rem);
      font-weight: 700;
      color: var(--primary-blue);
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }

    .section-heading::after {
      content: '';
      position: absolute;
      bottom: -0.5rem;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--accent-azure), var(--light-azure), transparent);
      box-shadow: 0 2px 8px rgba(36, 123, 160, 0.3);
    }

    .section-subheading {
      color: var(--text-muted);
      font-size: clamp(1rem, 2vw, 1.125rem);
      max-width: 600px;
      margin: 2rem auto 4rem;
      line-height: 1.6;
    }

    /* Custom Carousel Controls */
    .custom-carousel-btn {
      width: 50px;
      height: 50px;
      background: white;
      border-radius: 50%;
      box-shadow: var(--shadow-md);
      opacity: 1;
      transition: var(--transition-smooth);
    }

    .custom-carousel-btn:hover {
      background: var(--accent-azure);
      transform: scale(1.1);
      box-shadow: var(--shadow-blue-glow);
    }

    .custom-carousel-btn .carousel-control-prev-icon,
    .custom-carousel-btn .carousel-control-next-icon {
      filter: invert(1);
      width: 20px;
      height: 20px;
    }

    .custom-carousel-btn:hover .carousel-control-prev-icon,
    .custom-carousel-btn:hover .carousel-control-next-icon {
      filter: invert(0);
    }

    .carousel-control-prev {
      left: -25px;
    }

    .carousel-control-next {
      right: -25px;
    }

    /* Fixed Carousel Container - Prevents height jumping */
    #roomsCarouselDesktop,
    #roomsCarouselMobile {
      position: relative;
    }

    #roomsCarouselDesktop .carousel-inner {
      min-height: 450px;
    }

    #roomsCarouselMobile .carousel-inner {
      min-height: 480px;
    }

    /* Ensure smooth transitions without height change */
    .carousel-item {
      transition: transform 0.6s ease-in-out;
    }

    .carousel-item.active,
    .carousel-item-next,
    .carousel-item-prev {
      display: flex;
      align-items: flex-start;
    }

    /* Mobile Carousel */
    #roomsCarouselMobile .carousel-control-prev,
    #roomsCarouselMobile .carousel-control-next {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 50%;
      top: 50%;
      transform: translateY(-50%);
    }

    #roomsCarouselMobile .carousel-control-prev {
      left: 10px;
    }

    #roomsCarouselMobile .carousel-control-next {
      right: 10px;
    }

    /* ========== CTA SECTION ========== */
    .cta-section {
      position: relative;
      min-height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--navy-deep) 0%, var(--primary-blue) 50%, #0d3b66 100%);
      isolation: isolate;
      overflow: hidden;
    }

    .cta-overlay {
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 20% 50%, rgba(36, 123, 160, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 50%, rgba(56, 145, 166, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 50% 20%, rgba(193, 163, 79, 0.08) 0%, transparent 40%);
      z-index: 1;
    }

    .cta-overlay::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        repeating-linear-gradient(
          0deg,
          transparent,
          transparent 2px,
          rgba(255, 255, 255, 0.03) 2px,
          rgba(255, 255, 255, 0.03) 4px
        );
      opacity: 0.5;
    }

    .cta-content {
      position: relative;
      z-index: 2;
      text-align: center;
      padding: 3rem 2rem;
      max-width: 800px;
      margin: 0 auto;
    }

    .cta-title {
      font-family: 'Playfair Display', serif;
      color: white;
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 700;
      margin-bottom: 1.5rem;
      line-height: 1.2;
    }

    .cta-description {
      color: rgba(255, 255, 255, 0.9);
      font-size: clamp(1.125rem, 2.5vw, 1.375rem);
      line-height: 1.6;
      margin-bottom: 2.5rem;
    }

    /* ========== FOOTER ========== */
    .footer {
      background: linear-gradient(180deg, var(--navy-deep) 0%, var(--primary-blue) 100%);
      color: rgba(255, 255, 255, 0.8);
      padding: 4rem 0 2rem;
      position: relative;
    }

    .footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(36, 123, 160, 0.5), transparent);
    }

    .footer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 3rem;
      margin-bottom: 3rem;
    }

    .footer-title {
      font-family: 'Playfair Display', serif;
      color: var(--light-azure);
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-shadow: 0 2px 8px rgba(56, 145, 166, 0.3);
    }

    .footer-text {
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    .footer-heading {
      color: white;
      font-size: 1.125rem;
      font-weight: 600;
      margin-bottom: 1.25rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .footer-links {
      list-style: none;
      padding: 0;
    }

    .footer-links li {
      margin-bottom: 0.75rem;
    }

    .footer-links a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: var(--transition-smooth);
      display: inline-block;
      position: relative;
    }

    .footer-links a::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0;
      height: 2px;
      background: var(--light-azure);
      transition: width 0.3s;
      box-shadow: 0 0 8px rgba(56, 145, 166, 0.5);
    }

    .footer-links a:hover {
      color: var(--light-azure);
      transform: translateX(5px);
    }

    .footer-links a:hover::after {
      width: 100%;
    }

    .social-links {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
    }

    .social-link {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-decoration: none;
      transition: var(--transition-smooth);
      font-size: 1.125rem;
    }

    .social-link:hover {
      background: linear-gradient(135deg, var(--accent-azure), var(--light-azure));
      color: white;
      transform: translateY(-3px);
      box-shadow: var(--shadow-blue-glow);
    }

    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .contact-item {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.6;
    }

    .contact-item i {
      color: var(--light-azure);
      font-size: 1.125rem;
      margin-top: 0.2rem;
      flex-shrink: 0;
    }

    .footer-bottom {
      text-align: center;
      padding-top: 2rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.875rem;
    }

    /* ========== RESPONSIVE DESIGN ========== */
    @media (max-width: 1199.98px) {
      .rooms-section {
        padding: 4rem 0;
      }

      .carousel-control-prev {
        left: -15px;
      }

      .carousel-control-next {
        right: -15px;
      }
    }

    @media (max-width: 991.98px) {
      .masthead {
        background-attachment: scroll;
        min-height: 90vh;
      }

      .masthead .container {
        padding: 1.5rem;
      }

      .btn-modern {
        padding: 0.875rem 2rem;
        font-size: 0.875rem;
      }

      .rooms-section {
        padding: 3rem 0;
      }

      .section-heading {
        margin-bottom: 0.75rem;
      }

      .section-subheading {
        margin: 1.5rem auto 3rem;
      }

      .cta-section {
        min-height: 50vh;
      }

      .footer-grid {
        gap: 2.5rem;
      }
    }

    @media (max-width: 767.98px) {
      .masthead p {
        margin: 0 auto 2rem;
      }

      .btn-container {
        flex-direction: column !important;
        width: 100%;
      }

      .btn-modern {
        width: 100%;
        justify-content: center;
      }

      .btn-modern.me-sm-2 {
        margin-right: 0 !important;
      }

      .rooms-section {
        padding: 2.5rem 0;
      }

      .cta-section {
        min-height: 40vh;
      }

      .cta-content {
        padding: 2rem 1.5rem;
      }

      .footer {
        padding: 3rem 0 1.5rem;
      }

      .footer-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
      }

      .social-links {
        justify-content: center;
      }

      .contact-item {
        justify-content: center;
        text-align: left;
      }
    }

    @media (max-width: 575.98px) {
      .masthead-heading {
        letter-spacing: -0.03em;
      }

      .section-heading::after {
        width: 60px;
      }

      .footer-grid {
        gap: 1.5rem;
      }
    }

    /* ========== ANIMATIONS ========== */
    @media (prefers-reduced-motion: no-preference) {
      .carousel-item {
        transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .rooms-section,
      .cta-section,
      .footer {
        opacity: 0;
        animation: fadeIn 0.8s ease-out forwards;
        animation-timeline: view();
        animation-range: entry 0% cover 30%;
      }

      @keyframes fadeIn {
        to {
          opacity: 1;
        }
      }
    }

    /* ========== ACCESSIBILITY ========== */
    @media (prefers-reduced-motion: reduce) {
      *,
      *::before,
      *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
      }
    }

    /* Focus states for keyboard navigation */
    .btn-modern:focus-visible,
    .social-link:focus-visible,
    .footer-links a:focus-visible {
      outline: 2px solid var(--light-azure);
      outline-offset: 4px;
    }
  </style>
@endsection

@section('content')
  <header class="masthead" style="background-image: url('{{ asset('assets/img/frontpages/img/test.jpg') }}');">
    <div class="container">
      <div class="masthead-subheading">
        <i class="mdi mdi-map-marker-outline"></i>
        San Roque Sogod Southern Leyte 6606
      </div>
      <h1 class="masthead-heading">Hotel De SLSU</h1>
      <p>
        Your hub for <b>Corporate</b> events, stay, and facilities
      </p>
      <div class="mt-4 d-flex flex-column flex-sm-row align-items-center justify-content-center btn-container">
        <a class="btn btn-primary btn-modern text-uppercase me-sm-2 mb-2 mb-sm-0" href="#booking">
          <i class="ri-calendar-line"></i> Book Your Stay
        </a>
        <a class="btn btn-outline-light btn-modern text-uppercase" href="#services">
          Tell Me More
        </a>
      </div>
    </div>
  </header>

  <section class="rooms-section" id="rooms">
    <div class="container text-center">
      <h2 class="section-heading">Our Rooms</h2>
      <p class="section-subheading">
        Discover comfort and luxury in every corner of our thoughtfully designed accommodations
      </p>

      <!-- Desktop Carousel (3 cards per slide) -->
      <div class="d-none d-lg-block">
        <div id="roomsCarouselDesktop" class="carousel slide">
          <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
              <div class="row g-4">
                <div class="col-lg-4">
                  @include('frontpages.room-card', [
                      'title' => 'Family Room',
                      'description' => 'Spacious comfort for the whole family',
                  ])
                </div>
                <div class="col-lg-4">
                  @include('frontpages.room-card', [
                      'title' => 'Standard Room',
                      'description' => 'Cozy and affordable excellence',
                  ])
                </div>
                <div class="col-lg-4">
                  @include('frontpages.room-card', [
                      'title' => 'Luxury Suite',
                      'description' => 'Indulge in premium amenities',
                  ])
                </div>
              </div>
            </div>
            <!-- Slide 2 -->
            <div class="carousel-item">
              <div class="row g-4">
                <div class="col-lg-4">
                  @include('frontpages.room-card', [
                      'title' => 'Deluxe Room',
                      'description' => 'Enhanced comfort and style',
                  ])
                </div>
                <div class="col-lg-4">
                  @include('frontpages.room-card', [
                      'title' => 'Twin Room',
                      'description' => 'Perfect for friends or colleagues',
                  ])
                </div>
                <div class="col-lg-4">
                  @include('frontpages.room-card', [
                      'title' => 'Executive Suite',
                      'description' => 'Business-class sophistication',
                  ])
                </div>
              </div>
            </div>
          </div>

          <button class="carousel-control-prev custom-carousel-btn" type="button"
            data-bs-target="#roomsCarouselDesktop" data-bs-slide="prev" aria-label="Previous slide">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next custom-carousel-btn" type="button"
            data-bs-target="#roomsCarouselDesktop" data-bs-slide="next" aria-label="Next slide">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
        </div>
      </div>

      <div class="d-block d-lg-none">
        <div id="roomsCarouselMobile" class="carousel slide">
          <div class="carousel-inner">
            <div class="carousel-item active">
              @include('frontpages.room-card', [
                  'title' => 'Family Room',
                  'description' => 'Spacious comfort for the whole family',
              ])
            </div>
            <div class="carousel-item">
              @include('frontpages.room-card', [
                  'title' => 'Standard Room',
                  'description' => 'Cozy and affordable excellence',
              ])
            </div>
            <div class="carousel-item">
              @include('frontpages.room-card', [
                  'title' => 'Luxury Suite',
                  'description' => 'Indulge in premium amenities',
              ])
            </div>
            <div class="carousel-item">
              @include('frontpages.room-card', [
                  'title' => 'Deluxe Room',
                  'description' => 'Enhanced comfort and style',
              ])
            </div>
            <div class="carousel-item">
              @include('frontpages.room-card', [
                  'title' => 'Twin Room',
                  'description' => 'Perfect for friends or colleagues',
              ])
            </div>
            <div class="carousel-item">
              @include('frontpages.room-card', [
                  'title' => 'Executive Suite',
                  'description' => 'Business-class sophistication',
              ])
            </div>
          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#roomsCarouselMobile"
            data-bs-slide="prev" aria-label="Previous room">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#roomsCarouselMobile"
            data-bs-slide="next" aria-label="Next room">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section" id="services">
    <div class="cta-overlay"></div>
    <div class="cta-content">
      <h2 class="cta-title">Your Comfort, Our Priority</h2>
      <p class="cta-description">
        Your perfect getaway is just a few clicks away. Experience hospitality redefined.
      </p>
      <a href="#booking" class="btn btn-primary btn-modern">
        <i class="ri-arrow-right-line"></i> Start Your Journey
      </a>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <h3 class="footer-title">Hotel De SLSU</h3>
          <p class="footer-text">
            Your hub for corporate events, comfortable stays, and exceptional facilities in Southern Leyte.
          </p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-link" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link" aria-label="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
          </div>
        </div>

        <div class="footer-column">
          <h4 class="footer-heading">Quick Links</h4>
          <ul class="footer-links">
            <li><a href="#about">About Us</a></li>
            <li><a href="#rooms">Rooms & Venue</a></li>
            <li><a href="#amenities">Amenities</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="#privacy">Privacy Policy</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h4 class="footer-heading">Contact Us</h4>
          <div class="contact-info">
            <div class="contact-item">
              <i class="fas fa-map-marker-alt"></i>
              <span>San Roque Sogod Southern Leyte 6606</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-phone"></i>
              <span>+63 123 131 32</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-envelope"></i>
              <span>hoteldeslsu@gmail.com</span>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 Hotel De SLSU. Developed by Michael & Renz. All rights reserved.</p>
      </div>
    </div>
  </footer>
@endsection
