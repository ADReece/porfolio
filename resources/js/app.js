import './bootstrap';
import '../css/app.css';
import './components/modal';

import imagesLoaded from 'imagesloaded';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

// Make libraries available globally
Dropzone.autoDiscover = false;
window.Dropzone = Dropzone;
window.imagesLoaded = imagesLoaded;
window.Toastify = Toastify;

import fslightbox from 'fslightbox';
window.fslightbox = fslightbox;

// Global toast helper functions
window.showToast = function(message, type = 'info') {
    const backgrounds = {
        success: 'linear-gradient(to right, #10b981, #059669)',
        error: 'linear-gradient(to right, #ef4444, #dc2626)',
        warning: 'linear-gradient(to right, #f59e0b, #d97706)',
        info: 'linear-gradient(to right, #3b82f6, #2563eb)',
    };

    Toastify({
        text: message,
        duration: 3000,
        gravity: 'top',
        position: 'right',
        style: {
            background: backgrounds[type] || backgrounds.info,
        },
        stopOnFocus: true,
    }).showToast();
};

window.showSuccessToast = (message) => window.showToast(message, 'success');
window.showErrorToast = (message) => window.showToast(message, 'error');
window.showWarningToast = (message) => window.showToast(message, 'warning');
window.showInfoToast = (message) => window.showToast(message, 'info');

window.fslightbox = fslightbox;



import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

window.Alpine = Alpine;

Alpine.plugin(intersect);

// Wait for Livewire to be fully loaded before starting Alpine
// This ensures Livewire is available when Alpine components try to access it
function startAlpineWhenLivewireReady() {
    if (typeof window.Livewire !== 'undefined') {
        Alpine.start();
    } else {
        setTimeout(startAlpineWhenLivewireReady, 50);
    }
}

// Start the check after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startAlpineWhenLivewireReady);
} else {
    startAlpineWhenLivewireReady();
}


