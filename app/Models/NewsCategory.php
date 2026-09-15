<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wordpress_term_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function news(): HasMany
    {
        return $this->hasMany(
            News::class,
            'category_id'
        );
    }
}