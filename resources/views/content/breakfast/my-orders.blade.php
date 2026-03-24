@extends('layouts/contentNavbarLayout')

@section('title', 'My Breakfast Orders')

@section('page-style')
<style>
  /* ── Status timeline ────────────────────────────────────────────────── */
  .order-timeline { display:flex; align-items:center; }
  .order-timeline .step { display:flex; flex-direction:column; align-items:center; flex:1; }
  .order-timeline .step .dot {
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; margin-bottom:5px; flex-shrink:0;
  }
  .order-timeline .step small { font-size:11px; text-align:center; white-space:nowrap; }
  .order-timeline .line { flex:1; height:2px; margin-bottom:20px; }
  .order-timeline .line.done { background:#28a745; }
  .order-timeline .line.idle { background:#dee2e6; }

  /* ── Modal timeline (inside modal, same structure) ─────────────────── */
  .modal-timeline .order-timeline .dot { width:36px; height:36px; font-size:15px; }

  /* ── Stats grid ─────────────────────────────────────────────────────── */
  .stats-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }

  /* ── Note box ───────────────────────────────────────────────────────── */
  .note-box {
    padding: .75rem 1rem;
    font-size: .8125rem;
    color: var(--bs-secondary-color);
    border-left: 3px solid var(--bs-border-color);
  }
</style>
@endsection

@section('content')

  @if(session('success'))
    <span data-success-message="{{ session('success') }}" style="display:none;"></span>
  @endif

  <div class="row gy-4">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h4 class="mb-1">My Breakfast Orders</h4>
              <p class="mb-0 text-body-secondary">
                Track the status of your breakfast requests
                @if($guest['status'] === 'checked-in')
                  &mdash; Room <strong>{{ $guest['room_number'] ?? '—' }}</strong>
                @endif
              </p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm" id="refreshBtn" onclick="refreshOrders()">
                <i class="ri ri-refresh-line me-1"></i>Refresh
              </button>
              @if($guest['status'] === 'checked-in')
                <a href="{{ route('guest.breakfast.menu') }}" class="btn btn-primary btn-sm">
                  <i class="ri ri-add-line me-1"></i>New Order
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Not checked in ───────────────────────────────────────────────── --}}
    @if($guest['status'] !== 'checked-in')
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="ri ri-hotel-line icon-48px text-warning mb-3 d-block"></i>
            <h6 class="mb-1">Not Checked In</h6>
            <p class="text-body-secondary mb-0 small">Order history is available while you are checked in.</p>
          </div>
        </div>
      </div>

    @else

      {{-- ── Stats ─────────────────────────────────────────────────────── --}}
      @php
        $statuses  = $orders->pluck('status');
        $sPending  = $statuses->filter(fn($s) => $s === 'pending')->count();
        $sPrep     = $statuses->filter(fn($s) => $s === 'in_progress')->count();
        $sDeliv    = $statuses->filter(fn($s) => $s === 'delivering')->count();
        $sDone     = $statuses->filter(fn($s) => $s === 'completed')->count();
      @endphp
      <div class="col-12">
        <div class="stats-grid">
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-warning rounded"><i class="ri ri-time-line"></i></div></div>
              <div>
                <p class="mb-0 small text-body-secondary">Pending</p>
                <h5 class="mb-0">{{ $sPending }}</h5>
              </div>
            </div>
          </div>
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-info rounded"><i class="ri ri-loader-4-line"></i></div></div>
              <div>
                <p class="mb-0 small text-body-secondary">Preparing</p>
                <h5 class="mb-0">{{ $sPrep }}</h5>
              </div>
            </div>
          </div>
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-primary rounded"><i class="ri ri-e-bike-2-line"></i></div></div>
              <div>
                <p class="mb-0 small text-body-secondary">Delivering</p>
                <h5 class="mb-0">{{ $sDeliv }}</h5>
              </div>
            </div>
          </div>
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-success rounded"><i class="ri ri-checkbox-circle-line"></i></div></div>
              <div>
                <p class="mb-0 small text-body-secondary">Delivered</p>
                <h5 class="mb-0">{{ $sDone }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Orders Table ─────────────────────────────────────────────────── --}}
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Order History</h5>
            <small class="text-body-secondary" id="orderCount">{{ $orders->count() }} order(s)</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" id="ordersTableWrap">
              <table class="table table-hover align-middle mb-0" id="ordersTable">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4">#</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Requested At</th>
                    <th>Status</th>
                    <th class="text-center pe-4">Details</th>
                  </tr>
                </thead>
                <tbody id="ordersBody">
                  @forelse($orders as $order)
                    @php
                      $statusCfg = [
                        'pending'     => ['label'=>'Pending',          'badge'=>'bg-label-warning', 'icon'=>'ri-time-line'],
                        'in_progress' => ['label'=>'Being Prepared',   'badge'=>'bg-label-info',    'icon'=>'ri-loader-4-line'],
                        'delivering'  => ['label'=>'Out for Delivery', 'badge'=>'bg-label-primary', 'icon'=>'ri-e-bike-2-line'],
                        'completed'   => ['label'=>'Delivered',        'badge'=>'bg-label-success', 'icon'=>'ri-checkbox-circle-line'],
                        'cancelled'   => ['label'=>'Cancelled',        'badge'=>'bg-label-danger',  'icon'=>'ri-close-circle-line'],
                      ];
                      $sc    = $statusCfg[$order['status']] ?? $statusCfg['pending'];
                      $total = $order['total'];
                      $id    = $order['service_request_id'];
                    @endphp
                    <tr>
                      <td class="ps-4">
                        <span class="fw-medium text-body-secondary">#{{ $id }}</span>
                      </td>
                      <td>
                        <div class="d-flex flex-column gap-1">
                          @foreach($order['items'] as $item)
                            <div class="d-flex align-items-center gap-1">
                              <span class="badge bg-label-primary rounded-pill">{{ $item['qty'] }}×</span>
                              <span class="small">{{ $item['name'] ?? 'Unknown' }}</span>
                            </div>
                          @endforeach
                        </div>
                      </td>
                      <td>
                        <span class="fw-semibold text-primary">₱{{ number_format($total, 2) }}</span>
                      </td>
                      <td>
                        <span class="d-block">{{ \Carbon\Carbon::parse($order['requested_at'])->format('M d, Y') }}</span>
                        <small class="text-body-secondary">{{ \Carbon\Carbon::parse($order['requested_at'])->format('h:i A') }}</small>
                      </td>
                      <td>
                        <span class="badge {{ $sc['badge'] }}">
                          <i class="ri {{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
                        </span>
                      </td>
                      <td class="text-center pe-4">
                        <button type="button"
                                class="btn btn-sm btn-icon btn-outline-secondary"
                                onclick="openOrderModal({{ $id }})"
                                title="View Details">
                          <i class="ri ri-eye-line"></i>
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr id="emptyRow">
                      <td colspan="6" class="text-center py-5">
                        <i class="ri ri-shopping-basket-line icon-48px text-body-secondary mb-3 d-block"></i>
                        <h6 class="mb-1">No orders yet</h6>
                        <p class="text-body-secondary mb-3 small">Your breakfast orders will appear here.</p>
                        <a href="{{ route('guest.breakfast.menu') }}" class="btn btn-primary btn-sm">
                          <i class="ri ri-add-line me-1"></i>Order Breakfast
                        </a>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    @endif

  </div>{{-- end .row --}}


  {{-- ================================================================== --}}
  {{-- ORDER DETAIL MODALS (one per order, mirrored from super admin)     --}}
  {{-- ================================================================== --}}
  @if($guest['status'] === 'checked-in')
    @foreach($orders as $order)
      @php
        $statusCfg = [
          'pending'     => ['label'=>'Pending',          'badge'=>'bg-label-warning', 'icon'=>'ri-time-line'],
          'in_progress' => ['label'=>'Being Prepared',   'badge'=>'bg-label-info',    'icon'=>'ri-loader-4-line'],
          'delivering'  => ['label'=>'Out for Delivery', 'badge'=>'bg-label-primary', 'icon'=>'ri-e-bike-2-line'],
          'completed'   => ['label'=>'Delivered',        'badge'=>'bg-label-success', 'icon'=>'ri-checkbox-circle-line'],
          'cancelled'   => ['label'=>'Cancelled',        'badge'=>'bg-label-danger',  'icon'=>'ri-close-circle-line'],
        ];
        $sc    = $statusCfg[$order['status']] ?? $statusCfg['pending'];
        $total = $order['total'];
        $id    = $order['service_request_id'];

        $steps      = ['pending','in_progress','delivering','completed'];
        $lookupSt   = $order['status'] === 'cancelled' ? 'pending' : $order['status'];
        $currentIdx = array_search($lookupSt, $steps);
        if ($currentIdx === false) $currentIdx = 0;
        $stepDefs   = [
          ['label'=>'Pending',    'icon'=>'ri-time-line'],
          ['label'=>'Preparing',  'icon'=>'ri-loader-4-line'],
          ['label'=>'Delivering', 'icon'=>'ri-e-bike-2-line'],
          ['label'=>'Delivered',  'icon'=>'ri-checkbox-circle-line'],
        ];
      @endphp

      <div class="modal fade" id="orderModal{{ $id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <i class="ri ri-bowl-line me-2"></i>Order #{{ $id }} &mdash; Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="row g-4">

                {{-- ── Info cards row ───────────────────────────────────── --}}
                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-receipt-line me-1"></i>Order Info
                      </h6>
                      <p class="mb-1 small">
                        <span class="text-body-secondary">Request ID:</span>
                        <span class="fw-medium ms-1">#{{ $id }}</span>
                      </p>
                      <p class="mb-1 small">
                        <span class="text-body-secondary">Requested at:</span>
                        <span class="fw-medium ms-1">{{ $order['requested_at'] }}</span>
                      </p>
                      <p class="mb-0 small">
                        <span class="text-body-secondary">Status:</span>
                        <span class="badge {{ $sc['badge'] }} ms-1">
                          <i class="ri {{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
                        </span>
                      </p>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-hotel-line me-1"></i>Room Info
                      </h6>
                      <p class="mb-1 small">
                        <span class="text-body-secondary">Room:</span>
                        <span class="badge bg-label-secondary ms-1">
                          <i class="ri ri-door-line me-1"></i>{{ $guest['room_number'] ?? '—' }}
                        </span>
                      </p>
                      <p class="mb-0">
                        <span class="text-body-secondary small">Total:</span>
                        <span class="fw-semibold text-primary ms-1 fs-6">₱{{ number_format($total, 2) }}</span>
                      </p>
                    </div>
                  </div>
                </div>

                {{-- ── Order Items ───────────────────────────────────────── --}}
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
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
                            @foreach($order['items'] as $item)
                              <tr>
                                <td>{{ $item['name'] ?? 'Deleted Item' }}</td>
                                <td class="text-center">{{ $item['qty'] }}</td>
                                <td class="text-end">₱{{ number_format($item['price'], 2) }}</td>
                                <td class="text-end fw-medium">₱{{ number_format($item['subtotal'], 2) }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                          <tfoot class="table-light">
                            <tr>
                              <td colspan="3" class="text-end fw-bold">Total</td>
                              <td class="text-end fw-bold text-primary">₱{{ number_format($total, 2) }}</td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- ── Progress Timeline ─────────────────────────────────── --}}
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-map-pin-time-line me-1"></i>Progress
                      </h6>
                      <div class="modal-timeline">
                        <div class="order-timeline">
                          @foreach($stepDefs as $i => $step)
                            @php
                              $isDone   = $currentIdx > $i;
                              $isActive = $currentIdx === $i;
                              $dotCls   = $isDone   ? 'bg-success text-white'
                                        : ($isActive ? 'bg-primary text-white'
                                                     : 'bg-label-secondary text-body-secondary');
                              $lblCls   = $isDone   ? 'text-success fw-semibold'
                                        : ($isActive ? 'text-primary fw-semibold'
                                                     : 'text-body-secondary');
                              $ico      = $isDone ? 'ri-check-line' : $step['icon'];
                            @endphp
                            <div class="step">
                              <div class="dot {{ $dotCls }}">
                                <i class="ri {{ $ico }}"></i>
                              </div>
                              <small class="{{ $lblCls }}">{{ $step['label'] }}</small>
                            </div>
                            @if(!$loop->last)
                              <div class="line {{ $isDone ? 'done' : 'idle' }}"></div>
                            @endif
                          @endforeach
                        </div>
                      </div>

                      @if($order['status'] === 'cancelled')
                        <div class="text-center mt-3">
                          <span class="badge bg-label-danger px-3 py-2">
                            <i class="ri ri-close-circle-line me-1"></i>This order was cancelled
                          </span>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

                {{-- ── Note ──────────────────────────────────────────────── --}}
                @if(!empty($order['description']))
                  <div class="col-12">
                    <div class="note-box">
                      <span class="d-block small text-body-secondary fw-semibold text-uppercase mb-1">
                        <i class="ri ri-chat-3-line me-1"></i>Note
                      </span>
                      {{ $order['description'] }}
                    </div>
                  </div>
                @endif

              </div>{{-- end row --}}
            </div>{{-- end modal-body --}}

            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>

          </div>
        </div>
      </div>

    @endforeach
  @endif

@endsection

@section('page-script')
<script>
  const ORDERS_URL    = '{{ route("guest.breakfast.orders") }}';
  const MENU_URL      = '{{ route("guest.breakfast.menu") }}';
  const IS_CHECKED_IN = {{ $guest['status'] === 'checked-in' ? 'true' : 'false' }};

  const statusCfg = {
    pending:     { label:'Pending',          badge:'bg-label-warning', icon:'ri-time-line' },
    in_progress: { label:'Being Prepared',   badge:'bg-label-info',    icon:'ri-loader-4-line' },
    delivering:  { label:'Out for Delivery', badge:'bg-label-primary', icon:'ri-e-bike-2-line' },
    completed:   { label:'Delivered',        badge:'bg-label-success', icon:'ri-checkbox-circle-line' },
    cancelled:   { label:'Cancelled',        badge:'bg-label-danger',  icon:'ri-close-circle-line' },
  };

  const timelineSteps = [
    { key:'pending',     label:'Pending',    icon:'ri-time-line' },
    { key:'in_progress', label:'Preparing',  icon:'ri-loader-4-line' },
    { key:'delivering',  label:'Delivering', icon:'ri-e-bike-2-line' },
    { key:'completed',   label:'Delivered',  icon:'ri-checkbox-circle-line' },
  ];

  // ── Open modal for a specific order id ──────────────────────────────────
  function openOrderModal(id) {
    const el = document.getElementById('orderModal' + id);
    if (!el) return;
    const modal = new bootstrap.Modal(el);
    modal.show();
  }

  // ── Build timeline HTML (used when refreshing via AJAX) ─────────────────
  function buildTimeline(status) {
    const isCancelled  = status === 'cancelled';
    const lookupStatus = isCancelled ? 'pending' : status;
    const currentIdx   = timelineSteps.findIndex(s => s.key === lookupStatus);

    let html = '<div class="order-timeline">';
    timelineSteps.forEach((step, idx) => {
      const isDone   = currentIdx > idx;
      const isActive = currentIdx === idx;
      const dotCls   = isDone   ? 'bg-success text-white'
                     : isActive ? 'bg-primary text-white'
                                : 'bg-label-secondary text-body-secondary';
      const lblCls   = isDone   ? 'text-success fw-semibold'
                     : isActive ? 'text-primary fw-semibold'
                                : 'text-body-secondary';
      const ico      = isDone ? 'ri-check-line' : step.icon;

      html += `<div class="step">
        <div class="dot ${dotCls}"><i class="ri ${ico}"></i></div>
        <small class="${lblCls}">${step.label}</small>
      </div>`;

      if (idx < timelineSteps.length - 1) {
        html += `<div class="line ${currentIdx > idx ? 'done' : 'idle'}"></div>`;
      }
    });
    html += '</div>';

    if (isCancelled) {
      html += `<div class="text-center mt-3">
        <span class="badge bg-label-danger px-3 py-2">
          <i class="ri ri-close-circle-line me-1"></i>This order was cancelled
        </span>
      </div>`;
    }
    return html;
  }

  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Render a table row (no detail row — modal-based now) ─────────────────
  function renderRow(o) {
    const sc = statusCfg[o.status] ?? statusCfg.pending;
    const id = o.service_request_id;

    const itemBadges = (o.items ?? []).map(i =>
      `<div class="d-flex align-items-center gap-1">
        <span class="badge bg-label-primary rounded-pill">${esc(i.qty)}×</span>
        <span class="small">${esc(i.name ?? 'Unknown')}</span>
      </div>`
    ).join('');

    const [datePart, ...timeParts] = esc(o.requested_at ?? '—').split(' ');
    const total = parseFloat(o.total).toFixed(2);

    return `
      <tr>
        <td class="ps-4"><span class="fw-medium text-body-secondary">#${id}</span></td>
        <td><div class="d-flex flex-column gap-1">${itemBadges}</div></td>
        <td><span class="fw-semibold text-primary">₱${total}</span></td>
        <td>
          <span class="d-block">${datePart}</span>
          <small class="text-body-secondary">${timeParts.join(' ')}</small>
        </td>
        <td>
          <span class="badge ${sc.badge}">
            <i class="ri ${sc.icon} me-1"></i>${sc.label}
          </span>
        </td>
        <td class="text-center pe-4">
          <button type="button"
                  class="btn btn-sm btn-icon btn-outline-secondary"
                  data-bs-toggle="modal"
                  data-bs-target="#orderModal${id}"
                  title="View Details">
            <i class="ri ri-eye-line"></i>
          </button>
        </td>
      </tr>`;
  }

  // ── Build modal HTML for a refreshed order ──────────────────────────────
  function renderModal(o) {
    const sc  = statusCfg[o.status] ?? statusCfg.pending;
    const id  = o.service_request_id;
    const total = parseFloat(o.total).toFixed(2);

    const breakdownRows = (o.items ?? []).map(i =>
      `<tr>
        <td>${esc(i.name ?? 'Deleted Item')}</td>
        <td class="text-center">${esc(i.qty)}</td>
        <td class="text-end">₱${parseFloat(i.price).toFixed(2)}</td>
        <td class="text-end fw-medium">₱${parseFloat(i.subtotal).toFixed(2)}</td>
      </tr>`
    ).join('');

    const noteHtml = o.description
      ? `<div class="col-12">
           <div class="note-box">
             <span class="d-block small text-body-secondary fw-semibold text-uppercase mb-1">
               <i class="ri ri-chat-3-line me-1"></i>Note
             </span>
             ${esc(o.description)}
           </div>
         </div>`
      : '';

    const cancelledBadge = o.status === 'cancelled'
      ? `<div class="text-center mt-3">
           <span class="badge bg-label-danger px-3 py-2">
             <i class="ri ri-close-circle-line me-1"></i>This order was cancelled
           </span>
         </div>`
      : '';

    return `
      <div class="modal fade" id="orderModal${id}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="ri ri-bowl-line me-2"></i>Order #${id} &mdash; Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-receipt-line me-1"></i>Order Info
                      </h6>
                      <p class="mb-1 small"><span class="text-body-secondary">Request ID:</span> <span class="fw-medium ms-1">#${id}</span></p>
                      <p class="mb-1 small"><span class="text-body-secondary">Requested at:</span> <span class="fw-medium ms-1">${esc(o.requested_at)}</span></p>
                      <p class="mb-0 small"><span class="text-body-secondary">Status:</span>
                        <span class="badge ${sc.badge} ms-1"><i class="ri ${sc.icon} me-1"></i>${sc.label}</span>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-hotel-line me-1"></i>Room Info
                      </h6>
                      <p class="mb-1 small"><span class="text-body-secondary">Total:</span>
                        <span class="fw-semibold text-primary ms-1 fs-6">₱${total}</span>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-restaurant-line me-1"></i>Order Items
                      </h6>
                      <div class="table-responsive">
                        <table class="table table-sm mb-0">
                          <thead class="table-light">
                            <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr>
                          </thead>
                          <tbody>${breakdownRows}</tbody>
                          <tfoot class="table-light">
                            <tr>
                              <td colspan="3" class="text-end fw-bold">Total</td>
                              <td class="text-end fw-bold text-primary">₱${total}</td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-map-pin-time-line me-1"></i>Progress
                      </h6>
                      <div class="modal-timeline">${buildTimeline(o.status)}</div>
                      ${cancelledBadge}
                    </div>
                  </div>
                </div>
                ${noteHtml}
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>`;
  }

  // ── Refresh orders via AJAX ──────────────────────────────────────────────
  async function refreshOrders() {
    if (!IS_CHECKED_IN) return;
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Refreshing…';

    try {
      const res  = await fetch(ORDERS_URL, { headers: { Accept: 'application/json' } });
      const data = await res.json();
      const tbody   = document.getElementById('ordersBody');
      const countEl = document.getElementById('orderCount');

      // Remove existing dynamically created modals
      document.querySelectorAll('.dynamic-order-modal').forEach(m => m.remove());

      if (!data.success || !data.orders?.length) {
        tbody.innerHTML = `
          <tr id="emptyRow">
            <td colspan="6" class="text-center py-5">
              <i class="ri ri-shopping-basket-line icon-48px text-body-secondary mb-3 d-block"></i>
              <h6 class="mb-1">No orders yet</h6>
              <p class="text-body-secondary mb-3 small">Your breakfast orders will appear here.</p>
              <a href="${MENU_URL}" class="btn btn-primary btn-sm">
                <i class="ri ri-add-line me-1"></i>Order Breakfast
              </a>
            </td>
          </tr>`;
        if (countEl) countEl.textContent = '0 order(s)';
      } else {
        tbody.innerHTML = data.orders.map(renderRow).join('');
        if (countEl) countEl.textContent = `${data.orders.length} order(s)`;

        // Inject fresh modals into body
        const modalsHtml = data.orders.map(renderModal).join('');
        const wrapper    = document.createElement('div');
        wrapper.classList.add('dynamic-order-modal');
        wrapper.innerHTML = modalsHtml;
        document.body.appendChild(wrapper);
      }
    } catch (err) {
      console.error(err);
      alert('Failed to refresh orders. Please try again.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="ri ri-refresh-line me-1"></i>Refresh';
    }
  }
</script>
@endsection