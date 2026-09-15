<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'cover_media_id',
        'attachment_media_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'expires_at',
        'is_pinned',
        'view_count',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_pinned' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'cover_media_id'
        );
    }

    public function attachmentMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'attachment_media_id'
        );
    }

    public function scopePublished(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                'published'
            )
            ->whereNotNull(
                'published_at'
            )
            ->where(
                'published_at',
                '<=',
                now()
            )
            ->where(
                function ($query) {
                    $query
                        ->whereNull(
                            'expires_at'
                        )
                        ->orWhere(
                            'expires_at',
                            '>',
                            now()
                        );
                }
            );
    }

    public function scopePinned(
        Builder $query
    ): Builder {
        return $query->where(
            'is_pinned',
            true
        );
    }

    public function isPublished(): bool
    {
        return
            $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now())
            && (
                $this->expires_at === null
                || $this->expires_at->isFuture()
            );
    }

    public function getPublicationStateAttribute(): string
    {
        if ($this->status === 'draft') {
            return 'draft';
        }

        if ($this->status === 'archived') {
            return 'archived';
        }

        if (
            $this->published_at
            && $this->published_at->isFuture()
        ) {
            return 'scheduled';
        }

        if (
            $this->expires_at
            && $this->expires_at->lte(now())
        ) {
            return 'expired';
        }

        return 'published';
    }
}