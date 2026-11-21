<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeilleCard extends Model
{
    protected $fillable = [
        'component_key',
        'title',
        'value',
        'description',
        'icon',
        'image_path',
        'color',
        'animation',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(VeilleSection::class, 'section_id');
    }
}
