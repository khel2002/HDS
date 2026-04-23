/* ============================================================
   Current Guests – current_script.js
   ============================================================ */

function url(template, id) {
  return template.replace(':id', id);
}

// ── filter / search ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

  const applyBtn       = document.getElementById('applyFilters');
  const searchInput    = document.getElementById('searchInput');
  const roomTypeFilter = document.getElementById('roomTypeFilter');

  function applyFilters() {
    const params = new URLSearchParams();
    const search   = searchInput.value.trim();
    const roomType = roomTypeFilter.value;
    if (search)   params.set('search',    search);
    if (roomType) params.set('room_type', roomType);
    window.location.href = ROUTES.current + (params.toString() ? '?' + params.toString() : '');
  }

  if (applyBtn)    applyBtn.addEventListener('click', applyFilters);
  if (searchInput) searchInput.addEventListener('keydown', e => e.key === 'Enter' && applyFilters());

  // Bootstrap tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
    .forEach(el => new bootstrap.Tooltip(el));
});

// ── stay details modal ────────────────────────────────────────
function viewStayDetails(guest) {
  const modal   = new bootstrap.Modal(document.getElementById('stayDetailsModal'));
  const content = document.getElementById('stayDetailsContent');

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

  const checkoutDate = new Date(guest.check_out_date);
  const today        = new Date();
  today.setHours(0, 0, 0, 0);
  const isToday   = checkoutDate.toDateString() === today.toDateString();
  const isOverdue = checkoutDate < today && !isToday;

  let checkoutBadge = `<span class="text-body-secondary">${fmtDate(guest.check_out_date)}</span>`;
  if (isOverdue)  checkoutBadge = `<span class="badge bg-danger">OVERDUE — ${fmtDate(guest.check_out_date)}</span>`;
  if (isToday)    checkoutBadge = `<span class="badge bg-warning text-dark">Today — ${fmtDate(guest.check_out_date)}</span>`;

  content.innerHTML = `
    <div class="row g-4">
      <!-- Guest -->
      <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="ri-user-3-line me-1"></i>Guest Information</h6>
        <table class="table table-sm table-borderless mb-0">
          <tr><th style="width:40%">Name</th><td>${guest.first_name} ${guest.last_name}</td></tr>
          <tr><th>Email</th><td>${guest.email}</td></tr>
          <tr><th>Contact</th><td>${guest.contact_number || '—'}</td></tr>
          <tr><th>Adults</th><td>${guest.adults}</td></tr>
          <tr><th>Children</th><td>${guest.children}</td></tr>
        </table>
      </div>
      <!-- Room -->
      <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="ri-hotel-bed-line me-1"></i>Room Details</h6>
        <table class="table table-sm table-borderless mb-0">
          <tr><th style="width:40%">Room</th><td><span class="badge bg-label-primary">${guest.room_number}</span></td></tr>
          <tr><th>Type</th><td>${guest.room_type_name}</td></tr>
          <tr><th>Rate/Night</th><td>₱${parseFloat(guest.rate_per_night).toLocaleString('en-PH',{minimumFractionDigits:2})}</td></tr>
          <tr><th>Nights</th><td>${guest.no_nights}</td></tr>
        </table>
      </div>
      <!-- Stay -->
      <div class="col-12"><hr class="my-1"></div>
      <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="ri-calendar-line me-1"></i>Stay Period</h6>
        <table class="table table-sm table-borderless mb-0">
          <tr><th style="width:40%">Check-In</th><td>${fmtDateTime(guest.check_in_at)}</td></tr>
          <tr><th>Check-Out</th><td>${checkoutBadge}</td></tr>
          <tr><th>Registration</th><td>#${guest.registration_id}</td></tr>
          <tr><th>Reservation</th><td>#${guest.reservation_id}</td></tr>
        </table>
      </div>
      <!-- Payment -->
      <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="ri-money-dollar-circle-line me-1"></i>Payment</h6>
        <table class="table table-sm table-borderless mb-0">
          <tr><th style="width:40%">Total Amount</th><td class="fw-medium">${fmtMoney(guest.total_amount)}</td></tr>
          <tr><th>Balance</th><td class="${guest.balance > 0 ? 'text-danger fw-medium' : ''}">
            ${guest.balance > 0 ? fmtMoney(guest.balance) : '<span class="badge bg-label-success">Settled</span>'}
          </td></tr>
          <tr><th>Status</th><td><span class="badge ${pmBadge(guest.payment_status)}">${guest.payment_status}</span></td></tr>
          <tr><th>Method</th><td>${guest.payment_method ? (guest.payment_method.charAt(0).toUpperCase() + guest.payment_method.slice(1)) : '—'}</td></tr>
        </table>
      </div>
    </div>`;

  modal.show();
}

// ── view guest full profile ───────────────────────────────────
function viewGuestProfile(userId) {
  const modal   = new bootstrap.Modal(document.getElementById('guestProfileModal'));
  const content = document.getElementById('guestProfileContent');

  content.innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>`;

  modal.show();

  fetch(url(ROUTES.show, userId))
    .then(r => r.json())
    .then(data => {
      const { guest, stays, guestDetails } = data;
      const fullName = [guest.first_name, guest.middle_name, guest.last_name]
        .filter(Boolean).join(' ') || '(No name)';

      const staysHtml = stays.length
        ? stays.map(s => `
          <tr>
            <td>${s.room_number}</td>
            <td>${s.room_type_name}</td>
            <td>${s.check_in_at ? new Date(s.check_in_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}) : '—'}</td>
            <td>${s.check_out_date ? new Date(s.check_out_date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}) : '<span class="badge bg-label-success">In-House</span>'}</td>
            <td>${s.no_nights}N</td>
            <td>₱${parseFloat(s.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
          </tr>`).join('')
        : `<tr><td colspan="6" class="text-center text-body-secondary">No stay records yet.</td></tr>`;

      content.innerHTML = `
        <div class="row g-4">
          <div class="col-md-4 text-center">
            <div class="avatar avatar-xl mb-3 mx-auto">
              <div class="avatar-initial bg-label-success rounded-circle"
                   style="font-size:2rem;width:80px;height:80px;line-height:80px;">
                ${(guest.first_name || '?')[0].toUpperCase()}
              </div>
            </div>
            <h5 class="mb-1">${fullName}</h5>
            <p class="text-body-secondary mb-2">${guest.email}</p>
            <span class="badge ${guest.STATUS === 'active' ? 'bg-label-success' : 'bg-label-danger'}">
              ${guest.STATUS || 'N/A'}
            </span>
          </div>
          <div class="col-md-8">
            <h6 class="mb-3 text-primary">Account Details</h6>
            <table class="table table-sm table-borderless mb-4">
              <tr><th style="width:35%">User ID</th><td>#${guest.user_id}</td></tr>
              <tr><th>Contact</th><td>${guestDetails?.contact_number || '—'}</td></tr>
              <tr><th>Registered</th><td>${new Date(guest.created_at).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric'})}</td></tr>
            </table>
          </div>
        </div>
        <hr>
        <h6 class="mb-3">Stay History (${stays.length} record${stays.length !== 1 ? 's' : ''})</h6>
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead><tr><th>Room</th><th>Type</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Total</th></tr></thead>
            <tbody>${staysHtml}</tbody>
          </table>
        </div>`;
    })
    .catch(() => {
      content.innerHTML = `
        <div class="alert alert-danger m-3">
          <i class="ri-error-warning-line me-2"></i>Failed to load guest profile.
        </div>`;
    });
}