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
        'care_page_image_path',
        'care_page_content',
        'prevention_page_content',
        'footer_content',
    ];

    protected $casts = [
        'care_page_content' => 'array',
        'prevention_page_content' => 'array',
        'footer_content' => 'array',
    ];
}
