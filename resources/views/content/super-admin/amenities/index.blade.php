@extends('layouts/contentNavbarLayout')
@section('title', 'Amenities - Management')

@section('content')
  <div class="row gy-6">
    <!-- Page Header -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Amenities Management</h4>
              <p class="mb-0">Manage and view all room amenities</p>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addAmenityModal">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add New Amenity
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Amenity Statistics -->
    <div class="col-xl-4 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-primary rounded shadow-xs">
                <i class="icon-base ri ri-list-check icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Total Amenities</p>
              <h5 class="mb-0">{{ $stats['total_amenities'] ?? 0 }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-success rounded shadow-xs">
                <i class="icon-base ri ri-checkbox-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">In Use</p>
              <h5 class="mb-0">{{ $stats['in_use_amenities'] ?? 0 }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-info rounded shadow-xs">
                <i class="icon-base ri ri-hotel-bed-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Rooms Using</p>
              <h5 class="mb-0">{{ $stats['rooms_with_amenities'] ?? 0 }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Amenities Table -->
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h5 class="mb-0">All Amenities</h5>
          <div class="d-flex gap-2 flex-wrap">
            <input type="text" id="searchTable" class="form-control form-control-sm" placeholder="Search amenities..." style="width: 200px;">
            <button class="btn btn-outline-secondary btn-sm" type="button">
              <i class="icon-base ri ri-download-line me-1"></i>
              Export
            </button>
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.location.reload()">
              <i class="icon-base ri ri-refresh-line me-1"></i>
              Refresh
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width: 80px;">ID</th>
                <th>Amenity Name</th>
                <th class="text-center">Rooms Using</th>
                <th class="text-center">Status</th>
                <th style="width: 150px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($amenities as $amenity)
                <tr>
                  <td>
                    <span class="text-body-secondary">#{{ $amenity->amenity_id }}</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                          <i class="icon-base ri ri-check-line"></i>
                        </span>
                      </div>
                      <span class="fw-medium">{{ $amenity->amenity_name }}</span>
                    </div>
                  </td>
                  <td class="text-center">
                    @php
                      $roomCount = $amenity->rooms->count();
                    @endphp
                    @if($roomCount > 0)
                      <span class="badge bg-label-primary rounded-pill cursor-pointer"
                            data-bs-toggle="modal"
                            data-bs-target="#roomsUsingModal{{ $amenity->amenity_id }}"
                            role="button">
                        {{ $roomCount }} {{ $roomCount > 1 ? 'Rooms' : 'Room' }}
                      </span>
                    @else
                      <span class="badge bg-label-secondary rounded-pill">Not Used</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($roomCount > 0)
                      <span class="badge bg-label-success">Active</span>
                    @else
                      <span class="badge bg-label-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                              type="button"
                              title="View Details"
                              data-bs-toggle="modal"
                              data-bs-target="#viewAmenityModal{{ $amenity->amenity_id }}">
                        <i class="icon-base ri ri-eye-line"></i>
                      </button>
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                              type="button"
                              title="Edit"
                              data-bs-toggle="modal"
                              data-bs-target="#editAmenityModal{{ $amenity->amenity_id }}">
                        <i class="icon-base ri ri-edit-line"></i>
                      </button>
                      <button class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                              type="button"
                              title="Delete"
                              onclick="confirmDelete({{ $amenity->amenity_id }}, '{{ $amenity->amenity_name }}', {{ $roomCount }})">
                        <i class="icon-base ri ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    @if(count($amenities) === 0)
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="icon-base ri ri-list-check icon-64px text-body-secondary mb-4"></i>
            <h5 class="mb-2">No Amenities Found</h5>
            <p class="mb-4 text-body-secondary">There are no amenities available in the system.</p>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addAmenityModal">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add Your First Amenity
            </button>
          </div>
        </div>
      </div>
    @endif

    <!-- Popular Amenities -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Most Used Amenities</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>Amenity Name</th>
                  <th class="text-center">Total Rooms</th>
                  <th>Usage Percentage</th>
                  <th>Popular In</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $sortedAmenities = $amenities->sortByDesc(function($amenity) {
                    return $amenity->rooms->count();
                  })->take(10);
                  $totalRooms = $rooms->count() ?: 1;
                @endphp
                @foreach($sortedAmenities as $index => $amenity)
                  @php
                    $roomCount = $amenity->rooms->count();
                    $percentage = round(($roomCount / $totalRooms) * 100);
                    $roomTypes = $amenity->rooms->pluck('room_type_name')->unique()->take(3);
                  @endphp
                  <tr>
                    <td>
                      <span class="badge bg-label-primary rounded-pill">#{{ $index + 1 }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="icon-base ri ri-star-line text-warning me-2"></i>
                        <span class="fw-medium">{{ $amenity->amenity_name }}</span>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-label-info rounded-pill">{{ $roomCount }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress" style="width: 100px; height: 8px;">
                          <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="text-body-secondary">{{ $percentage }}%</span>
                      </div>
                    </td>
                    <td>
                      @if($roomTypes->count() > 0)
                        <div class="d-flex flex-wrap gap-1">
                          @foreach($roomTypes as $roomType)
                            <span class="badge bg-label-secondary">{{ $roomType }}</span>
                          @endforeach
                          @if($amenity->rooms->pluck('room_type_name')->unique()->count() > 3)
                            <span class="badge bg-label-secondary">+{{ $amenity->rooms->pluck('room_type_name')->unique()->count() - 3 }} more</span>
                          @endif
                        </div>
                      @else
                        <span class="text-body-secondary">—</span>
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
  </div>

  <!-- Add Amenity Modal -->
  <div class="modal fade" id="addAmenityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="icon-base ri ri-add-line me-2"></i>
            Add New Amenity
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="amenity_name" class="form-label">Amenity Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="amenity_name" name="amenity_name" required placeholder="e.g., WiFi, Air Conditioning">
            </div>
            <div class="alert alert-info mb-0">
              <i class="icon-base ri ri-information-line me-2"></i>
              The amenity will be available for assignment to rooms after creation.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add Amenity
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Amenity Modals -->
  @foreach($amenities as $amenity)
    <div class="modal fade" id="editAmenityModal{{ $amenity->amenity_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-edit-line me-2"></i>
              Edit Amenity
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <div class="mb-3">
                <label for="edit_amenity_name_{{ $amenity->amenity_id }}" class="form-label">Amenity Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_amenity_name_{{ $amenity->amenity_id }}" name="amenity_name" value="{{ $amenity->amenity_name }}" required>
              </div>
              @if($amenity->rooms->count() > 0)
                <div class="alert alert-warning mb-0">
                  <i class="icon-base ri ri-alert-line me-2"></i>
                  This amenity is currently used in {{ $amenity->rooms->count() }} {{ $amenity->rooms->count() > 1 ? 'rooms' : 'room' }}. Changes will be reflected in all rooms.
                </div>
              @endif
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

  <!-- View Amenity Details Modals -->
  @foreach($amenities as $amenity)
    <div class="modal fade" id="viewAmenityModal{{ $amenity->amenity_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-information-line me-2"></i>
              Amenity Details
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <!-- Amenity Information -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3">Basic Information</h6>
                <div class="card mb-0">
                  <div class="card-body">
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Amenity ID</small>
                      <span class="fw-medium">#{{ $amenity->amenity_id }}</span>
                    </div>
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Amenity Name</small>
                      <span class="fw-medium">{{ $amenity->amenity_name }}</span>
                    </div>
                    <div class="mb-0">
                      <small class="text-body-secondary d-block mb-1">Status</small>
                      @if($amenity->rooms->count() > 0)
                        <span class="badge bg-label-success">Active</span>
                      @else
                        <span class="badge bg-label-secondary">Inactive</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <!-- Usage Statistics -->
              <div class="col-md-6 mb-4">
                <h6 class="mb-3">Usage Statistics</h6>
                <div class="card mb-0">
                  <div class="card-body">
                    <div class="mb-3">
                      <small class="text-body-secondary d-block mb-1">Total Rooms Using</small>
                      <h5 class="mb-0 text-primary">{{ $amenity->rooms->count() }}</h5>
                    </div>
                    <div class="mb-0">
                      <small class="text-body-secondary d-block mb-1">Usage Rate</small>
                      @php
                        $totalRooms = $rooms->count() ?: 1;
                        $usageRate = round(($amenity->rooms->count() / $totalRooms) * 100);
                      @endphp
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                          <div class="progress-bar" role="progressbar" style="width: {{ $usageRate }}%"></div>
                        </div>
                        <span class="fw-medium">{{ $usageRate }}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Rooms Using This Amenity -->
              @if($amenity->rooms->count() > 0)
                <div class="col-12">
                  <h6 class="mb-3">Rooms Using This Amenity</h6>
                  <div class="card mb-0">
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                          <thead>
                            <tr>
                              <th>Room #</th>
                              <th>Room Type</th>
                              <th>Status</th>
                              <th>Rate</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($amenity->rooms->take(10) as $room)
                              <tr>
                                <td><span class="fw-medium">{{ $room->room_number }}</span></td>
                                <td>{{ $room->room_type_name }}</td>
                                <td>
                                  @if($room->status === 'available')
                                    <span class="badge bg-label-success">Available</span>
                                  @elseif($room->status === 'occupied')
                                    <span class="badge bg-label-warning">Occupied</span>
                                  @else
                                    <span class="badge bg-label-danger">Maintenance</span>
                                  @endif
                                </td>
                                <td>₱{{ number_format($room->rate_per_night, 2) }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                      @if($amenity->rooms->count() > 10)
                        <div class="text-center mt-3">
                          <small class="text-body-secondary">Showing 10 of {{ $amenity->rooms->count() }} rooms</small>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @else
                <div class="col-12">
                  <div class="alert alert-info mb-0">
                    <i class="icon-base ri ri-information-line me-2"></i>
                    This amenity is not currently assigned to any rooms.
                  </div>
                </div>
              @endif
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editAmenityModal{{ $amenity->amenity_id }}">
              <i class="icon-base ri ri-edit-line me-1"></i>
              Edit Amenity
            </button>
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <!-- Rooms Using Amenity Modals -->
  @foreach($amenities as $amenity)
    @if($amenity->rooms->count() > 0)
      <div class="modal fade" id="roomsUsingModal{{ $amenity->amenity_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Rooms Using "{{ $amenity->amenity_name }}"</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Room #</th>
                      <th>Room Type</th>
                      <th>Status</th>
                      <th>Rate/Night</th>
                      <th>Capacity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($amenity->rooms as $room)
                      <tr>
                        <td><span class="fw-medium">{{ $room->room_number }}</span></td>
                        <td>
                          <div class="d-flex align-items-center">
                            <i class="icon-base ri ri-hotel-bed-line text-primary me-2"></i>
                            {{ $room->room_type_name }}
                          </div>
                        </td>
                        <td>
                          @if($room->status === 'available')
                            <span class="badge bg-label-success">Available</span>
                          @elseif($room->status === 'occupied')
                            <span class="badge bg-label-warning">Occupied</span>
                          @else
                            <span class="badge bg-label-danger">Maintenance</span>
                          @endif
                        </td>
                        <td><span class="fw-medium text-primary">₱{{ number_format($room->rate_per_night, 2) }}</span></td>
                        <td>
                          <span class="badge bg-label-info rounded-pill">{{ $room->max_pax }} {{ $room->max_pax > 1 ? 'Guests' : 'Guest' }}</span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endforeach

  <style>
    .cursor-pointer {
      cursor: pointer;
    }
    .cursor-pointer:hover {
      opacity: 0.8;
      transform: scale(1.05);
      transition: all 0.2s ease;
    }
  </style>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Simple table search
      const searchInput = document.getElementById('searchTable');
      const table = document.querySelector('.table-responsive table tbody');
      const rows = table.querySelectorAll('tr');

      searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
      });
    });

    // Delete confirmation
    function confirmDelete(amenityId, amenityName, roomCount) {
      if (roomCount > 0) {
        if (confirm(`Warning: "${amenityName}" is currently used in ${roomCount} room(s). Deleting this amenity will remove it from all rooms.\n\nAre you sure you want to continue?`)) {
          // Submit delete form
          deleteAmenity(amenityId);
        }
      } else {
        if (confirm(`Are you sure you want to delete "${amenityName}"?`)) {
          deleteAmenity(amenityId);
        }
      }
    }

    function deleteAmenity(amenityId) {
      // Create and submit delete form
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/super_admin/amenities/${amenityId}`;

      const csrfToken = document.createElement('input');
      csrfToken.type = 'hidden';
      csrfToken.name = '_token';
      csrfToken.value = '{{ csrf_token() }}';

      const methodField = document.createElement('input');
      methodField.type = 'hidden';
      methodField.name = '_method';
      methodField.value = 'DELETE';

      form.appendChild(csrfToken);
      form.appendChild(methodField);
      document.body.appendChild(form);
      form.submit();
    }
  </script>
  @endpush
@endsection
