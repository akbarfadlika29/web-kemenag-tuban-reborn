<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelatedLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'media_id',
        'name',
        'url',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(
            Media::class
        );
    }
}
