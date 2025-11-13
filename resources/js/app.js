import './bootstrap';
import '../css/app.css';

import Masonry from 'masonry-layout';
import imagesLoaded from 'imagesloaded';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';
Dropzone.autoDiscover = false;
window.Dropzone = Dropzone;

window.Masonry = Masonry;
window.imagesLoaded = imagesLoaded;

import Macy from 'macy';

window.Macy = Macy;

import jQuery from 'jquery';

window.$ = jQuery;

import InfiniteScroll from 'infinite-scroll';

window.InfiniteScroll = InfiniteScroll;

import fslightbox from 'fslightbox';

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


