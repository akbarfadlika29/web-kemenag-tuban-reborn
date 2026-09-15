<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpidInformationDocument extends Model
{
    protected $table = 'ppid_information_documents';

    protected $fillable = [
        'information_id',
        'media_id',
        'title',
        'description',
        'version',
        'document_status',
        'document_date',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function information(): BelongsTo
    {
        return $this->belongsTo(
            PpidInformation::class,
            'information_id'
        );
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'media_id'
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match (
            $this->document_status
        ) {
            'active' =>
                'Aktif',

            'superseded' =>
                'Digantikan',

            'expired' =>
                'Kedaluwarsa',

            default =>
                $this->document_status,
        };
    }
}