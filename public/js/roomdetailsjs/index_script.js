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
    selectable: true,
    selectMirror: true,
    selectMinDistance: 5,
    unselectAuto: false,
    height: 'auto',
    fixedWeekCount: false,
    showNonCurrentDates: true,

    validRange: {
      start: new Date()
    },

    select: function(info) {
      handleAvailabilityDateSelect(info);
    },

    dayCellClassNames: function(arg) {
      const classes = [];
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (arg.date < today) {
        classes.push('fc-day-past');
      }

      return classes;
    },

    selectAllow: function(selectInfo) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return selectInfo.start >= today;
    },

    events: function(info, successCallback, failureCallback) {
      // Fetch booked dates from server
      fetch(`/reservation/booked-dates/${roomId}`)
        .then(response => response.json())
        .then(data => {
          successCallback(data);
        })
        .catch(error => {
          console.error('Error fetching bookings:', error);
          // Fallback to sample events if API fails
          const primaryColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--bs-primary').trim() || '#0d6efd';

          const sampleEvents = [
            {
              title: 'Booked',
              start: '2026-02-10',
              end: '2026-02-14',
              color: primaryColor,
              display: 'background',
              extendedProps: {
                status: 'booked',
                guestName: 'John Doe'
              }
            }
          ];
          successCallback(sampleEvents);
        });
    },

    eventClick: function(info) {
      const startDate = info.event.start ? info.event.start.toLocaleDateString() : 'N/A';
      const endDate = info.event.end ? new Date(info.event.end.getTime() - 86400000).toLocaleDateString() : 'N/A';
      const status = info.event.extendedProps.status || 'booked';
      const guestName = info.event.extendedProps.guestName || 'N/A';

      let htmlContent = `
        <div style="text-align: left;">
          <p><strong>Status:</strong> ${status.charAt(0).toUpperCase() + status.slice(1)}</p>
          <p><strong>From:</strong> ${startDate}</p>
          <p><strong>To:</strong> ${endDate}</p>
          ${guestName !== 'N/A' ? `<p><strong>Guest:</strong> ${guestName}</p>` : ''}
        </div>
      `;

      Swal.fire({
        title: 'Booking Details',
        html: htmlContent,
        icon: 'info',
        confirmButtonText: 'OK',
        customClass: {
            title: 'swal-title-azure',
            htmlContainer: 'swal-text-azure',
            confirmButton: 'swal-confirm-azure',
        }
      });
    },

    dateClick: function(info) {
      const clickedDate = info.date;
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (clickedDate < today) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Date',
          text: 'Cannot select past dates',
          confirmButtonText: 'OK',
          customClass: {
            title: 'swal-title-azure',
            htmlContainer: 'swal-text-azure',
            confirmButton: 'swal-confirm-azure',
          }
        });
        return;
      }

      const events = fullCalendar.getEvents();
      const isBooked = events.some(event => {
        const eventStart = new Date(event.start);
        const eventEnd = new Date(event.end);
        eventStart.setHours(0, 0, 0, 0);
        eventEnd.setHours(0, 0, 0, 0);

        return clickedDate >= eventStart && clickedDate < eventEnd;
      });

      if (isBooked) {
        Swal.fire({
          icon: 'warning',
          title: 'Date Unavailable',
          text: 'This date is not available',
          confirmButtonText: 'OK'
        });
      }
    }
  });

  fullCalendar.render();

  addCalendarLegend();

  addSelectionHelper();
}

function addCalendarLegend() {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  if (calendarEl.querySelector('.booking-legend')) return;

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
  if (!calendarEl) return;

  if (calendarEl.querySelector('.selection-helper')) return;

  const helper = document.createElement('div');
  helper.className = 'selection-helper';
  helper.style.cssText = 'margin-top: 1rem; padding: 0.75rem 1rem; background: var(--bs-primary-bg-subtle); border-left: 3px solid var(--bs-primary); border-radius: var(--bs-border-radius); font-size: 0.875rem; color: var(--bs-body-color);';
  helper.innerHTML = '<i class="ri-information-line"></i> Click and drag to select your check-in and check-out dates';

  calendarEl.insertBefore(helper, calendarEl.firstChild);
}

function handleAvailabilityDateSelect(info) {
  selectedCheckin = info.start;

  selectedCheckout = new Date(info.end.getTime() - 86400000);

  if (selectedCheckout <= selectedCheckin) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Selection',
      text: 'Please select at least one night. Check-out must be after check-in.',
      confirmButtonText: 'OK'
    });
    if (fullCalendar) {
      fullCalendar.unselect();
    }
    return;
  }

  const events = fullCalendar.getEvents();
  const hasConflict = events.some(event => {
    const eventStart = new Date(event.start);
    const eventEnd = new Date(event.end);
    eventStart.setHours(0, 0, 0, 0);
    eventEnd.setHours(0, 0, 0, 0);

    return (selectedCheckin < eventEnd && selectedCheckout > eventStart);
  });

  if (hasConflict) {
    Swal.fire({
      icon: 'error',
      title: 'Dates Unavailable',
      text: 'Some of the selected dates are not available. Please choose different dates.',
      confirmButtonText: 'OK'
    });
    if (fullCalendar) {
      fullCalendar.unselect();
    }
    selectedCheckin = null;
    selectedCheckout = null;
    return;
  }

  const checkinStr = formatDate(selectedCheckin);
  const checkoutStr = formatDate(selectedCheckout);

  const checkinDisplay = document.getElementById('checkinDisplay');
  const checkoutDisplay = document.getElementById('checkoutDisplay');

  if (checkinDisplay) checkinDisplay.textContent = checkinStr;
  if (checkoutDisplay) checkoutDisplay.textContent = checkoutStr;

  updatePricing();
  updateMobileFooter();

  smoothScrollToBooking();
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
      setTimeout(() => {
        bookingCard.style.boxShadow = '';
      }, 1500);
    }
  }

  requestAnimationFrame(animation);
}

function easeInOutCubic(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

function initializeCalendars() {

  const desktopCalendarEl = document.getElementById('desktopCalendarPicker');
  if (desktopCalendarEl) {
    desktopCalendar = new FullCalendar.Calendar(desktopCalendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev',
        center: 'title',
        right: 'next'
      },
      selectable: true,
      selectMirror: true,
      selectMinDistance: 5,
      unselectAuto: false,
      height: 'auto',
      fixedWeekCount: false,

      validRange: {
        start: new Date()
      },

      selectAllow: function(selectInfo) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectInfo.start >= today;
      },

      select: function(info) {
        handleDesktopDateSelect(info);
      },

      unselect: function() {
      }
    });
    desktopCalendar.render();
  }

  const mobileCalendarEl = document.getElementById('mobileCalendar');
  if (mobileCalendarEl) {
    mobileCalendar = new FullCalendar.Calendar(mobileCalendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev',
        center: 'title',
        right: 'next'
      },
      selectable: true,
      selectMirror: true,
      selectMinDistance: 5,
      unselectAuto: false,
      height: 'auto',
      fixedWeekCount: false,

      validRange: {
        start: new Date()
      },

      selectAllow: function(selectInfo) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectInfo.start >= today;
      },

      select: function(info) {
        handleMobileDateSelect(info);
      },

      unselect: function() {

      }
    });
    mobileCalendar.render();
  }
}

function handleDesktopDateSelect(info) {
  selectedCheckin = info.start;
  selectedCheckout = new Date(info.end.getTime() - 86400000);

  if (selectedCheckout <= selectedCheckin) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Selection',
      text: 'Please select at least one night. Check-out must be after check-in.',
      confirmButtonText: 'OK'
    });
    if (desktopCalendar) {
      desktopCalendar.unselect();
    }
    return;
  }

  const checkinStr = formatDate(selectedCheckin);
  const checkoutStr = formatDate(selectedCheckout);

  const desktopCheckinValue = document.getElementById('desktopCheckinValue');
  const desktopCheckoutValue = document.getElementById('desktopCheckoutValue');
  const desktopCheckinField = document.getElementById('desktopCheckinField');
  const desktopCheckoutField = document.getElementById('desktopCheckoutField');
  const saveDesktopDatesBtn = document.getElementById('saveDesktopDatesBtn');

  if (desktopCheckinValue) {
    desktopCheckinValue.textContent = checkinStr;
    desktopCheckinValue.classList.remove('placeholder');
  }
  if (desktopCheckoutValue) {
    desktopCheckoutValue.textContent = checkoutStr;
    desktopCheckoutValue.classList.remove('placeholder');
  }
  if (desktopCheckinField) desktopCheckinField.classList.add('selected');
  if (desktopCheckoutField) desktopCheckoutField.classList.add('selected');
  if (saveDesktopDatesBtn) saveDesktopDatesBtn.disabled = false;
}

function handleMobileDateSelect(info) {
  selectedCheckin = info.start;

  selectedCheckout = new Date(info.end.getTime() - 86400000);

  if (selectedCheckout <= selectedCheckin) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Selection',
      text: 'Please select at least one night. Check-out must be after check-in.',
      confirmButtonText: 'OK'
    });
    if (mobileCalendar) {
      mobileCalendar.unselect();
    }
    return;
  }

  const checkinStr = formatDate(selectedCheckin);
  const checkoutStr = formatDate(selectedCheckout);

  const mobileCheckinDisplay = document.getElementById('mobileCheckinDisplay');
  const mobileCheckoutDisplay = document.getElementById('mobileCheckoutDisplay');
  const mobileCheckinField = document.getElementById('mobileCheckinField');
  const mobileCheckoutField = document.getElementById('mobileCheckoutField');
  const saveDatesBtn = document.getElementById('saveDatesBtn');

  if (mobileCheckinDisplay) {
    mobileCheckinDisplay.textContent = checkinStr;
    mobileCheckinDisplay.classList.remove('placeholder');
  }
  if (mobileCheckoutDisplay) {
    mobileCheckoutDisplay.textContent = checkoutStr;
    mobileCheckoutDisplay.classList.remove('placeholder');
  }
  if (mobileCheckinField) mobileCheckinField.classList.add('active');
  if (mobileCheckoutField) mobileCheckoutField.classList.add('active');
  if (saveDatesBtn) saveDatesBtn.disabled = false;
}

function formatDate(date) {
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

function openDesktopCalendar() {
  const modal = document.getElementById('desktopCalendarModal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeDesktopCalendar() {
  const modal = document.getElementById('desktopCalendarModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

function clearDesktopDates() {
  selectedCheckin = null;
  selectedCheckout = null;

  if (desktopCalendar) {
    desktopCalendar.unselect();
  }

  const desktopCheckinValue = document.getElementById('desktopCheckinValue');
  const desktopCheckoutValue = document.getElementById('desktopCheckoutValue');
  const desktopCheckinField = document.getElementById('desktopCheckinField');
  const desktopCheckoutField = document.getElementById('desktopCheckoutField');
  const saveDesktopDatesBtn = document.getElementById('saveDesktopDatesBtn');
  const checkinDisplay = document.getElementById('checkinDisplay');
  const checkoutDisplay = document.getElementById('checkoutDisplay');

  if (desktopCheckinValue) {
    desktopCheckinValue.textContent = 'Select date';
    desktopCheckinValue.classList.add('placeholder');
  }
  if (desktopCheckoutValue) {
    desktopCheckoutValue.textContent = 'Select date';
    desktopCheckoutValue.classList.add('placeholder');
  }
  if (desktopCheckinField) desktopCheckinField.classList.remove('selected');
  if (desktopCheckoutField) desktopCheckoutField.classList.remove('selected');
  if (saveDesktopDatesBtn) saveDesktopDatesBtn.disabled = true;
  if (checkinDisplay) checkinDisplay.textContent = 'Add date';
  if (checkoutDisplay) checkoutDisplay.textContent = 'Add date';

  updatePricing();
  updateMobileFooter();
}

function saveDesktopDates() {
  if (selectedCheckin && selectedCheckout) {
    const checkinDisplay = document.getElementById('checkinDisplay');
    const checkoutDisplay = document.getElementById('checkoutDisplay');

    if (checkinDisplay) checkinDisplay.textContent = formatDate(selectedCheckin);
    if (checkoutDisplay) checkoutDisplay.textContent = formatDate(selectedCheckout);

    updatePricing();
    updateMobileFooter();
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
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeCalendarModal() {
  const modal = document.getElementById('calendarModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

function clearDates() {
  selectedCheckin = null;
  selectedCheckout = null;

  if (mobileCalendar) {
    mobileCalendar.unselect();
  }
  if (desktopCalendar) {
    desktopCalendar.unselect();
  }
  if (fullCalendar) {
    fullCalendar.unselect();
  }

  const mobileCheckinDisplay = document.getElementById('mobileCheckinDisplay');
  const mobileCheckoutDisplay = document.getElementById('mobileCheckoutDisplay');
  const mobileCheckinField = document.getElementById('mobileCheckinField');
  const mobileCheckoutField = document.getElementById('mobileCheckoutField');
  const saveDatesBtn = document.getElementById('saveDatesBtn');
  const checkinDisplay = document.getElementById('checkinDisplay');
  const checkoutDisplay = document.getElementById('checkoutDisplay');

  if (mobileCheckinDisplay) {
    mobileCheckinDisplay.textContent = 'Add date';
    mobileCheckinDisplay.classList.add('placeholder');
  }
  if (mobileCheckoutDisplay) {
    mobileCheckoutDisplay.textContent = 'Add date';
    mobileCheckoutDisplay.classList.add('placeholder');
  }
  if (mobileCheckinField) mobileCheckinField.classList.remove('active');
  if (mobileCheckoutField) mobileCheckoutField.classList.remove('active');
  if (saveDatesBtn) saveDatesBtn.disabled = true;
  if (checkinDisplay) checkinDisplay.textContent = 'Add date';
  if (checkoutDisplay) checkoutDisplay.textContent = 'Add date';

  updatePricing();
  updateMobileFooter();
}

function saveDates() {
  if (selectedCheckin && selectedCheckout) {
    const checkinDisplay = document.getElementById('checkinDisplay');
    const checkoutDisplay = document.getElementById('checkoutDisplay');

    if (checkinDisplay) checkinDisplay.textContent = formatDate(selectedCheckin);
    if (checkoutDisplay) checkoutDisplay.textContent = formatDate(selectedCheckout);

    if (desktopCalendar && selectedCheckin && selectedCheckout) {
      const endDate = new Date(selectedCheckout.getTime() + 86400000); // Add one day for exclusive end
      desktopCalendar.select(selectedCheckin, endDate);
    }

    updatePricing();
    updateMobileFooter();
  }

  closeCalendarModal();
}

// 🔥 UPDATED: Navigate to reservation form with pre-filled data
function proceedToReservation() {
  if (!selectedCheckin || !selectedCheckout) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Information',
      text: 'Please select check-in and check-out dates',
      confirmButtonText: 'OK'
    });
    return;
  }

  // Format dates to YYYY-MM-DD for URL parameters
  const checkinFormatted = selectedCheckin.toISOString().split('T')[0];
  const checkoutFormatted = selectedCheckout.toISOString().split('T')[0];

  // Build URL with pre-filled data
  const params = new URLSearchParams({
    arrival_date: checkinFormatted,
    departure_date: checkoutFormatted,
    adults: guests.adults,
    children: guests.children
  });

  // Navigate to reservation form with pre-filled data
  window.location.href = `/reservation/create/${roomId}?${params.toString()}`;
}

function updateGuestDisplay() {
  const total = guests.adults + guests.children;
  const infantText = guests.infants > 0 ? `, ${guests.infants} infant${guests.infants > 1 ? 's' : ''}` : '';

  const displayElement = document.getElementById('guestDisplay');
  if (displayElement) {
    displayElement.textContent = `${total} guest${total > 1 ? 's' : ''}${infantText}`;
  }

  updateButtonStates();
}

function updateButtonStates() {
  const totalGuests = guests.adults + guests.children;

  const adultsDecrement = document.getElementById('adultsDecrement');
  const adultsIncrement = document.getElementById('adultsIncrement');
  const adultsCount = document.getElementById('adultsCount');

  if (adultsDecrement) adultsDecrement.disabled = guests.adults <= 1;
  if (adultsIncrement) adultsIncrement.disabled = totalGuests >= maxGuests;
  if (adultsCount) adultsCount.textContent = guests.adults;

  const childrenDecrement = document.getElementById('childrenDecrement');
  const childrenIncrement = document.getElementById('childrenIncrement');
  const childrenCount = document.getElementById('childrenCount');

  if (childrenDecrement) childrenDecrement.disabled = guests.children <= 0;
  if (childrenIncrement) childrenIncrement.disabled = totalGuests >= maxGuests;
  if (childrenCount) childrenCount.textContent = guests.children;

  const infantsDecrement = document.getElementById('infantsDecrement');
  const infantsCount = document.getElementById('infantsCount');

  if (infantsDecrement) infantsDecrement.disabled = guests.infants <= 0;
  if (infantsCount) infantsCount.textContent = guests.infants;
}

function incrementGuest(type) {
  const totalGuests = guests.adults + guests.children;

  if (type === 'adults' || type === 'children') {
    if (totalGuests < maxGuests) {
      guests[type]++;
    }
  } else if (type === 'infants') {
    guests[type]++;
  }

  updateGuestDisplay();
}

function decrementGuest(type) {
  if (type === 'adults' && guests[type] > 1) {
    guests[type]--;
  } else if (type !== 'adults' && guests[type] > 0) {
    guests[type]--;
  }

  updateGuestDisplay();
}

function toggleGuestDropdown() {
  const dropdown = document.getElementById('guestDropdown');
  if (dropdown) {
    dropdown.classList.toggle('active');
  }
}

function closeGuestDropdown() {
  const dropdown = document.getElementById('guestDropdown');
  if (dropdown) {
    dropdown.classList.remove('active');
  }
}

function openAmenitiesModal() {
  const modal = document.getElementById('amenitiesModal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeAmenitiesModal() {
  const modal = document.getElementById('amenitiesModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

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

function handleStickyNav() {
  const stickyNav = document.getElementById('stickyNav');
  const scrollPosition = window.scrollY;

  if (stickyNav) {
    if (scrollPosition > 600) {
      stickyNav.classList.add('visible');
    } else {
      stickyNav.classList.remove('visible');
    }
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
          window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
          });

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
      if (rect.top <= 150 && rect.bottom >= 150) {
        currentSection = sectionId;
      }
    }
  });

  document.querySelectorAll('.nav-tab').forEach(tab => {
    const href = tab.getAttribute('href');
    if (href === '#' + currentSection) {
      tab.classList.add('active');
    } else {
      tab.classList.remove('active');
    }
  });
}

let mapInstance = null;

function initMap(latitude, longitude, locationName) {
  const mapElement = document.getElementById('map');
  if (!mapElement) return;

  // 🔥 FIX: remove existing map instance
  if (mapInstance) {
    mapInstance.remove();
    mapInstance = null;
  }

  mapInstance = L.map('map').setView([latitude, longitude], 16);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(mapInstance);

  const marker = L.marker([latitude, longitude]).addTo(mapInstance);
  marker.bindPopup(`<b>${locationName}</b><br>Hotel De SLSU`).openPopup();

  const primaryColor =
    getComputedStyle(document.documentElement)
      .getPropertyValue('--bs-primary')
      .trim() || '#0d6efd';

  L.circle([latitude, longitude], {
    color: primaryColor,
    fillColor: primaryColor,
    fillOpacity: 0.2,
    radius: 25
  }).addTo(mapInstance);
}

document.addEventListener('DOMContentLoaded', () => {
  initMap(
   10.39344739659632, 124.980512039808,
    'SLSU Main Campus'
  );
});


document.addEventListener('DOMContentLoaded', function() {
  const description = document.getElementById('roomDescription');
  if (description) {
    description.classList.add('truncated');
  }

  initializeCalendars();

  initializeScheduler();

  updateButtonStates();


  initNavigationTabs();

  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('guestDropdown');
    const guestsField = document.querySelector('.guests-field');

    if (dropdown && guestsField && !guestsField.contains(event.target)) {
      dropdown.classList.remove('active');
    }
  });

  const mapData = document.getElementById('mapData');
  if (mapData) {
    const lat = parseFloat(mapData.dataset.lat);
    const lng = parseFloat(mapData.dataset.lng);
    const name = mapData.dataset.name;

    if (lat && lng) {
      initMap(lat, lng, name);
    }
  }

  const desktopReserveBtn = document.getElementById('desktopReserveBtn');
  if (desktopReserveBtn) {
    desktopReserveBtn.addEventListener('click', proceedToReservation);
  }

  const checkinField = document.getElementById('checkinField');
  const checkoutField = document.getElementById('checkoutField');

  if (checkinField) {
    checkinField.addEventListener('click', openDesktopCalendar);
  }
  if (checkoutField) {
    checkoutField.addEventListener('click', openDesktopCalendar);
  }
});

window.addEventListener('scroll', handleStickyNav);


const calendarModal = document.getElementById('calendarModal');
if (calendarModal) {
  calendarModal.addEventListener('click', function(e) {
    if (e.target === this) {
      closeCalendarModal();
    }
  });
}

const desktopCalendarModal = document.getElementById('desktopCalendarModal');
if (desktopCalendarModal) {
  desktopCalendarModal.addEventListener('click', function(e) {
    if (e.target === this) {
      closeDesktopCalendar();
    }
  });
}

const amenitiesModal = document.getElementById('amenitiesModal');
if (amenitiesModal) {
  amenitiesModal.addEventListener('click', function(e) {
    if (e.target === this) {
      closeAmenitiesModal();
    }
  });
}
