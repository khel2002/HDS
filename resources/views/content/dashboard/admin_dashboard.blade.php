@extends('layouts/contentNavbarLayout')

@section('title', 'Admin Dashboard')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --azure-primary: #0080FF;
      --azure-light: #4DA6FF;
      --azure-lighter: #E6F3FF;
      --azure-dark: #0066CC;
      --azure-slsu: #060E4D;
      --success: #00D68F;
      --warning: #FFAA00;
      --danger: #FF3D71;
      --purple: #8B5CF6;
      --cyan: #00D4FF;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #F8F9FB;
      color: #2E3A59;
    }

    /* Container adjustments for mobile */
    @media (max-width: 768px) {
      .container-xxl {
        padding-left: 1rem;
        padding-right: 1rem;
      }
    }

    @media (max-width: 576px) {
      .container-xxl {
        padding-left: 0.875rem;
        padding-right: 0.875rem;
      }
    }

    /* Dashboard Header */
    .dashboard-hero {
      background: linear-gradient(135deg, var(--azure-slsu) 0%, #0B1678 50%, var(--azure-dark) 100%);
      border-radius: 16px;
      padding: 1.75rem 2rem;
      margin-bottom: 1.5rem;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(6, 14, 77, 0.15);
    }

    .dashboard-hero::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 250px;
      height: 250px;
      background: radial-gradient(circle, rgba(0, 163, 255, 0.15) 0%, transparent 70%);
    }

    .dashboard-hero-content {
      position: relative;
      z-index: 1;
    }

    .dashboard-hero h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #fff;
      margin: 0 0 0.25rem 0;
      letter-spacing: -0.01em;
    }

    .dashboard-hero p {
      color: rgba(255, 255, 255, 0.75);
      font-size: 0.875rem;
      margin: 0;
      font-weight: 500;
    }

    /* Stats Grid - 4 Columns */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 1.25rem 1.5rem;
      box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
      border: 1px solid #E8ECF2;
      transition: all 0.25s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
      border-color: var(--azure-light);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--card-accent);
    }

    .stat-card.azure::before {
      background: var(--azure-primary);
    }

    .stat-card.success::before {
      background: var(--success);
    }

    .stat-card.warning::before {
      background: var(--warning);
    }

    .stat-card.danger::before {
      background: var(--danger);
    }

    .stat-card.purple::before {
      background: var(--purple);
    }

    .stat-card.cyan::before {
      background: var(--cyan);
    }

    .stat-card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 0.75rem;
    }

    .stat-icon-wrapper {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      flex-shrink: 0;
    }

    .stat-card.azure .stat-icon-wrapper {
      background: rgba(0, 128, 255, 0.1);
      color: var(--azure-primary);
    }

    .stat-card.success .stat-icon-wrapper {
      background: rgba(0, 214, 143, 0.1);
      color: var(--success);
    }

    .stat-card.warning .stat-icon-wrapper {
      background: rgba(255, 170, 0, 0.1);
      color: var(--warning);
    }

    .stat-card.danger .stat-icon-wrapper {
      background: rgba(255, 61, 113, 0.1);
      color: var(--danger);
    }

    .stat-card.purple .stat-icon-wrapper {
      background: rgba(139, 92, 246, 0.1);
      color: var(--purple);
    }

    .stat-card.cyan .stat-icon-wrapper {
      background: rgba(0, 212, 255, 0.1);
      color: var(--cyan);
    }

    .stat-number {
      font-size: 1.75rem;
      font-weight: 800;
      color: #1A2332;
      line-height: 1.1;
      margin-bottom: 0.25rem;
      letter-spacing: -0.02em;
    }

    .stat-label {
      font-size: 0.813rem;
      color: #6C7B95;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .stat-footer {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      font-size: 0.75rem;
      color: #8C98AC;
      padding-top: 0.75rem;
      border-top: 1px solid #F1F3F7;
    }

    .stat-footer.positive {
      color: var(--success);
    }

    .stat-footer.negative {
      color: var(--danger);
    }

    .stat-footer i {
      font-size: 0.875rem;
    }

    /* Section Cards */
    .section-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
      border: 1px solid #E8ECF2;
      margin-bottom: 1.5rem;
      overflow: hidden;
    }

    .section-card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #F1F3F7;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #FAFBFC;
    }

    .section-card-title {
      font-size: 1rem;
      font-weight: 700;
      color: #1A2332;
      margin: 0;
    }

    .section-card-body {
      padding: 1.5rem;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 0.875rem;
    }

    .quick-action-btn {
      background: linear-gradient(135deg, var(--azure-primary), var(--azure-light));
      border: none;
      border-radius: 12px;
      padding: 1.25rem 0.875rem;
      text-align: center;
      text-decoration: none;
      color: #fff;
      transition: all 0.25s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.625rem;
    }

    .quick-action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 128, 255, 0.3);
      color: #fff;
    }

    .quick-action-icon {
      font-size: 1.5rem;
    }

    .quick-action-label {
      font-size: 0.813rem;
      font-weight: 600;
      line-height: 1.2;
    }

    /* Tables */
    .modern-table {
      width: 100%;
      min-width: 500px;
    }

    .modern-table thead th {
      padding: 0.875rem 1rem;
      font-size: 0.688rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #6C7B95;
      background: #FAFBFC;
      border-bottom: 2px solid #E8ECF2;
      white-space: nowrap;
    }

    .modern-table tbody td {
      padding: 1rem;
      font-size: 0.813rem;
      color: #2E3A59;
      border-bottom: 1px solid #F1F3F7;
      font-weight: 500;
    }

    .modern-table tbody tr {
      transition: background 0.15s ease;
    }

    .modern-table tbody tr:hover {
      background: #FAFBFC;
    }

    .modern-table tbody tr:last-child td {
      border-bottom: none;
    }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
      .modern-table {
        min-width: 450px;
      }
    }

    @media (max-width: 576px) {
      .modern-table {
        min-width: 400px;
      }
    }

    /* Badges */
    .status-badge-modern {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.688rem;
      font-weight: 700;
    }

    .status-badge-modern.success {
      background: linear-gradient(135deg, #E6FFF5, #CCFFE8);
      color: #00854D;
    }

    .status-badge-modern.warning {
      background: linear-gradient(135deg, #FFF8E6, #FFF0CC);
      color: #996A00;
    }

    .status-badge-modern.danger {
      background: linear-gradient(135deg, #FFE8ED, #FFCCD6);
      color: #B30028;
    }

    .status-badge-modern.info {
      background: linear-gradient(135deg, #EBF4FF, #D6E9FF);
      color: #004C99;
    }

    /* Button */
    .btn-modern-action {
      background: var(--azure-primary);
      color: #fff;
      border: none;
      padding: 0.5rem 1.25rem;
      border-radius: 10px;
      font-size: 0.813rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.25s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
    }

    .btn-modern-action:hover {
      background: var(--azure-dark);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 128, 255, 0.25);
      color: #fff;
    }

    /* Chart Container */
    .chart-container-modern {
      position: relative;
      height: 260px;
      padding: 0.5rem;
    }

    /* Room Status Cards */
    .status-cards-grid {
      display: grid;
      gap: 0.875rem;
    }

    .status-card-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.25rem;
      background: #FAFBFC;
      border-radius: 10px;
      border: 1px solid #E8ECF2;
      transition: all 0.2s ease;
    }

    .status-card-item:hover {
      background: #fff;
      border-color: var(--azure-light);
      transform: translateX(3px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .status-card-left {
      display: flex;
      align-items: center;
      gap: 0.875rem;
    }

    .status-indicator-dot {
      width: 10px;
      height: 10px;
      border-radius: 3px;
    }

    .status-indicator-dot.available {
      background: var(--success);
    }

    .status-indicator-dot.occupied {
      background: var(--azure-primary);
    }

    .status-indicator-dot.maintenance {
      background: var(--warning);
    }

    .status-indicator-dot.reserved {
      background: #8C98AC;
    }

    .status-card-label {
      font-size: 0.813rem;
      color: #2E3A59;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-card-value {
      font-size: 1.125rem;
      font-weight: 800;
      color: #1A2332;
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(15px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in {
      animation: fadeInUp 0.4s ease-out forwards;
      opacity: 0;
    }

    .delay-1 {
      animation-delay: 0.05s;
    }

    .delay-2 {
      animation-delay: 0.1s;
    }

    .delay-3 {
      animation-delay: 0.15s;
    }

    .delay-4 {
      animation-delay: 0.2s;
    }

    .delay-5 {
      animation-delay: 0.25s;
    }

    .delay-6 {
      animation-delay: 0.3s;
    }

    .delay-7 {
      animation-delay: 0.35s;
    }

    .delay-8 {
      animation-delay: 0.4s;
    }

    /* Responsive */
    /* Column adjustments for mobile */
    @media (max-width: 991px) {
      .row>[class*='col-lg'] {
        margin-bottom: 1.25rem;
      }
    }

    @media (max-width: 1400px) {
      .stats-container {
        grid-template-columns: repeat(4, 1fr);
      }
    }

    @media (max-width: 1200px) {
      .stats-container {
        grid-template-columns: repeat(3, 1fr);
      }

      .dashboard-hero h1 {
        font-size: 1.375rem;
      }
    }

    @media (max-width: 992px) {
      .stats-container {
        grid-template-columns: repeat(2, 1fr);
      }

      .quick-actions {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (max-width: 768px) {
      .dashboard-hero {
        padding: 1.5rem;
        margin-bottom: 1.25rem;
      }

      .dashboard-hero h1 {
        font-size: 1.25rem;
      }

      .dashboard-hero p {
        font-size: 0.813rem;
      }

      .stats-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.875rem;
      }

      .stat-card {
        padding: 1rem 1.25rem;
      }

      .stat-number {
        font-size: 1.5rem;
      }

      .stat-label {
        font-size: 0.75rem;
      }

      .quick-actions {
        grid-template-columns: repeat(2, 1fr);
      }

      .section-card-header {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 1rem 1.25rem;
      }

      .section-card-body {
        padding: 1.25rem;
      }

      .section-card-title {
        font-size: 0.938rem;
      }

      .chart-container-modern {
        height: 220px;
      }

      .row {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
      }

      .row>* {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
      }
    }

    @media (max-width: 576px) {
      .stats-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
      }

      .stat-card {
        padding: 0.875rem 1rem;
      }

      .stat-icon-wrapper {
        width: 36px;
        height: 36px;
        font-size: 1.125rem;
      }

      .stat-number {
        font-size: 1.375rem;
      }

      .stat-label {
        font-size: 0.688rem;
      }

      .stat-footer {
        font-size: 0.688rem;
        padding-top: 0.625rem;
      }

      .quick-actions {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
      }

      .quick-action-btn {
        padding: 1rem 0.75rem;
      }

      .quick-action-icon {
        font-size: 1.25rem;
      }

      .quick-action-label {
        font-size: 0.75rem;
      }

      .modern-table {
        font-size: 0.75rem;
      }

      .modern-table thead th {
        padding: 0.75rem 0.875rem;
        font-size: 0.625rem;
      }

      .modern-table tbody td {
        padding: 0.875rem;
        font-size: 0.75rem;
      }

      .row {
        margin-left: -0.375rem;
        margin-right: -0.375rem;
      }

      .row>* {
        padding-left: 0.375rem;
        padding-right: 0.375rem;
      }
    }

    @media (max-width: 480px) {
      .stats-container {
        grid-template-columns: 1fr;
        gap: 0.75rem;
      }

      .quick-actions {
        grid-template-columns: 1fr;
      }

      .dashboard-hero {
        padding: 1.25rem;
        border-radius: 12px;
      }

      .dashboard-hero h1 {
        font-size: 1.125rem;
      }

      .section-card {
        border-radius: 10px;
      }

      .chart-container-modern {
        height: 200px;
      }
    }

    @media (max-width: 380px) {
      .stat-card {
        padding: 0.75rem 0.875rem;
      }

      .stat-number {
        font-size: 1.25rem;
      }

      .stat-icon-wrapper {
        width: 32px;
        height: 32px;
        font-size: 1rem;
      }
    }
  </style>
@endsection

@section('content')
  <!-- Dashboard Hero -->
  <div class="dashboard-hero animate-fade-in">
    <div class="dashboard-hero-content">
      <h1>Welcome back, {{ auth()->user()->first_name ?? 'Administrator' }}</h1>
      <p>{{ now()->format('l, F j, Y') }} — Here's your hotel performance overview</p>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="stats-container">
    <!-- Check-ins Today -->
    <div class="stat-card azure animate-fade-in delay-1">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-login-box-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['today_check_ins'] }}</div>
      <div class="stat-label">Check-ins Today</div>
      <div class="stat-footer">
        <i class="ri-calendar-line"></i>
        <span>Expected arrivals</span>
      </div>
    </div>

    <!-- Check-outs Today -->
    <div class="stat-card warning animate-fade-in delay-2">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-logout-box-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['today_check_outs'] }}</div>
      <div class="stat-label">Check-outs Today</div>
      <div class="stat-footer">
        <i class="ri-calendar-check-line"></i>
        <span>Scheduled departures</span>
      </div>
    </div>

    <!-- Active Guests -->
    <div class="stat-card purple animate-fade-in delay-3">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-user-3-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['active_guests'] }}</div>
      <div class="stat-label">Active Guests</div>
      <div class="stat-footer">
        <span>Currently staying</span>
      </div>
    </div>

    <!-- Occupancy Rate -->
    <div class="stat-card success animate-fade-in delay-4">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-pie-chart-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['occupancy_rate'] }}%</div>
      <div class="stat-label">Occupancy Rate</div>
      <div class="stat-footer {{ $stats['occupancy_rate'] >= 75 ? 'positive' : 'negative' }}">
        <i class="ri-arrow-{{ $stats['occupancy_rate'] >= 75 ? 'up' : 'down' }}-line"></i>
        <span>{{ $stats['occupied_rooms'] }}/{{ $stats['total_rooms'] }} rooms</span>
      </div>
    </div>

    <!-- Available Rooms -->
    <div class="stat-card cyan animate-fade-in delay-5">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-hotel-bed-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['available_rooms'] }}</div>
      <div class="stat-label">Available Rooms</div>
      <div class="stat-footer">
        <span>Ready for booking</span>
      </div>
    </div>

    <!-- Today's Revenue -->
    <div class="stat-card success animate-fade-in delay-6">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-money-dollar-circle-line"></i>
        </div>
      </div>
      <div class="stat-number">₱{{ number_format($stats['today_revenue'], 0) }}</div>
      <div class="stat-label">Today's Revenue</div>
      <div class="stat-footer positive">
        <i class="ri-arrow-up-line"></i>
        <span>{{ $stats['total_payments_today'] }} transactions</span>
      </div>
    </div>

    <!-- Pending Reservations -->
    <div class="stat-card warning animate-fade-in delay-7">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-calendar-check-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['pending_reservations'] }}</div>
      <div class="stat-label">Pending Reservations</div>
      <div class="stat-footer">
        <span>Awaiting approval</span>
      </div>
    </div>

    <!-- Service Requests -->
    <div class="stat-card danger animate-fade-in delay-8">
      <div class="stat-card-top">
        <div class="stat-icon-wrapper">
          <i class="ri-customer-service-2-line"></i>
        </div>
      </div>
      <div class="stat-number">{{ $stats['pending_service_requests'] }}</div>
      <div class="stat-label">Service Requests</div>
      <div class="stat-footer">
        <span>{{ $stats['in_progress_service_requests'] }} in progress</span>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">Quick Actions</h2>
    </div>
    <div class="section-card-body">
      <div class="quick-actions">
        <a href="/super-admin/registration/check-in" class="quick-action-btn">
          <i class="ri-login-box-line quick-action-icon"></i>
          <span class="quick-action-label">Check-in Guest</span>
        </a>
        <a href="/super-admin/registration/check-out" class="quick-action-btn">
          <i class="ri-logout-box-line quick-action-icon"></i>
          <span class="quick-action-label">Check-out Guest</span>
        </a>
        <a href="/super-admin/reservations/all" class="quick-action-btn">
          <i class="ri-calendar-check-line quick-action-icon"></i>
          <span class="quick-action-label">View Reservations</span>
        </a>
        <a href="/super-admin/payments/record" class="quick-action-btn">
          <i class="ri-wallet-3-line quick-action-icon"></i>
          <span class="quick-action-label">Record Payment</span>
        </a>
        <a href="/super-admin/rooms/all" class="quick-action-btn">
          <i class="ri-hotel-bed-line quick-action-icon"></i>
          <span class="quick-action-label">Manage Rooms</span>
        </a>
        <a href="/super-admin/services/all" class="quick-action-btn">
          <i class="ri-customer-service-2-line quick-action-icon"></i>
          <span class="quick-action-label">Service Requests</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Two Column Layout -->
  <div class="row">
    <!-- Recent Reservations -->
    <div class="col-lg-6 mb-4">
      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Recent Reservations</h2>
          <a href="/super-admin/reservations/all" class="btn-modern-action">
            View All <i class="ri-arrow-right-line"></i>
          </a>
        </div>
        <div class="section-card-body">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Room</th>
                  <th>Check-in</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentReservations as $reservation)
                  <tr>
                    <td>
                      <div class="fw-semibold">{{ $reservation->first_name }} {{ $reservation->last_name }}</div>
                    </td>
                    <td>{{ $reservation->room_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}</td>
                    <td>
                      <span
                        class="status-badge-modern {{ $reservation->reservation_status == 'approved'
                            ? 'success'
                            : ($reservation->reservation_status == 'pending'
                                ? 'warning'
                                : ($reservation->reservation_status == 'rejected'
                                    ? 'danger'
                                    : 'info')) }}">
                        {{ ucfirst($reservation->reservation_status) }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-4" style="color: #8C98AC;">No recent reservations</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Registrations -->
    <div class="col-lg-6 mb-4">
      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Active Registrations</h2>
          <a href="/super-admin/registration/active" class="btn-modern-action">
            View All <i class="ri-arrow-right-line"></i>
          </a>
        </div>
        <div class="section-card-body">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Room</th>
                  <th>Check-out</th>
                </tr>
              </thead>
              <tbody>
                @forelse($activeRegistrations as $registration)
                  <tr>
                    <td>
                      <div class="fw-semibold">{{ $registration->first_name }} {{ $registration->last_name }}</div>
                    </td>
                    <td>{{ $registration->room_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($registration->check_out_date)->format('M d, Y') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center py-4" style="color: #8C98AC;">No active registrations</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Service & Payments -->
  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Pending Service Requests</h2>
          <a href="/super-admin/services/all" class="btn-modern-action">
            View All <i class="ri-arrow-right-line"></i>
          </a>
        </div>
        <div class="section-card-body">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Room</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pendingServiceRequests as $request)
                  <tr>
                    <td>{{ $request->first_name }} {{ $request->last_name }}</td>
                    <td>{{ $request->room_number }}</td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $request->service_type) }}</td>
                    <td>
                      <span
                        class="status-badge-modern {{ $request->request_status == 'pending'
                            ? 'warning'
                            : ($request->request_status == 'in_progress'
                                ? 'info'
                                : 'success') }}">
                        {{ ucfirst(str_replace('_', ' ', $request->request_status)) }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-4" style="color: #8C98AC;">No pending service requests
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Recent Payments</h2>
          <a href="/super-admin/payments/all" class="btn-modern-action">
            View All <i class="ri-arrow-right-line"></i>
          </a>
        </div>
        <div class="section-card-body">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Amount</th>
                  <th>Method</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentPayments as $payment)
                  <tr>
                    <td>{{ $payment->first_name }} {{ $payment->last_name }}</td>
                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                    <td class="text-capitalize">{{ $payment->payment_method }}</td>
                    <td>
                      <span
                        class="status-badge-modern {{ $payment->payment_status == 'completed'
                            ? 'success'
                            : ($payment->payment_status == 'pending'
                                ? 'warning'
                                : 'danger') }}">
                        {{ ucfirst($payment->payment_status) }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-4" style="color: #8C98AC;">No recent payments</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Revenue Analytics (7 Days)</h2>
        </div>
        <div class="section-card-body">
          <div class="chart-container-modern">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Occupancy Trends (30 Days)</h2>
        </div>
        <div class="section-card-body">
          <div class="chart-container-modern">
            <canvas id="occupancyChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Room Type Statistics -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">Room Type Statistics</h2>
    </div>
    <div class="section-card-body">
      <div class="table-responsive">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Room Type</th>
              <th>Rate/Night</th>
              <th>Total Rooms</th>
              <th>Available</th>
              <th>Occupied</th>
              <th>Occupancy Rate</th>
            </tr>
          </thead>
          <tbody>
            @forelse($roomTypeStats as $roomType)
              <tr>
                <td class="fw-semibold">{{ $roomType->room_type_name }}</td>
                <td>₱{{ number_format($roomType->rate_per_night, 2) }}</td>
                <td>{{ $roomType->total_rooms }}</td>
                <td><span class="status-badge-modern success">{{ $roomType->available_rooms }}</span></td>
                <td><span class="status-badge-modern info">{{ $roomType->occupied_rooms }}</span></td>
                <td>
                  @php
                    $rate =
                        $roomType->total_rooms > 0
                            ? round(($roomType->occupied_rooms / $roomType->total_rooms) * 100, 1)
                            : 0;
                  @endphp
                  <span
                    class="status-badge-modern {{ $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                    {{ $rate }}%
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4" style="color: #8C98AC;">No room types available</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Charts
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6C7B95';

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
      new Chart(revenueCtx, {
        type: 'bar',
        data: {
          labels: {!! json_encode(array_column($revenueChartData, 'date')) !!},
          datasets: [{
            label: 'Revenue',
            data: {!! json_encode(array_column($revenueChartData, 'revenue')) !!},
            backgroundColor: 'rgba(0, 128, 255, 0.15)',
            borderColor: '#0080FF',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(0, 128, 255, 0.3)',
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#1A2332',
              padding: 12,
              cornerRadius: 8,
              displayColors: false,
              titleFont: {
                size: 12,
                weight: '600'
              },
              bodyFont: {
                size: 14,
                weight: '700'
              },
              callbacks: {
                label: (context) => '₱' + context.parsed.y.toLocaleString('en-US', {
                  minimumFractionDigits: 2
                })
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              border: {
                display: false
              },
              grid: {
                color: '#F1F3F7',
                drawTicks: false
              },
              ticks: {
                padding: 8,
                font: {
                  size: 11,
                  weight: '600'
                },
                callback: (value) => '₱' + value.toLocaleString()
              }
            },
            x: {
              border: {
                display: false
              },
              grid: {
                display: false
              },
              ticks: {
                padding: 8,
                font: {
                  size: 11,
                  weight: '600'
                }
              }
            }
          }
        }
      });
    }

    const occupancyCtx = document.getElementById('occupancyChart');
    if (occupancyCtx) {
      new Chart(occupancyCtx, {
        type: 'line',
        data: {
          labels: {!! json_encode(array_column($occupancyChartData, 'date')) !!},
          datasets: [{
            label: 'Occupancy',
            data: {!! json_encode(array_column($occupancyChartData, 'occupancy')) !!},
            borderColor: '#00D68F',
            backgroundColor: 'rgba(0, 214, 143, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#00D68F',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2.5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#1A2332',
              padding: 12,
              cornerRadius: 8,
              displayColors: false,
              titleFont: {
                size: 12,
                weight: '600'
              },
              bodyFont: {
                size: 14,
                weight: '700'
              },
              callbacks: {
                label: (context) => context.parsed.y.toFixed(1) + '%'
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              border: {
                display: false
              },
              grid: {
                color: '#F1F3F7',
                drawTicks: false
              },
              ticks: {
                padding: 8,
                font: {
                  size: 11,
                  weight: '600'
                },
                callback: (value) => value + '%'
              }
            },
            x: {
              border: {
                display: false
              },
              grid: {
                display: false
              },
              ticks: {
                padding: 8,
                font: {
                  size: 11,
                  weight: '600'
                }
              }
            }
          }
        }
      });
    }

    const roomStatusCtx = document.getElementById('roomStatusChart');
    if (roomStatusCtx) {
      const roomStatusData = {!! json_encode($roomStatusDistribution) !!};
      new Chart(roomStatusCtx, {
        type: 'doughnut',
        data: {
          labels: roomStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
          datasets: [{
            data: roomStatusData.map(item => item.count),
            backgroundColor: ['#00D68F', '#0080FF', '#FFAA00', '#8C98AC'],
            borderWidth: 0,
            hoverBorderWidth: 2,
            hoverBorderColor: '#fff',
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '70%',
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#1A2332',
              padding: 12,
              cornerRadius: 8,
              displayColors: false,
              titleFont: {
                size: 12,
                weight: '600'
              },
              bodyFont: {
                size: 14,
                weight: '700'
              },
              callbacks: {
                label: (context) => {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed / total) * 100).toFixed(1);
                  return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                }
              }
            }
          }
        }
      });
    }
  </script>
@endsection
