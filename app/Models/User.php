<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'phone',
        'twitter',
        'instagram',
        'facebook',
        'portfolio_display_mode',
        'masonry_columns',
        'photos_per_page',
        'watermark_text'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get all of the settings for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'user_id', 'id');
    }

    public function collections() : HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get all of the photos for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function publicPhotos() : HasMany
    {
        return $this->hasMany(Photo::class)->where('private', false);
    }

    /**
     * Get all of the tags for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, 'user_id', 'id');
    }

    /**
     * Get current disk usage for User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function totalPhotoSize(): float
    {
        return $this->photos->sum('size') / 1024000; //(Size is store in Bytes.  /1024k for mb.)
    }
}
