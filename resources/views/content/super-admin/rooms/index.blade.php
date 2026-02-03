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

    <!-- Rooms Table -->
    <div class="col-12">
      <div class="card">
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
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit" data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $room->room_id }}">
                        <i class="icon-base ri ri-edit-line"></i>
                      </button>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#changeStatusModal{{ $room->room_id }}">
                            <i class="icon-base ri ri-refresh-line me-2"></i>
                            Change Status
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete({{ $room->room_id }}, '{{ $room->room_number }}', '{{ $room->status }}')">
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

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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
                <div class="col-md-6 mb-3">
                  <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="room_number" name="room_number" required placeholder="e.g., 101" maxlength="10">
                </div>
                <div class="col-md-6 mb-3">
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
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                  <select class="form-select" id="status" name="status" required>
                    <option value="available" selected>Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Selected Room Type Info</label>
                  <div class="alert alert-info mb-0 py-2" role="alert">
                    <small id="add_room_type_info">Please select a room type</small>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <label for="image" class="form-label">Room Image</label>
                  <input type="file" class="form-control" id="image" name="image" accept="image/*">
                  <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">Amenities (Select to auto-generate description)</label>
                  <div class="row">
                    @foreach($amenities as $amenity)
                      <div class="col-md-6 mb-2">
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
                <div class="col-12 mb-3">
                  <label class="form-label">Auto-Generated Description (Will be saved to Room Type)</label>
                  <div class="alert alert-warning mb-2" role="alert">
                    <small><i class="ri-information-line me-1"></i> <strong>Note:</strong> This description will be saved to the selected room type and will apply to all rooms of that type.</small>
                  </div>
                  <div class="alert alert-secondary" role="alert">
                    <small id="add_auto_description">No amenities selected yet. Select amenities above to auto-generate a description.</small>
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

  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    console.log('Room management script loaded');

    // Update room type description and info - GLOBAL FUNCTION
    window.updateRoomTypeDescription = function(prefix) {
      const select = document.getElementById(prefix + '_room_type_id') || document.getElementById('room_type_id');
      const selectedOption = select.options[select.selectedIndex];
      const infoElement = document.getElementById(prefix + '_room_type_info');

      if (selectedOption.value) {
        const description = selectedOption.getAttribute('data-description');
        const rate = selectedOption.getAttribute('data-rate');
        const pax = selectedOption.getAttribute('data-pax');

        if (infoElement) {
          infoElement.textContent = `Rate: ₱${rate}/night | Max: ${pax} guest(s)`;
        }
      } else {
        if (infoElement) {
          infoElement.textContent = 'Please select a room type';
        }
      }
    }

    // Update description based on selected amenities - GLOBAL FUNCTION
    window.updateDescription = function(prefix) {
      let checkboxes;

      if (prefix === 'add') {
        checkboxes = document.querySelectorAll('.amenity-checkbox:checked');
      } else {
        checkboxes = document.querySelectorAll('.amenity-checkbox-' + prefix.replace('edit_', '') + ':checked');
      }

      const descElement = document.getElementById(prefix + '_auto_description');

      if (checkboxes.length > 0) {
        const amenities = Array.from(checkboxes).map(cb => {
          const label = document.querySelector('label[for="' + cb.id + '"]');
          return label ? label.textContent.trim() : '';
        }).filter(name => name !== '');

        descElement.textContent = `This room features: ${amenities.join(', ')}.`;
      } else {
        descElement.textContent = 'No amenities selected yet. Select amenities above to auto-generate a description.';
      }
    }

    // Delete confirmation with SweetAlert2 - GLOBAL FUNCTION
    window.confirmDelete = function(roomId, roomNumber, status) {
      console.log('confirmDelete called:', roomId, roomNumber, status);
      if (status === 'occupied') {
        Swal.fire({
          title: 'Cannot Delete!',
          text: `Room ${roomNumber} is currently occupied. Please change the status first.`,
          icon: 'error',
          confirmButtonColor: '#696cff',
          confirmButtonText: 'OK'
        });
        return;
      }

      Swal.fire({
        title: 'Delete Room?',
        html: `Are you sure you want to delete <strong>Room ${roomNumber}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Create and submit delete form
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/super-admin/rooms/${roomId}`;

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
      });
    }

    // DOM Ready event listeners
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Table search and filter
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

      // Initialize description for edit modals on page load
      @foreach($rooms as $room)
        updateDescription('edit_{{ $room->room_id }}');
      @endforeach

      // Add SweetAlert2 confirmation to all form submissions
      const forms = document.querySelectorAll('form[method="POST"]');
      forms.forEach(form => {
        // Skip delete forms (they have their own handler)
        if (!form.querySelector('input[name="_method"][value="DELETE"]')) {
          form.addEventListener('submit', function(e) {
            // Only show confirmation for update and status change forms
            if (form.querySelector('input[name="_method"][value="PUT"]') ||
                form.querySelector('input[name="_method"][value="PATCH"]')) {
              e.preventDefault();

              const isStatusChange = form.querySelector('input[name="_method"][value="PATCH"]');

              Swal.fire({
                title: isStatusChange ? 'Update Room Status?' : 'Save Changes?',
                text: isStatusChange ? 'Are you sure you want to change the room status?' : 'Are you sure you want to save these changes?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
              }).then((result) => {
                if (result.isConfirmed) {
                  form.submit();
                }
              });
            }
          });
        }
      });
    });
  </script>
  @endpush
@endsection
