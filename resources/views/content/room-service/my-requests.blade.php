@extends('layouts/contentNavbarLayout')

@section('title', 'My Room Service Requests')

@section('page-style')
<style>
  /* ── Status timeline (same pattern as breakfast my-orders) ──── */
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

  /* ── Stats grid ─────────────────────────────────────────────── */
  .stats-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }

  /* ── Note box ───────────────────────────────────────────────── */
  .note-box {
    padding:.75rem 1rem; font-size:.8125rem;
    color:var(--bs-secondary-color);
    border-left:3px solid var(--bs-border-color);
  }

  /* ── Category badge pill ─────────────────────────────────────── */
  .cat-pill {
    display:inline-flex; align-items:center; gap:.3rem;
    padding:.15rem .6rem; border-radius:999px;
    font-size:.7rem; font-weight:600; text-transform:capitalize;
  }
</style>
@endsection

@section('content')

  @if(session('success'))
    <span data-success-message="{{ session('success') }}" style="display:none;"></span>
  @endif

  <div class="row gy-4">

    {{-- ── Page Header ──────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h4 class="mb-1">My Room Service Requests</h4>
              <p class="mb-0 text-body-secondary">
                Track the status of your room service requests
                @if($guest['status'] === 'checked-in')
                  &mdash; Room <strong>{{ $guest['room_number'] ?? '—' }}</strong>
                @endif
              </p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm" id="refreshBtn" onclick="refreshRequests()">
                <i class="ri ri-refresh-line me-1"></i>Refresh
              </button>
              @if($guest['status'] === 'checked-in')
                <a href="{{ route('guest.room-service.index') }}" class="btn btn-primary btn-sm">
                  <i class="ri ri-add-line me-1"></i>New Request
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Not checked in ───────────────────────────────────────── --}}
    @if($guest['status'] !== 'checked-in')
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="ri ri-hotel-line icon-48px text-warning mb-3 d-block"></i>
            <h6 class="mb-1">Not Checked In</h6>
            <p class="text-body-secondary mb-0 small">Request history is available while you are checked in.</p>
          </div>
        </div>
      </div>

    @else

      {{-- ── Stats ────────────────────────────────────────────────── --}}
      @php
        $statuses = $requests->pluck('status');
        $sPending = $statuses->filter(fn($s) => $s === 'pending')->count();
        $sInProg  = $statuses->filter(fn($s) => $s === 'in_progress')->count();
        $sDone    = $statuses->filter(fn($s) => $s === 'completed')->count();
      @endphp
      <div class="col-12">
        <div class="stats-grid">
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-warning rounded"><i class="ri ri-time-line"></i></div></div>
              <div><p class="mb-0 small text-body-secondary">Pending</p><h5 class="mb-0">{{ $sPending }}</h5></div>
            </div>
          </div>
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-info rounded"><i class="ri ri-loader-4-line"></i></div></div>
              <div><p class="mb-0 small text-body-secondary">In Progress</p><h5 class="mb-0">{{ $sInProg }}</h5></div>
            </div>
          </div>
          <div class="card mb-0">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="avatar"><div class="avatar-initial bg-label-success rounded"><i class="ri ri-checkbox-circle-line"></i></div></div>
              <div><p class="mb-0 small text-body-secondary">Completed</p><h5 class="mb-0">{{ $sDone }}</h5></div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Requests Table ───────────────────────────────────────── --}}
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Request History</h5>
            <small class="text-body-secondary" id="requestCount">{{ $requests->count() }} request(s)</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4">#</th>
                    <th>Services</th>
                    <th>Requested At</th>
                    <th>Status</th>
                    <th class="text-center pe-4">Details</th>
                  </tr>
                </thead>
                <tbody id="requestsBody">
                  @forelse($requests as $req)
                    @php
                      $statusCfg = [
                        'pending'     => ['label'=>'Pending',     'badge'=>'bg-label-warning', 'icon'=>'ri-time-line'],
                        'in_progress' => ['label'=>'In Progress', 'badge'=>'bg-label-info',    'icon'=>'ri-loader-4-line'],
                        'completed'   => ['label'=>'Completed',   'badge'=>'bg-label-success', 'icon'=>'ri-checkbox-circle-line'],
                        'cancelled'   => ['label'=>'Cancelled',   'badge'=>'bg-label-danger',  'icon'=>'ri-close-circle-line'],
                      ];
                      $sc  = $statusCfg[$req['status']] ?? $statusCfg['pending'];
                      $id  = $req['service_request_id'];

                      $catBadges = ['housekeeping'=>'bg-label-info','toiletries'=>'bg-label-success','technical'=>'bg-label-warning','comfort'=>'bg-label-primary'];
                      $catIcons  = ['housekeeping'=>'ri-brush-line','toiletries'=>'ri-flask-line','technical'=>'ri-tools-line','comfort'=>'ri-sofa-line'];
                    @endphp
                    <tr>
                      <td class="ps-4">
                        <span class="fw-medium text-body-secondary">#{{ $id }}</span>
                      </td>
                      <td>
                        <div class="d-flex flex-column gap-1">
                          @foreach($req['items'] as $item)
                            <div class="d-flex align-items-center gap-1">
                              <span class="badge {{ $catBadges[$item['category']] ?? 'bg-label-secondary' }} rounded-pill" style="font-size:.65rem;">
                                <i class="ri {{ $catIcons[$item['category']] ?? 'ri-service-line' }} me-1"></i>{{ ucfirst($item['category']) }}
                              </span>
                              <span class="small">{{ $item['name'] ?? 'Unknown' }}</span>
                              @if($item['requires_quantity'] && $item['quantity'] > 1)
                                <span class="text-body-secondary small">×{{ $item['quantity'] }}</span>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      </td>
                      <td>
                        <span class="d-block">{{ \Carbon\Carbon::parse($req['requested_at'])->format('M d, Y') }}</span>
                        <small class="text-body-secondary">{{ \Carbon\Carbon::parse($req['requested_at'])->format('h:i A') }}</small>
                      </td>
                      <td>
                        <span class="badge {{ $sc['badge'] }}">
                          <i class="ri {{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
                        </span>
                      </td>
                      <td class="text-center pe-4">
                        <button type="button"
                                class="btn btn-sm btn-icon btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#reqModal{{ $id }}"
                                title="View Details">
                          <i class="ri ri-eye-line"></i>
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr id="emptyRow">
                      <td colspan="5" class="text-center py-5">
                        <i class="ri ri-concierge-bell-line icon-48px text-body-secondary mb-3 d-block"></i>
                        <h6 class="mb-1">No requests yet</h6>
                        <p class="text-body-secondary mb-3 small">Your room service requests will appear here.</p>
                        <a href="{{ route('guest.room-service.index') }}" class="btn btn-primary btn-sm">
                          <i class="ri ri-add-line me-1"></i>Make a Request
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
  </div>


  {{-- ================================================================ --}}
  {{-- REQUEST DETAIL MODALS                                            --}}
  {{-- ================================================================ --}}
  @if($guest['status'] === 'checked-in')
    @foreach($requests as $req)
      @php
        $statusCfg = [
          'pending'     => ['label'=>'Pending',     'badge'=>'bg-label-warning', 'icon'=>'ri-time-line'],
          'in_progress' => ['label'=>'In Progress', 'badge'=>'bg-label-info',    'icon'=>'ri-loader-4-line'],
          'completed'   => ['label'=>'Completed',   'badge'=>'bg-label-success', 'icon'=>'ri-checkbox-circle-line'],
          'cancelled'   => ['label'=>'Cancelled',   'badge'=>'bg-label-danger',  'icon'=>'ri-close-circle-line'],
        ];
        $sc  = $statusCfg[$req['status']] ?? $statusCfg['pending'];
        $id  = $req['service_request_id'];

        $catBadges = ['housekeeping'=>'bg-label-info','toiletries'=>'bg-label-success','technical'=>'bg-label-warning','comfort'=>'bg-label-primary'];
        $catIcons  = ['housekeeping'=>'ri-brush-line','toiletries'=>'ri-flask-line','technical'=>'ri-tools-line','comfort'=>'ri-sofa-line'];

        // Timeline: 3-step (no delivering for room service)
        $steps      = ['pending','in_progress','completed'];
        $lookupSt   = $req['status'] === 'cancelled' ? 'pending' : $req['status'];
        $currentIdx = array_search($lookupSt, $steps);
        if ($currentIdx === false) $currentIdx = 0;
        $stepDefs = [
          ['label'=>'Pending',     'icon'=>'ri-time-line'],
          ['label'=>'In Progress', 'icon'=>'ri-loader-4-line'],
          ['label'=>'Completed',   'icon'=>'ri-checkbox-circle-line'],
        ];
      @endphp

      <div class="modal fade" id="reqModal{{ $id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <i class="ri ri-concierge-bell-line me-2"></i>Request #{{ $id }} &mdash; Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="row g-4">

                {{-- ── Info cards ─────────────────────────────────────── --}}
                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-receipt-line me-1"></i>Request Info
                      </h6>
                      <p class="mb-1 small">
                        <span class="text-body-secondary">Request ID:</span>
                        <span class="fw-medium ms-1">#{{ $id }}</span>
                      </p>
                      <p class="mb-1 small">
                        <span class="text-body-secondary">Submitted at:</span>
                        <span class="fw-medium ms-1">{{ $req['requested_at'] }}</span>
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
                      <p class="mb-0 small">
                        <span class="text-body-secondary">Total services:</span>
                        <span class="fw-medium ms-1">{{ count($req['items']) }}</span>
                      </p>
                    </div>
                  </div>
                </div>

                {{-- ── Services list ────────────────────────────────────── --}}
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
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
                            @foreach($req['items'] as $item)
                              <tr>
                                <td>
                                  <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs">
                                      <div class="avatar-initial bg-label-secondary rounded">
                                        <i class="ri {{ $item['icon'] ?? 'ri-service-line' }}" style="font-size:.75rem;"></i>
                                      </div>
                                    </div>
                                    <span class="fw-medium small">{{ $item['name'] ?? 'Unknown Service' }}</span>
                                  </div>
                                </td>
                                <td>
                                  <span class="badge {{ $catBadges[$item['category']] ?? 'bg-label-secondary' }}" style="font-size:.65rem;">
                                    <i class="ri {{ $catIcons[$item['category']] ?? 'ri-service-line' }} me-1"></i>
                                    {{ ucfirst($item['category'] ?? '—') }}
                                  </span>
                                </td>
                                <td class="text-center">
                                  @if($item['requires_quantity'])
                                    <span class="fw-semibold">{{ $item['quantity'] }}</span>
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

                {{-- ── Progress Timeline ────────────────────────────────── --}}
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                        <i class="ri ri-map-pin-time-line me-1"></i>Progress
                      </h6>
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

                      @if($req['status'] === 'cancelled')
                        <div class="text-center mt-3">
                          <span class="badge bg-label-danger px-3 py-2">
                            <i class="ri ri-close-circle-line me-1"></i>This request was cancelled
                          </span>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

                {{-- ── Note ─────────────────────────────────────────────── --}}
                @if(!empty($req['description']) && $req['description'] !== 'Room service request')
                  <div class="col-12">
                    <div class="note-box">
                      <span class="d-block small text-body-secondary fw-semibold text-uppercase mb-1">
                        <i class="ri ri-chat-3-line me-1"></i>Note
                      </span>
                      {{ $req['description'] }}
                    </div>
                  </div>
                @endif

              </div>
            </div>

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
  const REQUESTS_URL  = '{{ route("guest.room-service.requests-json") }}';
  const NEW_URL       = '{{ route("guest.room-service.index") }}';
  const IS_CHECKED_IN = {{ $guest['status'] === 'checked-in' ? 'true' : 'false' }};

  const statusCfg = {
    pending:     { label:'Pending',     badge:'bg-label-warning', icon:'ri-time-line' },
    in_progress: { label:'In Progress', badge:'bg-label-info',    icon:'ri-loader-4-line' },
    completed:   { label:'Completed',   badge:'bg-label-success', icon:'ri-checkbox-circle-line' },
    cancelled:   { label:'Cancelled',   badge:'bg-label-danger',  icon:'ri-close-circle-line' },
  };

  const catBadges = { housekeeping:'bg-label-info', toiletries:'bg-label-success', technical:'bg-label-warning', comfort:'bg-label-primary' };
  const catIcons  = { housekeeping:'ri-brush-line',  toiletries:'ri-flask-line',   technical:'ri-tools-line',   comfort:'ri-sofa-line' };

  const timelineSteps = [
    { key:'pending',     label:'Pending',     icon:'ri-time-line' },
    { key:'in_progress', label:'In Progress', icon:'ri-loader-4-line' },
    { key:'completed',   label:'Completed',   icon:'ri-checkbox-circle-line' },
  ];

  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function buildTimeline(status) {
    const isCancelled  = status === 'cancelled';
    const lookupStatus = isCancelled ? 'pending' : status;
    const currentIdx   = timelineSteps.findIndex(s => s.key === lookupStatus);

    let html = '<div class="order-timeline">';
    timelineSteps.forEach((step, idx) => {
      const isDone   = currentIdx > idx;
      const isActive = currentIdx === idx;
      const dotCls   = isDone ? 'bg-success text-white' : isActive ? 'bg-primary text-white' : 'bg-label-secondary text-body-secondary';
      const lblCls   = isDone ? 'text-success fw-semibold' : isActive ? 'text-primary fw-semibold' : 'text-body-secondary';
      const ico      = isDone ? 'ri-check-line' : step.icon;
      html += `<div class="step"><div class="dot ${dotCls}"><i class="ri ${ico}"></i></div><small class="${lblCls}">${step.label}</small></div>`;
      if (idx < timelineSteps.length - 1) {
        html += `<div class="line ${isDone ? 'done' : 'idle'}"></div>`;
      }
    });
    html += '</div>';
    if (isCancelled) {
      html += `<div class="text-center mt-3"><span class="badge bg-label-danger px-3 py-2"><i class="ri ri-close-circle-line me-1"></i>This request was cancelled</span></div>`;
    }
    return html;
  }

  function renderRow(r) {
    const sc  = statusCfg[r.status] ?? statusCfg.pending;
    const id  = r.service_request_id;
    const [date, ...time] = esc(r.requested_at ?? '—').split(' ');

    const itemBadges = (r.items ?? []).map(i =>
      `<div class="d-flex align-items-center gap-1">
        <span class="badge ${catBadges[i.category] ?? 'bg-label-secondary'} rounded-pill" style="font-size:.65rem;">
          <i class="ri ${catIcons[i.category] ?? 'ri-service-line'} me-1"></i>${esc(i.category)}
        </span>
        <span class="small">${esc(i.name ?? 'Unknown')}</span>
        ${i.requires_quantity && i.quantity > 1 ? `<span class="text-body-secondary small">×${i.quantity}</span>` : ''}
      </div>`
    ).join('');

    return `
      <tr>
        <td class="ps-4"><span class="fw-medium text-body-secondary">#${id}</span></td>
        <td><div class="d-flex flex-column gap-1">${itemBadges}</div></td>
        <td><span class="d-block">${date}</span><small class="text-body-secondary">${time.join(' ')}</small></td>
        <td><span class="badge ${sc.badge}"><i class="ri ${sc.icon} me-1"></i>${sc.label}</span></td>
        <td class="text-center pe-4">
          <button type="button" class="btn btn-sm btn-icon btn-outline-secondary"
                  data-bs-toggle="modal" data-bs-target="#reqModal${id}" title="View Details">
            <i class="ri ri-eye-line"></i>
          </button>
        </td>
      </tr>`;
  }

  function renderModal(r) {
    const sc  = statusCfg[r.status] ?? statusCfg.pending;
    const id  = r.service_request_id;

    const serviceRows = (r.items ?? []).map(i => `
      <tr>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-xs">
              <div class="avatar-initial bg-label-secondary rounded">
                <i class="ri ${esc(i.icon ?? 'ri-service-line')}" style="font-size:.75rem;"></i>
              </div>
            </div>
            <span class="fw-medium small">${esc(i.name ?? 'Unknown Service')}</span>
          </div>
        </td>
        <td>
          <span class="badge ${catBadges[i.category] ?? 'bg-label-secondary'}" style="font-size:.65rem;">
            <i class="ri ${catIcons[i.category] ?? 'ri-service-line'} me-1"></i>${esc(i.category ?? '—')}
          </span>
        </td>
        <td class="text-center">${i.requires_quantity
          ? `<span class="fw-semibold">${i.quantity}</span>`
          : `<span class="badge bg-label-secondary" style="font-size:.65rem;">Report</span>`}</td>
      </tr>`
    ).join('');

    const noteHtml = (r.description && r.description !== 'Room service request')
      ? `<div class="col-12">
           <div class="note-box">
             <span class="d-block small text-body-secondary fw-semibold text-uppercase mb-1">
               <i class="ri ri-chat-3-line me-1"></i>Note
             </span>
             ${esc(r.description)}
           </div>
         </div>`
      : '';

    return `
      <div class="modal fade dynamic-rs-modal" id="reqModal${id}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="ri ri-concierge-bell-line me-2"></i>Request #${id} &mdash; Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3"><i class="ri ri-receipt-line me-1"></i>Request Info</h6>
                      <p class="mb-1 small"><span class="text-body-secondary">Request ID:</span> <span class="fw-medium ms-1">#${id}</span></p>
                      <p class="mb-1 small"><span class="text-body-secondary">Submitted at:</span> <span class="fw-medium ms-1">${esc(r.requested_at)}</span></p>
                      <p class="mb-0 small"><span class="text-body-secondary">Status:</span>
                        <span class="badge ${sc.badge} ms-1"><i class="ri ${sc.icon} me-1"></i>${sc.label}</span>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card border h-100 mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3"><i class="ri ri-hotel-line me-1"></i>Room Info</h6>
                      <p class="mb-1 small"><span class="text-body-secondary">Total services:</span>
                        <span class="fw-medium ms-1">${(r.items ?? []).length}</span>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3"><i class="ri ri-list-check me-1"></i>Requested Services</h6>
                      <div class="table-responsive">
                        <table class="table table-sm mb-0">
                          <thead class="table-light">
                            <tr><th>Service</th><th>Category</th><th class="text-center">Qty</th></tr>
                          </thead>
                          <tbody>${serviceRows}</tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="card border mb-0">
                    <div class="card-body">
                      <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3"><i class="ri ri-map-pin-time-line me-1"></i>Progress</h6>
                      ${buildTimeline(r.status)}
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

  async function refreshRequests() {
    if (!IS_CHECKED_IN) return;
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Refreshing…';

    try {
      const res  = await fetch(REQUESTS_URL, { headers: { Accept: 'application/json' } });
      const data = await res.json();
      const tbody    = document.getElementById('requestsBody');
      const countEl  = document.getElementById('requestCount');

      document.querySelectorAll('.dynamic-rs-modal').forEach(m => m.remove());

      if (!data.success || !data.requests?.length) {
        tbody.innerHTML = `
          <tr id="emptyRow">
            <td colspan="5" class="text-center py-5">
              <i class="ri ri-concierge-bell-line icon-48px text-body-secondary mb-3 d-block"></i>
              <h6 class="mb-1">No requests yet</h6>
              <p class="text-body-secondary mb-3 small">Your room service requests will appear here.</p>
              <a href="${NEW_URL}" class="btn btn-primary btn-sm"><i class="ri ri-add-line me-1"></i>Make a Request</a>
            </td>
          </tr>`;
        if (countEl) countEl.textContent = '0 request(s)';
      } else {
        tbody.innerHTML = data.requests.map(renderRow).join('');
        if (countEl) countEl.textContent = `${data.requests.length} request(s)`;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = data.requests.map(renderModal).join('');
        document.body.appendChild(wrapper);
      }
    } catch (err) {
      console.error(err);
      alert('Failed to refresh. Please try again.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="ri ri-refresh-line me-1"></i>Refresh';
    }
  }
</script>
@endsection