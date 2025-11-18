<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\User;

class CollectionLayout extends Component
{
    public $user;

    /**
     * Create a new component instance.
     *
     * @param User|null $user
     * @return void
     */
    public function __construct($user = null)
    {
        $this->user = $user;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.collection-layout');
    }
}
