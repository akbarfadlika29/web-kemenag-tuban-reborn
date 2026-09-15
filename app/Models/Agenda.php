<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agenda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'cover_media_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'location',
        'start_at',
        'end_at',
        'status',
        'published_at',
        'is_featured',
        'view_count',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'cover_media_id'
        );
    }

    public function scopePublished(
        Builder $query
    ): Builder {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where(
                'published_at',
                '<=',
                now()
            );
    }

    public function scopeUpcoming(
        Builder $query
    ): Builder {
        return $query->where(
            'start_at',
            '>=',
            now()
        );
    }

    public function scopeFeatured(
        Builder $query
    ): Builder {
        return $query->where(
            'is_featured',
            true
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

        return 'published';
    }

    public function getEventStateAttribute(): string
    {
        if ($this->start_at->isFuture()) {
            return 'upcoming';
        }

        if (
            $this->end_at
            && now()->between(
                $this->start_at,
                $this->end_at
            )
        ) {
            return 'ongoing';
        }

        if (
            !$this->end_at
            && $this->start_at->isToday()
        ) {
            return 'ongoing';
        }

        return 'finished';
    }
}