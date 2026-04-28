@extends('layouts/contentNavbarLayout')
@section('title', 'Breakfast Orders')
{{-- Staff View: order status management only (no menu access) --}}

@section('content')
  @if(session('success'))
    <span data-success-message="{{ session('success') }}" style="display:none;"></span>
  @endif
  @if(session('error'))
    <span data-error-message="{{ session('error') }}" style="display:none;"></span>
  @endif

  <style>.swal-over-modal { z-index: 9999 !important; }</style>

  <div class="row gy-6">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h4 class="mb-1">Breakfast Orders</h4>
              <p class="mb-0 text-body-secondary">Review and process breakfast requests from guests</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              {{-- Quick-filter pills --}}
              <button class="btn btn-sm btn-outline-warning quick-filter-btn" data-status="pending">
                <i class="ri ri-time-line me-1"></i>Pending
                <span class="badge bg-warning text-dark ms-1" id="pill-pending">{{ $stats['pending'] }}</span>
              </button>
              <button class="btn btn-sm btn-outline-info quick-filter-btn" data-status="in_progress">
                <i class="ri ri-loader-4-line me-1"></i>Preparing
                <span class="badge bg-info ms-1" id="pill-in_progress">{{ $stats['in_progress'] }}</span>
              </button>
              <button class="btn btn-sm btn-outline-primary quick-filter-btn" data-status="delivering">
                <i class="ri ri-e-bike-2-line me-1"></i>Delivering
                <span class="badge bg-primary ms-1" id="pill-delivering">{{ $stats['delivering'] }}</span>
              </button>
              <button class="btn btn-sm btn-outline-secondary" id="refreshOrders">
                <i class="ri ri-refresh-line me-1"></i>Refresh
              </button>
            </div>
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
              <div class="avatar-initial bg-warning rounded shadow-xs">
                <i class="icon-base ri ri-time-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Pending</p>
              <h5 class="mb-0" data-stat="pending">{{ $stats['pending'] }}</h5>
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
                <i class="icon-base ri ri-loader-4-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Preparing</p>
              <h5 class="mb-0" data-stat="in_progress">{{ $stats['in_progress'] }}</h5>
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
              <div class="avatar-initial bg-primary rounded shadow-xs">
                <i class="icon-base ri ri-e-bike-2-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Out for Delivery</p>
              <h5 class="mb-0" data-stat="delivering">{{ $stats['delivering'] }}</h5>
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
              <p class="mb-0">Delivered</p>
              <h5 class="mb-0" data-stat="completed">{{ $stats['completed'] }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Search Guest / Room</label>
              <input type="text" class="form-control" id="searchOrders"
                     placeholder="Search by guest name or room number...">
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">Preparing</option>
                <option value="delivering">Out for Delivery</option>
                <option value="completed">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date</label>
              <input type="date" class="form-control" id="filterDate">
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" id="resetOrderFilters">
                <i class="icon-base ri ri-refresh-line me-1"></i>Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Orders Table ─────────────────────────────────────────────────── --}}
    <div class="col-12" id="ordersTableWrap">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title m-0">All Breakfast Orders</h5>
          <small class="text-body-secondary" id="orderCount">{{ $orders->total() }} order(s)</small>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Guest</th>
                  <th>Room</th>
                  <th>Items Ordered</th>
                  <th>Total</th>
                  <th>Requested At</th>
                  <th>Status</th>
                  <th class="text-center pe-4">Actions</th>
                </tr>
              </thead>
              <tbody id="ordersTableBody">
                @forelse($orders as $order)
                  @php
                    $statusMap = [
                      'pending'     => ['label' => 'Pending',          'badge' => 'bg-label-warning', 'icon' => 'ri-time-line'],
                      'in_progress' => ['label' => 'Being Prepared',   'badge' => 'bg-label-info',    'icon' => 'ri-loader-4-line'],
                      'delivering'  => ['label' => 'Out for Delivery', 'badge' => 'bg-label-primary', 'icon' => 'ri-e-bike-2-line'],
                      'completed'   => ['label' => 'Delivered',        'badge' => 'bg-label-success', 'icon' => 'ri-checkbox-circle-line'],
                      'cancelled'   => ['label' => 'Cancelled',        'badge' => 'bg-label-danger',  'icon' => 'ri-close-circle-line'],
                    ];
                    $s     = $statusMap[$order->request_status] ?? $statusMap['pending'];
                    $guest = $order->registration->guestDetails ?? null;
                    $room  = $order->registration->reservation->room ?? null;
                    $total = $order->breakfastOrders->sum(fn($i) => $i->price_at_order * $i->quantity);
                  @endphp
                  <tr class="order-row"
                      data-id="{{ $order->service_request_id }}"
                      data-status="{{ $order->request_status }}"
                      data-status-url="{{ route('staff.breakfast.orders.status', $order->service_request_id) }}"
                      data-guest="{{ strtolower(($guest->first_name ?? '') . ' ' . ($guest->last_name ?? '')) }}"
                      data-room="{{ strtolower($room->room_number ?? '') }}"
                      data-date="{{ \Carbon\Carbon::parse($order->requested_at)->toDateString() }}">

                    <td class="ps-4">
                      <span class="fw-medium text-body-secondary">#{{ $order->service_request_id }}</span>
                    </td>
                    <td>
                      @if($guest)
                        <div class="d-flex align-items-center gap-2">
                          <div class="avatar avatar-sm">
                            <div class="avatar-initial bg-label-primary rounded-circle">
                              {{ strtoupper(substr($guest->first_name, 0, 1)) }}{{ strtoupper(substr($guest->last_name, 0, 1)) }}
                            </div>
                          </div>
                          <div>
                            <span class="fw-medium d-block">{{ $guest->first_name }} {{ $guest->last_name }}</span>
                            @if($guest->contact_number)
                              <small class="text-body-secondary">{{ $guest->contact_number }}</small>
                            @endif
                          </div>
                        </div>
                      @else
                        <span class="text-body-secondary">—</span>
                      @endif
                    </td>
                    <td>
                      @if($room)
                        <span class="badge bg-label-secondary">
                          <i class="ri ri-door-line me-1"></i>{{ $room->room_number }}
                        </span>
                      @else
                        <span class="text-body-secondary">—</span>
                      @endif
                    </td>
                    <td>
                      <div class="d-flex flex-column gap-1">
                        @foreach($order->breakfastOrders as $lineItem)
                          <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-label-primary rounded-pill">{{ $lineItem->quantity }}×</span>
                            <span class="small">{{ $lineItem->breakfastMenu->meal_name ?? 'Unknown Item' }}</span>
                          </div>
                        @endforeach
                      </div>
                    </td>
                    <td>
                      <span class="fw-semibold text-primary">₱{{ number_format($total, 2) }}</span>
                    </td>
                    <td>
                      <span class="d-block">{{ \Carbon\Carbon::parse($order->requested_at)->format('M d, Y') }}</span>
                      <small class="text-body-secondary">{{ \Carbon\Carbon::parse($order->requested_at)->format('h:i A') }}</small>
                    </td>
                    <td>
                      <span class="badge {{ $s['badge'] }}">
                        <i class="ri {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}
                      </span>
                    </td>
                    <td class="text-center pe-4">
                      <div class="d-flex align-items-center justify-content-center gap-1">

                        {{-- View Details --}}
                        <button type="button"
                                class="btn btn-sm btn-icon btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#viewOrderModal{{ $order->service_request_id }}"
                                title="View Details">
                          <i class="ri ri-eye-line"></i>
                        </button>

                        {{-- Status-driven action buttons (staff only) --}}
                        @if($order->request_status === 'pending')
                          <button type="button"
                                  class="btn btn-sm btn-info order-status-btn"
                                  data-id="{{ $order->service_request_id }}"
                                  data-new-status="in_progress"
                                  data-url="{{ route('staff.breakfast.orders.status', $order->service_request_id) }}"
                                  title="Start Preparing">
                            <i class="ri ri-loader-4-line me-1"></i>Prepare
                          </button>
                          <button type="button"
                                  class="btn btn-sm btn-outline-danger order-status-btn"
                                  data-id="{{ $order->service_request_id }}"
                                  data-new-status="cancelled"
                                  data-url="{{ route('staff.breakfast.orders.status', $order->service_request_id) }}"
                                  title="Cancel Order">
                            <i class="ri ri-close-line me-1"></i>Cancel
                          </button>

                        @elseif($order->request_status === 'in_progress')
                          <button type="button"
                                  class="btn btn-sm btn-primary order-status-btn"
                                  data-id="{{ $order->service_request_id }}"
                                  data-new-status="delivering"
                                  data-url="{{ route('staff.breakfast.orders.status', $order->service_request_id) }}"
                                  title="Mark as Out for Delivery">
                            <i class="ri ri-e-bike-2-line me-1"></i>Deliver
                          </button>

                        @elseif($order->request_status === 'delivering')
                          <button type="button"
                                  class="btn btn-sm btn-success order-status-btn"
                                  data-id="{{ $order->service_request_id }}"
                                  data-new-status="completed"
                                  data-url="{{ route('staff.breakfast.orders.status', $order->service_request_id) }}"
                                  title="Mark as Delivered">
                            <i class="ri ri-checkbox-circle-line me-1"></i>Delivered
                          </button>

                        @elseif($order->request_status === 'completed')
                          <span class="text-success small"><i class="ri ri-check-double-line me-1"></i>Done</span>

                        @elseif($order->request_status === 'cancelled')
                          <span class="text-danger small"><i class="ri ri-close-circle-line me-1"></i>Cancelled</span>
                        @endif

                      </div>
                    </td>
                  </tr>
                @empty
                  <tr id="noOrdersRow">
                    <td colspan="8" class="text-center py-5">
                      <i class="ri ri-inbox-line icon-48px text-body-secondary mb-3 d-block"></i>
                      <p class="mb-0 text-body-secondary">No breakfast orders found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
          <div class="card-footer">
            {{ $orders->links() }}
          </div>
        @endif
      </div>
    </div>

    {{-- Empty state when filters yield nothing --}}
    <div class="col-12" id="filterEmptyState" style="display:none;">
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="ri ri-search-line icon-48px text-body-secondary mb-3"></i>
          <h6 class="mb-1">No orders match your filters</h6>
          <p class="text-body-secondary mb-3">Try adjusting your search or filter criteria.</p>
          <button class="btn btn-outline-secondary btn-sm" id="resetOrderFilters2">
            <i class="ri ri-refresh-line me-1"></i>Reset Filters
          </button>
        </div>
      </div>
    </div>

  </div>{{-- end .row --}}


  {{-- ================================================================== --}}
  {{-- VIEW ORDER DETAIL MODALS                                            --}}
  {{-- ================================================================== --}}
  @foreach($orders as $order)
    @php
      $statusMap = [
        'pending'     => ['label' => 'Pending',          'badge' => 'bg-label-warning', 'icon' => 'ri-time-line'],
        'in_progress' => ['label' => 'Being Prepared',   'badge' => 'bg-label-info',    'icon' => 'ri-loader-4-line'],
        'delivering'  => ['label' => 'Out for Delivery', 'badge' => 'bg-label-primary', 'icon' => 'ri-e-bike-2-line'],
        'completed'   => ['label' => 'Delivered',        'badge' => 'bg-label-success', 'icon' => 'ri-checkbox-circle-line'],
        'cancelled'   => ['label' => 'Cancelled',        'badge' => 'bg-label-danger',  'icon' => 'ri-close-circle-line'],
      ];
      $s     = $statusMap[$order->request_status] ?? $statusMap['pending'];
      $guest = $order->registration->guestDetails ?? null;
      $room  = $order->registration->reservation->room ?? null;
      $total = $order->breakfastOrders->sum(fn($i) => $i->price_at_order * $i->quantity);
    @endphp

    <div class="modal fade" id="viewOrderModal{{ $order->service_request_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-bowl-line me-2"></i>
              Order #{{ $order->service_request_id }} — Details
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="row g-4">

              {{-- Guest & Room Info --}}
              <div class="col-md-6">
                <div class="card border h-100 mb-0">
                  <div class="card-body">
                    <h6 class="card-title mb-3 text-body-secondary text-uppercase small fw-semibold">
                      <i class="ri ri-user-line me-1"></i>Guest Info
                    </h6>
                    @if($guest)
                      <p class="mb-1 fw-semibold">{{ $guest->first_name }} {{ $guest->last_name }}</p>
                      @if($guest->contact_number)
                        <p class="mb-1 small text-body-secondary">
                          <i class="ri ri-phone-line me-1"></i>{{ $guest->contact_number }}
                        </p>
                      @endif
                      @if($guest->dob)
                        <p class="mb-0 small text-body-secondary">
                          <i class="ri ri-cake-line me-1"></i>{{ \Carbon\Carbon::parse($guest->dob)->format('M d, Y') }}
                        </p>
                      @endif
                    @else
                      <p class="text-body-secondary mb-0">No guest info available.</p>
                    @endif
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border h-100 mb-0">
                  <div class="card-body">
                    <h6 class="card-title mb-3 text-body-secondary text-uppercase small fw-semibold">
                      <i class="ri ri-hotel-line me-1"></i>Booking Info
                    </h6>
                    @if($room)
                      <p class="mb-1">
                        <span class="fw-semibold">Room:</span>
                        <span class="badge bg-label-secondary ms-1">{{ $room->room_number }}</span>
                      </p>
                      @if($room->roomType ?? null)
                        <p class="mb-1 small text-body-secondary">{{ $room->roomType->room_type_name }}</p>
                      @endif
                    @endif
                    <p class="mb-1 small">
                      <span class="fw-medium">Check-in:</span>
                      {{ \Carbon\Carbon::parse($order->registration->reservation->check_in_date ?? now())->format('M d, Y') }}
                    </p>
                    <p class="mb-0 small">
                      <span class="fw-medium">Check-out:</span>
                      {{ \Carbon\Carbon::parse($order->registration->reservation->check_out_date ?? now())->format('M d, Y') }}
                    </p>
                  </div>
                </div>
              </div>

              {{-- Order Items --}}
              <div class="col-12">
                <div class="card border mb-0">
                  <div class="card-body">
                    <h6 class="card-title mb-3 text-body-secondary text-uppercase small fw-semibold">
                      <i class="ri ri-restaurant-line me-1"></i>Order Items
                    </h6>
                    <div class="table-responsive">
                      <table class="table table-sm mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Subtotal</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($order->breakfastOrders as $lineItem)
                            <tr>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  @if($lineItem->breakfastMenu->image_path ?? null)
                                    <img src="{{ asset('storage/' . $lineItem->breakfastMenu->image_path) }}"
                                         class="rounded" style="width:36px;height:28px;object-fit:cover;"
                                         alt="{{ $lineItem->breakfastMenu->meal_name }}">
                                  @else
                                    <div class="bg-label-secondary rounded d-flex align-items-center justify-content-center"
                                         style="width:36px;height:28px;">
                                      <i class="ri ri-bowl-line text-body-secondary" style="font-size:12px;"></i>
                                    </div>
                                  @endif
                                  <span class="fw-medium">{{ $lineItem->breakfastMenu->meal_name ?? 'Deleted Item' }}</span>
                                </div>
                              </td>
                              <td class="text-center">{{ $lineItem->quantity }}</td>
                              <td class="text-end">₱{{ number_format($lineItem->price_at_order, 2) }}</td>
                              <td class="text-end fw-medium">₱{{ number_format($lineItem->price_at_order * $lineItem->quantity, 2) }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                        <tfoot class="table-light">
                          <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold text-primary">₱{{ number_format($total, 2) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Status Timeline --}}
              <div class="col-12">
                <div class="modal-timeline">
                  @php
                    $steps = [
                      'pending'     => ['label' => 'Pending',    'icon' => 'ri-time-line'],
                      'in_progress' => ['label' => 'Preparing',  'icon' => 'ri-loader-4-line'],
                      'delivering'  => ['label' => 'Delivering', 'icon' => 'ri-e-bike-2-line'],
                      'completed'   => ['label' => 'Delivered',  'icon' => 'ri-checkbox-circle-line'],
                    ];
                    $stepKeys     = array_keys($steps);
                    $lookupStatus = $order->request_status === 'cancelled' ? 'pending' : $order->request_status;
                    $currentIdx   = array_search($lookupStatus, $stepKeys);
                    if ($currentIdx === false) $currentIdx = 0;
                  @endphp
                  <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                    @foreach($steps as $key => $step)
                      @php
                        $idx       = array_search($key, $stepKeys);
                        $isDone    = $currentIdx > $idx;
                        $isActive  = $currentIdx === $idx;
                        $dotClass  = $isDone   ? 'bg-success text-white'
                                   : ($isActive ? 'bg-primary text-white'
                                               : 'bg-label-secondary text-body-secondary');
                        $labelClass = $isDone   ? 'text-success fw-semibold'
                                    : ($isActive ? 'text-primary fw-semibold'
                                                : 'text-body-secondary');
                        $iconClass  = $isDone ? 'ri-check-line' : $step['icon'];
                      @endphp
                      <div class="d-flex flex-column align-items-center" style="flex:1;min-width:0;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 {{ $dotClass }}"
                             style="width:36px;height:36px;flex-shrink:0;">
                          <i class="ri {{ $iconClass }}" style="font-size:16px;"></i>
                        </div>
                        <small class="{{ $labelClass }}" style="font-size:11px;text-align:center;white-space:nowrap;">{{ $step['label'] }}</small>
                      </div>
                      @if(!$loop->last)
                        <div style="flex:1;height:2px;background:{{ $isDone ? '#28a745' : '#dee2e6' }};margin-bottom:20px;flex-shrink:1;"></div>
                      @endif
                    @endforeach
                  </div>
                  @if($order->request_status === 'cancelled')
                    <div class="text-center mt-1 mb-2">
                      <span class="badge bg-label-danger px-3 py-2">
                        <i class="ri ri-close-circle-line me-1"></i>This order was cancelled
                      </span>
                    </div>
                  @endif
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light">
                  <div>
                    <small class="text-body-secondary d-block">Requested At</small>
                    <span class="fw-medium">{{ \Carbon\Carbon::parse($order->requested_at)->format('M d, Y h:i A') }}</span>
                  </div>
                  <div class="text-center">
                    <small class="text-body-secondary d-block">Status</small>
                    <span class="badge modal-status-badge {{ $s['badge'] }} fs-6">
                      <i class="ri {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}
                    </span>
                  </div>
                  @if($order->completed_at)
                    <div class="text-end">
                      <small class="text-body-secondary d-block">Delivered At</small>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($order->completed_at)->format('M d, Y h:i A') }}</span>
                    </div>
                  @endif
                </div>
              </div>

              {{-- Guest Notes --}}
              @if($order->description)
                <div class="col-12">
                  <label class="form-label text-body-secondary small fw-semibold text-uppercase">
                    <i class="ri ri-chat-3-line me-1"></i>Notes from Guest
                  </label>
                  <div class="p-3 rounded border bg-light">{{ $order->description }}</div>
                </div>
              @endif

            </div>
          </div>

          {{-- Modal Footer --}}
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <div class="d-flex gap-2 modal-footer-actions">
              {{-- Populated by staff_orders_script.js on show.bs.modal --}}
            </div>
          </div>

        </div>
      </div>
    </div>
  @endforeach

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/breakfastjs/staff_orders_script.js') }}"></script>
@endsection