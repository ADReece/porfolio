<?php

namespace App\Models;

use App\Models\Traits\ScopesPublic;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Photo extends Model
{
    use HasFactory, UsesUuid, ScopesPublic;

    protected $fillable = [
        'user_id',
        'caption',
        'description',
        'url',
        'public',
        'size'
    ];

    public function getUri() : string
    {
        return $this->private ? $this->getAwsWatermarked() : $this->getAwsMedia();
    }

    public function getAwsMedia()
    {
        return \Storage::disk('s3')->temporaryUrl($this->url, now()->addMinutes(10));
    }

    public function getAwsThumbnail()
    {
        $key = str_replace('photos/', 'thumbnails/', $this->url);
        return \Storage::disk('s3')->temporaryUrl($key, now()->addMinutes(10));
    }

    public function getAwsWatermarked()
    {
        $key = str_replace('photos/', 'watermarked/', $this->url);
        return \Storage::disk('s3')->temporaryUrl($key, now()->addMinutes(10));
    }

    public function set() : BelongsTo
    {
        return $this->belongsTo(Set::class);
    }
}
