@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Book ' . $room->roomType->room_type_name . ' - Hotel De SLSU')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/registration-form.css') }}">
@endsection

@section('content')
  <div class="checkout-container">
    @if(session('success'))
      <div class="alert alert-success">
        <i class="ri-checkbox-circle-line"></i>
        <span>{{ session('success') }}</span>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-error">
        <i class="ri-error-warning-line"></i>
        <span>{{ session('error') }}</span>
      </div>
    @endif

    <!-- Progress Steps -->
    <div class="checkout-steps">
      <div class="steps-wrapper">
        <div class="step active clickable" data-step="1">
          <div class="step-icon">
            <i class="ri-shopping-cart-line"></i>
          </div>
          <span class="step-label">Room</span>
        </div>
        <div class="step" data-step="2">
          <div class="step-icon">
            <i class="ri-file-list-3-line"></i>
          </div>
          <span class="step-label">Details</span>
        </div>
        <div class="step" data-step="3">
          <div class="step-icon">
            <i class="ri-bank-card-line"></i>
          </div>
          <span class="step-label">Payment</span>
        </div>
        <div class="step" data-step="4">
          <div class="step-icon">
            <i class="ri-check-line"></i>
          </div>
          <span class="step-label">Confirmation</span>
        </div>
      </div>
    </div>

    <form action="{{ route('reservation.store') }}" method="POST" id="reservationForm">
      @csrf
      <input type="hidden" name="room_id" value="{{ $room->room_id }}">

      <div class="checkout-content">
        <!-- Main Content -->
        <div class="checkout-main">
          <!-- Step 1: Room Details -->
          <div class="step-content active" id="step1">
            <h2 class="section-title">Your Reservation (1 Room)</h2>

            <div class="room-summary">
              <div class="room-card">
                <img src="{{ asset('storage/' . $room->image_path) }}" alt="{{ $room->roomType->room_type_name }}" class="room-image">
                <div class="room-details">
                  <h3 class="room-name">{{ $room->roomType->room_type_name }}</h3>
                  <div class="room-meta">
                    <span><i class="ri-door-line"></i> Room {{ $room->room_number }}</span>
                    <span><i class="ri-user-line"></i> Up to {{ $room->roomType->max_pax }} guests</span>
                    <span class="free-badge">Available</span>
                  </div>
                  <p style="color: #64748b; font-size: 0.875rem; margin: 0.5rem 0;">{{ $room->roomType->description }}</p>
                  <div class="room-price">₱{{ number_format($room->roomType->rate_per_night, 0) }} <span style="font-size: 0.875rem; color: #64748b; font-weight: 400;">per night</span></div>
                </div>
              </div>

              <div>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Stay Details</h3>
                <div class="stay-details-grid">
                  <div class="form-group">
                    <label>Check-in Date <span class="required">*</span></label>
                    <input
                      type="date"
                      name="arrival_date"
                      id="arrivalDate"
                      class="form-control @error('arrival_date') is-invalid @enderror"
                      value="{{ old('arrival_date', request('arrival_date')) }}"
                      min="{{ date('Y-m-d') }}"
                      required
                    >
                    @error('arrival_date')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-group">
                    <label>Checkout Date <span class="required">*</span></label>
                    <input
                      type="date"
                      name="departure_date"
                      id="departureDate"
                      class="form-control @error('departure_date') is-invalid @enderror"
                      value="{{ old('departure_date', request('departure_date')) }}"
                      min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                      required
                    >
                    @error('departure_date')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-group">
                    <label>Adults <span class="required">*</span></label>
                    <input
                      type="number"
                      name="adults"
                      id="adults"
                      class="form-control @error('adults') is-invalid @enderror"
                      value="{{ old('adults', request('adults', 1)) }}"
                      min="1"
                      max="{{ $room->roomType->max_pax }}"
                      required
                    >
                    @error('adults')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-group">
                    <label>Children</label>
                    <input
                      type="number"
                      name="children"
                      id="children"
                      class="form-control @error('children') is-invalid @enderror"
                      value="{{ old('children', request('children', 0)) }}"
                      min="0"
                      max="{{ $room->roomType->max_pax - 1 }}"
                      required
                    >
                    @error('children')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                  <label>Purpose of Visit</label>
                  <textarea
                    name="purpose"
                    class="form-control"
                    placeholder="e.g., Vacation, Business trip, Family gathering"
                  >{{ old('purpose') }}</textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Guest Details -->
          <div class="step-content" id="step2">
            <h2 class="section-title">Guest Information</h2>

            <div class="form-grid">
              <div class="form-group">
                <label>First Name <span class="required">*</span></label>
                <input
                  type="text"
                  name="first_name"
                  class="form-control @error('first_name') is-invalid @enderror"
                  value="{{ old('first_name') }}"
                  required
                >
                @error('first_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label>Middle Name</label>
                <input
                  type="text"
                  name="middle_name"
                  class="form-control"
                  value="{{ old('middle_name') }}"
                >
              </div>

              <div class="form-group">
                <label>Last Name <span class="required">*</span></label>
                <input
                  type="text"
                  name="last_name"
                  class="form-control @error('last_name') is-invalid @enderror"
                  value="{{ old('last_name') }}"
                  required
                >
                @error('last_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label>Date of Birth <span class="required">*</span></label>
                <input
                  type="date"
                  name="dob"
                  class="form-control @error('dob') is-invalid @enderror"
                  value="{{ old('dob') }}"
                  max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                  required
                >
                @error('dob')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input
                  type="email"
                  name="email"
                  class="form-control @error('email') is-invalid @enderror"
                  value="{{ old('email') }}"
                  required
                >
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-hint">We'll send your temporary account details to this email</small>
              </div>

              <div class="form-group">
                <label>Contact Number <span class="required">*</span></label>
                <input
                  type="tel"
                  name="contact_number"
                  class="form-control @error('contact_number') is-invalid @enderror"
                  value="{{ old('contact_number') }}"
                  placeholder="+63 912 345 6789"
                  required
                >
                @error('contact_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Step 3: Payment -->
          <div class="step-content" id="step3">
            <div class="offers-banner">
              <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">×</button>
              <div class="offers-title">
                <i class="ri-percent-line"></i>
                Available Offers
              </div>
              <ul class="offers-list">
                <li>10% Instant Discount on select payment methods</li>
                <li>25% Cashback Voucher up to ₱60 on first reservation</li>
              </ul>
            </div>

            <h2 class="section-title">Payment Method</h2>

            <div class="payment-tabs">
              <button type="button" class="payment-tab active" data-payment="cash">Cash On Arrival</button>
              <button type="button" class="payment-tab" data-payment="online">Online Payment</button>
            </div>

            <div class="payment-content active" id="cash-payment">
              <div style="text-align: center; padding: 3rem 2rem;">
                <i class="ri-money-dollar-circle-line" style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">Cash Payment Selected</h3>
                <p style="color: #64748b; max-width: 500px; margin: 0 auto;">
                  You'll pay the reservation fee (₱500) upon check-in. The remaining balance will be settled during your stay.
                </p>
              </div>
              <input type="hidden" name="payment_method" value="cash" id="cashPaymentInput">
            </div>

            <div class="payment-content" id="online-payment">
              <div class="card-inputs">
                <div class="form-group">
                  <label>Card Number</label>
                  <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                </div>

                <div class="card-row">
                  <div class="form-group">
                    <label>Name on Card</label>
                    <input type="text" class="form-control" placeholder="John Doe">
                  </div>

                  <div class="form-group">
                    <label>Expiry</label>
                    <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                  </div>

                  <div class="form-group">
                    <label>CVV <i class="ri-information-line" style="color: #94a3b8; cursor: help;" title="3-digit security code on the back of your card"></i></label>
                    <input type="text" class="form-control" placeholder="123" maxlength="3">
                  </div>
                </div>

                <div class="save-card">
                  <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                  </label>
                  <span style="font-size: 0.9375rem; color: #475569;">Save Card for future billing?</span>
                </div>
              </div>
              <input type="hidden" name="payment_method" value="online" id="onlinePaymentInput" disabled>
            </div>

            <div class="payment-info">
              <div class="payment-info-title">
                <i class="ri-information-line"></i>
                Payment Details
              </div>
              <ul>
                <li>Pay <strong>₱500</strong> reservation fee now to secure your booking</li>
                <li>Remaining balance of <strong id="balanceAmountText2">₱0</strong> will be paid <span id="paymentMethodText">upon check-in</span></li>
              </ul>
            </div>
          </div>

          <!-- Form Actions - Aligned with sidebar button -->
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" id="backBtn" style="display: none;">
              <i class="ri-arrow-left-line"></i> Back
            </button>
            <button type="button" class="btn btn-primary" id="nextStepBtn">
              Continue <i class="ri-arrow-right-line"></i>
            </button>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="checkout-sidebar">
          <div class="sidebar-section" id="deliveryEstimate" style="display: none;">
            <div class="sidebar-title">Estimated Arrival</div>
            <div class="delivery-info">
              <div class="delivery-item">
                <img src="{{ asset('storage/' . $room->image_path) }}" alt="{{ $room->roomType->room_type_name }}">
                <div class="delivery-item-info">
                  <div class="delivery-item-name">{{ $room->roomType->room_type_name }}</div>
                  <div class="delivery-item-date" id="checkInDisplay">--</div>
                </div>
              </div>
            </div>
          </div>

          <div class="sidebar-section">
            <div class="sidebar-title">Price Summary</div>

            <div class="price-row">
              <span>₱{{ number_format($room->roomType->rate_per_night, 0) }} × <span id="priceNights">0</span> night(s)</span>
              <span class="price-value" id="subtotal">₱0</span>
            </div>

            <div class="price-row">
              <span>Reservation Fee</span>
              <span class="price-value" style="color: #10b981;">₱500</span>
            </div>

            <div class="price-row total">
              <span>Total Amount</span>
              <span id="totalAmount">₱0</span>
            </div>
          </div>

          <div class="sidebar-section" id="guestAddressDisplay" style="display: none;">
            <div class="sidebar-title">Guest Details</div>
            <div class="address-display">
              <span class="address-label">Primary Guest</span>
              <div class="address-name" id="guestNameDisplay">--</div>
              <div class="address-text">
                <div id="guestEmailDisplay">--</div>
                <div id="guestPhoneDisplay">--</div>
              </div>
              <button type="button" class="change-address" onclick="goToStep(2)">
                <i class="ri-edit-line"></i> Edit details
              </button>
            </div>
          </div>

          <div class="sidebar-section">
            <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 1rem;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.875rem; color: #64748b;">Pay Now</span>
                <span style="font-weight: 600; color: #10b981;">₱500</span>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="font-size: 0.875rem; color: #64748b;">Remaining Balance</span>
                <span style="font-weight: 600;" id="balanceAmount">₱0</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Pass PHP variables to JavaScript
    window.reservationData = {
      ratePerNight: {{ $room->roomType->rate_per_night }},
      maxPax: {{ $room->roomType->max_pax }},
      roomId: {{ $room->room_id }},
      prefilledNights: {{ $prefilledNights ?? 0 }},
      checkAvailabilityUrl: '{{ route("reservation.check-availability") }}',
      csrfToken: '{{ csrf_token() }}'
    };
  </script>
  <script src="{{ asset('/js/reservationjs/index_scripts.js') }}"></script>
@endsection
