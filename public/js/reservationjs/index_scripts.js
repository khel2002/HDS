// ─────────────────────────────────────────────
// STATE
// ─────────────────────────────────────────────
let currentStep   = 1;
const totalSteps  = 5;
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
  applyAuthUserPrefill();
  setupEventListeners();
  updateSidebarRooms();
  updatePricing();

  // ── Post-payment success redirect from server ──
  const isPaymentSuccess =
    urlParams.get('payment_success') === '1' ||
    document.getElementById('paymentSuccessFlag')?.value === '1';

  if (isPaymentSuccess) {
    currentStep = 5;
    updateStepDisplay();
    Swal.fire({
      icon: 'success', title: 'Payment Successful!',
      text: 'Check your email for login credentials.',
      confirmButtonColor: '#060E4D',
    });
  } else {
    updateStepDisplay();
  }

  // ── Pre-fill dates from URL params ──
  const prefilledArrival   = urlParams.get('arrival_date');
  const prefilledDeparture = urlParams.get('departure_date');
  if (prefilledArrival)   window._prefilledArrival   = prefilledArrival;
  if (prefilledDeparture) window._prefilledDeparture = prefilledDeparture;

  if (prefilledArrival && prefilledDeparture) {
    const nights = calcNights(prefilledArrival, prefilledDeparture);
    if (nights > 0) refreshPricingDisplay(nights);
  }
});

function initializeCheckout() {
  setPaymentInputs('cash');
}

function setupEventListeners() {
  document.querySelectorAll('.payment-tab').forEach(tab => {
    tab.addEventListener('click', () => switchPaymentMethod(tab.dataset.payment));
  });

  // Bind live-update listeners for ALL name/email/phone inputs
  // (works for both guests and logged-in users since both now show visible fields)
  ['first_name', 'last_name', 'email', 'contact_number'].forEach(field => {
    const el = document.querySelector(`input[name="${field}"]`);
    if (el) {
      el.addEventListener('blur', updateGuestDisplay);
      el.addEventListener('input', debounce(updateGuestDisplay, 400));
    }
  });

  document.querySelectorAll('.step').forEach((step, idx) => {
    step.addEventListener('click', () => { if (idx + 1 < currentStep) goToStep(idx + 1); });
  });

  document.getElementById('nextStepBtn')?.addEventListener('click', nextStep);
  document.getElementById('backBtn')?.addEventListener('click', prevStep);
}

// ─────────────────────────────────────────────
// AUTH USER PRE-FILL
// ─────────────────────────────────────────────

/**
 * If window.authUser is set (logged-in session):
 * - Update the sidebar display immediately using authUser data.
 * - The Step 2 form is now visible and pre-filled via Blade,
 *   so we do NOT skip step 2 anymore.
 */
function applyAuthUserPrefill() {
  const u = window.authUser;
  if (!u) return;
  // Update sidebar display immediately
  updateGuestDisplay();
}

// ─────────────────────────────────────────────
// PRICING
// ─────────────────────────────────────────────
function calcNights(arrival, departure) {
  if (!arrival || !departure) return 0;
  const diff = (new Date(departure) - new Date(arrival)) / 86400000;
  return Math.max(0, Math.floor(diff));
}

function updatePricing() {
  const arrival   = document.querySelector('.room-arrival-date[data-room-idx="0"]')?.value
                    || window._prefilledArrival || '';
  const departure = document.querySelector('.room-departure-date[data-room-idx="0"]')?.value
                    || window._prefilledDeparture || '';
  const nights    = calcNights(arrival, departure);
  refreshPricingDisplay(nights);
}

function refreshPricingDisplay(nights) {
  const roomCount  = selectedRooms.length;
  const fee        = 500 * roomCount;
  const totalRate  = selectedRooms.reduce((s, r) => s + r.rate_per_night, 0);
  const subtotal   = nights > 0 ? totalRate * nights : null;
  const balance    = subtotal !== null ? subtotal - fee : null;

  setEl('totalRoomsCount',         roomCount);
  setEl('roomCountForFee',         roomCount);
  setEl('roomCount',               roomCount);
  setEl('totalReservationFee',     fmt(fee));
  setEl('payNowAmount',            fmt(fee));
  setEl('totalReservationFeeText', fmt(fee));

  if (subtotal !== null) {
    setEl('priceNights',        nights);
    setEl('subtotal',           fmt(subtotal));
    setEl('totalAmount',        fmt(subtotal));
    setEl('balanceAmount',      fmt(balance));
    setEl('balanceAmountText2', fmt(balance));
  } else {
    ['priceNights','subtotal','totalAmount','balanceAmount','balanceAmountText2']
      .forEach(id => setEl(id, '—'));
  }
}

// ─────────────────────────────────────────────
// ROOM FORM STATE  (persist across step navigation)
// ─────────────────────────────────────────────

let _savedRoomFormState = {};
let _step3Initialized   = false;

function saveRoomFormState() {
  const container = document.getElementById('roomTabsForms');
  if (!container) return;
  container.querySelectorAll('input, select, textarea').forEach(el => {
    if (el.name) _savedRoomFormState[el.name] = el.value;
  });
}

function restoreRoomFormState() {
  const container = document.getElementById('roomTabsForms');
  if (!container) return;

  container.querySelectorAll('select.room-guest-count').forEach(el => {
    if (el.name && _savedRoomFormState[el.name] !== undefined) {
      el.value = _savedRoomFormState[el.name];
      const roomIdx = parseInt(el.dataset.roomIdx);
      const count   = parseInt(el.value);
      const c = document.getElementById(`guestBlocksContainer_${roomIdx}`);
      if (c) c.innerHTML = buildGuestBlocks(roomIdx, count);
    }
  });

  container.querySelectorAll('input, select, textarea').forEach(el => {
    if (el.name && _savedRoomFormState[el.name] !== undefined) {
      el.value = _savedRoomFormState[el.name];
    }
  });
}

// ─────────────────────────────────────────────
// ROOM TABS  (step 3)
// ─────────────────────────────────────────────
function renderRoomTabsAndForms() {
  renderRoomTabs();
  renderRoomForms();
  if (activeRoomTab >= selectedRooms.length) activeRoomTab = selectedRooms.length - 1;
  switchRoomTab(activeRoomTab);

  setTimeout(() => {
    if (_step3Initialized && Object.keys(_savedRoomFormState).length > 0) {
      restoreRoomFormState();
      document.querySelectorAll('.room-arrival-date, .room-departure-date').forEach(input => {
        input.addEventListener('change', updatePricing);
      });
    } else {
      injectPrefilledDates();
      prefillPrimaryGuestFromAccount();
      _step3Initialized = true;
    }
    updatePricing();
  }, 50);
}

function injectPrefilledDates() {
  if (!window._prefilledArrival || !window._prefilledDeparture) return;
  selectedRooms.forEach((_, idx) => {
    const a = document.querySelector(`input[name="rooms_data[${idx}][arrival_date]"]`);
    const d = document.querySelector(`input[name="rooms_data[${idx}][departure_date]"]`);
    if (a && !a.value) a.value = window._prefilledArrival;
    if (d && !d.value) d.value = window._prefilledDeparture;
  });
}

/**
 * Pre-fills Room 1 / Guest 1 from:
 * - window.authUser (logged-in) — pulls from the live Step 2 form fields
 *   so any edits the user made are respected.
 * - Step 2 form fields (guest path).
 * Fields are not overwritten if the user already typed something.
 */
function prefillPrimaryGuestFromAccount() {
  // For logged-in users, prefer the live Step 2 form values so edits are captured
  const firstName = document.querySelector('input[name="first_name"]')?.value.trim()
    || window.authUser?.first_name || '';
  const lastName  = document.querySelector('input[name="last_name"]')?.value.trim()
    || window.authUser?.last_name  || '';
  const email     = document.querySelector('input[name="email"]')?.value.trim()
    || window.authUser?.email      || '';
  const phone     = document.querySelector('input[name="contact_number"]')?.value.trim()
    || window.authUser?.contact_number || '';

  function fillIfEmpty(selector, value) {
    const el = document.querySelector(selector);
    if (el && !el.value.trim() && value) el.value = value;
  }

  fillIfEmpty(`input[name="rooms_data[0][guests][0][first_name]"]`, firstName);
  fillIfEmpty(`input[name="rooms_data[0][guests][0][last_name]"]`,  lastName);
  fillIfEmpty(`input[name="rooms_data[0][guests][0][email]"]`,      email);
  fillIfEmpty(`input[name="rooms_data[0][guests][0][phone]"]`,      phone);
}

function renderRoomTabs() {
  const list = document.getElementById('roomTabsList');
  if (!list) return;
  list.innerHTML = selectedRooms.map((room, idx) => `
    <button type="button"
      class="room-tab-btn ${idx === activeRoomTab ? 'active' : ''}"
      id="roomTab_${idx}" onclick="switchRoomTab(${idx})">
      <span class="tab-dot"></span>
      <span class="tab-label">Room ${idx + 1}</span>
    </button>`).join('');
}

function renderRoomForms() {
  const container = document.getElementById('roomTabsForms');
  if (!container) return;

  container.innerHTML = selectedRooms.map((room, roomIdx) => `
    <div class="room-form-panel ${roomIdx === activeRoomTab ? 'active' : ''}" id="roomPanel_${roomIdx}">

      <div class="room-section-heading">
        <div class="rsh-icon"><i class="ri-calendar-check-line"></i></div>
        <div class="rsh-text">
          <h3>Stay Details — Room ${roomIdx + 1}</h3>
          <p>${room.room_type_name} &middot; Room ${room.room_number} &middot; ₱${fmtNum(room.rate_per_night)}/night</p>
        </div>
      </div>

      <div class="stay-details-grid-2">
        <div class="form-group">
          <label>Check-in Date <span class="required">*</span></label>
          <input type="date" name="rooms_data[${roomIdx}][arrival_date]"
            class="form-control room-arrival-date" data-room-idx="${roomIdx}"
            min="${todayStr()}" required>
        </div>
        <div class="form-group">
          <label>Check-out Date <span class="required">*</span></label>
          <input type="date" name="rooms_data[${roomIdx}][departure_date]"
            class="form-control room-departure-date" data-room-idx="${roomIdx}"
            min="${tomorrowStr()}" required>
        </div>
        <div class="form-group">
          <label>Room Type</label>
          <input type="text" class="form-control" value="${room.room_type_name}"
            readonly style="background:#f8fafc;color:#64748b;cursor:default;">
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
        <label>Special Requests <span style="color:#94a3b8;font-weight:400;">(Optional)</span></label>
        <textarea name="rooms_data[${roomIdx}][special_requests]" class="form-control"
          placeholder="Early check-in, high floor, extra pillows…" style="min-height:72px;"></textarea>
      </div>

      <hr class="guest-divider">

      <div class="room-section-heading" style="margin-top:0;">
        <div class="rsh-icon"><i class="ri-group-line"></i></div>
        <div class="rsh-text">
          <h3>Guest Details — Room ${roomIdx + 1}</h3>
          <p>${roomIdx === 0
            ? 'Primary guest is pre-filled from your account details'
            : 'Fill in info for each guest staying in this room'}</p>
        </div>
      </div>

      <div id="guestBlocksContainer_${roomIdx}">
        ${buildGuestBlocks(roomIdx, Math.min(2, room.max_pax))}
      </div>
    </div>`).join('');

  container.querySelectorAll('.room-arrival-date, .room-departure-date').forEach(input => {
    input.addEventListener('change', updatePricing);
  });
}

function switchRoomTab(idx) {
  activeRoomTab = idx;
  document.querySelectorAll('.room-tab-btn').forEach((b, i)  => b.classList.toggle('active', i === idx));
  document.querySelectorAll('.room-form-panel').forEach((p, i) => p.classList.toggle('active', i === idx));
}

function onGuestCountChange(roomIdx, count) {
  const c = document.getElementById(`guestBlocksContainer_${roomIdx}`);
  if (c) {
    c.innerHTML = buildGuestBlocks(roomIdx, parseInt(count));
    if (roomIdx === 0) prefillPrimaryGuestFromAccount();
  }
}

function buildGuestOptions(maxPax, selected) {
  return Array.from({ length: maxPax }, (_, i) => i + 1)
    .map(i => `<option value="${i}" ${i === selected ? 'selected' : ''}>${i} Guest${i > 1 ? 's' : ''}</option>`)
    .join('');
}

function buildGuestBlocks(roomIdx, count) {
  return Array.from({ length: count }, (_, g) => {
    const isPrimary = g === 0;
    const isRoom1   = roomIdx === 0;
    const req       = isPrimary ? 'required' : '';
    const reqStar   = isPrimary ? ' <span class="required">*</span>' : '';

    const badgeLabel = '<i class="ri-shield-check-line"></i> From your account';

    const primaryBadge = (isPrimary && isRoom1)
      ? `<span class="guest-prefill-badge">${badgeLabel}</span>`
      : '';

    return `
      <div class="guest-block" id="guestBlock_${roomIdx}_${g}">
        <div class="guest-block-header">
          <span class="guest-block-label">Guest ${g + 1}${isPrimary ? ' (Primary)' : ''}</span>
          ${primaryBadge}
        </div>
        <div class="guest-block-body">
          <div class="guest-form-grid">
            <div class="form-group">
              <label>First Name${reqStar}</label>
              <input type="text" name="rooms_data[${roomIdx}][guests][${g}][first_name]"
                class="form-control" placeholder="Juan" ${req}>
            </div>
            <div class="form-group">
              <label>Last Name${reqStar}</label>
              <input type="text" name="rooms_data[${roomIdx}][guests][${g}][last_name]"
                class="form-control" placeholder="Dela Cruz" ${req}>
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
  }).join('');
}

// ─────────────────────────────────────────────
// ROOM SELECTION  (step 1)
// ─────────────────────────────────────────────
async function showAvailableRooms() {
  const section = document.getElementById('availableRoomsSection');
  const list    = document.getElementById('availableRoomsList');

  const arrival   = document.querySelector('.room-arrival-date[data-room-idx="0"]')?.value
                    || window._prefilledArrival || todayStr();
  const departure = document.querySelector('.room-departure-date[data-room-idx="0"]')?.value
                    || window._prefilledDeparture || tomorrowStr();

  list.innerHTML = `<div style="text-align:center;padding:2rem;grid-column:1/-1;">
    <i class="ri-loader-4-line" style="font-size:2rem;animation:spin 1s linear infinite;display:block;margin-bottom:.5rem;"></i>
    <p style="color:#64748b;">Loading available rooms…</p></div>`;
  section.style.display = 'block';

  try {
    const res  = await fetch(window.reservationData.getAvailableRoomsUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.reservationData.csrfToken },
      body: JSON.stringify({ arrival_date: arrival, departure_date: departure, exclude_room_ids: selectedRooms.map(r => r.room_id) }),
    });
    const data = await res.json();

    if (data.rooms?.length) {
      list.innerHTML = data.rooms.map(room => `
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
              <div class="room-price">₱${fmtNum(room.rate_per_night)}
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
      list.innerHTML = `<div style="text-align:center;padding:2rem;color:#64748b;grid-column:1/-1;">
        <i class="ri-information-line" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
        <p>No additional rooms available.</p></div>`;
    }
  } catch (err) {
    console.error('showAvailableRooms error:', err);
    list.innerHTML = `<div style="text-align:center;padding:2rem;color:#ef4444;grid-column:1/-1;">
      <i class="ri-error-warning-line" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
      <p>Failed to load rooms. Please try again.</p></div>`;
  }
}

function addRoom(roomId, roomNumber, roomTypeName, description, ratePerNight, maxPax, imagePath) {
  if (selectedRooms.find(r => r.room_id === roomId)) {
    Swal.fire({ icon: 'info', title: 'Already Added', text: 'This room is already selected.', confirmButtonColor: '#060E4D' });
    return;
  }
  selectedRooms.push({ room_id: roomId, room_number: roomNumber, room_type_name: roomTypeName,
    description, rate_per_night: ratePerNight, max_pax: maxPax, image_path: imagePath });
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
        <div class="room-price">₱${fmtNum(ratePerNight)}
          <span style="font-size:.875rem;color:#64748b;font-weight:400;">per night</span>
        </div>
      </div>
      <button type="button" class="remove-room-btn" onclick="removeRoom(this)"><i class="ri-close-line"></i></button>
    </div>`;

  document.querySelectorAll('.remove-room-btn').forEach(b => b.style.display = 'flex');
  _step3Initialized = false;
  _savedRoomFormState = {};
  if (document.getElementById('roomTabsList')) renderRoomTabsAndForms();
  updateSidebarRooms();
  updatePricing();
  Swal.fire({ icon: 'success', title: 'Room Added!',
    text: `${roomTypeName} (Room ${roomNumber}) added.`, timer: 2000, showConfirmButton: false });
  showAvailableRooms();
}

function removeRoom(button) {
  if (selectedRooms.length <= 1) {
    Swal.fire({ icon: 'warning', title: 'Cannot Remove', text: 'You must have at least one room.', confirmButtonColor: '#060E4D' });
    return;
  }
  const card   = button.closest('.selected-room');
  const roomId = parseInt(card.dataset.roomId);
  Swal.fire({
    title: 'Remove Room?', text: 'Remove this room from your reservation?', icon: 'question',
    showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, remove it',
  }).then(r => {
    if (!r.isConfirmed) return;
    selectedRooms = selectedRooms.filter(r => r.room_id !== roomId);
    card.remove();
    if (selectedRooms.length === 1)
      document.querySelectorAll('.remove-room-btn').forEach(b => b.style.display = 'none');
    if (activeRoomTab >= selectedRooms.length) activeRoomTab = selectedRooms.length - 1;
    if (document.getElementById('roomTabsList')) renderRoomTabsAndForms();
    updateSidebarRooms();
    updatePricing();
    Swal.fire({ icon: 'success', title: 'Room Removed', timer: 1500, showConfirmButton: false });
    if (document.getElementById('availableRoomsSection').style.display === 'block') showAvailableRooms();
  });
}

function updateSidebarRooms() {
  setEl('roomCount', selectedRooms.length);
  const list = document.getElementById('sidebarRoomsList');
  if (list) {
    list.innerHTML = selectedRooms.map(r => `
      <div class="sidebar-room-item">
        <img src="${r.image_path}" alt="${r.room_type_name}">
        <div>
          <div style="font-weight:700;font-size:.875rem;">${r.room_type_name}</div>
          <div style="font-size:.75rem;color:#64748b;">Room ${r.room_number} &middot; ₱${fmtNum(r.rate_per_night)}/night</div>
        </div>
      </div>`).join('');
  }
}

// ─────────────────────────────────────────────
// SIDEBAR GUEST DISPLAY
// ─────────────────────────────────────────────
function updateGuestDisplay() {
  // Always read from the live Step 2 form fields (works for both auth and guest users)
  const fn    = document.querySelector('input[name="first_name"]')?.value
    || window.authUser?.first_name || '';
  const ln    = document.querySelector('input[name="last_name"]')?.value
    || window.authUser?.last_name  || '';
  const email = document.querySelector('input[name="email"]')?.value
    || window.authUser?.email      || '';
  const phone = document.querySelector('input[name="contact_number"]')?.value
    || window.authUser?.contact_number || '';

  if (fn || ln) setEl('guestNameDisplay',  `${fn} ${ln}`.trim());
  if (email)    setEl('guestEmailDisplay', email);
  if (phone)    setEl('guestPhoneDisplay', `Mobile: ${phone}`);
}

// ─────────────────────────────────────────────
// PAYMENT TAB
// ─────────────────────────────────────────────
function switchPaymentMethod(method) {
  document.querySelectorAll('.payment-tab').forEach(t => t.classList.remove('active'));
  document.querySelector(`[data-payment="${method}"]`)?.classList.add('active');
  document.querySelectorAll('.payment-content').forEach(c => c.classList.remove('active'));
  document.getElementById(`${method}-payment`)?.classList.add('active');
  setPaymentInputs(method);
  setEl('paymentMethodText', method === 'cash' ? 'upon check-in' : 'online through your account');
  if (currentStep === 4) updateStep4Btn(method);
}

function setPaymentInputs(method) {
  const cash   = document.getElementById('cashPaymentInput');
  const online = document.getElementById('onlinePaymentInput');
  if (cash)   cash.disabled   = (method !== 'cash');
  if (online) online.disabled = (method !== 'online');
}

function updateStep4Btn(method) {
  const btn = document.getElementById('nextStepBtn');
  if (!btn) return;
  btn.innerHTML = method === 'online'
    ? '<i class="ri-secure-payment-line"></i> Proceed to Payment'
    : 'Confirm Reservation <i class="ri-arrow-right-line"></i>';
}

// ─────────────────────────────────────────────
// STEP NAVIGATION
// ─────────────────────────────────────────────
function nextStep() {
  if (!validateStep(currentStep)) return;

  if (currentStep === 3) saveRoomFormState();

  if (currentStep === 4) {
    handleFinalSubmission();
    return;
  }

  if (currentStep < 4) {
    currentStep++;
    updateStepDisplay();
  }
}

function prevStep() {
  if (currentStep === 3) saveRoomFormState();
  if (currentStep > 1) {
    currentStep--;
    updateStepDisplay();
  }
}

function goToStep(n) {
  if (n >= 1 && n <= totalSteps && n < currentStep) {
    if (currentStep === 3) saveRoomFormState();
    currentStep = n;
    updateStepDisplay();
  }
}

// ─────────────────────────────────────────────
// VALIDATION
// ─────────────────────────────────────────────
function validateStep(step) {
  if (step === 1) {
    if (selectedRooms.length === 0) {
      alert2('warning', 'No Rooms Selected', 'Please select at least one room.');
      return false;
    }
    return true;
  }

  if (step === 2) {
    const required = [
      { name: 'first_name',     label: 'First Name' },
      { name: 'last_name',      label: 'Last Name' },
      { name: 'email',          label: 'Email' },
      { name: 'contact_number', label: 'Contact Number' },
    ];

    // dob is only required for guest (non-logged-in) users
    if (!window.authUser) {
      required.push({ name: 'dob', label: 'Date of Birth' });
    }

    for (const f of required) {
      const el = document.querySelector(`input[name="${f.name}"]`);
      if (!el?.value.trim()) {
        alert2('warning', 'Incomplete Info', `Please fill in your ${f.label}.`);
        el?.focus();
        return false;
      }
    }

    const emailEl = document.querySelector('input[name="email"]');
    if (emailEl && !isValidEmail(emailEl.value)) {
      alert2('warning', 'Invalid Email', 'Please enter a valid email address.');
      emailEl.focus();
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
        alert2('warning', `Room ${i + 1} — Missing Dates`, `Please select check-in and check-out dates for Room ${i + 1}.`);
        return false;
      }
      if (new Date(departure) <= new Date(arrival)) {
        switchRoomTab(i);
        alert2('warning', `Room ${i + 1} — Invalid Dates`, 'Check-out must be after check-in.');
        return false;
      }
      const gFirst = document.querySelector(`input[name="rooms_data[${i}][guests][0][first_name]"]`)?.value;
      const gLast  = document.querySelector(`input[name="rooms_data[${i}][guests][0][last_name]"]`)?.value;
      if (!gFirst || !gLast) {
        switchRoomTab(i);
        alert2('warning', `Room ${i + 1} — Guest Name Required`, `Please enter the primary guest name for Room ${i + 1}.`);
        return false;
      }
    }
    return true;
  }

  if (step === 4) {
    const method = document.querySelector('.payment-tab.active')?.dataset.payment;
    if (!method) {
      alert2('warning', 'Select Payment Method', 'Please choose a payment method.');
      return false;
    }
    if (method === 'online') {
      initiateStripePayment();
      return false;
    }
    return true;
  }

  return true;
}

function updateStepDisplay() {
  document.querySelectorAll('.step').forEach((el, idx) => {
    const n = idx + 1;
    el.classList.remove('active', 'completed', 'clickable');
    if (n < currentStep)        el.classList.add('completed', 'clickable');
    else if (n === currentStep) el.classList.add('active');
  });

  document.querySelectorAll('.step-content').forEach((el, idx) => {
    el.classList.toggle('active', idx + 1 === currentStep);
  });

  const backBtn = document.getElementById('backBtn');
  const nextBtn = document.getElementById('nextStepBtn');

  if (backBtn) backBtn.style.display = currentStep === 1 ? 'none' : 'inline-flex';

  if (nextBtn) {
    if (currentStep === 5) {
      nextBtn.style.display = 'none';
    } else if (currentStep === 4) {
      nextBtn.style.display = 'inline-flex';
      const method = document.querySelector('.payment-tab.active')?.dataset.payment || 'cash';
      updateStep4Btn(method);
    } else {
      nextBtn.style.display  = 'inline-flex';
      nextBtn.innerHTML      = 'Continue <i class="ri-arrow-right-line"></i>';
    }
  }

  const guestBlock = document.getElementById('guestAddressDisplay');
  if (guestBlock) guestBlock.style.display = currentStep >= 3 ? 'block' : 'none';

  if (currentStep === 3) {
    saveRoomFormState();
    renderRoomTabsAndForms();
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─────────────────────────────────────────────
// STRIPE  (online path)
// ─────────────────────────────────────────────
async function initiateStripePayment() {
  try {
    Swal.fire({ title: 'Processing…', text: 'Redirecting to secure payment',
      allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    const fee = 500 * selectedRooms.length;

    const rooms = selectedRooms.map((room, idx) => ({
      room_id:          parseInt(room.room_id, 10),
      arrival_date:     document.querySelector(`input[name="rooms_data[${idx}][arrival_date]"]`)?.value   || '',
      departure_date:   document.querySelector(`input[name="rooms_data[${idx}][departure_date]"]`)?.value || '',
      number_of_guests: parseInt(
        document.querySelector(`select[name="rooms_data[${idx}][number_of_guests]"]`)?.value || '1', 10),
      special_requests: document.querySelector(`textarea[name="rooms_data[${idx}][special_requests]"]`)?.value || null,
      guests: buildGuestsPayload(idx),
    }));

    for (let i = 0; i < rooms.length; i++) {
      if (!rooms[i].arrival_date || !rooms[i].departure_date) {
        Swal.close();
        alert2('warning', `Room ${i + 1} — Missing Dates`, `Dates required for Room ${i + 1}.`);
        return;
      }
    }

    // Always read from the live Step 2 form fields
    const payload = {
      reservation_data: {
        rooms,
        first_name:     document.querySelector('input[name="first_name"]')?.value     || window.authUser?.first_name     || '',
        middle_name:    document.querySelector('input[name="middle_name"]')?.value    || window.authUser?.middle_name    || '',
        last_name:      document.querySelector('input[name="last_name"]')?.value      || window.authUser?.last_name      || '',
        email:          document.querySelector('input[name="email"]')?.value          || window.authUser?.email          || '',
        contact_number: document.querySelector('input[name="contact_number"]')?.value || window.authUser?.contact_number || '',
        dob:            document.querySelector('input[name="dob"]')?.value            || window.authUser?.dob            || '',
      },
      amount: fee,
    };

    const response = await fetch('/payment/create-checkout-session', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.reservationData.csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    let data;
    try { data = await response.json(); }
    catch { throw new Error(`Non-JSON response (HTTP ${response.status})`); }

    if (!response.ok) {
      if (data.errors) {
        const field = Object.keys(data.errors)[0];
        throw new Error(`${field}: ${data.errors[field][0]}`);
      }
      throw new Error(data.message || data.error || `HTTP ${response.status}`);
    }

    if (!data.url) throw new Error('No checkout URL from server');
    window.location.href = data.url;

  } catch (err) {
    console.error('[Stripe] error:', err);
    Swal.fire({ icon: 'error', title: 'Payment Error',
      text: err.message || 'Failed to process. Please try again.',
      confirmButtonColor: '#060E4D' });
  }
}

function buildGuestsPayload(roomIdx) {
  const count = parseInt(
    document.querySelector(`select[name="rooms_data[${roomIdx}][number_of_guests]"]`)?.value || '1', 10);
  const guests = [];
  for (let g = 0; g < count; g++) {
    guests.push({
      first_name:   document.querySelector(`input[name="rooms_data[${roomIdx}][guests][${g}][first_name]"]`)?.value   || '',
      last_name:    document.querySelector(`input[name="rooms_data[${roomIdx}][guests][${g}][last_name]"]`)?.value    || '',
      email:        document.querySelector(`input[name="rooms_data[${roomIdx}][guests][${g}][email]"]`)?.value        || '',
      phone:        document.querySelector(`input[name="rooms_data[${roomIdx}][guests][${g}][phone]"]`)?.value        || '',
      address:      document.querySelector(`input[name="rooms_data[${roomIdx}][guests][${g}][address]"]`)?.value      || '',
      arrival_time: document.querySelector(`input[name="rooms_data[${roomIdx}][guests][${g}][arrival_time]"]`)?.value || '',
    });
  }
  return guests;
}

// ─────────────────────────────────────────────
// CASH SUBMISSION
// ─────────────────────────────────────────────
function handleFinalSubmission() {
  const roomsInput = document.getElementById('roomsData');
  if (!roomsInput) {
    alert2('error', 'Form Error', 'Missing rooms input. Please refresh.');
    return;
  }

  roomsInput.value = JSON.stringify(selectedRooms.map(r => ({ room_id: r.room_id })));
  setPaymentInputs('cash');

  const form = document.getElementById('reservationForm');
  if (!form) {
    alert2('error', 'Form Error', 'Reservation form not found. Please refresh.');
    return;
  }

  form.submit();
}

// ─────────────────────────────────────────────
// UTILITIES
// ─────────────────────────────────────────────
function todayStr()    { return new Date().toISOString().split('T')[0]; }
function tomorrowStr() { const d = new Date(); d.setDate(d.getDate() + 1); return d.toISOString().split('T')[0]; }
function isValidEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
function fmtNum(n)     { return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }
function fmt(n)        { return '₱' + fmtNum(n); }
function setEl(id, v)  { const el = document.getElementById(id); if (el) el.textContent = v; }
function alert2(icon, title, text) {
  typeof Swal !== 'undefined'
    ? Swal.fire({ icon, title, text, confirmButtonColor: '#060E4D' })
    : alert(text);
}
function debounce(fn, ms) {
  let t;
  return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
}

const _css = document.createElement('style');
_css.textContent = `
  @keyframes spin { to { transform: rotate(360deg); } }

  .guest-prefill-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #4f46e5;
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    margin-left: 0.5rem;
  }
`;
document.head.appendChild(_css);

// ─────────────────────────────────────────────
// EXPORTS
// ─────────────────────────────────────────────
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