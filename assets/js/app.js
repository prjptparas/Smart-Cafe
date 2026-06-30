/**
 * Smart Cafe - Main Application JavaScript
 * 
 * Handles general UI interactions: navbar scroll, scroll animations,
 * toast notifications, and helper utilities.
 */

(function() {
    'use strict';

    // ---- Navbar Scroll Effect ----
    const navbar = document.querySelector('.navbar-smart');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ---- Scroll Animations (Intersection Observer) ----
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    if (animateElements.length > 0) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateElements.forEach(function(el) {
            observer.observe(el);
        });
    }

    // ---- Detect table number from URL ----
    const urlParams = new URLSearchParams(window.location.search);
    const tableParam = urlParams.get('table');
    if (tableParam && SmartCart) {
        SmartCart.setTable(tableParam);
        // Update any table indicator on the page
        const tableIndicators = document.querySelectorAll('.table-number-display');
        tableIndicators.forEach(function(el) {
            el.textContent = tableParam;
        });
    }

})();

// ---- Toast Notification System ----

/**
 * Show a toast notification
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 * @param {string} type - 'success', 'error', or 'info'
 */
function showToast(title, message, type) {
    type = type || 'info';
    
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-exclamation-circle-fill',
        info: 'bi-info-circle-fill'
    };

    const toast = document.createElement('div');
    toast.className = 'toast-smart ' + type;
    toast.innerHTML = 
        '<i class="bi ' + (icons[type] || icons.info) + ' toast-icon"></i>' +
        '<div class="toast-message"><strong>' + title + '</strong>' + 
        (message ? '<br><small>' + message + '</small>' : '') + '</div>' +
        '<button class="toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>';

    container.appendChild(toast);

    // Auto-remove after 3 seconds
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

/**
 * Format price with currency symbol
 */
function formatPrice(amount) {
    return '₹ ' + parseFloat(amount).toFixed(2);
}

/**
 * Get the base URL for API calls
 */
function getBaseUrl() {
    // Find the base URL from the meta tag or construct from current location
    const meta = document.querySelector('meta[name="base-url"]');
    if (meta) {
        return meta.getAttribute('content');
    }
    // Fallback: determine from current path
    const path = window.location.pathname;
    const smartCafeIndex = path.indexOf('/smart-cafe');
    if (smartCafeIndex !== -1) {
        return window.location.origin + path.substring(0, smartCafeIndex + '/smart-cafe'.length);
    }
    return window.location.origin;
}

/**
 * Make an API request
 */
async function apiRequest(endpoint, options) {
    options = options || {};
    const baseUrl = getBaseUrl();
    const url = baseUrl + '/api/' + endpoint;
    
    try {
        const response = await fetch(url, options);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}
