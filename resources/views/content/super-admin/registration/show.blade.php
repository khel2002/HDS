@extends('layouts/contentNavbarLayout')
@section('title', 'Registration #' . $registration->registration_id)

@section('content')
<div class="row gy-6">

  {{-- Breadcrumb / Back --}}
  <div class="col-12">
    <div class="d-flex align-items-center gap-2 mb-1">
      <a href="{{ route('super_admin.registration.all') }}" class="btn btn-sm btn-outline-secondary">
        <i class="icon-base ri ri-arrow-left-line me-1"></i> Back
      </a>
      <span class="text-muted">/</span>
      <span class="text-muted">Registration #{{ $registration->registration_id }}</span>
    </div>
  </div>

  {{-- Header Card --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            @php
              $leadGuest = $guests->firstWhere('is_primary', 1) ?? $guests->first();
              $guestName = $leadGuest ? trim($leadGuest->first_name . ' ' . $leadGuest->last_name) : '—';
              $initials  = $leadGuest ? strtoupper(substr($leadGuest->first_name, 0, 1) . substr($leadGuest->last_name, 0, 1)) : '?';
              $isActive  = is_null($registration->check_out_date);
            @endphp
            <div class="avatar avatar-lg">
              <div class="avatar-initial {{ $isActive ? 'bg-warning' : 'bg-secondary' }} rounded-circle fs-4 fw-bold">
                {{ $initials ?: '?' }}
              </div>
            </div>
            <div>
              <h4 class="mb-0">{{ $guestName }}</h4>
              <p class="mb-0 text-muted">{{ $registration->email }}</p>
              <div class="mt-1">
                @if($isActive)
                  <span class="badge bg-success">Active Stay</span>
                @else
                  <span class="badge bg-secondary">Checked Out</span>
                @endif
                <span class="badge bg-label-info ms-1">Rm {{ $registration->room_number }}</span>
                <span class="badge bg-label-secondary ms-1">{{ $registration->room_type_name }}</span>
              </div>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            @if($isActive)
              <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                <i class="icon-base ri ri-logout-box-line me-1"></i> Process Check-out
              </button>
            @endif
            <a href="{{ route('super_admin.registration.all') }}" class="btn btn-outline-secondary">
              <i class="icon-base ri ri-list-check me-1"></i> All Registrations
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Left Column --}}
  <div class="col-lg-8">

    {{-- Stay Details --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title m-0"><i class="icon-base ri ri-hotel-bed-line me-2"></i>Stay Details</h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-sm-6">
            <p class="text-muted mb-1 small fw-semibold text-uppercase">Registration ID</p>
            <p class="fw-bold mb-0">#{{ $registration->registration_id }}</p>
          </div>
          <div class="col-sm-6">
            <p class="text-muted mb-1 small fw-semibold text-uppercase">Reservation ID</p>
            <p class="fw-bold mb-0">#{{ $registration->reservation_id }}</p>
          </div>
          <div class="col-sm-6">
            <p class="text-muted mb-1 small fw-semibold text-uppercase">Room</p>
            <p class="fw-bold mb-0">{{ $registration->room_number }} — {{ $registration->room_type_name }}</p>
            <small class="text-muted">₱{{ number_format($registration->rate_per_night, 2) }}/night</small>
          </div>
          <div class="col-sm-6">
            <p class="text-muted mb-1 small fw-semibold text-uppercase">Number of Nights</p>
            <p class="fw-bold mb-0">{{ $registration->no_nights }} night{{ $registration->no_nights > 1 ? 's' : '' }}</p>
          </div>
          <div class="col-sm-6">
            <p class="text-muted mb-1 small fw-semibold text-uppercase">Checked In</p>
            <p class="fw-bold mb-0">{{ \Carbon\Carbon::parse($registration->check_in_at)->format('F d, Y') }}</p>
            <small class="text-muted">{{ \Carbon\Carbon::parse($registration->check_in_at)->format('h:i A') }}</small>
          </div>
          <div class="col-sm-6">
            <p class="text-muted mb-1 small fw-semibold text-uppercase">
              {{ $isActive ? 'Expected Check-out' : 'Checked Out' }}
            </p>
            @if($registration->check_out_date)
              <p class="fw-bold mb-0">{{ \Carbon\Carbon::parse($registration->check_out_date)->format('F d, Y') }}</p>
            @else
              <p class="fw-bold mb-0">{{ \Carbon\Carbon::parse($registration->expected_checkout)->format('F d, Y') }}</p>
              @if(\Carbon\Carbon::parse($registration->expected_checkout)->isPast())
                <span class="badge bg-danger">Overdue</span>
              @elseif(\Carbon\Carbon::parse($registration->expected_checkout)->isToday())
                <span class="badge bg-warning text-dark">Today</span>
              @endif
            @endif
          </div>
          @if($registration->purpose)
            <div class="col-12">
              <p class="text-muted mb-1 small fw-semibold text-uppercase">Special Requests / Purpose</p>
              <p class="mb-0">{{ $registration->purpose }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- All Guests for this Reservation --}}
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0"><i class="icon-base ri ri-group-line me-2"></i>Guest Details</h5>
        <span class="badge bg-label-primary">{{ $guests->count() }} guest{{ $guests->count() > 1 ? 's' : '' }}</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Arrival</th>
                <th>Departure</th>
                <th>Type</th>
              </tr>
            </thead>
            <tbody>
              @foreach($guests as $guest)
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial {{ $guest->is_primary ? 'bg-primary' : 'bg-label-secondary' }} rounded-circle">
                          {{ strtoupper(substr($guest->first_name,0,1) . substr($guest->last_name,0,1)) }}
                        </div>
                      </div>
                      <div>
                        <div class="fw-semibold">{{ $guest->first_name }} {{ $guest->middle_name }} {{ $guest->last_name }}</div>
                        @if($guest->dob)
                          <small class="text-muted">DOB: {{ \Carbon\Carbon::parse($guest->dob)->format('M d, Y') }}</small>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>{{ $guest->contact_number ?: '—' }}</td>
                  <td>{{ $guest->arrival_date ? \Carbon\Carbon::parse($guest->arrival_date)->format('M d, Y') : '—' }}</td>
                  <td>{{ $guest->departure_date ? \Carbon\Carbon::parse($guest->departure_date)->format('M d, Y') : '—' }}</td>
                  <td>
                    @if($guest->is_primary)
                      <span class="badge bg-primary">Lead Guest</span>
                    @else
                      <span class="badge bg-label-secondary">Additional</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  {{-- Right Column --}}
  <div class="col-lg-4">

    {{-- Payment Summary --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title m-0"><i class="icon-base ri ri-money-dollar-circle-line me-2"></i>Payment Summary</h5>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Method</span>
          @if($registration->payment_method === 'online')
            <span class="badge bg-label-success"><i class="ri-bank-card-line me-1"></i>Online</span>
          @else
            <span class="badge bg-label-warning"><i class="ri-money-dollar-circle-line me-1"></i>Cash</span>
          @endif
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Status</span>
          @if($registration->payment_status === 'completed')
            <span class="badge bg-success">Paid</span>
          @else
            <span class="badge bg-danger">Pending</span>
          @endif
        </div>
        @if($registration->paid_at)
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Paid at</span>
            <span class="small">{{ \Carbon\Carbon::parse($registration->paid_at)->format('M d, Y h:i A') }}</span>
          </div>
        @endif
        <hr>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Total Amount</span>
          <span class="fw-semibold">₱{{ number_format($registration->total_amount, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Reservation Fee</span>
          <span class="text-success fw-semibold">₱{{ number_format($registration->reservation_fee, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Balance Due</span>
          <span class="fw-bold {{ $registration->balance > 0 ? 'text-danger' : 'text-success' }}">
            ₱{{ number_format($registration->balance, 2) }}
          </span>
        </div>
      </div>
    </div>

    {{-- Timeline --}}
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0"><i class="icon-base ri ri-time-line me-2"></i>Timeline</h5>
      </div>
      <div class="card-body">
        <ul class="timeline timeline-center">
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-success"></span>
            <div class="timeline-event">
              <div class="timeline-header">
                <h6 class="mb-0">Reservation Created</h6>
                <small class="text-muted">{{ \Carbon\Carbon::parse($registration->created_at)->format('M d, Y') }}</small>
              </div>
              <p class="mt-1 mb-0 small text-muted">Guest made reservation online</p>
            </div>
          </li>
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-primary"></span>
            <div class="timeline-event">
              <div class="timeline-header">
                <h6 class="mb-0">Checked In</h6>
                <small class="text-muted">{{ \Carbon\Carbon::parse($registration->check_in_at)->format('M d, Y h:i A') }}</small>
              </div>
              <p class="mt-1 mb-0 small text-muted">Room {{ $registration->room_number }} occupied</p>
            </div>
          </li>
          @if($registration->check_out_date)
            <li class="timeline-item">
              <span class="timeline-point timeline-point-secondary"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">Checked Out</h6>
                  <small class="text-muted">{{ \Carbon\Carbon::parse($registration->check_out_date)->format('M d, Y') }}</small>
                </div>
                <p class="mt-1 mb-0 small text-muted">Room marked available</p>
              </div>
            </li>
          @else
            <li class="timeline-item">
              <span class="timeline-point timeline-point-warning"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">Expected Check-out</h6>
                  <small class="text-muted">{{ \Carbon\Carbon::parse($registration->expected_checkout)->format('M d, Y') }}</small>
                </div>
                <p class="mt-1 mb-0 small text-muted">Guest still staying</p>
              </div>
            </li>
          @endif
        </ul>
      </div>
    </div>

  </div>

</div>

{{-- Check-out Modal --}}
@if($isActive)
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="icon-base ri ri-logout-box-line me-2"></i>Confirm Check-out</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        @if($registration->balance > 0)
          <div class="alert alert-warning">
            <i class="icon-base ri ri-money-dollar-circle-line me-2"></i>
            <strong>Collect remaining balance of ₱{{ number_format($registration->balance, 2) }}</strong> before proceeding.
          </div>
        @else
          <div class="alert alert-success">
            <i class="icon-base ri ri-checkbox-circle-line me-2"></i>
            Guest is fully paid. Ready to check out.
          </div>
        @endif
        <p class="mb-1">Check out <strong>{{ $guestName }}</strong> from <strong>Room {{ $registration->room_number }}</strong>?</p>
        <p class="text-muted small mb-0">The room will be marked as <strong>Available</strong> after checkout.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('super_admin.registration.process-check-out', $registration->registration_id) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-warning">
            <i class="icon-base ri ri-logout-box-line me-1"></i> Confirm Check-out
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

@endsection