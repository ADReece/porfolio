<div x-data="masonryData" class="relative">
    <!-- Loading Overlay -->
    <div class="absolute inset-0 m-0 overflow-hidden bg-gray-100 dark:bg-gray-900 z-40 min-h-screen"
         x-show="initialLoading"
         x-transition>
        <div class="loader"></div>
    </div>

    <!-- Masonry Grid Container -->
    <div class="masonry-container w-full min-h-screen">
        <div id="masonry" class="masonry mx-auto" data-masonry-initialized="false">
            <div class="masonry-sizer"></div>
            <div class="masonry-gutter-sizer"></div>

            @foreach($media as $index => $m)
                <div class="masonry-item" data-photo-id="{{ $m->id }}" wire:key="photo-{{ $m->id }}">
                    <div class="relative group overflow-hidden rounded-lg shadow-sm hover:shadow-xl transition-shadow duration-300">
                        <!-- Image (clickable for lightbox) -->
                        <a href="{{ $m->getUri() }}"
                           data-fslightbox="gallery"
                           data-caption="{{ $m->caption ?? '' }}"
                           class="block">
                            <img src="{{ $m->getAwsThumbnail() }}"
                                 alt="{{ $m->caption ?? 'Photo' }}"
                                 class="w-full cursor-pointer"
                                 loading="{{ $index < 10 ? 'eager' : 'lazy' }}"
                            />
                        </a>

                        <!-- Hover Overlay with Buttons -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-end justify-end p-3 opacity-0 group-hover:opacity-100">
                            <!-- Download Request Button -->
                            <button type="button"
                                    onclick="requestDownload('{{ $m->id }}')"
                                    class="mr-2 bg-white hover:bg-gray-100 text-gray-800 rounded-full p-2 shadow-lg transition-transform transform hover:scale-110"
                                    title="Request Download">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>

                            <!-- Lightbox View Button -->
                            <button type="button"
                                    onclick="event.stopPropagation(); this.closest('.masonry-item').querySelector('[data-fslightbox]').click();"
                                    class="bg-white hover:bg-gray-100 text-gray-800 rounded-full p-2 shadow-lg transition-transform transform hover:scale-110"
                                    title="View Full Size">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Caption Overlay (if exists) -->
                        @if($m->caption)
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <p class="text-white text-sm font-medium truncate">{{ $m->caption }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Loading Indicator for Infinite Scroll -->
        @if($hasMore)
        <div class="w-full py-8 flex justify-center"
             x-intersect:enter="loadMorePhotos"
             wire:loading.remove
             wire:target="loadMore">
            <div class="text-gray-500 text-sm">Scroll down to load more...</div>
        </div>
        @endif

        <!-- Wire Loading Indicator (only shows during actual loading AND if there are more items) -->
        @if($hasMore)
        <div wire:loading wire:target="loadMore" class="w-full py-8 flex justify-center">
            <div class="loader small"></div>
            <span class="ml-2 text-gray-500 dark:text-gray-400 text-sm">Loading more photos...</span>
        </div>
        @endif
    </div>

    @php($cols = $columns ?? 4);
    @php($mobile_cols = 2)

    @if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
        @php($cols = $mobile_cols)
    @endif

    @php($size = (100 / $cols) - 1)

    <style>
        .masonry-item,
        .masonry-sizer { width:{{$size}}%;margin-top:5px; box-sizing:border-box; }
        .masonry-sizer { display:none; }
        .masonry-item > img {width: 100%;}
        .masonry-gutter-sizer { width:5px; }

        .loader,
        .loader:after {
            border-radius: 50%;
            width: 5em;
            height: 5em;
        }
        .loader { margin: 60px auto; font-size: 10px; position: relative; text-indent: -9999em; border-top: 1.1em solid rgba(0,0,0, 0.2); border-right: 1.1em solid rgba(0,0,0, 0.2); border-bottom: 1.1em solid rgba(0,0,0, 0.2); border-left: 1.1em solid #000; -webkit-transform: translateZ(0); -ms-transform: translateZ(0); transform: translateZ(0); -webkit-animation: load8 1.1s infinite linear; animation: load8 1.1s infinite linear; }
        .loader.small { width: 2em; height: 2em; border-width: .6em; }
        @-webkit-keyframes load8 { 0% { -webkit-transform: rotate(0deg); transform: rotate(0deg); } 100% { -webkit-transform: rotate(360deg); transform: rotate(360deg); } }
        @keyframes load8 { 0% { -webkit-transform: rotate(0deg); transform: rotate(0deg); } 100% { -webkit-transform: rotate(360deg); transform: rotate(360deg); } }
    </style>

    <script>
        function masonryData() {
            return {
                initialLoading: true,
                msnry: null,
                imagesLoaded: null,
                livewireReady: false,

                init() {
                    // If Livewire already bound, $wire will exist
                    if (this.$wire) {
                        this.livewireReady = true;
                    } else if (typeof window.Livewire !== 'undefined' || typeof window.livewire !== 'undefined') {
                        this.livewireReady = true;
                    } else {
                        document.addEventListener('livewire:load', () => {
                            this.livewireReady = true;
                            console.log('Livewire loaded and ready');
                        });
                    }

                    // Initialize after a short delay to ensure DOM is ready
                    this.$nextTick(() => {
                        this.initMasonry();
                    });
                },

                initMasonry() {
                    const grid = document.querySelector('#masonry');

                    if (!grid) {
                        console.error('Masonry grid element not found');
                        this.initialLoading = false;
                        return;
                    }

                    console.log('Initializing masonry grid...');

                    // Use imagesLoaded to ensure images are loaded before initializing masonry
                    this.imagesLoaded = imagesLoaded(grid, { background: true });

                    this.imagesLoaded.on('progress', () => {
                        if (this.msnry) {
                            this.msnry.layout();
                        }
                    });

                    this.imagesLoaded.on('always', () => {
                        console.log('All images loaded, initializing/updating masonry');
                        if (!this.msnry) {
                            this.msnry = new Masonry(grid, {
                                itemSelector: '.masonry-item',
                                columnWidth: '.masonry-sizer',
                                gutter: '.masonry-gutter-sizer',
                                percentPosition: true,
                                transitionDuration: '0.3s',
                                resize: true,
                                initLayout: true
                            });

                            grid.setAttribute('data-masonry-initialized', 'true');
                            console.log('Masonry initialized successfully');
                        } else {
                            this.msnry.reloadItems();
                            this.msnry.layout();
                            console.log('Masonry re-layouted');
                        }

                        this.initialLoading = false;
                        refreshFsLightbox();
                    });

                    // Failsafe: Hide loading after 3 seconds even if images don't load
                    setTimeout(() => {
                        if (this.initialLoading) {
                            console.warn('Timeout: Forcing masonry to initialize');
                            this.initialLoading = false;
                            if (this.msnry) {
                                this.msnry.layout();
                            }
                        }
                    }, 3000);
                },

                reLayoutMasonry() {
                    if (!this.msnry) return;

                    const grid = document.querySelector('#masonry');
                    const newImagesLoaded = imagesLoaded(grid, { background: true });

                    newImagesLoaded.on('always', () => {
                        this.msnry.reloadItems();
                        this.msnry.layout();
                        refreshFsLightbox();
                    });
                },

                loadMorePhotos() {
                    console.log('Loading more photos triggered...');

                    const resolveComponent = () => {
                        const host = document.querySelector('[x-data="masonryData"]');
                        let container = host;
                        while (container && !container.hasAttribute('wire:id')) {
                            container = container.parentElement;
                        }
                        if (!container) {
                            container = document.querySelector('[wire\\:id]');
                        }
                        if (!container) return null;
                        const id = container.getAttribute('wire:id');
                        const LW = window.Livewire || window.livewire;
                        if (!LW || typeof LW.find !== 'function') return null;
                        return LW.find(id);
                    };

                    const component = resolveComponent();
                    if (!component) {
                        console.warn('Livewire component not resolved yet, retrying shortly');
                        setTimeout(() => this.loadMorePhotos(), 200);
                        return;
                    }

                    try {
                        component.call('loadMore');
                    } catch (error) {
                        console.error('Exception in loadMorePhotos:', error);
                    }
                }
            }
        }

        // Listen for Livewire events
        window.addEventListener('masonry-items-loaded', () => {
            const component = Alpine.$data(document.querySelector('[x-data="masonryData"]'));
            if (component && component.reLayoutMasonry) {
                setTimeout(() => {
                    component.reLayoutMasonry();
                }, 100);
            }
        });

        document.addEventListener('livewire:update', () => {
            const component = Alpine.$data(document.querySelector('[x-data="masonryData"]'));
            if (component && component.msnry) {
                setTimeout(() => {
                    component.reLayoutMasonry();
                }, 200);
            }
        });
    </script>
</div>
