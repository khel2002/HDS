@extends('layouts/contentNavbarLayout')
@section('title', 'Room Types - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('content')
  <div class="row gy-6">
    <!-- Page Header -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Room Type Management</h4>
              <p class="mb-0">Manage and configure room type categories</p>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add New Room Type
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Room Type Statistics -->
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-primary rounded shadow-xs">
                <i class="icon-base ri ri-layout-grid-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Total Types</p>
              <h5 class="mb-0">{{ $stats['total_types'] ?? 0 }}</h5>
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
                <i class="icon-base ri ri-hotel-bed-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Active Rooms</p>
              <h5 class="mb-0">{{ $stats['total_rooms'] ?? 0 }}</h5>
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
                <i class="icon-base ri ri-price-tag-3-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Avg Rate/Night</p>
              <h5 class="mb-0">₱{{ number_format($stats['avg_rate'] ?? 0, 2) }}</h5>
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
                <i class="icon-base ri ri-user-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Avg Capacity</p>
              <h5 class="mb-0">{{ number_format($stats['avg_capacity'] ?? 0, 1) }}</h5>
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
            <div class="col-md-4">
              <label class="form-label">Search Room Type</label>
              <input type="text" class="form-control" id="searchRoomType" placeholder="Search by name...">
            </div>
            <div class="col-md-4">
              <label class="form-label">Sort By</label>
              <select class="form-select" id="sortFilter">
                <option value="name">Name (A-Z)</option>
                <option value="rate_asc">Rate (Low to High)</option>
                <option value="rate_desc">Rate (High to Low)</option>
                <option value="capacity">Capacity</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" onclick="window.location.reload()">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Room Types Table -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0 me-2">Detailed Room Types Overview</h5>
            <div class="dropdown">
              <button class="btn text-body-secondary p-0" type="button" id="roomTypesDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-base ri ri-more-2-line icon-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="roomTypesDropdown">
                <a class="dropdown-item" href="javascript:void(0);" onclick="window.location.reload()">Refresh</a>
                <a class="dropdown-item" href="javascript:void(0);">Export to Excel</a>
                <a class="dropdown-item" href="javascript:void(0);">Export to PDF</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0);">Print</a>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th class="text-truncate">Room Type</th>
                  <th class="text-truncate">Description</th>
                  <th class="text-truncate">Rate/Night</th>
                  <th class="text-truncate">Max Capacity</th>
                  <th class="text-truncate">Total</th>
                  <th class="text-truncate">Available</th>
                  <th class="text-truncate">Occupied</th>
                  <th class="text-truncate">Maintenance</th>
                  <th class="text-truncate">Actions</th>
                </tr>
              </thead>
              <tbody id="roomTypesTableBody">
                @foreach($roomTypes as $type)
                  @php
                    $typeRooms = $rooms->where('room_type_id', $type->room_type_id);
                    $typeAvailable = $typeRooms->where('status', 'available')->count();
                    $typeOccupied = $typeRooms->where('status', 'occupied')->count();
                    $typeMaintenance = $typeRooms->where('status', 'maintenance')->count();
                  @endphp
                  <tr data-room-type-name="{{ strtolower($type->room_type_name) }}" data-rate="{{ $type->rate_per_night }}" data-capacity="{{ $type->max_pax }}">
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <i class="icon-base ri ri-hotel-bed-line icon-22px text-primary me-2"></i>
                        <span class="fw-medium">{{ $type->room_type_name }}</span>
                      </div>
                    </td>
                    <td class="text-truncate" style="max-width: 200px;" title="{{ $type->description ?? 'N/A' }}">
                      {{ $type->description ?? 'N/A' }}
                    </td>
                    <td class="text-truncate">
                      <span class="fw-medium text-primary">₱{{ number_format($type->rate_per_night, 2) }}</span>
                    </td>
                    <td class="text-truncate">{{ $type->max_pax }} {{ $type->max_pax > 1 ? 'Guests' : 'Guest' }}</td>
                    <td class="text-truncate">
                      <span class="badge bg-label-primary rounded-pill">{{ $typeRooms->count() }}</span>
                    </td>
                    <td class="text-truncate">
                      <span class="badge bg-label-success rounded-pill">{{ $typeAvailable }}</span>
                    </td>
                    <td class="text-truncate">
                      <span class="badge bg-label-warning rounded-pill">{{ $typeOccupied }}</span>
                    </td>
                    <td class="text-truncate">
                      <span class="badge bg-label-danger rounded-pill">{{ $typeMaintenance }}</span>
                    </td>
                    <td class="text-truncate">
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewRoomTypeModal{{ $type->room_type_id }}">
                            <i class="icon-base ri ri-eye-line me-2"></i>View Details
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editRoomTypeModal{{ $type->room_type_id }}">
                            <i class="icon-base ri ri-edit-line me-2"></i>Edit
                          </a>
                          <a class="dropdown-item" href="{{ route('super_admin.rooms.index', ['room_type' => $type->room_type_id]) }}">
                            <i class="icon-base ri ri-list-check me-2"></i>View Rooms
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete({{ $type->room_type_id }}, '{{ $type->room_type_name }}', {{ $typeRooms->count() }})">
                            <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
                          </a>
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
    </div>
  </div>

  <!-- Add Room Type Modal -->
  <div class="modal fade" id="addRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="icon-base ri ri-add-line me-2"></i>
            Add New Room Type
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('super_admin.room-types.store') }}" method="POST" id="addRoomTypeForm">
          @csrf
          <div class="modal-body">
            <div class="row">
              <!-- Left Column -->
              <div class="col-lg-6 mb-4 mb-lg-0">
                <h6 class="mb-3 text-primary">Basic Information</h6>

                <div class="mb-3">
                  <label class="form-label" for="room_type_name">Room Type Name <span class="text-danger">*</span></label>
                  <input type="text" id="room_type_name" name="room_type_name" class="form-control" placeholder="e.g., Deluxe Suite" required>
                </div>

                <div class="mb-0">
                  <label class="form-label" for="description">Description</label>
                  <textarea id="description" name="description" class="form-control" rows="8" placeholder="Describe the room type features and amenities..."></textarea>
                  <small class="text-muted">Provide a detailed description of this room type</small>
                </div>
              </div>

              <!-- Right Column -->
              <div class="col-lg-6">
                <h6 class="mb-3 text-primary">Pricing & Capacity</h6>

                <div class="mb-3">
                  <label class="form-label" for="rate_per_night">Rate per Night (₱) <span class="text-danger">*</span></label>
                  <input type="number" id="rate_per_night" name="rate_per_night" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                  <small class="text-muted">Enter the nightly rate for this room type</small>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="max_pax">Maximum Capacity <span class="text-danger">*</span></label>
                  <input type="number" id="max_pax" name="max_pax" class="form-control" placeholder="2" min="1" required>
                  <small class="text-muted">Maximum number of guests allowed</small>
                </div>

                <div class="alert alert-info" role="alert">
                  <h6 class="alert-heading mb-2">
                    <i class="icon-base ri ri-information-line me-1"></i>
                    Important Notes
                  </h6>
                  <ul class="mb-0 ps-3">
                    <li>Room type name should be unique and descriptive</li>
                    <li>Rate can be updated later if needed</li>
                    <li>Capacity determines booking limits</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ri ri-add-line me-1"></i>
              Add Room Type
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Room Type Modals -->
  @foreach($roomTypes as $type)
    <div class="modal fade" id="editRoomTypeModal{{ $type->room_type_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-edit-line me-2"></i>
              Edit Room Type: {{ $type->room_type_name }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('super_admin.room-types.update', $type->room_type_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <div class="row">
                <!-- Left Column -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                  <h6 class="mb-3 text-primary">Basic Information</h6>

                  <div class="mb-3">
                    <label class="form-label" for="edit_room_type_name_{{ $type->room_type_id }}">Room Type Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_room_type_name_{{ $type->room_type_id }}" name="room_type_name" class="form-control" value="{{ $type->room_type_name }}" required>
                  </div>

                  <div class="mb-0">
                    <label class="form-label" for="edit_description_{{ $type->room_type_id }}">Description</label>
                    <textarea id="edit_description_{{ $type->room_type_id }}" name="description" class="form-control" rows="8">{{ $type->description }}</textarea>
                    <small class="text-muted">Provide a detailed description of this room type</small>
                  </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-6">
                  <h6 class="mb-3 text-primary">Pricing & Capacity</h6>

                  <div class="mb-3">
                    <label class="form-label" for="edit_rate_per_night_{{ $type->room_type_id }}">Rate per Night (₱) <span class="text-danger">*</span></label>
                    <input type="number" id="edit_rate_per_night_{{ $type->room_type_id }}" name="rate_per_night" class="form-control" value="{{ $type->rate_per_night }}" step="0.01" min="0" required>
                    <small class="text-muted">Current rate for this room type</small>
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="edit_max_pax_{{ $type->room_type_id }}">Maximum Capacity <span class="text-danger">*</span></label>
                    <input type="number" id="edit_max_pax_{{ $type->room_type_id }}" name="max_pax" class="form-control" value="{{ $type->max_pax }}" min="1" required>
                    <small class="text-muted">Maximum number of guests allowed</small>
                  </div>

                  <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading mb-2">
                      <i class="icon-base ri ri-alert-line me-1"></i>
                      Update Warning
                    </h6>
                    <p class="mb-0 small">Changes to rate and capacity will affect all rooms of this type.</p>
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

  <!-- View Room Type Details Modals -->
  @foreach($roomTypes as $type)
    @php
      $typeRooms = $rooms->where('room_type_id', $type->room_type_id);
      $typeAvailable = $typeRooms->where('status', 'available')->count();
      $typeOccupied = $typeRooms->where('status', 'occupied')->count();
      $typeMaintenance = $typeRooms->where('status', 'maintenance')->count();
    @endphp
    <div class="modal fade" id="viewRoomTypeModal{{ $type->room_type_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-hotel-bed-line me-2"></i>
              Room Type Details: {{ $type->room_type_name }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <!-- Left Column - Details -->
              <div class="col-lg-6 mb-4 mb-lg-0">
                <h6 class="mb-3 text-primary">Room Type Information</h6>

                <div class="mb-3">
                  <label class="form-label fw-medium">Room Type Name</label>
                  <p class="text-body-secondary">{{ $type->room_type_name }}</p>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-medium">Description</label>
                  <p class="text-body-secondary">{{ $type->description ?? 'No description available' }}</p>
                </div>

                <div class="row mb-3">
                  <div class="col-6">
                    <label class="form-label fw-medium">Rate per Night</label>
                    <p class="text-primary fw-bold fs-5">₱{{ number_format($type->rate_per_night, 2) }}</p>
                  </div>
                  <div class="col-6">
                    <label class="form-label fw-medium">Max Capacity</label>
                    <p class="text-body-secondary fw-medium fs-5">{{ $type->max_pax }} {{ $type->max_pax > 1 ? 'Guests' : 'Guest' }}</p>
                  </div>
                </div>
              </div>

              <!-- Right Column - Statistics -->
              <div class="col-lg-6">
                <h6 class="mb-3 text-primary">Room Statistics</h6>

                <div class="card bg-label-primary mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-primary">
                          <i class="icon-base ri ri-hotel-bed-line icon-24px"></i>
                        </span>
                      </div>
                      <div>
                        <p class="mb-0">Total Rooms</p>
                        <h4 class="mb-0">{{ $typeRooms->count() }}</h4>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-4">
                    <div class="card bg-label-success">
                      <div class="card-body text-center p-3">
                        <i class="icon-base ri ri-checkbox-circle-line icon-24px text-success mb-2"></i>
                        <h5 class="mb-0">{{ $typeAvailable }}</h5>
                        <small>Available</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="card bg-label-warning">
                      <div class="card-body text-center p-3">
                        <i class="icon-base ri ri-user-location-line icon-24px text-warning mb-2"></i>
                        <h5 class="mb-0">{{ $typeOccupied }}</h5>
                        <small>Occupied</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="card bg-label-danger">
                      <div class="card-body text-center p-3">
                        <i class="icon-base ri ri-tools-line icon-24px text-danger mb-2"></i>
                        <h5 class="mb-0">{{ $typeMaintenance }}</h5>
                        <small>Maintenance</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <div class="btn-group">
              <a href="{{ route('super_admin.rooms.index', ['room_type' => $type->room_type_id]) }}" class="btn btn-outline-primary">
                <i class="icon-base ri ri-list-check me-1"></i>
                View Rooms
              </a>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editRoomTypeModal{{ $type->room_type_id }}">
                <i class="icon-base ri ri-edit-line me-1"></i>
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/roomtypejs/index_script.js') }}"></script>
@endsection
