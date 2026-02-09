@extends('layouts/sections/navbar/landingpagenav')
@section('title', $room->roomType->room_type_name . ' - Hotel De SLSU')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

  <!-- FullCalendar CSS -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/main.min.css' rel='stylesheet' />

  <!-- Room Details CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/room-details.css') }}">
@endsection

@section('content')
  <!-- Sticky Navigation -->
  <nav class="sticky-nav" id="stickyNav">
    <div class="container-airbnb">
      <div class="sticky-nav-content">
        <div class="nav-tabs">
          <a href="#photos" class="nav-tab active">Photos</a>
          <a href="#amenities" class="nav-tab">Amenities</a>
          <a href="#availability" class="nav-tab">Availability</a>
          <a href="#location" class="nav-tab">Location</a>
        </div>

        </div>
      </div>
    </div>
  </nav>

  <div class="container-airbnb">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="room-title-main">{{ $room->roomType->room_type_name }}</h1>
      <div class="header-actions">
        <a href="{{ route('frontpage.index') }}" class="action-btn">
          <i class="ri-arrow-left-line"></i> Back
        </a>
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
        <!-- Room Info Card -->
        <div class="content-card">
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
            </div>
          </div>
        </div>

        <!-- About Card -->
        <div class="content-card">
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
        </div>

        <!-- Amenities Card -->
        <div class="content-card" id="amenities">
          <div class="info-section">
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
                <button class="show-amenities-btn" onclick="openAmenitiesModal()">Show all {{ count($amenities) }} amenities</button>
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
              <button class="show-amenities-btn" onclick="openAmenitiesModal()">Show all amenities</button>
            @endif
          </div>
        </div>

        <!-- Availability Calendar Card -->
        <div class="content-card" id="availability">
          <div class="info-section">
            <h3 class="section-title-large">Room Availability Schedule</h3>
            <p style="color: var(--bs-body-secondary-color); font-size: 1rem; margin-bottom: 1.5rem;">
              <i class="ri-calendar-line"></i> View room bookings and availability in real-time
            </p>
            <div id='calendar'></div>
          </div>
        </div>

        <!-- Location Card -->
        <div class="content-card" id="location">
          <div class="info-section">
            <h3 class="section-title-large">Where you'll be</h3>
            <p style="color: var(--bs-body-secondary-color); font-size: 1rem; margin-bottom: 1.5rem;">
              <i class="ri-map-pin-line"></i> Concepcion St, Sogod, Southern Leyte 6606, Philippines
            </p>

            <div class="map-container">
              <div id="map"></div>
            </div>

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
              <div class="form-field form-field-clickable" id="checkinField">
                <label class="form-label">Check-in</label>
                <div class="form-value" id="checkinDisplay">Add date</div>
              </div>
              <div class="form-field form-field-clickable" id="checkoutField">
                <label class="form-label">Checkout</label>
                <div class="form-value" id="checkoutDisplay">Add date</div>
              </div>
            </div>

            <div class="guests-field" onclick="toggleGuestDropdown()">
              <label class="form-label">Guests</label>
              <div class="form-value" id="guestDisplay">1 guest</div>

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
            <button class="reserve-btn" id="desktopReserveBtn">Reserve</button>
            <div class="charge-note">You won't be charged yet</div>

            <div class="price-breakdown">
              <div class="price-row">
                <span>₱{{ number_format($room->roomType->rate_per_night, 0) }} × <span id="nightsCount">0</span> night<span id="nightsPlural"></span></span>
                <span id="subtotalPrice">₱0</span>
              </div>
              <div class="price-row total">
                <span>Total</span>
                <span id="totalPrice">₱0</span>
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

  <!-- Desktop Calendar Modal -->
  <div class="desktop-calendar-modal" id="desktopCalendarModal">
    <div class="desktop-calendar-content">
      <div class="desktop-calendar-header">
        <h3>Select dates</h3>
        <button class="close-desktop-calendar-btn" onclick="closeDesktopCalendar()">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="desktop-calendar-body">
        <div class="date-selection-display">
          <div class="date-selection-field" id="desktopCheckinField">
            <div class="date-selection-label">Check-in</div>
            <div class="date-selection-value placeholder" id="desktopCheckinValue">Select date</div>
          </div>
          <div class="date-selection-field" id="desktopCheckoutField">
            <div class="date-selection-label">Checkout</div>
            <div class="date-selection-value placeholder" id="desktopCheckoutValue">Select date</div>
          </div>
        </div>

        <div id="desktopCalendarPicker"></div>
      </div>

      <div class="desktop-calendar-footer">
        <button class="clear-desktop-dates-btn" onclick="clearDesktopDates()">Clear dates</button>
        <button class="save-desktop-dates-btn" id="saveDesktopDatesBtn" disabled onclick="saveDesktopDates()">
          Save
        </button>
      </div>
    </div>
  </div>

  <<!-- Amenities Modal -->
  <div class="amenities-modal" id="amenitiesModal">
    <div class="amenities-modal-content">
      <div class="amenities-modal-header">
        <button class="close-amenities-btn" onclick="closeAmenitiesModal()">
          <i class="ri-close-line"></i>
        </button>
        <h2>What this place offers</h2>
      </div>

      <div class="amenities-modal-body">
        @if(!empty($amenities) && count($amenities) > 0)
          @php
            // Categorize amenities based on your actual database amenities
            $categorized = [
              'Bedroom' => [],
              'Bathroom' => [],
              'Entertainment' => [],
              'Internet and connectivity' => [],
              'Climate control' => [],
              'Room amenities' => [],
              'Spaces' => [],
              'View' => []
            ];

            // Icon mapping for your specific amenities
            $iconMap = [
              // Bed types
              'double size bed' => 'ri-hotel-bed-line',
              'two double beds' => 'ri-hotel-bed-line',
              'three single beds' => 'ri-hotel-bed-line',
              'king size bed' => 'ri-hotel-bed-line',
              'bunk beds' => 'ri-hotel-bed-line',

              // Bathroom
              'shower bathroom' => 'ri-drop-line',
              'bathroom' => 'ri-door-line',

              // Entertainment
              'flat-screen tv' => 'ri-tv-line',
              'tv' => 'ri-tv-line',

              // Internet
              'wi-fi access' => 'ri-wifi-line',
              'wifi' => 'ri-wifi-line',

              // Climate
              'air conditioning' => 'ri-temp-cold-line',
              'air purifier' => 'ri-contrast-drop-2-line',

              // Room amenities
              'work desk' => 'ri-table-2',
              'desk' => 'ri-table-2',
              'mini fridge' => 'ri-fridge-line',
              'fridge' => 'ri-fridge-line',

              // Spaces
              'living room' => 'ri-sofa-line',

              // View
              'relaxing view' => 'ri-landscape-line',
              'view' => 'ri-landscape-line'
            ];

            // Categorize each amenity
            foreach ($amenities as $amenity) {
              $amenityLower = strtolower($amenity);

              // Categorization based on your actual amenities
              if (preg_match('/(bed|beds)/i', $amenityLower)) {
                $categorized['Bedroom'][] = $amenity;
              }
              elseif (preg_match('/(shower|bathroom)/i', $amenityLower)) {
                $categorized['Bathroom'][] = $amenity;
              }
              elseif (preg_match('/(tv|television|screen)/i', $amenityLower)) {
                $categorized['Entertainment'][] = $amenity;
              }
              elseif (preg_match('/(wi-fi|wifi|internet)/i', $amenityLower)) {
                $categorized['Internet and connectivity'][] = $amenity;
              }
              elseif (preg_match('/(air conditioning|air purifier|climate)/i', $amenityLower)) {
                $categorized['Climate control'][] = $amenity;
              }
              elseif (preg_match('/(desk|fridge|mini)/i', $amenityLower)) {
                $categorized['Room amenities'][] = $amenity;
              }
              elseif (preg_match('/(living room|lounge|sitting)/i', $amenityLower)) {
                $categorized['Spaces'][] = $amenity;
              }
              elseif (preg_match('/(view|scenic|relaxing)/i', $amenityLower)) {
                $categorized['View'][] = $amenity;
              }
            }

            // Function to get icon for amenity
            function getAmenityIcon($amenity, $iconMap) {
              $amenityLower = strtolower($amenity);

              // Direct match
              if (isset($iconMap[$amenityLower])) {
                return $iconMap[$amenityLower];
              }

              // Partial match
              foreach ($iconMap as $keyword => $icon) {
                if (strpos($amenityLower, $keyword) !== false) {
                  return $icon;
                }
              }

              return 'ri-checkbox-circle-line'; // default icon
            }
          @endphp

          @foreach($categorized as $category => $items)
            @if(count($items) > 0)
              <div class="amenity-section">
                <h3 class="amenity-section-title">{{ $category }}</h3>
                <div class="amenity-list">
                  @foreach($items as $amenity)
                    <div class="amenity-list-item">
                      <i class="{{ getAmenityIcon($amenity, $iconMap) }}"></i>
                      <span>{{ $amenity }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          @endforeach
        @else
          <!-- Default display if no amenities in database -->
          <div class="amenity-section">
            <h3 class="amenity-section-title">Standard amenities</h3>
            <div class="amenity-list">
              <div class="amenity-list-item">
                <i class="ri-hotel-bed-line"></i>
                <span>Comfortable bed</span>
              </div>
              <div class="amenity-list-item">
                <i class="ri-drop-line"></i>
                <span>Private bathroom</span>
              </div>
              <div class="amenity-list-item">
                <i class="ri-wifi-line"></i>
                <span>Free Wi-Fi</span>
              </div>
              <div class="amenity-list-item">
                <i class="ri-temp-cold-line"></i>
                <span>Air conditioning</span>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Mobile Sticky Footer -->
  <div class="mobile-booking-footer">
    <div class="mobile-booking-content">
      <div class="mobile-price-info">
        <span class="mobile-price" id="mobilePrice">₱{{ number_format($room->roomType->rate_per_night, 0) }}</span>
        <span class="mobile-price-unit" id="mobilePriceUnit">per night</span>
      </div>
      @if($room->status === 'available')
        <button class="mobile-reserve-btn" id="mobileReserveBtn" onclick="openMobileBooking()">
          Choose dates
        </button>
      @else
        <button class="mobile-reserve-btn" disabled>
          {{ ucfirst($room->status) }}
        </button>
      @endif
    </div>
  </div>

  <!-- Mobile Calendar Modal -->
  <div class="calendar-modal" id="calendarModal">
    <div class="calendar-modal-content">
      <div class="calendar-header">
        <h3>Select dates</h3>
        <button class="close-calendar-btn" onclick="closeCalendarModal()">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="calendar-body">
        <div class="date-range-display">
          <div class="date-field" id="mobileCheckinField">
            <div class="date-label">Check-in</div>
            <div class="date-value placeholder" id="mobileCheckinDisplay">Add date</div>
          </div>
          <div class="date-field" id="mobileCheckoutField">
            <div class="date-label">Checkout</div>
            <div class="date-value placeholder" id="mobileCheckoutDisplay">Add date</div>
          </div>
        </div>

        <div id="mobileCalendar"></div>
      </div>

      <div class="calendar-footer">
        <button class="clear-dates-btn" onclick="clearDates()">Clear dates</button>
        <button class="calendar-save-btn" id="saveDatesBtn" disabled onclick="saveDates()">
          Save
        </button>
      </div>
    </div>
  </div>

  <!-- Hidden inputs -->
  <input type="hidden" id="maxGuestsValue" value="{{ $room->roomType->max_pax }}">
  <input type="hidden" id="ratePerNight" value="{{ $room->roomType->rate_per_night }}">
  <input type="hidden" id="roomId" value="{{ $room->room_id }}">
@endsection

@section('vendor-script')
  <!-- Leaflet JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <!-- FullCalendar JS -->
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js'></script>

  <!-- Room Details JS -->
  <script src="{{ asset('/js/roomdetailsjs/index_script.js') }}"></script>
@endsection
