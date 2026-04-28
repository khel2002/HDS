@extends('layouts/contentNavbarLayout')
@section('title', 'Guests - Current Guests')

@section('content')
<div class="row gy-6">

  {{-- ── PAGE HEADER ───────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">Current Guests</h4>
            <p class="mb-0 text-body-secondary">Guests currently checked in</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── STAT CARDS ───────────────────────────────────────── --}}
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-primary rounded shadow-xs">
              <i class="icon-base ri ri-user-3-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Current Guests</p>
            <h5 class="mb-0">{{ $stats['total_current'] }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-success rounded shadow-xs">
              <i class="icon-base ri ri-door-lock-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Rooms Occupied</p>
            <h5 class="mb-0">{{ $stats['total_rooms_occupied'] }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-warning rounded shadow-xs">
              <i class="icon-base ri ri-logout-box-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Checking Out Today</p>
            <h5 class="mb-0">{{ $stats['expected_checkout_today'] }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-danger rounded shadow-xs">
              <i class="icon-base ri ri-money-dollar-circle-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Outstanding Balance</p>
            <h5 class="mb-0">₱{{ number_format($stats['outstanding_balance'], 2) }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── FILTERS ──────────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-4">
            <label class="form-label">Search Guest / Room</label>
            <input type="text" class="form-control" id="searchInput"
                   value="{{ $search }}"
                   placeholder="Name, email, or room…">
          </div>
          <div class="col-md-3">
            <label class="form-label">Room Type</label>
            <select id="roomTypeFilter" class="form-select">
              <option value="">All Types</option>
              @foreach($roomTypes as $type)
                <option value="{{ $type->room_type_id }}"
                        {{ $roomType == $type->room_type_id ? 'selected' : '' }}>
                  {{ $type->room_type_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button id="applyFilters" class="btn btn-primary d-block w-100">
              <i class="icon-base ri ri-search-line me-1"></i>Search
            </button>
          </div>
          <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <a href="{{ route('staff.guests.current') }}"
               class="btn btn-outline-secondary d-block w-100">
              <i class="icon-base ri ri-refresh-line me-1"></i>Reset Filters
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── TABLE ────────────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">Currently Checked-In</h5>
        <small class="text-body-secondary">{{ count($currentGuests) }} guest(s) found</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Guest Name</th>
                <th>Room</th>
                <th>Room Type</th>
                <th>Check-In</th>
                <th>Expected Check-Out</th>
                <th>Nights</th>
                <th>Pax</th>
                <th>Balance</th>
                <th>Payment</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($currentGuests as $i => $guest)
                <tr>
                  <td class="text-body-secondary">{{ $i + 1 }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial bg-label-primary rounded-circle">
                          {{ strtoupper(substr($guest->first_name ?? '?', 0, 1)) }}
                        </div>
                      </div>
                      <div>
                        <span class="fw-medium d-block">
                          {{ trim(($guest->first_name ?? '') . ' ' . ($guest->last_name ?? '')) ?: '—' }}
                        </span>
                        <small class="text-body-secondary">{{ $guest->email }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-secondary">{{ $guest->room_number }}</span>
                  </td>
                  <td>{{ $guest->room_type_name }}</td>
                  <td class="text-body-secondary">
                    {{ $guest->check_in_at ? \Carbon\Carbon::parse($guest->check_in_at)->format('M d, Y g:i A') : '—' }}
                  </td>
                  <td class="text-body-secondary">
                    @php
                      $isToday = $guest->check_out_date === now()->toDateString();
                    @endphp
                    <span class="{{ $isToday ? 'text-warning fw-medium' : '' }}">
                      {{ \Carbon\Carbon::parse($guest->check_out_date)->format('M d, Y') }}
                    </span>
                    @if($isToday)
                      <span class="badge bg-label-warning ms-1">Today</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-label-info rounded-pill">{{ $guest->no_nights }}N</span>
                  </td>
                  <td>
                    <small>
                      <i class="icon-base ri ri-user-line me-1"></i>{{ $guest->adults }}
                      @if($guest->children)
                        <i class="icon-base ri ri-parent-line me-1 ms-1"></i>{{ $guest->children }}
                      @endif
                    </small>
                  </td>
                  <td>
                    @if($guest->balance > 0)
                      <span class="text-danger fw-medium">₱{{ number_format($guest->balance, 2) }}</span>
                    @else
                      <span class="badge bg-label-success">Settled</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $pmColor = match($guest->payment_status) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'refunded'  => 'info',
                        default     => 'secondary',
                      };
                    @endphp
                    <span class="badge bg-label-{{ $pmColor }}">{{ ucfirst($guest->payment_status) }}</span>
                    <br>
                    <small class="text-body-secondary">{{ ucfirst($guest->payment_method ?? '—') }}</small>
                  </td>
                  <td>
                    <button type="button"
                            class="btn btn-sm btn-icon btn-outline-primary"
                            data-bs-toggle="tooltip"
                            title="View Details"
                            onclick="viewCurrentGuestDetails({{ json_encode($guest) }})">
                      <i class="icon-base ri ri-eye-line"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center py-5">
                    <i class="icon-base ri ri-user-3-line icon-48px text-body-secondary mb-3 d-block"></i>
                    <p class="mb-0 text-body-secondary">No guests currently checked in.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ── CURRENT GUEST DETAILS MODAL ─────────────────────────── --}}
<div class="modal fade" id="currentGuestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-hotel-line me-2"></i>
          Guest Stay Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="currentGuestContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const ROUTES = {
    current:   '{{ route('staff.guests.current') }}',
    csrfToken: '{{ csrf_token() }}',
  };

  function viewCurrentGuestDetails(guest) {
    const checkIn  = guest.check_in_at
      ? new Date(guest.check_in_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
      : '—';
    const checkOut = guest.check_out_date
      ? new Date(guest.check_out_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
      : '—';
    const balance  = parseFloat(guest.balance) > 0
      ? `<span class="text-danger fw-medium">₱${parseFloat(guest.balance).toLocaleString('en-US', {minimumFractionDigits:2})}</span>`
      : `<span class="badge bg-label-success">Settled</span>`;

    document.getElementById('currentGuestContent').innerHTML = `
      <div class="row g-4">
        <div class="col-md-6">
          <h6 class="text-body-secondary text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em;">Guest Info</h6>
          <table class="table table-sm table-borderless">
            <tr><th class="text-body-secondary" style="width:40%">Name</th>
                <td class="fw-medium">${(guest.first_name ?? '') + ' ' + (guest.last_name ?? '')}</td></tr>
            <tr><th class="text-body-secondary">Email</th><td>${guest.email ?? '—'}</td></tr>
            <tr><th class="text-body-secondary">Contact</th><td>${guest.contact_number || '—'}</td></tr>
            <tr><th class="text-body-secondary">Adults</th><td>${guest.adults}</td></tr>
            <tr><th class="text-body-secondary">Children</th><td>${guest.children ?? 0}</td></tr>
          </table>
        </div>
        <div class="col-md-6">
          <h6 class="text-body-secondary text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em;">Stay Info</h6>
          <table class="table table-sm table-borderless">
            <tr><th class="text-body-secondary" style="width:40%">Room</th>
                <td><span class="badge bg-label-secondary">${guest.room_number}</span></td></tr>
            <tr><th class="text-body-secondary">Type</th><td>${guest.room_type_name}</td></tr>
            <tr><th class="text-body-secondary">Rate/Night</th>
                <td>₱${parseFloat(guest.rate_per_night).toLocaleString('en-US', {minimumFractionDigits:2})}</td></tr>
            <tr><th class="text-body-secondary">Check-In</th><td>${checkIn}</td></tr>
            <tr><th class="text-body-secondary">Check-Out</th><td>${checkOut}</td></tr>
            <tr><th class="text-body-secondary">Nights</th><td>${guest.no_nights}</td></tr>
          </table>
        </div>
        <div class="col-12">
          <h6 class="text-body-secondary text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em;">Payment</h6>
          <table class="table table-sm table-borderless">
            <tr><th class="text-body-secondary" style="width:20%">Total</th>
                <td class="fw-medium">₱${parseFloat(guest.total_amount).toLocaleString('en-US', {minimumFractionDigits:2})}</td></tr>
            <tr><th class="text-body-secondary">Balance</th><td>${balance}</td></tr>
            <tr><th class="text-body-secondary">Status</th>
                <td><span class="badge bg-label-${guest.payment_status === 'completed' ? 'success' : guest.payment_status === 'pending' ? 'warning' : 'info'}">${guest.payment_status ? guest.payment_status.charAt(0).toUpperCase() + guest.payment_status.slice(1) : '—'}</span></td></tr>
            <tr><th class="text-body-secondary">Method</th>
                <td>${guest.payment_method ? guest.payment_method.charAt(0).toUpperCase() + guest.payment_method.slice(1) : '—'}</td></tr>
          </table>
        </div>
      </div>`;

    const modal = new bootstrap.Modal(document.getElementById('currentGuestModal'));
    modal.show();
  }

  // Filter button
  document.getElementById('applyFilters')?.addEventListener('click', () => {
    const params = new URLSearchParams();
    const search   = document.getElementById('searchInput')?.value.trim();
    const roomType = document.getElementById('roomTypeFilter')?.value;
    if (search)   params.set('search', search);
    if (roomType) params.set('room_type', roomType);
    window.location.href = ROUTES.current + (params.toString() ? '?' + params.toString() : '');
  });
</script>
@endsection