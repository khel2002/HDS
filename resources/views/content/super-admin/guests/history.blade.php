@extends('layouts/contentNavbarLayout')
@section('title', 'Guests - History')

@section('content')
<div class="row gy-6">

  {{-- ── PAGE HEADER ──────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">Guest History</h4>
            <p class="mb-0 text-body-secondary">Completed stays and past check-outs</p>
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
              <i class="icon-base ri ri-history-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Total Stays</p>
            <h5 class="mb-0">{{ $stats['total_stays'] }}</h5>
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
              <i class="icon-base ri ri-money-dollar-circle-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Total Revenue</p>
            <h5 class="mb-0">₱{{ number_format($stats['total_revenue'], 2) }}</h5>
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
            <div class="avatar-initial bg-info rounded shadow-xs">
              <i class="icon-base ri ri-moon-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Avg. Nights/Stay</p>
            <h5 class="mb-0">{{ $stats['avg_nights'] }}</h5>
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
              <i class="icon-base ri ri-user-heart-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Unique Guests</p>
            <h5 class="mb-0">{{ $stats['unique_guests'] }}</h5>
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
          <div class="col-md-3">
            <label class="form-label">Search Guest / Room</label>
            <input type="text" class="form-control" id="searchInput"
                   value="{{ $search }}"
                   placeholder="Name, email, or room…">
          </div>
          <div class="col-md-2">
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
            <label class="form-label">Date From</label>
            <input type="date" class="form-control" id="dateFrom" value="{{ $dateFrom }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Date To</label>
            <input type="date" class="form-control" id="dateTo" value="{{ $dateTo }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button id="applyFilters" class="btn btn-primary d-block w-100">
              <i class="icon-base ri ri-search-line me-1"></i>Go
            </button>
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <a href="{{ route('super_admin.guests.history') }}"
               class="btn btn-outline-secondary d-block w-100">
              <i class="icon-base ri ri-refresh-line me-1"></i>Reset
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
        <h5 class="card-title m-0">Completed Stays</h5>
        <small class="text-body-secondary">{{ count($history) }} record(s)</small>
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
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Payment</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($history as $record)
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial bg-label-secondary rounded-circle">
                          {{ strtoupper(substr($record->first_name ?? '?', 0, 1)) }}
                        </div>
                      </div>
                      <div>
                        <span class="fw-medium d-block">
                          {{ trim($record->first_name . ' ' . $record->last_name) }}
                        </span>
                        <small class="text-body-secondary">{{ $record->email }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-secondary">{{ $record->room_number }}</span>
                  </td>
                  <td>{{ $record->room_type_name }}</td>
                  <td class="text-body-secondary">
                    {{ $record->check_in_at ? \Carbon\Carbon::parse($record->check_in_at)->format('M d, Y') : '—' }}
                  </td>
                  <td class="text-body-secondary">
                    {{ \Carbon\Carbon::parse($record->check_out_date)->format('M d, Y') }}
                  </td>
                  <td>
                    <span class="badge bg-label-info rounded-pill">{{ $record->no_nights }}N</span>
                  </td>
                  <td>
                    <small>
                      <i class="icon-base ri ri-user-line me-1"></i>{{ $record->adults }}
                      @if($record->children)
                        <i class="icon-base ri ri-parent-line me-1 ms-1"></i>{{ $record->children }}
                      @endif
                    </small>
                  </td>
                  <td class="fw-medium">₱{{ number_format($record->total_amount, 2) }}</td>
                  <td class="text-success fw-medium">₱{{ number_format($record->amount_paid, 2) }}</td>
                  <td>
                    @if($record->balance > 0)
                      <span class="text-danger fw-medium">₱{{ number_format($record->balance, 2) }}</span>
                    @else
                      <span class="badge bg-label-success">Settled</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $pmColor = match($record->payment_status) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'refunded'  => 'info',
                        default     => 'secondary',
                      };
                    @endphp
                    <span class="badge bg-label-{{ $pmColor }}">{{ ucfirst($record->payment_status) }}</span>
                    <br>
                    <small class="text-body-secondary">{{ ucfirst($record->payment_method ?? '—') }}</small>
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary"
                            data-bs-toggle="tooltip" title="View Stay Details"
                            onclick="viewHistoryDetails({{ json_encode($record) }})">
                      <i class="icon-base ri ri-eye-line"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="12" class="text-center py-5">
                    <i class="icon-base ri ri-history-line icon-48px text-body-secondary mb-3 d-block"></i>
                    <p class="mb-0 text-body-secondary">No completed stays found.</p>
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

{{-- ── HISTORY DETAILS MODAL ────────────────────────────────── --}}
<div class="modal fade" id="historyDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-history-line me-2"></i>
          Stay Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="historyDetailsContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const ROUTES = {
    history:   '{{ route('super_admin.guests.history') }}',
    csrfToken: '{{ csrf_token() }}',
  };
</script>
<script src="{{ asset('js/guestjs/history_script.js') }}"></script>
@endsection