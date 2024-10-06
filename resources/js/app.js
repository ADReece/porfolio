import './bootstrap';

import Masonry from 'masonry-layout';
import imagesLoaded from 'imagesloaded';

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

Alpine.plugin(intersect)

Alpine.start();


