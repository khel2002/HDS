/**
 * Guest Dashboard JavaScript
 * Handles interactive features and dynamic updates for the guest dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
  
  // Initialize all features
  initializeAnimations();
  initializeCountdown();
  updateTimeDisplay();
  
  // Auto-refresh certain elements every minute
  setInterval(updateTimeDisplay, 60000);
});

/**
 * Initialize fade-in animations for elements
 */
function initializeAnimations() {
  const animatedElements = document.querySelectorAll('.animate-fade-in');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1
  });
  
  animatedElements.forEach(el => observer.observe(el));
}

/**
 * Initialize countdown for check-out
 */
function initializeCountdown() {
  const nightsCard = document.getElementById('nights-remaining-card');
  
  if (nightsCard) {
    const nightsValueElement = nightsCard.querySelector('.stay-info-value');
    const nightsText = nightsValueElement ? nightsValueElement.textContent.trim() : '0';
    const nights = parseInt(nightsText);
    
    // Add visual emphasis if checkout is soon
    if (nights <= 1 && nights > 0) {
      nightsCard.style.borderColor = 'rgba(255, 170, 0, 0.3)';
      nightsCard.style.background = 'rgba(255, 170, 0, 0.1)';
      
      if (nightsValueElement) {
        nightsValueElement.style.color = '#FFAA00';
      }
    } else if (nights === 0) {
      nightsCard.style.borderColor = 'rgba(255, 61, 113, 0.3)';
      nightsCard.style.background = 'rgba(255, 61, 113, 0.1)';
      
      if (nightsValueElement) {
        nightsValueElement.style.color = '#FF3D71';
      }
    }
  }
}

/**
 * Update time-sensitive displays
 */
function updateTimeDisplay() {
  const now = new Date();
  const hours = now.getHours();
  
  // Update greeting based on time of day
  const greetingElement = document.querySelector('.welcome-greeting');
  if (greetingElement) {
    const currentText = greetingElement.textContent;
    
    // Extract the name (everything after "Welcome, " and before "!")
    const nameMatch = currentText.match(/Welcome,?\s+(.+?)!/);
    const name = nameMatch ? nameMatch[1].trim() : 'Guest';
    
    let timeGreeting = 'Good Morning';
    if (hours >= 12 && hours < 17) {
      timeGreeting = 'Good Afternoon';
    } else if (hours >= 17) {
      timeGreeting = 'Good Evening';
    }
    
    // Update if it doesn't already have a time-based greeting
    if (!currentText.includes('Good Morning') && 
        !currentText.includes('Good Afternoon') && 
        !currentText.includes('Good Evening')) {
      greetingElement.textContent = `${timeGreeting}, ${name}!`;
    }
  }
}

/**
 * Handle service card clicks with analytics
 */
document.querySelectorAll('.service-card').forEach(card => {
  card.addEventListener('click', function(e) {
    const serviceName = this.querySelector('.service-title').textContent;
    console.log(`Service clicked: ${serviceName}`);
    
    // Add visual feedback
    this.style.transform = 'scale(0.98)';
    setTimeout(() => {
      this.style.transform = '';
    }, 150);
  });
});

/**
 * Auto-update request statuses (if using live updates)
 */
function checkRequestUpdates() {
  // This function can be extended to poll for status updates
  // Example: fetch('/guest/requests/status-check')
  console.log('Checking for request updates...');
}

/**
 * Handle info card interactions
 */
document.querySelectorAll('.info-card').forEach(card => {
  card.addEventListener('click', function() {
    const title = this.querySelector('.info-card-title').textContent;
    const content = this.querySelector('.info-card-content').textContent;
    
    // Copy to clipboard functionality for WiFi password, phone numbers, etc.
    if (title === 'WiFi Access' || title === 'Front Desk') {
      const textToCopy = content;
      
      if (navigator.clipboard) {
        navigator.clipboard.writeText(textToCopy).then(() => {
          showNotification(`${title} copied to clipboard!`);
        }).catch(err => {
          console.error('Failed to copy:', err);
        });
      }
    }
  });
});

/**
 * Show notification message
 */
function showNotification(message) {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = 'guest-notification';
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: linear-gradient(135deg, #00D68F, #00B374);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 214, 143, 0.3);
    font-weight: 600;
    font-size: 0.875rem;
    z-index: 9999;
    animation: slideInUp 0.3s ease-out;
  `;
  
  document.body.appendChild(notification);
  
  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.animation = 'slideOutDown 0.3s ease-out';
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}

/**
 * Add CSS for notification animations
 */
const style = document.createElement('style');
style.textContent = `
  @keyframes slideInUp {
    from {
      transform: translateY(100px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOutDown {
    from {
      transform: translateY(0);
      opacity: 1;
    }
    to {
      transform: translateY(100px);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);

/**
 * Handle request item clicks for mobile
 */
document.querySelectorAll('.request-item').forEach(item => {
  item.addEventListener('click', function() {
    // Add ripple effect
    const ripple = document.createElement('span');
    ripple.style.cssText = `
      position: absolute;
      border-radius: 50%;
      background: rgba(0, 128, 255, 0.3);
      width: 20px;
      height: 20px;
      animation: ripple 0.6s ease-out;
      pointer-events: none;
    `;
    
    this.style.position = 'relative';
    this.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
  });
});

// Add ripple animation
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
  @keyframes ripple {
    to {
      width: 200px;
      height: 200px;
      opacity: 0;
    }
  }
`;
document.head.appendChild(rippleStyle);

/**
 * Smooth scroll for anchor links
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

/**
 * Handle window resize events
 */
let resizeTimer;
window.addEventListener('resize', function() {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(function() {
    console.log('Window resized - adjusting layout');
    // Add any resize-specific logic here
  }, 250);
});

/**
 * Add touch feedback for mobile devices
 */
if ('ontouchstart' in window) {
  document.querySelectorAll('.service-card, .btn-modern-action').forEach(element => {
    element.addEventListener('touchstart', function() {
      this.style.opacity = '0.8';
    });
    
    element.addEventListener('touchend', function() {
      this.style.opacity = '1';
    });
  });
}

/**
 * Lazy load images if any
 */
if ('IntersectionObserver' in window) {
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          observer.unobserve(img);
        }
      }
    });
  });
  
  document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
  });
}

/**
 * Console welcome message
 */
console.log('%c Welcome to Your Hotel Dashboard! ', 
  'background: linear-gradient(135deg, #060E4D, #0080FF); color: white; font-size: 16px; padding: 10px 20px; border-radius: 8px;'
);
console.log('Enjoy your stay! 🏨');