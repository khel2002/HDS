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
      <input type="hidden" name="rooms" id="roomsData">

      <div class="checkout-content">
        <!-- Main Content -->
        <div class="checkout-main">
          <!-- Step 1: Room Selection -->
          <div class="step-content active" id="step1">
            <h2 class="section-title">Your Reservation</h2>

            <!-- Selected Rooms List -->
            <div id="selectedRoomsList">
              <!-- Initial room -->
              <div class="room-card selected-room" data-room-id="{{ $room->room_id }}" data-rate="{{ $room->roomType->rate_per_night }}">
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
                <button type="button" class="remove-room-btn" onclick="removeRoom(this)" style="display: none;">
                  <i class="ri-close-line"></i>
                </button>
              </div>
            </div>

            <!-- Add More Rooms Button -->
            <button type="button" class="btn btn-outline" id="addMoreRoomsBtn" onclick="showAvailableRooms()">
              <i class="ri-add-line"></i> Add Another Room
            </button>

            <!-- Available Rooms Modal Content (Initially Hidden) -->
            <div id="availableRoomsSection" style="display: none; margin-top: 1.5rem;">
              <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Available Rooms</h3>
              <div id="availableRoomsList" class="available-rooms-grid">
                <!-- Will be populated via JavaScript -->
              </div>
            </div>
          </div>

          <!-- Step 2: Stay Details & Guest Information -->
          <div class="step-content" id="step2">
            <h2 class="section-title">Stay & Guest Information</h2>

            <!-- Stay Details -->
            <div style="margin-bottom: 2rem;">
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

            <!-- Guest Information -->
            <div>
              <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Primary Guest Information</h3>
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
          </div>

          <!-- Step 3: Payment -->
          <div class="step-content" id="step3">
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
                  You'll pay the reservation fee (₱500 per room) upon check-in. The remaining balance will be settled during your stay.
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
                <li>Pay <strong id="totalReservationFeeText">₱500</strong> reservation fee now to secure your booking</li>
                <li>Remaining balance of <strong id="balanceAmountText2">₱0</strong> will be paid <span id="paymentMethodText">upon check-in</span></li>
              </ul>
            </div>
          </div>

          <!-- Step 4: Confirmation -->
          <div class="step-content" id="step4">
            @if(session('payment_success'))
              <div class="success-header" style="text-align: center; padding: 3rem 2rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; color: white; margin-bottom: 2rem;">
                <div class="success-icon" style="font-size: 4rem; margin-bottom: 1rem;">
                  <i class="ri-checkbox-circle-fill"></i>
                </div>
                <h2 class="success-title" style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Reservation Confirmed!</h2>
                <p class="success-subtitle" style="font-size: 1.125rem; opacity: 0.95;">
                  @if(session('payment_method') === 'online')
                    Your payment has been processed successfully
                  @else
                    Your reservation has been created successfully
                  @endif
                </p>
              </div>

              @if(session('reservation_data'))
                @php
                  $reservationData = session('reservation_data');
                  $credentials = session('temp_credentials');
                @endphp

                <!-- Reservation Details -->
                <div class="info-card" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                  <div class="info-card-title" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ri-file-list-3-line"></i>
                    Reservation Details
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Reservation ID</span>
                    <span style="color: #1e293b; font-weight: 600;">#{{ $reservationData['reservation_id'] }}</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Room Type</span>
                    <span style="color: #1e293b; font-weight: 600;">{{ $reservationData['room_type_name'] }}</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Room Number</span>
                    <span style="color: #1e293b; font-weight: 600;">{{ $reservationData['room_number'] }}</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Check-in</span>
                    <span style="color: #1e293b; font-weight: 600;">{{ date('F d, Y', strtotime($reservationData['arrival_date'])) }}</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Check-out</span>
                    <span style="color: #1e293b; font-weight: 600;">{{ date('F d, Y', strtotime($reservationData['departure_date'])) }}</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0;">
                    <span style="color: #64748b; font-weight: 500;">Number of Nights</span>
                    <span style="color: #1e293b; font-weight: 600;">{{ $reservationData['no_nights'] }} night{{ $reservationData['no_nights'] > 1 ? 's' : '' }}</span>
                  </div>
                </div>

                <!-- Payment Information -->
                <div class="info-card" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                  <div class="info-card-title" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ri-money-dollar-circle-line"></i>
                    Payment Information
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Payment Method</span>
                    <span style="color: #1e293b; font-weight: 600;">
                      @if(session('payment_method') === 'online')
                        <span style="background: #dcfce7; color: #166534; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem;">
                          <i class="ri-bank-card-line"></i> Online Payment
                        </span>
                      @else
                        <span style="background: #fef3c7; color: #92400e; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem;">
                          <i class="ri-cash-line"></i> Cash on Arrival
                        </span>
                      @endif
                    </span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">Total Amount</span>
                    <span style="color: #1e293b; font-weight: 600;">₱{{ number_format($reservationData['total_amount'], 2) }}</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-weight: 500;">
                      @if(session('payment_method') === 'online')
                        Paid (Reservation Fee)
                      @else
                        Reservation Fee (Pay on Arrival)
                      @endif
                    </span>
                    <span style="color: #10b981; font-weight: 600;">₱500.00</span>
                  </div>

                  <div class="info-row" style="display: flex; justify-content: space-between; padding: 1rem 0;">
                    <span style="color: #64748b; font-weight: 500;">Remaining Balance</span>
                    <span style="color: #f59e0b; font-weight: 600;">₱{{ number_format($reservationData['balance'], 2) }}</span>
                  </div>
                </div>

                <!-- Account Credentials -->
                @if($credentials)
                  <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem;">
                    <div style="color: #856404; font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                      <i class="ri-lock-password-line"></i>
                      Your Temporary Account
                    </div>
                    <p style="margin-bottom: 1.5rem; color: #856404;">
                      <i class="ri-mail-send-line"></i>
                      A confirmation email has been sent to <strong>{{ $credentials['email'] }}</strong> with your login credentials.
                    </p>

                    <div style="background: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                      <div style="font-size: 0.75rem; color: #856404; text-transform: uppercase; font-weight: 600; margin-bottom: 0.25rem;">Email / Username</div>
                      <div style="font-size: 1.125rem; color: #1e293b; font-weight: 700;">{{ $credentials['email'] }}</div>
                    </div>

                    <div style="background: white; padding: 1rem; border-radius: 8px;">
                      <div style="font-size: 0.75rem; color: #856404; text-transform: uppercase; font-weight: 600; margin-bottom: 0.25rem;">Temporary Password</div>
                      <div style="font-size: 1.125rem; color: #1e293b; font-weight: 700; word-break: break-all;">{{ $credentials['password'] }}</div>
                    </div>
                  </div>
                @endif

                <!-- Important Notice -->
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                  <strong style="color: #991b1b;"><i class="ri-alert-line"></i> Important:</strong> Please change your password after your first login for security purposes.
                </div>

                <!-- Additional Information -->
                <div class="info-card" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                  <div class="info-card-title" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ri-information-line"></i>
                    Important Information
                  </div>
                  <ul style="color: #64748b; line-height: 1.8; margin: 0; padding-left: 1.5rem;">
                    <li>Check-in time: 2:00 PM</li>
                    <li>Check-out time: 12:00 PM</li>
                    <li>Please bring a valid ID upon check-in</li>
                    @if(session('payment_method') === 'cash')
                      <li>Reservation fee of ₱500 must be paid upon check-in</li>
                    @endif
                    <li>Remaining balance of ₱{{ number_format($reservationData['balance'], 2) }} must be paid during your stay</li>
                    <li>Cancellation must be made at least 24 hours before check-in</li>
                  </ul>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                  <a href="{{ route('login') }}" class="btn btn-primary" style="flex: 1; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                    <i class="ri-login-box-line"></i>
                    Login to Your Account
                  </a>
                  <a href="{{ route('frontpage.index') }}" class="btn btn-secondary" style="flex: 1; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: white; color: #64748b; border: 2px solid #e2e8f0;">
                    <i class="ri-home-line"></i>
                    Back to Home
                  </a>
                </div>
              @endif
            @endif
          </div>

          <!-- Form Actions -->
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
          <div class="sidebar-section">
            <div class="sidebar-title">Selected Rooms (<span id="roomCount">1</span>)</div>
            <div id="sidebarRoomsList">
              <!-- Will be populated via JavaScript -->
            </div>
          </div>

          <div class="sidebar-section">
            <div class="sidebar-title">Price Summary</div>

            <div class="price-row">
              <span><span id="totalRoomsCount">1</span> room(s) × <span id="priceNights">0</span> night(s)</span>
              <span class="price-value" id="subtotal">₱0</span>
            </div>

            <div class="price-row">
              <span>Reservation Fee (<span id="roomCountForFee">1</span> room)</span>
              <span class="price-value" style="color: #10b981;" id="totalReservationFee">₱500</span>
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
                <span style="font-weight: 600; color: #10b981;" id="payNowAmount">₱500</span>
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
      initialRoom: {
        room_id: {{ $room->room_id }},
        room_number: '{{ $room->room_number }}',
        room_type_name: '{{ $room->roomType->room_type_name }}',
        description: '{{ $room->roomType->description }}',
        rate_per_night: {{ $room->roomType->rate_per_night }},
        max_pax: {{ $room->roomType->max_pax }},
        image_path: '{{ asset('storage/' . $room->image_path) }}'
      },
      prefilledNights: {{ $prefilledNights ?? 0 }},
      checkAvailabilityUrl: '{{ route("reservation.check-availability") }}',
      getAvailableRoomsUrl: '{{ route("reservation.get-available-rooms") }}',
      csrfToken: '{{ csrf_token() }}'
    };
  </script>
  <script src="{{ asset('/js/reservationjs/index_scripts.js') }}"></script>
@endsection