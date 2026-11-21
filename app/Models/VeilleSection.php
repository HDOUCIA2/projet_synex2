<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeilleSection extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'accent',
        'description',
        'secondary_text',
        'button_text',
        'secondary_button_text',
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

    public function cards(): HasMany
    {
        return $this->hasMany(VeilleCard::class, 'section_id')
            ->orderBy('display_order')
            ->orderBy('id');
    }
}
