<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'user_id',
        'photo_id',
        'name',
        'description',
        'type',
        'prodigi_sku',
        'price',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isDigital(): bool
    {
        return $this->type === 'digital';
    }

    public function isPrint(): bool
    {
        return !is_null($this->prodigi_sku)
            || in_array($this->type, ['print_poster', 'print_mug', 'print_canvas']);
    }
}

