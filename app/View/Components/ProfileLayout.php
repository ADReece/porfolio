<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

class ProfileLayout extends Component
{
    /**
     * The user instance.
     *
     * @var \App\Models\User|null
     */
    public $user;

    /**
     * Create a new component instance.
     *
     * @param  \App\Models\User|null  $user
     * @return void
     */
    public function __construct(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.profile');
    }
}
