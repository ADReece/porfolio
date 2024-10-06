<x-profile-layout>
    <x-slot name="name">
        {{$user->name}}
    </x-slot>
    <x-slot name="header">
        <div class=" lg:mt-32 font-semibold mx-auto text-center text-gray-800 dark:text-gray-200 leading-tight">
            <h1 class="text-3xl">
                {{__("$user->name") }}
            </h1>
            <h2 class="text-l">
                {{__("@$user->username") }}
            </h2>
            <div class="font-light mt-5">
                {{ $user->bio ?? '' }}
            </div>
        </div>
    </x-slot>
    <div class="w-full mx-auto sm:px-2 lg:px-4 space-y-6">
        <livewire:masonry-grid :photos="$user->photos" />
    </div>
</x-profile-layout>
