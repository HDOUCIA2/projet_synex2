<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConflictEvent extends Model
{
    protected $fillable = [
        'source_row',
        'created_by_user_id',
        'status',
        'review_notes',
        'event_type_id',
        'sub_event_id',
        'other_event_id',
        'theme_id',
        'subtheme_id',
        'event_date_raw',
        'month_label',
        'event_year',
        'time_period',
        'event_type',
        'sub_event',
        'other_event',
        'theme',
        'subtheme',
        'actor_1',
        'association_1',
        'interaction_1',
        'actor_2',
        'association_2',
        'interaction_2',
        'interaction_type',
        'province',
        'department',
        'commune',
        'locality',
        'neighborhood',
        'street',
        'latitude',
        'longitude',
        'geoprecision',
        'source',
        'source_type',
        'source_url_1',
        'source_url_2',
        'source_url_3',
        'pastoral',
        'event_notes',
        'deaths',
        'injuries',
        'kidnappings',
        'arrests',
        'property_damage',
        'sexual_violence',
        'displaced_people',
        'conflict_number',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'event_year' => 'integer',
            'time_period' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geoprecision' => 'integer',
            'pastoral' => 'boolean',
            'deaths' => 'integer',
            'injuries' => 'integer',
            'kidnappings' => 'integer',
            'arrests' => 'integer',
            'property_damage' => 'integer',
            'sexual_violence' => 'integer',
            'displaced_people' => 'integer',
            'sequence' => 'integer',
        ];
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function eventTypeRecord(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function subEventRecord(): BelongsTo
    {
        return $this->belongsTo(SubEvent::class, 'sub_event_id');
    }

    public function otherEventRecord(): BelongsTo
    {
        return $this->belongsTo(OtherEvent::class, 'other_event_id');
    }

    public function themeRecord(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }

    public function subthemeRecord(): BelongsTo
    {
        return $this->belongsTo(Subtheme::class, 'subtheme_id');
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'conflict_event_actors')
            ->withPivot(['role', 'association', 'interaction'])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
