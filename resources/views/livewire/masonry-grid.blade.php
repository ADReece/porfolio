<div class="relative">
    <div x-data="masonryData" class="relative">
        <!-- Loading Overlay -->
        <div class="absolute inset-0 m-0 overflow-hidden bg-gray-100 dark:bg-gray-900 z-40 min-h-screen"
             x-show="initialLoading"
             x-transition>
            <div class="loader"></div>
        </div>

        <!-- Masonry Grid Container -->
        <div class="masonry-container w-full min-h-screen">
            <!-- Hidden template that Livewire renders into -->
            <div id="masonry-template" style="display: none;">
                @foreach($media as $index => $m)
                    @php
                        // Calculate the previous page's item count
                        $previousPageCount = ($page - 1) * $perPage;
                        $isNewItem = $index >= $previousPageCount;
                    @endphp
                    <div class="masonry-item" data-photo-id="{{ $m->id }}" wire:key="photo-{{ $m->id }}" data-new-item="{{ $isNewItem ? 'true' : 'false' }}">
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

            <!-- Actual masonry grid that JS controls -->
            <div id="masonry" class="masonry mx-auto" wire:ignore data-masonry-initialized="false" data-is-initial="{{ $isInitialLoad ? 'true' : 'false' }}">
            </div>
        </div>

        <!-- Loading Indicator for Infinite Scroll -->
        @if($hasMore)
            <!-- Scroll trigger (hidden when loading) -->
            <!-- Triggers 200px before reaching the element -->
            <div class="w-full py-8 flex justify-center"
                 x-intersect:enter.margin.200px.once="loadMorePhotos"
                 x-show="!isLoadingMore">
                <div class="text-gray-500 dark:text-gray-400 text-sm">Scroll down to load more...</div>
            </div>

            <!-- Loading indicator (only shows during loading) -->
            <div class="w-full py-8 flex items-center justify-center"
                 x-show="isLoadingMore"
                 x-cloak
                 style="display: none;">
                <div class="loader small"></div>
                <span class="ml-3 text-gray-500 dark:text-gray-400 text-sm">Loading more photos...</span>
            </div>
        @else
            <!-- All photos loaded message -->
            @if($loadedCount > 0)
            <div class="w-full py-8 flex justify-center">
                <div class="text-gray-500 dark:text-gray-400 text-sm">
                    Showing all {{ $loadedCount }} {{ Str::plural('photo', $loadedCount) }}
                </div>
            </div>
            @endif
        @endif
    </div>

    @php($cols = $columns ?? 4);
    @php($mobile_cols = 2)

    @if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
        @php($cols = $mobile_cols)
    @endif

    @php($size = (100 / $cols) - 1)

    <style>
        /* Custom Column-Based Masonry Layout */
        #masonry {
            display: flex;
            width: 100%;
        }

        .masonry-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0 4px;
        }

        .masonry-column:first-child {
            padding-left: 0;
        }

        .masonry-column:last-child {
            padding-right: 0;
        }

        .masonry-item {
            margin-bottom: 8px;
            break-inside: avoid;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.4s ease-out, transform 0.4s ease-out;
        }

        .masonry-item.loaded {
            opacity: 1;
            transform: translateY(0);
        }

        .masonry-item img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Hide sizer elements */
        .masonry-sizer,
        .masonry-gutter-sizer {
            display: none;
        }

        .loader {
            border-radius: 50%;
            width: 5em;
            height: 5em;
            margin: 60px auto;
            font-size: 10px;
            position: relative;
            text-indent: -9999em;
            border-top: 1.1em solid rgba(0, 0, 0, 0.2);
            border-right: 1.1em solid rgba(0, 0, 0, 0.2);
            border-bottom: 1.1em solid rgba(0, 0, 0, 0.2);
            border-left: 1.1em solid #000;
            transform: translateZ(0);
            animation: load8 1.1s infinite linear;
        }

        .loader.small {
            width: 20px;
            height: 20px;
            border-width: 3px;
            margin: 0;
            font-size: 10px;
        }

        /* Dark mode - make spinner white */
        .dark .loader {
            border-top-color: rgba(255, 255, 255, 0.2);
            border-right-color: rgba(255, 255, 255, 0.2);
            border-bottom-color: rgba(255, 255, 255, 0.2);
            border-left-color: rgba(255, 255, 255, 0.9);
        }

        @keyframes load8 {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        // Register Alpine component immediately (before Alpine starts)
        document.addEventListener('alpine:init', () => {
            Alpine.data('masonryData', () => ({
                initialLoading: true,
                isLoadingMore: false,
                loadMoreLock: false,
                columns: [],
                columnCount: {{ $cols }},

                init() {
                    this.$nextTick(() => {
                        this.initMasonry();
                    });

                    // Handle window resize
                    window.addEventListener('resize', () => {
                        this.debounce(() => this.relayout(), 250);
                    });
                },

                initMasonry() {
                    const grid = document.querySelector('#masonry');
                    const template = document.querySelector('#masonry-template');

                    if (!grid || !template) {
                        this.initialLoading = false;
                        return;
                    }

                    // Get all items from the hidden template (where Livewire renders them)
                    const items = Array.from(template.querySelectorAll('.masonry-item'));

                    if (items.length === 0) {
                        this.initialLoading = false;
                        return;
                    }

                    // Clone items and move them to the visible grid
                    const clonedItems = items.map(item => item.cloneNode(true));

                    // Create columns structure with cloned items
                    this.createColumns(grid, clonedItems);

                    // Wait for images to load for better positioning
                    const imgLoad = imagesLoaded(clonedItems, { background: true });

                    imgLoad.on('always', () => {
                        // Only redistribute once all initial images are loaded
                        this.redistributeAllItems();
                        this.initialLoading = false;
                        grid.setAttribute('data-masonry-initialized', 'true');
                        refreshFsLightbox();
                    });

                    // Failsafe
                    setTimeout(() => {
                        if (this.initialLoading) {
                            this.initialLoading = false;
                        }
                    }, 3000);
                },

                createColumns(grid, existingItems) {
                    // Store items temporarily
                    const items = existingItems || Array.from(grid.querySelectorAll('.masonry-item'));

                    // Clear grid
                    grid.innerHTML = '';
                    this.columns = [];

                    // Create column elements
                    for (let i = 0; i < this.columnCount; i++) {
                        const column = document.createElement('div');
                        column.className = 'masonry-column';
                        column.dataset.columnIndex = i;
                        grid.appendChild(column);
                        this.columns.push(column);
                    }

                    // Immediately distribute the items
                    items.forEach((item, index) => {
                        const shortestColumn = this.getShortestColumn();
                        shortestColumn.appendChild(item);

                        // Add loaded class with staggered delay for smooth animation
                        setTimeout(() => {
                            item.classList.add('loaded');
                        }, index * 50); // 50ms delay between each item
                    });
                },

                redistributeAllItems() {
                    if (!this.columns || this.columns.length === 0) return;

                    // Collect all items from all columns
                    const allItems = [];
                    this.columns.forEach(column => {
                        const items = Array.from(column.querySelectorAll('.masonry-item'));
                        allItems.push(...items);
                    });

                    // Clear all columns
                    this.columns.forEach(col => col.innerHTML = '');

                    // Redistribute items
                    allItems.forEach(item => {
                        const shortestColumn = this.getShortestColumn();
                        shortestColumn.appendChild(item);
                    });
                },

                getShortestColumn() {
                    let shortestColumn = this.columns[0];
                    let shortestHeight = this.getColumnHeight(shortestColumn);

                    this.columns.forEach(column => {
                        const height = this.getColumnHeight(column);
                        if (height < shortestHeight) {
                            shortestHeight = height;
                            shortestColumn = column;
                        }
                    });

                    return shortestColumn;
                },

                getColumnHeight(column) {
                    // Calculate total height of all items in column (actual content height)
                    let totalHeight = 0;
                    const items = column.querySelectorAll('.masonry-item');

                    items.forEach(item => {
                        // Use offsetHeight which gives the actual rendered height of each item
                        totalHeight += item.offsetHeight || 0;
                        // Add margin
                        totalHeight += 8;
                    });

                    return totalHeight;
                },

                appendNewItems() {
                    const template = document.querySelector('#masonry-template');
                    if (!template) {
                        this.isLoadingMore = false;
                        return;
                    }

                    // Find new items in the template (Livewire renders all items there)
                    const newTemplateItems = Array.from(template.querySelectorAll('.masonry-item[data-new-item="true"]'));

                    if (newTemplateItems.length > 0) {
                        // Clone the new items
                        const newItems = newTemplateItems.map(item => {
                            const cloned = item.cloneNode(true);
                            // Ensure animation styles are set BEFORE adding to DOM
                            cloned.classList.remove('loaded');
                            cloned.style.opacity = '0';
                            cloned.style.transform = 'translateY(30px)';
                            return cloned;
                        });

                        // Remove the marker from template items
                        newTemplateItems.forEach(item => {
                            item.removeAttribute('data-new-item');
                        });

                        // Wait for images to load BEFORE distributing
                        const imgLoad = imagesLoaded(newItems, { background: true });

                        imgLoad.on('always', () => {
                            // Now that images are loaded, distribute them
                            this.distributeNewItemsWithAnimation(newItems);

                            // Hide loading spinner after distribution
                            this.isLoadingMore = false;

                            // Refresh lightbox to include new items
                            refreshFsLightbox();
                        });
                    } else {
                        // No new items found
                        this.isLoadingMore = false;
                        refreshFsLightbox();
                    }
                },

                distributeNewItemsWithAnimation(items) {
                    items.forEach((item, index) => {
                        // Find the shortest column based on current actual heights
                        const shortestColumn = this.getShortestColumn();

                        // Add item to the shortest column
                        shortestColumn.appendChild(item);

                        // Force reflow to ensure the item is rendered
                        item.offsetHeight;

                        // Trigger animation with staggered delay
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                            item.classList.add('loaded');
                        }, index * 50); // 50ms delay between each new item
                    });
                },

                relayout() {
                    const grid = document.querySelector('#masonry');
                    if (!grid) return;

                    const allItems = Array.from(grid.querySelectorAll('.masonry-item'));
                    this.createColumns(grid, allItems);
                    refreshFsLightbox();
                },

                debounce(func, wait) {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(func, wait);
                },

                loadMorePhotos() {
                    // Prevent multiple simultaneous calls
                    if (this.isLoadingMore || this.loadMoreLock) {
                        return;
                    }

                    // Set lock to prevent rapid-fire calls
                    this.loadMoreLock = true;
                    this.isLoadingMore = true;

                    // Small delay before actually calling Livewire
                    setTimeout(() => {
                        if (window.Livewire && window.Livewire.emit) {
                            window.Livewire.emit('load-more-photos');
                        } else {
                            this.isLoadingMore = false;
                        }

                        // Release lock after a short delay
                        setTimeout(() => {
                            this.loadMoreLock = false;
                        }, 500);
                    }, 100);
                }
            }));
        });

        // Livewire v2 hook for message processing
        document.addEventListener('livewire:load', () => {
            Livewire.hook('message.processed', (message, component) => {
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="masonryData"]'));
                if (alpineComponent) {
                    setTimeout(() => alpineComponent.appendNewItems(), 150);
                }
            });
        });

        // Download request function
        function requestDownload(photoId) {
            window.promptUser({
                title: 'Request Download',
                message: 'Enter your email address to receive the download link:',
                placeholder: 'your@email.com',
                inputType: 'email',
                confirmText: 'Send Link',
                onConfirm: (email) => {
                    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        window.showErrorToast('Please enter a valid email address');
                        return;
                    }

                    fetch(`/photos/${photoId}/request-download`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ email: email })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.showSuccessToast('Download link has been sent to ' + email);
                        } else {
                            window.showErrorToast('Error: ' + (data.message || 'Failed to send download link'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.showErrorToast('An error occurred. Please try again.');
                    });
                }
            });
        }
    </script>
</div>
