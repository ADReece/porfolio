<?php

namespace App\Http\Livewire;

use App\Models\Photo;
use App\Models\Collection;
use App\Models\User;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;

class MasonryGrid extends Component
{
    // Accept either collection_id, user_id, or set_id
    public $collectionId = null;
    public $userId = null;
    public $setId = null;

    public int $perPage = 20;
    public int $columns = 4;
    public int $page = 1;
    public bool $hasMore = true;
    public int $totalCount = 0;

    public function mount()
    {
        // Get user settings if viewing a user's portfolio
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                $this->perPage = $user->photos_per_page ?? 20;
                $this->columns = $user->masonry_columns ?? 4;
            }
        } elseif ($this->collectionId) {
            // For collections, get owner's settings
            $collection = Collection::find($this->collectionId);
            if ($collection && $collection->user) {
                $this->perPage = $collection->user->photos_per_page ?? 20;
                $this->columns = $collection->user->masonry_columns ?? 4;
            }
        }

        // Calculate total count on mount
        $this->totalCount = $this->getQuery()->count();
        $this->hasMore = $this->totalCount > $this->perPage;
    }

    protected function getQuery(): Builder
    {
        $query = Photo::query();

        if ($this->collectionId) {
            // Get photos from all sets in the collection
            $collection = Collection::findOrFail($this->collectionId);
            $setIds = $collection->sets()->pluck('id');
            $query->whereIn('set_id', $setIds);

            // Filter by privacy if not a private collection
//            if (!$collection->private) {
//                $query->where('private', false);
//            }
        } elseif ($this->userId) {
            // Get photos for a specific user's portfolio
            $query->where('user_id', $this->userId)
                  ->where('hide_from_portfolio', false); // Exclude hidden photos

            // Also exclude photos from collections/sets marked as hidden
            $query->whereHas('set.collection', function($q) {
                $q->where('hide_from_portfolio', false);
            })->whereHas('set', function($q) {
                $q->where('hide_from_portfolio', false);
            });
        } elseif ($this->setId) {
            // Get photos from a specific set
            $query->where('set_id', $this->setId);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function loadMore()
    {
        $this->page++;

        // Check if there are more items to load
        $totalLoaded = $this->page * $this->perPage;
        $this->hasMore = $this->totalCount > $totalLoaded;

        $this->dispatchBrowserEvent('masonry-items-loaded');
    }

    public function render()
    {
        // Only fetch the photos we need for display
        $totalToShow = $this->page * $this->perPage;
        $media = $this->getQuery()
            ->take($totalToShow)
            ->get();

        return view('livewire.masonry-grid', [
            'media' => $media,
            'totalCount' => $this->totalCount,
            'loadedCount' => $media->count(),
            'columns' => $this->columns
        ]);
    }
}
