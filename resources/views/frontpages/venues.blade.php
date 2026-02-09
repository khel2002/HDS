{{-- Venues Section with Photo Cards --}}
<section class="features-section" id="venues">
  <div class="container">
    <div class="section-header text-center">
      <h2 class="section-title">Our Venues</h2>
      <p class="section-description">
        Perfect Spaces for Every Occasion
      </p>
    </div>

    <div class="features-grid">
      {{-- Venue Card 1 --}}
      <div class="feature-card">
        <div class="venue-image-container">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Conference Hall" class="venue-image"
            loading="lazy">

          {{-- Optional Badge --}}
          {{-- <div class="venue-badge">Popular</div> --}}

          {{-- Capacity Badge --}}
          <div class="venue-capacity">
            <i class="fas fa-users"></i>
            <span>Up to 200 pax</span>
          </div>
        </div>

        <div class="feature-card-content">
          <h3 class="feature-title">CONFERENCE ROOM</h3>
          <p class="feature-description">
            Spacious hall perfect for conferences, seminars, and corporate events with modern facilities
          </p>
        </div>
      </div>

      {{-- Venue Card 2 --}}
      <div class="feature-card">
        <div class="venue-image-container">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Function Room" class="venue-image"
            loading="lazy">

          <div class="venue-capacity">
            <i class="fas fa-users"></i>
            <span>Up to 100 pax</span>
          </div>
        </div>

        <div class="feature-card-content">
          <h3 class="feature-title">BALLROOM</h3>
          <p class="feature-description">
            Grand venue for weddings, celebrations, and large-scale events
          </p>
        </div>
      </div>

      {{-- Venue Card 3 --}}
      <div class="feature-card">
        <div class="venue-image-container">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Banquet Hall" class="venue-image"
            loading="lazy">

          <div class="venue-capacity">
            <i class="fas fa-users"></i>
            <span>Up to 300 pax</span>
          </div>
        </div>

        <div class="feature-card-content">
          <h3 class="feature-title">CAFETERIA</h3>
          <p class="feature-description">

            Elegant space ideal for meetings, workshops, and intimate gatherings
          </p>
        </div>
      </div>

      {{-- Venue Card 4 --}}
      <div class="feature-card">
        <div class="venue-image-container">
          <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="Meeting Room" class="venue-image"
            loading="lazy">

          <div class="venue-capacity">
            <i class="fas fa-users"></i>
            <span>Up to 30 pax</span>
          </div>
        </div>

        <div class="feature-card-content">
          <h3 class="feature-title">COFFEE PROSE</h3>
          <p class="feature-description">
            Professional space equipped for board meetings and small group sessions
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
