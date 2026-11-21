<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtheme extends Model
{
    protected $fillable = ['theme_id', 'name'];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
