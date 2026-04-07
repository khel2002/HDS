@extends('layouts/contentNavbarLayout')
@section('title', 'Checkout Requests')

@section('page-style')
<style>
  /* ── Status badge colours ─────────────────────────────────── */
  .status-pending    { background: rgba(255,171,0,.12);   color: #ffab00; border: 1.5px solid rgba(255,171,0,.35);   }
  .status-inspecting { background: rgba(105,108,255,.1);  color: #696cff; border: 1.5px solid rgba(105,108,255,.3);  }
  .status-has_issues { background: rgba(234,84,85,.1);    color: #ea5455; border: 1.5px solid rgba(234,84,85,.3);    }
  .status-cleared    { background: rgba(40,199,111,.1);   color: #28c76f; border: 1.5px solid rgba(40,199,111,.3);   }

  /* ── Timeline ─────────────────────────────────────────────── */
  .co-timeline { display: flex; align-items: center; }
  .co-timeline .step { display: flex; flex-direction: column; align-items: center; flex: 1; }
  .co-timeline .step .dot {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 6px; flex-shrink: 0;
  }
  .co-timeline .step small { font-size: .68rem; text-align: center; white-space: nowrap; }
  .co-timeline .line { flex: 1; height: 2px; margin-bottom: 24px; }
  .co-timeline .line.done { background: #28c76f; }
  .co-timeline .line.idle { background: var(--bs-border-color); }

  /* ── Charge row ───────────────────────────────────────────── */
  .charge-row td { vertical-align: middle; font-size: .82rem; }

  /* ── Toast ────────────────────────────────────────────────── */
  #co-toast-wrap {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    display: flex; flex-direction: column; gap: .5rem;
    z-index: 9999; pointer-events: none;
  }
  .co-toast {
    display: flex; align-items: center; gap: .7rem;
    padding: .75rem 1.1rem; border-radius: 10px;
    font-size: .84rem; font-weight: 500; min-width: 240px; max-width: 320px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15); pointer-events: auto; color: #fff;
    animation: coToastIn .22s ease forwards;
  }
  .co-toast.success { background: #2e7d32; }
  .co-toast.error   { background: #c62828; }
  .co-toast.warning { background: #e65100; }
  .co-toast.info    { background: #1565c0; }
  .co-toast.hide    { animation: coToastOut .2s ease forwards; }
  @keyframes coToastIn  { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:none} }
  @keyframes coToastOut { from{opacity:1;transform:none} to{opacity:0;transform:translateX(20px)} }
</style>
@endsection

@section('content')
<div class="row gy-4">

  {{-- ── Page Header ─────────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h4 class="mb-1">
              <i class="ri ri-logout-box-line me-2 text-warning"></i>Checkout Requests
            </h4>
            <p class="mb-0 text-body-secondary small">
              Guests requesting checkout — inspect rooms, add charges if needed, then finalize.
            </p>
          </div>
          <a href="{{ route('super_admin.registration.check-out') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri ri-list-check me-1"></i>All Active Guests
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Stat Cards ──────────────────────────────────────────── --}}
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar"><div class="avatar-initial bg-label-warning rounded"><i class="ri ri-time-line"></i></div></div>
        <div>
          <p class="mb-0 small text-body-secondary">Pending</p>
          <h5 class="mb-0" id="statPending">{{ $stats['pending'] }}</h5>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar"><div class="avatar-initial bg-label-primary rounded"><i class="ri ri-search-eye-line"></i></div></div>
        <div>
          <p class="mb-0 small text-body-secondary">Inspecting</p>
          <h5 class="mb-0" id="statInspecting">{{ $stats['inspecting'] }}</h5>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar"><div class="avatar-initial bg-label-danger rounded"><i class="ri ri-alert-line"></i></div></div>
        <div>
          <p class="mb-0 small text-body-secondary">Has Issues</p>
          <h5 class="mb-0" id="statIssues">{{ $stats['has_issues'] }}</h5>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar"><div class="avatar-initial bg-label-success rounded"><i class="ri ri-checkbox-circle-line"></i></div></div>
        <div>
          <p class="mb-0 small text-body-secondary">Cleared</p>
          <h5 class="mb-0" id="statCleared">{{ $stats['cleared'] }}</h5>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Main Table ───────────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Checkout Requests</h5>
        <small class="text-body-secondary">{{ $checkoutRequests->count() }} total</small>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Guest</th>
                <th>Room</th>
                <th>Requested</th>
                <th>Expected CO</th>
                <th>Balance</th>
                <th>Damage</th>
                <th>Status</th>
                <th class="text-center pe-4">Action</th>
              </tr>
            </thead>
            <tbody id="coTableBody">
              @forelse($checkoutRequests as $req)
                @php
                  $guestName = trim($req->guest_first_name . ' ' . $req->guest_last_name) ?: '—';
                  $initials  = strtoupper(substr($req->guest_first_name,0,1) . substr($req->guest_last_name,0,1)) ?: '?';
                  $statusCfg = [
                    'pending'    => ['label'=>'Pending',    'cls'=>'status-pending',    'icon'=>'ri-time-line'],
                    'inspecting' => ['label'=>'Inspecting', 'cls'=>'status-inspecting', 'icon'=>'ri-search-eye-line'],
                    'has_issues' => ['label'=>'Has Issues', 'cls'=>'status-has_issues', 'icon'=>'ri-alert-line'],
                    'cleared'    => ['label'=>'Cleared',    'cls'=>'status-cleared',    'icon'=>'ri-checkbox-circle-line'],
                  ];
                  $sc = $statusCfg[$req->status] ?? $statusCfg['pending'];
                @endphp
                <tr id="row-cr-{{ $req->checkout_request_id }}">
                  <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial bg-label-warning rounded-circle fw-bold">{{ $initials }}</div>
                      </div>
                      <div>
                        <div class="fw-semibold">{{ $guestName }}</div>
                        <small class="text-body-secondary">{{ $req->email }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-info">Rm {{ $req->room_number }}</span>
                    <div><small class="text-body-secondary">{{ $req->room_type_name }}</small></div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($req->requested_at)->format('M d, Y') }}</div>
                    <small class="text-body-secondary">{{ \Carbon\Carbon::parse($req->requested_at)->format('h:i A') }}</small>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($req->expected_checkout)->format('M d, Y') }}</div>
                    @if(\Carbon\Carbon::parse($req->expected_checkout)->isPast())
                      <span class="badge bg-danger" style="font-size:.65rem;">Overdue</span>
                    @elseif(\Carbon\Carbon::parse($req->expected_checkout)->isToday())
                      <span class="badge bg-warning text-dark" style="font-size:.65rem;">Today</span>
                    @endif
                  </td>
                  <td>
                    <span class="fw-semibold {{ $req->balance > 0 ? 'text-danger' : 'text-success' }}">
                      ₱{{ number_format($req->balance, 2) }}
                    </span>
                  </td>
                  <td>
                    @if($req->total_damage > 0)
                      <span class="fw-semibold text-danger">₱{{ number_format($req->total_damage, 2) }}</span>
                      <div><small class="text-body-secondary">{{ $req->damage_charges->count() }} charge(s)</small></div>
                    @else
                      <span class="text-body-secondary small">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge {{ $sc['cls'] }}" style="font-size:.75rem; padding:.35rem .75rem;">
                      <i class="ri {{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
                    </span>
                  </td>
                  <td class="text-center pe-4">
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            onclick="openInspectModal({{ $req->checkout_request_id }})">
                      <i class="ri ri-search-eye-line me-1"></i>Inspect
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <i class="ri ri-checkbox-circle-line icon-48px text-success mb-3 d-block"></i>
                    <h6>No checkout requests</h6>
                    <p class="text-body-secondary small mb-0">Guests who request checkout will appear here.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>{{-- end row --}}

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- INSPECT MODAL                                               --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="inspectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri ri-search-eye-line me-2 text-primary"></i>
          Room Inspection — <span id="modalGuestName">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="modalBody">
        <div class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>
      </div>

      <div class="modal-footer" id="modalFooter"></div>

    </div>
  </div>
</div>

<div id="co-toast-wrap"></div>
@endsection

@section('page-script')
<script>
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  const ROUTES = {
    details:       (id) => `/super-admin/checkout-requests/${id}/details`,
    startInspect:  (id) => `/super-admin/checkout-requests/${id}/start-inspection`,
    addCharge:     (id) => `/super-admin/checkout-requests/${id}/add-charge`,
    removeCharge:  (id, cid) => `/super-admin/checkout-requests/${id}/charges/${cid}`,
    markCleared:   (id) => `/super-admin/checkout-requests/${id}/mark-cleared`,
    markIssues:    (id) => `/super-admin/checkout-requests/${id}/mark-has-issues`,
    finalize:      (id) => `/super-admin/checkout-requests/${id}/finalize`,
  };

  let currentCrId = null;

  // ── Toast ──────────────────────────────────────────────────────
  const _icons = {
    success: 'ri-checkbox-circle-line',
    error:   'ri-error-warning-line',
    warning: 'ri-alert-line',
    info:    'ri-information-line',
  };
  function toast(type, msg) {
    const wrap = document.getElementById('co-toast-wrap');
    const el   = document.createElement('div');
    el.className = 'co-toast ' + type;
    el.innerHTML = `<i class="ri ${_icons[type]}"></i><span>${msg}</span>`;
    wrap.appendChild(el);
    setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 220); }, 3600);
  }

  // ── Open modal ─────────────────────────────────────────────────
  async function openInspectModal(crId) {
    currentCrId = crId;
    document.getElementById('modalGuestName').textContent = '—';
    document.getElementById('modalBody').innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;
    document.getElementById('modalFooter').innerHTML = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('inspectModal')).show();
    await refreshModal(crId);
  }

  // ── Refresh modal content ──────────────────────────────────────
  async function refreshModal(crId) {
    try {
      const res  = await fetch(ROUTES.details(crId), { headers: { Accept: 'application/json' } });
      const data = await res.json();
      if (!data.success) { toast('error', 'Failed to load details.'); return; }
      renderModal(data.data);
    } catch (e) {
      console.error(e);
      toast('error', 'Network error.');
    }
  }

  // ── Render modal ───────────────────────────────────────────────
  function renderModal(d) {
    const guestName = `${d.guest_first_name} ${d.guest_last_name}`.trim() || '—';
    document.getElementById('modalGuestName').textContent = guestName;

    const statusMap = {
      pending:    { label: 'Pending',    cls: 'bg-label-warning', icon: 'ri-time-line'            },
      inspecting: { label: 'Inspecting', cls: 'bg-label-primary', icon: 'ri-search-eye-line'      },
      has_issues: { label: 'Has Issues', cls: 'bg-label-danger',  icon: 'ri-alert-line'           },
      cleared:    { label: 'Cleared',    cls: 'bg-label-success', icon: 'ri-checkbox-circle-line' },
    };
    const sc = statusMap[d.status] ?? statusMap.pending;

    // ── Timeline ─────────────────────────────────────────────────
    const steps = [
      { key: 'pending',    label: 'Requested',  icon: 'ri-send-plane-line'       },
      { key: 'inspecting', label: 'Inspecting', icon: 'ri-search-eye-line'       },
      { key: 'resolved',   label: d.status === 'has_issues' ? 'Issues Found' : 'Cleared',
                           icon: d.status === 'has_issues'  ? 'ri-alert-line' : 'ri-checkbox-circle-line' },
    ];
    const order    = ['pending', 'inspecting', 'cleared', 'has_issues'];
    const idx      = ['pending', 'inspecting'].includes(d.status) ? order.indexOf(d.status) : 2;

    let timeline = `<div class="co-timeline">`;
    steps.forEach((step, i) => {
      const isDone   = idx > i;
      const isActive = idx === i || (i === 2 && ['cleared','has_issues'].includes(d.status));
      const dotCls   = isDone   ? 'bg-success text-white'
                     : (isActive
                         ? (d.status === 'has_issues' && i === 2 ? 'bg-danger text-white' : 'bg-primary text-white')
                         : 'bg-label-secondary text-body-secondary');
      const lblCls   = isDone   ? 'text-success fw-semibold'
                     : (isActive
                         ? (d.status === 'has_issues' && i === 2 ? 'text-danger fw-semibold' : 'text-primary fw-semibold')
                         : 'text-body-secondary');
      const ico      = isDone ? 'ri-check-line' : step.icon;
      timeline += `<div class="step"><div class="dot ${dotCls}"><i class="ri ${ico}"></i></div><small class="${lblCls}">${step.label}</small></div>`;
      if (i < steps.length - 1) {
        timeline += `<div class="line ${isDone ? 'done' : 'idle'}"></div>`;
      }
    });
    timeline += `</div>`;

    // ── Charges table ─────────────────────────────────────────────
    const charges  = d.damage_charges ?? [];
    const totalDmg = charges.reduce((s, c) => s + parseFloat(c.amount), 0);

    let chargesHtml = '';
    if (charges.length > 0) {
      chargesHtml = `
        <div class="col-12">
          <div class="card border mb-0">
            <div class="card-body">
              <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                <i class="ri ri-alert-line me-1 text-danger"></i>Damage / Missing Charges
              </h6>
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Description</th>
                      <th class="text-end">Amount</th>
                      <th class="text-center">Acknowledged</th>
                      ${['pending','inspecting','has_issues'].includes(d.status) ? '<th></th>' : ''}
                    </tr>
                  </thead>
                  <tbody>
                    ${charges.map(c => `
                      <tr class="charge-row" id="charge-row-${c.id}">
                        <td>${esc(c.description)}</td>
                        <td class="text-end fw-semibold text-danger">₱${parseFloat(c.amount).toFixed(2)}</td>
                        <td class="text-center">
                          ${c.is_acknowledged
                            ? '<span class="badge bg-label-success"><i class="ri ri-checkbox-circle-line me-1"></i>Yes</span>'
                            : '<span class="badge bg-label-danger"><i class="ri ri-time-line me-1"></i>Pending</span>'}
                        </td>
                        ${['pending','inspecting','has_issues'].includes(d.status) ? `
                        <td class="text-center">
                          ${!c.is_acknowledged ? `
                            <button class="btn btn-sm btn-icon btn-outline-danger"
                                    onclick="doRemoveCharge(${d.checkout_request_id}, ${c.id})"
                                    title="Remove charge">
                              <i class="ri ri-delete-bin-line"></i>
                            </button>` : ''}
                        </td>` : ''}
                      </tr>`).join('')}
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <td class="fw-semibold">Total</td>
                      <td class="text-end fw-bold text-danger">₱${totalDmg.toFixed(2)}</td>
                      <td></td>
                      ${['pending','inspecting','has_issues'].includes(d.status) ? '<td></td>' : ''}
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>`;
    }

    // ── Add-charge form (only when inspecting or has_issues) ──────
    let addChargeForm = '';
    if (['inspecting', 'has_issues'].includes(d.status)) {
      addChargeForm = `
        <div class="col-12">
          <div class="card border mb-0" style="border-color: rgba(234,84,85,.3) !important; background: rgba(234,84,85,.03);">
            <div class="card-body">
              <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                <i class="ri ri-add-circle-line me-1 text-danger"></i>Add Damage / Missing Charge
              </h6>
              <div class="row g-2 align-items-end">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Description <span class="text-danger">*</span></label>
                  <input type="text" id="chargeDesc" class="form-control form-control-sm"
                         placeholder="e.g. Broken lamp, Missing towel…" maxlength="255">
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-semibold">Amount (₱) <span class="text-danger">*</span></label>
                  <input type="number" id="chargeAmount" class="form-control form-control-sm"
                         placeholder="0.00" min="1" step="0.01">
                </div>
                <div class="col-md-3">
                  <button class="btn btn-danger btn-sm w-100" onclick="doAddCharge(${d.checkout_request_id})">
                    <i class="ri ri-add-line me-1"></i>Add Charge
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>`;
    }

    // ── Staff note (cleared / has_issues) ─────────────────────────
    let staffNoteHtml = '';
    if (d.staff_notes) {
      staffNoteHtml = `
        <div class="col-12">
          <div class="p-3 rounded" style="background: var(--bs-tertiary-bg); font-size: .82rem; border-left: 3px solid var(--bs-border-color);">
            <span class="d-block text-body-secondary fw-semibold text-uppercase mb-1" style="font-size:.68rem; letter-spacing:.06em;">
              <i class="ri ri-chat-3-line me-1"></i>Staff Note
            </span>
            ${esc(d.staff_notes)}
          </div>
        </div>`;
    }

    // ── Guest note ────────────────────────────────────────────────
    let guestNoteHtml = '';
    if (d.notes) {
      guestNoteHtml = `
        <div class="col-12">
          <div class="p-3 rounded" style="background: var(--bs-tertiary-bg); font-size: .82rem; border-left: 3px solid var(--bs-border-color);">
            <span class="d-block text-body-secondary fw-semibold text-uppercase mb-1" style="font-size:.68rem; letter-spacing:.06em;">
              <i class="ri ri-user-3-line me-1"></i>Guest Note
            </span>
            ${esc(d.notes)}
          </div>
        </div>`;
    }

    // ── Cleared notice ────────────────────────────────────────────
    let clearedNotice = '';
    if (d.status === 'cleared') {
      const unack = charges.filter(c => !c.is_acknowledged).length;
      clearedNotice = `
        <div class="col-12">
          <div class="alert ${unack > 0 ? 'alert-warning' : 'alert-success'} mb-0">
            <i class="ri ${unack > 0 ? 'ri-time-line' : 'ri-checkbox-circle-line'} me-2"></i>
            ${unack > 0
              ? `Room is cleared but <strong>${unack} charge(s)</strong> still pending guest acknowledgement. Finalize will be available once acknowledged.`
              : 'Room is cleared and all charges acknowledged. You can now finalize checkout.'}
          </div>
        </div>`;
    }

    document.getElementById('modalBody').innerHTML = `
      <div class="row g-3">

        {{-- Guest / Room info --}}
        <div class="col-md-6">
          <div class="card border h-100 mb-0">
            <div class="card-body">
              <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                <i class="ri ri-user-3-line me-1"></i>Guest Info
              </h6>
              <p class="mb-1 small"><span class="text-body-secondary">Name:</span> <span class="fw-medium ms-1">${esc(guestName)}</span></p>
              <p class="mb-1 small"><span class="text-body-secondary">Email:</span> <span class="fw-medium ms-1">${esc(d.email)}</span></p>
              <p class="mb-0 small">
                <span class="text-body-secondary">Status:</span>
                <span class="badge ${sc.cls} ms-1"><i class="ri ${sc.icon} me-1"></i>${sc.label}</span>
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border h-100 mb-0">
            <div class="card-body">
              <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                <i class="ri ri-hotel-line me-1"></i>Stay Info
              </h6>
              <p class="mb-1 small"><span class="text-body-secondary">Room:</span> <span class="badge bg-label-info ms-1"><i class="ri ri-door-line me-1"></i>${esc(d.room_number)}</span></p>
              <p class="mb-1 small"><span class="text-body-secondary">Type:</span> <span class="fw-medium ms-1">${esc(d.room_type_name)}</span></p>
              <p class="mb-1 small"><span class="text-body-secondary">Nights:</span> <span class="fw-medium ms-1">${d.no_nights}</span></p>
              <p class="mb-1 small"><span class="text-body-secondary">Expected CO:</span> <span class="fw-medium ms-1">${d.expected_checkout}</span></p>
              <p class="mb-0 small"><span class="text-body-secondary">Balance:</span>
                <span class="fw-bold ms-1 ${parseFloat(d.balance) > 0 ? 'text-danger' : 'text-success'}">
                  ₱${parseFloat(d.balance).toFixed(2)}
                </span>
              </p>
            </div>
          </div>
        </div>

        {{-- Timeline --}}
        <div class="col-12">
          <div class="card border mb-0">
            <div class="card-body">
              <h6 class="text-body-secondary text-uppercase small fw-semibold mb-3">
                <i class="ri ri-map-pin-time-line me-1"></i>Inspection Progress
              </h6>
              ${timeline}
            </div>
          </div>
        </div>

        ${staffNoteHtml}
        ${guestNoteHtml}
        ${chargesHtml}
        ${addChargeForm}
        ${clearedNotice}

      </div>`;

    // ── Modal footer buttons ───────────────────────────────────────
    let footer = `<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>`;

    if (d.status === 'pending') {
      footer += `
        <button class="btn btn-primary" onclick="doStartInspection(${d.checkout_request_id})">
          <i class="ri ri-search-eye-line me-1"></i>Start Inspection
        </button>`;
    }

    if (d.status === 'inspecting') {
      footer += `
        <div class="me-auto d-flex gap-2">
          <input type="text" id="staffNoteInput" class="form-control form-control-sm" style="width:220px;"
                 placeholder="Optional staff note…">
        </div>
        <button class="btn btn-success" onclick="doMarkCleared(${d.checkout_request_id})">
          <i class="ri ri-checkbox-circle-line me-1"></i>Mark as Cleared
        </button>`;
    }

    if (d.status === 'has_issues') {
      footer += `
        <div class="me-auto d-flex gap-2">
          <input type="text" id="staffNoteInput" class="form-control form-control-sm" style="width:220px;"
                 placeholder="Optional staff note…">
        </div>
        <button class="btn btn-success" onclick="doMarkCleared(${d.checkout_request_id})">
          <i class="ri ri-checkbox-circle-line me-1"></i>Mark as Cleared
        </button>`;
    }

    if (d.status === 'cleared') {
      const unack = (d.damage_charges ?? []).filter(c => !c.is_acknowledged).length;
      footer += `
        <button class="btn btn-warning ${unack > 0 ? 'disabled' : ''}"
                ${unack > 0 ? 'disabled title="Wait for guest to acknowledge charges"' : ''}
                onclick="doFinalize(${d.checkout_request_id})">
          <i class="ri ri-logout-box-line me-1"></i>Finalize Checkout
        </button>`;
    }

    document.getElementById('modalFooter').innerHTML = footer;
  }

  // ── API calls ──────────────────────────────────────────────────
  async function doStartInspection(crId) {
    const ok = await apiPost(ROUTES.startInspect(crId), {});
    if (ok) { toast('info', 'Inspection started.'); updateStats(ok.stats); await refreshModal(crId); }
  }

  async function doAddCharge(crId) {
    const desc   = document.getElementById('chargeDesc')?.value?.trim();
    const amount = parseFloat(document.getElementById('chargeAmount')?.value);
    if (!desc)           { toast('warning', 'Please enter a description.'); return; }
    if (isNaN(amount) || amount < 1) { toast('warning', 'Please enter a valid amount (min ₱1).'); return; }

    const ok = await apiPost(ROUTES.addCharge(crId), { description: desc, amount });
    if (ok) {
      toast('success', 'Charge added and guest balance updated.');
      updateStats(ok.stats);
      document.getElementById('chargeDesc').value   = '';
      document.getElementById('chargeAmount').value = '';
      await refreshModal(crId);
    }
  }

  async function doRemoveCharge(crId, chargeId) {
    if (!confirm('Remove this charge and deduct from balance?')) return;
    const ok = await apiDelete(ROUTES.removeCharge(crId, chargeId));
    if (ok) { toast('success', 'Charge removed.'); updateStats(ok.stats); await refreshModal(crId); }
  }

  async function doMarkCleared(crId) {
    const note = document.getElementById('staffNoteInput')?.value?.trim() ?? '';
    const ok   = await apiPost(ROUTES.markCleared(crId), { staff_notes: note });
    if (ok) { toast('success', 'Room marked as cleared!'); updateStats(ok.stats); await refreshModal(crId); }
  }

  async function doMarkIssues(crId) {
    const note = document.getElementById('staffNoteInput')?.value?.trim() ?? '';
    const ok   = await apiPost(ROUTES.markIssues(crId), { staff_notes: note });
    if (ok) { toast('warning', 'Status set to Has Issues.'); updateStats(ok.stats); await refreshModal(crId); }
  }

  async function doFinalize(crId) {
    if (!confirm('Finalize checkout? The room will be freed and registration closed.')) return;
    const ok = await apiPost(ROUTES.finalize(crId), {});
    if (ok) {
      toast('success', ok.message);
      // Remove row from table
      document.getElementById(`row-cr-${crId}`)?.remove();
      bootstrap.Modal.getInstance(document.getElementById('inspectModal'))?.hide();
    }
  }

  // ── Generic helpers ────────────────────────────────────────────
  async function apiPost(url, body) {
    try {
      const res  = await fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body:    JSON.stringify(body),
      });
      const data = await res.json();
      if (!data.success) { toast('error', data.error ?? 'Something went wrong.'); return null; }
      return data;
    } catch (e) {
      toast('error', 'Network error. Please try again.');
      return null;
    }
  }

  async function apiDelete(url) {
    try {
      const res  = await fetch(url, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      });
      const data = await res.json();
      if (!data.success) { toast('error', data.error ?? 'Something went wrong.'); return null; }
      return data;
    } catch (e) {
      toast('error', 'Network error. Please try again.');
      return null;
    }
  }

  function updateStats(stats) {
    if (!stats) return;
    document.getElementById('statPending').textContent    = stats.pending    ?? 0;
    document.getElementById('statInspecting').textContent = stats.inspecting ?? 0;
    document.getElementById('statIssues').textContent     = stats.has_issues ?? 0;
    document.getElementById('statCleared').textContent    = stats.cleared    ?? 0;
  }

  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>
@endsection