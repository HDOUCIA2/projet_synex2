<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Actor extends Model
{
    protected $fillable = ['name'];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(ConflictEvent::class, 'conflict_event_actors')
            ->withPivot(['role', 'association', 'interaction'])
            ->withTimestamps();
    }
}
