@extends('layouts/contentNavbarLayout')
@section('title', 'Check-out Guest')

@section('content')
<div class="row gy-6">

  {{-- Page Header --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1"><i class="icon-base ri ri-logout-box-line me-2 text-warning"></i>Check-out Guest</h4>
            <p class="mb-0 text-muted">Guests currently checked in. Process check-out and free up the room.</p>
          </div>
          <span class="badge bg-label-warning fs-6 px-3 py-2" id="statBadgeTotal">{{ $registrations->count() }} Active</span>
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
    $today        = \Carbon\Carbon::today();
    $dueToday     = $registrations->filter(fn($r) => \Carbon\Carbon::parse($r->expected_checkout)->isToday());
    $overdue      = $registrations->filter(fn($r) => \Carbon\Carbon::parse($r->expected_checkout)->lt($today));
    $stillStaying = $registrations->filter(fn($r) => \Carbon\Carbon::parse($r->expected_checkout)->gt($today));
  @endphp

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-warning rounded shadow-xs"><i class="icon-base ri ri-user-location-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Currently In</p><h5 class="mb-0" id="statTotal">{{ $registrations->count() }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-info rounded shadow-xs"><i class="icon-base ri ri-time-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Checkout Today</p><h5 class="mb-0" id="statToday">{{ $dueToday->count() }}</h5></div>
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
          <div class="avatar"><div class="avatar-initial bg-success rounded shadow-xs"><i class="icon-base ri ri-calendar-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Still Staying</p><h5 class="mb-0" id="statStaying">{{ $stillStaying->count() }}</h5></div>
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
            <label class="form-label">Checkout Status</label>
            <select class="form-select" id="filterDate">
              <option value="">All</option>
              <option value="overdue">Overdue</option>
              <option value="today">Today</option>
              <option value="staying">Still Staying</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Payment Method</label>
            <select class="form-select" id="filterPayment">
              <option value="">All</option>
              <option value="cash">Cash</option>
              <option value="online">Online</option>
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
        <h5 class="card-title m-0">Active Registrations — Currently Checked In</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Guest</th>
                <th>Room</th>
                <th>Checked In</th>
                <th>Expected Checkout</th>
                <th>Nights</th>
                <th>Payment</th>
                <th>Balance</th>
                <th>Inspection</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="checkoutTableBody">
              @forelse($registrations as $reg)
                @php
                  $checkout        = \Carbon\Carbon::parse($reg->expected_checkout);
                  $checkinAt       = \Carbon\Carbon::parse($reg->check_in_at);
                  $isOverdue       = $checkout->lt($today);
                  $isToday         = $checkout->isToday();
                  $guestName       = trim($reg->guest_first_name . ' ' . $reg->guest_last_name) ?: '—';
                  $initials        = strtoupper(substr($reg->guest_first_name, 0, 1) . substr($reg->guest_last_name, 0, 1));
                  $dateGroup       = $isOverdue ? 'overdue' : ($isToday ? 'today' : 'staying');

                  // Gate checks for this row
                  $inspectionOk    = isset($reg->inspection_status) && $reg->inspection_status === 'cleared';
                  $balanceOk       = $reg->balance <= 0;
                  $canCheckout     = $inspectionOk && $balanceOk;

                  $inspectionLabel = match($reg->inspection_status ?? 'none') {
                    'cleared'    => ['label' => 'Cleared',    'cls' => 'bg-label-success'],
                    'inspecting' => ['label' => 'Inspecting', 'cls' => 'bg-label-primary'],
                    'has_issues' => ['label' => 'Has Issues', 'cls' => 'bg-label-danger'],
                    'pending'    => ['label' => 'Pending',    'cls' => 'bg-label-warning'],
                    default      => ['label' => 'No Request', 'cls' => 'bg-label-secondary'],
                  };
                @endphp
                <tr id="row-reg-{{ $reg->registration_id }}"
                    data-name="{{ strtolower($guestName) }}"
                    data-email="{{ strtolower($reg->email) }}"
                    data-room="{{ strtolower($reg->room_number) }}"
                    data-payment="{{ $reg->payment_method }}"
                    data-date="{{ $dateGroup }}">

                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial bg-label-warning rounded-circle fw-bold">{{ $initials ?: '?' }}</div>
                      </div>
                      <div>
                        <div class="fw-semibold">{{ $guestName }}</div>
                        <small class="text-muted">{{ $reg->email }}</small>
                      </div>
                    </div>
                  </td>

                  <td>
                    <span class="badge bg-label-info">Rm {{ $reg->room_number }}</span>
                    <div><small class="text-muted">{{ $reg->room_type_name }}</small></div>
                  </td>

                  <td>
                    <div class="fw-semibold">{{ $checkinAt->format('M d, Y') }}</div>
                    <small class="text-muted">{{ $checkinAt->format('h:i A') }}</small>
                  </td>

                  <td>
                    <div class="fw-semibold">{{ $checkout->format('M d, Y') }}</div>
                    @if($isOverdue)
                      <span class="badge bg-danger">Overdue</span>
                    @elseif($isToday)
                      <span class="badge bg-warning text-dark">Today</span>
                    @else
                      <small class="text-muted">{{ $checkout->diffForHumans() }}</small>
                    @endif
                  </td>

                  <td><span class="badge bg-label-secondary">{{ $reg->no_nights }}n</span></td>

                  <td>
                    @if($reg->payment_method === 'online')
                      <span class="badge bg-label-success"><i class="ri-bank-card-line me-1"></i>Online</span>
                    @else
                      <span class="badge bg-label-warning"><i class="ri-money-dollar-circle-line me-1"></i>Cash</span>
                    @endif
                    <div class="mt-1">
                      @if($balanceOk)
                        <span class="badge bg-label-success">Paid</span>
                      @else
                        <span class="badge bg-label-danger">Unpaid</span>
                      @endif
                    </div>
                  </td>

                  <td>
                    @if($reg->balance > 0)
                      <div class="fw-semibold text-danger">₱{{ number_format($reg->balance, 2) }}</div>
                      <small class="text-muted">of ₱{{ number_format($reg->total_amount, 2) }}</small>
                    @else
                      <span class="badge bg-label-success">Fully Paid</span>
                    @endif
                  </td>

                  {{-- NEW: Inspection status column --}}
                  <td>
                    <span class="badge {{ $inspectionLabel['cls'] }}">{{ $inspectionLabel['label'] }}</span>
                  </td>

                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item"
                           href="{{ route('super_admin.registration.show', $reg->registration_id) }}">
                          <i class="icon-base ri ri-eye-line me-2"></i>View Details
                        </a>
                        {{-- Only show checkout option; the modal itself enforces the gates --}}
                        <a class="dropdown-item text-warning" href="javascript:void(0);"
                           data-bs-toggle="modal"
                           data-bs-target="#checkoutModal{{ $reg->registration_id }}">
                          <i class="icon-base ri ri-logout-box-line me-2"></i>Check-out
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr id="emptyRow">
                  <td colspan="9">
                    <div class="text-center py-5">
                      <i class="icon-base ri ri-hotel-bed-line icon-48px text-muted mb-3 d-block"></i>
                      <h5>No Active Guests</h5>
                      <p class="text-muted mb-0">No guests are currently checked in.</p>
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
@foreach($registrations as $reg)
  @php
    $checkout        = \Carbon\Carbon::parse($reg->expected_checkout);
    $checkinAt       = \Carbon\Carbon::parse($reg->check_in_at);
    $guestName       = trim($reg->guest_first_name . ' ' . $reg->guest_last_name) ?: '—';
    $allGuests       = \Illuminate\Support\Facades\DB::table('guest_details')
                          ->where('reservation_id', $reg->reservation_id)
                          ->orderBy('is_primary', 'desc')
                          ->get();

    $inspectionOk    = isset($reg->inspection_status) && $reg->inspection_status === 'cleared';
    $balanceOk       = $reg->balance <= 0;
    $canCheckout     = $inspectionOk && $balanceOk;
  @endphp

  {{-- CHECK-OUT CONFIRM MODAL --}}
  <div class="modal fade" id="checkoutModal{{ $reg->registration_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base ri ri-logout-box-line me-2"></i>Confirm Check-out</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          {{-- ── Gate blockers ── --}}
          @if(! $inspectionOk)
            <div class="alert alert-danger">
              <i class="icon-base ri ri-search-eye-line me-2"></i>
              <strong>Room inspection not cleared.</strong>
              The room must be inspected and marked as <em>Cleared</em> before checkout.
              Current status:
              <span class="badge bg-label-{{ match($reg->inspection_status ?? 'none') {
                'inspecting' => 'primary',
                'has_issues' => 'danger',
                'pending'    => 'warning',
                default      => 'secondary',
              } }} ms-1">{{ ucfirst(str_replace('_', ' ', $reg->inspection_status ?? 'No Request')) }}</span>
            </div>
          @endif

          @if(! $balanceOk)
            <div class="alert alert-danger">
              <i class="icon-base ri ri-money-dollar-circle-line me-2"></i>
              <strong>Outstanding balance of ₱{{ number_format($reg->balance, 2) }}</strong> must be collected before checking out.
            </div>
          @endif

          @if($canCheckout)
            <div class="alert alert-success">
              <i class="icon-base ri ri-checkbox-circle-line me-2"></i>
              All requirements met. Ready to check out.
            </div>
          @endif

          <table class="table table-sm table-borderless">
            <tr><td class="text-muted fw-semibold" width="40%">Guest</td><td class="fw-bold">{{ $guestName }}</td></tr>
            <tr><td class="text-muted fw-semibold">Room</td><td>Rm {{ $reg->room_number }} — {{ $reg->room_type_name }}</td></tr>
            <tr><td class="text-muted fw-semibold">Checked In</td><td>{{ $checkinAt->format('F d, Y h:i A') }}</td></tr>
            <tr><td class="text-muted fw-semibold">Checkout Date</td><td>{{ $checkout->format('F d, Y') }}</td></tr>
            <tr><td class="text-muted fw-semibold">Nights</td><td>{{ $reg->no_nights }}</td></tr>
            <tr><td class="text-muted fw-semibold">Guests</td><td>{{ $allGuests->count() }} person(s)</td></tr>
            <tr><td class="text-muted fw-semibold">Total Amount</td><td>₱{{ number_format($reg->total_amount, 2) }}</td></tr>
            <tr>
              <td class="text-muted fw-semibold">Balance</td>
              <td class="fw-bold {{ $reg->balance > 0 ? 'text-danger' : 'text-success' }}">
                ₱{{ number_format($reg->balance, 2) }}
              </td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Inspection</td>
              <td>
                <span class="badge bg-label-{{ $inspectionOk ? 'success' : 'danger' }}">
                  {{ $inspectionOk ? 'Cleared' : ucfirst(str_replace('_', ' ', $reg->inspection_status ?? 'No Request')) }}
                </span>
              </td>
            </tr>
          </table>

          @if($canCheckout)
            <p class="text-muted small mb-0">
              <i class="ri-information-line me-1"></i>
              The room will be marked as <strong>Available</strong> after checkout.
            </p>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button"
                  class="btn btn-warning btn-checkout-confirm {{ $canCheckout ? '' : 'disabled' }}"
                  {{ $canCheckout ? '' : 'disabled title="Complete inspection and settle balance first"' }}
                  data-registration-id="{{ $reg->registration_id }}"
                  data-url="{{ route('super_admin.registration.process-check-out', $reg->registration_id) }}"
                  data-modal="checkoutModal{{ $reg->registration_id }}">
            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
            <i class="icon-base ri ri-logout-box-line me-1"></i> Confirm Check-out
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
    document.querySelectorAll('#checkoutTableBody tr[data-name]').forEach(row => {
      const matchSearch  = !search  || row.dataset.name.includes(search) || row.dataset.email.includes(search) || row.dataset.room.includes(search);
      const matchPayment = !payment || row.dataset.payment === payment;
      const matchDate    = !date    || row.dataset.date === date;
      row.style.display  = (matchSearch && matchPayment && matchDate) ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', applyFilters);
  filterPayment.addEventListener('change', applyFilters);
  filterDate.addEventListener('change', applyFilters);

  // ── AJAX Check-out ────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-checkout-confirm');
    if (!btn || btn.disabled) return;

    const registrationId = btn.dataset.registrationId;
    const url            = btn.dataset.url;
    const modalId        = btn.dataset.modal;
    const spinner        = btn.querySelector('.spinner-border');
    const icon           = btn.querySelector('.ri-logout-box-line');

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
        const row = document.getElementById('row-reg-' + registrationId);
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
    document.getElementById('statTotal').textContent   = stats.total;
    document.getElementById('statToday').textContent   = stats.today;
    document.getElementById('statOverdue').textContent = stats.overdue;
    document.getElementById('statStaying').textContent = stats.staying;
    document.getElementById('statBadgeTotal').textContent = stats.total + ' Active';
  }

  function checkEmptyState() {
    const rows = document.querySelectorAll('#checkoutTableBody tr[data-name]');
    if (rows.length === 0) {
      document.getElementById('checkoutTableBody').innerHTML = `
        <tr id="emptyRow">
          <td colspan="9">
            <div class="text-center py-5">
              <i class="icon-base ri ri-hotel-bed-line icon-48px text-muted mb-3 d-block"></i>
              <h5>No Active Guests</h5>
              <p class="text-muted mb-0">No guests are currently checked in.</p>
            </div>
          </td>
        </tr>`;
    }
  }

  function showToast(message, type) {
    const toast     = document.getElementById('toastMsg');
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
  document.querySelectorAll('#checkoutTableBody tr[data-name]').forEach(r => r.style.display = '');
}
</script>

@endsection