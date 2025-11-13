<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'stripe_price_id',
        'stripe_product_id',
        'price',
        'annual_price',
        'annual_stripe_price_id',
        'annual_discount_percent',
        'photo_limit',
        'collection_limit',
        'private_collections',
        'watermarking',
        'selling',
        'video_upload',
        'custom_templates',
        'features',
        'active',
        'sort_order',
        'recommended',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'annual_discount_percent' => 'integer',
        'photo_limit' => 'integer',
        'collection_limit' => 'integer',
        'private_collections' => 'boolean',
        'watermarking' => 'boolean',
        'selling' => 'boolean',
        'video_upload' => 'boolean',
        'custom_templates' => 'boolean',
        'features' => 'array',
        'active' => 'boolean',
        'sort_order' => 'integer',
        'recommended' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isFree(): bool
    {
        return $this->slug === 'free';
    }

    public function priceFor(string $interval = 'month'): ?string
    {
        if ($this->isFree()) return '0.00';
        if ($interval === 'year' && $this->annual_price) return number_format($this->annual_price, 2);
        return number_format($this->price, 2);
    }

    public function stripePriceFor(string $interval = 'month'): ?string
    {
        return $interval === 'year' ? $this->annual_stripe_price_id : $this->stripe_price_id;
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) $this->$feature;
    }
}
