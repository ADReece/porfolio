<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GeneratedPrint extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'photo_id',
        'template_id',
        'print_data',
        'output_path',
        'status',
        'error_message',
    ];

    protected $casts = [
        'print_data' => 'array',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function outputUrl(): ?string
    {
        if (!$this->output_path || $this->status !== 'completed') {
            return null;
        }
        return Storage::disk('s3')->temporaryUrl($this->output_path, now()->addMinutes(30));
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}
