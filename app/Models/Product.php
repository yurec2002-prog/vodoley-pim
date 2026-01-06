<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'values' => 'array',   // Превращаем JSON свойств в массив
        'raw_data' => 'array', // Превращаем исходник в массив
    ];

    // Связь с категорией
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
