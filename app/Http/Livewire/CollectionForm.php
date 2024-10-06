<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Collection;
use Illuminate\Support\Facades\Hash;

class CollectionForm extends Component
{
    public $name;
    public $status;
    public $event_date;
    public $cover_image_id;
    public $private = false;
    public $password;

    protected $rules = [
        'name' => 'required|string|max:255',
        'event_date' => 'required|date',
        'private' => 'boolean',
        'password' => 'nullable|string|min:6|required_if:private,true',
    ];

    public function saveCollection()
    {
        $this->validate();

        Auth::user()->collections()->create([
            'name' => $this->name,
            'status' => 'Draft',
            'event_date' => $this->event_date,
            'private' => $this->private,
            'password' => $this->private ? Hash::make($this->password) : null,
        ]);

        session()->flash('message', 'Collection successfully created.');

        // Reset form after creation
        $this->reset();
    }

    public function render()
    {
        return view('livewire.collection-form');
    }
}
