


let currentStep = 1;
const totalSteps = 4;


document.addEventListener('DOMContentLoaded', function() {
  initializeCheckout();
  setupEventListeners();
  updatePricing();
  updateStepDisplay();
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


function updatePricing() {
  const arrival = document.getElementById('arrivalDate')?.value;
  const departure = document.getElementById('departureDate')?.value;
  const adults = parseInt(document.getElementById('adults')?.value) || 0;
  const children = parseInt(document.getElementById('children')?.value) || 0;

  if (!arrival || !departure) return;

  const arrivalDate = new Date(arrival);
  const departureDate = new Date(departure);
  const nights = Math.ceil((departureDate - arrivalDate) / (1000 * 60 * 60 * 24));

  if (nights > 0 && window.reservationData) {
    const subtotal = window.reservationData.ratePerNight * nights;
    const reservationFee = 500;
    const total = subtotal + reservationFee;
    const balance = total - reservationFee;


    updateElementText('priceNights', nights);
    updateElementText('subtotal', '₱' + subtotal.toLocaleString());
    updateElementText('totalAmount', '₱' + total.toLocaleString());
    updateElementText('balanceAmount', '₱' + balance.toLocaleString());
    updateElementText('balanceAmountText2', '₱' + balance.toLocaleString());


    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const checkInText = arrivalDate.toLocaleDateString('en-US', options);
    updateElementText('checkInDisplay', checkInText);
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

    const form = document.getElementById('reservationForm');
    if (form) {
      form.submit();
    }
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


    const children = parseInt(document.getElementById('children')?.value) || 0;
    const totalGuests = adults + children;

    if (window.reservationData && totalGuests > window.reservationData.maxPax) {
      showAlert('warning', 'Too Many Guests', `This room can accommodate a maximum of ${window.reservationData.maxPax} guests`);
      return false;
    }

  } else if (step === 2) {

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


    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput && !isValidEmail(emailInput.value)) {
      showAlert('warning', 'Invalid Email', 'Please enter a valid email address');
      emailInput.focus();
      return false;
    }

    updateGuestDisplay();
  }

  return true;
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
      nextBtn.innerHTML = '<i class="ri-check-line"></i> Confirm Reservation';
    } else {
      nextBtn.innerHTML = 'Continue <i class="ri-arrow-right-line"></i>';
    }
  }


  const deliveryEstimate = document.getElementById('deliveryEstimate');
  const guestAddressDisplay = document.getElementById('guestAddressDisplay');

  if (deliveryEstimate) {
    deliveryEstimate.style.display = currentStep >= 2 ? 'block' : 'none';
  }

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


function formatCurrency(amount) {
  return '₱' + amount.toLocaleString('en-PH', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
}


function calculateNights(startDate, endDate) {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}


window.nextStep = nextStep;
window.prevStep = prevStep;
window.goToStep = goToStep;
window.switchPaymentMethod = switchPaymentMethod;
