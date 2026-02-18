@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Book ' . $room->roomType->room_type_name . ' - Hotel De SLSU')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/registration-form.css') }}">
@endsection

@section('content')
  {{-- ── Hidden flag so JS can detect a server-side payment_success session ── --}}
  <input type="hidden" id="paymentSuccessFlag" value="{{ session('payment_success') ? '1' : '0' }}">

  <div class="checkout-container">

    @if(session('success'))
      <div class="alert alert-success"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
      <div class="alert alert-error"><i class="ri-error-warning-line"></i><span>{{ session('error') }}</span></div>
    @endif

    {{-- Progress Steps --}}
    <div class="checkout-steps">
      <div class="steps-wrapper">
        <div class="step active clickable" data-step="1">
          <div class="step-icon"><i class="ri-hotel-line"></i></div>
          <span class="step-label">Rooms</span>
        </div>
        <div class="step" data-step="2">
          <div class="step-icon"><i class="ri-user-line"></i></div>
          <span class="step-label">Account</span>
        </div>
        <div class="step" data-step="3">
          <div class="step-icon"><i class="ri-file-list-3-line"></i></div>
          <span class="step-label">Details</span>
        </div>
        <div class="step" data-step="4">
          <div class="step-icon"><i class="ri-bank-card-line"></i></div>
          <span class="step-label">Payment</span>
        </div>
        <div class="step" data-step="5">
          <div class="step-icon"><i class="ri-check-line"></i></div>
          <span class="step-label">Confirmation</span>
        </div>
      </div>
    </div>

    <form action="{{ route('reservation.store') }}" method="POST" id="reservationForm">
      @csrf
      <input type="hidden" name="rooms" id="roomsData">

      <div class="checkout-content">
        <div class="checkout-main">

          {{-- STEP 1: Room Selection --}}
          <div class="step-content active" id="step1">
            <h2 class="section-title">Select Your Room(s)</h2>

            <div id="selectedRoomsList">
              <div class="room-card selected-room"
                   data-room-id="{{ $room->room_id }}"
                   data-rate="{{ $room->roomType->rate_per_night }}">
                <img src="{{ asset('storage/' . $room->image_path) }}" alt="{{ $room->roomType->room_type_name }}" class="room-image">
                <div class="room-details">
                  <h3 class="room-name">{{ $room->roomType->room_type_name }}</h3>
                  <div class="room-meta">
                    <span><i class="ri-door-line"></i> Room {{ $room->room_number }}</span>
                    <span><i class="ri-user-line"></i> Up to {{ $room->roomType->max_pax }} guests</span>
                    <span class="free-badge">Available</span>
                  </div>
                  <p style="color:#64748b; font-size:0.875rem; margin:0.5rem 0;">{{ $room->roomType->description }}</p>
                  <div class="room-price">
                    ₱{{ number_format($room->roomType->rate_per_night, 0) }}
                    <span style="font-size:0.875rem; color:#64748b; font-weight:400;">per night</span>
                  </div>
                </div>
                <button type="button" class="remove-room-btn" onclick="removeRoom(this)" style="display:none;">
                  <i class="ri-close-line"></i>
                </button>
              </div>
            </div>

            <button type="button" class="btn btn-outline" id="addMoreRoomsBtn" onclick="showAvailableRooms()">
              <i class="ri-add-line"></i> Add Another Room
            </button>

            <div id="availableRoomsSection" style="display:none; margin-top:1.5rem;">
              <h3 style="font-size:1rem; font-weight:600; margin-bottom:1rem; color:#1e293b;">
                <i class="ri-door-open-line"></i> Other Available Rooms
              </h3>
              <div id="availableRoomsList" class="available-rooms-grid"></div>
            </div>
          </div>

          {{-- STEP 2: Primary Guest Account --}}
          <div class="step-content" id="step2">
            <h2 class="section-title">Primary Guest Account</h2>
            <div class="pga-section">
              <div class="pga-header">
                <div class="pga-icon"><i class="ri-user-settings-line"></i></div>
                <div>
                  <div class="pga-title">Reservation Account</div>
                  <div class="pga-subtitle">
                    One account is created for this reservation — even across multiple rooms.
                    Temporary login credentials will be sent to the email you provide.
                  </div>
                </div>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label>First Name <span class="required">*</span></label>
                  <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="Juan" required>
                  @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label>Middle Name</label>
                  <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}" placeholder="dela">
                </div>
                <div class="form-group">
                  <label>Last Name <span class="required">*</span></label>
                  <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Cruz" required>
                  @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label>Date of Birth <span class="required">*</span></label>
                  <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') }}" max="{{ date('Y-m-d', strtotime('-1 day')) }}" required>
                  @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label>Email Address <span class="required">*</span></label>
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="juan@example.com" required>
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  <small class="form-hint"><i class="ri-mail-send-line"></i> Temporary login credentials will be sent here</small>
                </div>
                <div class="form-group">
                  <label>Contact Number <span class="required">*</span></label>
                  <input type="tel" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}" placeholder="+63 912 345 6789" required>
                  @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="pga-note">
                <i class="ri-information-line"></i>
                <span>This single account covers all rooms in your reservation. You can manage your bookings after logging in with your temporary credentials.</span>
              </div>
            </div>
          </div>

          {{-- STEP 3: Stay & Guest Details (per-room tabs) --}}
          <div class="step-content" id="step3">
            <h2 class="section-title">Stay &amp; Guest Details</h2>
            <div class="room-tabs-layout">
              <div class="room-tabs-sidebar" id="roomTabsSidebar">
                <div class="room-tabs-header">
                  <i class="ri-hotel-line"></i>
                  <span>Rooms</span>
                </div>
                <div id="roomTabsList"></div>
              </div>
              <div class="room-tabs-forms" id="roomTabsForms"></div>
            </div>
          </div>

          {{-- STEP 4: Payment --}}
          <div class="step-content" id="step4">
            <h2 class="section-title">Payment Method</h2>
            <div class="payment-tabs">
              <button type="button" class="payment-tab active" data-payment="cash">Cash On Arrival</button>
              <button type="button" class="payment-tab" data-payment="online">Online Payment</button>
            </div>

            <div class="payment-content active" id="cash-payment">
              <div style="text-align:center; padding:3rem 2rem;">
                <i class="ri-money-dollar-circle-line" style="font-size:4rem; color:#10b981; margin-bottom:1rem; display:block;"></i>
                <h3 style="font-size:1.25rem; font-weight:600; margin-bottom:0.5rem;">Cash Payment Selected</h3>
                <p style="color:#64748b; max-width:500px; margin:0 auto;">
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
                    <label>CVV</label>
                    <input type="text" class="form-control" placeholder="123" maxlength="3">
                  </div>
                </div>
                <div class="save-card">
                  <label class="toggle-switch"><input type="checkbox"><span class="toggle-slider"></span></label>
                  <span style="font-size:0.9375rem; color:#475569;">Save Card for future billing?</span>
                </div>
              </div>
              <input type="hidden" name="payment_method" value="online" id="onlinePaymentInput" disabled>
            </div>

            <div class="payment-info">
              <div class="payment-info-title"><i class="ri-information-line"></i> Payment Details</div>
              <ul>
                <li>Pay <strong id="totalReservationFeeText">₱500</strong> reservation fee now to secure your booking</li>
                <li>Remaining balance of <strong id="balanceAmountText2">₱0</strong> will be paid <span id="paymentMethodText">upon check-in</span></li>
              </ul>
            </div>
          </div>

          {{-- STEP 5: Confirmation --}}
          <div class="step-content" id="step5">
            @if(session('payment_success'))
              @php
                $reservationData = session('reservation_data');
                $credentials     = session('temp_credentials');
                $paymentMethod   = session('payment_method', 'cash');
              @endphp

              <div style="text-align:center; padding:3rem 2rem; background:linear-gradient(135deg,#10b981,#059669); border-radius:16px; color:white; margin-bottom:2rem;">
                <div style="font-size:4rem; margin-bottom:1rem;"><i class="ri-checkbox-circle-fill"></i></div>
                <h2 style="font-size:2rem; font-weight:700; margin-bottom:0.5rem;">Reservation Confirmed!</h2>
                <p style="font-size:1.125rem; opacity:0.95;">
                  @if($paymentMethod === 'online') Your payment has been processed successfully
                  @else Your reservation has been created successfully @endif
                </p>
              </div>

              @if($reservationData)
                <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 2px 8px rgba(0,0,0,.1); margin-bottom:1.5rem;">
                  <div style="font-size:1.125rem; font-weight:700; margin-bottom:1.5rem; color:#1e293b; display:flex; align-items:center; gap:0.5rem;"><i class="ri-file-list-3-line"></i> Reservation Details</div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Reservation ID</span>
                    <span style="color:#1e293b; font-weight:700;">#{{ $reservationData['reservation_id'] }}</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Room(s)</span>
                    <span style="color:#1e293b; font-weight:600; text-align:right;">
                      @if(isset($reservationData['rooms']) && count($reservationData['rooms']) > 0)
                        @foreach($reservationData['rooms'] as $rd)
                          {{ $rd['room_type_name'] }} — Rm {{ $rd['room_number'] }}<br>
                        @endforeach
                      @else
                        {{ $reservationData['room_type_name'] }} — Rm {{ $reservationData['room_number'] }}
                      @endif
                    </span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Check-in</span>
                    <span style="color:#1e293b; font-weight:600;">{{ date('F d, Y', strtotime($reservationData['arrival_date'])) }}</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Check-out</span>
                    <span style="color:#1e293b; font-weight:600;">{{ date('F d, Y', strtotime($reservationData['departure_date'])) }}</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0;">
                    <span style="color:#64748b; font-weight:500;">Number of Nights</span>
                    <span style="color:#1e293b; font-weight:600;">{{ $reservationData['no_nights'] }} night{{ $reservationData['no_nights'] > 1 ? 's' : '' }}</span>
                  </div>
                </div>

                <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 2px 8px rgba(0,0,0,.1); margin-bottom:1.5rem;">
                  <div style="font-size:1.125rem; font-weight:700; margin-bottom:1.5rem; color:#1e293b; display:flex; align-items:center; gap:0.5rem;"><i class="ri-money-dollar-circle-line"></i> Payment Information</div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Payment Method</span>
                    <span>
                      @if($paymentMethod === 'online')
                        <span style="background:#dcfce7; color:#166534; padding:0.4rem 0.875rem; border-radius:6px; font-size:0.875rem; font-weight:600;"><i class="ri-bank-card-line"></i> Online Payment</span>
                      @else
                        <span style="background:#fef3c7; color:#92400e; padding:0.4rem 0.875rem; border-radius:6px; font-size:0.875rem; font-weight:600;"><i class="ri-cash-line"></i> Cash on Arrival</span>
                      @endif
                    </span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Total Amount</span>
                    <span style="color:#1e293b; font-weight:700;">₱{{ number_format($reservationData['total_amount'], 2) }}</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-weight:500;">Reservation Fee</span>
                    <span style="color:#10b981; font-weight:600;">₱{{ number_format($reservationData['reservation_fee'], 2) }}</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:0.875rem 0;">
                    <span style="color:#64748b; font-weight:500;">Remaining Balance</span>
                    <span style="color:#f59e0b; font-weight:700;">₱{{ number_format($reservationData['balance'], 2) }}</span>
                  </div>
                </div>

                @if($credentials)
                  <div style="background:#fff3cd; border:2px solid #ffc107; border-radius:12px; padding:2rem; margin-bottom:1.5rem;">
                    <div style="color:#856404; font-size:1.125rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;"><i class="ri-lock-password-line"></i> Your Temporary Account</div>
                    <p style="margin-bottom:1.25rem; color:#856404; font-size:0.875rem;"><i class="ri-mail-send-line"></i> Confirmation sent to <strong>{{ $credentials['email'] }}</strong></p>
                    <div style="background:white; padding:1rem; border-radius:8px; margin-bottom:0.75rem;">
                      <div style="font-size:0.7rem; color:#856404; text-transform:uppercase; font-weight:700; letter-spacing:0.08em; margin-bottom:0.25rem;">Email / Username</div>
                      <div style="font-size:1.05rem; color:#1e293b; font-weight:700;">{{ $credentials['email'] }}</div>
                    </div>
                    <div style="background:white; padding:1rem; border-radius:8px;">
                      <div style="font-size:0.7rem; color:#856404; text-transform:uppercase; font-weight:700; letter-spacing:0.08em; margin-bottom:0.25rem;">Temporary Password</div>
                      <div style="font-size:1.05rem; color:#1e293b; font-weight:700; word-break:break-all;">{{ $credentials['password'] }}</div>
                    </div>
                  </div>
                @endif

                <div style="background:#fef2f2; border-left:4px solid #ef4444; padding:1rem 1.5rem; border-radius:8px; margin-bottom:1.5rem;">
                  <strong style="color:#991b1b;"><i class="ri-alert-line"></i> Important:</strong> Please change your password after first login for security.
                </div>

                <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 2px 8px rgba(0,0,0,.1); margin-bottom:1.5rem;">
                  <div style="font-size:1.125rem; font-weight:700; margin-bottom:1rem; color:#1e293b; display:flex; align-items:center; gap:0.5rem;"><i class="ri-information-line"></i> Important Information</div>
                  <ul style="color:#64748b; line-height:2; margin:0; padding-left:1.5rem;">
                    <li>Check-in time: 2:00 PM</li>
                    <li>Check-out time: 12:00 PM</li>
                    <li>Please bring a valid ID upon check-in</li>
                    @if($paymentMethod === 'cash')
                      <li>Reservation fee of ₱{{ number_format($reservationData['reservation_fee'], 2) }} must be paid upon check-in</li>
                    @endif
                    <li>Remaining balance of ₱{{ number_format($reservationData['balance'], 2) }} must be paid during your stay</li>
                    <li>Cancellation must be made at least 24 hours before check-in</li>
                  </ul>
                </div>

                <div style="display:flex; gap:1rem; margin-top:2rem;">
                  <a href="{{ route('login') }}" class="btn btn-primary" style="flex:1; justify-content:center; background:linear-gradient(135deg,#060E4D,#013a72);">
                    <i class="ri-login-box-line"></i> Login to Your Account
                  </a>
                  <a href="{{ route('frontpage.index') }}" class="btn btn-secondary" style="flex:1; justify-content:center;">
                    <i class="ri-home-line"></i> Back to Home
                  </a>
                </div>
              @endif

            @else
              {{-- Fallback: session expired or direct access --}}
              <div style="text-align:center; padding:3rem 2rem;">
                <i class="ri-error-warning-line" style="font-size:4rem; color:#f59e0b; margin-bottom:1rem; display:block;"></i>
                <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:0.5rem;">No Confirmation Data</h2>
                <p style="color:#64748b;">Your session may have expired. Please check your email for confirmation details.</p>
                <a href="{{ route('frontpage.index') }}" class="btn btn-primary" style="margin-top:1.5rem; display:inline-flex;">
                  <i class="ri-home-line"></i> Back to Home
                </a>
              </div>
            @endif
          </div>

          {{-- Form Actions --}}
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" id="backBtn" style="display:none;">
              <i class="ri-arrow-left-line"></i> Back
            </button>
            <button type="button" class="btn btn-primary" id="nextStepBtn">
              Continue <i class="ri-arrow-right-line"></i>
            </button>
          </div>

        </div>{{-- end checkout-main --}}

        {{-- SIDEBAR --}}
        <div class="checkout-sidebar">
          <div class="sidebar-section">
            <div class="sidebar-title">Selected Rooms (<span id="roomCount">1</span>)</div>
            <div id="sidebarRoomsList"></div>
          </div>

          <div class="sidebar-section">
            <div class="sidebar-title">Price Summary</div>
            <div class="price-row">
              <span><span id="totalRoomsCount">1</span> room(s) × <span id="priceNights">—</span> night(s)</span>
              <span class="price-value" id="subtotal">—</span>
            </div>
            <div class="price-row">
              <span>Reservation Fee (<span id="roomCountForFee">1</span> × ₱500)</span>
              <span class="price-value" style="color:#10b981;" id="totalReservationFee">₱500</span>
            </div>
            <div class="price-row total">
              <span>Total Amount</span>
              <span id="totalAmount">—</span>
            </div>
          </div>

          <div class="sidebar-section" id="guestAddressDisplay" style="display:none;">
            <div class="sidebar-title">Reservation By</div>
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
            <div style="padding:1rem; background:#f0fdf4; border-radius:8px; border:1px solid #bbf7d0; margin-bottom:0.75rem;">
              <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                <span style="font-size:0.8125rem; color:#166534; font-weight:600;">Pay Now (Reservation Fee)</span>
                <span style="font-weight:700; color:#10b981;" id="payNowAmount">₱500</span>
              </div>
              <div style="display:flex; justify-content:space-between;">
                <span style="font-size:0.8125rem; color:#64748b;">Remaining Balance</span>
                <span style="font-weight:600; color:#f59e0b;" id="balanceAmount">—</span>
              </div>
            </div>
            <p style="font-size:0.75rem; color:#94a3b8; text-align:center; margin:0;">₱500 per room secures your booking</p>
          </div>
        </div>

      </div>{{-- end checkout-content --}}
    </form>
  </div>
@endsection

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    window.reservationData = {
      initialRoom: {
        room_id:        {{ $room->room_id }},
        room_number:    '{{ $room->room_number }}',
        room_type_name: '{{ $room->roomType->room_type_name }}',
        description:    '{{ addslashes($room->roomType->description) }}',
        rate_per_night: {{ $room->roomType->rate_per_night }},
        max_pax:        {{ $room->roomType->max_pax }},
        image_path:     '{{ asset('storage/' . $room->image_path) }}'
      },
      prefilledNights:      {{ $prefilledNights ?? 0 }},
      checkAvailabilityUrl: '{{ route("reservation.check-availability") }}',
      getAvailableRoomsUrl: '{{ route("reservation.get-available-rooms") }}',
      csrfToken:            '{{ csrf_token() }}'
    };
  </script>
  <script src="{{ asset('/js/reservationjs/index_scripts.js') }}"></script>
@endsection