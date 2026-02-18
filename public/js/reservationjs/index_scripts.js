// ─────────────────────────────────────────────
// STATE
// ─────────────────────────────────────────────
let currentStep  = 1;
const totalSteps = 5;
let selectedRooms = [];
let activeRoomTab = 0;

// ─────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);

  if (window.reservationData?.initialRoom) {
    selectedRooms.push(window.reservationData.initialRoom);
  }

  initializeCheckout();
  setupEventListeners();
  updateSidebarRooms();
  updatePricing();

  // ── Handle post-Stripe redirect ──────────────────────────────
  // The controller sets session and redirects to this same page
  // with payment_success=1 in the query string, OR the blade
  // already has session('payment_success') = true (server-side).
  // We detect either case here.
  const isPaymentSuccess = urlParams.get('payment_success') === '1'
    || document.getElementById('paymentSuccessFlag')?.value === '1';

  if (isPaymentSuccess) {
    currentStep = 5;
    updateStepDisplay();
    Swal.fire({
      icon: 'success',
      title: 'Payment Successful!',
      text: 'Check your email for login credentials.',
      confirmButtonColor: '#060E4D'
    });
  } else {
    updateStepDisplay();
  }

  // ── Pre-fill dates from URL params (coming from room-details page) ──
  const prefilledArrival   = urlParams.get('arrival_date');
  const prefilledDeparture = urlParams.get('departure_date');

  if (prefilledArrival)   window._prefilledArrival   = prefilledArrival;
  if (prefilledDeparture) window._prefilledDeparture = prefilledDeparture;

  // Pre-fill nights count in sidebar immediately if dates are known
  if (prefilledArrival && prefilledDeparture) {
    const diff = (new Date(prefilledDeparture) - new Date(prefilledArrival)) / 86400000;
    const nights = Math.max(0, Math.ceil(diff));
    if (nights > 0) {
      const totalRate = selectedRooms.reduce((s, r) => s + r.rate_per_night, 0);
      const subtotal  = totalRate * nights;
      const fee       = 500 * selectedRooms.length;
      updateElementText('priceNights',   nights);
      updateElementText('subtotal',      '₱' + formatNumber(subtotal));
      updateElementText('totalAmount',   '₱' + formatNumber(subtotal));
      updateElementText('balanceAmount', '₱' + formatNumber(subtotal - fee));
      updateElementText('balanceAmountText2', '₱' + formatNumber(subtotal - fee));
    }
  }
});

function initializeCheckout() {
  const cashInput   = document.getElementById('cashPaymentInput');
  const onlineInput = document.getElementById('onlinePaymentInput');
  if (cashInput)   cashInput.disabled   = false;
  if (onlineInput) onlineInput.disabled = true;
}

function setupEventListeners() {
  // Payment tabs
  document.querySelectorAll('.payment-tab').forEach(tab => {
    tab.addEventListener('click', () => switchPaymentMethod(tab.dataset.payment));
  });

  // Sync primary-account sidebar display (step 2 fields)
  ['first_name', 'last_name', 'email', 'contact_number'].forEach(field => {
    const input = document.querySelector(`input[name="${field}"]`);
    if (input) {
      input.addEventListener('blur', updateGuestDisplay);
      input.addEventListener('input', debounce(updateGuestDisplay, 400));
    }
  });

  // Progress-step click (go back only)
  document.querySelectorAll('.step').forEach((step, idx) => {
    step.addEventListener('click', () => {
      if (idx + 1 < currentStep) goToStep(idx + 1);
    });
  });

  document.getElementById('nextStepBtn')?.addEventListener('click', nextStep);
  document.getElementById('backBtn')?.addEventListener('click', prevStep);
}

// ─────────────────────────────────────────────
// PRICING
// ─────────────────────────────────────────────
function updatePricing() {
  const roomCount             = selectedRooms.length;
  const reservationFeePerRoom = 500;
  const totalReservationFee   = reservationFeePerRoom * roomCount;

  // Try room-0 panel date inputs first (step 3 rendered)
  let nights = 0;
  const firstArrival   = document.querySelector('.room-arrival-date[data-room-idx="0"]')?.value;
  const firstDeparture = document.querySelector('.room-departure-date[data-room-idx="0"]')?.value;

  if (firstArrival && firstDeparture) {
    const diff = (new Date(firstDeparture) - new Date(firstArrival)) / 86400000;
    nights = Math.max(0, Math.ceil(diff));
  } else if (window._prefilledArrival && window._prefilledDeparture) {
    // Fall back to pre-filled URL param values before step 3 is mounted
    const diff = (new Date(window._prefilledDeparture) - new Date(window._prefilledArrival)) / 86400000;
    nights = Math.max(0, Math.ceil(diff));
  }

  const totalRatePerNight = selectedRooms.reduce((s, r) => s + r.rate_per_night, 0);
  const subtotal          = nights > 0 ? totalRatePerNight * nights : null;
  const balance           = subtotal !== null ? subtotal - totalReservationFee : null;

  updateElementText('totalRoomsCount',          roomCount);
  updateElementText('roomCountForFee',          roomCount);
  updateElementText('roomCount',                roomCount);
  updateElementText('totalReservationFee',      '₱' + formatNumber(totalReservationFee));
  updateElementText('payNowAmount',             '₱' + formatNumber(totalReservationFee));
  updateElementText('totalReservationFeeText',  '₱' + formatNumber(totalReservationFee));

  if (nights > 0 && subtotal !== null) {
    updateElementText('priceNights',       nights);
    updateElementText('subtotal',          '₱' + formatNumber(subtotal));
    updateElementText('totalAmount',       '₱' + formatNumber(subtotal));
    updateElementText('balanceAmount',     '₱' + formatNumber(balance));
    updateElementText('balanceAmountText2','₱' + formatNumber(balance));
  } else {
    updateElementText('priceNights',       '—');
    updateElementText('subtotal',          '—');
    updateElementText('totalAmount',       '—');
    updateElementText('balanceAmount',     '—');
    updateElementText('balanceAmountText2','—');
  }
}

// ─────────────────────────────────────────────
// ROOM TABS (step 3)
// ─────────────────────────────────────────────
function renderRoomTabsAndForms() {
  renderRoomTabs();
  renderRoomForms();
  if (activeRoomTab >= selectedRooms.length) activeRoomTab = selectedRooms.length - 1;
  switchRoomTab(activeRoomTab);

  // ── After DOM is rendered, inject pre-filled dates ──────────
  // Small timeout to let the DOM settle after innerHTML assignment
  setTimeout(() => {
    injectPrefilledDates();
    // Recompute pricing now that date inputs exist in the DOM
    updatePricing();
  }, 50);
}

/**
 * Reads window._prefilledArrival / _prefilledDeparture (set from URL params)
 * and populates ALL room date inputs that are still empty.
 */
function injectPrefilledDates() {
  if (!window._prefilledArrival || !window._prefilledDeparture) return;

  selectedRooms.forEach((_, idx) => {
    const arrivalInput   = document.querySelector(`input[name="rooms_data[${idx}][arrival_date]"]`);
    const departureInput = document.querySelector(`input[name="rooms_data[${idx}][departure_date]"]`);

    if (arrivalInput && !arrivalInput.value) {
      arrivalInput.value = window._prefilledArrival;
    }
    if (departureInput && !departureInput.value) {
      departureInput.value = window._prefilledDeparture;
    }
  });
}

function renderRoomTabs() {
  const list = document.getElementById('roomTabsList');
  if (!list) return;
  list.innerHTML = selectedRooms.map((room, idx) => `
    <button type="button"
      class="room-tab-btn ${idx === activeRoomTab ? 'active' : ''}"
      id="roomTab_${idx}"
      onclick="switchRoomTab(${idx})"
    >
      <span class="tab-dot"></span>
      <span class="tab-label">Room ${idx + 1}</span>
    </button>
  `).join('');
}

function renderRoomForms() {
  const container = document.getElementById('roomTabsForms');
  if (!container) return;

  container.innerHTML = selectedRooms.map((room, roomIdx) => `
    <div class="room-form-panel ${roomIdx === activeRoomTab ? 'active' : ''}" id="roomPanel_${roomIdx}">

      <!-- Stay Details -->
      <div class="room-section-heading">
        <div class="rsh-icon"><i class="ri-calendar-check-line"></i></div>
        <div class="rsh-text">
          <h3>Stay Details — Room ${roomIdx + 1}</h3>
          <p>${room.room_type_name} &middot; Room ${room.room_number} &middot; ₱${formatNumber(room.rate_per_night)}/night</p>
        </div>
      </div>

      <div class="stay-details-grid-2">
        <div class="form-group">
          <label>Check-in Date <span class="required">*</span></label>
          <input type="date" name="rooms_data[${roomIdx}][arrival_date]"
            class="form-control room-arrival-date" data-room-idx="${roomIdx}"
            min="${getTodayStr()}" required>
        </div>
        <div class="form-group">
          <label>Check-out Date <span class="required">*</span></label>
          <input type="date" name="rooms_data[${roomIdx}][departure_date]"
            class="form-control room-departure-date" data-room-idx="${roomIdx}"
            min="${getTomorrowStr()}" required>
        </div>
        <div class="form-group">
          <label>Room Type</label>
          <input type="text" class="form-control" value="${room.room_type_name}"
            readonly style="background:#f8fafc; color:#64748b; cursor:default;">
        </div>
        <div class="form-group">
          <label>Number of Guests <span class="required">*</span></label>
          <select name="rooms_data[${roomIdx}][number_of_guests]"
            class="form-control room-guest-count" data-room-idx="${roomIdx}"
            onchange="onGuestCountChange(${roomIdx}, this.value)">
            ${buildGuestOptions(room.max_pax, Math.min(2, room.max_pax))}
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-bottom:1.25rem;">
        <label>Special Requests <span style="color:#94a3b8; font-weight:400;">(Optional)</span></label>
        <textarea name="rooms_data[${roomIdx}][special_requests]" class="form-control"
          placeholder="Early check-in, high floor, extra pillows…" style="min-height:72px;"></textarea>
      </div>

      <hr class="guest-divider">

      <!-- Guest Details -->
      <div class="room-section-heading" style="margin-top:0;">
        <div class="rsh-icon"><i class="ri-group-line"></i></div>
        <div class="rsh-text">
          <h3>Guest Details — Room ${roomIdx + 1}</h3>
          <p>Fill in info for each guest staying in this room</p>
        </div>
      </div>

      <div id="guestBlocksContainer_${roomIdx}">
        ${buildGuestBlocks(roomIdx, Math.min(2, room.max_pax))}
      </div>
    </div>
  `).join('');

  // Bind date listeners for live pricing update
  container.querySelectorAll('.room-arrival-date, .room-departure-date').forEach(input => {
    input.addEventListener('change', updatePricing);
  });
}

function switchRoomTab(idx) {
  activeRoomTab = idx;
  document.querySelectorAll('.room-tab-btn').forEach((btn, i)  => btn.classList.toggle('active', i === idx));
  document.querySelectorAll('.room-form-panel').forEach((p, i) => p.classList.toggle('active', i === idx));
}

function onGuestCountChange(roomIdx, count) {
  const c = document.getElementById(`guestBlocksContainer_${roomIdx}`);
  if (c) c.innerHTML = buildGuestBlocks(roomIdx, parseInt(count));
}

function buildGuestOptions(maxPax, selected = 2) {
  let html = '';
  for (let i = 1; i <= maxPax; i++) {
    html += `<option value="${i}" ${i === selected ? 'selected' : ''}>${i} Guest${i > 1 ? 's' : ''}</option>`;
  }
  return html;
}

function buildGuestBlocks(roomIdx, count) {
  let html = '';
  for (let g = 0; g < count; g++) {
    const isPrimary = g === 0;
    html += `
      <div class="guest-block" id="guestBlock_${roomIdx}_${g}">
        <div class="guest-block-header">
          <span class="guest-block-label">Guest ${g + 1}${isPrimary ? ' (Primary)' : ''}</span>
        </div>
        <div class="guest-block-body">
          <div class="guest-form-grid">
            <div class="form-group">
              <label>First Name${isPrimary ? ' <span class="required">*</span>' : ''}</label>
              <input type="text" name="rooms_data[${roomIdx}][guests][${g}][first_name]"
                class="form-control" placeholder="Juan" ${isPrimary ? 'required' : ''}>
            </div>
            <div class="form-group">
              <label>Last Name${isPrimary ? ' <span class="required">*</span>' : ''}</label>
              <input type="text" name="rooms_data[${roomIdx}][guests][${g}][last_name]"
                class="form-control" placeholder="Dela Cruz" ${isPrimary ? 'required' : ''}>
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <div class="input-icon-wrap">
                <i class="ri-mail-line"></i>
                <input type="email" name="rooms_data[${roomIdx}][guests][${g}][email]"
                  class="form-control" placeholder="juan@example.com">
              </div>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <div class="input-icon-wrap">
                <i class="ri-phone-line"></i>
                <input type="tel" name="rooms_data[${roomIdx}][guests][${g}][phone]"
                  class="form-control" placeholder="+63 912 345 6789">
              </div>
            </div>
            <div class="form-group guest-full-col">
              <label>Address</label>
              <div class="input-icon-wrap">
                <i class="ri-map-pin-line"></i>
                <input type="text" name="rooms_data[${roomIdx}][guests][${g}][address]"
                  class="form-control" placeholder="123 Main St, City, ZIP">
              </div>
            </div>
            <div class="form-group">
              <label>Estimated Arrival Time</label>
              <div class="input-icon-wrap">
                <i class="ri-time-line"></i>
                <input type="time" name="rooms_data[${roomIdx}][guests][${g}][arrival_time]"
                  class="form-control">
              </div>
            </div>
          </div>
        </div>
      </div>`;
  }
  return html;
}

// ─────────────────────────────────────────────
// ROOM SELECTION (Step 1)
// ─────────────────────────────────────────────
async function showAvailableRooms() {
  const availableSection = document.getElementById('availableRoomsSection');
  const availableList    = document.getElementById('availableRoomsList');

  const firstArrival   = document.querySelector('.room-arrival-date[data-room-idx="0"]')?.value
                         || window._prefilledArrival
                         || getTodayStr();
  const firstDeparture = document.querySelector('.room-departure-date[data-room-idx="0"]')?.value
                         || window._prefilledDeparture
                         || getTomorrowStr();

  availableList.innerHTML = `
    <div style="text-align:center;padding:2rem;grid-column:1/-1;">
      <i class="ri-loader-4-line" style="font-size:2rem;animation:spin 1s linear infinite;display:block;margin-bottom:.5rem;"></i>
      <p style="color:#64748b;">Loading available rooms…</p>
    </div>`;
  availableSection.style.display = 'block';

  try {
    const response = await fetch(window.reservationData.getAvailableRoomsUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.reservationData.csrfToken
      },
      body: JSON.stringify({
        arrival_date:     firstArrival,
        departure_date:   firstDeparture,
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
            <div class="room-meta" style="margin:.5rem 0;flex-wrap:wrap;">
              <span><i class="ri-door-line"></i> Room ${room.room_number}</span>
              <span><i class="ri-user-line"></i> Up to ${room.max_pax} guests</span>
            </div>
            <p style="font-size:.875rem;color:#64748b;margin:.5rem 0;">${room.description || ''}</p>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;">
              <div class="room-price">₱${formatNumber(room.rate_per_night)}
                <span style="font-size:.75rem;font-weight:400;color:#64748b;">per night</span>
              </div>
              <button type="button" class="btn-add-room"
                onclick="addRoom(${room.room_id},'${room.room_number}','${room.room_type_name}',
                  '${(room.description||'').replace(/'/g,"\\'")}',
                  ${room.rate_per_night},${room.max_pax},'${room.image_path}')">
                <i class="ri-add-line"></i> Add
              </button>
            </div>
          </div>
        </div>`).join('');
    } else {
      availableList.innerHTML = `
        <div style="text-align:center;padding:2rem;color:#64748b;grid-column:1/-1;">
          <i class="ri-information-line" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
          <p>No additional rooms available.</p>
        </div>`;
    }
  } catch (err) {
    console.error('Error fetching rooms:', err);
    availableList.innerHTML = `
      <div style="text-align:center;padding:2rem;color:#ef4444;grid-column:1/-1;">
        <i class="ri-error-warning-line" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
        <p>Failed to load rooms. Please try again.</p>
      </div>`;
  }
}

function addRoom(roomId, roomNumber, roomTypeName, description, ratePerNight, maxPax, imagePath) {
  if (selectedRooms.find(r => r.room_id === roomId)) {
    Swal.fire({ icon: 'info', title: 'Already Added', text: 'This room is already in your selection.', confirmButtonColor: '#060E4D' });
    return;
  }

  const room = { room_id: roomId, room_number: roomNumber, room_type_name: roomTypeName,
                  description, rate_per_night: ratePerNight, max_pax: maxPax, image_path: imagePath };
  selectedRooms.push(room);
  activeRoomTab = selectedRooms.length - 1;

  document.getElementById('selectedRoomsList').innerHTML += `
    <div class="room-card selected-room" data-room-id="${roomId}" data-rate="${ratePerNight}">
      <img src="${imagePath}" alt="${roomTypeName}" class="room-image">
      <div class="room-details">
        <h3 class="room-name">${roomTypeName}</h3>
        <div class="room-meta">
          <span><i class="ri-door-line"></i> Room ${roomNumber}</span>
          <span><i class="ri-user-line"></i> Up to ${maxPax} guests</span>
          <span class="free-badge">Available</span>
        </div>
        <p style="color:#64748b;font-size:.875rem;margin:.5rem 0;">${description}</p>
        <div class="room-price">₱${formatNumber(ratePerNight)}
          <span style="font-size:.875rem;color:#64748b;font-weight:400;">per night</span>
        </div>
      </div>
      <button type="button" class="remove-room-btn" onclick="removeRoom(this)"><i class="ri-close-line"></i></button>
    </div>`;

  if (selectedRooms.length > 1) {
    document.querySelectorAll('.remove-room-btn').forEach(btn => btn.style.display = 'flex');
  }

  if (document.getElementById('roomTabsList')) renderRoomTabsAndForms();

  updateSidebarRooms();
  updatePricing();

  Swal.fire({ icon: 'success', title: 'Room Added!',
    text: `${roomTypeName} (Room ${roomNumber}) added to your reservation.`,
    timer: 2000, showConfirmButton: false });

  showAvailableRooms();
}

function removeRoom(button) {
  const roomCard = button.closest('.selected-room');
  const roomId   = parseInt(roomCard.dataset.roomId);

  if (selectedRooms.length <= 1) {
    Swal.fire({ icon: 'warning', title: 'Cannot Remove', text: 'You must have at least one room.', confirmButtonColor: '#060E4D' });
    return;
  }

  Swal.fire({
    title: 'Remove Room?', text: 'Remove this room from your reservation?', icon: 'question',
    showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, remove it'
  }).then(result => {
    if (!result.isConfirmed) return;

    selectedRooms = selectedRooms.filter(r => r.room_id !== roomId);
    roomCard.remove();
    if (selectedRooms.length === 1) {
      document.querySelectorAll('.remove-room-btn').forEach(btn => btn.style.display = 'none');
    }
    if (activeRoomTab >= selectedRooms.length) activeRoomTab = selectedRooms.length - 1;

    if (document.getElementById('roomTabsList')) renderRoomTabsAndForms();

    updateSidebarRooms();
    updatePricing();

    Swal.fire({ icon: 'success', title: 'Room Removed', timer: 1500, showConfirmButton: false });

    if (document.getElementById('availableRoomsSection').style.display === 'block') showAvailableRooms();
  });
}

function updateSidebarRooms() {
  const sidebarList = document.getElementById('sidebarRoomsList');
  const roomCount   = document.getElementById('roomCount');
  if (roomCount) roomCount.textContent = selectedRooms.length;

  if (sidebarList) {
    sidebarList.innerHTML = selectedRooms.map(room => `
      <div class="sidebar-room-item">
        <img src="${room.image_path}" alt="${room.room_type_name}">
        <div>
          <div style="font-weight:700;font-size:.875rem;">${room.room_type_name}</div>
          <div style="font-size:.75rem;color:#64748b;">Room ${room.room_number} &middot; ₱${formatNumber(room.rate_per_night)}/night</div>
        </div>
      </div>`).join('');
  }
}

// ─────────────────────────────────────────────
// GUEST DISPLAY (sidebar)
// ─────────────────────────────────────────────
function updateGuestDisplay() {
  const firstName = document.querySelector('input[name="first_name"]')?.value || '';
  const lastName  = document.querySelector('input[name="last_name"]')?.value  || '';
  const email     = document.querySelector('input[name="email"]')?.value      || '';
  const phone     = document.querySelector('input[name="contact_number"]')?.value || '';

  if (firstName || lastName) updateElementText('guestNameDisplay', `${firstName} ${lastName}`.trim());
  if (email) updateElementText('guestEmailDisplay', email);
  if (phone) updateElementText('guestPhoneDisplay', `Mobile: ${phone}`);
}

// ─────────────────────────────────────────────
// PAYMENT TAB
// ─────────────────────────────────────────────
function switchPaymentMethod(method) {
  document.querySelectorAll('.payment-tab').forEach(t => t.classList.remove('active'));
  document.querySelector(`[data-payment="${method}"]`)?.classList.add('active');
  document.querySelectorAll('.payment-content').forEach(c => c.classList.remove('active'));
  document.getElementById(`${method}-payment`)?.classList.add('active');

  const cashInput   = document.getElementById('cashPaymentInput');
  const onlineInput = document.getElementById('onlinePaymentInput');
  if (cashInput)   cashInput.disabled   = method !== 'cash';
  if (onlineInput) onlineInput.disabled = method !== 'online';

  updateElementText('paymentMethodText',
    method === 'cash' ? 'upon check-in' : 'online through your account');

  if (currentStep === 4) {
    const nextBtn = document.getElementById('nextStepBtn');
    if (nextBtn) {
      nextBtn.innerHTML = method === 'online'
        ? '<i class="ri-secure-payment-line"></i> Proceed to Payment'
        : 'Confirm Reservation <i class="ri-arrow-right-line"></i>';
    }
  }
}

// ─────────────────────────────────────────────
// STEP NAVIGATION
// ─────────────────────────────────────────────
function nextStep() {
  if (currentStep < totalSteps) {
    if (!validateStep(currentStep)) return;
    currentStep++;
    updateStepDisplay();
  } else {
    handleFinalSubmission();
  }
}

function prevStep() {
  if (currentStep > 1) { currentStep--; updateStepDisplay(); }
}

function goToStep(step) {
  if (step >= 1 && step <= totalSteps && step < currentStep) {
    currentStep = step;
    updateStepDisplay();
  }
}

// ─────────────────────────────────────────────
// VALIDATION
// ─────────────────────────────────────────────
function validateStep(step) {
  if (step === 1) {
    if (selectedRooms.length === 0) {
      showAlert('warning', 'No Rooms Selected', 'Please select at least one room.');
      return false;
    }
    return true;
  }

  if (step === 2) {
    const fields = [
      { name: 'first_name',     label: 'First Name' },
      { name: 'last_name',      label: 'Last Name' },
      { name: 'email',          label: 'Email' },
      { name: 'contact_number', label: 'Contact Number' },
      { name: 'dob',            label: 'Date of Birth' },
    ];
    for (const field of fields) {
      const input = document.querySelector(`input[name="${field.name}"]`);
      if (!input || !input.value.trim()) {
        showAlert('warning', 'Incomplete Account Info', `Please fill in your ${field.label}.`);
        input?.focus();
        return false;
      }
    }
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput && !isValidEmail(emailInput.value)) {
      showAlert('warning', 'Invalid Email', 'Please enter a valid email address.');
      emailInput.focus();
      return false;
    }
    updateGuestDisplay();
    return true;
  }

  if (step === 3) {
    for (let i = 0; i < selectedRooms.length; i++) {
      const arrival   = document.querySelector(`input[name="rooms_data[${i}][arrival_date]"]`)?.value;
      const departure = document.querySelector(`input[name="rooms_data[${i}][departure_date]"]`)?.value;

      if (!arrival || !departure) {
        switchRoomTab(i);
        showAlert('warning', `Room ${i + 1} — Missing Dates`,
          `Please select check-in and check-out dates for Room ${i + 1}.`);
        return false;
      }
      if (new Date(departure) <= new Date(arrival)) {
        switchRoomTab(i);
        showAlert('warning', `Room ${i + 1} — Invalid Dates`,
          'Check-out date must be after check-in date.');
        return false;
      }

      const gFirst = document.querySelector(`input[name="rooms_data[${i}][guests][0][first_name]"]`)?.value;
      const gLast  = document.querySelector(`input[name="rooms_data[${i}][guests][0][last_name]"]`)?.value;
      if (!gFirst || !gLast) {
        switchRoomTab(i);
        showAlert('warning', `Room ${i + 1} — Missing Guest Name`,
          `Please enter the primary guest name for Room ${i + 1}.`);
        return false;
      }
    }
    return true;
  }

  if (step === 4) {
    const activePayment = document.querySelector('.payment-tab.active')?.dataset.payment;
    if (!activePayment) {
      showAlert('warning', 'Select Payment Method', 'Please choose a payment method.');
      return false;
    }
    if (activePayment === 'online') {
      initiateStripePayment();
      return false; // Stripe handles navigation
    }
    return true;
  }

  return true;
}

function updateStepDisplay() {
  document.querySelectorAll('.step').forEach((step, idx) => {
    const n = idx + 1;
    step.classList.remove('active', 'completed', 'clickable');
    if (n < currentStep)        step.classList.add('completed', 'clickable');
    else if (n === currentStep) step.classList.add('active');
  });

  document.querySelectorAll('.step-content').forEach((content, idx) => {
    content.classList.remove('active');
    if (idx + 1 === currentStep) content.classList.add('active');
  });

  const backBtn = document.getElementById('backBtn');
  const nextBtn = document.getElementById('nextStepBtn');

  if (backBtn) backBtn.style.display = currentStep === 1 ? 'none' : 'inline-flex';

  if (nextBtn) {
    if (currentStep === 5) {
      nextBtn.style.display = 'none';
    } else if (currentStep === 4) {
      nextBtn.style.display = 'inline-flex';
      const activePayment = document.querySelector('.payment-tab.active')?.dataset.payment;
      nextBtn.innerHTML = activePayment === 'online'
        ? '<i class="ri-secure-payment-line"></i> Proceed to Payment'
        : 'Confirm Reservation <i class="ri-arrow-right-line"></i>';
    } else {
      nextBtn.style.display = 'inline-flex';
      nextBtn.innerHTML = 'Continue <i class="ri-arrow-right-line"></i>';
    }
  }

  const guestDisplay = document.getElementById('guestAddressDisplay');
  if (guestDisplay) guestDisplay.style.display = currentStep >= 3 ? 'block' : 'none';

  if (currentStep === 3) {
    activeRoomTab = 0;   // always start on Room 1 tab
    renderRoomTabsAndForms();
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─────────────────────────────────────────────
// STRIPE PAYMENT
// ─────────────────────────────────────────────
async function initiateStripePayment() {
  try {
    Swal.fire({
      title: 'Processing…', text: 'Redirecting to secure payment',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const totalReservationFee = 500 * selectedRooms.length;

    // ── Build rooms payload — cast all numeric fields explicitly ──
    const roomsPayload = selectedRooms.map((room, idx) => ({
      room_id:          parseInt(room.room_id, 10),
      arrival_date:     document.querySelector(`input[name="rooms_data[${idx}][arrival_date]"]`)?.value   || '',
      departure_date:   document.querySelector(`input[name="rooms_data[${idx}][departure_date]"]`)?.value || '',
      // Laravel validates this as 'integer' — must be a number, not a string
      number_of_guests: parseInt(
        document.querySelector(`select[name="rooms_data[${idx}][number_of_guests]"]`)?.value || '1',
        10
      ),
      special_requests: document.querySelector(`textarea[name="rooms_data[${idx}][special_requests]"]`)?.value || null,
    }));

    // ── Sanity-check: make sure dates are actually filled ────────
    for (let i = 0; i < roomsPayload.length; i++) {
      if (!roomsPayload[i].arrival_date || !roomsPayload[i].departure_date) {
        Swal.close();
        showAlert('warning', `Room ${i + 1} — Missing Dates`,
          `Check-in and check-out dates are required for Room ${i + 1}.`);
        return;
      }
    }

    const reservationData = {
      rooms:          roomsPayload,
      first_name:     document.querySelector('input[name="first_name"]')?.value   || '',
      middle_name:    document.querySelector('input[name="middle_name"]')?.value   || '',
      last_name:      document.querySelector('input[name="last_name"]')?.value     || '',
      email:          document.querySelector('input[name="email"]')?.value         || '',
      contact_number: document.querySelector('input[name="contact_number"]')?.value || '',
      dob:            document.querySelector('input[name="dob"]')?.value           || '',
    };

    const payload = {
      reservation_data: reservationData,
      amount: totalReservationFee,   // already a number (500 * n)
    };

    // ── Log what we're sending so it's easy to inspect ──────────
    console.log('[Stripe] Sending payload:', JSON.stringify(payload, null, 2));

    const response = await fetch('/payment/create-checkout-session', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.reservationData.csrfToken,
        'Accept': 'application/json',          // ← ensures Laravel returns JSON for validation errors
      },
      body: JSON.stringify(payload)
    });

    // ── Parse response regardless of HTTP status ─────────────────
    let data;
    try {
      data = await response.json();
    } catch (parseErr) {
      throw new Error(`Server returned non-JSON response (HTTP ${response.status})`);
    }

    console.log('[Stripe] Server response:', data);

    if (!response.ok) {
      // Laravel validation errors arrive as { message, errors: { field: [...] } }
      if (data.errors) {
        const firstField  = Object.keys(data.errors)[0];
        const firstMsg    = data.errors[firstField][0];
        throw new Error(`Validation: ${firstField} — ${firstMsg}`);
      }
      // Controller error with real message (e.g. Stripe exception)
      throw new Error(data.message || data.error || `HTTP ${response.status}`);
    }

    if (!data.url) {
      throw new Error('No checkout URL returned from server');
    }

    // ── All good — redirect to Stripe ───────────────────────────
    window.location.href = data.url;

  } catch (error) {
    console.error('[Stripe] Payment error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Payment Error',
      text: error.message || 'Failed to process payment. Please try again.',
      confirmButtonColor: '#060E4D'
    });
  }
}

// ─────────────────────────────────────────────
// FINAL SUBMISSION (cash path)
// ─────────────────────────────────────────────
function handleFinalSubmission() {
  document.getElementById('roomsData').value =
    JSON.stringify(selectedRooms.map(r => ({ room_id: r.room_id })));
  document.getElementById('reservationForm')?.submit();
}

// ─────────────────────────────────────────────
// UTILITIES
// ─────────────────────────────────────────────
function getTodayStr()    { return new Date().toISOString().split('T')[0]; }
function getTomorrowStr() { const d = new Date(); d.setDate(d.getDate() + 1); return d.toISOString().split('T')[0]; }
function showAlert(icon, title, text) {
  typeof Swal !== 'undefined' ? Swal.fire({ icon, title, text, confirmButtonColor: '#060E4D' }) : alert(text);
}
function isValidEmail(e)  { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
function updateElementText(id, text) { const el = document.getElementById(id); if (el) el.textContent = text; }
function formatNumber(num) {
  return Number(num).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}
function debounce(func, wait) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => func(...args), wait); };
}

const _s = document.createElement('style');
_s.textContent = '@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}';
document.head.appendChild(_s);

window.nextStep               = nextStep;
window.prevStep               = prevStep;
window.goToStep               = goToStep;
window.switchPaymentMethod    = switchPaymentMethod;
window.showAvailableRooms     = showAvailableRooms;
window.addRoom                = addRoom;
window.removeRoom             = removeRoom;
window.switchRoomTab          = switchRoomTab;
window.onGuestCountChange     = onGuestCountChange;
window.renderRoomTabsAndForms = renderRoomTabsAndForms;