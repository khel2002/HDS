// Reservations Management JavaScript

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeDateCalculation();
    initializeFormValidation();
});

// Filter functionality
function initializeFilters() {
    const searchInput = document.getElementById('searchReservation');
    const statusFilter = document.getElementById('statusFilter');
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    const roomTypeFilter = document.getElementById('roomTypeFilter');

    if (searchInput) {
        searchInput.addEventListener('input', filterReservations);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterReservations);
    }

    if (dateRangeFilter) {
        dateRangeFilter.addEventListener('change', filterReservations);
    }

    if (roomTypeFilter) {
        roomTypeFilter.addEventListener('change', filterReservations);
    }
}

// Filter reservations based on search and filters
function filterReservations() {
    const searchTerm = document.getElementById('searchReservation')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const roomTypeFilter = document.getElementById('roomTypeFilter')?.value || '';
    
    const rows = document.querySelectorAll('#reservationsTableBody tr');
    
    rows.forEach(row => {
        const guestName = row.dataset.guestName || '';
        const guestEmail = row.dataset.guestEmail || '';
        const status = row.dataset.status || '';
        const roomType = row.dataset.roomType || '';
        
        const matchesSearch = guestName.includes(searchTerm) || guestEmail.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesRoomType = !roomTypeFilter || roomType === roomTypeFilter;
        
        if (matchesSearch && matchesStatus && matchesRoomType) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Reset all filters
function resetFilters() {
    document.getElementById('searchReservation').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateRangeFilter').value = '';
    document.getElementById('roomTypeFilter').value = '';
    filterReservations();
}

// Calculate total amount based on dates and room selection
function initializeDateCalculation() {
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    const roomSelect = document.getElementById('room_id');

    if (checkInInput && checkOutInput && roomSelect) {
        checkInInput.addEventListener('change', calculateTotal);
        checkOutInput.addEventListener('change', calculateTotal);
        roomSelect.addEventListener('change', calculateTotal);
    }
}

function calculateTotal() {
    const checkInDate = document.getElementById('check_in_date')?.value;
    const checkOutDate = document.getElementById('check_out_date')?.value;
    const roomSelect = document.getElementById('room_id');
    
    if (!checkInDate || !checkOutDate || !roomSelect?.value) {
        return;
    }

    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const ratePerNight = parseFloat(selectedOption.dataset.rate || 0);

    const startDate = new Date(checkInDate);
    const endDate = new Date(checkOutDate);
    
    if (endDate <= startDate) {
        document.getElementById('totalAmountDisplay').textContent = '₱0.00';
        document.getElementById('nightsDisplay').textContent = 'Check-out must be after check-in';
        return;
    }

    const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    const totalAmount = nights * ratePerNight;

    document.getElementById('totalAmountDisplay').textContent = `₱${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    document.getElementById('nightsDisplay').textContent = `${nights} night${nights > 1 ? 's' : ''} × ₱${ratePerNight.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[id$="ReservationForm"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Approve reservation
function approveReservation(reservationId) {
    Swal.fire({
        title: 'Approve Reservation?',
        text: 'Are you sure you want to approve this reservation?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28c76f',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Approving reservation',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit approval
            fetch(`/super-admin/reservations/${reservationId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: 'Reservation has been approved successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to approve reservation');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to approve reservation. Please try again.'
                });
            });
        }
    });
}

// Reject reservation
function rejectReservation(reservationId) {
    Swal.fire({
        title: 'Reject Reservation?',
        text: 'Are you sure you want to reject this reservation?',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Reason for rejection (optional)',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonColor: '#ea5455',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            // Optional validation
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Rejecting reservation',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit rejection
            fetch(`/super-admin/reservations/${reservationId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    reason: result.value || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected',
                        text: 'Reservation has been rejected.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to reject reservation');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to reject reservation. Please try again.'
                });
            });
        }
    });
}

// Cancel reservation
function cancelReservation(reservationId, guestName) {
    Swal.fire({
        title: 'Cancel Reservation?',
        html: `Are you sure you want to cancel the reservation for <strong>${guestName}</strong>?`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Reason for cancellation (optional)',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonColor: '#ea5455',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'No, Keep It'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Cancelling reservation',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit cancellation
            fetch(`/super-admin/reservations/${reservationId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    reason: result.value || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled',
                        text: 'Reservation has been cancelled successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to cancel reservation');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to cancel reservation. Please try again.'
                });
            });
        }
    });
}

// Export to Excel
function exportToExcel() {
    Swal.fire({
        icon: 'info',
        title: 'Export to Excel',
        text: 'This feature will be implemented soon.',
        timer: 2000,
        showConfirmButton: false
    });
}

// Export to PDF
function exportToPDF() {
    Swal.fire({
        icon: 'info',
        title: 'Export to PDF',
        text: 'This feature will be implemented soon.',
        timer: 2000,
        showConfirmButton: false
    });
}

// Set minimum dates for date inputs
function setMinimumDates() {
    const today = new Date().toISOString().split('T')[0];
    const checkInInputs = document.querySelectorAll('input[name="check_in_date"]');
    const checkOutInputs = document.querySelectorAll('input[name="check_out_date"]');
    
    checkInInputs.forEach(input => {
        input.setAttribute('min', today);
        
        input.addEventListener('change', function() {
            const checkInDate = this.value;
            const form = this.closest('form');
            const checkOutInput = form?.querySelector('input[name="check_out_date"]');
            
            if (checkOutInput && checkInDate) {
                const minCheckOut = new Date(checkInDate);
                minCheckOut.setDate(minCheckOut.getDate() + 1);
                checkOutInput.setAttribute('min', minCheckOut.toISOString().split('T')[0]);
                
                // Clear check-out if it's before the new check-in
                if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(checkInDate)) {
                    checkOutInput.value = '';
                }
            }
        });
    });
}

// Call on page load
document.addEventListener('DOMContentLoaded', setMinimumDates);

// Handle form submission for add reservation
document.getElementById('addReservationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading
    Swal.fire({
        title: 'Creating Reservation...',
        text: 'Please wait',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Submit the form
    this.submit();
});

// Auto-hide success/error messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('searchReservation')?.focus();
    }
    
    // Ctrl/Cmd + N to open new reservation modal
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const addModal = new bootstrap.Modal(document.getElementById('addReservationModal'));
        addModal.show();
    }
});