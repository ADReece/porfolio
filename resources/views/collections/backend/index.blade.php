<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl flex">
                    <x-card title="Create New" href="{{route('collections.create')}}">
                        Create New Collection
                    </x-card>
                    @if(!is_null($collections))
                        @foreach($collections as $collection)
                            <x-card title="{{$collection->name}}">{{$collection->event_date}}</x-card>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>