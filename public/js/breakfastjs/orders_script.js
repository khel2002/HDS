document.addEventListener('DOMContentLoaded', () => {

  // ── Toast helper ───────────────────────────────────────────────────────
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });

  function showSuccess(msg) { Toast.fire({ icon: 'success', title: msg }); }
  function showError(msg)   { Toast.fire({ icon: 'error',   title: msg }); }

  // Bootstrap session messages
  const successEl = document.querySelector('[data-success-message]');
  const errorEl   = document.querySelector('[data-error-message]');
  if (successEl) showSuccess(successEl.dataset.successMessage);
  if (errorEl)   showError(errorEl.dataset.errorMessage);

  // ── CSRF ───────────────────────────────────────────────────────────────
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ── Status config ──────────────────────────────────────────────────────
  const statusMap = {
    pending:     { label: 'Pending',          badge: 'bg-label-warning', icon: 'ri-time-line' },
    in_progress: { label: 'Being Prepared',   badge: 'bg-label-info',    icon: 'ri-loader-4-line' },
    delivering:  { label: 'Out for Delivery', badge: 'bg-label-primary', icon: 'ri-e-bike-2-line' },
    completed:   { label: 'Delivered',        badge: 'bg-label-success', icon: 'ri-checkbox-circle-line' },
    cancelled:   { label: 'Cancelled',        badge: 'bg-label-danger',  icon: 'ri-close-circle-line' },
  };

  const timelineSteps = [
    { key: 'pending',     label: 'Pending',    icon: 'ri-time-line' },
    { key: 'in_progress', label: 'Preparing',  icon: 'ri-loader-4-line' },
    { key: 'delivering',  label: 'Delivering', icon: 'ri-e-bike-2-line' },
    { key: 'completed',   label: 'Delivered',  icon: 'ri-checkbox-circle-line' },
  ];

  const statKeys = ['pending', 'in_progress', 'delivering', 'completed', 'cancelled'];

  // ── Build timeline HTML ────────────────────────────────────────────────
  function buildTimeline(currentStatus) {
    const isCancelled  = currentStatus === 'cancelled';
    const lookupStatus = isCancelled ? 'pending' : currentStatus;
    const currentIdx   = timelineSteps.findIndex(s => s.key === lookupStatus);

    let html = '<div class="d-flex align-items-center justify-content-between mb-3 px-2">';

    timelineSteps.forEach((step, idx) => {
      const isDone   = currentIdx > idx;   // step fully passed → green
      const isActive = currentIdx === idx; // current step → primary/blue

      const dotClass = isDone
        ? 'bg-success text-white'
        : isActive
          ? 'bg-primary text-white'
          : 'bg-label-secondary text-body-secondary';

      const labelClass = isDone
        ? 'text-success fw-semibold'
        : isActive
          ? 'text-primary fw-semibold'
          : 'text-body-secondary';

      // Show a checkmark icon for fully-done steps for extra clarity
      const iconClass = isDone ? 'ri-check-line' : step.icon;

      html += `
        <div class="d-flex flex-column align-items-center" style="flex:1;min-width:0;">
          <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 ${dotClass}"
               style="width:36px;height:36px;flex-shrink:0;">
            <i class="ri ${iconClass}" style="font-size:16px;"></i>
          </div>
          <small class="${labelClass}" style="font-size:11px;text-align:center;white-space:nowrap;">${step.label}</small>
        </div>`;

      if (idx < timelineSteps.length - 1) {
        // Line between step[idx] → step[idx+1] is green once step[idx] is done
        const lineGreen = currentIdx > idx;
        html += `<div style="flex:1;height:2px;background:${lineGreen ? '#28a745' : '#dee2e6'};margin-bottom:20px;flex-shrink:1;"></div>`;
      }
    });

    html += '</div>';

    if (isCancelled) {
      html += `<div class="text-center mt-1 mb-2">
        <span class="badge bg-label-danger px-3 py-2">
          <i class="ri ri-close-circle-line me-1"></i>This order was cancelled
        </span>
      </div>`;
    }

    return html;
  }

  // ── Build table action cell ────────────────────────────────────────────
  function buildActionCell(item) {
    const viewBtn = `<button type="button"
        class="btn btn-sm btn-icon btn-outline-secondary"
        data-bs-toggle="modal"
        data-bs-target="#viewOrderModal${item.service_request_id}"
        title="View Details">
      <i class="ri ri-eye-line"></i>
    </button>`;

    let actionBtns = '';

    if (item.request_status === 'pending') {
      actionBtns = `
        <button type="button" class="btn btn-sm btn-info order-status-btn"
            data-id="${item.service_request_id}" data-new-status="in_progress" data-url="${item.status_url}">
          <i class="ri ri-loader-4-line me-1"></i>Prepare
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger order-status-btn"
            data-id="${item.service_request_id}" data-new-status="cancelled" data-url="${item.status_url}">
          <i class="ri ri-close-line me-1"></i>Cancel
        </button>`;
    } else if (item.request_status === 'in_progress') {
      actionBtns = `
        <button type="button" class="btn btn-sm btn-primary order-status-btn"
            data-id="${item.service_request_id}" data-new-status="delivering" data-url="${item.status_url}">
          <i class="ri ri-e-bike-2-line me-1"></i>Deliver
        </button>`;
    } else if (item.request_status === 'delivering') {
      actionBtns = `
        <button type="button" class="btn btn-sm btn-success order-status-btn"
            data-id="${item.service_request_id}" data-new-status="completed" data-url="${item.status_url}">
          <i class="ri ri-checkbox-circle-line me-1"></i>Delivered
        </button>`;
    } else if (item.request_status === 'completed') {
      actionBtns = `<span class="text-success small"><i class="ri ri-check-double-line me-1"></i>Done</span>`;
    } else if (item.request_status === 'cancelled') {
      actionBtns = `<span class="text-danger small"><i class="ri ri-close-circle-line me-1"></i>Cancelled</span>`;
    }

    return `<div class="d-flex align-items-center justify-content-center gap-1">${viewBtn}${actionBtns}</div>`;
  }

  // ── Build modal footer action buttons ─────────────────────────────────
  function buildModalFooterActions(item) {
    if (item.request_status === 'pending') {
      return `
        <button type="button" class="btn btn-info order-status-btn"
            data-id="${item.service_request_id}" data-new-status="in_progress" data-url="${item.status_url}">
          <i class="ri ri-loader-4-line me-1"></i>Start Preparing
        </button>
        <button type="button" class="btn btn-outline-danger order-status-btn"
            data-id="${item.service_request_id}" data-new-status="cancelled" data-url="${item.status_url}">
          <i class="ri ri-close-line me-1"></i>Cancel Order
        </button>`;
    } else if (item.request_status === 'in_progress') {
      return `
        <div class="d-flex align-items-center gap-2 text-info small me-auto">
          <div class="spinner-border spinner-border-sm text-info" role="status"></div>
          <span>Being prepared in the kitchen…</span>
        </div>
        <button type="button" class="btn btn-primary order-status-btn"
            data-id="${item.service_request_id}" data-new-status="delivering" data-url="${item.status_url}">
          <i class="ri ri-e-bike-2-line me-1"></i>Out for Delivery
        </button>`;
    } else if (item.request_status === 'delivering') {
      return `
        <div class="d-flex align-items-center gap-2 text-primary small me-auto">
          <i class="ri ri-e-bike-2-line" style="font-size:18px;"></i>
          <span>On the way to the guest's room…</span>
        </div>
        <button type="button" class="btn btn-success order-status-btn"
            data-id="${item.service_request_id}" data-new-status="completed" data-url="${item.status_url}">
          <i class="ri ri-checkbox-circle-line me-1"></i>Mark as Delivered
        </button>`;
    } else if (item.request_status === 'completed') {
      return `<span class="text-success"><i class="ri ri-check-double-line me-1"></i>Order successfully delivered.</span>`;
    } else if (item.request_status === 'cancelled') {
      return `<span class="text-danger"><i class="ri ri-close-circle-line me-1"></i>This order was cancelled.</span>`;
    }
    return '';
  }

  // ── Sync modal from row on open ────────────────────────────────────────
  function syncModalFromRow(orderId) {
    const row   = document.querySelector(`.order-row[data-id="${orderId}"]`);
    const modal = document.getElementById(`viewOrderModal${orderId}`);
    if (!row || !modal) return;

    const currentStatus = row.dataset.status;
    const statusUrl     = row.dataset.statusUrl;
    const s = statusMap[currentStatus] || statusMap.pending;

    // Update status badge
    modal.querySelectorAll('.modal-status-badge').forEach(el => {
      el.className = `badge modal-status-badge ${s.badge} fs-6`;
      el.innerHTML = `<i class="ri ${s.icon} me-1"></i>${s.label}`;
    });

    // Re-render timeline
    const timelineEl = modal.querySelector('.modal-timeline');
    if (timelineEl) {
      timelineEl.innerHTML = buildTimeline(currentStatus);
    }

    // Rebuild footer buttons
    const footerActions = modal.querySelector('.modal-footer-actions');
    if (footerActions) {
      footerActions.innerHTML = buildModalFooterActions({
        service_request_id: orderId,
        request_status: currentStatus,
        status_url: statusUrl,
      });
      attachStatusButtons(footerActions);
    }
  }

  // ── Patch row after AJAX ───────────────────────────────────────────────
  function patchRow(row, item) {
    const s = statusMap[item.request_status] || statusMap.pending;
    row.dataset.status = item.request_status;

    const badgeTd = row.querySelector('td:nth-child(7)');
    if (badgeTd) {
      badgeTd.innerHTML = `<span class="badge ${s.badge}"><i class="ri ${s.icon} me-1"></i>${s.label}</span>`;
    }

    const actionsTd = row.querySelector('td:nth-child(8)');
    if (actionsTd) {
      actionsTd.innerHTML = buildActionCell(item);
      attachStatusButtons(actionsTd);
    }
  }

  // ── Patch modal after AJAX ─────────────────────────────────────────────
  function patchModal(item) {
    const modal = document.getElementById(`viewOrderModal${item.service_request_id}`);
    if (!modal) return;

    const s = statusMap[item.request_status] || statusMap.pending;

    modal.querySelectorAll('.modal-status-badge').forEach(el => {
      el.className = `badge modal-status-badge ${s.badge} fs-6`;
      el.innerHTML = `<i class="ri ${s.icon} me-1"></i>${s.label}`;
    });

    const timelineEl = modal.querySelector('.modal-timeline');
    if (timelineEl) {
      timelineEl.innerHTML = buildTimeline(item.request_status);
    }

    const footerActions = modal.querySelector('.modal-footer-actions');
    if (footerActions) {
      footerActions.innerHTML = buildModalFooterActions(item);
      attachStatusButtons(footerActions);
    }
  }

  // ── Update stat counters ───────────────────────────────────────────────
  function updateStats(stats) {
    statKeys.forEach(key => {
      const el = document.querySelector(`[data-stat="${key}"]`);
      if (el && stats[key] !== undefined) el.textContent = stats[key];
    });
  }

  // ── Status change handler ──────────────────────────────────────────────
  function handleStatusChange(btn) {
    const id          = btn.dataset.id;
    const newStatus   = btn.dataset.newStatus;
    const url         = btn.dataset.url;
    const parentModal = btn.closest('.modal');

    const confirmMessages = {
      in_progress: { title: 'Start preparing this order?',      icon: 'info',    confirmText: 'Yes, prepare it!' },
      delivering:  { title: 'Mark as out for delivery?',        icon: 'info',    confirmText: 'Yes, send it out!' },
      completed:   { title: 'Confirm the order was delivered?', icon: 'success', confirmText: 'Yes, delivered!' },
      cancelled:   { title: 'Cancel this order?',               icon: 'warning', confirmText: 'Yes, cancel it!', danger: true },
    };

    const cfg = confirmMessages[newStatus] || { title: 'Update status?', icon: 'question', confirmText: 'Yes' };

    Swal.fire({
      title: cfg.title,
      icon: cfg.icon,
      showCancelButton: true,
      confirmButtonText: cfg.confirmText,
      cancelButtonText: 'No, go back',
      customClass: { container: 'swal-over-modal' },
      ...(cfg.danger ? { confirmButtonColor: '#dc3545' } : {}),
    }).then(result => {
      if (!result.isConfirmed) return;

      btn.disabled = true;
      const originalHtml = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      fetch(url, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ status: newStatus }),
      })
      .then(r => r.json())
      .then(data => {
        if (data.item) {
          const row = document.querySelector(`.order-row[data-id="${id}"]`);
          if (row) patchRow(row, data.item);
          patchModal(data.item);
          if (data.stats) updateStats(data.stats);

          if (parentModal) {
            const bsModal = bootstrap.Modal.getInstance(parentModal);
            if (bsModal) bsModal.hide();
          }

          const labels = {
            in_progress: 'Order is now being prepared!',
            delivering:  'Order is out for delivery!',
            completed:   'Order has been delivered!',
            cancelled:   'Order has been cancelled.',
          };
          showSuccess(labels[newStatus] || 'Status updated.');
        } else {
          showError(data.message || 'Something went wrong.');
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      })
      .catch(() => {
        showError('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      });
    });
  }

  // ── Attach listeners ───────────────────────────────────────────────────
  function attachStatusButtons(scope = document) {
    scope.querySelectorAll('.order-status-btn').forEach(btn => {
      btn.removeEventListener('click', btn._handler);
      btn._handler = () => handleStatusChange(btn);
      btn.addEventListener('click', btn._handler);
    });
  }

  attachStatusButtons();

  // ── Sync modal on open ────────────────────────────────────────────────
  document.querySelectorAll('.modal[id^="viewOrderModal"]').forEach(modal => {
    modal.addEventListener('show.bs.modal', () => {
      const orderId = modal.id.replace('viewOrderModal', '');
      syncModalFromRow(orderId);
    });
  });

  // ── Filtering ──────────────────────────────────────────────────────────
  const searchInput  = document.getElementById('searchOrders');
  const filterStatus = document.getElementById('filterStatus');
  const filterDate   = document.getElementById('filterDate');
  const emptyState   = document.getElementById('filterEmptyState');
  const orderCountEl = document.getElementById('orderCount');

  function applyFilters() {
    const search = (searchInput?.value || '').toLowerCase().trim();
    const status = filterStatus?.value || '';
    const date   = filterDate?.value || '';
    const rows   = document.querySelectorAll('.order-row');
    let visible  = 0;

    rows.forEach(row => {
      const show =
        (!search || row.dataset.guest.includes(search) || row.dataset.room.includes(search)) &&
        (!status || row.dataset.status === status) &&
        (!date   || row.dataset.date === date);

      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (orderCountEl) orderCountEl.textContent = `${visible} order(s)`;
    if (emptyState)   emptyState.style.display  = visible > 0 ? 'none' : '';
  }

  searchInput?.addEventListener('input', applyFilters);
  filterStatus?.addEventListener('change', applyFilters);
  filterDate?.addEventListener('change', applyFilters);

  function resetFilters() {
    if (searchInput)  searchInput.value  = '';
    if (filterStatus) filterStatus.value = '';
    if (filterDate)   filterDate.value   = '';
    applyFilters();
  }

  document.getElementById('resetOrderFilters')?.addEventListener('click', resetFilters);
  document.getElementById('resetOrderFilters2')?.addEventListener('click', resetFilters);
  document.getElementById('refreshOrders')?.addEventListener('click', () => window.location.reload());

});