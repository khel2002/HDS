@extends('layouts/contentNavbarLayout')
@section('title', 'Check-in Guest')

@section('content')
<div class="row gy-6">

  {{-- Page Header --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1"><i class="icon-base ri ri-login-box-line me-2 text-primary"></i>Check-in Guest</h4>
            <p class="mb-0 text-muted">Approved reservations awaiting check-in. Verify guest ID before proceeding.</p>
          </div>
          <span class="badge bg-label-primary fs-6 px-3 py-2" id="statBadgeTotal">{{ $reservations->count() }} Pending</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Toast --}}
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="toastMsg" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body fw-semibold" id="toastText"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  {{-- Stats --}}
  @php
    $today    = \Carbon\Carbon::today();
    $overdue  = $reservations->filter(fn($r) => \Carbon\Carbon::parse($r->check_in_date)->lt($today));
    $dueToday = $reservations->filter(fn($r) => \Carbon\Carbon::parse($r->check_in_date)->isToday());
    $upcoming = $reservations->filter(fn($r) => \Carbon\Carbon::parse($r->check_in_date)->gt($today));
  @endphp

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-primary rounded shadow-xs"><i class="icon-base ri ri-calendar-check-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Total Pending</p><h5 class="mb-0" id="statTotal">{{ $reservations->count() }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-success rounded shadow-xs"><i class="icon-base ri ri-time-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Due Today</p><h5 class="mb-0" id="statToday">{{ $dueToday->count() }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-danger rounded shadow-xs"><i class="icon-base ri ri-alarm-warning-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Overdue</p><h5 class="mb-0" id="statOverdue">{{ $overdue->count() }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-info rounded shadow-xs"><i class="icon-base ri ri-calendar-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Upcoming</p><h5 class="mb-0" id="statUpcoming">{{ $upcoming->count() }}</h5></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Search Guest</label>
            <input type="text" class="form-control" id="searchInput" placeholder="Name, email, room...">
          </div>
          <div class="col-md-3">
            <label class="form-label">Payment Method</label>
            <select class="form-select" id="filterPayment">
              <option value="">All</option>
              <option value="cash">Cash</option>
              <option value="online">Online</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Check-in Date</label>
            <select class="form-select" id="filterDate">
              <option value="">All</option>
              <option value="overdue">Overdue</option>
              <option value="today">Today</option>
              <option value="upcoming">Upcoming</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-outline-secondary d-block w-100" onclick="resetFilters()">
              <i class="icon-base ri ri-refresh-line me-1"></i> Reset
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">Approved Reservations — Awaiting Check-in</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Guest (Lead)</th>
                <th>Room</th>
                <th>Check-in Date</th>
                <th>Check-out Date</th>
                <th>Nights</th>
                <th>Pax</th>
                <th>Payment</th>
                <th>Balance</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="checkinTableBody">
              @forelse($reservations as $res)
                @php
                  $checkInDate = \Carbon\Carbon::parse($res->check_in_date);
                  $isOverdue   = $checkInDate->lt($today);
                  $isToday     = $checkInDate->isToday();
                  $guestName   = trim($res->guest_first_name . ' ' . $res->guest_last_name) ?: '—';
                  $initials    = strtoupper(substr($res->guest_first_name, 0, 1) . substr($res->guest_last_name, 0, 1));
                @endphp
                <tr id="row-res-{{ $res->reservation_id }}"
                    data-name="{{ strtolower($guestName) }}"
                    data-email="{{ strtolower($res->email) }}"
                    data-room="{{ strtolower($res->room_number) }}"
                    data-payment="{{ $res->payment_method }}"
                    data-date="{{ $isOverdue ? 'overdue' : ($isToday ? 'today' : 'upcoming') }}">

                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial bg-label-primary rounded-circle fw-bold">{{ $initials ?: '?' }}</div>
                      </div>
                      <div>
                        <div class="fw-semibold">{{ $guestName }}</div>
                        <small class="text-muted">{{ $res->email }}</small>
                      </div>
                    </div>
                  </td>

                  <td>
                    <span class="badge bg-label-info">Rm {{ $res->room_number }}</span>
                    <div><small class="text-muted">{{ $res->room_type_name }}</small></div>
                  </td>

                  <td>
                    <div class="fw-semibold">{{ $checkInDate->format('M d, Y') }}</div>
                    @if($isOverdue)
                      <span class="badge bg-danger">Overdue</span>
                    @elseif($isToday)
                      <span class="badge bg-warning text-dark">Today</span>
                    @else
                      <small class="text-muted">{{ $checkInDate->diffForHumans() }}</small>
                    @endif
                  </td>

                  <td>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($res->check_out_date)->format('M d, Y') }}</div>
                  </td>

                  <td><span class="badge bg-label-secondary">{{ $res->no_nights }}n</span></td>

                  <td>{{ $res->adults }}</td>

                  <td>
                    @if($res->payment_method === 'online')
                      <span class="badge bg-label-success"><i class="ri-bank-card-line me-1"></i>Online</span>
                    @else
                      <span class="badge bg-label-warning"><i class="ri-money-dollar-circle-line me-1"></i>Cash</span>
                    @endif
                    <div class="mt-1">
                      @if($res->payment_status === 'completed')
                        <span class="badge bg-label-success">Paid</span>
                      @else
                        <span class="badge bg-label-danger">Unpaid</span>
                      @endif
                    </div>
                  </td>

                  <td>
                    <div class="fw-semibold text-warning">₱{{ number_format($res->balance, 2) }}</div>
                    <small class="text-muted">of ₱{{ number_format($res->total_amount, 2) }}</small>
                  </td>

                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);"
                           data-bs-toggle="modal"
                           data-bs-target="#viewDetailsModal{{ $res->reservation_id }}">
                          <i class="icon-base ri ri-eye-line me-2"></i>View Details
                        </a>
                        <a class="dropdown-item text-primary" href="javascript:void(0);"
                           data-bs-toggle="modal"
                           data-bs-target="#checkinModal{{ $res->reservation_id }}">
                          <i class="icon-base ri ri-login-box-line me-2"></i>Check-in
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr id="emptyRow">
                  <td colspan="9">
                    <div class="text-center py-5">
                      <i class="icon-base ri ri-checkbox-circle-line icon-48px text-success mb-3 d-block"></i>
                      <h5>All caught up!</h5>
                      <p class="text-muted mb-0">No approved reservations awaiting check-in.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>{{-- end row --}}

{{-- ─── ALL MODALS ─── --}}
@foreach($reservations as $res)
  @php
    $checkInDate = \Carbon\Carbon::parse($res->check_in_date);
    $guestName   = trim($res->guest_first_name . ' ' . $res->guest_last_name) ?: '—';
    $allGuests   = \Illuminate\Support\Facades\DB::table('guest_details')
                      ->where('reservation_id', $res->reservation_id)
                      ->orderBy('is_primary', 'desc')
                      ->get();
  @endphp

  {{-- VIEW DETAILS MODAL --}}
  <div class="modal fade" id="viewDetailsModal{{ $res->reservation_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="icon-base ri ri-eye-line me-2"></i>Reservation #{{ $res->reservation_id }} — Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <div class="bg-label-info rounded p-3">
                <small class="text-muted d-block fw-semibold text-uppercase mb-1">Room</small>
                <span class="fw-bold">Rm {{ $res->room_number }} — {{ $res->room_type_name }}</span>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="bg-label-success rounded p-3">
                <small class="text-muted d-block fw-semibold text-uppercase mb-1">Check-in</small>
                <span class="fw-bold">{{ $checkInDate->format('M d, Y') }}</span>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="bg-label-warning rounded p-3">
                <small class="text-muted d-block fw-semibold text-uppercase mb-1">Check-out</small>
                <span class="fw-bold">{{ \Carbon\Carbon::parse($res->check_out_date)->format('M d, Y') }}</span>
              </div>
            </div>
          </div>

          <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="icon-base ri ri-user-settings-line me-2 text-primary"></i>Account Holder
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <small class="text-muted fw-semibold d-block">Email</small>
              <span>{{ $res->email }}</span>
            </div>
            <div class="col-sm-6">
              <small class="text-muted fw-semibold d-block">Payment</small>
              <span>{{ ucfirst($res->payment_method) }} —
                @if($res->payment_status === 'completed')
                  <span class="badge bg-label-success">Paid</span>
                @else
                  <span class="badge bg-label-danger">Unpaid</span>
                @endif
              </span>
            </div>
            <div class="col-sm-4">
              <small class="text-muted fw-semibold d-block">Nights</small>
              <span class="fw-semibold">{{ $res->no_nights }}</span>
            </div>
            <div class="col-sm-4">
              <small class="text-muted fw-semibold d-block">Total Amount</small>
              <span class="fw-semibold">₱{{ number_format($res->total_amount, 2) }}</span>
            </div>
            <div class="col-sm-4">
              <small class="text-muted fw-semibold d-block">Balance Due</small>
              <span class="fw-bold text-warning">₱{{ number_format($res->balance, 2) }}</span>
            </div>
          </div>

          <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="icon-base ri ri-group-line me-2 text-primary"></i>Room Guests
            <span class="badge bg-label-primary ms-1">{{ $allGuests->count() }}</span>
          </h6>

          @if($allGuests->isEmpty())
            <div class="alert alert-secondary mb-0">
              <i class="icon-base ri ri-information-line me-2"></i>No guest details recorded for this reservation.
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                  <tr><th>#</th><th>Name</th><th>Contact</th><th>Type</th></tr>
                </thead>
                <tbody>
                  @foreach($allGuests as $i => $guest)
                    <tr>
                      <td>{{ $i + 1 }}</td>
                      <td class="fw-semibold">
                        {{ $guest->first_name }}
                        {{ $guest->middle_name ? $guest->middle_name . ' ' : '' }}
                        {{ $guest->last_name }}
                      </td>
                      <td>{{ $guest->contact_number ?: '—' }}</td>
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
          @endif

          @if($res->purpose ?? null)
            <div class="mt-3">
              <small class="text-muted fw-semibold d-block">Special Requests</small>
              <p class="mb-0">{{ $res->purpose }}</p>
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                  onclick="setTimeout(() => bootstrap.Modal.getOrCreateInstance(document.getElementById('checkinModal{{ $res->reservation_id }}')).show(), 250)">
            <i class="icon-base ri ri-login-box-line me-1"></i> Proceed to Check-in
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- CHECK-IN CONFIRM MODAL --}}
  <div class="modal fade" id="checkinModal{{ $res->reservation_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base ri ri-login-box-line me-2"></i>Confirm Check-in</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="icon-base ri ri-information-line me-2"></i>
            Please verify the guest's valid ID before proceeding.
          </div>
          <table class="table table-sm table-borderless">
            <tr><td class="text-muted fw-semibold" width="40%">Guest</td><td class="fw-bold">{{ $guestName }}</td></tr>
            <tr><td class="text-muted fw-semibold">Room</td><td>Rm {{ $res->room_number }} — {{ $res->room_type_name }}</td></tr>
            <tr><td class="text-muted fw-semibold">Check-in</td><td>{{ $checkInDate->format('F d, Y') }}</td></tr>
            <tr><td class="text-muted fw-semibold">Check-out</td><td>{{ \Carbon\Carbon::parse($res->check_out_date)->format('F d, Y') }}</td></tr>
            <tr><td class="text-muted fw-semibold">Nights</td><td>{{ $res->no_nights }}</td></tr>
            <tr><td class="text-muted fw-semibold">Guests</td><td>{{ $allGuests->count() }} person(s)</td></tr>
            <tr>
              <td class="text-muted fw-semibold">Payment</td>
              <td>
                {{ ucfirst($res->payment_method) }}
                @if($res->payment_method === 'cash')
                  <span class="badge bg-label-warning ms-1">Collect ₱{{ number_format($res->reservation_fee, 2) }} fee</span>
                @else
                  <span class="badge bg-label-success ms-1">Paid online</span>
                @endif
              </td>
            </tr>
            <tr><td class="text-muted fw-semibold">Balance Due</td><td class="fw-bold text-warning">₱{{ number_format($res->balance, 2) }}</td></tr>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary btn-checkin-confirm"
                  data-reservation-id="{{ $res->reservation_id }}"
                  data-url="{{ route('staff.registration.process-check-in', $res->reservation_id) }}"
                  data-modal="checkinModal{{ $res->reservation_id }}">
            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
            <i class="icon-base ri ri-login-box-line me-1"></i> Confirm Check-in
          </button>
        </div>
      </div>
    </div>
  </div>

@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Filters ──────────────────────────────────────────────────
  const searchInput   = document.getElementById('searchInput');
  const filterPayment = document.getElementById('filterPayment');
  const filterDate    = document.getElementById('filterDate');

  function applyFilters() {
    const search  = searchInput.value.toLowerCase();
    const payment = filterPayment.value;
    const date    = filterDate.value;
    document.querySelectorAll('#checkinTableBody tr[data-name]').forEach(row => {
      const matchSearch  = !search  || row.dataset.name.includes(search) || row.dataset.email.includes(search) || row.dataset.room.includes(search);
      const matchPayment = !payment || row.dataset.payment === payment;
      const matchDate    = !date    || row.dataset.date === date;
      row.style.display  = (matchSearch && matchPayment && matchDate) ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', applyFilters);
  filterPayment.addEventListener('change', applyFilters);
  filterDate.addEventListener('change', applyFilters);

  // ── AJAX Check-in ─────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-checkin-confirm');
    if (!btn) return;

    const reservationId = btn.dataset.reservationId;
    const url           = btn.dataset.url;
    const modalId       = btn.dataset.modal;
    const spinner       = btn.querySelector('.spinner-border');
    const icon          = btn.querySelector('.ri-login-box-line');

    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');

    fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept':       'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({}),
    })
    .then(res => res.json())
    .then(data => {
      bootstrap.Modal.getInstance(document.getElementById(modalId))?.hide();

      if (data.success) {
        const row = document.getElementById('row-res-' + reservationId);
        if (row) row.remove();

        updateStats(data.stats);
        checkEmptyState();
        showToast(data.message, 'success');
      } else {
        showToast(data.message, 'danger');
        btn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
      }
    })
    .catch(() => {
      bootstrap.Modal.getInstance(document.getElementById(modalId))?.hide();
      showToast('Something went wrong. Please try again.', 'danger');
      btn.disabled = false;
      spinner.classList.add('d-none');
      icon.classList.remove('d-none');
    });
  });

  // ── Helpers ───────────────────────────────────────────────────
  function updateStats(stats) {
    document.getElementById('statTotal').textContent    = stats.total;
    document.getElementById('statToday').textContent    = stats.today;
    document.getElementById('statOverdue').textContent  = stats.overdue;
    document.getElementById('statUpcoming').textContent = stats.upcoming;
    document.getElementById('statBadgeTotal').textContent = stats.total + ' Pending';
  }

  function checkEmptyState() {
    const rows = document.querySelectorAll('#checkinTableBody tr[data-name]');
    if (rows.length === 0) {
      const tbody = document.getElementById('checkinTableBody');
      tbody.innerHTML = `
        <tr id="emptyRow">
          <td colspan="9">
            <div class="text-center py-5">
              <i class="icon-base ri ri-checkbox-circle-line icon-48px text-success mb-3 d-block"></i>
              <h5>All caught up!</h5>
              <p class="text-muted mb-0">No approved reservations awaiting check-in.</p>
            </div>
          </td>
        </tr>`;
    }
  }

  function showToast(message, type) {
    const toast    = document.getElementById('toastMsg');
    const toastText = document.getElementById('toastText');
    toast.className = `toast align-items-center text-white border-0 bg-${type}`;
    toastText.textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 4000 }).show();
  }

});

function resetFilters() {
  document.getElementById('searchInput').value   = '';
  document.getElementById('filterPayment').value = '';
  document.getElementById('filterDate').value    = '';
  document.querySelectorAll('#checkinTableBody tr[data-name]').forEach(r => r.style.display = '');
}
</script>

@endsection