const maxGuests = parseInt(document.getElementById('maxGuestsValue')?.value || 5);
const ratePerNight = parseInt(document.getElementById('ratePerNight')?.value || 0);
const roomId = document.getElementById('roomId')?.value || '';

let guests = {
  adults: 1,
  children: 0,
  infants: 0
};

let selectedCheckin = null;
let selectedCheckout = null;
let desktopCalendar = null;
let mobileCalendar = null;
let fullCalendar = null;

// --- AVAILABILITY SCHEDULER (Click-based, no drag) ---

function initializeScheduler() {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  fullCalendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'UTC',
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth'
    },
    editable: false,
    selectable: false,       // disable drag-select
    height: 'auto',
    fixedWeekCount: false,
    showNonCurrentDates: true,

    validRange: {
      start: new Date()
    },

    dayCellClassNames: function(arg) {
      const classes = [];
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (arg.date < today) classes.push('fc-day-past');

      // Highlight selected range
      if (selectedCheckin && selectedCheckout) {
        const d = new Date(arg.date);
        d.setHours(0, 0, 0, 0);
        const ci = new Date(selectedCheckin); ci.setHours(0, 0, 0, 0);
        const co = new Date(selectedCheckout); co.setHours(0, 0, 0, 0);
        if (d.getTime() === ci.getTime()) classes.push('fc-day-checkin');
        else if (d.getTime() === co.getTime()) classes.push('fc-day-checkout');
        else if (d > ci && d < co) classes.push('fc-day-inrange');
      } else if (selectedCheckin) {
        const d = new Date(arg.date); d.setHours(0, 0, 0, 0);
        const ci = new Date(selectedCheckin); ci.setHours(0, 0, 0, 0);
        if (d.getTime() === ci.getTime()) classes.push('fc-day-checkin');
      }

      return classes;
    },

    events: function(info, successCallback, failureCallback) {
      fetch(`/reservation/booked-dates/${roomId}`)
        .then(response => response.json())
        .then(data => { successCallback(data); })
        .catch(error => {
          console.error('Error fetching bookings:', error);
          const primaryColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--bs-primary').trim() || '#0d6efd';
          successCallback([{
            title: 'Booked',
            start: '2026-02-10',
            end: '2026-02-14',
            color: primaryColor,
            display: 'background',
            extendedProps: { status: 'booked', guestName: 'John Doe' }
          }]);
        });
    },

    eventClick: function(info) {
      const startDate = info.event.start ? info.event.start.toLocaleDateString() : 'N/A';
      const endDate = info.event.end
        ? new Date(info.event.end.getTime() - 86400000).toLocaleDateString()
        : 'N/A';
      const status = info.event.extendedProps.status || 'booked';
      const guestName = info.event.extendedProps.guestName || 'N/A';

      Swal.fire({
        title: 'Booking Details',
        html: `<div style="text-align:left;">
          <p><strong>Status:</strong> ${status.charAt(0).toUpperCase() + status.slice(1)}</p>
          <p><strong>From:</strong> ${startDate}</p>
          <p><strong>To:</strong> ${endDate}</p>
          ${guestName !== 'N/A' ? `<p><strong>Guest:</strong> ${guestName}</p>` : ''}
        </div>`,
        icon: 'info',
        confirmButtonText: 'OK',
        customClass: {
          title: 'swal-title-azure',
          htmlContainer: 'swal-text-azure',
          confirmButton: 'swal-confirm-azure',
        }
      });
    },

    // ✅ CLICK-BASED selection logic
    dateClick: function(info) {
      const clickedDate = new Date(info.date);
      clickedDate.setHours(0, 0, 0, 0);

      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (clickedDate < today) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Date',
          text: 'Cannot select past dates.',
          confirmButtonText: 'OK',
          customClass: {
            title: 'swal-title-azure',
            htmlContainer: 'swal-text-azure',
            confirmButton: 'swal-confirm-azure',
          }
        });
        return;
      }

      // Check if date is booked
      const events = fullCalendar.getEvents();
      const isBooked = events.some(event => {
        const eventStart = new Date(event.start); eventStart.setHours(0, 0, 0, 0);
        const eventEnd = new Date(event.end); eventEnd.setHours(0, 0, 0, 0);
        return clickedDate >= eventStart && clickedDate < eventEnd;
      });

      if (isBooked) {
        Swal.fire({
          icon: 'warning',
          title: 'Date Unavailable',
          text: 'This date is already booked.',
          confirmButtonText: 'OK',
          customClass: {
            title: 'swal-title-azure',
            htmlContainer: 'swal-text-azure',
            confirmButton: 'swal-confirm-azure',
          }
        });
        return;
      }

      // First click = check-in, second click = check-out
      if (!selectedCheckin || (selectedCheckin && selectedCheckout)) {
        // Reset and set check-in
        selectedCheckin = clickedDate;
        selectedCheckout = null;

        updateSchedulerHelper('Select your check-out date');
        fullCalendar.render();

        // Update booking card displays
        const checkinDisplay = document.getElementById('checkinDisplay');
        const checkoutDisplay = document.getElementById('checkoutDisplay');
        if (checkinDisplay) checkinDisplay.textContent = formatDate(selectedCheckin);
        if (checkoutDisplay) checkoutDisplay.textContent = 'Add date';

        updatePricing();
        updateMobileFooter();

      } else {
        // Second click = check-out
        if (clickedDate <= selectedCheckin) {
          Swal.fire({
            icon: 'warning',
            title: 'Invalid Date',
            text: 'Check-out must be after check-in.',
            confirmButtonText: 'OK',
            customClass: {
              title: 'swal-title-azure',
              htmlContainer: 'swal-text-azure',
              confirmButton: 'swal-confirm-azure',
            }
          });
          return;
        }

        // Check for conflicts in the range
        const hasConflict = events.some(event => {
          const eventStart = new Date(event.start); eventStart.setHours(0, 0, 0, 0);
          const eventEnd = new Date(event.end); eventEnd.setHours(0, 0, 0, 0);
          return (selectedCheckin < eventEnd && clickedDate > eventStart);
        });

        if (hasConflict) {
          Swal.fire({
            icon: 'error',
            title: 'Dates Unavailable',
            text: 'Some dates in this range are already booked. Please choose different dates.',
            confirmButtonText: 'OK',
            customClass: {
              title: 'swal-title-azure',
              htmlContainer: 'swal-text-azure',
              confirmButton: 'swal-confirm-azure',
            }
          });
          return;
        }

        selectedCheckout = clickedDate;
        fullCalendar.render();

        // Update booking card displays
        const checkinDisplay = document.getElementById('checkinDisplay');
        const checkoutDisplay = document.getElementById('checkoutDisplay');
        if (checkinDisplay) checkinDisplay.textContent = formatDate(selectedCheckin);
        if (checkoutDisplay) checkoutDisplay.textContent = formatDate(selectedCheckout);

        updatePricing();
        updateMobileFooter();
        updateSchedulerHelper('Click and drag to select your check-in and check-out dates');
        smoothScrollToBooking();
      }
    }
  });

  fullCalendar.render();
  addCalendarLegend();
  addSelectionHelper();
  injectSchedulerStyles();
}

function injectSchedulerStyles() {
  if (document.getElementById('scheduler-click-styles')) return;
  const style = document.createElement('style');
  style.id = 'scheduler-click-styles';
  style.textContent = `
    /* Clickable day cells */
    .fc-daygrid-day:not(.fc-day-past) {
      cursor: pointer !important;
    }
    .fc-daygrid-day:not(.fc-day-past):hover .fc-daygrid-day-frame {
      background: var(--bs-primary-bg-subtle) !important;
      transition: background 0.15s ease;
    }

    /* Check-in day */
    .fc-day-checkin .fc-daygrid-day-frame {
      background: var(--bs-primary) !important;
      border-radius: 4px 0 0 4px;
    }
    .fc-day-checkin .fc-daygrid-day-number {
      color: #fff !important;
      font-weight: 700;
    }

    /* Check-out day */
    .fc-day-checkout .fc-daygrid-day-frame {
      background: var(--bs-primary) !important;
      border-radius: 0 4px 4px 0;
    }
    .fc-day-checkout .fc-daygrid-day-number {
      color: #fff !important;
      font-weight: 700;
    }

    /* In-range days */
    .fc-day-inrange .fc-daygrid-day-frame {
      background: var(--bs-primary-bg-subtle) !important;
      border-radius: 0;
    }
  `;
  document.head.appendChild(style);
}

function updateSchedulerHelper(message) {
  const helper = document.querySelector('#calendar .selection-helper');
  if (helper) {
    helper.innerHTML = `<i class="ri-information-line"></i> ${message}`;
  }
}

function addCalendarLegend() {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl || calendarEl.querySelector('.booking-legend')) return;

  const legend = document.createElement('div');
  legend.className = 'booking-legend';
  legend.innerHTML = `
    <div class="legend-item">
      <div class="legend-color available"></div>
      <span>Available</span>
    </div>
    <div class="legend-item">
      <div class="legend-color booked"></div>
      <span>Booked</span>
    </div>
    <div class="legend-item">
      <div class="legend-color today"></div>
      <span>Today</span>
    </div>
  `;
  calendarEl.appendChild(legend);
}

function addSelectionHelper() {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl || calendarEl.querySelector('.selection-helper')) return;

  const helper = document.createElement('div');
  helper.className = 'selection-helper';
  helper.style.cssText = 'margin-top: 1rem; padding: 0.75rem 1rem; background: var(--bs-primary-bg-subtle); border-left: 3px solid var(--bs-primary); border-radius: var(--bs-border-radius); font-size: 0.875rem; color: var(--bs-body-color);';
  helper.innerHTML = '<i class="ri-information-line"></i> Click a date to set check-in, then click another date to set check-out';
  calendarEl.insertBefore(helper, calendarEl.firstChild);
}

function smoothScrollToBooking() {
  const bookingCard = document.getElementById('booking');
  if (!bookingCard) return;

  const bookingCardRect = bookingCard.getBoundingClientRect();
  const bookingCardTop = bookingCardRect.top + window.pageYOffset;
  const offset = 100;
  const targetPosition = bookingCardTop - offset;
  const startPosition = window.pageYOffset;
  const distance = targetPosition - startPosition;
  const duration = 1000;
  let start = null;

  function animation(currentTime) {
    if (start === null) start = currentTime;
    const timeElapsed = currentTime - start;
    const progress = Math.min(timeElapsed / duration, 1);
    const ease = easeInOutCubic(progress);
    window.scrollTo(0, startPosition + (distance * ease));
    if (timeElapsed < duration) {
      requestAnimationFrame(animation);
    } else {
      bookingCard.style.transition = 'box-shadow 0.3s ease';
      bookingCard.style.boxShadow = '0 0 20px rgba(var(--bs-primary-rgb), 0.5)';
      setTimeout(() => { bookingCard.style.boxShadow = ''; }, 1500);
    }
  }
  requestAnimationFrame(animation);
}

function easeInOutCubic(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

// --- DESKTOP & MOBILE BOOKING CALENDARS ---

function initializeCalendars() {
  const desktopCalendarEl = document.getElementById('desktopCalendarPicker');
  if (desktopCalendarEl) {
    desktopCalendar = new FullCalendar.Calendar(desktopCalendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: { left: 'prev', center: 'title', right: 'next' },
      selectable: true,
      selectMirror: true,
      selectMinDistance: 5,
      unselectAuto: false,
      height: 'auto',
      fixedWeekCount: false,
      validRange: { start: new Date() },
      selectAllow: function(selectInfo) {
        const today = new Date(); today.setHours(0, 0, 0, 0);
        return selectInfo.start >= today;
      },
      select: function(info) { handleDesktopDateSelect(info); }
    });
    desktopCalendar.render();
  }

  const mobileCalendarEl = document.getElementById('mobileCalendar');
  if (mobileCalendarEl) {
    mobileCalendar = new FullCalendar.Calendar(mobileCalendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: { left: 'prev', center: 'title', right: 'next' },
      selectable: true,
      selectMirror: true,
      selectMinDistance: 5,
      unselectAuto: false,
      height: 'auto',
      fixedWeekCount: false,
      validRange: { start: new Date() },
      selectAllow: function(selectInfo) {
        const today = new Date(); today.setHours(0, 0, 0, 0);
        return selectInfo.start >= today;
      },
      select: function(info) { handleMobileDateSelect(info); }
    });
    mobileCalendar.render();
  }
}

function handleDesktopDateSelect(info) {
  selectedCheckin = info.start;
  selectedCheckout = new Date(info.end.getTime() - 86400000);

  if (selectedCheckout <= selectedCheckin) {
    Swal.fire({ icon: 'warning', title: 'Invalid Selection', text: 'Please select at least one night. Check-out must be after check-in.', confirmButtonText: 'OK' });
    if (desktopCalendar) desktopCalendar.unselect();
    return;
  }

  const checkinStr = formatDate(selectedCheckin);
  const checkoutStr = formatDate(selectedCheckout);

  const dci = document.getElementById('desktopCheckinValue');
  const dco = document.getElementById('desktopCheckoutValue');
  const dcif = document.getElementById('desktopCheckinField');
  const dcof = document.getElementById('desktopCheckoutField');
  const saveBtn = document.getElementById('saveDesktopDatesBtn');

  if (dci) { dci.textContent = checkinStr; dci.classList.remove('placeholder'); }
  if (dco) { dco.textContent = checkoutStr; dco.classList.remove('placeholder'); }
  if (dcif) dcif.classList.add('selected');
  if (dcof) dcof.classList.add('selected');
  if (saveBtn) saveBtn.disabled = false;
}

function handleMobileDateSelect(info) {
  selectedCheckin = info.start;
  selectedCheckout = new Date(info.end.getTime() - 86400000);

  if (selectedCheckout <= selectedCheckin) {
    Swal.fire({ icon: 'warning', title: 'Invalid Selection', text: 'Please select at least one night. Check-out must be after check-in.', confirmButtonText: 'OK' });
    if (mobileCalendar) mobileCalendar.unselect();
    return;
  }

  const checkinStr = formatDate(selectedCheckin);
  const checkoutStr = formatDate(selectedCheckout);

  const mci = document.getElementById('mobileCheckinDisplay');
  const mco = document.getElementById('mobileCheckoutDisplay');
  const mcif = document.getElementById('mobileCheckinField');
  const mcof = document.getElementById('mobileCheckoutField');
  const saveBtn = document.getElementById('saveDatesBtn');

  if (mci) { mci.textContent = checkinStr; mci.classList.remove('placeholder'); }
  if (mco) { mco.textContent = checkoutStr; mco.classList.remove('placeholder'); }
  if (mcif) mcif.classList.add('active');
  if (mcof) mcof.classList.add('active');
  if (saveBtn) saveBtn.disabled = false;
}

function formatDate(date) {
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

function openDesktopCalendar() {
  const modal = document.getElementById('desktopCalendarModal');
  if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}

function closeDesktopCalendar() {
  const modal = document.getElementById('desktopCalendarModal');
  if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
}

function clearDesktopDates() {
  selectedCheckin = null;
  selectedCheckout = null;
  if (desktopCalendar) desktopCalendar.unselect();
  if (fullCalendar) fullCalendar.render();

  const dci = document.getElementById('desktopCheckinValue');
  const dco = document.getElementById('desktopCheckoutValue');
  const dcif = document.getElementById('desktopCheckinField');
  const dcof = document.getElementById('desktopCheckoutField');
  const saveBtn = document.getElementById('saveDesktopDatesBtn');
  const ci = document.getElementById('checkinDisplay');
  const co = document.getElementById('checkoutDisplay');

  if (dci) { dci.textContent = 'Select date'; dci.classList.add('placeholder'); }
  if (dco) { dco.textContent = 'Select date'; dco.classList.add('placeholder'); }
  if (dcif) dcif.classList.remove('selected');
  if (dcof) dcof.classList.remove('selected');
  if (saveBtn) saveBtn.disabled = true;
  if (ci) ci.textContent = 'Add date';
  if (co) co.textContent = 'Add date';

  updatePricing();
  updateMobileFooter();
}

function saveDesktopDates() {
  if (selectedCheckin && selectedCheckout) {
    const ci = document.getElementById('checkinDisplay');
    const co = document.getElementById('checkoutDisplay');
    if (ci) ci.textContent = formatDate(selectedCheckin);
    if (co) co.textContent = formatDate(selectedCheckout);
    updatePricing();
    updateMobileFooter();
    if (fullCalendar) fullCalendar.render();
  }
  closeDesktopCalendar();
}

function updatePricing() {
  const nightsCountEl = document.getElementById('nightsCount');
  const nightsPluralEl = document.getElementById('nightsPlural');
  const subtotalPriceEl = document.getElementById('subtotalPrice');
  const totalPriceEl = document.getElementById('totalPrice');

  if (selectedCheckin && selectedCheckout) {
    const timeDiff = selectedCheckout - selectedCheckin;
    const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
    if (nights > 0) {
      const subtotal = ratePerNight * nights;
      if (nightsCountEl) nightsCountEl.textContent = nights;
      if (nightsPluralEl) nightsPluralEl.textContent = nights > 1 ? 's' : '';
      if (subtotalPriceEl) subtotalPriceEl.textContent = '₱' + subtotal.toLocaleString();
      if (totalPriceEl) totalPriceEl.textContent = '₱' + subtotal.toLocaleString();
    }
  } else {
    if (nightsCountEl) nightsCountEl.textContent = '0';
    if (nightsPluralEl) nightsPluralEl.textContent = '';
    if (subtotalPriceEl) subtotalPriceEl.textContent = '₱0';
    if (totalPriceEl) totalPriceEl.textContent = '₱0';
  }
}

function updateMobileFooter() {
  const mobilePrice = document.getElementById('mobilePrice');
  const mobilePriceUnit = document.getElementById('mobilePriceUnit');
  const mobileReserveBtn = document.getElementById('mobileReserveBtn');
  if (!mobilePrice || !mobilePriceUnit || !mobileReserveBtn) return;

  if (selectedCheckin && selectedCheckout) {
    const timeDiff = selectedCheckout - selectedCheckin;
    const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
    const total = ratePerNight * nights;
    mobilePrice.textContent = '₱' + total.toLocaleString();
    mobilePriceUnit.textContent = `for ${nights} night${nights > 1 ? 's' : ''}`;
    mobileReserveBtn.textContent = 'Reserve';
    mobileReserveBtn.onclick = function() { proceedToReservation(); };
  } else {
    mobilePrice.textContent = '₱' + ratePerNight.toLocaleString();
    mobilePriceUnit.textContent = 'per night';
    mobileReserveBtn.textContent = 'Choose dates';
    mobileReserveBtn.onclick = function() { openMobileBooking(); };
  }
}

function openMobileBooking() {
  const modal = document.getElementById('calendarModal');
  if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}

function closeCalendarModal() {
  const modal = document.getElementById('calendarModal');
  if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
}

function clearDates() {
  selectedCheckin = null;
  selectedCheckout = null;
  if (mobileCalendar) mobileCalendar.unselect();
  if (desktopCalendar) desktopCalendar.unselect();
  if (fullCalendar) fullCalendar.render();

  const mci = document.getElementById('mobileCheckinDisplay');
  const mco = document.getElementById('mobileCheckoutDisplay');
  const mcif = document.getElementById('mobileCheckinField');
  const mcof = document.getElementById('mobileCheckoutField');
  const saveBtn = document.getElementById('saveDatesBtn');
  const ci = document.getElementById('checkinDisplay');
  const co = document.getElementById('checkoutDisplay');

  if (mci) { mci.textContent = 'Add date'; mci.classList.add('placeholder'); }
  if (mco) { mco.textContent = 'Add date'; mco.classList.add('placeholder'); }
  if (mcif) mcif.classList.remove('active');
  if (mcof) mcof.classList.remove('active');
  if (saveBtn) saveBtn.disabled = true;
  if (ci) ci.textContent = 'Add date';
  if (co) co.textContent = 'Add date';

  updatePricing();
  updateMobileFooter();
}

function saveDates() {
  if (selectedCheckin && selectedCheckout) {
    const ci = document.getElementById('checkinDisplay');
    const co = document.getElementById('checkoutDisplay');
    if (ci) ci.textContent = formatDate(selectedCheckin);
    if (co) co.textContent = formatDate(selectedCheckout);
    if (desktopCalendar && selectedCheckin && selectedCheckout) {
      desktopCalendar.select(selectedCheckin, new Date(selectedCheckout.getTime() + 86400000));
    }
    updatePricing();
    updateMobileFooter();
    if (fullCalendar) fullCalendar.render();
  }
  closeCalendarModal();
}

function proceedToReservation() {
  if (!selectedCheckin || !selectedCheckout) {
    Swal.fire({ icon: 'warning', title: 'Missing Information', text: 'Please select check-in and check-out dates', confirmButtonText: 'OK' });
    return;
  }
  const checkinFormatted = selectedCheckin.toISOString().split('T')[0];
  const checkoutFormatted = selectedCheckout.toISOString().split('T')[0];
  const params = new URLSearchParams({
    arrival_date: checkinFormatted,
    departure_date: checkoutFormatted,
    adults: guests.adults,
    children: guests.children
  });
  window.location.href = `/reservation/create/${roomId}?${params.toString()}`;
}

// --- GUEST CONTROLS ---

function updateGuestDisplay() {
  const total = guests.adults + guests.children;
  const infantText = guests.infants > 0 ? `, ${guests.infants} infant${guests.infants > 1 ? 's' : ''}` : '';
  const displayElement = document.getElementById('guestDisplay');
  if (displayElement) displayElement.textContent = `${total} guest${total > 1 ? 's' : ''}${infantText}`;
  updateButtonStates();
}

function updateButtonStates() {
  const totalGuests = guests.adults + guests.children;

  const ad = document.getElementById('adultsDecrement');
  const ai = document.getElementById('adultsIncrement');
  const ac = document.getElementById('adultsCount');
  if (ad) ad.disabled = guests.adults <= 1;
  if (ai) ai.disabled = totalGuests >= maxGuests;
  if (ac) ac.textContent = guests.adults;

  const cd = document.getElementById('childrenDecrement');
  const ci2 = document.getElementById('childrenIncrement');
  const cc = document.getElementById('childrenCount');
  if (cd) cd.disabled = guests.children <= 0;
  if (ci2) ci2.disabled = totalGuests >= maxGuests;
  if (cc) cc.textContent = guests.children;

  const id = document.getElementById('infantsDecrement');
  const ic = document.getElementById('infantsCount');
  if (id) id.disabled = guests.infants <= 0;
  if (ic) ic.textContent = guests.infants;
}

function incrementGuest(type) {
  const totalGuests = guests.adults + guests.children;
  if (type === 'adults' || type === 'children') {
    if (totalGuests < maxGuests) guests[type]++;
  } else if (type === 'infants') {
    guests[type]++;
  }
  updateGuestDisplay();
}

function decrementGuest(type) {
  if (type === 'adults' && guests[type] > 1) guests[type]--;
  else if (type !== 'adults' && guests[type] > 0) guests[type]--;
  updateGuestDisplay();
}

function toggleGuestDropdown() {
  const dropdown = document.getElementById('guestDropdown');
  if (dropdown) dropdown.classList.toggle('active');
}

function closeGuestDropdown() {
  const dropdown = document.getElementById('guestDropdown');
  if (dropdown) dropdown.classList.remove('active');
}

// --- MODALS ---

function openAmenitiesModal() {
  const modal = document.getElementById('amenitiesModal');
  if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}

function closeAmenitiesModal() {
  const modal = document.getElementById('amenitiesModal');
  if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
}

// --- DESCRIPTION TOGGLE ---

function toggleDescription() {
  const description = document.getElementById('roomDescription');
  const button = document.getElementById('showMoreBtn');
  if (!description || !button) return;

  if (description.classList.contains('truncated')) {
    description.classList.remove('truncated');
    description.classList.add('expanded');
    button.innerHTML = 'Show less <i class="ri-arrow-up-s-line"></i>';
  } else {
    description.classList.remove('expanded');
    description.classList.add('truncated');
    button.innerHTML = 'Show more <i class="ri-arrow-down-s-line"></i>';
  }
}

// --- STICKY NAV ---

function handleStickyNav() {
  const stickyNav = document.getElementById('stickyNav');
  if (stickyNav) {
    stickyNav.classList.toggle('visible', window.scrollY > 600);
  }
  updateActiveTab();
}

function initNavigationTabs() {
  document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');
      if (targetId && targetId.startsWith('#')) {
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          const offsetTop = targetElement.getBoundingClientRect().top + window.pageYOffset - 100;
          window.scrollTo({ top: offsetTop, behavior: 'smooth' });
          document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
          this.classList.add('active');
        }
      }
    });
  });
}

function updateActiveTab() {
  const sections = ['photos', 'amenities', 'availability', 'location'];
  let currentSection = 'photos';
  sections.forEach(sectionId => {
    const section = document.getElementById(sectionId);
    if (section) {
      const rect = section.getBoundingClientRect();
      if (rect.top <= 150 && rect.bottom >= 150) currentSection = sectionId;
    }
  });
  document.querySelectorAll('.nav-tab').forEach(tab => {
    const href = tab.getAttribute('href');
    tab.classList.toggle('active', href === '#' + currentSection);
  });
}

// --- MAP ---

let mapInstance = null;

function initMap(latitude, longitude, locationName) {
  const mapElement = document.getElementById('map');
  if (!mapElement) return;
  if (mapInstance) { mapInstance.remove(); mapInstance = null; }

  mapInstance = L.map('map').setView([latitude, longitude], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(mapInstance);

  const marker = L.marker([latitude, longitude]).addTo(mapInstance);
  marker.bindPopup(`<b>${locationName}</b><br>Hotel De SLSU`).openPopup();

  const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#0d6efd';
  L.circle([latitude, longitude], { color: primaryColor, fillColor: primaryColor, fillOpacity: 0.2, radius: 25 }).addTo(mapInstance);
}

// --- INIT ---

document.addEventListener('DOMContentLoaded', function() {
  const description = document.getElementById('roomDescription');
  if (description) description.classList.add('truncated');

  initializeCalendars();
  initializeScheduler();
  updateButtonStates();
  initNavigationTabs();

  // Close guest dropdown on outside click
  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('guestDropdown');
    const guestsField = document.querySelector('.guests-field');
    if (dropdown && guestsField && !guestsField.contains(event.target)) {
      dropdown.classList.remove('active');
    }
  });

  // Init map
  const mapData = document.getElementById('mapData');
  if (mapData) {
    const lat = parseFloat(mapData.dataset.lat);
    const lng = parseFloat(mapData.dataset.lng);
    const name = mapData.dataset.name;
    if (lat && lng) initMap(lat, lng, name);
  } else {
    initMap(10.39344739659632, 124.980512039808, 'SLSU Main Campus');
  }

  // Desktop reserve button
  const desktopReserveBtn = document.getElementById('desktopReserveBtn');
  if (desktopReserveBtn) desktopReserveBtn.addEventListener('click', proceedToReservation);

  // Open desktop calendar on check-in/out field click
  const checkinField = document.getElementById('checkinField');
  const checkoutField = document.getElementById('checkoutField');
  if (checkinField) checkinField.addEventListener('click', openDesktopCalendar);
  if (checkoutField) checkoutField.addEventListener('click', openDesktopCalendar);
});

window.addEventListener('scroll', handleStickyNav);

// Close modals on backdrop click
document.getElementById('calendarModal')?.addEventListener('click', function(e) {
  if (e.target === this) closeCalendarModal();
});
document.getElementById('desktopCalendarModal')?.addEventListener('click', function(e) {
  if (e.target === this) closeDesktopCalendar();
});
document.getElementById('amenitiesModal')?.addEventListener('click', function(e) {
  if (e.target === this) closeAmenitiesModal();
});