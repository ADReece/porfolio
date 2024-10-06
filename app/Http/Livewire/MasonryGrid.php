<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;

class MasonryGrid extends Component
{
    public Collection $media;

    public int $amount = 5;

    public function load()
    {
        $this->amount = $this->amount + 10;
        $this->dispatchBrowserEvent('media-loaded');
    }
    public function render()
    {
        return view('livewire.masonry-grid', [
            'media' => $this->media->take($this->amount)
        ]);
    }
}
