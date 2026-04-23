/* ============================================================
   Guest History – history_script.js
   ============================================================ */

// ── filter / search ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

  const applyBtn       = document.getElementById('applyFilters');
  const searchInput    = document.getElementById('searchInput');
  const roomTypeFilter = document.getElementById('roomTypeFilter');
  const dateFrom       = document.getElementById('dateFrom');
  const dateTo         = document.getElementById('dateTo');

  function applyFilters() {
    const params = new URLSearchParams();
    const search   = searchInput.value.trim();
    const roomType = roomTypeFilter.value;
    const from     = dateFrom.value;
    const to       = dateTo.value;

    if (search)   params.set('search',    search);
    if (roomType) params.set('room_type', roomType);
    if (from)     params.set('date_from', from);
    if (to)       params.set('date_to',   to);

    window.location.href = ROUTES.history + (params.toString() ? '?' + params.toString() : '');
  }

  if (applyBtn)    applyBtn.addEventListener('click', applyFilters);
  if (searchInput) searchInput.addEventListener('keydown', e => e.key === 'Enter' && applyFilters());

  // Bootstrap tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
    .forEach(el => new bootstrap.Tooltip(el));
});

// ── history details modal ─────────────────────────────────────
function viewHistoryDetails(record) {
  const modal   = new bootstrap.Modal(document.getElementById('historyDetailsModal'));
  const content = document.getElementById('historyDetailsContent');

  const fmtDate = d => d
    ? new Date(d).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })
    : '—';
  const fmtDateTime = d => d
    ? new Date(d).toLocaleString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })
    : '—';
  const fmtMoney = n => '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });

  const pmBadge = s => ({
    completed: 'bg-label-success',
    pending:   'bg-label-warning',
    refunded:  'bg-label-info',
  }[s] || 'bg-label-secondary');

  // Duration badge colour
  const nights    = parseInt(record.no_nights) || 0;
  let durationBadge = 'bg-label-info';
  if (nights >= 7)      durationBadge = 'bg-label-primary';
  else if (nights >= 3) durationBadge = 'bg-label-success';

  content.innerHTML = `
    <div class="row g-4">

      <!-- Guest info -->
      <div class="col-md-6">
        <div class="card border-0 bg-label-secondary h-100">
          <div class="card-body">
            <h6 class="text-primary mb-3"><i class="ri-user-3-line me-1"></i>Guest</h6>
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded-circle">
                  ${(record.first_name || '?')[0].toUpperCase()}
                </div>
              </div>
              <div>
                <p class="fw-medium mb-0">${record.first_name} ${record.last_name}</p>
                <small class="text-body-secondary">${record.email}</small>
              </div>
            </div>
            <table class="table table-sm table-borderless mb-0">
              <tr><th style="width:45%">Adults</th><td>${record.adults}</td></tr>
              <tr><th>Children</th><td>${record.children}</td></tr>
              ${record.purpose ? `<tr><th>Purpose</th><td>${record.purpose}</td></tr>` : ''}
            </table>
          </div>
        </div>
      </div>

      <!-- Room info -->
      <div class="col-md-6">
        <div class="card border-0 bg-label-secondary h-100">
          <div class="card-body">
            <h6 class="text-primary mb-3"><i class="ri-hotel-bed-line me-1"></i>Room</h6>
            <table class="table table-sm table-borderless mb-0">
              <tr><th style="width:45%">Room Number</th><td><span class="badge bg-label-secondary">${record.room_number}</span></td></tr>
              <tr><th>Room Type</th><td>${record.room_type_name}</td></tr>
              <tr><th>Rate/Night</th><td>₱${parseFloat(record.rate_per_night).toLocaleString('en-PH',{minimumFractionDigits:2})}</td></tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Stay period -->
      <div class="col-md-6">
        <div class="card border-0 bg-label-secondary h-100">
          <div class="card-body">
            <h6 class="text-primary mb-3"><i class="ri-calendar-event-line me-1"></i>Stay Period</h6>
            <table class="table table-sm table-borderless mb-0">
              <tr><th style="width:45%">Check-In</th><td>${fmtDateTime(record.check_in_at)}</td></tr>
              <tr><th>Check-Out</th><td>${fmtDate(record.check_out_date)}</td></tr>
              <tr>
                <th>Duration</th>
                <td><span class="badge ${durationBadge}">${nights} Night${nights !== 1 ? 's' : ''}</span></td>
              </tr>
              <tr><th>Reg. ID</th><td>#${record.registration_id}</td></tr>
              <tr><th>Res. ID</th><td>#${record.reservation_id}</td></tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div class="col-md-6">
        <div class="card border-0 bg-label-secondary h-100">
          <div class="card-body">
            <h6 class="text-primary mb-3"><i class="ri-money-dollar-circle-line me-1"></i>Payment Summary</h6>
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <th style="width:45%">Total Amount</th>
                <td class="fw-medium">${fmtMoney(record.total_amount)}</td>
              </tr>
              <tr>
                <th>Amount Paid</th>
                <td class="text-success fw-medium">${fmtMoney(record.amount_paid)}</td>
              </tr>
              <tr>
                <th>Balance</th>
                <td class="${record.balance > 0 ? 'text-danger fw-medium' : ''}">
                  ${record.balance > 0
                    ? fmtMoney(record.balance)
                    : '<span class="badge bg-label-success">Settled</span>'}
                </td>
              </tr>
              <tr>
                <th>Status</th>
                <td><span class="badge ${pmBadge(record.payment_status)}">${record.payment_status}</span></td>
              </tr>
              <tr>
                <th>Method</th>
                <td>${record.payment_method
                    ? record.payment_method.charAt(0).toUpperCase() + record.payment_method.slice(1)
                    : '—'}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

    </div>`;

  modal.show();
}

// ── helper: SweetAlert for future admin actions ───────────────
function confirmHistoryAction(title, text, icon, onConfirm) {
  Swal.fire({
    title,
    html: text,
    icon,
    showCancelButton: true,
    confirmButtonText: 'Confirm',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#696cff',
    reverseButtons: true,
  }).then(result => {
    if (result.isConfirmed) onConfirm();
  });
}