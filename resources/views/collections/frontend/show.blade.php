<x-collection-layout>
    <h1>{{$collection->name}}</h1>
    <livewire:masonry-grid :photos="$collection->sets[0]->photos" />
</x-collection-layout>