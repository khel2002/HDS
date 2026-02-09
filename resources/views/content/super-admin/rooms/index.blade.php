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
              @if(isset($selectedRoomType) && $selectedRoomType)
                @php
                  $selectedType = $roomTypes->firstWhere('room_type_id', $selectedRoomType);
                @endphp
                @if($selectedType)
                  <div class="mt-2">
                    <span class="badge bg-label-primary">
                      <i class="icon-base ri ri-filter-line me-1"></i>
                      Filtered by: {{ $selectedType->room_type_name }}
                    </span>
                    <a href="{{ route('super_admin.rooms.index') }}" class="badge bg-label-secondary ms-2">
                      <i class="icon-base ri ri-close-line me-1"></i>
                      Clear Filter
                    </a>
                  </div>
                @endif
              @endif
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addRoomModal">
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

    <!-- Filters -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label">Search Room</label>
              <input type="text" class="form-control" id="searchTable" placeholder="Search by room number...">
            </div>
            <div class="col-md-3">
              <label class="form-label">Room Type</label>
              <select class="form-select" id="roomTypeFilter">
                <option value="">All Types</option>
                @foreach($roomTypes as $type)
                  <option value="{{ $type->room_type_id }}" {{ isset($selectedRoomType) && $selectedRoomType == $type->room_type_id ? 'selected' : '' }}>
                    {{ $type->room_type_name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select id="filterStatus" class="form-select">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" onclick="window.location.href='{{ route('super_admin.rooms.index') }}'">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rooms Table -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0 me-2">All Rooms</h5>
            <div class="dropdown">
              <button class="btn text-body-secondary p-0" type="button" id="roomsDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-base ri ri-more-2-line icon-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="roomsDropdown">
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
                  <th class="text-truncate">Room #</th>
                  <th class="text-truncate">Image</th>
                  <th class="text-truncate">Room Type</th>
                  <th class="text-truncate">Status</th>
                  <th class="text-truncate">Rate/Night</th>
                  <th class="text-truncate">Max Capacity</th>
                  <th class="text-truncate">Amenities</th>
                  <th class="text-truncate">Actions</th>
                </tr>
              </thead>
              <tbody id="roomsTableBody">
                @foreach($rooms as $room)
                  <tr data-room-number="{{ strtolower($room->room_number) }}" data-room-type="{{ $room->room_type_id }}" data-status="{{ $room->status }}">
                    <td class="text-truncate">
                      <span class="fw-medium">{{ $room->room_number }}</span>
                    </td>
                    <td class="text-truncate">
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
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <i class="icon-base ri ri-hotel-bed-line icon-20px text-primary me-2"></i>
                        <span>{{ $room->room_type_name }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">
                      @if($room->status === 'available')
                        <span class="badge bg-label-success">Available</span>
                      @elseif($room->status === 'occupied')
                        <span class="badge bg-label-warning">Occupied</span>
                      @else
                        <span class="badge bg-label-danger">Maintenance</span>
                      @endif
                    </td>
                    <td class="text-truncate">
                      <span class="fw-medium text-primary">₱{{ number_format($room->rate_per_night, 2) }}</span>
                    </td>
                    <td class="text-truncate">
                      <span class="badge bg-label-info rounded-pill">{{ $room->max_pax }} {{ $room->max_pax > 1 ? 'Guests' : 'Guest' }}</span>
                    </td>
                    <td class="text-truncate">
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
                    <td class="text-truncate">
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewDetailsModal{{ $room->room_id }}">
                            <i class="icon-base ri ri-eye-line me-2"></i>View Details
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $room->room_id }}">
                            <i class="icon-base ri ri-edit-line me-2"></i>Edit
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#changeStatusModal{{ $room->room_id }}">
                            <i class="icon-base ri ri-refresh-line me-2"></i>Change Status
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete({{ $room->room_id }}, '{{ $room->room_number }}', '{{ $room->status }}')">
                            <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete Room
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

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-add-line me-2"></i>
              Add New Room
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('super_admin.rooms.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
              <div class="row">
                <!-- Left Column - Basic Information -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                  <h6 class="mb-3 text-primary">Basic Information</h6>

                  <div class="mb-3">
                    <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="room_number" name="room_number" required placeholder="e.g., 101" maxlength="10">
                  </div>

                  <div class="mb-3">
                    <label for="room_type_id" class="form-label">Room Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="room_type_id" name="room_type_id" required onchange="updateRoomTypeDescription('add')">
                      <option value="">Select Room Type</option>
                      @foreach($roomTypes as $type)
                        <option value="{{ $type->room_type_id }}"
                                data-description="{{ $type->description ?? 'No description available' }}"
                                data-rate="{{ number_format($type->rate_per_night, 2) }}"
                                data-pax="{{ $type->max_pax }}">
                          {{ $type->room_type_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                      <option value="available" selected>Available</option>
                      <option value="occupied">Occupied</option>
                      <option value="maintenance">Maintenance</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Selected Room Type Info</label>
                    <div class="alert alert-info mb-0 py-2" role="alert">
                      <small id="add_room_type_info">Please select a room type</small>
                    </div>
                  </div>

                  <div class="mb-0">
                    <label for="image" class="form-label">Room Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                  </div>
                </div>

                <!-- Right Column - Amenities & Description -->
                <div class="col-lg-6">
                  <h6 class="mb-3 text-primary">Amenities & Description</h6>

                  <div class="mb-3">
                    <label class="form-label">Amenities (Select to auto-generate description)</label>
                    <div class="border rounded p-3" style="max-height: 280px; overflow-y: auto;">
                      <div class="row">
                        @foreach($amenities as $amenity)
                          <div class="col-12 mb-2">
                            <div class="form-check">
                              <input class="form-check-input amenity-checkbox" type="checkbox" name="amenities[]" value="{{ $amenity->amenity_id }}" id="amenity_{{ $amenity->amenity_id }}" onchange="updateDescription('add')">
                              <label class="form-check-label" for="amenity_{{ $amenity->amenity_id }}">
                                {{ $amenity->amenity_name }}
                              </label>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>

                  <div class="mb-0">
                    <label class="form-label">Auto-Generated Description</label>
                    <div class="alert alert-warning mb-2" role="alert">
                      <small><i class="ri-information-line me-1"></i> <strong>Note:</strong> This description will be saved to the selected room type and will apply to all rooms of that type.</small>
                    </div>
                    <div class="alert alert-secondary mb-0" role="alert">
                      <small id="add_auto_description">No amenities selected yet. Select amenities above to auto-generate a description.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ri ri-add-line me-1"></i>
                Add Room
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Room Modals -->
    @foreach($rooms as $room)
      <div class="modal fade" id="editRoomModal{{ $room->room_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="icon-base ri ri-edit-line me-2"></i>
                Edit Room {{ $room->room_number }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('super_admin.rooms.update', $room->room_id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="modal-body">
                <div class="row">
                  <!-- Left Column - Image and Basic Info -->
                  <div class="col-lg-5 mb-4 mb-lg-0">
                    @if($room->image_path)
                      <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div>
                          <img src="{{ asset('storage/' . $room->image_path) }}" alt="{{ $room->room_number }}" class="rounded img-fluid" style="max-height: 250px; width: 100%; object-fit: cover;">
                        </div>
                      </div>
                    @endif
                    <div class="mb-3">
                      <label for="edit_image_{{ $room->room_id }}" class="form-label">Change Room Image</label>
                      <input type="file" class="form-control" id="edit_image_{{ $room->room_id }}" name="image" accept="image/*">
                      <small class="text-muted">Leave empty to keep current image</small>
                    </div>

                    <div class="mb-3">
                      <label for="edit_room_number_{{ $room->room_id }}" class="form-label">Room Number <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="edit_room_number_{{ $room->room_id }}" name="room_number" value="{{ $room->room_number }}" required maxlength="10">
                    </div>

                    <div class="mb-3">
                      <label for="edit_status_{{ $room->room_id }}" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="edit_status_{{ $room->room_id }}" name="status" required>
                        <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="occupied" {{ $room->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="maintenance" {{ $room->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                      </select>
                    </div>
                  </div>

                  <!-- Right Column - Room Type and Amenities -->
                  <div class="col-lg-7">
                    <div class="mb-3">
                      <label for="edit_room_type_id_{{ $room->room_id }}" class="form-label">Room Type <span class="text-danger">*</span></label>
                      <select class="form-select" id="edit_room_type_id_{{ $room->room_id }}" name="room_type_id" required onchange="updateRoomTypeDescription('edit_{{ $room->room_id }}')">
                        @foreach($roomTypes as $type)
                          <option value="{{ $type->room_type_id }}"
                                  {{ $room->room_type_id == $type->room_type_id ? 'selected' : '' }}
                                  data-description="{{ $type->description ?? 'No description available' }}"
                                  data-rate="{{ number_format($type->rate_per_night, 2) }}"
                                  data-pax="{{ $type->max_pax }}">
                            {{ $type->room_type_name }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Room Type Info</label>
                      <div class="alert alert-info mb-0 py-2" role="alert">
                        <small id="edit_{{ $room->room_id }}_room_type_info">Rate: ₱{{ number_format($room->rate_per_night, 2) }}/night | Max: {{ $room->max_pax }} guest(s)</small>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Amenities (Select to auto-update room type description)</label>
                      <div class="row">
                        @php
                          $roomAmenityIds = DB::table('room_amenities')
                            ->where('room_id', $room->room_id)
                            ->pluck('amenity_id')
                            ->toArray();
                        @endphp
                        @foreach($amenities as $amenity)
                          <div class="col-md-6 mb-2">
                            <div class="form-check">
                              <input class="form-check-input amenity-checkbox-{{ $room->room_id }}"
                                     type="checkbox"
                                     name="amenities[]"
                                     value="{{ $amenity->amenity_id }}"
                                     id="edit_amenity_{{ $room->room_id }}_{{ $amenity->amenity_id }}"
                                     {{ in_array($amenity->amenity_id, $roomAmenityIds) ? 'checked' : '' }}
                                     onchange="updateDescription('edit_{{ $room->room_id }}')">
                              <label class="form-check-label" for="edit_amenity_{{ $room->room_id }}_{{ $amenity->amenity_id }}">
                                {{ $amenity->amenity_name }}
                              </label>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>

                    <div class="mb-0">
                      <label class="form-label">Auto-Generated Description (Will be saved to Room Type)</label>
                      <div class="alert alert-warning" role="alert">
                        <small><i class="ri-information-line me-1"></i> <strong>Note:</strong> This description will update the <strong>{{ $room->room_type_name }}</strong> room type description for all rooms of this type.</small>
                      </div>
                      <div class="alert alert-secondary" role="alert">
                        <small id="edit_{{ $room->room_id }}_auto_description">
                          @if(!empty($room->amenities))
                            This room features: {{ implode(', ', $room->amenities) }}.
                          @else
                            No amenities selected yet.
                          @endif
                        </small>
                      </div>
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

    <!-- Change Status Modals -->
    @foreach($rooms as $room)
      <div class="modal fade" id="changeStatusModal{{ $room->room_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="icon-base ri ri-refresh-line me-2"></i>
                Change Room Status
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('super_admin.rooms.update-status', $room->room_id) }}" method="POST">
              @csrf
              @method('PATCH')
              <div class="modal-body">
                <p class="mb-3">Change status for <strong>Room {{ $room->room_number }}</strong></p>
                <div class="mb-3">
                  <label for="change_status_{{ $room->room_id }}" class="form-label">New Status <span class="text-danger">*</span></label>
                  <select class="form-select" id="change_status_{{ $room->room_id }}" name="status" required>
                    <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ $room->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="maintenance" {{ $room->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                  <i class="icon-base ri ri-check-line me-1"></i>
                  Update Status
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endforeach

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
                    </div>
                  </div>

                  <!-- Description -->
                  @if($room->description)
                    <div class="mb-4">
                      <h6 class="mb-2">Room Type Description</h6>
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
                  @endif
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              <div class="btn-group">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changeStatusModal{{ $room->room_id }}">
                  <i class="icon-base ri ri-refresh-line me-1"></i>
                  Change Status
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $room->room_id }}">
                  <i class="icon-base ri ri-edit-line me-1"></i>
                  Edit Room
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach

    @if(count($rooms) === 0)
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="icon-base ri ri-hotel-bed-line icon-64px text-body-secondary mb-4"></i>
            <h5 class="mb-2">No Rooms Found</h5>
            <p class="mb-4 text-body-secondary">There are no rooms available in the system.</p>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addRoomModal">
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
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Rooms Overview</h5>
          <a href="{{ route('super_admin.room-types.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="icon-base ri ri-settings-3-line me-1"></i>
            Manage Types
          </a>
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
                @foreach($roomTypes as $type)
                  @php
                    $typeRooms = collect($rooms)->where('room_type_id', $type->room_type_id);
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

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/roomjs/index_script.js') }}"></script>
@endsection
