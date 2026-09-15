<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'short_name',
        'slug',
        'code',
        'type',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'head_name',
        'head_title',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
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

    /**
     * Relasi ke modul Berita
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'unit_id');
    }

    /**
     * Relasi ke modul Pengumuman
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'unit_id');
    }

    /**
     * Relasi ke modul Agenda
     */
    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'unit_id');
    }

    /**
     * Relasi ke modul Layanan Publik
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'unit_id');
    }
}