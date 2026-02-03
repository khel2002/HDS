@extends('layouts/contentNavbarLayout')
@section('title', 'Rooms - Management')

@section('content')
  <div class="row gy-6">
    <!-- Page Header -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Room Management</h4>
              <p class="mb-0">Manage and view all hotel rooms</p>
            </div>
            <button class="btn btn-primary" type="button">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add New Room
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Room Statistics -->
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
              <p class="mb-0">Total Rooms</p>
              <h5 class="mb-0">{{ $stats['total_rooms'] }}</h5>
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
              <p class="mb-0">Available</p>
              <h5 class="mb-0">{{ $stats['available_rooms'] }}</h5>
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
                <i class="icon-base ri ri-user-location-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Occupied</p>
              <h5 class="mb-0">{{ $stats['occupied_rooms'] }}</h5>
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
                <i class="icon-base ri ri-tools-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Maintenance</p>
              <h5 class="mb-0">{{ $stats['maintenance_rooms'] }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rooms Table -->
    <div class="col-12">
      <div class="card">
<<<<<<< Updated upstream
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h5 class="mb-0">All Rooms</h5>
          <div class="d-flex gap-2 flex-wrap">
            <input type="text" id="searchTable" class="form-control form-control-sm" placeholder="Search rooms..." style="width: 200px;">
            <select id="filterStatus" class="form-select form-select-sm" style="width: 150px;">
              <option value="">All Status</option>
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="maintenance">Maintenance</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm" type="button">
              <i class="icon-base ri ri-download-line me-1"></i>
              Export
            </button>
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.location.reload()">
              <i class="icon-base ri ri-refresh-line me-1"></i>
              Refresh
            </button>
=======
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label">Room Type</label>
              <select class="form-select" id="roomTypeFilter">
                <option value="">All Types</option>
                @foreach ($roomTypes as $type)
                  <option value="{{ $type->room_type_id }}">{{ $type->room_type_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Search</label>
              <input type="text" class="form-control" id="searchRoom" placeholder="Room number...">
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Reset Filters
              </button>
            </div>
>>>>>>> Stashed changes
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Room #</th>
                <th>Image</th>
                <th>Room Type</th>
                <th>Status</th>
                <th>Rate/Night</th>
                <th>Max Capacity</th>
                <th>Amenities</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rooms as $room)
                <tr>
                  <td>
                    <span class="fw-medium">{{ $room->room_number }}</span>
                  </td>
                  <td>
                    @if ($room->image_path)
                      <img
                        src="{{ asset('storage/' . $room->image_path) }}"
                        alt="{{ $room->room_type_name }}"
                        class="rounded"
                        style="width: 60px; height: 45px; object-fit: cover;"
                      >
                    @else
                      <div class="bg-label-secondary rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 45px;">
                        <i class="icon-base ri ri-image-line text-body-secondary"></i>
                      </div>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="icon-base ri ri-hotel-bed-line icon-20px text-primary me-2"></i>
                      <span>{{ $room->room_type_name }}</span>
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
                  <td>
                    <span class="fw-medium text-primary">₱{{ number_format($room->rate_per_night, 2) }}</span>
                  </td>
                  <td>
                    <span class="badge bg-label-info rounded-pill">{{ $room->max_pax }} {{ $room->max_pax > 1 ? 'Guests' : 'Guest' }}</span>
                  </td>
                  <td>
                    @if(!empty($room->amenities))
                      <div class="d-flex flex-wrap gap-1">
                        @foreach(array_slice($room->amenities, 0, 2) as $amenity)
                          <span class="badge bg-label-secondary" style="font-size: 0.75rem;">{{ $amenity }}</span>
                        @endforeach
                        @if(count($room->amenities) > 2)
                          <span class="badge bg-label-secondary cursor-pointer" style="font-size: 0.75rem;"
                                data-bs-toggle="modal"
                                data-bs-target="#amenitiesModal{{ $room->room_id }}"
                                role="button">
                            +{{ count($room->amenities) - 2 }}
                          </span>
                        @endif
                      </div>
                    @else
                      <span class="text-body-secondary">—</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="View Details" data-bs-toggle="modal" data-bs-target="#viewDetailsModal{{ $room->room_id }}">
                        <i class="icon-base ri ri-eye-line"></i>
                      </button>
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit">
                        <i class="icon-base ri ri-edit-line"></i>
                      </button>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);">
                            <i class="icon-base ri ri-refresh-line me-2"></i>
                            Change Status
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);">
                            <i class="icon-base ri ri-image-add-line me-2"></i>
                            Update Image
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);">
                            <i class="icon-base ri ri-file-copy-line me-2"></i>
                            Duplicate Room
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="javascript:void(0);">
                            <i class="icon-base ri ri-delete-bin-line me-2"></i>
                            Delete Room
                          </a>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

<<<<<<< Updated upstream
    <!-- Amenities Modals -->
    @foreach($rooms as $room)
      @if(!empty($room->amenities) && count($room->amenities) > 2)
        <div class="modal fade" id="amenitiesModal{{ $room->room_id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Room {{ $room->room_number }} - All Amenities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="d-flex flex-wrap gap-2">
                  @foreach($room->amenities as $amenity)
                    <span class="badge bg-label-info">
                      <i class="icon-base ri ri-check-line me-1"></i>
                      {{ $amenity }}
                    </span>
                  @endforeach
=======
    <!-- Rooms Grid -->
    @foreach ($rooms as $room)
      <div class="col-xl-4 col-md-6">
        <div class="card h-100">
          <div class="card-header p-0 position-relative">
            @if ($room->image_path)
              <img src="{{ asset('storage/' . $room->image_path) }}" class="card-img-top"
                alt="{{ $room->room_type_name }}" style="height: 200px; object-fit: cover;">
            @else
              <div class="bg-label-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="icon-base ri ri-image-line icon-64px text-body-secondary"></i>
              </div>
            @endif
            <div class="position-absolute top-0 end-0 m-3">
              @if ($room->status === 'available')
                <span class="badge bg-success">Available</span>
              @elseif($room->status === 'occupied')
                <span class="badge bg-warning">Occupied</span>
              @else
                <span class="badge bg-danger">Maintenance</span>
              @endif
            </div>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="mb-1">Room {{ $room->room_number }}</h5>
                <p class="mb-0 text-body-secondary">{{ $room->room_type_name }}</p>
              </div>
              <div class="dropdown">
                <button class="btn text-body-secondary p-0" type="button" data-bs-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  <i class="icon-base ri ri-more-2-line icon-24px"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                  <a class="dropdown-item" href="javascript:void(0);">Edit Room</a>
                  <a class="dropdown-item" href="javascript:void(0);">Change Status</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="javascript:void(0);">Delete Room</a>
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
    <!-- View Details Modals -->
    @foreach($rooms as $room)
      <div class="modal fade" id="viewDetailsModal{{ $room->room_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="icon-base ri ri-hotel-bed-line me-2"></i>
                Room {{ $room->room_number }} - Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <!-- Left Side - Room Image -->
                <div class="col-lg-5 mb-4 mb-lg-0">
                  @if ($room->image_path)
                    <img
                      src="{{ asset('storage/' . $room->image_path) }}"
                      class="img-fluid rounded"
                      alt="{{ $room->room_type_name }}"
                      style="width: 100%; height: 400px; object-fit: cover;"
                    >
                  @else
                    <div class="bg-label-secondary rounded d-flex align-items-center justify-content-center" style="width: 100%; height: 400px;">
                      <div class="text-center">
                        <i class="icon-base ri ri-image-line icon-64px text-body-secondary mb-3"></i>
                        <p class="text-body-secondary mb-0">No Image Available</p>
                      </div>
                    </div>
                  @endif
                </div>

                <!-- Right Side - Room Details -->
                <div class="col-lg-7">
                  <!-- Room Header Info -->
                  <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <div>
                        <h4 class="mb-1">{{ $room->room_type_name }}</h4>
                        <p class="text-body-secondary mb-0">Room {{ $room->room_number }}</p>
                      </div>
                      <div>
                        @if($room->status === 'available')
                          <span class="badge bg-success">Available</span>
                        @elseif($room->status === 'occupied')
                          <span class="badge bg-warning">Occupied</span>
                        @else
                          <span class="badge bg-danger">Maintenance</span>
                        @endif
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-4 mb-3">
                      <div>
                        <small class="text-body-secondary d-block">Rate per Night</small>
                        <h5 class="mb-0 text-primary">₱{{ number_format($room->rate_per_night, 2) }}</h5>
                      </div>
                      <div class="vr" style="height: 40px;"></div>
                      <div>
                        <small class="text-body-secondary d-block">Max Capacity</small>
                        <h6 class="mb-0">
                          <i class="icon-base ri ri-user-line me-1"></i>
                          {{ $room->max_pax }} {{ $room->max_pax > 1 ? 'Guests' : 'Guest' }}
                        </h6>
                      </div>
                      @if(isset($room->size_sqm))
                        <div class="vr" style="height: 40px;"></div>
                        <div>
                          <small class="text-body-secondary d-block">Room Size</small>
                          <h6 class="mb-0">{{ $room->size_sqm }} sqm</h6>
                        </div>
                      @endif
                    </div>
                  </div>

                  <!-- Additional Details -->
                  @if(isset($room->floor) || isset($room->bed_type))
                    <div class="mb-4">
                      <h6 class="mb-3">Additional Details</h6>
                      <div class="row g-3">
                        @if(isset($room->floor))
                          <div class="col-6">
                            <div class="d-flex align-items-center">
                              <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="icon-base ri ri-building-line"></i>
                                </span>
                              </div>
                              <div>
                                <small class="text-body-secondary d-block">Floor</small>
                                <span class="fw-medium">{{ $room->floor }}</span>
                              </div>
                            </div>
                          </div>
                        @endif
                        @if(isset($room->bed_type))
                          <div class="col-6">
                            <div class="d-flex align-items-center">
                              <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="icon-base ri ri-hotel-bed-line"></i>
                                </span>
                              </div>
                              <div>
                                <small class="text-body-secondary d-block">Bed Type</small>
                                <span class="fw-medium">{{ $room->bed_type }}</span>
                              </div>
                            </div>
                          </div>
                        @endif
                      </div>
                    </div>
                  @endif

                  <!-- Description -->
                  @if(isset($room->description) && !empty($room->description))
                    <div class="mb-4">
                      <h6 class="mb-2">Description</h6>
                      <p class="text-body-secondary mb-0">{{ $room->description }}</p>
                    </div>
                  @endif

                  <!-- Amenities -->
                  @if(!empty($room->amenities))
                    <div class="mb-0">
                      <h6 class="mb-3">Amenities & Features</h6>
                      <div class="row g-2">
                        @foreach($room->amenities as $amenity)
                          <div class="col-6">
                            <div class="d-flex align-items-center">
                              <i class="icon-base ri ri-checkbox-circle-line text-success me-2"></i>
                              <span>{{ $amenity }}</span>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
=======
            @if (!empty($room->amenities))
              <div class="mb-3">
                <p class="mb-2 fw-medium">Amenities:</p>
                <div class="d-flex flex-wrap gap-2">
                  @foreach (array_slice($room->amenities, 0, 3) as $amenity)
                    <span class="badge bg-label-info">{{ $amenity }}</span>
                  @endforeach
                  @if (count($room->amenities) > 3)
                    <span class="badge bg-label-secondary">+{{ count($room->amenities) - 3 }} more</span>
>>>>>>> Stashed changes
                  @endif
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              <div class="btn-group">
                <button type="button" class="btn btn-outline-primary">
                  <i class="icon-base ri ri-refresh-line me-1"></i>
                  Change Status
                </button>
                <button type="button" class="btn btn-primary">
                  <i class="icon-base ri ri-edit-line me-1"></i>
                  Edit Room
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach

    @if (count($rooms) === 0)
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="icon-base ri ri-hotel-bed-line icon-64px text-body-secondary mb-4"></i>
            <h5 class="mb-2">No Rooms Found</h5>
            <p class="mb-4 text-body-secondary">There are no rooms available in the system.</p>
            <button class="btn btn-primary" type="button">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add Your First Room
            </button>
          </div>
        </div>
      </div>
    @endif

    <!-- Room Types Summary -->
    <div class="col-12">
      <div class="card">
<<<<<<< Updated upstream
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Rooms Overview</h5>
          <a href="{{ route('super_admin.room-types.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="icon-base ri ri-settings-3-line me-1"></i>
            Manage Types
          </a>
=======
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0 me-2">Room Types Overview</h5>
            <div class="dropdown">
              <button class="btn text-body-secondary p-0" type="button" id="roomTypesDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-base ri ri-more-2-line icon-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="roomTypesDropdown">
                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                <a class="dropdown-item" href="javascript:void(0);">Export</a>
                <a class="dropdown-item" href="javascript:void(0);">Manage Types</a>
              </div>
            </div>
          </div>
>>>>>>> Stashed changes
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Room Type</th>
                  <th>Description</th>
                  <th>Rate/Night</th>
                  <th>Max Capacity</th>
                  <th class="text-center">Total Rooms</th>
                  <th class="text-center">Available</th>
                  <th class="text-center">Occupied</th>
                  <th class="text-center">Maintenance</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($roomTypes as $type)
                  @php
                    $typeRooms = $rooms->where('room_type_id', $type->room_type_id);
                    $typeAvailable = $typeRooms->where('status', 'available')->count();
                    $typeOccupied = $typeRooms->where('status', 'occupied')->count();
                    $typeMaintenance = $typeRooms->where('status', 'maintenance')->count();
                  @endphp
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="icon-base ri ri-hotel-bed-line icon-22px text-primary me-2"></i>
                        <span class="fw-medium">{{ $type->room_type_name }}</span>
                      </div>
                    </td>
                    <td class="text-truncate" style="max-width: 250px;" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $type->description ?? 'N/A' }}">{{ $type->description ?? 'N/A' }}</td>
                    <td><span class="fw-medium">₱{{ number_format($type->rate_per_night, 2) }}</span></td>
                    <td>{{ $type->max_pax }} {{ $type->max_pax > 1 ? 'Guests' : 'Guest' }}</td>
                    <td class="text-center">
                      <span class="badge bg-label-primary rounded-pill">{{ $typeRooms->count() }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-label-success rounded-pill">{{ $typeAvailable }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-label-warning rounded-pill">{{ $typeOccupied }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-label-danger rounded-pill">{{ $typeMaintenance }}</span>
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

      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });


      const searchInput = document.getElementById('searchTable');
      const filterStatus = document.getElementById('filterStatus');
      const table = document.querySelector('.table-responsive table tbody');
      const rows = table.querySelectorAll('tr');

      function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value.toLowerCase();

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          const statusBadge = row.querySelector('.badge');
          const rowStatus = statusBadge ? statusBadge.textContent.toLowerCase() : '';

          const matchesSearch = text.includes(searchTerm);
          const matchesStatus = !statusFilter || rowStatus.includes(statusFilter);

          row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
      }

      searchInput.addEventListener('keyup', filterTable);
      filterStatus.addEventListener('change', filterTable);
    });
  </script>
  @endpush
@endsection
