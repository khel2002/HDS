@extends('layouts/contentNavbarLayout')
@section('title', 'All Registrations')

@section('content')
<div class="row gy-6">

  {{-- Page Header --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h4 class="mb-1"><i class="icon-base ri ri-file-list-3-line me-2 text-primary"></i>All Registrations</h4>
            <p class="mb-0 text-muted">Complete audit of every guest registration — active and completed.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('super_admin.registration.check-in') }}" class="btn btn-primary btn-sm">
              <i class="icon-base ri ri-login-box-line me-1"></i> Check-in
            </a>
            <a href="{{ route('super_admin.registration.check-out') }}" class="btn btn-warning btn-sm">
              <i class="icon-base ri ri-logout-box-line me-1"></i> Check-out
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="col-12">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icon-base ri ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  @endif

  {{-- Filters --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form method="GET" action="{{ route('super_admin.registration.all') }}" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Guest, email, room..."
                   value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">All</option>
              <option value="active"     {{ request('status') === 'active'     ? 'selected' : '' }}>Active</option>
              <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
          </div>
          <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
              <i class="icon-base ri ri-search-line me-1"></i> Filter
            </button>
            <a href="{{ route('super_admin.registration.all') }}" class="btn btn-outline-secondary flex-fill">
              <i class="icon-base ri ri-refresh-line me-1"></i> Reset
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Registration Records</h5>
        <small class="text-muted">{{ $registrations->total() }} total records</small>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Guest</th>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Nights</th>
                <th>Payment</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($registrations as $reg)
                @php
                  $guestName = trim($reg->guest_first_name . ' ' . $reg->guest_last_name) ?: '—';
                  $initials  = strtoupper(substr($reg->guest_first_name,0,1) . substr($reg->guest_last_name,0,1));
                  $isActive  = $reg->reg_status === 'active';
                @endphp
                <tr>
                  {{-- Registration ID --}}
                  <td>
                    <span class="fw-semibold text-primary">#{{ $reg->registration_id }}</span>
                    <div><small class="text-muted">Res #{{ $reg->reservation_id }}</small></div>
                  </td>

                  {{-- Guest --}}
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial {{ $isActive ? 'bg-label-warning' : 'bg-label-secondary' }} rounded-circle fw-bold">
                          {{ $initials ?: '?' }}
                        </div>
                      </div>
                      <div>
                        <div class="fw-semibold">{{ $guestName }}</div>
                        <small class="text-muted">{{ $reg->email }}</small>
                      </div>
                    </div>
                  </td>

                  {{-- Room --}}
                  <td>
                    <span class="badge bg-label-info">Rm {{ $reg->room_number }}</span>
                    <div><small class="text-muted">{{ $reg->room_type_name }}</small></div>
                  </td>

                  {{-- Check-in --}}
                  <td>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($reg->check_in_at)->format('M d, Y') }}</div>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($reg->check_in_at)->format('h:i A') }}</small>
                  </td>

                  {{-- Check-out --}}
                  <td>
                    @if($reg->check_out_date)
                      <div class="fw-semibold">{{ \Carbon\Carbon::parse($reg->check_out_date)->format('M d, Y') }}</div>
                    @else
                      <small class="text-muted">Expected: {{ \Carbon\Carbon::parse($reg->expected_checkout)->format('M d, Y') }}</small>
                    @endif
                  </td>

                  {{-- Nights --}}
                  <td><span class="badge bg-label-secondary">{{ $reg->no_nights }}n</span></td>

                  {{-- Payment --}}
                  <td>
                    @if($reg->payment_method === 'online')
                      <span class="badge bg-label-success"><i class="ri-bank-card-line me-1"></i>Online</span>
                    @else
                      <span class="badge bg-label-warning"><i class="ri-money-dollar-circle-line me-1"></i>Cash</span>
                    @endif
                    <div>
                      @if($reg->payment_status === 'completed')
                        <span class="badge bg-label-success mt-1">Paid</span>
                      @else
                        <span class="badge bg-label-danger mt-1">Pending</span>
                      @endif
                    </div>
                  </td>

                  {{-- Total --}}
                  <td>
                    <div class="fw-semibold">₱{{ number_format($reg->total_amount, 2) }}</div>
                    @if($reg->balance > 0 && $isActive)
                      <small class="text-danger">₱{{ number_format($reg->balance, 2) }} due</small>
                    @endif
                  </td>

                  {{-- Status --}}
                  <td>
                    @if($isActive)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Completed</span>
                    @endif
                  </td>

                  {{-- Actions --}}
                  <td>
                    <div class="d-flex gap-1">
                      <a href="{{ route('super_admin.registration.show', $reg->registration_id) }}"
                         class="btn btn-sm btn-outline-primary">
                        <i class="icon-base ri ri-eye-line me-1"></i> View
                      </a>
                      @if($isActive)
                        <button type="button" class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#quickCheckoutModal{{ $reg->registration_id }}">
                          <i class="icon-base ri ri-logout-box-line"></i>
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>

                {{-- Quick Checkout Modal (for active only) --}}
                @if($isActive)
                <div class="modal fade" id="quickCheckoutModal{{ $reg->registration_id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="icon-base ri ri-logout-box-line me-2"></i>Quick Check-out</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        @if($reg->balance > 0)
                          <div class="alert alert-warning">
                            <strong>Collect ₱{{ number_format($reg->balance, 2) }}</strong> remaining balance before checking out.
                          </div>
                        @endif
                        <p>Check out <strong>{{ $guestName }}</strong> from Room <strong>{{ $reg->room_number }}</strong>?</p>
                        <p class="text-muted small mb-0">Room will be marked <strong>Available</strong> after checkout.</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('super_admin.registration.process-check-out', $reg->registration_id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-warning">
                            <i class="icon-base ri ri-logout-box-line me-1"></i> Confirm
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                @endif

              @empty
                <tr>
                  <td colspan="10">
                    <div class="text-center py-5">
                      <i class="icon-base ri ri-file-list-3-line icon-48px text-muted mb-3 d-block"></i>
                      <h5>No registrations found</h5>
                      <p class="text-muted mb-0">Try adjusting your filters.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Pagination --}}
      @if($registrations->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
          <small class="text-muted">
            Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} of {{ $registrations->total() }}
          </small>
          {{ $registrations->withQueryString()->links() }}
        </div>
      @endif

    </div>
  </div>

</div>
@endsection