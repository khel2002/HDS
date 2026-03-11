'use strict';

// ─────────────────────────────────────────────────────────────────────────────
// BOOT
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

  // Flash session messages
  const sEl = document.getElementById('__flashSuccess');
  const eEl = document.getElementById('__flashError');
  if (sEl) toast('success', 'Success!', sEl.dataset.msg);
  if (eEl) toast('error',   'Error!',   eEl.dataset.msg);

  // Init Bootstrap tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
    .forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));

  // Filtering
  ['searchInput','statusFilter','paymentFilter','dateFilter'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener(id === 'searchInput' ? 'input' : 'change', filterRows);
  });
  document.getElementById('resetBtn')?.addEventListener('click', () => {
    ['searchInput','statusFilter','paymentFilter','dateFilter'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    filterRows();
  });
});

// ─────────────────────────────────────────────────────────────────────────────
// FILTER
// ─────────────────────────────────────────────────────────────────────────────
function filterRows() {
  const term    = (document.getElementById('searchInput')?.value  || '').toLowerCase();
  const status  =  document.getElementById('statusFilter')?.value || '';
  const payment =  document.getElementById('paymentFilter')?.value || '';
  const date    =  document.getElementById('dateFilter')?.value   || '';
  let   count   = 0;

  document.querySelectorAll('#bookingsTableBody .booking-row').forEach(row => {
    const ok =
      (!term    || (row.dataset.search  || '').includes(term))  &&
      (!status  || row.dataset.status  === status)              &&
      (!payment || row.dataset.payment === payment)             &&
      (!date    || row.dataset.checkin === date);
    row.style.display = ok ? '' : 'none';
    if (ok) count++;
  });

  const badge = document.getElementById('visibleCount');
  if (badge) badge.textContent = count + ' booking' + (count !== 1 ? 's' : '');
}

// ─────────────────────────────────────────────────────────────────────────────
// VIEW BOOKING DETAILS
// ─────────────────────────────────────────────────────────────────────────────
async function viewBooking(primaryId) {
  const modal    = document.getElementById('viewBookingModal');
  const body     = document.getElementById('viewModalBody');
  const footer   = document.getElementById('viewModalFooter');
  const subtitle = document.getElementById('viewModalSubtitle');

  // Reset to loading state
  body.innerHTML    = `<div class="d-flex flex-column align-items-center justify-content-center" style="min-height:240px;gap:1rem;"><div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;"></div><p class="text-body-secondary mb-0">Loading booking information…</p></div>`;
  footer.innerHTML  = `<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="ri ri-close-line me-1"></i>Close</button>`;
  subtitle.textContent = 'Loading…';

  bootstrap.Modal.getOrCreateInstance(modal).show();

  try {
    const urlEl = document.getElementById(`showUrl__${primaryId}`);
    if (!urlEl) throw new Error('Route URL not found — please refresh.');

    const res  = await fetch(urlEl.value, {
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to load.');

    const bk = data.booking;

    subtitle.textContent =
      `${bk.guest.first_name} ${bk.guest.last_name} · Booking #${bk.primary_reservation_id} · ${bk.room_count} room${bk.room_count !== 1 ? 's' : ''}`;

    body.innerHTML   = buildModalBody(bk);
    footer.innerHTML = buildModalFooter(bk);

    // Re-init tooltips inside freshly rendered modal
    body.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));

  } catch (err) {
    body.innerHTML = `<div class="alert alert-danger m-4"><i class="ri ri-error-warning-line me-2"></i>${err.message}</div>`;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// BUILD MODAL — BODY
// ─────────────────────────────────────────────────────────────────────────────
function buildModalBody(bk) {
  const fmt   = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' }) : '—';
  const money = v => '₱' + parseFloat(v || 0).toLocaleString('en-PH', { minimumFractionDigits:2 });

  const g        = bk.guest;
  const initials = (g.first_name[0] + g.last_name[0]).toUpperCase();

  // ── Status timeline ─────────────────────────────────────────────────────
  const allStatuses  = bk.rooms.map(r => r.reservation_status);
  const allApproved  = allStatuses.every(s => s === 'approved');
  const allRejected  = allStatuses.every(s => s === 'rejected');
  const allCancelled = allStatuses.every(s => s === 'cancelled');
  const anyApproved  = allStatuses.some(s => s === 'approved');

  const steps = [
    { label:'Booked',   done: true,                       icon:'ri-calendar-line'       },
    { label:'Pending',  done: true,                       icon:'ri-time-line'            },
    { label:'Reviewed', done: anyApproved || allRejected, icon:'ri-file-check-line',
      fail: allRejected || allCancelled },
    { label:'Approved', done: allApproved,                icon:'ri-checkbox-circle-line',
      fail: allRejected || allCancelled },
  ];

  const timeline = `
    <div class="booking-timeline">
      ${steps.map(s => `
        <div class="tl-step ${s.done && !s.fail ? 'done' : ''} ${s.fail ? 'fail' : ''}">
          <div class="tl-dot"><i class="ri ${s.icon}" style="font-size:.65rem;"></i></div>
          <span class="tl-label">${s.label}</span>
        </div>`).join('')}
    </div>`;

  // ── Financial summary boxes ──────────────────────────────────────────────
  const balColor  = parseFloat(bk.total_balance) > 0 ? '#e74c3c' : '#00b894';
  const balBg     = parseFloat(bk.total_balance) > 0 ? '#fdecea' : '#e8f8f5';

  const finBoxes = `
    <div class="fin-grid mb-3">
      <div class="fin-box" style="background:#E6F3FF;">
        <p class="fin-box-label" style="color:#060E4D;">Total Amount</p>
        <p class="fin-box-value" style="color:#060E4D;">${money(bk.total_amount)}</p>
      </div>
      <div class="fin-box" style="background:${balBg};">
        <p class="fin-box-label" style="color:${balColor};">Balance Due</p>
        <p class="fin-box-value" style="color:${balColor};">${money(bk.total_balance)}</p>
      </div>
      <div class="fin-box" style="background:#e8f0fb;">
        <p class="fin-box-label" style="color:#013a72;">Reservation Fee</p>
        <p class="fin-box-value" style="color:#013a72;">${money(bk.total_res_fee)}</p>
      </div>
      <div class="fin-box" style="background:${bk.all_fee_paid ? '#e8f8f5' : '#fdecea'};">
        <p class="fin-box-label" style="color:${bk.all_fee_paid ? '#00695c' : '#c0392b'};">Fee Paid</p>
        <p class="fin-box-value" style="color:${bk.all_fee_paid ? '#00695c' : '#c0392b'};">
          ${bk.all_fee_paid ? '<i class="ri ri-check-double-line"></i> Yes' : '<i class="ri ri-close-line"></i> No'}
        </p>
      </div>
    </div>`;

  // ── Payment row ──────────────────────────────────────────────────────────
  const methodBadge = bk.payment_method === 'online'
    ? `<span class="badge bg-label-info"><i class="ri ri-bank-card-line me-1"></i>Online</span>`
    : bk.payment_method === 'cash'
    ? `<span class="badge bg-label-warning"><i class="ri ri-cash-line me-1"></i>Cash on Arrival</span>`
    : `<span class="badge bg-label-secondary">—</span>`;

  const payStatusBadge = {
    completed: 'bg-label-success',
    pending:   'bg-label-warning',
    refunded:  'bg-label-info',
  };
  const ps = bk.payment_status;

  const paymentRow = `
    <div class="d-flex justify-content-between align-items-center p-3 rounded mb-3" style="background:#f8f9fc;border:1px solid #e9ecef;">
      <div>
        <p style="font-size:.7rem;font-weight:600;color:#6d6777;text-transform:uppercase;margin:0 0 .25rem;">Method</p>
        ${methodBadge}
      </div>
      <div class="text-end">
        <p style="font-size:.7rem;font-weight:600;color:#6d6777;text-transform:uppercase;margin:0 0 .25rem;">Payment Status</p>
        <span class="badge ${payStatusBadge[ps] || 'bg-label-secondary'}" style="text-transform:capitalize;">${ps || '—'}</span>
      </div>
      ${bk.paid_amount ? `
      <div class="text-end">
        <p style="font-size:.7rem;font-weight:600;color:#6d6777;text-transform:uppercase;margin:0 0 .25rem;">Paid</p>
        <span class="fw-bold">${money(bk.paid_amount)}</span>
      </div>` : ''}
    </div>`;

  // ── Room cards ────────────────────────────────────────────────────────────
  const colClass = bk.rooms.length === 1 ? 'col-12'
                 : bk.rooms.length === 2 ? 'col-md-6'
                 : 'col-lg-4 col-md-6';

  const roomCards = bk.rooms.map(r => {
    const stBadgeClass = {
      pending:   'bg-label-warning',
      approved:  'bg-label-success',
      rejected:  'bg-label-danger',
      cancelled: 'bg-label-secondary',
    };
    const stIcon = {
      pending:   'ri-time-line',
      approved:  'ri-checkbox-circle-line',
      rejected:  'ri-close-circle-line',
      cancelled: 'ri-forbid-line',
    };
    const sc = r.reservation_status;

    const imgHtml = r.room_image
      ? `<img src="${r.room_image}" alt="${esc(r.room_type_name)}" class="room-detail-img">`
      : `<div class="room-detail-img-placeholder"><i class="ri ri-hotel-bed-line"></i></div>`;

    let roomActions = '';
    if (sc === 'pending') {
      roomActions = `
        <div class="room-action-group mt-auto">
          <button class="room-action-btn approve" onclick="singleRoomAction(${r.reservation_id},'approve')">
            <i class="ri ri-checkbox-circle-line"></i> Approve
          </button>
          <button class="room-action-btn reject" onclick="singleRoomAction(${r.reservation_id},'reject')">
            <i class="ri ri-close-circle-line"></i> Reject
          </button>
        </div>`;
    } else if (sc === 'approved') {
      roomActions = `
        <div class="room-action-group mt-auto">
          <button class="room-action-btn cancel" onclick="singleRoomAction(${r.reservation_id},'cancel')">
            <i class="ri ri-forbid-line"></i> Cancel Room
          </button>
        </div>`;
    }

    return `
      <div class="${colClass}">
        <div class="room-detail-card">
          ${imgHtml}
          <div class="room-detail-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="room-detail-name">${esc(r.room_type_name)}</p>
                <p class="room-detail-num">Room ${esc(r.room_number)}</p>
              </div>
              <span class="badge ${stBadgeClass[sc] || 'bg-label-secondary'}" style="white-space:nowrap;">
                <i class="ri ${stIcon[sc] || 'ri-question-line'} me-1"></i>${esc(sc)}
              </span>
            </div>

            <div class="room-meta-row">
              <span class="room-meta-chip"><i class="ri ri-calendar-line"></i>
                ${fmt(r.check_in_date)} → ${fmt(r.check_out_date)}
              </span>
              <span class="room-meta-chip"><i class="ri ri-moon-line"></i> ${r.no_nights} night${r.no_nights !== 1 ? 's' : ''}</span>
              <span class="room-meta-chip"><i class="ri ri-group-line"></i> ${r.no_of_pax} guest${r.no_of_pax !== 1 ? 's' : ''}</span>
            </div>

            ${r.purpose ? `
              <div style="background:#f8f9fc;border-radius:.25rem;padding:.5rem .75rem;font-size:.78rem;color:#6d6777;">
                <i class="ri ri-sticky-note-line me-1"></i>${esc(r.purpose)}
              </div>` : ''}

            <div class="room-detail-divider"></div>

            <div class="room-fin-row">
              <span class="label">Rate / Night</span>
              <span class="value text-primary">₱${fmtNum(r.rate_per_night)}</span>
            </div>
            <div class="room-fin-row">
              <span class="label">Room Total</span>
              <span class="value text-primary">₱${fmtNum(r.total_amount)}</span>
            </div>
            <div class="room-fin-row">
              <span class="label">Balance</span>
              <span style="font-weight:700;color:${parseFloat(r.balance) > 0 ? '#e74c3c' : '#00b894'};">
                ₱${fmtNum(r.balance)}
              </span>
            </div>
            <div class="room-fin-row">
              <span class="label">Res. Fee Paid</span>
              <span style="color:${r.reservation_fee_paid ? '#00b894' : '#e74c3c'};font-weight:600;">
                ${r.reservation_fee_paid ? '<i class="ri ri-check-line"></i> Yes' : '<i class="ri ri-close-line"></i> No'}
              </span>
            </div>

            ${roomActions}
          </div>
        </div>
      </div>`;
  }).join('');

  // ── Assemble ─────────────────────────────────────────────────────────────
  return `
    <div class="p-4 border-bottom" style="background:#f8f9fc;">
      ${timeline}
    </div>

    <div class="p-4">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="d-flex align-items-center gap-3 p-3 rounded mb-3" style="background:#f8f9fc;border:1px solid #e9ecef;">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-circle" style="width:52px;height:52px;font-size:1.125rem;">
                ${initials}
              </div>
            </div>
            <div>
              <p class="fw-bold mb-0">${esc(g.first_name)} ${g.middle_name ? esc(g.middle_name) + ' ' : ''}${esc(g.last_name)}</p>
              <p class="text-body-secondary mb-0" style="font-size:.8125rem;">${esc(g.email)}</p>
            </div>
          </div>

          <p class="modal-section-label"><i class="ri ri-user-line"></i> Guest Details</p>
          <div class="info-grid mb-4">
            <div class="info-item">
              <label>Contact</label>
              <span>${esc(g.contact_number || '—')}</span>
            </div>
            <div class="info-item">
              <label>Date of Birth</label>
              <span>${g.dob ? new Date(g.dob + 'T00:00:00').toLocaleDateString('en-PH', {year:'numeric',month:'short',day:'numeric'}) : '—'}</span>
            </div>
            <div class="info-item">
              <label>Booking Date</label>
              <span>${fmt(bk.booking_date)}</span>
            </div>
            <div class="info-item">
              <label>Total Rooms</label>
              <span class="badge bg-label-info"><i class="ri ri-hotel-line me-1"></i>${bk.room_count}</span>
            </div>
          </div>

          <p class="modal-section-label"><i class="ri ri-money-dollar-circle-line"></i> Financial</p>
          ${finBoxes}
          ${paymentRow}
        </div>

        <div class="col-lg-8">
          <p class="modal-section-label"><i class="ri ri-hotel-line"></i> Room${bk.rooms.length !== 1 ? 's' : ''} (${bk.rooms.length})</p>
          <div class="row g-3">${roomCards}</div>
        </div>
      </div>
    </div>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// BUILD MODAL — FOOTER  (bulk actions)
// ─────────────────────────────────────────────────────────────────────────────
function buildModalFooter(bk) {
  const allStatuses = bk.rooms.map(r => r.reservation_status);
  const allPending  = allStatuses.every(s => s === 'pending');
  const anyActive   = allStatuses.some(s => ['pending','approved'].includes(s));
  const payId       = bk.payment_id;
  const ids         = bk.rooms.map(r => r.reservation_id);
  const gName       = escJs(bk.guest.first_name + ' ' + bk.guest.last_name);

  let btns = `<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
    <i class="ri ri-close-line me-1"></i>Close
  </button>`;

  if (allPending && payId) {
    btns += `
    <button type="button" class="btn btn-success"
        onclick="closeViewThen(()=>bulkAction(${payId},${JSON.stringify(ids)},'approve','${gName}'))">
      <i class="ri ri-checkbox-circle-line me-1"></i>Approve All
    </button>
    <button type="button" class="btn btn-danger"
        onclick="closeViewThen(()=>bulkAction(${payId},${JSON.stringify(ids)},'reject','${gName}'))">
      <i class="ri ri-close-circle-line me-1"></i>Reject All
    </button>`;
  }

  if (anyActive && payId) {
    btns += `
    <button type="button" class="btn btn-warning"
        onclick="closeViewThen(()=>bulkAction(${payId},${JSON.stringify(ids)},'cancel','${gName}'))">
      <i class="ri ri-forbid-line me-1"></i>Cancel All
    </button>`;
  }

  return btns;
}

// ─────────────────────────────────────────────────────────────────────────────
// SINGLE ROOM ACTION (called from inside the modal room card)
// ─────────────────────────────────────────────────────────────────────────────
async function singleRoomAction(reservationId, action) {
  const cfg = confirmCfg(action, null, 'this room');
  const result = await Swal.fire(cfg);
  if (!result.isConfirmed) return;

  closeViewThen(async () => {
    showSpinner();
    try {
      const urlEl = document.getElementById(`${action}Url__${reservationId}`);
      if (!urlEl) throw new Error(`Route URL not found for ${action} on reservation ${reservationId}.`);
      const data = await postFetch(urlEl.value);
      patchRowStatus(reservationId, data.new_status);
      updateStats(data.stats);
      Swal.close();
      toast(toastIcon(action), toastTitle(action), data.message);
    } catch (err) {
      Swal.close();
      showError(err.message);
    }
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// BULK ACTION (all rooms in a booking)
// ─────────────────────────────────────────────────────────────────────────────
async function bulkAction(paymentId, reservationIds, action, guestName) {
  const cfg = confirmCfg(action, guestName, 'all rooms');
  const result = await Swal.fire(cfg);
  if (!result.isConfirmed) return;

  showSpinner();
  try {
    let data;
    const bulkUrlEl = document.getElementById(`bulk${cap(action)}Url__${paymentId}`);

    if (bulkUrlEl) {
      data = await postFetch(bulkUrlEl.value);
    } else {
      for (const rid of reservationIds) {
        const el = document.getElementById(`${action}Url__${rid}`);
        if (el) data = await postFetch(el.value);
      }
    }

    reservationIds.forEach(rid => {
      if (data?.new_status) patchRowStatus(rid, data.new_status);
    });
    updateStats(data?.stats || {});

    // Update the row's overall status badge + data-status
    const firstRowEl = document.getElementById(`deleteUrl__${reservationIds[0]}`);
    const row = firstRowEl?.closest('tr');
    if (row && data?.new_status) {
      row.dataset.status = data.new_status;
      const stCell = row.cells[7];
      if (stCell) {
        stCell.innerHTML = buildStatusBadge(data.new_status);
      }
      // Rebuild the dropdown
      rebuildDropdown(row, data.new_status, paymentId, reservationIds, guestName);
    }

    Swal.close();
    toast(toastIcon(action), toastTitle(action), data?.message || 'Done.');
  } catch (err) {
    Swal.close();
    showError(err.message);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// DELETE BOOKING
// ─────────────────────────────────────────────────────────────────────────────
async function confirmDeleteBooking(reservationIds, guestName) {
  const result = await Swal.fire({
    title: 'Delete Booking?',
    html:  `Delete all reservations for <strong>${esc(guestName)}</strong>?<br>
            <small class="text-muted">This cannot be undone.</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e74c3c',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it!',
  });
  if (!result.isConfirmed) return;

  closeViewThen(async () => {
    showSpinner();
    try {
      let lastData;
      for (const rid of reservationIds) {
        const el = document.getElementById(`deleteUrl__${rid}`);
        if (!el) continue;
        const res  = await fetch(el.value, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        });
        lastData = await res.json();
        if (!res.ok) throw new Error(lastData.message || 'Delete failed.');
      }

      const firstEl = document.getElementById(`deleteUrl__${reservationIds[0]}`);
      firstEl?.closest('tr')?.remove();

      updateStats(lastData?.stats || {});
      renumberRows();
      filterRows();

      Swal.close();
      toast('success', 'Deleted!', `Booking for "${esc(guestName)}" has been removed.`);
    } catch (err) {
      Swal.close();
      showError(err.message);
    }
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// DOM PATCHING
// ─────────────────────────────────────────────────────────────────────────────

function patchRowStatus(reservationId, newStatus) {
  const el  = document.getElementById(`deleteUrl__${reservationId}`);
  if (!el) return;
  const row = el.closest('tr');
  if (!row) return;

  row.dataset.status = newStatus;

  const stCell = row.cells[7];
  if (stCell) {
    stCell.innerHTML = buildStatusBadge(newStatus);
  }
}

function buildStatusBadge(status) {
  const badgeClass = {
    approved:  'bg-label-success',
    pending:   'bg-label-warning',
    rejected:  'bg-label-danger',
    cancelled: 'bg-label-secondary',
  };
  const icon = {
    approved:  'ri-checkbox-circle-line',
    pending:   'ri-time-line',
    rejected:  'ri-close-circle-line',
    cancelled: 'ri-forbid-line',
  };
  const cls  = badgeClass[status] || 'bg-label-secondary';
  const ico  = icon[status]       || 'ri-question-line';
  return `<span class="badge ${cls}"><i class="ri ${ico} me-1"></i>${cap(status)}</span>`;
}

function rebuildDropdown(row, newStatus, payId, ids, guestName) {
  const g = escJs(guestName);
  const primaryId = row.dataset.primary;

  const allApproved = newStatus === 'approved';
  const allPending  = newStatus === 'pending';
  const anyActive   = ['pending','approved'].includes(newStatus);

  let items = `
    <a class="dropdown-item" href="javascript:void(0);" onclick="viewBooking(${primaryId})">
      <i class="icon-base ri ri-eye-line me-2"></i>View Details
    </a>`;

  if (allPending && payId) {
    items += `
      <div class="dropdown-divider"></div>
      <a class="dropdown-item text-success" href="javascript:void(0);"
         onclick="bulkAction(${payId},${JSON.stringify(ids)},'approve','${g}')">
        <i class="icon-base ri ri-checkbox-circle-line me-2"></i>Approve All
      </a>
      <a class="dropdown-item text-danger" href="javascript:void(0);"
         onclick="bulkAction(${payId},${JSON.stringify(ids)},'reject','${g}')">
        <i class="icon-base ri ri-close-circle-line me-2"></i>Reject All
      </a>`;
  }

  if (anyActive && payId) {
    items += `
      <a class="dropdown-item text-warning" href="javascript:void(0);"
         onclick="bulkAction(${payId},${JSON.stringify(ids)},'cancel','${g}')">
        <i class="icon-base ri ri-forbid-line me-2"></i>Cancel All
      </a>`;
  }

  if (!allApproved) {
    items += `
      <div class="dropdown-divider"></div>
      <a class="dropdown-item text-danger" href="javascript:void(0);"
         onclick="confirmDeleteBooking(${JSON.stringify(ids)},'${g}')">
        <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete Booking
      </a>`;
  }

  const dropdownMenu = row.querySelector('.dropdown-menu');
  if (dropdownMenu) dropdownMenu.innerHTML = items;
}

function updateStats(stats) {
  if (!stats) return;
  ['total','pending','approved','rejected','cancelled'].forEach(k => {
    const el = document.querySelector(`[data-stat="${k}"]`);
    if (el) el.textContent = stats[k];
  });
  // Combined rejected+cancelled stat card
  const rcEl = document.querySelector('[data-stat="rejected_cancelled"]');
  if (rcEl && stats.rejected !== undefined && stats.cancelled !== undefined) {
    rcEl.textContent = stats.rejected + stats.cancelled;
  }
}

function renumberRows() {
  document.querySelectorAll('#bookingsTableBody .booking-row').forEach((r, i) => {
    const el = r.querySelector('.row-num');
    if (el) el.textContent = i + 1;
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────────────────────

function closeViewThen(fn) {
  const modal = document.getElementById('viewBookingModal');
  const inst  = bootstrap.Modal.getInstance(modal);
  if (inst) {
    modal.addEventListener('hidden.bs.modal', fn, { once: true });
    inst.hide();
  } else {
    fn();
  }
}

async function postFetch(url) {
  const res  = await fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json',
               'Content-Type': 'application/json' },
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Request failed.');
  return data;
}

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function showSpinner() {
  Swal.fire({
    allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
    background: 'transparent', backdrop: 'rgba(0,0,0,.5)',
    didOpen() {
      Swal.showLoading();
      const l = Swal.getLoader();
      if (l) { l.style.width='56px'; l.style.height='56px';
               l.style.borderColor='rgba(255,255,255,.25)';
               l.style.borderTopColor='#fff'; }
    },
  });
}

function showError(text) {
  Swal.fire({ icon:'error', title:'Error!', text, confirmButtonText:'OK' });
}

function toast(icon, title, text = '') {
  Swal.fire({ icon, title, text, timer:3000, toast:true, position:'top-end',
              showConfirmButton:false, timerProgressBar:true });
}

function confirmCfg(action, guestName, scope) {
  const label = { approve:'Approve', reject:'Reject', cancel:'Cancel' };
  const icon  = { approve:'question', reject:'warning', cancel:'warning' };
  const color = { approve:'#00b894', reject:'#e74c3c', cancel:'#ffc107' };
  const who   = guestName ? `<strong>${esc(guestName)}</strong>` : scope;
  return {
    title: `${label[action]} ${cap(scope)}?`,
    html:  `${label[action]} ${scope === 'all rooms' ? 'all room reservations' : 'the room reservation'} for ${who}?`,
    icon: icon[action] || 'question',
    showCancelButton: true,
    confirmButtonColor: color[action] || '#060E4D',
    cancelButtonColor: '#6c757d',
    confirmButtonText: `Yes, ${action}!`,
  };
}

function toastIcon(action)  { return { approve:'success', reject:'error', cancel:'warning' }[action] || 'info'; }
function toastTitle(action) { return { approve:'Approved!', reject:'Rejected!', cancel:'Cancelled!' }[action] || 'Done!'; }

function fmtNum(v) {
  return parseFloat(v || 0).toLocaleString('en-PH', { minimumFractionDigits:2, maximumFractionDigits:2 });
}

function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

function esc(s) {
  return String(s ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function escJs(s) {
  return String(s ?? '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}