<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickLink extends Model
{
    use SoftDeletes;

    public const TARGET_PAGE = 'page';

    public const TARGET_NEWS_CATEGORY =
        'news_category';

    public const TARGET_ROUTE = 'route';

    public const TARGET_URL = 'url';

    public const TARGET_TYPES = [
        self::TARGET_PAGE,
        self::TARGET_NEWS_CATEGORY,
        self::TARGET_ROUTE,
        self::TARGET_URL,
    ];

    protected $fillable = [
        'media_id',
        'page_id',
        'news_category_id',
        'label',
        'target_type',
        'route_name',
        'url',
        'sort_order',
        'open_in_new_tab',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'media_id'
        );
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(
            Page::class,
            'page_id'
        );
    }

    public function newsCategory(): BelongsTo
    {
        return $this->belongsTo(
            NewsCategory::class,
            'news_category_id'
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeOrdered(
        Builder $query
    ): Builder {
        return $query
            ->orderBy('sort_order')
            ->orderBy('label');
    }
}
