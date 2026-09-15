<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Regulation extends Model
{
    use SoftDeletes;

    public const LEGAL = [
        'unverified' => 'Belum diverifikasi',
        'in_force' => 'Berlaku',
        'revoked' => 'Dicabut',
        'partial' => 'Berlaku sebagian',
    ];

    public const PUBLICATION = [
        'draft' => 'Draf',
        'published' => 'Terbit',
        'archived' => 'Arsip',
    ];

    protected $fillable = [
        'regulation_type_id', 'unit_id', 'created_by', 'updated_by',
        'title', 'slug', 'number', 'year', 'issuing_authority',
        'subject', 'summary', 'description', 'issued_at', 'effective_at',
        'legal_status', 'legal_status_note', 'source_url',
        'publication_status', 'published_at', 'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'year' => 'integer',
        'issued_at' => 'date',
        'effective_at' => 'date',
        'published_at' => 'datetime',
    ];

    public function type()
    {
        return $this->belongsTo(RegulationType::class, 'regulation_type_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function documents()
    {
        return $this->hasMany(RegulationDocument::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getLegalLabelAttribute()
    {
        return self::LEGAL[$this->legal_status] ?? 'Belum diverifikasi';
    }
}
