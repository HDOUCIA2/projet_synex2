<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvincePopulation extends Model
{
    protected $fillable = [
        'province_id',
        'year',
        'population',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'province_id' => 'integer',
            'year' => 'integer',
            'population' => 'integer',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
