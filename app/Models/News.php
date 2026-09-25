<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'editorial_state',
        'editorial_version',
        'revision_required',
        'rejection_reason',
        'wordpress_post_id',
        'unit_id',
        'category_id',
        'author_id',
        'cover_media_id',
        'title',
        'slug',
        'excerpt',
        'content',
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
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'editorial_version' => 'integer',
            'revision_required' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            NewsCategory::class,
            'category_id'
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'author_id'
        );
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'cover_media_id'
        );
    }

    public function tags(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                NewsTag::class,
                'news_news_tag',
                'news_id',
                'news_tag_id'
            )
            ->withTimestamps();
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

    public function getReadingTimeAttribute(): int
    {
        $text = html_entity_decode(
            strip_tags(
                $this->content ?? ''
            ),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $words = preg_split(
            '/\s+/u',
            trim($text),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $wordCount = count(
            $words ?: []
        );

        return max(
            1,
            (int) ceil(
                $wordCount / 200
            )
        );
    }

    public function getPublicationStateAttribute(): string
    {
        if (
            $this->status === 'draft'
        ) {
            return 'draft';
        }

        if (
            $this->status === 'archived'
        ) {
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
}