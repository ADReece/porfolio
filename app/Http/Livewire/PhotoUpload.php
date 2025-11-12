<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PhotoUpload extends Component
{
    public $setId;
    public $uploadCount = 0;

    protected $listeners = ['fileUploaded' => 'handleFileUploaded'];

    public function mount($setId = null)
    {
        $this->setId = $setId;
    }

    public function handleFileUploaded()
    {
        $this->uploadCount++;

        // Emit event to refresh the photos grid
        $this->emit('photoUploaded');
    }

    public function render()
    {
        return view('livewire.photo-upload');
    }
}
