<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulationDocument extends Model
{
    protected $fillable = [
        'regulation_id', 'media_id', 'label',
        'document_role', 'sort_order',
    ];

    public function regulation()
    {
        return $this->belongsTo(Regulation::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
