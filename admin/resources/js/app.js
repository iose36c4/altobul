// Altobul Admin - Main JavaScript Entry Point

// Alpine.js components are defined inline in Blade templates
// This file is for any global utilities

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('[x-data*="show: true"]');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            if (msg._x_dataStack && msg._x_dataStack[0].show) {
                msg._x_dataStack[0].show = false;
            }
        }, 5000);
    });
    
    // Confirm delete forms
    document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('onsubmit').match(/confirm\('([^']+)'\)/);
            if (message && !confirm(message[1])) {
                e.preventDefault();
            }
        });
    });
    
    // Add loading state to submit buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
            }
        });
    });
    
    // Copy to clipboard helper
    window.copyToClipboard = function(text) {
        return navigator.clipboard.writeText(text);
    };
    
    // Format numbers
    window.formatNumber = function(num) {
        return new Intl.NumberFormat('es-AR').format(num);
    };
    
    // Format dates
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    };
});

// Global utility functions
window.AltobulAdmin = {
    // Show toast notification
    toast: function(message, type = 'success') {
        const colors = {
            success: 'bg-green-100 border-green-400 text-green-700',
            error: 'bg-red-100 border-red-400 text-red-700',
            warning: 'bg-amber-100 border-amber-400 text-amber-700',
            info: 'bg-blue-100 border-blue-400 text-blue-700'
        };
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg border animate-slide-in ${colors[type] || colors.success}`;
        toast.textContent = message;
        toast.style.animation = 'slide-in 0.3s ease-out';
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slide-in 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    },
    
    // Confirm action
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    // AJAX helper
    api: function(endpoint, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        };
        
        return fetch(endpoint, { ...defaults, ...options })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => Promise.reject(err));
                }
                return res.json();
            });
    }
};

// Add custom styles for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
`;
document.head.appendChild(style);