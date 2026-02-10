@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Reservation Confirmed - Hotel De SLSU')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/registration-form.css') }}">
  <style>
    .confirmation-container {
      max-width: 800px;
      margin: 2rem auto;
      padding: 2rem;
    }

    .success-header {
      text-align: center;
      padding: 3rem 2rem;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      border-radius: 16px;
      color: white;
      margin-bottom: 2rem;
    }

    .success-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      animation: scaleIn 0.5s ease-out;
    }

    @keyframes scaleIn {
      from {
        transform: scale(0);
      }
      to {
        transform: scale(1);
      }
    }

    .success-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .success-subtitle {
      font-size: 1.125rem;
      opacity: 0.95;
    }

    .info-card {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
    }

    .info-card-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 1rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: #64748b;
      font-weight: 500;
    }

    .info-value {
      color: #1e293b;
      font-weight: 600;
      text-align: right;
    }

    .credentials-box {
      background: #fff3cd;
      border: 2px solid #ffc107;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 1.5rem;
    }

    .credentials-title {
      color: #856404;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .credential-item {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .credential-item:last-child {
      margin-bottom: 0;
    }

    .credential-label {
      font-size: 0.75rem;
      color: #856404;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .credential-value {
      font-size: 1.125rem;
      color: #1e293b;
      font-weight: 700;
      word-break: break-all;
    }

    .warning-box {
      background: #fef2f2;
      border-left: 4px solid #ef4444;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .warning-box strong {
      color: #991b1b;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }

    .btn {
      flex: 1;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      text-align: center;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
    }

    .btn-secondary {
      background: white;
      color: #64748b;
      border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
      border-color: #cbd5e1;
      background: #f8fafc;
    }

    .payment-badge {
      display: inline-block;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 600;
    }

    .payment-badge.online {
      background: #dcfce7;
      color: #166534;
    }

    .payment-badge.cash {
      background: #fef3c7;
      color: #92400e;
    }

    @media (max-width: 768px) {
      .action-buttons {
        flex-direction: column;
      }

      .info-row {
        flex-direction: column;
        gap: 0.5rem;
      }

      .info-value {
        text-align: left;
      }
    }
  </style>
@endsection

@section('content')
  <div class="confirmation-container">
    <!-- Success Header -->
    <div class="success-header">
      <div class="success-icon">
        <i class="ri-checkbox-circle-fill"></i>
      </div>
      <h1 class="success-title">Reservation Confirmed!</h1>
      <p class="success-subtitle">
        @if($paymentMethod === 'online')
          Your payment has been processed successfully
        @else
          Your reservation has been created successfully
        @endif
      </p>
    </div>

    <!-- Reservation Details -->
    <div class="info-card">
      <div class="info-card-title">
        <i class="ri-file-list-3-line"></i>
        Reservation Details
      </div>

      <div class="info-row">
        <span class="info-label">Reservation ID</span>
        <span class="info-value">#{{ $reservationData['reservation_id'] }}</span>
      </div>

      <div class="info-row">
        <span class="info-label">Room Type</span>
        <span class="info-value">{{ $reservationData['room_type_name'] }}</span>
      </div>

      <div class="info-row">
        <span class="info-label">Room Number</span>
        <span class="info-value">{{ $reservationData['room_number'] }}</span>
      </div>

      <div class="info-row">
        <span class="info-label">Check-in</span>
        <span class="info-value">{{ date('F d, Y', strtotime($reservationData['arrival_date'])) }}</span>
      </div>

      <div class="info-row">
        <span class="info-label">Check-out</span>
        <span class="info-value">{{ date('F d, Y', strtotime($reservationData['departure_date'])) }}</span>
      </div>

      <div class="info-row">
        <span class="info-label">Number of Nights</span>
        <span class="info-value">{{ $reservationData['no_nights'] }} night{{ $reservationData['no_nights'] > 1 ? 's' : '' }}</span>
      </div>
    </div>

    <!-- Payment Information -->
    <div class="info-card">
      <div class="info-card-title">
        <i class="ri-money-dollar-circle-line"></i>
        Payment Information
      </div>

      <div class="info-row">
        <span class="info-label">Payment Method</span>
        <span class="info-value">
          <span class="payment-badge {{ $paymentMethod }}">
            @if($paymentMethod === 'online')
              <i class="ri-bank-card-line"></i> Online Payment
            @else
              <i class="ri-cash-line"></i> Cash on Arrival
            @endif
          </span>
        </span>
      </div>

      <div class="info-row">
        <span class="info-label">Total Amount</span>
        <span class="info-value">₱{{ number_format($reservationData['total_amount'], 2) }}</span>
      </div>

      <div class="info-row">
        <span class="info-label">
          @if($paymentMethod === 'online')
            Paid (Reservation Fee)
          @else
            Reservation Fee (Pay on Arrival)
          @endif
        </span>
        <span class="info-value" style="color: #10b981;">₱500.00</span>
      </div>

      <div class="info-row">
        <span class="info-label">Remaining Balance</span>
        <span class="info-value" style="color: #f59e0b;">₱{{ number_format($reservationData['balance'], 2) }}</span>
      </div>
    </div>

    <!-- Account Credentials -->
    <div class="credentials-box">
      <div class="credentials-title">
        <i class="ri-lock-password-line"></i>
        Your Temporary Account
      </div>
      <p style="margin-bottom: 1.5rem; color: #856404;">
        <i class="ri-mail-send-line"></i>
        A confirmation email has been sent to <strong>{{ $credentials['email'] }}</strong> with your login credentials.
      </p>

      <div class="credential-item">
        <div class="credential-label">Email / Username</div>
        <div class="credential-value">{{ $credentials['email'] }}</div>
      </div>

      <div class="credential-item">
        <div class="credential-label">Temporary Password</div>
        <div class="credential-value">{{ $credentials['password'] }}</div>
      </div>
    </div>

    <!-- Important Notice -->
    <div class="warning-box">
      <strong><i class="ri-alert-line"></i> Important:</strong> Please change your password after your first login for security purposes.
    </div>

    <!-- Additional Information -->
    <div class="info-card">
      <div class="info-card-title">
        <i class="ri-information-line"></i>
        Important Information
      </div>
      <ul style="color: #64748b; line-height: 1.8; margin: 0; padding-left: 1.5rem;">
        <li>Check-in time: 2:00 PM</li>
        <li>Check-out time: 12:00 PM</li>
        <li>Please bring a valid ID upon check-in</li>
        @if($paymentMethod === 'cash')
          <li>Reservation fee of ₱500 must be paid upon check-in</li>
        @endif
        <li>Remaining balance of ₱{{ number_format($reservationData['balance'], 2) }} must be paid during your stay</li>
        <li>Cancellation must be made at least 24 hours before check-in</li>
      </ul>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="ri-login-box-line"></i>
        Login to Your Account
      </a>
      <a href="{{ route('frontpage.index') }}" class="btn btn-secondary">
        <i class="ri-home-line"></i>
        Back to Home
      </a>
    </div>
  </div>
@endsection

@section('vendor-script')
  <script>
    // Clear session data after page loads
    window.addEventListener('load', function() {
      // Optional: Add confetti or celebration animation
      console.log('Reservation confirmed successfully!');
    });
  </script>
@endsection
