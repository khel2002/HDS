@extends('layouts/sections/navbar/landingpagenav')
@section('title', $room->roomType->room_type_name . ' - Hotel De SLSU')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/materio.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

  <!-- Room Details Themed CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/room-details.css') }}">

  <style>
    /* Mobile Sticky Footer for Booking */
    .mobile-booking-footer {
      display: none;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: white;
      border-top: 1px solid #e5e7eb;
      padding: 1rem;
      z-index: 1000;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }

    .mobile-booking-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 100%;
      gap: 1rem;
    }

    .mobile-price-info {
      display: flex;
      flex-direction: column;
    }

    .mobile-price {
      font-size: 1.125rem;
      font-weight: 700;
      color: #222;
    }

    .mobile-price-unit {
      font-size: 0.875rem;
      color: #6b7280;
      font-weight: 400;
    }

    .mobile-reserve-btn {
      background: linear-gradient(135deg, #E61E4D 0%, #D70466 100%);
      color: white;
      border: none;
      padding: 0.875rem 2rem;
      border-radius: 0.5rem;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      white-space: nowrap;
      box-shadow: 0 2px 8px rgba(230, 30, 77, 0.3);
    }

    .mobile-reserve-btn:hover {
      background: linear-gradient(135deg, #D70466 0%, #C8035A 100%);
      box-shadow: 0 4px 12px rgba(230, 30, 77, 0.4);
      transform: translateY(-1px);
    }

    .mobile-reserve-btn:active {
      transform: translateY(0);
    }

    .mobile-reserve-btn:disabled {
      background: #9ca3af;
      cursor: not-allowed;
      box-shadow: none;
    }

    /* Hide sticky nav price section on mobile */
    @media (max-width: 991.98px) {
      .sticky-nav-price {
        display: none !important;
      }

      .mobile-booking-footer {
        display: block;
      }

      /* Add padding to content to prevent overlap with footer */
      .container-airbnb {
        padding-bottom: 90px;
      }

      /* Hide the desktop booking card on mobile */
      .booking-card {
        display: none;
      }

      /* Adjust content layout for mobile */
      .content-layout {
        display: block !important;
      }

      .main-info {
        max-width: 100% !important;
      }
    }

    /* Keep desktop booking card visible on larger screens */
    @media (min-width: 992px) {
      .mobile-booking-footer {
        display: none !important;
      }

      .booking-card {
        display: block;
      }
    }

    /* Tablet adjustments */
    @media (max-width: 767.98px) {
      .mobile-booking-content {
        gap: 0.75rem;
      }

      .mobile-price {
        font-size: 1rem;
      }

      .mobile-reserve-btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9375rem;
      }
    }

    /* Small mobile adjustments */
    @media (max-width: 575.98px) {
      .mobile-booking-footer {
        padding: 0.875rem 1rem;
      }

      .mobile-price {
        font-size: 0.9375rem;
      }

      .mobile-price-unit {
        font-size: 0.8125rem;
      }

      .mobile-reserve-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
      }
    }
  </style>
@endsection

@section('content')
  <!-- Sticky Navigation -->
  <nav class="sticky-nav" id="stickyNav">
    <div class="container-airbnb">
      <div class="sticky-nav-content">
        <div class="nav-tabs">
          <a href="#photos" class="nav-tab active">Photos</a>
          <a href="#amenities" class="nav-tab">Amenities</a>
          <a href="#location" class="nav-tab">Location</a>
        </div>
        <div class="sticky-nav-price">
          <div class="sticky-price">
            <strong>₱{{ number_format($room->roomType->rate_per_night, 0) }}</strong> / night
          </div>
          @if($room->status === 'available')
            <button class="sticky-reserve-btn" onclick="document.getElementById('booking').scrollIntoView({behavior: 'smooth', block: 'start'})">
              Reserve
            </button>
          @endif
        </div>
      </div>
    </div>
  </nav>

  <div class="container-airbnb">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="room-title-main">{{ $room->roomType->room_type_name }}</h1>
      <div class="header-actions">
        <button class="action-btn" onclick="window.history.back()">
          <i class="ri-arrow-left-line"></i> Back
        </button>
      </div>
    </div>

    <!-- Photo Grid -->
    <div class="photo-grid" id="photos">
      <div class="photo-grid-item">
        @if($room->image_path)
          <img src="{{ asset('storage/' . $room->image_path) }}" alt="{{ $room->roomType->room_type_name }}">
        @else
          <img src="{{ asset('assets/img/frontpages/img/default-room.jpg') }}" alt="{{ $room->roomType->room_type_name }}">
        @endif
        <button class="show-all-photos">
          <i class="ri-grid-line"></i>
          Show all photos
        </button>
      </div>
    </div>

    <!-- Main Content Layout -->
    <div class="content-layout">
      <!-- Left Column -->
      <div class="main-info">
        <!-- Room Info -->
        <div class="info-section">
          <div class="host-info">
            <div class="host-details">
              <h2>{{ $room->roomType->room_type_name }}</h2>
              <div class="room-specs">
                {{ $room->roomType->max_pax }} {{ $room->roomType->max_pax > 1 ? 'guests' : 'guest' }} ·
                Room {{ $room->room_id }} ·
                @if($room->status === 'available')
                  <span class="status-badge status-available">
                    <i class="ri-checkbox-circle-line"></i> Available
                  </span>
                @elseif($room->status === 'occupied')
                  <span class="status-badge status-occupied">
                    <i class="ri-user-location-line"></i> Occupied
                  </span>
                @else
                  <span class="status-badge status-maintenance">
                    <i class="ri-tools-line"></i> Maintenance
                  </span>
                @endif
              </div>
            </div>
          </div>

          <div class="badge-award">
            <div class="badge-icon">🏆</div>
            <div class="badge-content">
              <h4>Guest favorite</h4>
              <p>One of the most loved rooms at Hotel De SLSU</p>
            </div>
          </div>

          <div class="feature-list">
            <div class="feature-item">
              <div class="feature-icon"><i class="ri-door-open-line"></i></div>
              <div class="feature-content">
                <h4>Great check-in experience</h4>
                <p>Recent guests loved the smooth start to their stay.</p>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon"><i class="ri-home-smile-2-line"></i></div>
              <div class="feature-content">
                <h4>Room {{ $room->room_id }}</h4>
                <p>{{ $room->roomType->room_type_name }} with modern amenities.</p>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon"><i class="ri-calendar-check-line"></i></div>
              <div class="feature-content">
                <h4>Free cancellation before check-in</h4>
                <p>Get a full refund if you change your mind.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- About -->
        <div class="info-section">
          <h3 class="section-title-large">About this room</h3>
          <div class="description-text" id="roomDescription">
            {{ $room->roomType->description ?? 'Snuggle up in this calm, luxurious, modern room at Hotel De SLSU which is located in an understated area in Sogod, Southern Leyte that is easily accessible from the city. Perfect for travelers looking for reasonable-priced lodging.' }}
          </div>
          @php
            $description = $room->roomType->description ?? 'Snuggle up in this calm, luxurious, modern room at Hotel De SLSU which is located in an understated area in Sogod, Southern Leyte that is easily accessible from the city. Perfect for travelers looking for reasonable-priced lodging.';
            $wordCount = str_word_count($description);
          @endphp
          @if($wordCount > 50)
            <button class="show-more-btn" id="showMoreBtn" onclick="toggleDescription()">
              Show more <i class="ri-arrow-down-s-line"></i>
            </button>
          @endif
        </div>

        <!-- Amenities -->
        <div class="info-section" id="amenities">
          <h3 class="section-title-large">What this place offers</h3>
          @if(!empty($amenities) && count($amenities) > 0)
            <div class="amenities-grid">
              @foreach(array_slice($amenities, 0, 6) as $amenity)
                <div class="amenity-item">
                  <i class="ri-checkbox-circle-line"></i>
                  <span>{{ $amenity }}</span>
                </div>
              @endforeach
            </div>
            @if(count($amenities) > 6)
              <button class="show-amenities-btn">Show all {{ count($amenities) }} amenities</button>
            @endif
          @else
            <div class="amenities-grid">
              <div class="amenity-item">
                <i class="ri-wifi-line"></i>
                <span>Wi-Fi</span>
              </div>
              <div class="amenity-item">
                <i class="ri-tv-line"></i>
                <span>Television</span>
              </div>
              <div class="amenity-item">
                <i class="ri-temp-cold-line"></i>
                <span>Air conditioning</span>
              </div>
              <div class="amenity-item">
                <i class="ri-parking-box-line"></i>
                <span>Free parking</span>
              </div>
            </div>
          @endif
        </div>

        <!-- Location -->
        <div class="info-section" id="location">
          <h3 class="section-title-large">Where you'll be</h3>
          <p style="color: var(--bs-body-secondary-color); font-size: 1rem; margin-bottom: 1.5rem;">
            <i class="ri-map-pin-line"></i> Concepcion St, Sogod, Southern Leyte 6606, Philippines
          </p>

          <!-- Map Container -->
          <div class="map-container">
            <div id="map"></div>
          </div>

          <!-- Hidden map data - 9XRH+RX8, Concepcion St, Sogod, Southern Leyte -->
          <div id="mapData"
               data-lat="10.3922"
               data-lng="124.9798"
               data-name="Hotel De SLSU"
               style="display: none;">
          </div>

          <p style="color: var(--bs-body-secondary-color); font-size: 0.8125rem; margin-top: 1rem;">
            <i class="ri-information-line"></i>
            Exact location will be provided after booking confirmation
          </p>
        </div>
      </div>

      <!-- Right Column - Booking Card (Desktop Only) -->
      <div>
        <div class="booking-card" id="booking">
          <div class="price-header">
            <span class="price-large">₱{{ number_format($room->roomType->rate_per_night, 0) }}</span>
            <span class="price-unit">night</span>
          </div>

          <div class="booking-form">
            <div class="form-row">
              <div class="form-field">
                <label class="form-label">Check-in</label>
                <div class="form-value">Add date</div>
              </div>
              <div class="form-field">
                <label class="form-label">Checkout</label>
                <div class="form-value">Add date</div>
              </div>
            </div>
            <div class="guests-field" onclick="toggleGuestDropdown()">
              <label class="form-label">Guests</label>
              <div class="form-value" id="guestDisplay">1 guest</div>

              <!-- Guest Dropdown -->
              <div class="guest-dropdown" id="guestDropdown" onclick="event.stopPropagation()">
                <div class="guest-row">
                  <div class="guest-info">
                    <h4>Adults</h4>
                    <p>Age 13+</p>
                  </div>
                  <div class="guest-controls">
                    <button class="guest-btn" onclick="decrementGuest('adults')" id="adultsDecrement">
                      <i class="ri-subtract-line"></i>
                    </button>
                    <span class="guest-count" id="adultsCount">1</span>
                    <button class="guest-btn" onclick="incrementGuest('adults')" id="adultsIncrement">
                      <i class="ri-add-line"></i>
                    </button>
                  </div>
                </div>

                <div class="guest-row">
                  <div class="guest-info">
                    <h4>Children</h4>
                    <p>Ages 2–12</p>
                  </div>
                  <div class="guest-controls">
                    <button class="guest-btn" onclick="decrementGuest('children')" id="childrenDecrement">
                      <i class="ri-subtract-line"></i>
                    </button>
                    <span class="guest-count" id="childrenCount">0</span>
                    <button class="guest-btn" onclick="incrementGuest('children')" id="childrenIncrement">
                      <i class="ri-add-line"></i>
                    </button>
                  </div>
                </div>

                <div class="guest-row">
                  <div class="guest-info">
                    <h4>Infants</h4>
                    <p>Under 2</p>
                  </div>
                  <div class="guest-controls">
                    <button class="guest-btn" onclick="decrementGuest('infants')" id="infantsDecrement">
                      <i class="ri-subtract-line"></i>
                    </button>
                    <span class="guest-count" id="infantsCount">0</span>
                    <button class="guest-btn" onclick="incrementGuest('infants')" id="infantsIncrement">
                      <i class="ri-add-line"></i>
                    </button>
                  </div>
                </div>

                <div class="guest-note">
                  This place has a maximum of {{ $room->roomType->max_pax }} guests, not including infants. Pets aren't allowed.
                </div>

                <button class="close-dropdown-btn" onclick="closeGuestDropdown()">Close</button>
              </div>
            </div>
          </div>

          @if($room->status === 'available')
            <button class="reserve-btn">Reserve</button>
            <div class="charge-note">You won't be charged yet</div>

            <div class="price-breakdown">
              <div class="price-row">
                <span>₱{{ number_format($room->roomType->rate_per_night, 0) }} × 2 nights</span>
                <span>₱{{ number_format($room->roomType->rate_per_night * 2, 0) }}</span>
              </div>
              <div class="price-row total">
                <span>Total</span>
                <span>₱{{ number_format($room->roomType->rate_per_night * 2, 0) }}</span>
              </div>
            </div>
          @else
            <button class="reserve-btn" disabled>Currently {{ ucfirst($room->status) }}</button>
          @endif

          <a href="#" class="report-link">
            <i class="ri-flag-line"></i>
            Report this listing
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Sticky Footer (Mobile Only) -->
  <div class="mobile-booking-footer">
    <div class="mobile-booking-content">
      <div class="mobile-price-info">
        <span class="mobile-price">₱{{ number_format($room->roomType->rate_per_night, 0) }}</span>
        <span class="mobile-price-unit">per night</span>
      </div>
      @if($room->status === 'available')
        <button class="mobile-reserve-btn" onclick="openMobileBooking()">
          Reserve
        </button>
      @else
        <button class="mobile-reserve-btn" disabled>
          {{ ucfirst($room->status) }}
        </button>
      @endif
    </div>
  </div>

  <!-- Hidden input for max guests -->
  <input type="hidden" id="maxGuestsValue" value="{{ $room->roomType->max_pax }}">

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <!-- Room Details JS -->
  <script src="{{ asset('/js/roomdetailsjs/index_script.js') }}"></script>

  <script>
    // Function to handle mobile booking button click
    function openMobileBooking() {
      // You can either:
      // 1. Scroll to a booking section
      // 2. Open a modal with booking form
      // 3. Navigate to a booking page

      // For now, let's show an alert (replace with your booking logic)
      alert('Opening booking interface for ' + '{{ $room->roomType->room_type_name }}');

      // Example: Navigate to booking page


      // Example: Open a modal (if you have one)
      // $('#bookingModal').modal('show');
    }
  </script>
@endsection
