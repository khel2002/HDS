@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Reservation Confirmed - Hotel De SLSU')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
@endsection

@section('content')
<div style="max-width:720px; margin:2rem auto; padding:0 1rem;">

  {{-- ── SUCCESS HERO ── --}}
  <div style="text-align:center; padding:3rem 2rem; background:linear-gradient(135deg,#10b981,#059669); border-radius:16px; color:white; margin-bottom:2rem; box-shadow:0 8px 32px rgba(16,185,129,.25);">
    <div style="font-size:4rem; margin-bottom:1rem;"><i class="ri-checkbox-circle-fill"></i></div>
    <h2 style="font-size:2rem; font-weight:700; margin-bottom:0.5rem;">Reservation Confirmed!</h2>
    <p style="font-size:1.125rem; opacity:0.95; margin:0;">
      @if($paymentMethod === 'online')
        Your payment has been processed successfully.
      @else
        Your reservation has been created successfully.
      @endif
    </p>
  </div>

  @if($reservationData)

    {{-- ── RESERVATION DETAILS ── --}}
    <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:1.5rem;">
      <div style="font-size:1.125rem; font-weight:700; margin-bottom:1.5rem; color:#1e293b; display:flex; align-items:center; gap:.5rem;">
        <i class="ri-file-list-3-line" style="color:#060E4D;"></i> Reservation Details
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Reservation ID</span>
        <span style="font-weight:700; color:#1e293b;">#{{ $reservationData['reservation_id'] }}</span>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Room(s)</span>
        <span style="font-weight:600; color:#1e293b; text-align:right;">
          @if(isset($reservationData['rooms']) && count($reservationData['rooms']) > 0)
            @foreach($reservationData['rooms'] as $rd)
              {{ $rd['room_type_name'] }} — Rm {{ $rd['room_number'] }}<br>
            @endforeach
          @else
            {{ $reservationData['room_type_name'] }} — Rm {{ $reservationData['room_number'] }}
          @endif
        </span>
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Check-in</span>
        <span style="font-weight:600; color:#1e293b;">{{ date('F d, Y', strtotime($reservationData['arrival_date'])) }}</span>
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Check-out</span>
        <span style="font-weight:600; color:#1e293b;">{{ date('F d, Y', strtotime($reservationData['departure_date'])) }}</span>
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0;">
        <span style="color:#64748b; font-weight:500;">Number of Nights</span>
        <span style="font-weight:600; color:#1e293b;">{{ $reservationData['no_nights'] }} night{{ $reservationData['no_nights'] > 1 ? 's' : '' }}</span>
      </div>
    </div>

    {{-- ── PAYMENT DETAILS ── --}}
    <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:1.5rem;">
      <div style="font-size:1.125rem; font-weight:700; margin-bottom:1.5rem; color:#1e293b; display:flex; align-items:center; gap:.5rem;">
        <i class="ri-money-dollar-circle-line" style="color:#060E4D;"></i> Payment Information
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Payment Method</span>
        <span>
          @if($paymentMethod === 'online')
            <span style="background:#dcfce7; color:#166534; padding:.4rem .875rem; border-radius:6px; font-size:.875rem; font-weight:600;">
              <i class="ri-bank-card-line"></i> Online Payment
            </span>
          @else
            <span style="background:#fef3c7; color:#92400e; padding:.4rem .875rem; border-radius:6px; font-size:.875rem; font-weight:600;">
              <i class="ri-cash-line"></i> Cash on Arrival
            </span>
          @endif
        </span>
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Total Amount</span>
        <span style="font-weight:700; color:#1e293b;">₱{{ number_format($reservationData['total_amount'], 2) }}</span>
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0; border-bottom:1px solid #e2e8f0;">
        <span style="color:#64748b; font-weight:500;">Reservation Fee</span>
        <span style="font-weight:600; color:#10b981;">₱{{ number_format($reservationData['reservation_fee'], 2) }}</span>
      </div>

      <div style="display:flex; justify-content:space-between; padding:.875rem 0;">
        <span style="color:#64748b; font-weight:500;">Remaining Balance</span>
        <span style="font-weight:700; color:#f59e0b;">₱{{ number_format($reservationData['balance'], 2) }}</span>
      </div>
    </div>

    {{-- ── TEMPORARY CREDENTIALS ── --}}
    @if($credentials)
      <div style="background:#fff3cd; border:2px solid #ffc107; border-radius:12px; padding:2rem; margin-bottom:1.5rem;">
        <div style="color:#856404; font-size:1.125rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem;">
          <i class="ri-lock-password-line"></i> Your Temporary Account
        </div>
        <p style="margin-bottom:1.25rem; color:#856404; font-size:.875rem;">
          <i class="ri-mail-send-line"></i> A confirmation email has been sent to <strong>{{ $credentials['email'] }}</strong>
        </p>
        <div style="background:white; padding:1rem; border-radius:8px; margin-bottom:.75rem;">
          <div style="font-size:.7rem; color:#856404; text-transform:uppercase; font-weight:700; letter-spacing:.08em; margin-bottom:.25rem;">Email / Username</div>
          <div style="font-size:1.05rem; color:#1e293b; font-weight:700;">{{ $credentials['email'] }}</div>
        </div>
        <div style="background:white; padding:1rem; border-radius:8px;">
          <div style="font-size:.7rem; color:#856404; text-transform:uppercase; font-weight:700; letter-spacing:.08em; margin-bottom:.25rem;">Temporary Password</div>
          <div style="font-size:1.05rem; color:#1e293b; font-weight:700; word-break:break-all;">{{ $credentials['password'] }}</div>
        </div>
      </div>
    @endif

    {{-- ── SECURITY WARNING ── --}}
    <div style="background:#fef2f2; border-left:4px solid #ef4444; padding:1rem 1.5rem; border-radius:8px; margin-bottom:1.5rem;">
      <strong style="color:#991b1b;"><i class="ri-alert-line"></i> Important:</strong>
      Please change your password after first login for security.
    </div>

    {{-- ── IMPORTANT INFORMATION ── --}}
    <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:1.5rem;">
      <div style="font-size:1.125rem; font-weight:700; margin-bottom:1rem; color:#1e293b; display:flex; align-items:center; gap:.5rem;">
        <i class="ri-information-line" style="color:#060E4D;"></i> Important Information
      </div>
      <ul style="color:#64748b; line-height:2.2; margin:0; padding-left:1.5rem;">
        <li>Check-in time: <strong>2:00 PM</strong></li>
        <li>Check-out time: <strong>12:00 PM</strong></li>
        <li>Please bring a <strong>valid ID</strong> upon check-in</li>
        @if($paymentMethod === 'cash')
          <li>Reservation fee of <strong>₱{{ number_format($reservationData['reservation_fee'], 2) }}</strong> must be paid upon check-in</li>
        @endif
        <li>Remaining balance of <strong>₱{{ number_format($reservationData['balance'], 2) }}</strong> must be paid during your stay</li>
        <li>Cancellation must be made at least <strong>24 hours</strong> before check-in</li>
      </ul>
    </div>

    {{-- ── CTA BUTTONS ── --}}
    <div style="display:flex; gap:1rem; margin-top:2rem; margin-bottom:3rem; flex-wrap:wrap;">
      <a href="{{ route('login') }}"
         style="flex:1; min-width:200px; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
                padding:1rem; background:linear-gradient(135deg,#060E4D,#013a72); color:white;
                border-radius:8px; font-weight:700; text-decoration:none; font-size:.9375rem;">
        <i class="ri-login-box-line"></i> Login to Your Account
      </a>
      <a href="{{ route('frontpage.index') }}"
         style="flex:1; min-width:200px; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
                padding:1rem; background:white; color:#060E4D;
                border:2px solid #060E4D; border-radius:8px; font-weight:700; text-decoration:none; font-size:.9375rem;">
        <i class="ri-home-line"></i> Back to Home
      </a>
    </div>

  @else
    {{-- ── FALLBACK (session expired) ── --}}
    <div style="text-align:center; padding:3rem 2rem; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.08);">
      <i class="ri-error-warning-line" style="font-size:4rem; color:#f59e0b; margin-bottom:1rem; display:block;"></i>
      <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:.5rem;">Session Expired</h2>
      <p style="color:#64748b; margin-bottom:1.5rem;">
        Your session may have expired. Please check your email for confirmation details,
        or contact us if you need assistance.
      </p>
      <a href="{{ route('frontpage.index') }}"
         style="display:inline-flex; align-items:center; gap:.5rem; padding:.875rem 2rem;
                background:linear-gradient(135deg,#060E4D,#013a72); color:white;
                border-radius:8px; font-weight:700; text-decoration:none;">
        <i class="ri-home-line"></i> Back to Home
      </a>
    </div>
  @endif

</div>
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Show success toast on page load
  document.addEventListener('DOMContentLoaded', function () {
    @if($paymentMethod === 'online')
      Swal.fire({
        icon: 'success',
        title: 'Payment Successful!',
        text: 'Check your email for login credentials.',
        confirmButtonColor: '#060E4D'
      });
    @else
      Swal.fire({
        icon: 'success',
        title: 'Reservation Confirmed!',
        text: 'Check your email for your booking details and login credentials.',
        confirmButtonColor: '#060E4D'
      });
    @endif
  });
</script>
@endsection