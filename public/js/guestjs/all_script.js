/* ============================================================
   All Guests – all_script.js
   ============================================================ */

// ── helpers ──────────────────────────────────────────────────
function url(template, id) {
  return template.replace(':id', id);
}

function csrfHeaders() {
  return {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': ROUTES.csrfToken,
  };
}

// ── filter / search ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

  const applyBtn     = document.getElementById('applyFilters');
  const searchInput  = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');

  function applyFilters() {
    const params = new URLSearchParams();
    const search = searchInput.value.trim();
    const status = statusFilter.value;
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    window.location.href = ROUTES.all + (params.toString() ? '?' + params.toString() : '');
  }

  if (applyBtn)    applyBtn.addEventListener('click', applyFilters);
  if (searchInput) searchInput.addEventListener('keydown', e => e.key === 'Enter' && applyFilters());

  // Bootstrap tooltips
  const tips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tips.forEach(el => new bootstrap.Tooltip(el));
});

// ── view guest profile ────────────────────────────────────────
function viewGuestProfile(userId) {
  const modal   = new bootstrap.Modal(document.getElementById('guestProfileModal'));
  const content = document.getElementById('guestProfileContent');

  content.innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
      </div>
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
            <td>${s.check_in_at ? new Date(s.check_in_at).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'}) : '—'}</td>
            <td>${s.check_out_date ? new Date(s.check_out_date).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'}) : '<span class="badge bg-label-success">In-House</span>'}</td>
            <td>${s.no_nights}N</td>
            <td>₱${parseFloat(s.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
            <td><span class="badge bg-label-${s.payment_status === 'completed' ? 'success' : s.payment_status === 'pending' ? 'warning' : 'info'}">${s.payment_status}</span></td>
          </tr>`).join('')
        : `<tr><td colspan="7" class="text-center text-body-secondary">No stay records yet.</td></tr>`;

      content.innerHTML = `
        <div class="row g-4">
          <div class="col-md-4 text-center">
            <div class="avatar avatar-xl mb-3 mx-auto">
              <div class="avatar-initial bg-label-primary rounded-circle" style="font-size:2rem;width:80px;height:80px;line-height:80px;">
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
              <tr><th>Date of Birth</th><td>${guestDetails?.dob ? new Date(guestDetails.dob).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric'}) : '—'}</td></tr>
              <tr><th>Registered</th><td>${new Date(guest.created_at).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric'})}</td></tr>
              <tr><th>Last Login</th><td>${guest.last_login_at ? new Date(guest.last_login_at).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric'}) : '—'}</td></tr>
            </table>
          </div>
        </div>
        <hr>
        <h6 class="mb-3">Stay History (${stays.length} record${stays.length !== 1 ? 's' : ''})</h6>
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th>Room</th><th>Type</th><th>Check-In</th><th>Check-Out</th>
                <th>Nights</th><th>Total</th><th>Payment</th>
              </tr>
            </thead>
            <tbody>${staysHtml}</tbody>
          </table>
        </div>`;
    })
    .catch(() => {
      content.innerHTML = `
        <div class="alert alert-danger m-3">
          <i class="ri-error-warning-line me-2"></i>
          Failed to load guest profile. Please try again.
        </div>`;
    });
}

// ── toggle status ─────────────────────────────────────────────
function toggleGuestStatus(userId, currentStatus, guestName) {
  const newStatus  = currentStatus === 'active' ? 'inactive' : 'active';
  const actionWord = newStatus === 'active' ? 'Activate' : 'Deactivate';
  const iconColor  = newStatus === 'active' ? 'success' : 'warning';

  Swal.fire({
    title: `${actionWord} Guest?`,
    html: `Are you sure you want to <strong>${actionWord.toLowerCase()}</strong> the account of<br><strong>${guestName}</strong>?`,
    icon: 'question',
    iconColor: `var(--bs-${iconColor})`,
    showCancelButton: true,
    confirmButtonText: `Yes, ${actionWord}`,
    cancelButtonText: 'Cancel',
    confirmButtonColor: newStatus === 'active' ? '#28c76f' : '#ff9f43',
    reverseButtons: true,
  }).then(result => {
    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Updating…',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    fetch(url(ROUTES.updateStatus, userId), {
      method: 'PATCH',
      headers: csrfHeaders(),
      body: JSON.stringify({ status: newStatus }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: data.message,
            confirmButtonColor: '#696cff',
          }).then(() => window.location.reload());
        } else {
          throw new Error(data.message || 'Update failed.');
        }
      })
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: err.message || 'Something went wrong.',
          confirmButtonColor: '#696cff',
        });
      });
  });
}

// ── delete (deactivate) guest ─────────────────────────────────
function deleteGuest(userId, guestName, currentStatus) {
  if (currentStatus === 'inactive') {
    Swal.fire({
      icon: 'info',
      title: 'Already Inactive',
      text: `${guestName}'s account is already deactivated.`,
      confirmButtonColor: '#696cff',
    });
    return;
  }

  Swal.fire({
    title: 'Deactivate Account?',
    html: `This will deactivate <strong>${guestName}'s</strong> account.<br>
           <small class="text-muted">The guest will no longer be able to log in. This can be reversed.</small>`,
    icon: 'warning',
    iconColor: '#ff4c51',
    showCancelButton: true,
    confirmButtonText: 'Yes, Deactivate',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#ff4c51',
    reverseButtons: true,
    input: 'checkbox',
    inputValue: 0,
    inputPlaceholder: 'I understand this action will disable the guest account.',
    preConfirm: (checked) => {
      if (!checked) {
        Swal.showValidationMessage('Please confirm you understand this action.');
      }
      return checked;
    },
  }).then(result => {
    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Processing…',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    fetch(url(ROUTES.destroy, userId), {
      method: 'DELETE',
      headers: csrfHeaders(),
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Deactivated',
            text: data.message,
            confirmButtonColor: '#696cff',
          }).then(() => window.location.reload());
        } else {
          throw new Error(data.message || 'Operation failed.');
        }
      })
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Cannot Deactivate',
          text: err.message || 'Something went wrong.',
          confirmButtonColor: '#696cff',
        });
      });
  });
}