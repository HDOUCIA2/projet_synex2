<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'address',
        'contact_email',
        'contact_phone',
        'location',
    ];
}
