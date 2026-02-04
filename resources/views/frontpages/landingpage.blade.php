@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Hotel De SLSU')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/materio.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
  {{-- Hero Header Section --}}
  <header class="masthead" style="background-image: url('{{ asset('assets/img/frontpages/img/header-bg.jpg') }}');">
    <div class="container">
      <div class="masthead-content">
        {{-- Location --}}
        <div class="masthead-subheading">
          <i class="mdi mdi-map-marker-outline"></i>
          <span>San Roque Sogod Southern Leyte 6606</span>
        </div>

        {{-- Brand Logo & Title --}}
        <div class="masthead-brand">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Hotel De SLSU Logo" class="brand-logo">
          <div class="masthead-heading">
            <span class="top-text">Hotel De</span>
            <span class="bottom-text">SLSU</span>
          </div>
        </div>

        {{-- Tagline --}}
        <p class="masthead-tagline">
          Your hub for <strong class="text-white">Corporate</strong> events, stay, and facilities
        </p>

        {{-- CTA Button --}}
        <div class="masthead-cta">
          <a class="btn btn-primary btn-modern text-uppercase" href="#booking">
            <i class="ri-calendar-line me-2"></i> Book Your Stay
          </a>
        </div>
      </div>
    </div>
  </header>

  {{-- Rooms Section --}}
  <section class="rooms-section" id="rooms">
    <div class="container">
      <div class="section-header text-center">
        <h2 class="section-heading text-uppercase">Our Rooms</h2>
        <p class="section-subtitle">Experience comfort and luxury in every detail</p>
      </div>

      {{-- Desktop Carousel (3 items per slide) --}}
      <div class="d-none d-lg-block">
        <div id="roomsCarouselDesktop" class="carousel slide mt-5">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <div class="row g-4">
                @foreach ($roomsFirst as $room)
                  <div class="col-lg-4">
                    @include('frontpages.room-card', [
                        'room_type_name' => $room->roomType->room_type_name,
                        'description' => $room->roomType->description,
                        'image' => $room->image_path,
                    ])
                  </div>
                @endforeach
              </div>
            </div>
            <div class="carousel-item">
              <div class="row g-4">
                @foreach ($roomsSecond as $room)
                  <div class="col-lg-4">
                    @include('frontpages.room-card', [
                        'room_type_name' => $room->roomType->room_type_name,
                        'description' => $room->roomType->description,
                        'image' => $room->image_path,
                    ])
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#roomsCarouselDesktop"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#roomsCarouselDesktop"
            data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>

      {{-- Mobile Carousel (1 item per slide) --}}
      <div class="d-block d-lg-none">
        <div id="roomsCarouselMobile" class="carousel slide mt-4">
          <div class="carousel-inner">
            <div class="carousel-item active">
              @foreach ($roomsMobFirst as $room)
                @include('frontpages.room-card', [
                    'room_type_name' => $room->roomType->room_type_name,
                    'description' => $room->roomType->description,
                    'image' => $room->image_path,
                ])
              @endforeach
            </div>
            @foreach ($roomsMobSecond as $room)
              <div class="carousel-item">
                @include('frontpages.room-card', [
                    'room_type_name' => $room->roomType->room_type_name,
                    'description' => $room->roomType->description,
                    'image' => $room->image_path,
                ])
              </div>
            @endforeach
          </div>

          <button class="carousel-control-prev custom-carousel-btn-mobile" type="button"
            data-bs-target="#roomsCarouselMobile" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next custom-carousel-btn-mobile" type="button"
            data-bs-target="#roomsCarouselMobile" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>
  </section>

  {{-- Venues/Amenities Section --}}
  <section class="features-section" id="amenities">
    <div class="container">
      <div class="section-header text-center">
        <h2 class="section-title">Venues & Amenities</h2>
        <p class="section-description">One Destination, Infinite Occasions</p>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-dumbbell"></i>
          </div>
          <h3 class="feature-title">Fitness Center</h3>
          <p class="feature-description">
            State-of-the-art gym with personal trainers available 24/7
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-spa"></i>
          </div>
          <h3 class="feature-title">Luxury Spa</h3>
          <p class="feature-description">
            Rejuvenate with our comprehensive spa and wellness treatments
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-car"></i>
          </div>
          <h3 class="feature-title">Valet Parking</h3>
          <p class="feature-description">
            Complimentary valet service for all our valued guests
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3 class="feature-title">24/7 Security</h3>
          <p class="feature-description">
            Your safety is our priority with round-the-clock security
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Call to Action Section --}}
  <section class="cta-section">
    <div class="cta-overlay"></div>
    <div class="cta-content">
      <h2 class="cta-title">Your Comfort Our Priority</h2>
      <p class="cta-description">
        Your Perfect Getaway is Just a Few Clicks Away
      </p>
      <a href="#booking" class="btn btn-light btn-lg mt-3">
        <i class="ri-calendar-check-line me-2"></i>
        Reserve Now
      </a>
    </div>
  </section>

  {{-- Footer --}}
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        {{-- About Column --}}
        <div class="footer-column">
          <h3 class="footer-title">Hotel De SLSU</h3>
          <p class="footer-text">
            Your hub for Corporate events, stay, and facilities.
          </p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="Facebook">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="#" class="social-link" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link" aria-label="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
          </div>
        </div>

        {{-- Quick Links Column --}}
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

        {{-- Contact Column --}}
        <div class="footer-column">
          <h4 class="footer-heading">Contact Us</h4>
          <div class="contact-info">
            <div class="contact-item">
              <i class="fas fa-map-marker-alt"></i>
              <span>San Roque Sogod Southern Leyte 6606</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-phone"></i>
              <span>12313132</span>
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
