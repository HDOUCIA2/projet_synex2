<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteFooterLink extends Model
{
    protected $fillable = [
        'type',
        'route_name',
        'label',
        'url',
        'icon',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function getHrefAttribute(): string
    {
        return $this->type === 'useful'
            ? route($this->route_name)
            : $this->url;
    }
}
