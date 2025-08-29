import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// Global utilities for the Review SAAS application

// Toast notification system
window.showToast = function(message, type = 'success', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-500' :
        'bg-blue-500'
    }`;
    
    toast.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' :
                'fa-info-circle'
            }"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, duration);
};

// Loading overlay utility
window.showLoading = function(message = 'Loading...') {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
    loading.innerHTML = `
        <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-700">${message}</p>
        </div>
    `;
    document.body.appendChild(loading);
};

window.hideLoading = function() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        document.body.removeChild(loading);
    }
};

// Confirm dialog utility
window.confirmAction = function(message, callback, options = {}) {
    const defaultOptions = {
        title: 'Confirm Action',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'warning'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: finalOptions.title,
            text: message,
            icon: finalOptions.type,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: finalOptions.confirmText,
            cancelButtonText: finalOptions.cancelText
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    } else {
        // Fallback to native confirm
        if (confirm(message) && callback) {
            callback();
        }
    }
};

// Copy to clipboard utility
window.copyToClipboard = function(text, successMessage = 'Copied to clipboard!') {
    if (navigator.clipboard && window.isSecureContext) {
        // Use modern clipboard API
        navigator.clipboard.writeText(text).then(() => {
            showToast(successMessage, 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(text, successMessage);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(text, successMessage);
    }
};

function fallbackCopyTextToClipboard(text, successMessage) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast(successMessage, 'success');
    } catch (err) {
        showToast('Failed to copy text', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Format numbers utility
window.formatNumber = function(num, decimals = 0) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(num);
};

// Format currency utility
window.formatCurrency = function(amount, currency = 'INR') {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

// Date formatting utility
window.formatDate = function(date, options = {}) {
    const defaultOptions = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    };
    const finalOptions = { ...defaultOptions, ...options };
    
    return new Intl.DateTimeFormat('en-US', finalOptions).format(new Date(date));
};

// Time ago utility
window.timeAgo = function(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - new Date(date)) / 1000);
    
    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    };
    
    for (const [unit, seconds] of Object.entries(intervals)) {
        const interval = Math.floor(diffInSeconds / seconds);
        if (interval >= 1) {
            return `${interval} ${unit}${interval !== 1 ? 's' : ''} ago`;
        }
    }
    
    return 'just now';
};

// Form validation utility
window.validateForm = function(form) {
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        const errorElement = form.querySelector(`[data-error-for="${input.name}"]`);
        
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            if (errorElement) {
                errorElement.textContent = 'This field is required';
                errorElement.classList.remove('hidden');
            }
        } else {
            input.classList.remove('border-red-500');
            if (errorElement) {
                errorElement.classList.add('hidden');
            }
        }
        
        // Email validation
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                isValid = false;
                input.classList.add('border-red-500');
                if (errorElement) {
                    errorElement.textContent = 'Please enter a valid email address';
                    errorElement.classList.remove('hidden');
                }
            }
        }
    });
    
    return isValid;
};

// AJAX form submission utility
window.submitFormAjax = function(form, options = {}) {
    const defaultOptions = {
        onSuccess: (data) => showToast('Operation completed successfully'),
        onError: (error) => showToast(error.message || 'An error occurred', 'error'),
        showLoading: true,
        resetForm: false
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    if (!validateForm(form)) {
        return Promise.reject(new Error('Form validation failed'));
    }
    
    if (finalOptions.showLoading) {
        showLoading('Processing...');
    }
    
    const formData = new FormData(form);
    const method = form.method || 'POST';
    const url = form.action;
    
    return fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (finalOptions.showLoading) {
            hideLoading();
        }
        
        if (data.success) {
            if (finalOptions.resetForm) {
                form.reset();
            }
            finalOptions.onSuccess(data);
        } else {
            throw new Error(data.message || 'Operation failed');
        }
        
        return data;
    })
    .catch(error => {
        if (finalOptions.showLoading) {
            hideLoading();
        }
        finalOptions.onError(error);
        throw error;
    });
};

// Star rating component
window.initStarRating = function(container, options = {}) {
    const defaultOptions = {
        maxStars: 5,
        initialRating: 0,
        onChange: null,
        readonly: false
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    let currentRating = finalOptions.initialRating;
    
    function createStars() {
        container.innerHTML = '';
        
        for (let i = 1; i <= finalOptions.maxStars; i++) {
            const star = document.createElement('i');
            star.className = `fas fa-star cursor-pointer text-2xl transition-colors ${
                i <= currentRating ? 'text-yellow-400' : 'text-gray-300'
            }`;
            star.dataset.rating = i;
            
            if (!finalOptions.readonly) {
                star.addEventListener('click', () => {
                    currentRating = i;
                    updateStars();
                    if (finalOptions.onChange) {
                        finalOptions.onChange(currentRating);
                    }
                });
                
                star.addEventListener('mouseenter', () => {
                    updateStars(i);
                });
            }
            
            container.appendChild(star);
        }
        
        if (!finalOptions.readonly) {
            container.addEventListener('mouseleave', () => {
                updateStars();
            });
        }
    }
    
    function updateStars(hoverRating = null) {
        const rating = hoverRating || currentRating;
        const stars = container.querySelectorAll('.fa-star');
        
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
    
    createStars();
    
    return {
        setRating: (rating) => {
            currentRating = rating;
            updateStars();
        },
        getRating: () => currentRating
    };
};

// Initialize components on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdowns
    document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        if (trigger && menu) {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                menu.classList.toggle('hidden');
            });
            
            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }
    });
    
    // Initialize copy buttons
    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const text = button.dataset.copy || button.textContent;
            copyToClipboard(text);
        });
    });
    
    // Initialize confirm buttons
    document.querySelectorAll('[data-confirm]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const message = button.dataset.confirm;
            confirmAction(message, () => {
                if (button.href) {
                    window.location.href = button.href;
                } else if (button.form) {
                    button.form.submit();
                } else if (button.onclick) {
                    button.onclick();
                }
            });
        });
    });
    
    // Initialize AJAX forms
    document.querySelectorAll('[data-ajax-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitFormAjax(form);
        });
    });
    
    // Auto-hide flash messages
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        });
    }, 5000);
});

// Error handling for unhandled promise rejections
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    showToast('An unexpected error occurred', 'error');
});

// Service worker registration (if needed)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // Register service worker if available
        // navigator.serviceWorker.register('/sw.js');
    });
}