<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventionResource extends Model
{
    protected $fillable = ['title', 'description', 'file_path', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
