@extends('layouts/contentNavbarLayout')
@section('title', 'Reservations - Management')

@section('content')
  @if(session('success'))
    <span id="__flashSuccess" data-msg="{{ session('success') }}" style="display:none;"></span>
  @endif
  @if(session('error'))
    <span id="__flashError" data-msg="{{ session('error') }}" style="display:none;"></span>
  @endif

  <div class="row gy-6">

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{--  PAGE HEADER                                                          --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">

            <div>
              <h4 class="mb-1">Reservation Management</h4>
              <p class="mb-0">View, review and manage all guest bookings across all rooms</p>
            </div>

            <a href="{{ route('super_admin.reservations.walkin') }}" class="btn btn-primary" type="button">
              <i class="ri ri-add-line me-1"></i>
              walk-in reservation
            </a>

          </div>
        </div>
      </div>
    </div>



    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{--  STAT CARDS                                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-primary rounded shadow-xs">
                <i class="icon-base ri ri-calendar-check-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Total Reservations</p>
              <h5 class="mb-0" data-stat="total">{{ $stats['total'] }}</h5>
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
                <i class="icon-base ri ri-time-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Pending</p>
              <h5 class="mb-0" data-stat="pending">{{ $stats['pending'] }}</h5>
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
                <i class="icon-base ri ri-checkbox-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Approved</p>
              <h5 class="mb-0" data-stat="approved">{{ $stats['approved'] }}</h5>
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
                <i class="icon-base ri ri-close-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Rejected / Cancelled</p>
              <h5 class="mb-0" data-stat="rejected_cancelled">{{ $stats['rejected'] + $stats['cancelled'] }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{--  FILTER BAR                                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label">Search Guest / Room</label>
              <input type="text" class="form-control" id="searchInput" placeholder="Name, email, room...">
            </div>
            <div class="col-md-2">
              <label class="form-label">Status</label>
              <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Payment</label>
              <select id="paymentFilter" class="form-select">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="online">Online</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Check-in Date</label>
              <input type="date" id="dateFilter" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" id="resetBtn">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{--  TABLE                                                                --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0 me-2">All Bookings</h5>
            <span class="badge bg-label-primary" id="visibleCount">{{ count($bookings) }} bookings</span>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th class="text-truncate">#</th>
                  <th class="text-truncate">Guest</th>
                  <th class="text-truncate">Rooms</th>
                  <th class="text-truncate">Check-in</th>
                  <th class="text-truncate">Check-out</th>
                  <th class="text-truncate">Total</th>
                  <th class="text-truncate">Payment</th>
                  <th class="text-truncate">Status</th>
                  <th class="text-truncate text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="bookingsTableBody">
                @forelse($bookings as $i => $bk)
                  @php
                    $statuses    = $bk->rooms->pluck('reservation_status')->unique()->values();
                    $overallSt   = $bk->overall_status;
                    $isMixed     = $statuses->count() > 1;
                    $allPending  = $bk->rooms->every(fn($r) => $r->reservation_status === 'pending');
                    $anyActive   = $bk->rooms->some(fn($r) => in_array($r->reservation_status, ['pending', 'approved']));
                    $allApproved = $bk->rooms->every(fn($r) => $r->reservation_status === 'approved');

                    $searchStr = strtolower(
                      $bk->first_name . ' ' . $bk->last_name . ' ' . $bk->email . ' ' .
                      $bk->rooms->pluck('room_number')->implode(' ') . ' ' .
                      $bk->rooms->pluck('room_type_name')->implode(' ')
                    );
                    $initials = strtoupper(substr($bk->first_name, 0, 1) . substr($bk->last_name, 0, 1));
                  @endphp

                  <tr class="booking-row"
                      data-primary="{{ $bk->primary_reservation_id }}"
                      data-payment-id="{{ $bk->payment_id }}"
                      data-ids="{{ json_encode($bk->reservation_ids) }}"
                      data-search="{{ $searchStr }}"
                      data-status="{{ $overallSt }}"
                      data-payment="{{ $bk->payment_method }}"
                      data-checkin="{{ $bk->check_in_date }}">

                    {{-- # --}}
                    <td>
                      <span class="row-num text-body-secondary">{{ $i + 1 }}</span>
                    </td>

                    {{-- Guest --}}
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm">
                          <div class="avatar-initial bg-label-primary rounded-circle">
                            {{ $initials }}
                          </div>
                        </div>
                        <div style="min-width:0;">
                          <div class="fw-medium text-truncate" style="max-width:160px;">
                            {{ $bk->first_name }} {{ $bk->last_name }}
                          </div>
                          <div class="text-truncate text-body-secondary" style="font-size:.775rem;max-width:160px;">
                            {{ $bk->email }}
                          </div>
                        </div>
                      </div>
                    </td>

                    {{-- Rooms --}}
                    <td>
                      @if($bk->room_count === 1)
                        @php $r0 = $bk->rooms->first(); @endphp
                        <div class="fw-medium">{{ $r0->room_type_name }}</div>
                        <div class="text-body-secondary" style="font-size:.775rem;">Room {{ $r0->room_number }}</div>
                      @else
                        <span class="badge bg-label-info mb-1">
                          <i class="icon-base ri ri-hotel-line me-1"></i>{{ $bk->room_count }} rooms
                        </span>
                        <div class="text-body-secondary" style="font-size:.775rem;">
                          {{ $bk->rooms->pluck('room_number')->map(fn($n) => '#' . $n)->implode(', ') }}
                        </div>
                      @endif
                    </td>

                    {{-- Check-in --}}
                    <td>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($bk->check_in_date)->format('M d, Y') }}</span>
                    </td>

                    {{-- Check-out --}}
                    <td>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($bk->check_out_date)->format('M d, Y') }}</span>
                    </td>

                    {{-- Total --}}
                    <td>
                      <span class="fw-medium text-primary">₱{{ number_format($bk->total_amount, 2) }}</span>
                      @if($bk->total_balance > 0)
                        <div class="text-danger" style="font-size:.75rem;">Bal: ₱{{ number_format($bk->total_balance, 2) }}</div>
                      @else
                        <div class="text-success" style="font-size:.75rem;"><i class="ri ri-check-line"></i> Settled</div>
                      @endif
                    </td>

                    {{-- Payment method --}}
                    <td>
                      @if($bk->payment_method === 'online')
                        <span class="badge bg-label-info">
                          <i class="ri ri-bank-card-line me-1"></i>Online
                        </span>
                      @elseif($bk->payment_method === 'cash')
                        <span class="badge bg-label-warning">
                          <i class="ri ri-cash-line me-1"></i>Cash
                        </span>
                      @else
                        <span class="badge bg-label-secondary">—</span>
                      @endif
                    </td>

                    {{-- Status --}}
                    <td>
                      @if($isMixed)
                        <span class="badge bg-label-primary mb-1">Mixed</span>
                        <div class="d-flex flex-column gap-1">
                          @foreach($bk->rooms as $r)
                            <span class="badge bg-label-{{ $r->reservation_status === 'approved' ? 'success' : ($r->reservation_status === 'pending' ? 'warning' : 'danger') }}" style="font-size:.7rem;">
                              Rm {{ $r->room_number }}: {{ ucfirst($r->reservation_status) }}
                            </span>
                          @endforeach
                        </div>
                      @elseif($overallSt === 'approved')
                        <span class="badge bg-label-success">
                          <i class="ri ri-checkbox-circle-line me-1"></i>Approved
                        </span>
                      @elseif($overallSt === 'pending')
                        <span class="badge bg-label-warning">
                          <i class="ri ri-time-line me-1"></i>Pending
                        </span>
                      @elseif($overallSt === 'rejected')
                        <span class="badge bg-label-danger">
                          <i class="ri ri-close-circle-line me-1"></i>Rejected
                        </span>
                      @else
                        <span class="badge bg-label-secondary">
                          <i class="ri ri-forbid-line me-1"></i>Cancelled
                        </span>
                      @endif
                    </td>

                    {{-- Actions --}}
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);" onclick="viewBooking({{ $bk->primary_reservation_id }})">
                            <i class="icon-base ri ri-eye-line me-2"></i>View Details
                          </a>

                          @if($allPending && $bk->payment_id)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-success" href="javascript:void(0);"
                               onclick="bulkAction({{ $bk->payment_id }}, {{ json_encode($bk->reservation_ids) }}, 'approve', '{{ addslashes($bk->first_name . ' ' . $bk->last_name) }}')">
                              <i class="icon-base ri ri-checkbox-circle-line me-2"></i>Approve All
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                               onclick="bulkAction({{ $bk->payment_id }}, {{ json_encode($bk->reservation_ids) }}, 'reject', '{{ addslashes($bk->first_name . ' ' . $bk->last_name) }}')">
                              <i class="icon-base ri ri-close-circle-line me-2"></i>Reject All
                            </a>
                          @endif

                          @if($anyActive && $bk->payment_id)
                            <a class="dropdown-item text-warning" href="javascript:void(0);"
                               onclick="bulkAction({{ $bk->payment_id }}, {{ json_encode($bk->reservation_ids) }}, 'cancel', '{{ addslashes($bk->first_name . ' ' . $bk->last_name) }}')">
                              <i class="icon-base ri ri-forbid-line me-2"></i>Cancel All
                            </a>
                          @endif

                          @if(!$allApproved)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                               onclick="confirmDeleteBooking({{ json_encode($bk->reservation_ids) }}, '{{ addslashes($bk->first_name . ' ' . $bk->last_name) }}')">
                              <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete Booking
                            </a>
                          @endif
                        </div>
                      </div>

                      {{-- Hidden URL holders --}}
                      <div style="display:none;">
                        <input id="showUrl__{{ $bk->primary_reservation_id }}"
                               value="{{ route('super_admin.reservations.show', $bk->primary_reservation_id) }}">
                        @if($bk->payment_id)
                          <input id="bulkApproveUrl__{{ $bk->payment_id }}"
                                 value="{{ route('super_admin.reservations.booking.approve', $bk->payment_id) }}">
                          <input id="bulkRejectUrl__{{ $bk->payment_id }}"
                                 value="{{ route('super_admin.reservations.booking.reject', $bk->payment_id) }}">
                          <input id="bulkCancelUrl__{{ $bk->payment_id }}"
                                 value="{{ route('super_admin.reservations.booking.cancel', $bk->payment_id) }}">
                        @endif
                        @foreach($bk->reservation_ids as $rid)
                          <input id="approveUrl__{{ $rid }}"
                                 value="{{ route('super_admin.reservations.approve', $rid) }}">
                          <input id="rejectUrl__{{ $rid }}"
                                 value="{{ route('super_admin.reservations.reject', $rid) }}">
                          <input id="cancelUrl__{{ $rid }}"
                                 value="{{ route('super_admin.reservations.cancel', $rid) }}">
                          <input id="deleteUrl__{{ $rid }}"
                                 value="{{ route('super_admin.reservations.destroy', $rid) }}">
                        @endforeach
                      </div>
                    </td>

                  </tr>
                @empty
                  <tr id="emptyRow">
                    <td colspan="9">
                      <div class="text-center py-5">
                        <i class="icon-base ri ri-calendar-check-line icon-64px text-body-secondary mb-4" style="font-size:3.5rem;display:block;"></i>
                        <h5 class="mb-2">No Reservations Yet</h5>
                        <p class="mb-0 text-body-secondary">Guest reservations will appear here once they book a room.</p>
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

  </div>{{-- end .row --}}


  {{-- ══════════════════════════════════════════════════════════════════════ --}}
  {{--  VIEW BOOKING DETAILS MODAL                                           --}}
  {{-- ══════════════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title d-flex align-items-center gap-2">
              <i class="icon-base ri ri-calendar-check-line"></i> Booking Details
            </h5>
            <p class="mb-0 text-body-secondary" style="font-size:.8125rem;" id="viewModalSubtitle">Loading…</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-0" id="viewModalBody">
          <div class="d-flex flex-column align-items-center justify-content-center" style="min-height:240px;gap:1rem;">
            <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;"></div>
            <p class="text-body-secondary mb-0">Loading booking information…</p>
          </div>
        </div>

        <div class="modal-footer" id="viewModalFooter">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="icon-base ri ri-close-line me-1"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <style>
    /* ── Room card inside modal ───────────────────────────────────────────── */
    .room-detail-card { border:1px solid #e9ecef; border-radius:.5rem; overflow:hidden; height:100%; display:flex; flex-direction:column; }
    .room-detail-img  { width:100%; height:140px; object-fit:cover; }
    .room-detail-img-placeholder { width:100%; height:100px; background:#f4f5fa; display:flex; align-items:center; justify-content:center; }
    .room-detail-img-placeholder i { font-size:2.5rem; color:#a8b1bb; }
    .room-detail-body { padding:.875rem 1rem; flex:1; display:flex; flex-direction:column; gap:.5rem; }
    .room-detail-name { font-weight:700; font-size:.9375rem; margin:0; }
    .room-detail-num  { font-size:.8rem; color:#6d6777; }
    .room-meta-row    { display:flex; gap:.5rem; flex-wrap:wrap; margin:.25rem 0; }
    .room-meta-chip   { background:#f4f5fa; color:#5a5a72; font-size:.75rem; font-weight:500; padding:.2rem .6rem; border-radius:.25rem; display:inline-flex; align-items:center; gap:.25rem; }
    .room-detail-divider { height:1px; background:#e9ecef; margin:.25rem 0; }
    .room-fin-row { display:flex; justify-content:space-between; align-items:center; font-size:.8125rem; }
    .room-fin-row .label { color:#6d6777; }
    .room-fin-row .value { font-weight:700; }
    .room-action-group { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.5rem; }
    .room-action-btn { flex:1; min-width:0; padding:.35rem .5rem; border-radius:.375rem; font-size:.75rem; font-weight:600; border:1.5px solid; cursor:pointer; transition:all .15s; display:flex; align-items:center; justify-content:center; gap:.3rem; background:transparent; }
    .room-action-btn.approve { border-color:#00b894; color:#00b894; }
    .room-action-btn.approve:hover { background:#00b894; color:#fff; }
    .room-action-btn.reject  { border-color:#e74c3c; color:#e74c3c; }
    .room-action-btn.reject:hover  { background:#e74c3c; color:#fff; }
    .room-action-btn.cancel  { border-color:#ffc107; color:#856404; }
    .room-action-btn.cancel:hover  { background:#ffc107; color:#fff; }
    /* ── Booking timeline ─────────────────────────────────────────────────── */
    .booking-timeline { display:flex; align-items:center; gap:0; margin:.5rem 0; }
    .tl-step { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; }
    .tl-step:not(:last-child)::after { content:''; position:absolute; top:14px; left:50%; width:100%; height:2px; background:#e9ecef; z-index:0; }
    .tl-step.done:not(:last-child)::after { background:#00b894; }
    .tl-dot { width:28px; height:28px; border-radius:50%; border:2px solid #e9ecef; background:#fff; display:flex; align-items:center; justify-content:center; font-size:.7rem; z-index:1; }
    .tl-step.done .tl-dot   { border-color:#00b894; background:#00b894; color:#fff; }
    .tl-step.active .tl-dot { border-color:#ffc107; background:#ffc107; color:#fff; }
    .tl-step.fail .tl-dot   { border-color:#e74c3c; background:#e74c3c; color:#fff; }
    .tl-label { font-size:.68rem; color:#6d6777; margin-top:.35rem; text-align:center; font-weight:500; }
    /* ── Info grid ─────────────────────────────────────────────────────────── */
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:.5rem 1.5rem; }
    .info-item label { font-size:.75rem; color:#6d6777; margin:0 0 .1rem; display:block; }
    .info-item span  { font-size:.9rem; font-weight:600; }
    /* ── Finance summary boxes ─────────────────────────────────────────────── */
    .fin-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.75rem; }
    .fin-box  { border-radius:.375rem; padding:.875rem 1rem; text-align:center; }
    .fin-box-label { font-size:.7rem; font-weight:600; letter-spacing:.04em; text-transform:uppercase; margin:0 0 .35rem; opacity:.75; }
    .fin-box-value { font-size:1.1rem; font-weight:800; margin:0; line-height:1; }
    /* ── Section label ─────────────────────────────────────────────────────── */
    .modal-section-label { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; display:flex; align-items:center; gap:.5rem; margin-bottom:1rem; color:#566a7f; }
    .modal-section-label::after { content:''; flex:1; height:1px; background:#e9ecef; }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/reservationjs/superadmin.js') }}"></script>
@endsection
