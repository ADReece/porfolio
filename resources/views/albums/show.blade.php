<x-album-layout>
    <h1>{{$album->name}}</h1>
    <livewire:masonry-grid :media="$album->media" />
</x-album-layout>