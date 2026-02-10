
function filterByRoomType(roomTypeId) {

    const baseUrl = window.location.origin + '/super-admin/rooms';

    if (roomTypeId && roomTypeId !== '') {
        window.location.href = `${baseUrl}?room_type=${roomTypeId}`;
    } else {
        window.location.href = baseUrl;
    }
}


document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTable');
    const statusFilter = document.getElementById('filterStatus');
    const tableBody = document.getElementById('roomsTableBody');

    if (!searchInput || !statusFilter || !tableBody) {
        return;
    }

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const rows = tableBody.getElementsByTagName('tr');

        for (let row of rows) {
            const roomNumber = row.getAttribute('data-room-number') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesSearch = roomNumber.includes(searchTerm);
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


function confirmDelete(roomId, roomNumber, status) {
    if (status === 'occupied') {
        Swal.fire({
            icon: 'error',
            title: 'Cannot Delete',
            text: 'Cannot delete an occupied room. Please change the status first.',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete Room ${roomNumber}. This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/super-admin/rooms/${roomId}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}


function updateRoomTypeDescription(prefix) {
    const selectId = prefix === 'add' ? 'room_type_id' : `edit_room_type_id_${prefix.replace('edit_', '')}`;
    const infoId = `${prefix}_room_type_info`;

    const select = document.getElementById(selectId);
    const infoElement = document.getElementById(infoId);

    if (!select || !infoElement) {
        return;
    }

    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption.value) {
        const rate = selectedOption.getAttribute('data-rate');
        const pax = selectedOption.getAttribute('data-pax');

        infoElement.textContent = `Rate: ₱${rate}/night | Max: ${pax} guest(s)`;
    } else {
        infoElement.textContent = 'Please select a room type';
    }
}


function updateDescription(prefix) {
    const checkboxClass = prefix === 'add' ? 'amenity-checkbox' : `amenity-checkbox-${prefix.replace('edit_', '')}`;
    const descriptionId = `${prefix}_auto_description`;

    const checkboxes = document.querySelectorAll(`.${checkboxClass}:checked`);
    const descriptionElement = document.getElementById(descriptionId);

    if (!descriptionElement) {
        return;
    }

    if (checkboxes.length === 0) {
        descriptionElement.textContent = 'No amenities selected yet. Select amenities above to auto-generate a description.';
        return;
    }

    const amenityNames = Array.from(checkboxes).map(cb => {
        const label = document.querySelector(`label[for="${cb.id}"]`);
        return label ? label.textContent.trim() : '';
    }).filter(name => name);

    if (amenityNames.length > 0) {
        descriptionElement.textContent = `This room features: ${amenityNames.join(', ')}.`;
    }
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
