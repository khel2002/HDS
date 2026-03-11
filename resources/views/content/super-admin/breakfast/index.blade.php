@extends('layouts/contentNavbarLayout')
@section('title', 'Breakfast Menu - Management')

@section('content')
  @if(session('success'))
    <span data-success-message="{{ session('success') }}" style="display:none;"></span>
  @endif
  @if(session('error'))
    <span data-error-message="{{ session('error') }}" style="display:none;"></span>
  @endif

  <div class="row gy-6">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Breakfast Menu Management</h4>
              <p class="mb-0">Manage all breakfast menu items offered to guests</p>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addBreakfastModal">
              <i class="icon-base ri ri-add-line me-1"></i> Add New Item
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Stats ───────────────────────────────────────────────────────── --}}
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar"><div class="avatar-initial bg-primary rounded shadow-xs"><i class="icon-base ri ri-restaurant-line icon-24px"></i></div></div>
            <div class="ms-3"><p class="mb-0">Total Items</p><h5 class="mb-0" data-stat="total">{{ $stats['total_items'] }}</h5></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar"><div class="avatar-initial bg-success rounded shadow-xs"><i class="icon-base ri ri-checkbox-circle-line icon-24px"></i></div></div>
            <div class="ms-3"><p class="mb-0">Available</p><h5 class="mb-0" data-stat="available">{{ $stats['available_items'] }}</h5></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar"><div class="avatar-initial bg-danger rounded shadow-xs"><i class="icon-base ri ri-close-circle-line icon-24px"></i></div></div>
            <div class="ms-3"><p class="mb-0">Unavailable</p><h5 class="mb-0" data-stat="unavailable">{{ $stats['unavailable_items'] }}</h5></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar"><div class="avatar-initial bg-info rounded shadow-xs"><i class="icon-base ri ri-money-dollar-circle-line icon-24px"></i></div></div>
            <div class="ms-3"><p class="mb-0">Avg. Price</p><h5 class="mb-0" data-stat="avg_price">₱{{ number_format($stats['avg_price'], 2) }}</h5></div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── View toggle + Filters ────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Search Item</label>
              <input type="text" class="form-control" id="searchTable" placeholder="Search by meal name...">
            </div>
            <div class="col-md-3">
              <label class="form-label">Availability</label>
              <select class="form-select" id="filterAvailability">
                <option value="">All</option>
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" id="resetFilters">
                <i class="icon-base ri ri-refresh-line me-1"></i> Reset Filters
              </button>
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <div class="btn-group d-flex" role="group">
                <button type="button" class="btn btn-outline-primary active" id="viewTable" data-bs-toggle="tooltip" title="Table View">
                  <i class="ri ri-list-check"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="viewCards" data-bs-toggle="tooltip" title="Card View">
                  <i class="ri ri-layout-grid-line"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── TABLE VIEW ───────────────────────────────────────────────────── --}}
    <div class="col-12" id="tableView">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title m-0">All Breakfast Items</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Image</th>
                  <th>Meal Name</th>
                  <th>Description</th>
                  <th>Price</th>
                  <th>Availability</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="breakfastTableBody">
                @foreach($breakfastItems as $item)
                  <tr data-id="{{ $item->breakfast_id }}"
                      data-meal-name="{{ strtolower($item->meal_name) }}"
                      data-available="{{ $item->is_available ? '1' : '0' }}">
                    <td><span class="row-num">{{ $loop->iteration }}</span></td>
                    <td>
                      @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}"
                             alt="{{ $item->meal_name }}"
                             class="rounded"
                             style="width:60px;height:45px;object-fit:cover;">
                      @else
                        <div class="bg-label-secondary rounded d-flex align-items-center justify-content-center"
                             style="width:60px;height:45px;">
                          <i class="ri ri-image-line text-body-secondary"></i>
                        </div>
                      @endif
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="icon-base ri ri-bowl-line icon-20px text-primary me-2"></i>
                        <span class="fw-medium">{{ $item->meal_name }}</span>
                      </div>
                    </td>
                    <td>
                      @if($item->description)
                        <span class="text-truncate d-inline-block" style="max-width:260px;"
                              data-bs-toggle="tooltip" data-bs-placement="top"
                              title="{{ $item->description }}">
                          {{ $item->description }}
                        </span>
                      @else
                        <span class="text-body-secondary">—</span>
                      @endif
                    </td>
                    <td><span class="fw-medium text-primary">₱{{ number_format($item->price, 2) }}</span></td>
                    <td>
                      {{-- Toggle switch --}}
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input availability-toggle"
                               type="checkbox"
                               role="switch"
                               data-id="{{ $item->breakfast_id }}"
                               data-name="{{ addslashes($item->meal_name) }}"
                               {{ $item->is_available ? 'checked' : '' }}>
                      </div>
                    </td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                          <i class="icon-base ri ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="javascript:void(0);"
                             data-bs-toggle="modal"
                             data-bs-target="#viewBreakfastModal{{ $item->breakfast_id }}">
                            <i class="icon-base ri ri-eye-line me-2"></i>View Details
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);"
                             data-bs-toggle="modal"
                             data-bs-target="#editBreakfastModal{{ $item->breakfast_id }}">
                            <i class="icon-base ri ri-edit-line me-2"></i>Edit
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="javascript:void(0);"
                             onclick="confirmDelete({{ $item->breakfast_id }}, '{{ addslashes($item->meal_name) }}')">
                            <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
                          </a>
                        </div>
                      </div>
                      <input type="hidden" id="toggleUrl{{ $item->breakfast_id }}"
                             value="{{ route('super_admin.breakfast.menu.toggle', $item->breakfast_id) }}">
                      <input type="hidden" id="deleteUrl{{ $item->breakfast_id }}"
                             value="{{ route('super_admin.breakfast.menu.destroy', $item->breakfast_id) }}">
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- ── CARD VIEW ────────────────────────────────────────────────────── --}}
    <div class="col-12" id="cardView" style="display:none;">
      <div class="row g-4" id="breakfastCardBody">
        @foreach($breakfastItems as $item)
          @php $available = $item->is_available; @endphp
          <div class="col-xl-3 col-md-4 col-sm-6 breakfast-card"
               data-id="{{ $item->breakfast_id }}"
               data-meal-name="{{ strtolower($item->meal_name) }}"
               data-available="{{ $available ? '1' : '0' }}">
            <div class="card h-100 {{ $available ? '' : 'opacity-65' }}">
              {{-- Image --}}
              @if($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}"
                     class="card-img-top"
                     alt="{{ $item->meal_name }}"
                     style="height:180px;object-fit:cover;">
              @else
                <div class="bg-label-secondary d-flex align-items-center justify-content-center"
                     style="height:180px;">
                  <i class="ri ri-image-line icon-48px text-body-secondary"></i>
                </div>
              @endif

              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="card-title mb-0 fw-semibold">{{ $item->meal_name }}</h6>
                  <span class="badge {{ $available ? 'bg-label-success' : 'bg-label-danger' }} ms-2 flex-shrink-0">
                    {{ $available ? 'Available' : 'Unavailable' }}
                  </span>
                </div>

                @if($item->description)
                  <p class="text-body-secondary small mb-2" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    {{ $item->description }}
                  </p>
                @endif

                <div class="mt-auto">
                  <p class="fw-bold text-primary mb-3">₱{{ number_format($item->price, 2) }}</p>

                  {{-- Toggle switch --}}
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input availability-toggle"
                             type="checkbox"
                             role="switch"
                             data-id="{{ $item->breakfast_id }}"
                             data-name="{{ addslashes($item->meal_name) }}"
                             {{ $available ? 'checked' : '' }}>
                      <label class="form-check-label small">
                        {{ $available ? 'Available' : 'Unavailable' }}
                      </label>
                    </div>
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                        <i class="ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);"
                           data-bs-toggle="modal"
                           data-bs-target="#viewBreakfastModal{{ $item->breakfast_id }}">
                          <i class="icon-base ri ri-eye-line me-2"></i>View Details
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);"
                           data-bs-toggle="modal"
                           data-bs-target="#editBreakfastModal{{ $item->breakfast_id }}">
                          <i class="icon-base ri ri-edit-line me-2"></i>Edit
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                           onclick="confirmDelete({{ $item->breakfast_id }}, '{{ addslashes($item->meal_name) }}')">
                          <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Empty state --}}
    <div class="col-12" id="emptyState" @if(count($breakfastItems) > 0) style="display:none;" @endif>
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="icon-base ri ri-restaurant-line icon-64px text-body-secondary mb-4"></i>
          <h5 class="mb-2">No Breakfast Items Found</h5>
          <p class="mb-4 text-body-secondary">There are no items in the breakfast menu yet.</p>
          <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addBreakfastModal">
            <i class="icon-base ri ri-add-line me-1"></i> Add Your First Item
          </button>
        </div>
      </div>
    </div>

  </div>{{-- end .row --}}


  {{-- ================================================================== --}}
  {{-- ADD MODAL                                                           --}}
  {{-- ================================================================== --}}
  <div class="modal fade" id="addBreakfastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base ri ri-add-line me-2"></i>Add New Breakfast Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="addBreakfastForm"
              data-store-url="{{ route('super_admin.breakfast.menu.store') }}"
              enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row g-4">

              {{-- Image upload with preview --}}
              <div class="col-12">
                <label class="form-label">Item Image</label>
                <div class="d-flex align-items-center gap-3">
                  <div id="add_img_preview"
                       class="rounded border d-flex align-items-center justify-content-center bg-label-secondary flex-shrink-0"
                       style="width:100px;height:75px;overflow:hidden;">
                    <i class="ri ri-image-line icon-32px text-body-secondary"></i>
                  </div>
                  <div class="flex-grow-1">
                    <input type="file" class="form-control" id="add_image" name="image"
                           accept="image/jpg,image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-muted">JPG, PNG, GIF, WEBP · Max 2 MB</small>
                  </div>
                </div>
              </div>

              <div class="col-md-8">
                <label for="add_meal_name" class="form-label">Meal Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_meal_name" name="meal_name"
                       required maxlength="150" placeholder="e.g., Classic Filipino Breakfast">
              </div>
              <div class="col-md-4">
                <label for="add_price" class="form-label">Price (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">₱</span>
                  <input type="number" class="form-control" id="add_price" name="price"
                         required min="0" step="0.01" placeholder="0.00">
                </div>
              </div>
              <div class="col-12">
                <label for="add_description" class="form-label">Description</label>
                <textarea class="form-control" id="add_description" name="description"
                          rows="3" maxlength="500"
                          placeholder="Briefly describe this meal (optional)..."></textarea>
                <small class="text-muted">Max 500 characters</small>
              </div>
              <div class="col-12">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="add_is_available"
                         name="is_available" value="1" checked>
                  <label class="form-check-label" for="add_is_available">Available for ordering</label>
                </div>
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ri ri-add-line me-1"></i>Add Item
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ================================================================== --}}
  {{-- VIEW DETAILS + EDIT MODALS  (one pair per item)                    --}}
  {{-- ================================================================== --}}
  @foreach($breakfastItems as $item)

    {{-- VIEW DETAILS --}}
    <div class="modal fade" id="viewBreakfastModal{{ $item->breakfast_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-bowl-line me-2"></i>{{ $item->meal_name }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="viewBody{{ $item->breakfast_id }}">
            <div class="row g-4">
              <div class="col-md-5">
                @if($item->image_path)
                  <img src="{{ asset('storage/' . $item->image_path) }}"
                       class="img-fluid rounded w-100"
                       style="max-height:280px;object-fit:cover;"
                       alt="{{ $item->meal_name }}">
                @else
                  <div class="bg-label-secondary rounded d-flex align-items-center justify-content-center"
                       style="height:220px;">
                    <div class="text-center">
                      <i class="ri ri-image-line icon-48px text-body-secondary"></i>
                      <p class="text-body-secondary mb-0 mt-2 small">No Image</p>
                    </div>
                  </div>
                @endif
              </div>
              <div class="col-md-7 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="mb-0">{{ $item->meal_name }}</h4>
                    @if($item->is_available)
                      <span class="badge bg-success">Available</span>
                    @else
                      <span class="badge bg-danger">Unavailable</span>
                    @endif
                  </div>

                  <h3 class="text-primary mb-3">₱{{ number_format($item->price, 2) }}</h3>

                  @if($item->description)
                    <p class="text-body-secondary mb-0">{{ $item->description }}</p>
                  @else
                    <p class="text-body-secondary fst-italic mb-0">No description provided.</p>
                  @endif
                </div>

                <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input availability-toggle"
                           type="checkbox"
                           role="switch"
                           data-id="{{ $item->breakfast_id }}"
                           data-name="{{ addslashes($item->meal_name) }}"
                           {{ $item->is_available ? 'checked' : '' }}>
                    <label class="form-check-label">
                      {{ $item->is_available ? 'Available' : 'Unavailable' }}
                    </label>
                  </div>
                  <button type="button" class="btn btn-primary btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#editBreakfastModal{{ $item->breakfast_id }}">
                    <i class="ri ri-edit-line me-1"></i>Edit
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    {{-- EDIT --}}
    <div class="modal fade" id="editBreakfastModal{{ $item->breakfast_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="icon-base ri ri-edit-line me-2"></i>Edit: {{ $item->meal_name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form class="edit-breakfast-form"
                data-id="{{ $item->breakfast_id }}"
                data-update-url="{{ route('super_admin.breakfast.menu.update', $item->breakfast_id) }}"
                enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
              <div class="row g-4">

                {{-- Current image + upload --}}
                <div class="col-12">
                  <label class="form-label">Item Image</label>
                  <div class="d-flex align-items-center gap-3">
                    <div id="edit_img_preview_{{ $item->breakfast_id }}"
                         class="rounded border d-flex align-items-center justify-content-center bg-label-secondary flex-shrink-0"
                         style="width:100px;height:75px;overflow:hidden;">
                      @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}"
                             style="width:100%;height:100%;object-fit:cover;"
                             alt="{{ $item->meal_name }}">
                      @else
                        <i class="ri ri-image-line icon-32px text-body-secondary"></i>
                      @endif
                    </div>
                    <div class="flex-grow-1">
                      <input type="file" class="form-control"
                             id="edit_image_{{ $item->breakfast_id }}"
                             name="image"
                             accept="image/jpg,image/jpeg,image/png,image/gif,image/webp">
                      <small class="text-muted">Leave empty to keep current image · Max 2 MB</small>
                      @if($item->image_path)
                        <div class="form-check mt-2">
                          <input class="form-check-input" type="checkbox"
                                 id="remove_image_{{ $item->breakfast_id }}"
                                 name="remove_image" value="1">
                          <label class="form-check-label text-danger small"
                                 for="remove_image_{{ $item->breakfast_id }}">
                            Remove current image
                          </label>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="col-md-8">
                  <label class="form-label">Meal Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="meal_name"
                         required maxlength="150" value="{{ $item->meal_name }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="price"
                           required min="0" step="0.01" value="{{ $item->price }}">
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description"
                            rows="3" maxlength="500">{{ $item->description }}</textarea>
                  <small class="text-muted">Max 500 characters</small>
                </div>
                <div class="col-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox"
                           id="edit_is_available_{{ $item->breakfast_id }}"
                           name="is_available" value="1"
                           {{ $item->is_available ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_is_available_{{ $item->breakfast_id }}">
                      Available for ordering
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ri ri-save-line me-1"></i>Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  @endforeach

  <style>
    .opacity-65 { opacity: .65; }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/breakfastjs/index_script.js') }}"></script>
@endsection