@extends('layouts/contentNavbarLayout')
@section('title', 'Breakfast Menu')
{{-- Staff View: can toggle availability only — no add / edit / delete --}}

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
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h4 class="mb-1">Breakfast Menu</h4>
              <p class="mb-0 text-body-secondary">Browse all breakfast items and toggle their availability</p>
            </div>
            <a href="{{ route('staff.breakfast.orders.index') }}" class="btn btn-primary">
              <i class="ri ri-list-check me-1"></i> View Orders
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Stats ───────────────────────────────────────────────────────── --}}
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-primary rounded shadow-xs">
                <i class="icon-base ri ri-restaurant-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Total Items</p>
              <h5 class="mb-0" data-stat="total">{{ $stats['total_items'] }}</h5>
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
              <h5 class="mb-0" data-stat="available">{{ $stats['available_items'] }}</h5>
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
                <i class="icon-base ri ri-close-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Unavailable</p>
              <h5 class="mb-0" data-stat="unavailable">{{ $stats['unavailable_items'] }}</h5>
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
              <p class="mb-0">Avg. Price</p>
              <h5 class="mb-0" data-stat="avg_price">₱{{ number_format($stats['avg_price'], 2) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Filters + View Toggle ────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Search Item</label>
              <input type="text" class="form-control" id="searchTable"
                     placeholder="Search by meal name...">
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
                <button type="button" class="btn btn-outline-primary active" id="viewTable"
                        data-bs-toggle="tooltip" title="Table View">
                  <i class="ri ri-list-check"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="viewCards"
                        data-bs-toggle="tooltip" title="Card View">
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
                  <th>Details</th>
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

                    <td>
                      <span class="fw-medium text-primary">₱{{ number_format($item->price, 2) }}</span>
                    </td>

                    <td>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input availability-toggle"
                               type="checkbox"
                               role="switch"
                               data-id="{{ $item->breakfast_id }}"
                               data-name="{{ addslashes($item->meal_name) }}"
                               data-toggle-url="{{ route('staff.breakfast.menu.toggle', $item->breakfast_id) }}"
                               {{ $item->is_available ? 'checked' : '' }}>
                      </div>
                    </td>

                    <td>
                      <button type="button"
                              class="btn btn-sm btn-icon btn-outline-secondary"
                              data-bs-toggle="modal"
                              data-bs-target="#viewBreakfastModal{{ $item->breakfast_id }}"
                              title="View Details">
                        <i class="ri ri-eye-line"></i>
                      </button>
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
                  <p class="text-body-secondary small mb-2"
                     style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    {{ $item->description }}
                  </p>
                @endif

                <div class="mt-auto">
                  <p class="fw-bold text-primary mb-3">₱{{ number_format($item->price, 2) }}</p>

                  <div class="d-flex align-items-center justify-content-between">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input availability-toggle"
                             type="checkbox"
                             role="switch"
                             data-id="{{ $item->breakfast_id }}"
                             data-name="{{ addslashes($item->meal_name) }}"
                             data-toggle-url="{{ route('staff.breakfast.menu.toggle', $item->breakfast_id) }}"
                             {{ $available ? 'checked' : '' }}>
                      <label class="form-check-label small">
                        {{ $available ? 'Available' : 'Unavailable' }}
                      </label>
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-icon btn-outline-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#viewBreakfastModal{{ $item->breakfast_id }}"
                            title="View Details">
                      <i class="ri ri-eye-line"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- ── Empty state ──────────────────────────────────────────────────── --}}
    <div class="col-12" id="emptyState"
         @if(count($breakfastItems) > 0) style="display:none;" @endif>
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="icon-base ri ri-restaurant-line icon-64px text-body-secondary mb-4"></i>
          <h5 class="mb-2">No Breakfast Items Found</h5>
          <p class="mb-0 text-body-secondary">The breakfast menu has no items yet. Contact a super admin to add items.</p>
        </div>
      </div>
    </div>

  </div>{{-- end .row --}}


  {{-- ================================================================== --}}
  {{-- VIEW DETAILS MODALS  (one per item, read-only)                     --}}
  {{-- ================================================================== --}}
  @foreach($breakfastItems as $item)
    <div class="modal fade" id="viewBreakfastModal{{ $item->breakfast_id }}"
         tabindex="-1" aria-hidden="true">
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
                    <span class="item-badge badge {{ $item->is_available ? 'bg-success' : 'bg-danger' }}">
                      {{ $item->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                  </div>

                  <h3 class="text-primary mb-3">₱{{ number_format($item->price, 2) }}</h3>

                  @if($item->description)
                    <p class="text-body-secondary mb-0">{{ $item->description }}</p>
                  @else
                    <p class="text-body-secondary fst-italic mb-0">No description provided.</p>
                  @endif
                </div>

                {{-- Toggle inside modal --}}
                <div class="mt-4 pt-3 border-top">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input availability-toggle"
                           type="checkbox"
                           role="switch"
                           data-id="{{ $item->breakfast_id }}"
                           data-name="{{ addslashes($item->meal_name) }}"
                           data-toggle-url="{{ route('staff.breakfast.menu.toggle', $item->breakfast_id) }}"
                           {{ $item->is_available ? 'checked' : '' }}>
                    <label class="form-check-label">
                      Mark as available for ordering
                    </label>
                  </div>
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
  @endforeach

  <style>
    .opacity-65 { opacity: .65; }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/breakfastjs/staff_menu_script.js') }}"></script>
@endsection