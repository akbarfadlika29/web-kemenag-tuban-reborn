<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wordpress_attachment_id',
        'unit_id',
        'original_name',
        'file_name',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size',
        'type',
        'title',
        'alt_text',
        'description',
        'is_public',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_public' => 'boolean',
    ];

    public function isUsedByRegulations(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable('regulation_documents')
            && \Illuminate\Support\Facades\DB::table('regulation_documents')
                ->where('media_id', $this->getKey())
                ->exists();
    }

    public function isInUse(): bool
{
    if ($this->isUsedByRegulations()) {
        return true;
    }

    return false;
}

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}

