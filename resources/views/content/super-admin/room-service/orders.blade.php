@extends('layouts/contentNavbarLayout')
@section('title', 'Room Service Requests')
{{-- Super Admin --}}
@section('content')
  @if(session('success'))
    <span data-success-message="{{ session('success') }}" style="display:none;"></span>
  @endif
  @if(session('error'))
    <span data-error-message="{{ session('error') }}" style="display:none;"></span>
  @endif

  <style>.swal-over-modal { z-index: 9999 !important; }</style>

  @php
    $categoryIcons  = ['housekeeping'=>'ri-brush-line','toiletries'=>'ri-flask-line','technical'=>'ri-tools-line','comfort'=>'ri-sofa-line'];
    $categoryBadges = ['housekeeping'=>'bg-label-info','toiletries'=>'bg-label-success','technical'=>'bg-label-warning','comfort'=>'bg-label-primary'];
    $statusMap = [
      'pending'     => ['label'=>'Pending',     'badge'=>'bg-label-warning', 'icon'=>'ri-time-line'],
      'in_progress' => ['label'=>'In Progress', 'badge'=>'bg-label-info',    'icon'=>'ri-loader-4-line'],
      'completed'   => ['label'=>'Completed',   'badge'=>'bg-label-success', 'icon'=>'ri-checkbox-circle-line'],
      'cancelled'   => ['label'=>'Cancelled',   'badge'=>'bg-label-danger',  'icon'=>'ri-close-circle-line'],
    ];
  @endphp

  <div class="row gy-6">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Room Service Requests</h4>
              <p class="mb-0 text-body-secondary">Review and manage all room service requests from guests</p>
            </div>
            <button class="btn btn-outline-secondary" id="refreshOrders" type="button">
              <i class="icon-base ri ri-refresh-line me-1"></i> Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Stats ────────────────────────────────────────────────────────── --}}
    <div class="col-xl-4 col-md-4">
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
    <div class="col-xl-4 col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-info rounded shadow-xs">
                <i class="icon-base ri ri-loader-4-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">In Progress</p>
              <h5 class="mb-0" data-stat="in_progress">{{ $stats['in_progress'] }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-success rounded shadow-xs">
                <i class="icon-base ri ri-checkbox-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Completed</p>
              <h5 class="mb-0" data-stat="completed">{{ $stats['completed'] }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-4 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Search Guest / Room</label>
              <input type="text" class="form-control" id="searchOrders" placeholder="Search by guest name or room...">
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Category</label>
              <select class="form-select" id="filterCategory">
                <option value="">All Categories</option>
                <option value="housekeeping">Housekeeping</option>
                <option value="toiletries">Toiletries</option>
                <option value="technical">Technical</option>
                <option value="comfort">Comfort</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" id="resetOrderFilters">
                <i class="icon-base ri ri-refresh-line me-1"></i> Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Requests Table ───────────────────────────────────────────────── --}}
    <div class="col-12" id="ordersTableWrap">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title m-0">All Room Service Requests</h5>
          <small class="text-body-secondary" id="orderCount">{{ $requests->total() }} request(s)</small>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Guest</th>
                  <th>Room</th>
                  <th>Services Requested</th>
                  <th>Requested At</th>
                  <th>Status</th>
                  <th class="text-center pe-4">Actions</th>
                </tr>
              </thead>
              <tbody id="ordersTableBody">
                @forelse($requests as $req)
                  @php
                    $s     = $statusMap[$req->request_status] ?? $statusMap['pending'];
                    $guest = $req->registration->guestDetails ?? null;
                    $room  = $req->registration->reservation->room ?? null;
                    $cats  = $req->roomOrderItems->pluck('roomServiceItem.category')->filter()->unique()->values();
                  @endphp
                  <tr class="order-row"
                      data-id="{{ $req->service_request_id }}"
                      data-status="{{ $req->request_status }}"
                      data-status-url="{{ route('super_admin.room-service.requests.status', $req->service_request_id) }}"
                      data-guest="{{ strtolower(($guest->first_name ?? '') . ' ' . ($guest->last_name ?? '')) }}"
                      data-room="{{ strtolower($room->room_number ?? '') }}"
                      data-category="{{ $cats->implode(',') }}">

                    <td class="ps-4">
                      <span class="fw-medium text-body-secondary">#{{ $req->service_request_id }}</span>
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
                        @foreach($req->roomOrderItems->take(3) as $orderItem)
                          @php $item = $orderItem->roomServiceItem; @endphp
                          <div class="d-flex align-items-center gap-1">
                            <span class="badge {{ $categoryBadges[$item->category ?? ''] ?? 'bg-label-secondary' }} rounded-pill" style="font-size:.65rem;">
                              <i class="ri {{ $categoryIcons[$item->category ?? ''] ?? 'ri-service-line' }} me-1"></i>{{ ucfirst($item->category ?? '—') }}
                            </span>
                            <span class="small">{{ $item->item_name ?? 'Unknown' }}</span>
                            @if($item->requires_quantity && $orderItem->quantity > 1)
                              <span class="text-body-secondary small">×{{ $orderItem->quantity }}</span>
                            @endif
                          </div>
                        @endforeach
                        @if($req->roomOrderItems->count() > 3)
                          <small class="text-body-secondary">+{{ $req->roomOrderItems->count() - 3 }} more</small>
                        @endif
                      </div>
                    </td>
                    <td>
                      <span class="d-block">{{ \Carbon\Carbon::parse($req->requested_at)->format('M d, Y') }}</span>
                      <small class="text-body-secondary">{{ \Carbon\Carbon::parse($req->requested_at)->format('h:i A') }}</small>
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
                                data-bs-target="#viewReqModal{{ $req->service_request_id }}"
                                title="View Details">
                          <i class="ri ri-eye-line"></i>
                        </button>

                        {{-- Status-driven action buttons --}}
                        @if($req->request_status === 'pending')
                          <button type="button"
                                  class="btn btn-sm btn-info order-status-btn"
                                  data-id="{{ $req->service_request_id }}"
                                  data-new-status="in_progress"
                                  data-url="{{ route('super_admin.room-service.requests.status', $req->service_request_id) }}"
                                  title="Mark as In Progress">
                            <i class="ri ri-loader-4-line me-1"></i>Process
                          </button>
                          <button type="button"
                                  class="btn btn-sm btn-outline-danger order-status-btn"
                                  data-id="{{ $req->service_request_id }}"
                                  data-new-status="cancelled"
                                  data-url="{{ route('super_admin.room-service.requests.status', $req->service_request_id) }}"
                                  title="Cancel Request">
                            <i class="ri ri-close-line me-1"></i>Cancel
                          </button>

                        @elseif($req->request_status === 'in_progress')
                          <button type="button"
                                  class="btn btn-sm btn-success order-status-btn"
                                  data-id="{{ $req->service_request_id }}"
                                  data-new-status="completed"
                                  data-url="{{ route('super_admin.room-service.requests.status', $req->service_request_id) }}"
                                  title="Mark as Completed">
                            <i class="ri ri-checkbox-circle-line me-1"></i>Complete
                          </button>

                        @elseif($req->request_status === 'completed')
                          <span class="text-success small"><i class="ri ri-check-double-line me-1"></i>Done</span>

                        @elseif($req->request_status === 'cancelled')
                          <span class="text-danger small"><i class="ri ri-close-circle-line me-1"></i>Cancelled</span>
                        @endif

                      </div>
                    </td>
                  </tr>
                @empty
                  <tr id="noOrdersRow">
                    <td colspan="7" class="text-center py-5">
                      <i class="ri ri-concierge-bell-line icon-48px text-body-secondary mb-3 d-block"></i>
                      <p class="mb-0 text-body-secondary">No room service requests found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- Pagination --}}
        @if($requests->hasPages())
          <div class="card-footer">
            {{ $requests->links() }}
          </div>
        @endif
      </div>
    </div>

    {{-- Empty state when filters yield nothing --}}
    <div class="col-12" id="filterEmptyState" style="display:none;">
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="ri ri-search-line icon-48px text-body-secondary mb-3"></i>
          <h6 class="mb-1">No requests match your filters</h6>
          <p class="text-body-secondary mb-3">Try adjusting your search or filter criteria.</p>
          <button class="btn btn-outline-secondary btn-sm" id="resetOrderFilters2">
            <i class="ri ri-refresh-line me-1"></i>Reset Filters
          </button>
        </div>
      </div>
    </div>

  </div>{{-- end .row --}}


  {{-- ================================================================== --}}
  {{-- REQUEST DETAIL MODALS                                               --}}
  {{-- ================================================================== --}}
  @foreach($requests as $req)
    @php
      $s     = $statusMap[$req->request_status] ?? $statusMap['pending'];
      $guest = $req->registration->guestDetails ?? null;
      $room  = $req->registration->reservation->room ?? null;

      // Timeline: 3-step (pending → in_progress → completed)
      $steps      = ['pending', 'in_progress', 'completed'];
      $stepDefs   = [
        'pending'     => ['label'=>'Pending',     'icon'=>'ri-time-line'],
        'in_progress' => ['label'=>'In Progress', 'icon'=>'ri-loader-4-line'],
        'completed'   => ['label'=>'Completed',   'icon'=>'ri-checkbox-circle-line'],
      ];
      $lookupStatus = $req->request_status === 'cancelled' ? 'pending' : $req->request_status;
      $currentIdx   = array_search($lookupStatus, $steps);
      if ($currentIdx === false) $currentIdx = 0;
    @endphp

    <div class="modal fade" id="viewReqModal{{ $req->service_request_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">
              <i class="icon-base ri ri-concierge-bell-line me-2"></i>
              Request #{{ $req->service_request_id }} — Details
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
                        <span class="badge bg-label-secondary ms-1">
                          <i class="ri ri-door-line me-1"></i>{{ $room->room_number }}
                        </span>
                      </p>
                      @if($room->roomType ?? null)
                        <p class="mb-1 small text-body-secondary">{{ $room->roomType->room_type_name }}</p>
                      @endif
                    @endif
                    <p class="mb-1 small">
                      <span class="fw-medium">Check-in:</span>
                      {{ \Carbon\Carbon::parse($req->registration->reservation->check_in_date ?? now())->format('M d, Y') }}
                    </p>
                    <p class="mb-0 small">
                      <span class="fw-medium">Check-out:</span>
                      {{ \Carbon\Carbon::parse($req->registration->reservation->check_out_date ?? now())->format('M d, Y') }}
                    </p>
                  </div>
                </div>
              </div>

              {{-- Services List --}}
              <div class="col-12">
                <div class="card border mb-0">
                  <div class="card-body">
                    <h6 class="card-title mb-3 text-body-secondary text-uppercase small fw-semibold">
                      <i class="ri ri-list-check me-1"></i>Requested Services
                    </h6>
                    <div class="table-responsive">
                      <table class="table table-sm mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Service</th>
                            <th>Category</th>
                            <th class="text-center">Qty</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($req->roomOrderItems as $orderItem)
                            @php $item = $orderItem->roomServiceItem; @endphp
                            <tr>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <div class="avatar avatar-xs">
                                    <div class="avatar-initial bg-label-secondary rounded">
                                      <i class="ri {{ $item->icon ?? 'ri-service-line' }}" style="font-size:.75rem;"></i>
                                    </div>
                                  </div>
                                  <span class="fw-medium small">{{ $item->item_name ?? 'Unknown Service' }}</span>
                                </div>
                              </td>
                              <td>
                                <span class="badge {{ $categoryBadges[$item->category ?? ''] ?? 'bg-label-secondary' }}" style="font-size:.65rem;">
                                  <i class="ri {{ $categoryIcons[$item->category ?? ''] ?? 'ri-service-line' }} me-1"></i>
                                  {{ ucfirst($item->category ?? '—') }}
                                </span>
                              </td>
                              <td class="text-center">
                                @if($item->requires_quantity ?? true)
                                  <span class="fw-semibold">{{ $orderItem->quantity }}</span>
                                @else
                                  <span class="badge bg-label-secondary" style="font-size:.65rem;">Report</span>
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

              {{-- Status Timeline --}}
              <div class="col-12">
                <div class="modal-timeline">
                  <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                    @foreach($steps as $idx => $stepKey)
                      @php
                        $isDone   = $currentIdx > $idx;
                        $isActive = $currentIdx === $idx;
                        $dotClass  = $isDone   ? 'bg-success text-white'
                                   : ($isActive ? 'bg-primary text-white'
                                               : 'bg-label-secondary text-body-secondary');
                        $lblClass  = $isDone   ? 'text-success fw-semibold'
                                   : ($isActive ? 'text-primary fw-semibold'
                                               : 'text-body-secondary');
                        $ico       = $isDone ? 'ri-check-line' : $stepDefs[$stepKey]['icon'];
                      @endphp
                      <div class="d-flex flex-column align-items-center" style="flex:1;min-width:0;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 {{ $dotClass }}"
                             style="width:36px;height:36px;flex-shrink:0;">
                          <i class="ri {{ $ico }}" style="font-size:16px;"></i>
                        </div>
                        <small class="{{ $lblClass }}" style="font-size:11px;text-align:center;white-space:nowrap;">
                          {{ $stepDefs[$stepKey]['label'] }}
                        </small>
                      </div>
                      @if(!$loop->last)
                        <div style="flex:1;height:2px;background:{{ $isDone ? '#28a745' : '#dee2e6' }};margin-bottom:20px;flex-shrink:1;"></div>
                      @endif
                    @endforeach
                  </div>

                  @if($req->request_status === 'cancelled')
                    <div class="text-center mt-1 mb-2">
                      <span class="badge bg-label-danger px-3 py-2">
                        <i class="ri ri-close-circle-line me-1"></i>This request was cancelled
                      </span>
                    </div>
                  @endif
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light">
                  <div>
                    <small class="text-body-secondary d-block">Requested At</small>
                    <span class="fw-medium">{{ \Carbon\Carbon::parse($req->requested_at)->format('M d, Y h:i A') }}</span>
                  </div>
                  <div class="text-center">
                    <small class="text-body-secondary d-block">Status</small>
                    <span class="badge modal-status-badge {{ $s['badge'] }} fs-6">
                      <i class="ri {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}
                    </span>
                  </div>
                  @if($req->completed_at)
                    <div class="text-end">
                      <small class="text-body-secondary d-block">Completed At</small>
                      <span class="fw-medium">{{ \Carbon\Carbon::parse($req->completed_at)->format('M d, Y h:i A') }}</span>
                    </div>
                  @endif
                </div>
              </div>

              {{-- Notes --}}
              @if(!empty($req->description) && $req->description !== 'Room service request')
                <div class="col-12">
                  <label class="form-label text-body-secondary small fw-semibold text-uppercase">
                    <i class="ri ri-chat-3-line me-1"></i>Notes from Guest
                  </label>
                  <div class="p-3 rounded border bg-light">
                    {{ $req->description }}
                  </div>
                </div>
              @endif

            </div>
          </div>

          {{-- Modal Footer --}}
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <div class="d-flex gap-2 modal-footer-actions">
              {{-- Populated by room_service_orders.js --}}
            </div>
          </div>

        </div>
      </div>
    </div>
  @endforeach

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/room-service/room_service_orders.js') }}"></script>
@endsection