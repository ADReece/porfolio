<x-profile-layout>
    @php($userAccent = $user->portfolio_accent_color ?? '#6366F1')
    @php($userFont = $user->portfolio_font ?? null)
    <style>
        :root { --portfolio-accent: {{ $userAccent }}; }
        a.portfolio-accent-link { color: var(--portfolio-accent); }
        a.portfolio-accent-link:hover { text-decoration: underline; }
        @if($userFont && $userFont !== 'system') body { font-family: '{{ $userFont }}', sans-serif; } @endif
    </style>

    <x-slot name="header">
        <div class="flex flex-col items-center">
            <h2 class="font-bold text-3xl text-gray-900 dark:text-gray-100">
                {{ $user->name ?? $user->username }}
            </h2>
            @if($user->bio)
            <p class="mt-2 text-gray-600 dark:text-gray-400 text-center max-w-2xl">
                {{ $user->bio }}
            </p>
            @endif
        </div>
    </x-slot>

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 overflow-x-hidden">
        @if($collections->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-500 dark:text-gray-400">No collections available yet.</p>
            </div>
        @else
            <!-- Collections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($collections as $collection)
                    <a href="{{ route('profile.collection', ['username' => $user->username, 'collection_id' => $collection->id]) }}"
                       class="group relative overflow-hidden rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.01] origin-center block">

                        <!-- Cover Photo -->
                        <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
                            @if($collection->coverPhoto)
                                <img src="{{ $collection->coverPhoto->getAwsThumbnail() }}"
                                     alt="{{ $collection->name }}"
                                     class="w-full h-64 object-cover"
                                     style="object-position: {{ $collection->cover_photo_object_position ?? 'center center' }};">
                            @else
                                <!-- Placeholder if no cover photo -->
                                <div class="w-full h-64 flex items-center justify-center bg-gradient-to-br from-gray-300 to-gray-400">
                                    <svg class="w-20 h-20 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Overlay with Collection Info -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">{{ $collection->name }}</h3>
                                @if($collection->event_date)
                                    <p class="text-sm text-gray-200">
                                        {{ $collection->event_date->format('F j, Y') }}
                                    </p>
                                @endif
                                @if($collection->private)
                                    <span class="inline-block mt-2 px-3 py-1 bg-yellow-500 text-yellow-900 text-xs font-semibold rounded-full">
                                        🔒 Private
                                    </span>
                                @endif
                                @if($collection->watermarked)
                                    <span class="inline-block mt-2 px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full">
                                        💎 Premium
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Always visible title bar -->
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                            <h3 class="text-white text-lg font-semibold">{{ $collection->name }}</h3>
                            @if($collection->event_date)
                                <p class="text-gray-300 text-sm">{{ $collection->event_date->format('M j, Y') }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-profile-layout>
