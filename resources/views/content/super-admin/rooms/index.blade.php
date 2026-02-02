@extends('layouts/contentNavbarLayout')
@section('title', 'Rooms - Management')

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

    <!-- Filters -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label">Room Type</label>
              <select class="form-select" id="roomTypeFilter">
                <option value="">All Types</option>
                @foreach($roomTypes as $type)
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
          </div>
        </div>
      </div>
    </div>

    <!-- Rooms Grid -->
    @foreach($rooms as $room)
      <div class="col-xl-4 col-md-6">
        <div class="card h-100">
          <div class="card-header p-0 position-relative">
            @if($room->image_path)
              <img src="{{ asset($room->image_path) }}" class="card-img-top" alt="{{ $room->room_type_name }}" style="height: 200px; object-fit: cover;">
            @else
              <div class="bg-label-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="icon-base ri ri-image-line icon-64px text-body-secondary"></i>
              </div>
            @endif
            <div class="position-absolute top-0 end-0 m-3">
              @if($room->status === 'available')
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
                <button class="btn text-body-secondary p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="icon-base ri ri-more-2-line icon-24px"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                  <a class="dropdown-item" href="javascript:void(0);">Edit Room</a>
                  <a class="dropdown-item" href="javascript:void(0);">Change Status</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="javascript:void(0);">Delete Room</a>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-body-secondary">Rate per Night:</span>
                <strong class="text-primary">₱{{ number_format($room->rate_per_night, 2) }}</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-body-secondary">Max Capacity:</span>
                <span>{{ $room->max_pax }} {{ $room->max_pax > 1 ? 'Guests' : 'Guest' }}</span>
              </div>
            </div>

            @if(!empty($room->amenities))
              <div class="mb-3">
                <p class="mb-2 fw-medium">Amenities:</p>
                <div class="d-flex flex-wrap gap-2">
                  @foreach(array_slice($room->amenities, 0, 3) as $amenity)
                    <span class="badge bg-label-info">{{ $amenity }}</span>
                  @endforeach
                  @if(count($room->amenities) > 3)
                    <span class="badge bg-label-secondary">+{{ count($room->amenities) - 3 }} more</span>
                  @endif
                </div>
              </div>
            @endif

            <div class="d-grid gap-2">
              <button class="btn btn-primary btn-sm" type="button">
                <i class="icon-base ri ri-eye-line me-1"></i>
                View Details
              </button>
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
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0 me-2">Room Types Overview</h5>
            <div class="dropdown">
              <button class="btn text-body-secondary p-0" type="button" id="roomTypesDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-base ri ri-more-2-line icon-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="roomTypesDropdown">
                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                <a class="dropdown-item" href="javascript:void(0);">Export</a>
                <a class="dropdown-item" href="javascript:void(0);">Manage Types</a>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th class="text-truncate">Room Type</th>
                  <th class="text-truncate">Description</th>
                  <th class="text-truncate">Rate/Night</th>
                  <th class="text-truncate">Max Capacity</th>
                  <th class="text-truncate">Total Rooms</th>
                  <th class="text-truncate">Available</th>
                </tr>
              </thead>
              <tbody>
                @foreach($roomTypes as $type)
                  @php
                    $typeRooms = $rooms->where('room_type_id', $type->room_type_id);
                    $typeAvailable = $typeRooms->where('status', 'available')->count();
                  @endphp
                  <tr>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <i class="icon-base ri ri-hotel-bed-line icon-22px text-primary me-2"></i>
                        <span class="fw-medium">{{ $type->room_type_name }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">{{ $type->description ?? 'N/A' }}</td>
                    <td class="text-truncate">₱{{ number_format($type->rate_per_night, 2) }}</td>
                    <td class="text-truncate">{{ $type->max_pax }} {{ $type->max_pax > 1 ? 'Guests' : 'Guest' }}</td>
                    <td class="text-truncate">
                      <span class="badge bg-label-primary rounded-pill">{{ $typeRooms->count() }}</span>
                    </td>
                    <td class="text-truncate">
                      <span class="badge bg-label-success rounded-pill">{{ $typeAvailable }}</span>
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
@endsection
