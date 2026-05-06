/**
 * room_service_orders.js
 * Super Admin — Room Service Requests
 * Place at: public/js/room-service/room_service_orders.js
 */

/* ── Config ──────────────────────────────────────────────────────────────── */
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const STATUS_MAP = {
  pending:     { label: 'Pending',     badge: 'bg-label-warning', icon: 'ri-time-line' },
  in_progress: { label: 'In Progress', badge: 'bg-label-info',    icon: 'ri-loader-4-line' },
  completed:   { label: 'Completed',   badge: 'bg-label-success', icon: 'ri-checkbox-circle-line' },
  cancelled:   { label: 'Cancelled',   badge: 'bg-label-danger',  icon: 'ri-close-circle-line' },
};

const TIMELINE_STEPS = [
  { key: 'pending',     label: 'Pending',     icon: 'ri-time-line' },
  { key: 'in_progress', label: 'In Progress', icon: 'ri-loader-4-line' },
  { key: 'completed',   label: 'Completed',   icon: 'ri-checkbox-circle-line' },
];

/* ── Toast via SweetAlert2 mixin ─────────────────────────────────────────── */
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  customClass: { popup: 'swal-over-modal' },
});

function showToast(icon, title) {
  Toast.fire({ icon, title });
}

/* ── Session flash messages ───────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const success = document.querySelector('[data-success-message]');
  const error   = document.querySelector('[data-error-message]');
  if (success) showToast('success', success.dataset.successMessage);
  if (error)   showToast('error',   error.dataset.errorMessage);
});

/* ── Client-side filtering ───────────────────────────────────────────────── */
const searchInput    = document.getElementById('searchOrders');
const statusFilter   = document.getElementById('filterStatus');
const categoryFilter = document.getElementById('filterCategory');
const tableWrap      = document.getElementById('ordersTableWrap');
const emptyState     = document.getElementById('filterEmptyState');

function applyFilters() {
  const search = (searchInput?.value ?? '').toLowerCase().trim();
  const status = statusFilter?.value ?? '';
  const cat    = categoryFilter?.value ?? '';

  const rows   = document.querySelectorAll('#ordersTableBody .order-row');
  let visible  = 0;

  rows.forEach(row => {
    const guestMatch  = !search || (row.dataset.guest ?? '').includes(search) || (row.dataset.room ?? '').includes(search);
    const statusMatch = !status || row.dataset.status === status;
    const catMatch    = !cat    || (row.dataset.category ?? '').split(',').includes(cat);

    const show = guestMatch && statusMatch && catMatch;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  if (tableWrap)  tableWrap.style.display  = visible > 0 ? '' : 'none';
  if (emptyState) emptyState.style.display = visible > 0 ? 'none' : '';
}

searchInput?.addEventListener('input', applyFilters);
statusFilter?.addEventListener('change', applyFilters);
categoryFilter?.addEventListener('change', applyFilters);

function resetFilters() {
  if (searchInput)    searchInput.value    = '';
  if (statusFilter)   statusFilter.value   = '';
  if (categoryFilter) categoryFilter.value = '';
  applyFilters();
}

document.getElementById('resetOrderFilters')?.addEventListener('click', resetFilters);
document.getElementById('resetOrderFilters2')?.addEventListener('click', resetFilters);

/* ── Refresh button ──────────────────────────────────────────────────────── */
document.getElementById('refreshOrders')?.addEventListener('click', () => {
  window.location.reload();
});

/* ── Status update ───────────────────────────────────────────────────────── */
async function updateStatus(id, newStatus, url, rowEl) {
  const cfg = STATUS_MAP[newStatus];
  const confirmText = newStatus === 'cancelled'
    ? 'Are you sure you want to cancel this request?'
    : `Mark this request as <strong>${cfg?.label ?? newStatus}</strong>?`;

  const result = await Swal.fire({
    title: 'Confirm Action',
    html: confirmText,
    icon: newStatus === 'cancelled' ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, proceed',
    cancelButtonText: 'Cancel',
    confirmButtonColor: newStatus === 'cancelled' ? '#dc3545' : '#696CFF',
    customClass: { popup: 'swal-over-modal' },
  });

  if (!result.isConfirmed) return;

  try {
    const res  = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
        'Accept':       'application/json',
      },
      body: JSON.stringify({ status: newStatus }),
    });
    const data = await res.json();

    if (!res.ok || !data.success) {
      throw new Error(data.message ?? 'Something went wrong.');
    }

    showToast('success', data.message ?? 'Status updated.');

    // Update the row in the table
    if (rowEl) {
      rowEl.dataset.status = newStatus;

      // Update status badge
      const badge = rowEl.querySelector('.badge');
      if (badge && cfg) {
        badge.className = `badge ${cfg.badge}`;
        badge.innerHTML = `<i class="ri ${cfg.icon} me-1"></i>${cfg.label}`;
      }

      // Replace action buttons
      const actionsCell = rowEl.querySelector('td:last-child .d-flex');
      if (actionsCell) {
        actionsCell.innerHTML = buildActionButtons(id, newStatus, url);
      }

      // Update modal if open
      updateModal(id, newStatus);

      // Update stat counters
      refreshStatCounters();
    }
  } catch (err) {
    console.error(err);
    showToast('error', err.message ?? 'Failed to update status.');
  }
}

function buildActionButtons(id, status, url) {
  const viewBtn = `
    <button type="button"
            class="btn btn-sm btn-icon btn-outline-secondary"
            data-bs-toggle="modal"
            data-bs-target="#viewReqModal${id}"
            title="View Details">
      <i class="ri ri-eye-line"></i>
    </button>`;

  if (status === 'pending') {
    return viewBtn + `
      <button type="button" class="btn btn-sm btn-info order-status-btn"
              data-id="${id}" data-new-status="in_progress" data-url="${url}" title="Mark as In Progress">
        <i class="ri ri-loader-4-line me-1"></i>Process
      </button>
      <button type="button" class="btn btn-sm btn-outline-danger order-status-btn"
              data-id="${id}" data-new-status="cancelled" data-url="${url}" title="Cancel Request">
        <i class="ri ri-close-line me-1"></i>Cancel
      </button>`;
  }

  if (status === 'in_progress') {
    return viewBtn + `
      <button type="button" class="btn btn-sm btn-success order-status-btn"
              data-id="${id}" data-new-status="completed" data-url="${url}" title="Mark as Completed">
        <i class="ri ri-checkbox-circle-line me-1"></i>Complete
      </button>`;
  }

  if (status === 'completed') {
    return viewBtn + `<span class="text-success small"><i class="ri ri-check-double-line me-1"></i>Done</span>`;
  }

  if (status === 'cancelled') {
    return viewBtn + `<span class="text-danger small"><i class="ri ri-close-circle-line me-1"></i>Cancelled</span>`;
  }

  return viewBtn;
}

/* ── Delegate click for status buttons ──────────────────────────────────── */
document.getElementById('ordersTableBody')?.addEventListener('click', e => {
  const btn = e.target.closest('.order-status-btn');
  if (!btn) return;

  const id        = btn.dataset.id;
  const newStatus = btn.dataset.newStatus;
  const url       = btn.dataset.url;
  const rowEl     = document.querySelector(`.order-row[data-id="${id}"]`);

  updateStatus(id, newStatus, url, rowEl);
});

/* ── Update modal on status change ──────────────────────────────────────── */
function updateModal(id, newStatus) {
  const modal = document.getElementById(`viewReqModal${id}`);
  if (!modal) return;

  const cfg = STATUS_MAP[newStatus] ?? STATUS_MAP.pending;

  // Update status badge inside modal
  modal.querySelector('.modal-status-badge')?.let(el => {
    el.className = `badge modal-status-badge ${cfg.badge} fs-6`;
    el.innerHTML = `<i class="ri ${cfg.icon} me-1"></i>${cfg.label}`;
  });

  // Rebuild timeline
  const timelineWrap = modal.querySelector('.modal-timeline');
  if (timelineWrap) {
    timelineWrap.innerHTML = buildTimeline(newStatus);
  }

  // Update footer action buttons
  const footerActions = modal.querySelector('.modal-footer-actions');
  if (footerActions) {
    const rowEl = document.querySelector(`.order-row[data-id="${id}"]`);
    const url   = rowEl?.dataset.statusUrl ?? '';
    footerActions.innerHTML = buildModalFooterActions(id, newStatus, url);
  }
}

function buildTimeline(status) {
  const isCancelled  = status === 'cancelled';
  const lookupStatus = isCancelled ? 'pending' : status;
  const currentIdx   = TIMELINE_STEPS.findIndex(s => s.key === lookupStatus);

  let html = '<div class="d-flex align-items-center justify-content-between mb-3 px-2">';

  TIMELINE_STEPS.forEach((step, idx) => {
    const isDone   = currentIdx > idx;
    const isActive = currentIdx === idx;
    const dotCls   = isDone   ? 'bg-success text-white'
                   : isActive ? 'bg-primary text-white'
                              : 'bg-label-secondary text-body-secondary';
    const lblCls   = isDone   ? 'text-success fw-semibold'
                   : isActive ? 'text-primary fw-semibold'
                              : 'text-body-secondary';
    const ico      = isDone ? 'ri-check-line' : step.icon;

    html += `
      <div class="d-flex flex-column align-items-center" style="flex:1;min-width:0;">
        <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 ${dotCls}"
             style="width:36px;height:36px;flex-shrink:0;">
          <i class="ri ${ico}" style="font-size:16px;"></i>
        </div>
        <small class="${lblCls}" style="font-size:11px;text-align:center;white-space:nowrap;">${step.label}</small>
      </div>`;

    if (idx < TIMELINE_STEPS.length - 1) {
      html += `<div style="flex:1;height:2px;background:${isDone ? '#28a745' : '#dee2e6'};margin-bottom:20px;flex-shrink:1;"></div>`;
    }
  });

  html += '</div>';

  if (isCancelled) {
    html += `<div class="text-center mt-1 mb-2">
      <span class="badge bg-label-danger px-3 py-2">
        <i class="ri ri-close-circle-line me-1"></i>This request was cancelled
      </span>
    </div>`;
  }

  return html;
}

function buildModalFooterActions(id, status, url) {
  if (status === 'pending') {
    return `
      <button type="button" class="btn btn-info order-status-btn"
              data-id="${id}" data-new-status="in_progress" data-url="${url}">
        <i class="ri ri-loader-4-line me-1"></i>Mark In Progress
      </button>
      <button type="button" class="btn btn-outline-danger order-status-btn"
              data-id="${id}" data-new-status="cancelled" data-url="${url}">
        <i class="ri ri-close-line me-1"></i>Cancel Request
      </button>`;
  }
  if (status === 'in_progress') {
    return `
      <button type="button" class="btn btn-success order-status-btn"
              data-id="${id}" data-new-status="completed" data-url="${url}">
        <i class="ri ri-checkbox-circle-line me-1"></i>Mark Completed
      </button>`;
  }
  return '';
}

/* Polyfill: NodeList.forEach doesn't have .let; use optional helper */
Element.prototype.let = function(fn) { fn(this); };

/* ── Populate modal footer actions on modal show ─────────────────────────── */
document.addEventListener('show.bs.modal', e => {
  const modal = e.target;
  if (!modal.id.startsWith('viewReqModal')) return;

  const id     = modal.id.replace('viewReqModal', '');
  const rowEl  = document.querySelector(`.order-row[data-id="${id}"]`);
  if (!rowEl) return;

  const status = rowEl.dataset.status;
  const url    = rowEl.dataset.statusUrl ?? '';

  const footerActions = modal.querySelector('.modal-footer-actions');
  if (footerActions) {
    footerActions.innerHTML = buildModalFooterActions(id, status, url);
  }
});

/* ── Delegate modal footer action buttons ────────────────────────────────── */
document.addEventListener('click', e => {
  const btn = e.target.closest('.modal-footer-actions .order-status-btn');
  if (!btn) return;

  const id        = btn.dataset.id;
  const newStatus = btn.dataset.newStatus;
  const url       = btn.dataset.url;
  const rowEl     = document.querySelector(`.order-row[data-id="${id}"]`);

  // Close the modal first
  const modalEl  = btn.closest('.modal');
  const bsModal  = bootstrap.Modal.getInstance(modalEl);
  if (bsModal) bsModal.hide();

  setTimeout(() => updateStatus(id, newStatus, url, rowEl), 400);
});

/* ── Live stat counter refresh ───────────────────────────────────────────── */
function refreshStatCounters() {
  const rows    = document.querySelectorAll('#ordersTableBody .order-row');
  const counts  = { pending: 0, in_progress: 0, completed: 0, cancelled: 0 };

  rows.forEach(r => {
    const s = r.dataset.status;
    if (s in counts) counts[s]++;
  });

  Object.entries(counts).forEach(([key, val]) => {
    const el = document.querySelector(`[data-stat="${key}"]`);
    if (el) el.textContent = val;
  });
}