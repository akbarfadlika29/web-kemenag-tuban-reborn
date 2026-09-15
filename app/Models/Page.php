<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'unit_id',
        'cover_media_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'template',
        'status',
        'published_at',
        'show_in_menu',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'show_in_menu' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            Page::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this
            ->hasMany(
                Page::class,
                'parent_id'
            )
            ->orderBy('sort_order')
            ->orderBy('title');
    }

public function childrenRecursive(): HasMany
{
    return $this
        ->children()
        ->with([
            'unit',
            'coverMedia',
            'childrenRecursive',
        ]);
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
            );
    }

    public function isPublished(): bool
    {
        return
            $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(
                now()
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

    public function descendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;

            $ids = array_merge(
                $ids,
                $child->descendantIds()
            );
        }

        return $ids;
    }
}