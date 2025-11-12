<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $set->name }} - {{ $set->collection->name }}
            </h2>
            <a href="{{ route('collections.edit', $set->collection) }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Collection
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Upload Section -->
            <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Upload Photos</h3>
                    @livewire('photo-upload', ['setId' => $set->id], key('upload-'.$set->id))
                </div>
            </div>

            <!-- Photos Grid (Livewire Component for Live Updates) -->
            @livewire('set-photos', ['setId' => $set->id], key('photos-'.$set->id))
        </div>
    </div>


</x-app-layout>

