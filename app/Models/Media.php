<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class Media extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'user_id',
        'caption',
        'description',
        'url',
        'public',
        'size'
    ];

    public function getAwsMedia()
    {
        return \Storage::disk('s3')->temporaryUrl($this->url, now()->addMinutes(10));
    }

    public function getAwsThumbnail()
    {
        $key = str_replace('media/', 'thumbs/', $this->url);
        return \Storage::disk('s3')->temporaryUrl($key, now()->addMinutes(10));
    }
}
