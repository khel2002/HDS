@extends('layouts/contentNavbarLayout')

@section('title', 'My Stay Dashboard')

@section('page-style')
  <link rel="stylesheet" href="{{ asset('assets/css/guest.css') }}">
@endsection

@section('content')
  <!-- Reservation Status Banner -->
  @if(isset($reservationStatus) && $reservationStatus['has_reservation'])
    <div class="reservation-status-banner {{ $reservationStatus['status'] }} animate-fade-in">
      <div class="status-banner-content">
        <div class="status-icon-wrapper">
          @if($reservationStatus['status'] == 'approved')
            <i class="ri-checkbox-circle-line"></i>
          @elseif($reservationStatus['status'] == 'pending')
            <i class="ri-time-line"></i>
          @else
            <i class="ri-information-line"></i>
          @endif
        </div>
        
        <div class="status-details">
          <div class="status-title">
            @if($reservationStatus['status'] == 'approved')
              @if($reservationStatus['is_checked_in'])
                ✓ Currently Checked In
              @else
                ✓ Reservation Confirmed
              @endif
            @elseif($reservationStatus['status'] == 'pending')
              ⏳ Reservation Pending Approval
            @endif
          </div>
          
          <div class="status-info-grid">
            @if($reservationStatus['room_assigned'])
              <div class="status-info-item">
                <span class="info-label">Room:</span>
                <span class="info-value">{{ $reservationStatus['room_number'] }} ({{ $reservationStatus['room_type'] }})</span>
              </div>
            @else
              <div class="status-info-item">
                <span class="info-label">Room Type:</span>
                <span class="info-value">{{ $reservationStatus['room_type'] ?? 'To be assigned' }}</span>
              </div>
            @endif
            
            <div class="status-info-item">
              <span class="info-label">Check-in:</span>
              <span class="info-value">{{ \Carbon\Carbon::parse($reservationStatus['check_in_date'])->format('M d, Y') }}</span>
            </div>
            
            <div class="status-info-item">
              <span class="info-label">Check-out:</span>
              <span class="info-value">{{ \Carbon\Carbon::parse($reservationStatus['check_out_date'])->format('M d, Y') }}</span>
            </div>
            
            @if(!$reservationStatus['is_checked_in'] && $reservationStatus['days_until_checkin'] > 0)
              <div class="status-info-item">
                <span class="info-label">Days until check-in:</span>
                <span class="info-value">{{ $reservationStatus['days_until_checkin'] }} day(s)</span>
              </div>
            @endif
          </div>
          
          @if($reservationStatus['status'] == 'pending')
            <div class="status-message">
              <i class="ri-information-line"></i>
              Your reservation is pending approval. We'll notify you once it's confirmed.
            </div>
          @elseif($reservationStatus['status'] == 'approved' && !$reservationStatus['is_checked_in'])
            <div class="status-message success">
              <i class="ri-check-line"></i>
              Your reservation has been approved! Please proceed to check-in on {{ \Carbon\Carbon::parse($reservationStatus['check_in_date'])->format('M d, Y') }}.
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  <!-- Welcome Hero -->
  @if(isset($guestInfo['has_active_stay']) && $guestInfo['has_active_stay'])
  <div class="welcome-hero animate-fade-in">
    <div class="welcome-content">
      <h1 class="welcome-greeting">Welcome, {{ $guestInfo['guest_name'] ?? 'Guest' }}!</h1>
      <p class="welcome-subtitle">We hope you're enjoying your stay at our hotel</p>
      
      <div class="stay-info-grid">
        <div class="stay-info-item">
          <div class="stay-info-label">Room Number</div>
          <div class="stay-info-value">
            <i class="ri-door-line"></i>
            {{ $guestInfo['room_number'] ?? 'N/A' }}
          </div>
          @if(isset($guestInfo['room_type']) && $guestInfo['room_type'] !== 'N/A')
            <div class="stay-info-meta">{{ $guestInfo['room_type'] }}</div>
          @endif
        </div>
        
        <div class="stay-info-item">
          <div class="stay-info-label">Check-in Date</div>
          <div class="stay-info-value">
            <i class="ri-calendar-check-line"></i>
            {{ $guestInfo['check_in_date'] ? \Carbon\Carbon::parse($guestInfo['check_in_date'])->format('M d, Y') : 'N/A' }}
          </div>
          @if(isset($guestInfo['nights_stayed']) && $guestInfo['nights_stayed'] > 0)
            <div class="stay-info-meta">{{ $guestInfo['nights_stayed'] }} night(s) stayed</div>
          @endif
        </div>
        
        <div class="stay-info-item">
          <div class="stay-info-label">Check-out Date</div>
          <div class="stay-info-value">
            <i class="ri-calendar-line"></i>
            {{ $guestInfo['check_out_date'] ? \Carbon\Carbon::parse($guestInfo['check_out_date'])->format('M d, Y') : 'N/A' }}
          </div>
          @if(isset($guestInfo['nights_total']) && $guestInfo['nights_total'] > 0)
            <div class="stay-info-meta">{{ $guestInfo['nights_total'] }} night(s) total</div>
          @endif
        </div>
        
        <div class="stay-info-item" id="nights-remaining-card">
          <div class="stay-info-label">Nights Remaining</div>
          <div class="stay-info-value">
            <i class="ri-moon-line"></i>
            {{ $guestInfo['nights_remaining'] ?? '0' }}
          </div>
          @if(isset($guestInfo['adults']) && isset($guestInfo['children']))
            <div class="stay-info-meta">
              {{ $guestInfo['adults'] }} adult(s)
              @if($guestInfo['children'] > 0), {{ $guestInfo['children'] }} child(ren)@endif
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  @else
  <div class="welcome-hero-simple animate-fade-in">
    <div class="welcome-content">
      {{-- FIXED: Use guest_name from guestInfo instead of auth()->user()->first_name --}}
      <h1 class="welcome-greeting">Welcome, {{ $guestInfo['guest_name'] ?? 'Guest' }}!</h1>
      <p class="welcome-subtitle">Book a room to start your amazing stay with us</p>
    </div>
  </div>
  @endif

  <!-- Quick Services -->
  <div class="services-grid">
    <a href="/guest/breakfast/order" class="service-card breakfast animate-fade-in delay-1">
      <div class="service-icon-wrapper">
        <i class="ri-restaurant-2-line"></i>
      </div>
      <h3 class="service-title">Order Breakfast</h3>
      <p class="service-description">Start your day with a delicious meal</p>
    </a>

    <a href="/guest/room-service" class="service-card room-service animate-fade-in delay-2">
      <div class="service-icon-wrapper">
        <i class="ri-customer-service-2-line"></i>
      </div>
      <h3 class="service-title">Room Service</h3>
      <p class="service-description">Request housekeeping or assistance</p>
    </a>

    <a href="/guest/help" class="service-card concierge animate-fade-in delay-3">
      <div class="service-icon-wrapper">
        <i class="ri-customer-service-line"></i>
      </div>
      <h3 class="service-title">Help & Contact</h3>
      <p class="service-description">Get assistance anytime</p>
    </a>

    <a href="/guest/checkout-info" class="service-card checkout animate-fade-in delay-4">
      <div class="service-icon-wrapper">
        <i class="ri-information-line"></i>
      </div>
      <h3 class="service-title">Check-out Info</h3>
      <p class="service-description">View check-out details</p>
    </a>
  </div>

  <!-- Quick Stats Summary -->
  @if(isset($guestInfo['balance']) && $guestInfo['balance'] > 0)
  <div class="row mb-4">
    <div class="col-12">
      <div class="alert-card balance-alert">
        <div class="alert-icon">
          <i class="ri-wallet-3-line"></i>
        </div>
        <div class="alert-content">
          <div class="alert-title">Outstanding Balance</div>
          <div class="alert-message">You have a remaining balance of <strong>₱{{ number_format($guestInfo['balance'], 2) }}</strong></div>
        </div>
        <a href="/guest/payments" class="btn-alert-action">
          Pay Now <i class="ri-arrow-right-line"></i>
        </a>
      </div>
    </div>
  </div>
  @endif

  <!-- My Requests Section -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <i class="ri-file-list-line"></i>
        My Recent Requests
      </h2>
      <a href="/guest/my-requests" class="btn-modern-action">
        View All <i class="ri-arrow-right-line"></i>
      </a>
    </div>
    <div class="section-card-body">
      @if(isset($recentRequests) && count($recentRequests) > 0)
        <div class="request-list">
          @foreach($recentRequests as $request)
            <div class="request-item">
              <div class="request-left">
                <div class="request-type">{{ str_replace('_', ' ', ucfirst($request->service_type ?? 'Service Request')) }}</div>
                <div class="request-date">{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y • h:i A') }}</div>
              </div>
              <div class="request-right">
                <span class="request-status {{ strtolower(str_replace('_', '-', $request->request_status ?? 'pending')) }}">
                  {{ ucfirst(str_replace('_', ' ', $request->request_status ?? 'Pending')) }}
                </span>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <i class="ri-file-list-line empty-state-icon"></i>
          <h3 class="empty-state-title">No Recent Requests</h3>
          <p class="empty-state-text">You haven't made any service requests yet</p>
        </div>
      @endif
    </div>
  </div>

  <!-- My Breakfast Orders -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <i class="ri-restaurant-2-line"></i>
        My Breakfast Orders
      </h2>
      <a href="/guest/breakfast/my-orders" class="btn-modern-action">
        View All <i class="ri-arrow-right-line"></i>
      </a>
    </div>
    <div class="section-card-body">
      @if(isset($breakfastOrders) && count($breakfastOrders) > 0)
        <div class="request-list">
          @foreach($breakfastOrders as $order)
            <div class="request-item">
              <div class="request-left">
                <div class="request-type">Breakfast Order #{{ $order->id }}</div>
                <div class="request-date">{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y • h:i A') }}</div>
                @if($order->items)
                  <div class="request-items">{{ $order->items }}</div>
                @endif
              </div>
              <div class="request-right">
                <span class="request-status {{ strtolower($order->status ?? 'pending') }}">
                  {{ ucfirst($order->status ?? 'Pending') }}
                </span>
                @if($order->total_price)
                  <div class="request-price">₱{{ number_format($order->total_price, 2) }}</div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <i class="ri-restaurant-2-line empty-state-icon"></i>
          <h3 class="empty-state-title">No Breakfast Orders</h3>
          <p class="empty-state-text">Order a delicious breakfast to start your day!</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Hotel Information -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <i class="ri-information-line"></i>
        Hotel Information
      </h2>
    </div>
    <div class="section-card-body">
      <div class="info-cards-grid">
        <div class="info-card">
          <div class="info-card-header">
            <div class="info-icon wifi">
              <i class="ri-wifi-line"></i>
            </div>
            <h3 class="info-card-title">WiFi Access</h3>
          </div>
          <div class="info-card-content">Hotel_Guest_WiFi</div>
          <p class="info-card-description">Password: {{ $hotelInfo['wifi_password'] ?? 'Available at front desk' }}</p>
        </div>

        <div class="info-card">
          <div class="info-card-header">
            <div class="info-icon phone">
              <i class="ri-phone-line"></i>
            </div>
            <h3 class="info-card-title">Front Desk</h3>
          </div>
          <div class="info-card-content">{{ $hotelInfo['front_desk_phone'] ?? 'Dial 0' }}</div>
          <p class="info-card-description">24/7 assistance available</p>
        </div>

        <div class="info-card">
          <div class="info-card-header">
            <div class="info-icon time">
              <i class="ri-time-line"></i>
            </div>
            <h3 class="info-card-title">Check-out Time</h3>
          </div>
          <div class="info-card-content">{{ $hotelInfo['checkout_time'] ?? '12:00 PM' }}</div>
          <p class="info-card-description">Late check-out available upon request</p>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="{{ asset('js/guestjs/index_script.js') }}"></script>
@endsection