<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProgramNews extends Model
{
    public const CATEGORIES = [
        'analyse' => 'Analyse',
        'veille' => 'Veille',
        'paix' => 'Paix',
        'publication' => 'Publication',
    ];

    public const REGIONS = [
        'nord' => 'Nord',
        'sud' => 'Sud',
        'est' => 'Est',
        'ouest' => 'Ouest',
    ];

    protected $fillable = [
        'title',
        'summary',
        'content',
        'image_path',
        'category',
        'region',
        'published_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where('published_at', '<=', now());
    }
}
