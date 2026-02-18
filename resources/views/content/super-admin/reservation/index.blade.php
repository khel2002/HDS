@extends('layouts/contentNavbarLayout')
@section('title', 'Reservations - Management')

@section('page-style')
  <link rel="stylesheet" href="{{ asset('assets/css/reservations.css') }}">
@endsection

@section('content')
  <div class="row gy-6">
    <!-- Page Header -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Reservations Management</h4>
              <p class="mb-0">Manage and monitor all hotel reservations</p>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addReservationModal">
              <i class="icon-base ri ri-add-line me-1"></i>
              New Reservation
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reservation Statistics -->
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
              <h5 class="mb-0">{{ $stats['total_reservations'] ?? 0 }}</h5>
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
              <h5 class="mb-0">{{ $stats['pending_reservations'] ?? 0 }}</h5>
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
              <h5 class="mb-0">{{ $stats['approved_reservations'] ?? 0 }}</h5>
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
                <i class="icon-base ri ri-money-dollar-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Total Revenue</p>
              <h5 class="mb-0">₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label">Search</label>
              <input type="text" class="form-control" id="searchReservation" placeholder="Guest name, email, room...">
            </div>
            <div class="col-md-2">
              <label class="form-label">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Date Range</label>
              <select class="form-select" id="dateRangeFilter">
                <option value="">All Dates</option>
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Room Type</label>
              <select class="form-select" id="roomTypeFilter">
                <option value="">All Types</option>
                @foreach($roomTypes as $type)
                  <option value="{{ $type->room_type_id }}">{{ $type->room_type_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" onclick="resetFilters()">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reservations Table -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0 me-2">All Reservations</h5>
            <div class="dropdown">
              <button class="btn text-body-secondary p-0" type="button" id="reservationsDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-base ri ri-more-2-line icon-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="reservationsDropdown">
                <a class="dropdown-item" href="javascript:void(0);" onclick="window.location.reload()">Refresh</a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="exportToExcel()">Export to Excel</a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="exportToPDF()">Export to PDF</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0);" onclick="window.print()">Print</a>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th class="text-truncate" style="width: 80px;">ID</th>
                  <th class="text-truncate">Guest Details</th>
                  <th class="text-truncate">Room</th>
                  <th class="text-truncate">Check-in</th>
                  <th class="text-truncate">Check-out</th>
                  <th class="text-truncate">Guests</th>
                  <th class="text-truncate">Amount</th>
                  <th class="text-truncate text-center">Status</th>
                  <th class="text-truncate">Actions</th>
                </tr>
              </thead>
              <tbody id="reservationsTableBody">
                @foreach($reservations as $reservation)
                  <tr data-reservation-id="{{ $reservation->reservation_id }}" 
                      data-status="{{ $reservation->reservation_status }}"
                      data-guest-name="{{ strtolower($reservation->guest_first_name . ' ' . $reservation->guest_last_name) }}"
                      data-guest-email="{{ strtolower($reservation->guest_email ?? '') }}"
                      data-room-type="{{ $reservation->room_type_id ?? '' }}">
                    <td class="text-truncate">
                      <span class="text-body-secondary fw-medium">#{{ $reservation->reservation_id }}</span>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex flex-column">
                        <span class="fw-medium">{{ $reservation->guest_first_name }} {{ $reservation->guest_last_name }}</span>
                        <small class="text-body-secondary">{{ $reservation->guest_email ?? 'N/A' }}</small>
                      </div>
                    </td>
                    <td class="text-truncate">
                      @if($reservation->room_number)
                        <div class="d-flex align-items-center">
                          <i class="icon-base ri ri-hotel-bed-line text-primary me-2"></i>
                          <div>
                            <span class="fw-medium">{{ $reservation->room_number }}</span>
                            <br><small class="text-body-secondary">{{ $reservation->room_type_name }}</small>
                          </div>
                        </div>
                      @else
                        <span class="badge bg-label-secondary">Not Assigned</span>
                      @endif
                    </td>
                    <td class="text-truncate">
                      {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}
                    </td>
                    <td class="text-truncate">
                      {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('M d, Y') }}
                    </td>
                    <td class="text-truncate">
                      <span class="badge bg-label-info rounded-pill">
                        {{ $reservation->adults + $reservation->children }} 
                        {{ ($reservation->adults + $reservation->children) > 1 ? 'Guests' : 'Guest' }}
                      </span>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex flex-column">
                        <span class="fw-medium text-primary">₱{{ number_format($reservation->total_amount, 2) }}</span>
                        @if($reservation->balance > 0)
                          <small class="text-warning">Balance: ₱{{ number_format($reservation->balance, 2) }}</small>
                        @else
                          <small class="text-success">Paid</small>
                        @endif
                      </div>
                    </td>
                    <td class="text-truncate text-center">
                      @if($reservation->reservation_status === 'pending')
                        <span class="badge bg-label-warning">Pending</span>
                      @elseif($reservation->reservation_status === 'approved')
                        <span class="badge bg-label-success">Approved</span>
                      @elseif($reservation->reservation_status === 'rejected')
                        <span class="badge bg-label-danger">Rejected</span>
                      @else
                        <span class="badge bg-label-secondary">Cancelled</span>
                      @endif
                    </td>
                    <td class="text-truncate">
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewReservationModal{{ $reservation->reservation_id }}">
                            <i class="icon-base ri ri-eye-line me-2"></i>View Details
                          </a>
                          @if($reservation->reservation_status === 'pending')
                            <a class="dropdown-item" href="javascript:void(0);" onclick="approveReservation({{ $reservation->reservation_id }})">
                              <i class="icon-base ri ri-checkbox-circle-line me-2 text-success"></i>Approve
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="rejectReservation({{ $reservation->reservation_id }})">
                              <i class="icon-base ri ri-close-circle-line me-2 text-danger"></i>Reject
                            </a>
                          @endif
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editReservationModal{{ $reservation->reservation_id }}">
                            <i class="icon-base ri ri-edit-line me-2"></i>Edit
                          </a>
                          @if($reservation->reservation_status !== 'cancelled')
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="cancelReservation({{ $reservation->reservation_id }}, '{{ $reservation->guest_first_name }} {{ $reservation->guest_last_name }}')">
                              <i class="icon-base ri ri-close-line me-2"></i>Cancel Reservation
                            </a>
                          @endif
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          
          @if(count($reservations) === 0)
            <div class="text-center py-5">
              <i class="icon-base ri ri-calendar-check-line icon-64px text-body-secondary mb-4"></i>
              <h5 class="mb-2">No Reservations Found</h5>
              <p class="mb-4 text-body-secondary">There are no reservations matching your criteria.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Upcoming Check-ins -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Upcoming Check-ins (Next 7 Days)</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Guest Name</th>
                  <th>Room</th>
                  <th>Nights</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $upcomingReservations = collect($reservations)
                    ->filter(function($reservation) {
                      $checkIn = \Carbon\Carbon::parse($reservation->check_in_date);
                      $today = \Carbon\Carbon::today();
                      return $checkIn->between($today, $today->copy()->addDays(7)) && $reservation->reservation_status === 'approved';
                    })
                    ->sortBy('check_in_date');
                @endphp
                @forelse($upcomingReservations as $reservation)
                  <tr>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="fw-medium">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}</span>
                        <small class="text-body-secondary">{{ \Carbon\Carbon::parse($reservation->check_in_date)->diffForHumans() }}</small>
                      </div>
                    </td>
                    <td>
                      <span class="fw-medium">{{ $reservation->guest_first_name }} {{ $reservation->guest_last_name }}</span>
                    </td>
                    <td>
                      @if($reservation->room_number)
                        <span class="badge bg-label-primary">{{ $reservation->room_number }}</span>
                      @else
                        <span class="badge bg-label-secondary">Not Assigned</span>
                      @endif
                    </td>
                    <td>{{ $reservation->no_nights }} {{ $reservation->no_nights > 1 ? 'nights' : 'night' }}</td>
                    <td><span class="badge bg-label-success">Confirmed</span></td>
                    <td>
                      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewReservationModal{{ $reservation->reservation_id }}">
                        View
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-body-secondary">No upcoming check-ins in the next 7 days</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Reservation Modal -->
  <div class="modal fade" id="addReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="icon-base ri ri-add-line me-2"></i>
            Create New Reservation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('super_admin.reservations.store') }}" method="POST" id="addReservationForm">
          @csrf
          <div class="modal-body">
            <div class="row">
              <!-- Guest Information -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3 text-primary">Guest Information</h6>
                <div class="mb-3">
                  <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                  <label for="middle_name" class="form-label">Middle Name</label>
                  <input type="text" class="form-control" id="middle_name" name="middle_name">
                </div>
                <div class="mb-3">
                  <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                  <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                </div>
                <div class="mb-3">
                  <label for="dob" class="form-label">Date of Birth</label>
                  <input type="date" class="form-control" id="dob" name="dob">
                </div>
              </div>

              <!-- Reservation Details -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3 text-primary">Reservation Details</h6>
                <div class="mb-3">
                  <label for="room_id" class="form-label">Room <span class="text-danger">*</span></label>
                  <select class="form-select" id="room_id" name="room_id" required>
                    <option value="">Select Room</option>
                    @foreach($rooms as $room)
                      <option value="{{ $room->room_id }}" data-rate="{{ $room->rate_per_night }}">
                        {{ $room->room_number }} - {{ $room->room_type_name }} (₱{{ number_format($room->rate_per_night, 2) }}/night)
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3">
                  <label for="check_in_date" class="form-label">Check-in Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="check_in_date" name="check_in_date" required>
                </div>
                <div class="mb-3">
                  <label for="check_out_date" class="form-label">Check-out Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="check_out_date" name="check_out_date" required>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="adults" class="form-label">Adults <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="adults" name="adults" min="1" value="1" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="children" class="form-label">Children</label>
                    <input type="number" class="form-control" id="children" name="children" min="0" value="0">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="purpose" class="form-label">Purpose of Visit</label>
                  <textarea class="form-control" id="purpose" name="purpose" rows="2"></textarea>
                </div>
                <div class="alert alert-info mb-0">
                  <div class="d-flex justify-content-between align-items-center">
                    <span><strong>Total Amount:</strong></span>
                    <span class="fw-bold text-primary" id="totalAmountDisplay">₱0.00</span>
                  </div>
                  <small class="d-block mt-2" id="nightsDisplay">Select dates to calculate</small>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ri ri-save-line me-1"></i>
              Create Reservation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Reservation Modals -->
  @foreach($reservations as $reservation)
    <div class="modal fade" id="viewReservationModal{{ $reservation->reservation_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-information-line me-2"></i>
              Reservation #{{ $reservation->reservation_id }} Details
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <!-- Guest Information -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3 text-primary">Guest Information</h6>
                <div class="card">
                  <div class="card-body">
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Full Name</small>
                      <span class="fw-medium">{{ $reservation->guest_first_name }} {{ $reservation->guest_middle_name }} {{ $reservation->guest_last_name }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Email</small>
                      <span class="fw-medium">{{ $reservation->guest_email ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Contact Number</small>
                      <span class="fw-medium">{{ $reservation->guest_contact ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-0">
                      <small class="text-body-secondary d-block mb-1">Date of Birth</small>
                      <span class="fw-medium">{{ $reservation->guest_dob ? \Carbon\Carbon::parse($reservation->guest_dob)->format('M d, Y') : 'N/A' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Reservation Details -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3 text-primary">Reservation Details</h6>
                <div class="card">
                  <div class="card-body">
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Room</small>
                      @if($reservation->room_number)
                        <span class="fw-medium">{{ $reservation->room_number }} - {{ $reservation->room_type_name }}</span>
                      @else
                        <span class="badge bg-label-secondary">Not Assigned</span>
                      @endif
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Check-in Date</small>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('l, F d, Y') }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Check-out Date</small>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('l, F d, Y') }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Number of Nights</small>
                      <span class="fw-medium">{{ $reservation->no_nights }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Guests</small>
                      <span class="fw-medium">{{ $reservation->adults }} Adult(s), {{ $reservation->children }} Child(ren)</span>
                    </div>
                    <div class="mb-0">
                      <small class="text-body-secondary d-block mb-1">Status</small>
                      @if($reservation->reservation_status === 'pending')
                        <span class="badge bg-label-warning">Pending Approval</span>
                      @elseif($reservation->reservation_status === 'approved')
                        <span class="badge bg-label-success">Approved</span>
                      @elseif($reservation->reservation_status === 'rejected')
                        <span class="badge bg-label-danger">Rejected</span>
                      @else
                        <span class="badge bg-label-secondary">Cancelled</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <!-- Payment Information -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3 text-primary">Payment Information</h6>
                <div class="card">
                  <div class="card-body">
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Total Amount</small>
                      <h5 class="mb-0 text-primary">₱{{ number_format($reservation->total_amount, 2) }}</h5>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Reservation Fee</small>
                      <span class="fw-medium">₱{{ number_format($reservation->reservation_fee, 2) }}</span>
                      @if($reservation->reservation_fee_paid)
                        <span class="badge bg-label-success ms-2">Paid</span>
                      @else
                        <span class="badge bg-label-warning ms-2">Unpaid</span>
                      @endif
                    </div>
                    <div class="mb-0">
                      <small class="text-body-secondary d-block mb-1">Balance</small>
                      <span class="fw-medium {{ $reservation->balance > 0 ? 'text-warning' : 'text-success' }}">
                        ₱{{ number_format($reservation->balance, 2) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Additional Information -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3 text-primary">Additional Information</h6>
                <div class="card">
                  <div class="card-body">
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Purpose of Visit</small>
                      <span class="fw-medium">{{ $reservation->purpose ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Booking Date</small>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($reservation->booking_date)->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="mb-0">
                      <small class="text-body-secondary d-block mb-1">Created At</small>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($reservation->created_at)->format('M d, Y h:i A') }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            @if($reservation->reservation_status === 'pending')
              <button type="button" class="btn btn-success" onclick="approveReservation({{ $reservation->reservation_id }})">
                <i class="icon-base ri ri-checkbox-circle-line me-1"></i>
                Approve
              </button>
              <button type="button" class="btn btn-danger" onclick="rejectReservation({{ $reservation->reservation_id }})">
                <i class="icon-base ri ri-close-circle-line me-1"></i>
                Reject
              </button>
            @endif
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editReservationModal{{ $reservation->reservation_id }}">
              <i class="icon-base ri ri-edit-line me-1"></i>
              Edit
            </button>
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <!-- Edit Reservation Modals -->
  @foreach($reservations as $reservation)
    <div class="modal fade" id="editReservationModal{{ $reservation->reservation_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-edit-line me-2"></i>
              Edit Reservation #{{ $reservation->reservation_id }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('super_admin.reservations.update', $reservation->reservation_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <div class="row">
                <!-- Guest Information -->
                <div class="col-md-6 mb-4">
                  <h6 class="mb-3 text-primary">Guest Information</h6>
                  <div class="mb-3">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="first_name" value="{{ $reservation->guest_first_name }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" value="{{ $reservation->guest_middle_name }}">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="last_name" value="{{ $reservation->guest_last_name }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ $reservation->guest_email }}">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" value="{{ $reservation->guest_contact }}">
                  </div>
                </div>

                <!-- Reservation Details -->
                <div class="col-md-6 mb-4">
                  <h6 class="mb-3 text-primary">Reservation Details</h6>
                  <div class="mb-3">
                    <label class="form-label">Room</label>
                    <select class="form-select" name="room_id">
                      <option value="">Not Assigned</option>
                      @foreach($rooms as $room)
                        <option value="{{ $room->room_id }}" {{ $reservation->room_id == $room->room_id ? 'selected' : '' }}>
                          {{ $room->room_number }} - {{ $room->room_type_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Check-in Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="check_in_date" value="{{ $reservation->check_in_date }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Check-out Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="check_out_date" value="{{ $reservation->check_out_date }}" required>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Adults <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="adults" value="{{ $reservation->adults }}" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Children</label>
                      <input type="number" class="form-control" name="children" value="{{ $reservation->children }}" min="0">
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="reservation_status">
                      <option value="pending" {{ $reservation->reservation_status === 'pending' ? 'selected' : '' }}>Pending</option>
                      <option value="approved" {{ $reservation->reservation_status === 'approved' ? 'selected' : '' }}>Approved</option>
                      <option value="rejected" {{ $reservation->reservation_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                      <option value="cancelled" {{ $reservation->reservation_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Purpose of Visit</label>
                    <textarea class="form-control" name="purpose" rows="2">{{ $reservation->purpose }}</textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ri ri-save-line me-1"></i>
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/reservationjs/superadmin.js') }}"></script>
@endsection