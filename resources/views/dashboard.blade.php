<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <p class="basis-3">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 -m-1 text-gray-900 dark:text-gray-100 flex flex-row flex-wrap">

                    <x-dashboard.card title="Upload" href="upload" icon="">
                        Upload images and media
                    </x-dashboard.card>

                    <x-dashboard.card title="Albums" href="albums" icon="">
                        Manage Albums
                    </x-dashboard.card>

                    <x-dashboard.card title="Settings" href="settings" icon="">
                        Manage Settings
                    </x-dashboard.card>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
