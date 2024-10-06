<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;

class MasonryGrid extends Component
{
    public Collection $photos;

    public int $amount = 5;

    public function load()
    {
        $this->amount = $this->amount + 10;
        $this->dispatchBrowserEvent('media-loaded');
    }
    public function render()
    {
        return view('livewire.masonry-grid', [
            'media' => $this->photos->take($this->amount)
        ]);
    }
}
