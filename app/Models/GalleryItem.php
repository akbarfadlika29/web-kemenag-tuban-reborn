<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    protected $fillable = [
        'gallery_id',
        'media_id',
        'caption',
        'alt_text',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(
            Gallery::class
        );
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(
            Media::class
        );
    }
}