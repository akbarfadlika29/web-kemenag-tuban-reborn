<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'parent_id',
        'page_id',
        'label',
        'location',
        'type',
        'url',
        'route_name',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )
            ->orderBy('sort_order')
            ->orderBy('label');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()
            ->with('childrenRecursive');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function descendantIds(): array
    {
        $this->loadMissing('childrenRecursive');

        return $this->collectDescendantIds(
            $this->childrenRecursive
        );
    }

    private function collectDescendantIds(
        Collection $children
    ): array {
        $ids = [];

        foreach ($children as $child) {
            $ids[] = $child->id;

            $ids = array_merge(
                $ids,
                $this->collectDescendantIds(
                    $child->childrenRecursive
                )
            );
        }

        return $ids;
    }
}
