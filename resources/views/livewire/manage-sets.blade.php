<div>
    <!-- Success message -->
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <h2 class="text-xl mb-4">Manage Sets for Collection: {{ $collection->name }}</h2>

    <!-- Add Set Form -->
    <form wire:submit.prevent="addSet">
        <div class="mb-4">
            <label for="setName" class="block text-gray-700">Set Name</label>
            <input type="text" id="setName" wire:model="setName" class="w-full p-2 border border-gray-300 rounded mt-1">
            @error('setName') <span class="text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="photos" class="block text-gray-700">Upload Photos</label>
            <input type="file" id="photos" wire:model="photos" multiple class="w-full p-2 border border-gray-300 rounded mt-1">
            @error('photos.*') <span class="text-red-600">{{ $message }}</span> @enderror

            @if ($photos)
                <div class="mt-4">
                    <h3 class="text-gray-700">Photo Previews:</h3>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach ($photos as $photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="h-24 w-24 object-cover">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add Set
        </button>
    </form>

    <!-- Display Existing Sets -->
    <div class="mt-8">
        <h3 class="text-lg mb-4">Existing Sets</h3>
        <ul>
            @foreach ($sets as $set)
                <li class="mb-2">
                    <strong>{{ $set->name }}</strong> - Photos: {{ $set->photos->count() }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
