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
              <button class="btn btn-outline-secondary d-block w-100" type="button">
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
                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
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
              <tbody>
                @foreach($roomTypes as $type)
                  @php
                    $typeRooms = $rooms->where('room_type_id', $type->room_type_id);
                    $typeAvailable = $typeRooms->where('status', 'available')->count();
                    $typeOccupied = $typeRooms->where('status', 'occupied')->count();
                    $typeMaintenance = $typeRooms->where('status', 'maintenance')->count();
                  @endphp
                  <tr>
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
                          <a class="dropdown-item" href="javascript:void(0);">
                            <i class="icon-base ri ri-eye-line me-2"></i>View Details
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);">
                            <i class="icon-base ri ri-edit-line me-2"></i>Edit
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);">
                            <i class="icon-base ri ri-list-check me-2"></i>View Rooms
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="javascript:void(0);">
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
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Room Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addRoomTypeForm">
            <div class="row g-4">
              <div class="col-12">
                <label class="form-label" for="roomTypeName">Room Type Name</label>
                <input type="text" id="roomTypeName" class="form-control" placeholder="e.g., Deluxe Suite" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" class="form-control" rows="3" placeholder="Describe the room type..."></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="ratePerNight">Rate per Night (₱)</label>
                <input type="number" id="ratePerNight" class="form-control" placeholder="0.00" step="0.01" min="0" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="maxPax">Maximum Capacity</label>
                <input type="number" id="maxPax" class="form-control" placeholder="2" min="1" required>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">
            <i class="icon-base ri ri-add-line me-1"></i>
            Add Room Type
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection
