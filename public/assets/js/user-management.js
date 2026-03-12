/**
 * User Management JavaScript
 * Handles all user CRUD operations and modal interactions
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
  // Helper: Get role name from role_id
  // ========================================
  function getRoleName(roleId) {
    const roles = { 1: 'Guest', 2: 'Staff', 3: 'Admin', 4: 'Super Admin' };
    return roles[String(roleId)] || 'N/A';
  }

  // ========================================
  // Helper: Find a table row by user ID
  // ========================================
  function findRow(userId) {
    return document.querySelector(`tr[data-user-id="${userId}"]`);
  }

  // ========================================
  // Helper: Update stat cards in-place
  // e.g. updateStats('total', +1) / updateStats('active', -1)
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
      document.querySelectorAll('tbody tr[data-user-id]').forEach(row => {
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
      document.querySelectorAll('tbody tr[data-user-id]').forEach(row => {
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
  // View User Details
  // ========================================
  window.viewUser = function (userId, firstName, middleName, lastName, email, role, status, createdAt, updatedAt) {
    // Populate header section
    const fullName = `${firstName} ${middleName ? middleName + ' ' : ''}${lastName}`;
    document.getElementById('viewUserFullName').textContent = fullName;
    document.getElementById('viewUserEmailHeader').textContent = email || '-';

    // Status badge in header
    const statusBadge = document.getElementById('viewUserStatusBadge');
    const statusLower = status.toLowerCase();
    let badgeClass = 'bg-success';

    if (statusLower === 'inactive') {
      badgeClass = 'bg-warning';
    } else if (statusLower === 'suspended') {
      badgeClass = 'bg-danger';
    }

    statusBadge.className = `badge rounded-pill ${badgeClass}`;
    statusBadge.textContent = status;

    // Avatar initials
    const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
    document.getElementById('viewUserAvatar').textContent = initials;

    // Populate details section
    document.getElementById('viewFirstName').textContent = firstName || '-';
    document.getElementById('viewMiddleName').textContent = middleName || '-';
    document.getElementById('viewLastName').textContent = lastName || '-';
    document.getElementById('viewEmail').textContent = email || '-';
    document.getElementById('viewRole').textContent = role || '-';

    // Status with badge in details
    const statusContainer = document.getElementById('viewStatus');
    const statusClass = statusLower === 'active' ? 'bg-success' : statusLower === 'inactive' ? 'bg-warning' : 'bg-danger';
    statusContainer.innerHTML = `<span class="badge ${statusClass}">${status}</span>`;

    document.getElementById('viewJoinedDate').textContent = createdAt || '-';
    document.getElementById('viewUpdatedDate').textContent = updatedAt || '-';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    modal.show();
  };

  // ========================================
  // Edit User — open modal
  // ========================================
  window.editUser = function (userId, roleId, email, firstName, middleName, lastName) {
    document.getElementById('editUserId').value = userId;
    document.getElementById('editRoleId').value = roleId;
    document.getElementById('editEmail').value = email;
    document.getElementById('editFirstName').value = firstName;
    document.getElementById('editMiddleName').value = middleName || '';
    document.getElementById('editLastName').value = lastName;
    document.getElementById('editPassword').value = '';

    new bootstrap.Modal(document.getElementById('editUserModal')).show();
  };

  // Edit User — form submit
  const editUserForm = document.getElementById('editUserForm');
  if (editUserForm) {
    editUserForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const userId = document.getElementById('editUserId').value;

      fetch(`/admin/users/${userId}/update`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();

            const row = findRow(userId);
            const firstName = document.getElementById('editFirstName').value;
            const lastName = document.getElementById('editLastName').value;
            const email = document.getElementById('editEmail').value;
            const roleId = document.getElementById('editRoleId').value;
            const middleName = document.getElementById('editMiddleName').value;
            const roleName = getRoleName(roleId);

            if (row) {
              row.querySelector('.avatar-initial').textContent = (
                firstName.charAt(0) + lastName.charAt(0)
              ).toUpperCase();
              row.querySelector('.user-name').textContent = `${firstName} ${lastName}`;
              row.querySelector('.user-email').textContent = email;

              const currentStatus = row.querySelector('.badge').textContent.trim();

              const viewBtn = row.querySelector('[title="View Details"]');
              if (viewBtn) {
                viewBtn.setAttribute(
                  'onclick',
                  `viewUser('${userId}','${firstName}','${middleName}','${lastName}','${email}',` +
                    `'${roleName}','${currentStatus}','${row.cells[4].textContent.trim()}','${new Date().toLocaleString()}')`
                );
              }

              const editBtn = row.querySelector('[title="Edit"]');
              if (editBtn) {
                editBtn.setAttribute(
                  'onclick',
                  `editUser('${userId}','${roleId}','${email}','${firstName}','${middleName}','${lastName}')`
                );
              }
            }

            showAlert('success', 'User updated successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to update user');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while updating the user'));
    });
  }

  // ========================================
  // Change Status — open modal
  // ========================================
  window.changeStatus = function (userId, userName, currentStatus) {
    document.getElementById('statusUserId').value = userId;
    document.getElementById('statusUserName').textContent = userName;
    document.getElementById('newStatus').value = currentStatus.toLowerCase();

    new bootstrap.Modal(document.getElementById('changeStatusModal')).show();
  };

  // Change Status — form submit
  const changeStatusForm = document.getElementById('changeStatusForm');
  if (changeStatusForm) {
    changeStatusForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const userId = document.getElementById('statusUserId').value;
      const newStatus = document.getElementById('newStatus').value;

      fetch(`/admin/users/${userId}/status`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('changeStatusModal')).hide();

            const row = findRow(userId);
            if (row) {
              const badge = row.querySelector('.badge');
              const oldStatus = badge.textContent.trim().toLowerCase();

              badge.className = `badge ${getStatusClass(newStatus)}`;
              badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

              const statusLink = row.querySelector('[onclick^="changeStatus"]');
              if (statusLink) {
                const userName = row.querySelector('.user-name').textContent.trim();
                statusLink.setAttribute('onclick', `changeStatus('${userId}','${userName}','${newStatus}')`);
              }

              // ── Update stat cards ──────────────────────────────────────
              updateStats(oldStatus, -1);
              updateStats(newStatus, +1);
              // ──────────────────────────────────────────────────────────
            }

            showAlert('success', 'Status updated successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to update status');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while updating the status'));
    });
  }

  // ========================================
  // Delete User — open modal
  // ========================================
  window.deleteUser = function (userId, userName) {
    const modalEl = document.getElementById('deleteUserModal');
    const existing = bootstrap.Modal.getInstance(modalEl);
    if (existing) existing.dispose();

    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;

    new bootstrap.Modal(modalEl).show();
  };

  // Delete User — form submit
  const deleteUserForm = document.getElementById('deleteUserForm');
  if (deleteUserForm) {
    deleteUserForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const userId = document.getElementById('deleteUserId').value;
      const submitBtn = this.querySelector('[type="submit"]');

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

      const formData = new FormData(this);

      fetch(`/admin/users/${userId}/delete`, {
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
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
            if (modalInstance) modalInstance.hide();

            const row = findRow(userId);
            if (row) {
              // Grab the status BEFORE removing the row
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
                if (tbody && tbody.querySelectorAll('tr[data-user-id]').length === 0) {
                  const emptyRow = document.createElement('tr');
                  emptyRow.id = 'no-data-row';
                  emptyRow.innerHTML = '<td colspan="6" class="text-center py-3">No users found</td>';
                  tbody.appendChild(emptyRow);
                }
              }, 300);
            }

            showAlert('success', data.message || 'User deleted successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to delete user');
          }
        })
        .catch(err => {
          console.error('Delete error:', err);
          showAlert('danger', 'An error occurred while deleting the user');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="icon-base ri ri-delete-bin-line me-1"></i>Delete User';
        });
    });
  }

  // ========================================
  // Add New User — form submit
  // ========================================
  const addUserForm = document.querySelector('#addUserModal form');
  if (addUserForm) {
    addUserForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('/admin/users', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            this.reset();

            const user = data.user;
            const userId = user.user_id;
            const firstName = user.first_name;
            const lastName = user.last_name;
            const email = user.email;
            const roleId = user.role_id;
            const roleName = getRoleName(roleId);
            const status = user.STATUS || 'active';
            const createdAt = user.created_at;
            const middleName = user.middle_name || '';
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

            const noDataRow = document.getElementById('no-data-row');
            if (noDataRow) noDataRow.remove();

            const tbody = document.querySelector('tbody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-user-id', userId);
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
                    onclick="viewUser('${userId}','${firstName}','${middleName}','${lastName}','${email}','${roleName}','${status}','${createdAt}','${createdAt}')">
                    <i class="icon-base ri ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit"
                    onclick="editUser('${userId}','${roleId}','${email}','${firstName}','${middleName}','${lastName}')">
                    <i class="icon-base ri ri-edit-line"></i>
                  </button>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                      data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                      <i class="icon-base ri ri-more-2-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="javascript:void(0);"
                        onclick="changeStatus('${userId}','${firstName} ${lastName}','${status}')">
                        <i class="icon-base ri ri-refresh-line me-2"></i>
                        Change Status
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="javascript:void(0);"
                        onclick="deleteUser('${userId}','${firstName} ${lastName}')">
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

            showAlert('success', 'User added successfully!');
          } else {
            showAlert('danger', data.message || 'Failed to add user');
          }
        })
        .catch(() => showAlert('danger', 'An error occurred while adding the user'));
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
