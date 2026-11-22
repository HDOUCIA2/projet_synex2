<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventionInitiative extends Model
{
    public const TYPES = [
        'campaign' => 'Campagne de prévention',
        'mediation' => 'Médiation',
        'arbitration' => 'Arbitrage',
        'agreement' => 'Accord de paix',
    ];

    protected $fillable = [
        'title',
        'type',
        'initiative_date',
        'location',
        'province',
        'source',
        'note',
        'records',
        'display_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'initiative_date' => 'date',
            'records' => 'integer',
            'display_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
