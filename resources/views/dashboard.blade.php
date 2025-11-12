<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('profile.view', ['username' => auth()->user()->username]) }}"
               target="_blank"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                View Your Portfolio
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 dark:from-indigo-700 dark:to-purple-800 overflow-hidden shadow-lg sm:rounded-lg">
                <div class="p-8 text-white">
                    <h3 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->name ?? auth()->user()->username }}! 👋</h3>
                    <p class="text-indigo-100">Manage your photography portfolio and collections from here.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            @php
                $collections = auth()->user()->collections()->count();
                $totalPhotos = \App\Models\Photo::where('user_id', auth()->id())->count();
                $publishedCollections = auth()->user()->collections()->where('status', 'Published')->count();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Collections Count -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-lg p-3">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Collections</dt>
                                    <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $collections }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos Count -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-3">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Photos</dt>
                                    <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalPhotos }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Published Count -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-lg p-3">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Published</dt>
                                    <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $publishedCollections }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    <!-- Manage Collections -->
                    <a href="{{ route('collections.index') }}"
                       class="block p-6 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:shadow-lg transition-all group">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-lg p-2 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800 transition-colors">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h4 class="ml-3 text-lg font-semibold text-gray-900 dark:text-gray-100">Manage Collections</h4>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View, edit, and organize your photo collections</p>
                    </a>

                    <!-- Create Collection -->
                    <a href="{{ route('collections.create') }}"
                       class="block p-6 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-green-500 dark:hover:border-green-400 hover:shadow-lg transition-all group">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-2 group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h4 class="ml-3 text-lg font-semibold text-gray-900 dark:text-gray-100">Create Collection</h4>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Start a new photo collection or project</p>
                    </a>

                    <!-- Settings -->
                    <a href="{{ route('profile.edit') }}"
                       class="block p-6 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-purple-500 dark:hover:border-purple-400 hover:shadow-lg transition-all group">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-lg p-2 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h4 class="ml-3 text-lg font-semibold text-gray-900 dark:text-gray-100">Settings</h4>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Configure your profile and portfolio settings</p>
                    </a>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
