@extends('layouts/contentNavbarLayout')
@section('title', 'Guests - All Guests')

@section('content')
<div class="row gy-6">

  {{-- ── PAGE HEADER ───────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">All Guests</h4>
            <p class="mb-0 text-body-secondary">Complete list of registered guest accounts</p>
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
            <p class="mb-0">Total Guests</p>
            <h5 class="mb-0">{{ $stats['total_guests'] }}</h5>
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
            <p class="mb-0">Active</p>
            <h5 class="mb-0">{{ $stats['active_guests'] }}</h5>
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
              <i class="icon-base ri ri-user-forbid-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">Inactive</p>
            <h5 class="mb-0">{{ $stats['inactive_guests'] }}</h5>
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
              <i class="icon-base ri ri-user-add-line icon-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <p class="mb-0">New This Month</p>
            <h5 class="mb-0">{{ $stats['new_this_month'] }}</h5>
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
            <label class="form-label">Search Guest</label>
            <input type="text" class="form-control" id="searchInput"
                   value="{{ $search }}"
                   placeholder="Name or email…">
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select id="statusFilter" class="form-select">
              <option value="">All Status</option>
              <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            <a href="{{ route('super_admin.guests.all') }}"
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
        <h5 class="card-title m-0">Guest Accounts</h5>
        <small class="text-body-secondary">{{ count($guests) }} guest(s) found</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Guest Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Reservations</th>
                <th>Total Stays</th>
                <th>Last Login</th>
                <th>Registered</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($guests as $i => $guest)
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
                        <small class="text-body-secondary">ID #{{ $guest->user_id }}</small>
                      </div>
                    </div>
                  </td>
                  <td>{{ $guest->email }}</td>
                  <td>
                    @if($guest->STATUS === 'active')
                      <span class="badge bg-label-success">Active</span>
                    @elseif($guest->STATUS === 'inactive')
                      <span class="badge bg-label-danger">Inactive</span>
                    @else
                      <span class="badge bg-label-secondary">{{ $guest->STATUS ?? 'N/A' }}</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-label-info rounded-pill">{{ $guest->total_reservations }}</span>
                  </td>
                  <td>
                    <span class="badge bg-label-primary rounded-pill">{{ $guest->total_stays }}</span>
                  </td>
                  <td class="text-body-secondary">
                    {{ $guest->last_login_at ? \Carbon\Carbon::parse($guest->last_login_at)->format('M d, Y') : '—' }}
                  </td>
                  <td class="text-body-secondary">
                    {{ \Carbon\Carbon::parse($guest->created_at)->format('M d, Y') }}
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
                           onclick="toggleGuestStatus(
                             {{ $guest->user_id }},
                             '{{ $guest->STATUS }}',
                             '{{ addslashes(trim(($guest->first_name ?? '') . ' ' . ($guest->last_name ?? ''))) }}'
                           )">
                          <i class="icon-base ri ri-toggle-line me-2"></i>
                          {{ $guest->STATUS === 'active' ? 'Deactivate' : 'Activate' }}
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                           onclick="deleteGuest(
                             {{ $guest->user_id }},
                             '{{ addslashes(trim(($guest->first_name ?? '') . ' ' . ($guest->last_name ?? ''))) }}',
                             '{{ $guest->STATUS }}'
                           )">
                          <i class="icon-base ri ri-delete-bin-line me-2"></i>Deactivate Account
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-5">
                    <i class="icon-base ri ri-user-3-line icon-48px text-body-secondary mb-3 d-block"></i>
                    <p class="mb-0 text-body-secondary">No guests found.</p>
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

{{-- ── GUEST PROFILE MODAL ──────────────────────────────────── --}}
<div class="modal fade" id="guestProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-user-3-line me-2"></i>
          Guest Profile
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="guestProfileContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading…</span>
          </div>
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
    show:         '{{ route('super_admin.guests.show', ':id') }}',
    updateStatus: '{{ route('super_admin.guests.update-status', ':id') }}',
    destroy:      '{{ route('super_admin.guests.destroy', ':id') }}',
    all:          '{{ route('super_admin.guests.all') }}',
    csrfToken:    '{{ csrf_token() }}',
  };
</script>
<script src="{{ asset('js/guestjs/all_script.js') }}"></script>
@endsection