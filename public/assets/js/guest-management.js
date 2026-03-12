/**
 * Guest Management JavaScript
 * Handles all guest user CRUD operations with guest_details table
 * Updates the table AND stat cards in-place — no full page reload needed
 */

document.addEventListener('DOMContentLoaded', function () {
  // ========================================
  // CSRF Token
  // ========================================
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // ========================================
  // Helper: Get status badge class
  // ========================================
  function getStatusClass(status) {
    switch (status.toLowerCase()) {
      case 'active':
        return 'bg-success';
      case 'inactive':
        return 'bg-warning';
      case 'suspended':
        return 'bg-danger';
      default:
        return 'bg-secondary';
    }
  }

  // ========================================
  // Helper: Find a table row by guest ID
  // ========================================
  function findRow(guestId) {
    return document.querySelector(`tr[data-guest-id="${guestId}"]`);
  }

  // ========================================
  // Helper: Update stat cards in-place
  // ========================================
  function updateStats(statKey, delta) {
    const el = document.getElementById(`stat-${statKey}`);
    if (!el) return;
    const current = parseInt(el.textContent, 10) || 0;
    el.textContent = Math.max(0, current + delta);
  }

  // ========================================
  // Search Functionality
  // ========================================
  const searchInput = document.getElementById('searchTable');
  if (searchInput) {
    searchInput.addEventListener('keyup', function () {
      const searchValue = this.value.toLowerCase();
      document.querySelectorAll('tbody tr[data-guest-id]').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchValue) ? '' : 'none';
      });
    });
  }

  // ========================================
  // Filter by Status
  // ========================================
  const filterStatus = document.getElementById('filterStatus');
  if (filterStatus) {
    filterStatus.addEventListener('change', function () {
      const filterValue = this.value.toLowerCase();
      document.querySelectorAll('tbody tr[data-guest-id]').forEach(row => {
        if (filterValue === '') {
          row.style.display = '';
        } else {
          const badge = row.querySelector('.badge');
          row.style.display = badge && badge.textContent.trim().toLowerCase() === filterValue ? '' : 'none';
        }
      });
    });
  }

  // ========================================
  // View Guest Details
  // ========================================
  window.viewGuest = function (guestId, firstName, middleName, lastName, email, contactNumber, dob, status, createdAt, updatedAt) {
    document.getElementById('viewGuestFirstName').textContent = firstName || '-';
    document.getElementById('viewGuestMiddleName').textContent = middleName || '-';
    document.getElementById('viewGuestLastName').textContent = lastName || '-';
    document.getElementById('viewGuestEmail').textContent = email || '-';
    document.getElementById('viewGuestContactNumber').textContent = contactNumber || '-';
    document.getElementById('viewGuestDob').textContent = dob || '-';
    document.getElementById('viewGuestJoinedDate').textContent = createdAt || '-';
    document.getElementById('viewGuestUpdatedDate').textContent = updatedAt || '-';

    document.getElementById('viewGuestStatus').innerHTML = `<span class="badge ${getStatusClass(status)}">${status}</span>`;

    document.getElementById('viewGuestAvatar').textContent = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

    new bootstrap.Modal(document.getElementById('viewGuestModal')).show();
  };

  // ========================================
  // Edit Guest — open modal
  // ========================================
  window.editGuest = function (guestId, email, firstName, middleName, lastName, contactNumber, dob) {
    document.getElementById('editGuestId').value = guestId;
    document.getElementById('editGuestEmail').value = email;
    document.getElementById('editGuestFirstName').value = firstName;
    document.getElementById('editGuestMiddleName').value = middleName || '';
    document.getElementById('editGuestLastName').value = lastName;
    document.getElementById('editGuestContactNumber').value = contactNumber;
    document.getElementById('editGuestDob').value = dob;
    document.getElementById('editGuestPassword').value = '';

    new bootstrap.Modal(document.getElementById('editGuestModal')).show();
  };

  // Edit Guest — form submit
  const editGuestForm = document.getElementById('editGuestForm');
  if (editGuestForm) {
    editGuestForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const guestId = document.getElementById('editGuestId').value;

      fetch(`/admin/guests/${guestId}/update`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editGuestModal')).hide();

            const row = findRow(guestId);
            const firstName = document.getElementById('editGuestFirstName').value;
            const lastName = document.getElementById('editGuestLastName').value;
            const email = document.getElementById('editGuestEmail').value;
            const middleName = document.getElementById('editGuestMiddleName').value;
            const contactNumber = document.getElementById('editGuestContactNumber').value;
            const dob = document.getElementById('editGuestDob').value;

            if (row) {
              row.querySelector('.avatar-initial').textContent = (
                firstName.charAt(0) + lastName.charAt(0)
              ).toUpperCase();
              row.querySelector('.user-name').textContent = `${firstName} ${lastName}`;
              row.querySelector('.user-email').textContent = email;
              row.querySelector('.user-contact').textContent = contactNumber;

              const currentStatus = row.querySelector('.badge').textContent.trim();

              const viewBtn = row.querySelector('[title="View Details"]');
              if (viewBtn) {
                viewBtn.setAttribute(
                  'onclick',
                  `viewGuest('${guestId}','${firstName}','${middleName}','${lastName}','${email}','${contactNumber}','${dob}',` +
                    `'${currentStatus}','${row.cells[5].textContent.trim()}','${new Date().toLocaleString()}')`
                );
              }

              const editBtn = row.querySelector('[title="Edit"]');
              if (editBtn) {
                editBtn.setAttribute(
                  'onclick',
                  `editGuest('${guestId}','${email}','${firstName}','${middleName}','${lastName}','${contactNumber}','${dob}')`
                );
              }
            }

            showAlert('success', 'Guest user updated successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to update guest user');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while updating the guest user'));
    });
  }

  // ========================================
  // Change Guest Status — open modal
  // ========================================
  window.changeGuestStatus = function (guestId, userName, currentStatus) {
    document.getElementById('statusGuestId').value = guestId;
    document.getElementById('statusGuestName').textContent = userName;
    document.getElementById('newGuestStatus').value = currentStatus.toLowerCase();

    new bootstrap.Modal(document.getElementById('changeGuestStatusModal')).show();
  };

  // Change Guest Status — form submit
  const changeGuestStatusForm = document.getElementById('changeGuestStatusForm');
  if (changeGuestStatusForm) {
    changeGuestStatusForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const guestId = document.getElementById('statusGuestId').value;
      const newStatus = document.getElementById('newGuestStatus').value;

      fetch(`/admin/guests/${guestId}/status`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('changeGuestStatusModal')).hide();

            const row = findRow(guestId);
            if (row) {
              const badge = row.querySelector('.badge');
              if (badge) {
                badge.className = `badge ${getStatusClass(newStatus)}`;
                badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
              }

              // Update onclick attributes
              const statusBtn = row.querySelector('[onclick*="changeGuestStatus"]');
              if (statusBtn) {
                const currentOnclick = statusBtn.getAttribute('onclick');
                const updatedOnclick = currentOnclick.replace(
                  /'[^']*'\)$/,
                  `'${newStatus}')`
                );
                statusBtn.setAttribute('onclick', updatedOnclick);
              }

              const viewBtn = row.querySelector('[onclick*="viewGuest"]');
              if (viewBtn) {
                const currentOnclick = viewBtn.getAttribute('onclick');
                const parts = currentOnclick.split("'");
                parts[15] = newStatus; // Update status parameter
                viewBtn.setAttribute('onclick', parts.join("'"));
              }
            }

            // ── Update stat cards ────────────────────────────────
            if (data.old_status && data.new_status) {
              updateStats(data.old_status, -1);
              updateStats(data.new_status, +1);
            }
            // ────────────────────────────────────────────────────

            showAlert('success', data.message || 'Guest status updated successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to update guest status');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while updating the guest status'));
    });
  }

  // ========================================
  // Delete Guest — open modal
  // ========================================
  window.deleteGuest = function (guestId, userName) {
    document.getElementById('deleteGuestId').value = guestId;
    document.getElementById('deleteGuestName').textContent = userName;

    new bootstrap.Modal(document.getElementById('deleteGuestModal')).show();
  };

  // Delete Guest — form submit
  const deleteGuestForm = document.getElementById('deleteGuestForm');
  if (deleteGuestForm) {
    deleteGuestForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

      const formData = new FormData(this);
      const guestId = document.getElementById('deleteGuestId').value;

      fetch(`/admin/guests/${guestId}/delete`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => {
          if (!res.ok && res.headers.get('content-type')?.includes('text/html')) {
            throw new Error(`Server error: ${res.status}`);
          }
          return res.json();
        })
        .then(data => {
          if (data.success) {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteGuestModal'));
            if (modalInstance) modalInstance.hide();

            const row = findRow(guestId);
            if (row) {
              const deletedStatus = row.querySelector('.badge').textContent.trim().toLowerCase();

              row.style.transition = 'opacity 0.3s ease';
              row.style.opacity = '0';

              setTimeout(() => {
                row.remove();

                // ── Update stat cards ──────────────────────────────────
                updateStats('total', -1);
                updateStats(deletedStatus, -1);
                // ──────────────────────────────────────────────────────

                const tbody = document.querySelector('tbody');
                if (tbody && tbody.querySelectorAll('tr[data-guest-id]').length === 0) {
                  const emptyRow = document.createElement('tr');
                  emptyRow.id = 'no-data-row';
                  emptyRow.innerHTML = '<td colspan="7" class="text-center py-3">No guest users found</td>';
                  tbody.appendChild(emptyRow);
                }
              }, 300);
            }

            showAlert('success', data.message || 'Guest user deleted successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to delete guest user');
          }
        })
        .catch(err => {
          console.error('Delete error:', err);
          showAlert('danger', 'An error occurred while deleting the guest user');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="icon-base ri ri-delete-bin-line me-1"></i>Delete Guest User';
        });
    });
  }

  // ========================================
  // Add New Guest — form submit
  // ========================================
  const addGuestForm = document.querySelector('#addGuestModal form');
  if (addGuestForm) {
    addGuestForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('/admin/guests', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addGuestModal')).hide();
            this.reset();

            const guest = data.guest;
            const guestId = guest.guest_details_id;
            const firstName = guest.first_name;
            const lastName = guest.last_name;
            const email = guest.user.email;
            const contactNumber = guest.contact_number;
            const dob = guest.dob;
            const status = guest.user.STATUS || 'active';
            const createdAt = guest.user.created_at;
            const middleName = guest.middle_name || '';
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

            const noDataRow = document.getElementById('no-data-row');
            if (noDataRow) noDataRow.remove();

            const tbody = document.querySelector('tbody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-guest-id', guestId);
            newRow.style.opacity = '0';
            newRow.style.transition = 'opacity 0.3s ease';

            newRow.innerHTML = `
              <td>
                <div class="avatar avatar-sm">
                  <span class="avatar-initial rounded-circle bg-label-primary">${initials}</span>
                </div>
              </td>
              <td class="user-name">${firstName} ${lastName}</td>
              <td class="user-email">${email}</td>
              <td class="user-contact">${contactNumber}</td>
              <td class="text-center align-middle">
                <span class="badge ${getStatusClass(status)}">
                  ${status.charAt(0).toUpperCase() + status.slice(1)}
                </span>
              </td>
              <td class="text-center align-middle">${createdAt}</td>
              <td class="text-center align-middle">
                <div class="d-flex justify-content-center gap-1">
                  <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                    title="View Details"
                    onclick="viewGuest('${guestId}','${firstName}','${middleName}','${lastName}','${email}','${contactNumber}','${dob}','${status}','${createdAt}','${createdAt}')">
                    <i class="icon-base ri ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit"
                    onclick="editGuest('${guestId}','${email}','${firstName}','${middleName}','${lastName}','${contactNumber}','${dob}')">
                    <i class="icon-base ri ri-edit-line"></i>
                  </button>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                      data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                      <i class="icon-base ri ri-more-2-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="javascript:void(0);"
                        onclick="changeGuestStatus('${guestId}','${firstName} ${lastName}','${status}')">
                        <i class="icon-base ri ri-refresh-line me-2"></i>
                        Change Status
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="javascript:void(0);"
                        onclick="deleteGuest('${guestId}','${firstName} ${lastName}')">
                        <i class="icon-base ri ri-delete-bin-line me-2"></i>
                        Delete Account
                      </a>
                    </div>
                  </div>
                </div>
              </td>
            `;

            tbody.appendChild(newRow);
            requestAnimationFrame(() => {
              newRow.style.opacity = '1';
            });

            // ── Update stat cards ──────────────────────────────────────
            updateStats('total', +1);
            updateStats(status.toLowerCase(), +1);
            // ──────────────────────────────────────────────────────────

            showAlert('success', 'Guest user added successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to add guest user');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while adding the guest user'));
    });
  }

  // ========================================
  // Alert Helper
  // ========================================
  function showAlert(type, message) {
    const existing = document.querySelector('.alert-notification');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show alert-notification`;
    alert.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;';
    alert.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alert);

    setTimeout(() => {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 150);
    }, 5000);
  }
});/**
 * Guest Management JavaScript
 * Handles all guest user CRUD operations with guest_details table
 * Updates the table AND stat cards in-place — no full page reload needed
 */

document.addEventListener('DOMContentLoaded', function () {
  // ========================================
  // CSRF Token
  // ========================================
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // ========================================
  // Helper: Get status badge class
  // ========================================
  function getStatusClass(status) {
    switch (status.toLowerCase()) {
      case 'active':
        return 'bg-success';
      case 'inactive':
        return 'bg-warning';
      case 'suspended':
        return 'bg-danger';
      default:
        return 'bg-secondary';
    }
  }

  // ========================================
  // Helper: Find a table row by guest ID
  // ========================================
  function findRow(guestId) {
    return document.querySelector(`tr[data-guest-id="${guestId}"]`);
  }

  // ========================================
  // Helper: Update stat cards in-place
  // ========================================
  function updateStats(statKey, delta) {
    const el = document.getElementById(`stat-${statKey}`);
    if (!el) return;
    const current = parseInt(el.textContent, 10) || 0;
    el.textContent = Math.max(0, current + delta);
  }

  // ========================================
  // Search Functionality
  // ========================================
  const searchInput = document.getElementById('searchTable');
  if (searchInput) {
    searchInput.addEventListener('keyup', function () {
      const searchValue = this.value.toLowerCase();
      document.querySelectorAll('tbody tr[data-guest-id]').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchValue) ? '' : 'none';
      });
    });
  }

  // ========================================
  // Filter by Status
  // ========================================
  const filterStatus = document.getElementById('filterStatus');
  if (filterStatus) {
    filterStatus.addEventListener('change', function () {
      const filterValue = this.value.toLowerCase();
      document.querySelectorAll('tbody tr[data-guest-id]').forEach(row => {
        if (filterValue === '') {
          row.style.display = '';
        } else {
          const badge = row.querySelector('.badge');
          row.style.display = badge && badge.textContent.trim().toLowerCase() === filterValue ? '' : 'none';
        }
      });
    });
  }

  // ========================================
  // View Guest Details
  // ========================================
 window.viewGuest = function (guestId, firstName, middleName, lastName, email, contactNumber, dob, status, createdAt, updatedAt) {

  // Header section
  const fullName = `${firstName} ${middleName ? middleName + ' ' : ''}${lastName}`;
  document.getElementById('viewGuestFullName').textContent = fullName;
  document.getElementById('viewGuestEmail').textContent = email || '-';

  // Header status badge
  const statusBadge = document.getElementById('viewGuestStatusBadge');
  statusBadge.className = `badge rounded-pill ${getStatusClass(status)}`;
  statusBadge.textContent = status;

  // Avatar initials
  document.getElementById('viewGuestAvatar').textContent = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

  // Detail rows
  document.getElementById('viewGuestFirstName').textContent    = firstName     || '-';
  document.getElementById('viewGuestMiddleName').textContent   = middleName    || '-';
  document.getElementById('viewGuestLastName').textContent     = lastName      || '-';
  document.getElementById('viewGuestEmailBody').textContent    = email         || '-';
  document.getElementById('viewGuestContactNumber').textContent = contactNumber || '-';
  document.getElementById('viewGuestDob').textContent          = dob           || '-';
  document.getElementById('viewGuestJoinedDate').textContent   = createdAt     || '-';
  document.getElementById('viewGuestUpdatedDate').textContent  = updatedAt     || '-';

  // Status badge in detail row
  document.getElementById('viewGuestStatus').innerHTML =
    `<span class="badge ${getStatusClass(status)}">${status}</span>`;

  new bootstrap.Modal(document.getElementById('viewGuestModal')).show();
};



  // ========================================
  // Edit Guest — open modal
  // ========================================
  window.editGuest = function (guestId, email, firstName, middleName, lastName, contactNumber, dob) {
    document.getElementById('editGuestId').value = guestId;
    document.getElementById('editGuestEmail').value = email;
    document.getElementById('editGuestFirstName').value = firstName;
    document.getElementById('editGuestMiddleName').value = middleName || '';
    document.getElementById('editGuestLastName').value = lastName;
    document.getElementById('editGuestContactNumber').value = contactNumber;
    document.getElementById('editGuestDob').value = dob;
    document.getElementById('editGuestPassword').value = '';

    new bootstrap.Modal(document.getElementById('editGuestModal')).show();
  };

  // Edit Guest — form submit
  const editGuestForm = document.getElementById('editGuestForm');
  if (editGuestForm) {
    editGuestForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const guestId = document.getElementById('editGuestId').value;

      fetch(`/admin/guests/${guestId}/update`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editGuestModal')).hide();

            const row = findRow(guestId);
            const firstName = document.getElementById('editGuestFirstName').value;
            const lastName = document.getElementById('editGuestLastName').value;
            const email = document.getElementById('editGuestEmail').value;
            const middleName = document.getElementById('editGuestMiddleName').value;
            const contactNumber = document.getElementById('editGuestContactNumber').value;
            const dob = document.getElementById('editGuestDob').value;

            if (row) {
              row.querySelector('.avatar-initial').textContent = (
                firstName.charAt(0) + lastName.charAt(0)
              ).toUpperCase();
              row.querySelector('.user-name').textContent = `${firstName} ${lastName}`;
              row.querySelector('.user-email').textContent = email;
              row.querySelector('.user-contact').textContent = contactNumber;

              const currentStatus = row.querySelector('.badge').textContent.trim();

              const viewBtn = row.querySelector('[title="View Details"]');
              if (viewBtn) {
                viewBtn.setAttribute(
                  'onclick',
                  `viewGuest('${guestId}','${firstName}','${middleName}','${lastName}','${email}','${contactNumber}','${dob}',` +
                    `'${currentStatus}','${row.cells[5].textContent.trim()}','${new Date().toLocaleString()}')`
                );
              }

              const editBtn = row.querySelector('[title="Edit"]');
              if (editBtn) {
                editBtn.setAttribute(
                  'onclick',
                  `editGuest('${guestId}','${email}','${firstName}','${middleName}','${lastName}','${contactNumber}','${dob}')`
                );
              }
            }

            showAlert('success', 'Guest user updated successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to update guest user');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while updating the guest user'));
    });
  }

  // ========================================
  // Change Guest Status — open modal
  // ========================================
  window.changeGuestStatus = function (guestId, userName, currentStatus) {
    document.getElementById('statusGuestId').value = guestId;
    document.getElementById('statusGuestName').textContent = userName;
    document.getElementById('newGuestStatus').value = currentStatus.toLowerCase();

    new bootstrap.Modal(document.getElementById('changeGuestStatusModal')).show();
  };

  // Change Guest Status — form submit
  const changeGuestStatusForm = document.getElementById('changeGuestStatusForm');
  if (changeGuestStatusForm) {
    changeGuestStatusForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const guestId = document.getElementById('statusGuestId').value;
      const newStatus = document.getElementById('newGuestStatus').value;

      fetch(`/admin/guests/${guestId}/status`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('changeGuestStatusModal')).hide();

            const row = findRow(guestId);
            if (row) {
              const badge = row.querySelector('.badge');
              if (badge) {
                badge.className = `badge ${getStatusClass(newStatus)}`;
                badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
              }

              // Update onclick attributes
              const statusBtn = row.querySelector('[onclick*="changeGuestStatus"]');
              if (statusBtn) {
                const currentOnclick = statusBtn.getAttribute('onclick');
                const updatedOnclick = currentOnclick.replace(
                  /'[^']*'\)$/,
                  `'${newStatus}')`
                );
                statusBtn.setAttribute('onclick', updatedOnclick);
              }

              const viewBtn = row.querySelector('[onclick*="viewGuest"]');
              if (viewBtn) {
                const currentOnclick = viewBtn.getAttribute('onclick');
                const parts = currentOnclick.split("'");
                parts[15] = newStatus; // Update status parameter
                viewBtn.setAttribute('onclick', parts.join("'"));
              }
            }

            // ── Update stat cards ────────────────────────────────
            if (data.old_status && data.new_status) {
              updateStats(data.old_status, -1);
              updateStats(data.new_status, +1);
            }
            // ────────────────────────────────────────────────────

            showAlert('success', data.message || 'Guest status updated successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to update guest status');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while updating the guest status'));
    });
  }

  // ========================================
  // Delete Guest — open modal
  // ========================================
  window.deleteGuest = function (guestId, userName) {
    document.getElementById('deleteGuestId').value = guestId;
    document.getElementById('deleteGuestName').textContent = userName;

    new bootstrap.Modal(document.getElementById('deleteGuestModal')).show();
  };

  // Delete Guest — form submit
  const deleteGuestForm = document.getElementById('deleteGuestForm');
  if (deleteGuestForm) {
    deleteGuestForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

      const formData = new FormData(this);
      const guestId = document.getElementById('deleteGuestId').value;

      fetch(`/admin/guests/${guestId}/delete`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => {
          if (!res.ok && res.headers.get('content-type')?.includes('text/html')) {
            throw new Error(`Server error: ${res.status}`);
          }
          return res.json();
        })
        .then(data => {
          if (data.success) {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteGuestModal'));
            if (modalInstance) modalInstance.hide();

            const row = findRow(guestId);
            if (row) {
              const deletedStatus = row.querySelector('.badge').textContent.trim().toLowerCase();

              row.style.transition = 'opacity 0.3s ease';
              row.style.opacity = '0';

              setTimeout(() => {
                row.remove();

                // ── Update stat cards ──────────────────────────────────
                updateStats('total', -1);
                updateStats(deletedStatus, -1);
                // ──────────────────────────────────────────────────────

                const tbody = document.querySelector('tbody');
                if (tbody && tbody.querySelectorAll('tr[data-guest-id]').length === 0) {
                  const emptyRow = document.createElement('tr');
                  emptyRow.id = 'no-data-row';
                  emptyRow.innerHTML = '<td colspan="7" class="text-center py-3">No guest users found</td>';
                  tbody.appendChild(emptyRow);
                }
              }, 300);
            }

            showAlert('success', data.message || 'Guest user deleted successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to delete guest user');
          }
        })
        .catch(err => {
          console.error('Delete error:', err);
          showAlert('danger', 'An error occurred while deleting the guest user');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="icon-base ri ri-delete-bin-line me-1"></i>Delete Guest User';
        });
    });
  }

  // ========================================
  // Add New Guest — form submit
  // ========================================
  const addGuestForm = document.querySelector('#addGuestModal form');
  if (addGuestForm) {
    addGuestForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('/admin/guests', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addGuestModal')).hide();
            this.reset();

            const guest = data.guest;
            const guestId = guest.guest_details_id;
            const firstName = guest.first_name;
            const lastName = guest.last_name;
            const email = guest.user.email;
            const contactNumber = guest.contact_number;
            const dob = guest.dob;
            const status = guest.user.STATUS || 'active';
            const createdAt = guest.user.created_at;
            const middleName = guest.middle_name || '';
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

            const noDataRow = document.getElementById('no-data-row');
            if (noDataRow) noDataRow.remove();

            const tbody = document.querySelector('tbody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-guest-id', guestId);
            newRow.style.opacity = '0';
            newRow.style.transition = 'opacity 0.3s ease';

            newRow.innerHTML = `
              <td>
                <div class="avatar avatar-sm">
                  <span class="avatar-initial rounded-circle bg-label-primary">${initials}</span>
                </div>
              </td>
              <td class="user-name">${firstName} ${lastName}</td>
              <td class="user-email">${email}</td>
              <td class="user-contact">${contactNumber}</td>
              <td class="text-center align-middle">
                <span class="badge ${getStatusClass(status)}">
                  ${status.charAt(0).toUpperCase() + status.slice(1)}
                </span>
              </td>
              <td class="text-center align-middle">${createdAt}</td>
              <td class="text-center align-middle">
                <div class="d-flex justify-content-center gap-1">
                  <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                    title="View Details"
                    onclick="viewGuest('${guestId}','${firstName}','${middleName}','${lastName}','${email}','${contactNumber}','${dob}','${status}','${createdAt}','${createdAt}')">
                    <i class="icon-base ri ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit"
                    onclick="editGuest('${guestId}','${email}','${firstName}','${middleName}','${lastName}','${contactNumber}','${dob}')">
                    <i class="icon-base ri ri-edit-line"></i>
                  </button>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                      data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                      <i class="icon-base ri ri-more-2-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="javascript:void(0);"
                        onclick="changeGuestStatus('${guestId}','${firstName} ${lastName}','${status}')">
                        <i class="icon-base ri ri-refresh-line me-2"></i>
                        Change Status
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="javascript:void(0);"
                        onclick="deleteGuest('${guestId}','${firstName} ${lastName}')">
                        <i class="icon-base ri ri-delete-bin-line me-2"></i>
                        Delete Account
                      </a>
                    </div>
                  </div>
                </div>
              </td>
            `;

            tbody.appendChild(newRow);
            requestAnimationFrame(() => {
              newRow.style.opacity = '1';
            });

            // ── Update stat cards ──────────────────────────────────────
            updateStats('total', +1);
            updateStats(status.toLowerCase(), +1);
            // ──────────────────────────────────────────────────────────

            showAlert('success', 'Guest user added successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to add guest user');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while adding the guest user'));
    });
  }

  // ========================================
  // Alert Helper
  // ========================================
  function showAlert(type, message) {
    const existing = document.querySelector('.alert-notification');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show alert-notification`;
    alert.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;';
    alert.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alert);

    setTimeout(() => {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 150);
    }, 5000);
  }
});
