<?php

namespace App\Support;

final class ConflictCsvSchema
{
    public const COLUMNS = [
        'event_date_raw' => 'date',
        'month_label' => 'mois',
        'event_year' => 'année',
        'time_period' => 'time',
        'event_type' => 'événement',
        'sub_event' => 'sousévénement',
        'other_event' => 'autre',
        'theme' => 'thématique',
        'subtheme' => 'sousthématique',
        'actor_1' => 'acteur1',
        'association_1' => 'assoc1',
        'interaction_1' => 'inter1',
        'actor_2' => 'acteur2',
        'association_2' => 'assoc2',
        'interaction_2' => 'inter2',
        'interaction_type' => 'interaction',
        'province' => 'province',
        'department' => 'département',
        'commune' => 'commune',
        'locality' => 'localité',
        'neighborhood' => 'quartier',
        'street' => 'rue',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'geoprecision' => 'geoprecision',
        'source' => 'source',
        'source_type' => 'type de source',
        'source_url_1' => 'URL1',
        'source_url_2' => 'URL2',
        'source_url_3' => 'URL3',
        'pastoral' => 'pastoral',
        'event_notes' => 'notes décrivant événement',
        'deaths' => 'morts',
        'injuries' => 'blessés',
        'kidnappings' => 'kidnappés',
        'arrests' => 'arrestation',
        'property_damage' => 'dégâts matériels',
        'sexual_violence' => 'violence sexuelle',
        'displaced_people' => 'personnes déplacées',
        'conflict_number' => 'numéro du conflit',
        'sequence' => 'séquence',
    ];

    public static function headers(): array
    {
        return array_values(self::COLUMNS);
    }
}
