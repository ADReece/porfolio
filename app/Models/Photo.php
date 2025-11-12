<?php

namespace App\Models;

use App\Models\Traits\ScopesPublic;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory, UsesUuid, ScopesPublic;

    protected $fillable = [
        'user_id',
        'set_id',
        'caption',
        'description',
        'url',
        'public',
        'private',
        'size',
        'watermarked',
        'purchased',
        'hide_from_portfolio'
    ];

    protected $casts = [
        'private' => 'boolean',
        'watermarked' => 'boolean',
        'purchased' => 'boolean',
        'hide_from_portfolio' => 'boolean',
    ];

    public function getUri() : string
    {
        // Show watermarked version ONLY if:
        // 1. Photo is explicitly marked as watermarked, OR
        // 2. Photo's set's collection is explicitly marked as watermarked
        // Note: Private collections do NOT automatically show watermarked versions
        $shouldWatermark = $this->watermarked ||
                          ($this->set && $this->set->collection && $this->set->collection->watermarked);

        return $shouldWatermark ? $this->getAwsWatermarked() : $this->getAwsMedia();
    }

    public function shouldShowWatermark() : bool
    {
        // Only show watermark if explicitly marked, not based on privacy
        return $this->watermarked ||
               ($this->set && $this->set->collection && $this->set->collection->watermarked);
    }

    public function getAwsMedia()
    {
        return Storage::disk('s3')->temporaryUrl($this->url, now()->addMinutes(10));
    }

    public function getAwsThumbnail()
    {
        $key = str_replace('photos/', 'thumbnails/', $this->url);
        return Storage::disk('s3')->temporaryUrl($key, now()->addMinutes(10));
    }

    public function getAwsWatermarked()
    {
        $key = str_replace('photos/', 'watermarked/', $this->url);
        return Storage::disk('s3')->temporaryUrl($key, now()->addMinutes(10));
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function set() : BelongsTo
    {
        return $this->belongsTo(Set::class);
    }
}
