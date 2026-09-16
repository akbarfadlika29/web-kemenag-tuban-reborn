<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpidInformation extends Model
{
    use SoftDeletes;

    protected $table = 'ppid_informations';

    protected $fillable = [
        'category_id',
        'unit_id',

        'title',
        'slug',
        'classification',
        'excerpt',
        'description',

        'information_holder',
        'person_in_charge',
        'information_form',
        'information_format',
        'publication_media',
        'retention_period',
        'retention_unit',
        'availability',
        'access_level',
        'document_number',
        'document_date',
        'effective_date',
        'last_reviewed_at',
        'legal_basis',
        'notes',

        'year',

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
            'year' => 'integer',
            'retention_period' => 'integer',

            'document_date' => 'date',
            'effective_date' => 'date',
            'last_reviewed_at' => 'datetime',
            'published_at' => 'datetime',

            'is_featured' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            PpidCategory::class,
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

    public function documents(): HasMany
    {
        return $this->hasMany(
            PpidInformationDocument::class,
            'information_id'
        )
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryDocument(): HasOne
    {
        return $this->hasOne(
            PpidInformationDocument::class,
            'information_id'
        )
            ->where(
                'is_primary',
                true
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

    public function scopeFeatured(
        Builder $query
    ): Builder {
        return $query->where(
            'is_featured',
            true
        );
    }

    public function scopeClassification(
        Builder $query,
        string $classification
    ): Builder {
        return $query->where(
            'classification',
            $classification
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
            $this->published_at !== null
            && $this->published_at->isFuture()
        ) {
            return 'scheduled';
        }

        return 'published';
    }

    public function getClassificationLabelAttribute(): string
    {
        return match (
            $this->classification
        ) {
            'berkala' =>
                'Informasi Berkala',

            'serta_merta' =>
                'Informasi Serta Merta',

            'setiap_saat' =>
                'Informasi Setiap Saat',
            'dikecualikan' => 'Informasi Dikecualikan',

            default =>
                $this->classification,
        };
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return match (
            $this->availability
        ) {
            'online' =>
                'Online',

            'by_request' =>
                'Melalui Permohonan',

            'both' =>
                'Online & Permohonan',

            default =>
                $this->availability,
        };
    }

    public function getAccessLevelLabelAttribute(): string
    {
        return match (
            $this->access_level
        ) {
            'public' =>
                'Publik',

            'limited' =>
                'Terbatas',

            default =>
                $this->access_level,
        };
    }
}