<x-collection-layout>
    <h1>{{$collection->name}}</h1>
    <livewire:masonry-grid :media="$collection->sets" />
</x-collection-layout>