<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public ?\App\Models\User $user = null;

    public function __construct(?\App\Models\User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest', ['user' => $this->user]);
    }
}
