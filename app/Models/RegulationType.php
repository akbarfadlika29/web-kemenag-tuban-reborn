<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulationType extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function regulations()
    {
        return $this->hasMany(Regulation::class);
    }
}
