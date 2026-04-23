@extends('layouts/contentNavbarLayout')
@section('title', 'Guests - Current Guests')

@section('content')
<div class="row gy-6">

  {{-- ── PAGE HEADER ──────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">Current Guests</h4>
            <p class="mb-0 text-body-secondary">Guests currently checked in to the hotel</p>
          </div>
          <span class="badge bg-label-success fs-6 px-3 py-2">
            <i class="icon-base ri ri-hotel-bed-line me-1"></i>
            {{ $stats['total_current'] }} Active
          </span>
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
            <div class="avatar-initial bg-success rounded shadow-xs">
              <i class="icon-base ri ri-user-location-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Guests In-House</p>
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
            <div class="avatar-initial bg-primary rounded shadow-xs">
              <i class="icon-base ri ri-hotel-bed-line icon-24px"></i>
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
              <i class="icon-base ri ri-logout-box-r-line icon-24px"></i>
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
                   placeholder="Name, email, or room number…">
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
            <a href="{{ route('super_admin.guests.current') }}"
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
        <h5 class="card-title m-0">In-House Guests</h5>
        <small class="text-body-secondary">{{ count($currentGuests) }} guest(s)</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Guest</th>
                <th>Room</th>
                <th>Room Type</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Nights</th>
                <th>Pax</th>
                <th>Balance</th>
                <th>Payment</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($currentGuests as $guest)
                @php
                  $checkoutDate  = \Carbon\Carbon::parse($guest->check_out_date);
                  $isCheckingOut = $checkoutDate->isToday();
                  $isOverdue     = $checkoutDate->isPast() && !$checkoutDate->isToday();
                @endphp
                <tr class="{{ $isOverdue ? 'table-danger' : ($isCheckingOut ? 'table-warning' : '') }}">
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial bg-label-success rounded-circle">
                          {{ strtoupper(substr($guest->first_name ?? '?', 0, 1)) }}
                        </div>
                      </div>
                      <div>
                        <span class="fw-medium d-block">
                          {{ trim($guest->first_name . ' ' . $guest->last_name) }}
                        </span>
                        <small class="text-body-secondary">{{ $guest->email }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-primary fs-6">{{ $guest->room_number }}</span>
                  </td>
                  <td>{{ $guest->room_type_name }}</td>
                  <td>
                    <span class="text-body-secondary">
                      {{ \Carbon\Carbon::parse($guest->check_in_at)->format('M d, Y') }}
                    </span>
                    <br>
                    <small class="text-body-secondary">
                      {{ \Carbon\Carbon::parse($guest->check_in_at)->format('h:i A') }}
                    </small>
                  </td>
                  <td>
                    @if($isOverdue)
                      <span class="badge bg-danger">
                        Overdue · {{ $checkoutDate->format('M d, Y') }}
                      </span>
                    @elseif($isCheckingOut)
                      <span class="badge bg-warning text-dark">
                        Today · {{ $checkoutDate->format('M d, Y') }}
                      </span>
                    @else
                      <span class="text-body-secondary">
                        {{ $checkoutDate->format('M d, Y') }}
                      </span>
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
                      <span class="fw-medium text-danger">₱{{ number_format($guest->balance, 2) }}</span>
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
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon"
                              data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);"
                           onclick="viewGuestProfile({{ $guest->user_id }})">
                          <i class="icon-base ri ri-eye-line me-2"></i>View Profile
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);"
                           onclick="viewStayDetails({{ json_encode($guest) }})">
                          <i class="icon-base ri ri-hotel-bed-line me-2"></i>Stay Details
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center py-5">
                    <i class="icon-base ri ri-hotel-bed-line icon-48px text-body-secondary mb-3 d-block"></i>
                    <p class="mb-0 text-body-secondary">No guests are currently checked in.</p>
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

{{-- ── STAY DETAILS MODAL ───────────────────────────────────── --}}
<div class="modal fade" id="stayDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-hotel-bed-line me-2"></i>
          Stay Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="stayDetailsContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- ── GUEST PROFILE MODAL ──────────────────────────────────── --}}
<div class="modal fade" id="guestProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-user-3-line me-2"></i>Guest Profile
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="guestProfileContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const ROUTES = {
    show:      '{{ route('super_admin.guests.show', ':id') }}',
    current:   '{{ route('super_admin.guests.current') }}',
    csrfToken: '{{ csrf_token() }}',
  };
</script>
<script src="{{ asset('js/guestjs/current_script.js') }}"></script>
@endsection