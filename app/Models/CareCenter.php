<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CareCenter extends Model
{
    protected $fillable = [
        'name', 'slug', 'structure_type', 'description', 'province', 'department',
        'locality', 'address', 'latitude', 'longitude', 'phone', 'email',
        'opening_hours', 'is_free', 'confidentiality_note', 'is_published',
        'show_on_public_map', 'show_public_detail',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
        'show_on_public_map' => 'boolean',
        'show_public_detail' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(CareService::class, 'care_center_service');
    }
}
