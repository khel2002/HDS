let currentStep = 1;
const totalSteps = 4;
let selectedRooms = [];


document.addEventListener('DOMContentLoaded', function() {
  // Check if returning from successful payment
  const urlParams = new URLSearchParams(window.location.search);
  const paymentSuccess = urlParams.get('payment_success');

  if (paymentSuccess === '1') {
    currentStep = 4;
  }

  // Initialize with the initial room
  if (window.reservationData && window.reservationData.initialRoom) {
    selectedRooms.push(window.reservationData.initialRoom);
  }

  initializeCheckout();
  setupEventListeners();
  updatePricing();
  updateStepDisplay();
  updateSidebarRooms();

  if (paymentSuccess === '1') {
    Swal.fire({
      icon: 'success',
      title: 'Payment Successful!',
      text: 'Your reservation has been confirmed. Check your email for login credentials.',
      confirmButtonColor: '#8b5cf6'
    });
  }
});


function initializeCheckout() {
  const cashInput = document.getElementById('cashPaymentInput');
  if (cashInput) {
    cashInput.disabled = false;
  }
  const onlineInput = document.getElementById('onlinePaymentInput');
  if (onlineInput) {
    onlineInput.disabled = true;
  }
}


function setupEventListeners() {
  const arrivalDate = document.getElementById('arrivalDate');
  const departureDate = document.getElementById('departureDate');
  const adults = document.getElementById('adults');
  const children = document.getElementById('children');

  if (arrivalDate) arrivalDate.addEventListener('change', updatePricing);
  if (departureDate) departureDate.addEventListener('change', updatePricing);
  if (adults) adults.addEventListener('input', updatePricing);
  if (children) children.addEventListener('input', updatePricing);

  document.querySelectorAll('.payment-tab').forEach(tab => {
    tab.addEventListener('click', function() {
      const paymentType = this.dataset.payment;
      switchPaymentMethod(paymentType);
    });
  });

  const guestFields = ['first_name', 'last_name', 'email', 'contact_number'];
  guestFields.forEach(field => {
    const input = document.querySelector(`input[name="${field}"]`);
    if (input) {
      input.addEventListener('blur', updateGuestDisplay);
      input.addEventListener('input', debounce(updateGuestDisplay, 500));
    }
  });

  document.querySelectorAll('.step').forEach((step, index) => {
    step.addEventListener('click', function() {
      const stepNumber = index + 1;
      if (stepNumber < currentStep) {
        goToStep(stepNumber);
      }
    });
  });

  const nextBtn = document.getElementById('nextStepBtn');
  const backBtn = document.getElementById('backBtn');

  if (nextBtn) {
    nextBtn.addEventListener('click', nextStep);
  }

  if (backBtn) {
    backBtn.addEventListener('click', prevStep);
  }
}


async function showAvailableRooms() {
  const availableSection = document.getElementById('availableRoomsSection');
  const availableList = document.getElementById('availableRoomsList');
  
  // Get date values for availability check
  const arrivalDate = document.getElementById('arrivalDate')?.value;
  const departureDate = document.getElementById('departureDate')?.value;
  
  if (!arrivalDate || !departureDate) {
    Swal.fire({
      icon: 'warning',
      title: 'Select Dates First',
      text: 'Please select check-in and checkout dates in the Details section before adding more rooms.',
      confirmButtonColor: '#8b5cf6'
    });
    // Jump to step 2 to set dates
    goToStep(2);
    return;
  }

  try {
    // Show loading
    availableList.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="ri-loader-4-line" style="font-size: 2rem; animation: spin 1s linear infinite;"></i><p>Loading available rooms...</p></div>';
    availableSection.style.display = 'block';

    const response = await fetch(window.reservationData.getAvailableRoomsUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.reservationData.csrfToken
      },
      body: JSON.stringify({
        arrival_date: arrivalDate,
        departure_date: departureDate,
        exclude_room_ids: selectedRooms.map(r => r.room_id)
      })
    });

    const data = await response.json();

    if (data.rooms && data.rooms.length > 0) {
      availableList.innerHTML = data.rooms.map(room => `
        <div class="available-room-card">
          <img src="${room.image_path}" alt="${room.room_type_name}" class="available-room-image">
          <div class="available-room-info">
            <h4>${room.room_type_name}</h4>
            <div class="room-meta" style="margin: 0.5rem 0;">
              <span><i class="ri-door-line"></i> Room ${room.room_number}</span>
              <span><i class="ri-user-line"></i> Up to ${room.max_pax} guests</span>
            </div>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0.5rem 0;">${room.description}</p>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
              <div class="room-price">₱${formatNumber(room.rate_per_night)} <span style="font-size: 0.75rem; font-weight: 400;">per night</span></div>
              <button type="button" class="btn-add-room" onclick="addRoom(${room.room_id}, '${room.room_number}', '${room.room_type_name}', '${room.description}', ${room.rate_per_night}, ${room.max_pax}, '${room.image_path}')">
                <i class="ri-add-line"></i> Add
              </button>
            </div>
          </div>
        </div>
      `).join('');
    } else {
      availableList.innerHTML = '<div style="text-align: center; padding: 2rem; color: #64748b;"><i class="ri-information-line" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><p>No additional rooms available for your selected dates.</p></div>';
    }

  } catch (error) {
    console.error('Error fetching available rooms:', error);
    availableList.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;"><i class="ri-error-warning-line" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><p>Failed to load available rooms. Please try again.</p></div>';
  }
}


function addRoom(roomId, roomNumber, roomTypeName, description, ratePerNight, maxPax, imagePath) {
  // Check if room already selected
  if (selectedRooms.find(r => r.room_id === roomId)) {
    Swal.fire({
      icon: 'info',
      title: 'Already Added',
      text: 'This room is already in your selection.',
      confirmButtonColor: '#8b5cf6'
    });
    return;
  }

  const room = {
    room_id: roomId,
    room_number: roomNumber,
    room_type_name: roomTypeName,
    description: description,
    rate_per_night: ratePerNight,
    max_pax: maxPax,
    image_path: imagePath
  };

  selectedRooms.push(room);

  // Add to selected rooms list
  const selectedList = document.getElementById('selectedRoomsList');
  const removeBtn = selectedRooms.length > 1 ? '' : 'style="display: none;"';
  
  selectedList.innerHTML += `
    <div class="room-card selected-room" data-room-id="${roomId}" data-rate="${ratePerNight}">
      <img src="${imagePath}" alt="${roomTypeName}" class="room-image">
      <div class="room-details">
        <h3 class="room-name">${roomTypeName}</h3>
        <div class="room-meta">
          <span><i class="ri-door-line"></i> Room ${roomNumber}</span>
          <span><i class="ri-user-line"></i> Up to ${maxPax} guests</span>
          <span class="free-badge">Available</span>
        </div>
        <p style="color: #64748b; font-size: 0.875rem; margin: 0.5rem 0;">${description}</p>
        <div class="room-price">₱${formatNumber(ratePerNight)} <span style="font-size: 0.875rem; color: #64748b; font-weight: 400;">per night</span></div>
      </div>
      <button type="button" class="remove-room-btn" onclick="removeRoom(this)">
        <i class="ri-close-line"></i>
      </button>
    </div>
  `;

  // Show remove buttons for all rooms if more than 1
  if (selectedRooms.length > 1) {
    document.querySelectorAll('.remove-room-btn').forEach(btn => {
      btn.style.display = 'flex';
    });
  }

  updateSidebarRooms();
  updatePricing();

  // Show success message
  Swal.fire({
    icon: 'success',
    title: 'Room Added!',
    text: `${roomTypeName} (Room ${roomNumber}) has been added to your reservation.`,
    timer: 2000,
    showConfirmButton: false
  });

  // Remove from available list
  const availableList = document.getElementById('availableRoomsList');
  showAvailableRooms(); // Refresh the list
}


function removeRoom(button) {
  const roomCard = button.closest('.selected-room');
  const roomId = parseInt(roomCard.dataset.roomId);

  // Don't allow removing the last room
  if (selectedRooms.length <= 1) {
    Swal.fire({
      icon: 'warning',
      title: 'Cannot Remove',
      text: 'You must have at least one room in your reservation.',
      confirmButtonColor: '#8b5cf6'
    });
    return;
  }

  Swal.fire({
    title: 'Remove Room?',
    text: 'Are you sure you want to remove this room from your reservation?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, remove it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      // Remove from array
      selectedRooms = selectedRooms.filter(r => r.room_id !== roomId);
      
      // Remove from DOM
      roomCard.remove();

      // Hide remove buttons if only 1 room left
      if (selectedRooms.length === 1) {
        document.querySelectorAll('.remove-room-btn').forEach(btn => {
          btn.style.display = 'none';
        });
      }

      updateSidebarRooms();
      updatePricing();

      Swal.fire({
        icon: 'success',
        title: 'Room Removed',
        timer: 1500,
        showConfirmButton: false
      });

      // Refresh available rooms if section is open
      const availableSection = document.getElementById('availableRoomsSection');
      if (availableSection.style.display === 'block') {
        showAvailableRooms();
      }
    }
  });
}


function updateSidebarRooms() {
  const sidebarList = document.getElementById('sidebarRoomsList');
  const roomCount = document.getElementById('roomCount');
  
  roomCount.textContent = selectedRooms.length;

  sidebarList.innerHTML = selectedRooms.map(room => `
    <div class="sidebar-room-item">
      <img src="${room.image_path}" alt="${room.room_type_name}">
      <div>
        <div style="font-weight: 600; font-size: 0.875rem;">${room.room_type_name}</div>
        <div style="font-size: 0.75rem; color: #64748b;">Room ${room.room_number}</div>
      </div>
    </div>
  `).join('');
}


function updatePricing() {
  const arrival = document.getElementById('arrivalDate')?.value;
  const departure = document.getElementById('departureDate')?.value;
  const adults = parseInt(document.getElementById('adults')?.value) || 0;
  const children = parseInt(document.getElementById('children')?.value) || 0;

  if (!arrival || !departure || selectedRooms.length === 0) return;

  const arrivalDate = new Date(arrival);
  const departureDate = new Date(departure);
  const nights = Math.ceil((departureDate - arrivalDate) / (1000 * 60 * 60 * 24));

  if (nights > 0) {
    // Calculate total for all rooms
    const totalRoomCost = selectedRooms.reduce((sum, room) => sum + room.rate_per_night, 0);
    const subtotal = totalRoomCost * nights;
    const reservationFeePerRoom = 500;
    const totalReservationFee = reservationFeePerRoom * selectedRooms.length;
    const total = subtotal + totalReservationFee;
    const balance = total - totalReservationFee;

    // Update all pricing displays
    updateElementText('totalRoomsCount', selectedRooms.length);
    updateElementText('priceNights', nights);
    updateElementText('subtotal', '₱' + formatNumber(subtotal));
    updateElementText('roomCountForFee', selectedRooms.length);
    updateElementText('totalReservationFee', '₱' + formatNumber(totalReservationFee));
    updateElementText('totalAmount', '₱' + formatNumber(total));
    updateElementText('payNowAmount', '₱' + formatNumber(totalReservationFee));
    updateElementText('balanceAmount', '₱' + formatNumber(balance));
    updateElementText('balanceAmountText2', '₱' + formatNumber(balance));
    updateElementText('totalReservationFeeText', '₱' + formatNumber(totalReservationFee));
  }
}


function updateGuestDisplay() {
  const firstName = document.querySelector('input[name="first_name"]')?.value || '';
  const lastName = document.querySelector('input[name="last_name"]')?.value || '';
  const email = document.querySelector('input[name="email"]')?.value || '';
  const phone = document.querySelector('input[name="contact_number"]')?.value || '';

  if (firstName || lastName) {
    updateElementText('guestNameDisplay', `${firstName} ${lastName}`.trim());
  }
  if (email) {
    updateElementText('guestEmailDisplay', email);
  }
  if (phone) {
    updateElementText('guestPhoneDisplay', `Mobile: ${phone}`);
  }
}


function switchPaymentMethod(method) {
  document.querySelectorAll('.payment-tab').forEach(tab => {
    tab.classList.remove('active');
  });
  const activeTab = document.querySelector(`[data-payment="${method}"]`);
  if (activeTab) {
    activeTab.classList.add('active');
  }

  document.querySelectorAll('.payment-content').forEach(content => {
    content.classList.remove('active');
  });
  const activeContent = document.getElementById(`${method}-payment`);
  if (activeContent) {
    activeContent.classList.add('active');
  }

  const cashInput = document.getElementById('cashPaymentInput');
  const onlineInput = document.getElementById('onlinePaymentInput');

  if (cashInput) cashInput.disabled = method !== 'cash';
  if (onlineInput) onlineInput.disabled = method !== 'online';

  const paymentText = method === 'cash' ? 'upon check-in' : 'online through your account';
  updateElementText('paymentMethodText', paymentText);
}


function nextStep() {
  if (currentStep < totalSteps) {
    if (!validateStep(currentStep)) {
      return;
    }

    currentStep++;
    updateStepDisplay();
  } else {
    handleFinalSubmission();
  }
}


function prevStep() {
  if (currentStep > 1) {
    currentStep--;
    updateStepDisplay();
  }
}


function goToStep(step) {
  if (step >= 1 && step <= totalSteps && step < currentStep) {
    currentStep = step;
    updateStepDisplay();
  }
}


function validateStep(step) {
  if (step === 1) {
    // Validate room selection
    if (selectedRooms.length === 0) {
      showAlert('warning', 'No Rooms Selected', 'Please select at least one room for your reservation');
      return false;
    }
  } else if (step === 2) {
    // Validate dates and guests
    const arrival = document.getElementById('arrivalDate')?.value;
    const departure = document.getElementById('departureDate')?.value;
    const adults = parseInt(document.getElementById('adults')?.value) || 0;

    if (!arrival || !departure) {
      showAlert('warning', 'Missing Information', 'Please select check-in and checkout dates');
      return false;
    }

    const arrivalDate = new Date(arrival);
    const departureDate = new Date(departure);

    if (departureDate <= arrivalDate) {
      showAlert('warning', 'Invalid Dates', 'Checkout date must be after check-in date');
      return false;
    }

    if (adults < 1) {
      showAlert('warning', 'Missing Information', 'At least one adult is required');
      return false;
    }

    // Validate guest details
    const requiredFields = [
      { name: 'first_name', label: 'First Name' },
      { name: 'last_name', label: 'Last Name' },
      { name: 'email', label: 'Email' },
      { name: 'contact_number', label: 'Contact Number' },
      { name: 'dob', label: 'Date of Birth' }
    ];

    for (const field of requiredFields) {
      const input = document.querySelector(`input[name="${field.name}"]`);
      if (!input || !input.value.trim()) {
        showAlert('warning', 'Missing Information', `Please fill in your ${field.label}`);
        if (input) input.focus();
        return false;
      }
    }

    // Validate email format
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput && !isValidEmail(emailInput.value)) {
      showAlert('warning', 'Invalid Email', 'Please enter a valid email address');
      emailInput.focus();
      return false;
    }

    updateGuestDisplay();

  } else if (step === 3) {
    // Validate payment method selection
    const activePaymentMethod = document.querySelector('.payment-tab.active')?.dataset.payment;

    if (!activePaymentMethod) {
      showAlert('warning', 'Payment Required', 'Please select a payment method');
      return false;
    }

    if (activePaymentMethod === 'online') {
      initiateStripePayment();
      return false;
    }
  }

  return true;
}


async function initiateStripePayment() {
  try {
    Swal.fire({
      title: 'Processing...',
      text: 'Redirecting to secure payment',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const reservationFeePerRoom = 500;
    const totalReservationFee = reservationFeePerRoom * selectedRooms.length;

    const reservationData = {
      rooms: selectedRooms.map(room => ({ room_id: room.room_id })),
      first_name: document.querySelector('input[name="first_name"]').value,
      middle_name: document.querySelector('input[name="middle_name"]')?.value || '',
      last_name: document.querySelector('input[name="last_name"]').value,
      email: document.querySelector('input[name="email"]').value,
      contact_number: document.querySelector('input[name="contact_number"]').value,
      dob: document.querySelector('input[name="dob"]').value,
      arrival_date: document.getElementById('arrivalDate').value,
      departure_date: document.getElementById('departureDate').value,
      adults: parseInt(document.getElementById('adults').value),
      children: parseInt(document.getElementById('children').value),
      purpose: document.querySelector('textarea[name="purpose"]')?.value || ''
    };

    const response = await fetch('/payment/create-checkout-session', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.reservationData.csrfToken
      },
      body: JSON.stringify({
        reservation_data: reservationData,
        amount: totalReservationFee
      })
    });

    const data = await response.json();

    if (data.error) {
      throw new Error(data.error);
    }

    window.location.href = data.url;

  } catch (error) {
    console.error('Payment error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Payment Error',
      text: 'Failed to process payment. Please try again.',
      confirmButtonColor: '#8b5cf6'
    });
  }
}


function handleFinalSubmission() {
  // Prepare rooms data
  const roomsData = JSON.stringify(selectedRooms.map(room => ({ room_id: room.room_id })));
  document.getElementById('roomsData').value = roomsData;

  const form = document.getElementById('reservationForm');
  if (form) {
    form.submit();
  }
}


function updateStepDisplay() {
  document.querySelectorAll('.step').forEach((step, index) => {
    const stepNumber = index + 1;
    step.classList.remove('active', 'completed', 'clickable');

    if (stepNumber < currentStep) {
      step.classList.add('completed', 'clickable');
    } else if (stepNumber === currentStep) {
      step.classList.add('active');
    }
  });

  document.querySelectorAll('.step-content').forEach((content, index) => {
    content.classList.remove('active');
    if (index + 1 === currentStep) {
      content.classList.add('active');
    }
  });

  const backBtn = document.getElementById('backBtn');
  const nextBtn = document.getElementById('nextStepBtn');

  if (backBtn) {
    backBtn.style.display = currentStep === 1 ? 'none' : 'inline-flex';
  }

  if (nextBtn) {
    if (currentStep === 3) {
      const activePayment = document.querySelector('.payment-tab.active')?.dataset.payment;
      if (activePayment === 'online') {
        nextBtn.innerHTML = '<i class="ri-secure-payment-line"></i> Proceed to Payment';
      } else {
        nextBtn.innerHTML = 'Continue <i class="ri-arrow-right-line"></i>';
      }
    } else if (currentStep === 4) {
      nextBtn.style.display = 'none';
    } else {
      nextBtn.innerHTML = 'Continue <i class="ri-arrow-right-line"></i>';
    }
  }

  const guestAddressDisplay = document.getElementById('guestAddressDisplay');
  if (guestAddressDisplay) {
    guestAddressDisplay.style.display = currentStep >= 3 ? 'block' : 'none';
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}


function showAlert(icon, title, text) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: '#8b5cf6'
    });
  } else {
    alert(text);
  }
}


function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}


function updateElementText(id, text) {
  const element = document.getElementById(id);
  if (element) {
    element.textContent = text;
  }
}


function formatNumber(num) {
  return num.toLocaleString('en-PH', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
}


function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}


// CSS for spinner animation
const style = document.createElement('style');
style.textContent = `
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;
document.head.appendChild(style);

// Expose functions to global scope
window.nextStep = nextStep;
window.prevStep = prevStep;
window.goToStep = goToStep;
window.switchPaymentMethod = switchPaymentMethod;
window.showAvailableRooms = showAvailableRooms;
window.addRoom = addRoom;
window.removeRoom = removeRoom;