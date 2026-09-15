<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wordpress_term_id',
        'name',
        'slug',
    ];

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(
            News::class,
            'news_news_tag',
            'news_tag_id',
            'news_id'
        )->withTimestamps();
    }
}