<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Jobs\ProcessImageUpload;
use App\Models\Collection;
use App\Models\Set;

class ManageSets extends Component
{
    use WithFileUploads;

    public $collectionId;
    public $setName;
    public $photos = [];

    public function mount($collectionId)
    {
        $this->collectionId = $collectionId;
    }

    protected $rules = [
        'setName' => 'required|string|max:255',
        'photos.*' => 'image|max:2048', // 2MB max per photo
    ];

    protected $listeners = ['imageProcessed'];

    public function imageProcessed($set, $imagePath)
    {
        // Handle the image processed event (e.g., display notification or update the UI)
        session()->flash('message', "Image processed and uploaded: {$imagePath}");
    }

    public function addSet()
    {
        $this->validate();

        $set = Set::create([
            'collection_id' => $this->collectionId,
            'name' => $this->setName,
        ]);

        // Dispatch a job for each photo to process and upload it asynchronously
        foreach ($this->photos as $photo) {
            $temporaryPath = $photo->store('uploads/temp');
            // Create a unique name for the photo
            $fileName = uniqid() . '.' . $photo->getClientOriginalExtension();

            // Dispatch the job to process the image and upload to S3
            ProcessImageUpload::dispatch(auth()->user(), $temporaryPath, $set, $fileName);
        }

        session()->flash('message', 'Set created and images are being processed in the background.');

        // Reset the form
        $this->reset(['setName', 'photos']);
    }

    public function render()
    {
        return view('livewire.manage-sets', [
            'collection' => Collection::find($this->collectionId),
            'sets' => Set::where('collection_id', $this->collectionId)->get(),
        ]);
    }
}
