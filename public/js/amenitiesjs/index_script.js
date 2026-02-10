
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchAmenity');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('amenitiesTableBody');

    if (!searchInput || !statusFilter || !tableBody) {
        return;
    }

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const rows = tableBody.getElementsByTagName('tr');

        for (let row of rows) {
            const amenityName = row.getAttribute('data-amenity-name') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesSearch = amenityName.includes(searchTerm);
            const matchesStatus = statusValue === '' || status === statusValue;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }


    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);
});


function confirmDelete(amenityId, amenityName, roomCount) {
    let confirmMessage = '';
    let confirmIcon = 'warning';

    if (roomCount > 0) {
        confirmMessage = `"${amenityName}" is currently used in ${roomCount} room(s). Deleting this amenity will remove it from all rooms.\n\nAre you sure you want to continue?`;
        confirmIcon = 'warning';
    } else {
        confirmMessage = `Are you sure you want to delete "${amenityName}"?`;
        confirmIcon = 'question';
    }

    Swal.fire({
        title: 'Are you sure?',
        text: confirmMessage,
        icon: confirmIcon,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteAmenity(amenityId);
        }
    });
}


function deleteAmenity(amenityId) {

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/super-admin/amenities/${amenityId}`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;

    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';

    form.appendChild(csrfInput);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
}


document.addEventListener('DOMContentLoaded', function() {

    const successMessage = document.querySelector('[data-success-message]');
    const errorMessage = document.querySelector('[data-error-message]');

    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: successMessage.getAttribute('data-success-message'),
            timer: 3000,
            showConfirmButton: false
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: errorMessage.getAttribute('data-error-message'),
            confirmButtonText: 'OK'
        });
    }
});
