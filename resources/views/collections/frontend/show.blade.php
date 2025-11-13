<x-collection-layout>
    <!-- Full-Page Loading Spinner -->
    <div id="collection-loader" class="fixed inset-0 bg-gray-900 dark:bg-black z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="loader-large"></div>
            <p class="mt-4 text-white text-lg">Loading collection...</p>
        </div>
    </div>

    <!-- Full-screen Hero Section -->
    <div class="relative w-full h-screen -mt-8">
        @if($collection->coverPhoto)
            <!-- Hero Image -->
            <img src="{{ $collection->coverPhoto->getUri() }}"
                 alt="{{ $collection->name }}"
                 class="absolute inset-0 w-full h-full object-cover">

            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        @else
            <!-- Fallback gradient if no cover photo -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900"></div>
        @endif

        <!-- Hero Content -->
        <div class="relative h-full flex flex-col items-center justify-center text-white px-4">
            <h1 class="text-5xl md:text-7xl font-bold text-center mb-4 drop-shadow-2xl">
                {{ $collection->name }}
            </h1>

            @if($collection->event_date)
                <p class="text-xl md:text-2xl text-gray-200 mb-6 drop-shadow-lg">
                    {{ $collection->event_date->format('F j, Y') }}
                </p>
            @endif

            @if($collection->description)
                <p class="text-lg text-gray-200 max-w-2xl text-center mb-8 drop-shadow-lg">
                    {{ $collection->description }}
                </p>
            @endif

            <!-- Badges -->
            <div class="flex gap-3 mb-8">
                @if($collection->private)
                    <span class="px-4 py-2 bg-yellow-500 text-yellow-900 text-sm font-semibold rounded-full">
                        🔒 Private Collection
                    </span>
                @endif
                @if($collection->watermarked)
                    <span class="px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-full">
                        💎 Premium Content
                    </span>
                @endif
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 animate-bounce">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Photo Gallery Section -->
    <div class="py-12 px-8 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto">
            <livewire:masonry-grid :collectionId="$collection->id" />
        </div>
    </div>

    <style>
        .loader-large {
            border: 8px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border-top: 8px solid white;
            width: 80px;
            height: 80px;
            animation: spin-large 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin-large {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        // Hide loader when page is fully loaded
        window.addEventListener('load', function() {
            const loader = document.getElementById('collection-loader');
            if (loader) {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 300);
            }
        });

        // Fallback: Hide after 5 seconds even if load event doesn't fire
        setTimeout(function() {
            const loader = document.getElementById('collection-loader');
            if (loader && loader.style.display !== 'none') {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 300);
            }
        }, 5000);
    </script>
</x-collection-layout>