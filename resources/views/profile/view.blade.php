<x-profile-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $user->username }}
        </h2>
    </x-slot>
    <div class="w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        @component('components.profile.masonry-grid', ['user' => $user])@endcomponent
    </div>
</x-profile-layout>
