<?php

return [
    'hero_title' => 'Prévenir les conflits, renforcer la cohésion sociale',
    'hero_description' => 'Des actions concrètes, communautaires et inclusives pour réduire les risques, renforcer le dialogue et favoriser des territoires plus résilients.',
    'source' => [
        'label' => 'Extraction Conflict Data Tchad',
        'from' => '2021-05-27',
        'to' => '2026-02-06',
        'note' => 'Les chiffres correspondent aux enregistrements présents dans l’extraction fournie. Ils ne constituent pas un recensement exhaustif de toutes les actions menées au Tchad.',
        'development_strategic_total' => 142,
        'other_sub_events_not_included' => 13,
    ],
    'metrics' => [
        'campaigns' => 79,
        'mediations' => 31,
        'arbitrations' => 15,
        'peace_agreements' => 4,
    ],
    'actions_by_type' => [
        ['key' => 'campaign', 'label' => 'Campagnes de prévention', 'metric' => 'campaigns', 'color' => '#AF8807'],
        ['key' => 'mediation', 'label' => 'Médiations', 'metric' => 'mediations', 'color' => '#D2AD36'],
        ['key' => 'arbitration', 'label' => 'Arbitrages', 'metric' => 'arbitrations', 'color' => '#E9C66A'],
        ['key' => 'agreement', 'label' => 'Accords de paix', 'metric' => 'peace_agreements', 'color' => '#F2E4BF'],
    ],
    'province_distribution' => [
        ['province' => 'Logone Oriental', 'count' => 65],
        ['province' => 'Moyen-Chari', 'count' => 37],
        ['province' => 'Mandoul', 'count' => 27],
    ],
    'axes' => [
        [
            'number' => '01',
            'icon' => 'bx-book-open',
            'title' => 'L’information là où elle se lit',
            'description' => 'Affiches, brochures et messages de sensibilisation, y compris dans les langues locales.',
            'tag' => 'Information communautaire',
        ],
        [
            'number' => '02',
            'icon' => 'bx-group',
            'title' => 'Les relais communautaires',
            'description' => 'Chefs traditionnels, leaders religieux, associations de femmes et clubs de jeunes.',
            'tag' => 'Acteurs locaux',
        ],
        [
            'number' => '03',
            'icon' => 'bx-map-pin',
            'title' => 'Les zones sûres et à risque',
            'description' => 'Repérage des points d’eau, marchés, axes scolaires et couloirs de transhumance.',
            'tag' => 'Vigilance territoriale',
        ],
        [
            'number' => '04',
            'icon' => 'bx-shield-quarter',
            'title' => 'Les codes de conduite',
            'description' => 'Formation, confidentialité et prévention de l’exploitation et des abus sexuels.',
            'tag' => 'Protection et éthique',
        ],
    ],
    'approach_title' => 'Notre approche de prévention',
    'approach_points' => [
        'Renforcer le dialogue et la cohésion sociale',
        'Impliquer les acteurs locaux',
        'Réduire les facteurs de risque',
        'Promouvoir des territoires pacifiques et résilients',
    ],
];
