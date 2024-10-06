<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class ImageProcessed
{
    use InteractsWithSockets, SerializesModels;

    public $set;
    public $imagePath;

    public function __construct($set, $imagePath)
    {
        $this->set = $set;
        $this->imagePath = $imagePath;
    }
}
