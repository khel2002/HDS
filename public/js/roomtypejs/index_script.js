console.log('Room Type management script loaded');

// Delete confirmation with SweetAlert2 - GLOBAL FUNCTION
window.confirmDelete = function(roomTypeId, roomTypeName, roomCount) {
  console.log('confirmDelete called:', roomTypeId, roomTypeName, roomCount);

  if (roomCount > 0) {
    Swal.fire({
      title: 'Cannot Delete!',
      html: `<strong>${roomTypeName}</strong> has <strong>${roomCount}</strong> room(s) associated with it.<br><br>Please delete or reassign those rooms first.`,
      icon: 'error',
      confirmButtonColor: '#696cff',
      confirmButtonText: 'OK'
    });
    return;
  }

  Swal.fire({
    title: 'Delete Room Type?',
    html: `Are you sure you want to delete <strong>${roomTypeName}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#8592a3',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      // Create and submit delete form
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/super-admin/room-types/${roomTypeId}`;

      const csrfToken = document.createElement('input');
      csrfToken.type = 'hidden';
      csrfToken.name = '_token';
      csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      const methodField = document.createElement('input');
      methodField.type = 'hidden';
      methodField.name = '_method';
      methodField.value = 'DELETE';

      form.appendChild(csrfToken);
      form.appendChild(methodField);
      document.body.appendChild(form);
      form.submit();
    }
  });
}

// DOM Ready event listeners
document.addEventListener('DOMContentLoaded', function() {

  // Search functionality
  const searchInput = document.getElementById('searchRoomType');
  const tableBody = document.getElementById('roomTypesTableBody');
  const rows = tableBody ? tableBody.querySelectorAll('tr') : [];

  if (searchInput && rows.length > 0) {
    searchInput.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();

      rows.forEach(row => {
        const roomTypeName = row.getAttribute('data-room-type-name') || '';
        const textContent = row.textContent.toLowerCase();

        if (roomTypeName.includes(searchTerm) || textContent.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Sort functionality
  const sortFilter = document.getElementById('sortFilter');

  if (sortFilter && tableBody && rows.length > 0) {
    sortFilter.addEventListener('change', function() {
      const sortBy = this.value;
      const rowsArray = Array.from(rows);

      rowsArray.sort((a, b) => {
        switch(sortBy) {
          case 'name':
            const nameA = a.getAttribute('data-room-type-name') || '';
            const nameB = b.getAttribute('data-room-type-name') || '';
            return nameA.localeCompare(nameB);

          case 'rate_asc':
            const rateA = parseFloat(a.getAttribute('data-rate')) || 0;
            const rateB = parseFloat(b.getAttribute('data-rate')) || 0;
            return rateA - rateB;

          case 'rate_desc':
            const rateDescA = parseFloat(a.getAttribute('data-rate')) || 0;
            const rateDescB = parseFloat(b.getAttribute('data-rate')) || 0;
            return rateDescB - rateDescA;

          case 'capacity':
            const capA = parseInt(a.getAttribute('data-capacity')) || 0;
            const capB = parseInt(a.getAttribute('data-capacity')) || 0;
            return capB - capA;

          default:
            return 0;
        }
      });

      // Reorder the rows in the table
      rowsArray.forEach(row => tableBody.appendChild(row));
    });
  }

  // Handle Add Room Type Form submission
  const addForm = document.getElementById('addRoomTypeForm');

  if (addForm) {
    addForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const modal = document.getElementById('addRoomTypeModal');
      const modalInstance = modal ? bootstrap.Modal.getInstance(modal) : null;

      // Close modal first
      if (modalInstance) {
        modalInstance.hide();
      }

      // Show confirmation
      setTimeout(() => {
        Swal.fire({
          title: 'Add Room Type?',
          text: 'Are you sure you want to add this room type?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#696cff',
          cancelButtonColor: '#8592a3',
          confirmButtonText: 'Yes, add it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            addForm.submit();
          } else {
            // Reopen modal if cancelled
            if (modalInstance) {
              modalInstance.show();
            }
          }
        });
      }, 300);
    });
  }

  // Handle Edit Room Type Forms submission
  const editForms = document.querySelectorAll('form[action*="/room-types/"][method="POST"]');

  editForms.forEach(form => {
    const methodInput = form.querySelector('input[name="_method"]');

    // Only handle PUT method forms (edit forms)
    if (methodInput && methodInput.value === 'PUT') {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        const modal = form.closest('.modal');
        const modalInstance = modal ? bootstrap.Modal.getInstance(modal) : null;

        // Close modal first
        if (modalInstance) {
          modalInstance.hide();
        }

        // Show confirmation
        setTimeout(() => {
          Swal.fire({
            title: 'Save Changes?',
            text: 'Are you sure you want to update this room type?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, save it!',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              form.submit();
            } else {
              // Reopen modal if cancelled
              if (modalInstance) {
                modalInstance.show();
              }
            }
          });
        }, 300);
      });
    }
  });
});
