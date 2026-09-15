<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'category_id',
        'unit_id',
        'cover_media_id',

        'title',
        'slug',
        'excerpt',
        'description',

        'requirements',
        'procedure',
        'completion_time',

        'is_free',
        'fee_description',

        'service_output',
        'legal_basis',

        'service_channel',
        'service_url',
        'service_location',
        'service_hours',

        'contact_name',
        'contact_phone',
        'contact_email',

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
            'is_free' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ServiceCategory::class,
            'category_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class,
            'unit_id'
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
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where(
                'published_at',
                '<=',
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

    public function isPublished(): bool
    {
        return
            $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
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

    public function getServiceChannelLabelAttribute(): string
    {
        return match ($this->service_channel) {
            'online' => 'Online',
            'offline' => 'Offline',
            'hybrid' => 'Online & Offline',
            default => $this->service_channel,
        };
    }
}