@extends('layouts/sections/navbar/landingpagenav')
@section('title', 'Reservation Confirmed - Hotel De SLSU')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">

  <style>
    .success-container {
      max-width: 800px;
      margin: 2rem auto;
      padding: 0 1rem;
    }

    .success-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .success-header {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
    }

    .success-icon {
      width: 80px;
      height: 80px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      animation: scaleIn 0.5s ease-out;
    }

    .success-icon i {
      font-size: 3rem;
      color: #10b981;
    }

    @keyframes scaleIn {
      0% {
        transform: scale(0);
      }
      50% {
        transform: scale(1.1);
      }
      100% {
        transform: scale(1);
      }
    }

    .success-header h1 {
      font-size: 2rem;
      margin: 0 0 0.5rem 0;
      font-weight: 600;
    }

    .success-header p {
      margin: 0;
      opacity: 0.9;
      font-size: 1.125rem;
    }

    .success-body {
      padding: 2rem;
    }

    .credentials-section {
      background: #fef3c7;
      border: 2px solid #fbbf24;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }

    .credentials-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.125rem;
      font-weight: 600;
      color: #92400e;
      margin-bottom: 1rem;
    }

    .credentials-title i {
      font-size: 1.5rem;
    }

    .credentials-info {
      background: white;
      padding: 1.5rem;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }

    .credential-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid #e5e7eb;
    }

    .credential-row:last-child {
      border-bottom: none;
    }

    .credential-label {
      font-weight: 500;
      color: #64748b;
    }

    .credential-value {
      font-family: 'Courier New', monospace;
      font-weight: 600;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .copy-btn {
      background: #247ba0;
      color: white;
      border: none;
      padding: 0.375rem 0.75rem;
      border-radius: 0.375rem;
      cursor: pointer;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    .copy-btn:hover {
      background: #1e6a8e;
    }

    .warning-text {
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
      color: #92400e;
      font-size: 0.9375rem;
      line-height: 1.6;
    }

    .warning-text i {
      margin-top: 0.25rem;
      flex-shrink: 0;
    }

    .reservation-details {
      background: #f8fafc;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .detail-section-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .detail-label {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 500;
    }

    .detail-value {
      font-size: 1rem;
      color: #1e293b;
      font-weight: 600;
    }

    .payment-summary {
      background: linear-gradient(135deg, rgba(36, 123, 160, 0.1), rgba(56, 145, 166, 0.1));
      border: 2px solid #247ba0;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .payment-summary-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 1rem;
    }

    .payment-row {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
    }

    .payment-row.total {
      border-top: 2px solid #247ba0;
      padding-top: 1rem;
      margin-top: 0.5rem;
      font-size: 1.25rem;
      font-weight: 700;
      color: #247ba0;
    }

    .next-steps {
      background: #f1f5f9;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .next-steps-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .steps-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .step-item {
      display: flex;
      gap: 1rem;
      padding: 0.75rem 0;
    }

    .step-number {
      width: 32px;
      height: 32px;
      background: #247ba0;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }

    .step-content {
      flex: 1;
      padding-top: 0.25rem;
    }

    .step-content strong {
      display: block;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }

    .step-content p {
      margin: 0;
      color: #64748b;
      font-size: 0.9375rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn {
      padding: 0.875rem 1.5rem;
      border: none;
      border-radius: 0.5rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s;
    }

    .btn-primary {
      background: linear-gradient(135deg, #247ba0, #3891a6);
      color: white;
      flex: 1;
      justify-content: center;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(36, 123, 160, 0.3);
    }

    .btn-secondary {
      background: #e2e8f0;
      color: #475569;
    }

    .btn-secondary:hover {
      background: #cbd5e1;
    }

    @media (max-width: 768px) {
      .success-container {
        margin: 1rem auto;
      }

      .success-header {
        padding: 2rem 1.5rem;
      }

      .success-header h1 {
        font-size: 1.5rem;
      }

      .success-body {
        padding: 1.5rem;
      }

      .detail-grid {
        grid-template-columns: 1fr;
      }

      .credential-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }
    }

    @media print {
      .action-buttons {
        display: none;
      }
    }
  </style>
@endsection

@section('content')
  <div class="success-container">
    <div class="success-card">
      <div class="success-header">
        <div class="success-icon">
          <i class="ri-checkbox-circle-fill"></i>
        </div>
        <h1>Reservation Confirmed!</h1>
        <p>Your booking has been successfully created</p>
      </div>

      <div class="success-body">
        <!-- Temporary Account Credentials -->
        <div class="credentials-section">
          <div class="credentials-title">
            <i class="ri-key-2-line"></i>
            Your Temporary Account Credentials
          </div>

          <div class="credentials-info">
            <div class="credential-row">
              <span class="credential-label">Email</span>
              <span class="credential-value">
                {{ $credentials['email'] }}
                <button class="copy-btn" onclick="copyToClipboard('{{ $credentials['email'] }}', this)">
                  <i class="ri-file-copy-line"></i> Copy
                </button>
              </span>
            </div>
            <div class="credential-row">
              <span class="credential-label">Password</span>
              <span class="credential-value">
                {{ $credentials['password'] }}
                <button class="copy-btn" onclick="copyToClipboard('{{ $credentials['password'] }}', this)">
                  <i class="ri-file-copy-line"></i> Copy
                </button>
              </span>
            </div>
            <div class="credential-row">
              <span class="credential-label">Reservation ID</span>
              <span class="credential-value">
                #{{ str_pad($credentials['reservation_id'], 6, '0', STR_PAD_LEFT) }}
              </span>
            </div>
          </div>

          <div class="warning-text">
            <i class="ri-information-line"></i>
            <span>
              <strong>Important:</strong> Please save these credentials. You'll need them to:
              <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                <li>Track your reservation status</li>
                <li>Make online payments</li>
                <li>View your booking details</li>
              </ul>
              These credentials have been sent to your email: <strong>{{ $credentials['email'] }}</strong>
            </span>
          </div>
        </div>

        <!-- Reservation Details -->
        <div class="reservation-details">
          <div class="detail-section-title">
            <i class="ri-file-list-3-line"></i>
            Reservation Details
          </div>

          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Guest Name</span>
              <span class="detail-value">{{ $reservation->first_name }} {{ $reservation->last_name }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Room Type</span>
              <span class="detail-value">{{ $reservation->room_type_name }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Room Number</span>
              <span class="detail-value">{{ $reservation->room_number }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Check-in</span>
              <span class="detail-value">{{ date('M d, Y', strtotime($reservation->arrival_date)) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Checkout</span>
              <span class="detail-value">{{ date('M d, Y', strtotime($reservation->departure_date)) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nights</span>
              <span class="detail-value">{{ $reservation->no_nights }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Guests</span>
              <span class="detail-value">{{ $reservation->adults }} Adult(s), {{ $reservation->children }} Child(ren)</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Booking Date</span>
              <span class="detail-value">{{ date('M d, Y', strtotime($reservation->booking_date)) }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Summary -->
        <div class="payment-summary">
          <div class="payment-summary-title">Payment Summary</div>

          <div class="payment-row">
            <span>Room Rate ({{ $reservation->no_nights }} night{{ $reservation->no_nights > 1 ? 's' : '' }})</span>
            <span>₱{{ number_format($reservation->rate_per_night * $reservation->no_nights, 2) }}</span>
          </div>

          <div class="payment-row">
            <span>Reservation Fee <span style="color: #10b981;">(Paid)</span></span>
            <span>₱{{ number_format($reservation->reservation_fee, 2) }}</span>
          </div>

          <div class="payment-row total">
            <span>Total Amount</span>
            <span>₱{{ number_format($reservation->total_amount, 2) }}</span>
          </div>

          <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #cbd5e1; color: #64748b; font-size: 0.9375rem;">
            <strong>Remaining Balance:</strong> ₱{{ number_format($reservation->balance, 2) }}
            <br>
            <small>To be paid {{ $reservation->payment_method === 'online' ? 'online through your account' : 'upon check-in' }}</small>
          </div>
        </div>

        <!-- Next Steps -->
        <div class="next-steps">
          <div class="next-steps-title">
            <i class="ri-map-pin-line"></i>
            What's Next?
          </div>

          <ul class="steps-list">
            <li class="step-item">
              <div class="step-number">1</div>
              <div class="step-content">
                <strong>Wait for Approval</strong>
                <p>Our staff will review your reservation. You'll receive an email notification once approved.</p>
              </div>
            </li>
            <li class="step-item">
              <div class="step-number">2</div>
              <div class="step-content">
                <strong>Log in to Your Account</strong>
                <p>Use your temporary credentials to track your reservation status and make payments.</p>
              </div>
            </li>
            <li class="step-item">
              <div class="step-number">3</div>
              <div class="step-content">
                <strong>Complete Payment</strong>
                <p>Pay the remaining balance {{ $reservation->payment_method === 'online' ? 'online' : 'upon arrival' }} before check-in.</p>
              </div>
            </li>
            <li class="step-item">
              <div class="step-number">4</div>
              <div class="step-content">
                <strong>Enjoy Your Stay</strong>
                <p>Check in on {{ date('M d, Y', strtotime($reservation->arrival_date)) }} and enjoy your stay!</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <a href="{{ route('frontpage.index') }}" class="btn btn-secondary">
            <i class="ri-home-line"></i> Back to Home
          </a>
          <button onclick="window.print()" class="btn btn-secondary">
            <i class="ri-printer-line"></i> Print Details
          </button>
          <a href="#" class="btn btn-primary">
            <i class="ri-login-box-line"></i> Login to Your Account
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('vendor-script')
  <script>
    function copyToClipboard(text, button) {
      navigator.clipboard.writeText(text).then(function() {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="ri-check-line"></i> Copied!';
        button.style.background = '#10b981';

        setTimeout(function() {
          button.innerHTML = originalHTML;
          button.style.background = '#247ba0';
        }, 2000);
      }, function(err) {
        console.error('Could not copy text: ', err);
      });
    }

    // Warn user before leaving the page
    window.addEventListener('beforeunload', function (e) {
      const message = 'Make sure you have saved your login credentials before leaving this page.';
      e.preventDefault();
      e.returnValue = message;
      return message;
    });
  </script>
@endsection
