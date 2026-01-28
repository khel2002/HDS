  @extends('layouts/sections/navbar/landingpagenav')
  @section('title', 'Hotel De SLSU')

  @section('vendor-style')
    {{-- Materio core CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/materio.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">
  @endsection
  @section('content')
    <header class="masthead" style="background-image: url('{{ asset('assets/img/frontpages/img/test.jpg') }}'); ">
      <div class="container">
        <div class="masthead-subheading" style="font-size: 0.9rem;">
          <i class="mdi mdi-map-marker-outline"></i>
          San Roque Sogod Southern Leyte 6606
        </div>
        <div class="masthead-heading" style="font-size: 4rem;">Hotel De SLSU</div>
        <p>
          "Your hub for <b class="text-white">Corporate</b> events, stay, and facilities"
        </p>
        <div class="mt-4 d-flex flex-column flex-sm-row align-items-center justify-content-center">
          <a class="btn btn-primary btn-modern text-uppercase me-sm-2 mb-2 mb-sm-0" href="#booking">
            <i class="ri-calendar-line me-2"></i> Book Your Stay
          </a>
          <a class="btn btn-outline-light btn-modern text-uppercase" href="#services">
            Tell Me More
          </a>
        </div>
      </div>
    </header>

    <div class="rooms-section">
      <div class="container text-center">
        <h2 class="section-heading text-uppercase">Our Rooms</h2>

        <div id="carouselExampleCaptions" class="carousel slide mt-4">
          <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
              <img src="{{ asset('assets/img/frontpages/img/test.jpg') }}" class="d-block w-100" alt="Room 1">
              <div class="carousel-caption mb-5">
                <h5>Family Room</h5>
              </div>
              <div class="carousel-description mt-5">
                <p>Spacious room with two queen beds, ideal for families or groups.</p>
              </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
              <img src="{{ asset('assets/img/frontpages/img/test.jpg') }}" class="d-block w-100" alt="Room 2">
              <div class="carousel-caption mb-5">
                <h5>Family Room</h5>
              </div>
              <div class="carousel-description mt-5">
                <p>Spacious room with two queen beds, ideal for families or groups.</p>
              </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
              <img src="{{ asset('assets/img/frontpages/img/test.jpg') }}" class="d-block w-100" alt="Room 3">
              <div class="carousel-caption mb-5">
                <h5>Family Room</h5>
              </div>
              <div class="carousel-description mt-5">
                <p>Spacious room with two queen beds, ideal for families or groups.</p>
              </div>
            </div>
          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>

  @endsection
