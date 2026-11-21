<?php

namespace Database\Seeders;

use App\Models\ProgramNews;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProgramNewsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            [
                'title' => 'EXEMPLE FICTIF 01 — Atelier de lancement du programme',
                'summary' => 'Exemple fictif pour la démonstration : une équipe présente les objectifs et les étapes d’un programme de suivi territorial.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Dans ce scénario de démonstration, une équipe organise une rencontre de lancement pour expliquer les objectifs du programme, les outils de suivi et les prochaines étapes. Aucun événement réel n’est décrit.',
                'published_at' => '2026-11-05 09:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 02 — Formation à la collecte des données',
                'summary' => 'Exemple fictif pour la démonstration : des participants découvrent un protocole commun de collecte et de vérification des informations.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Le scénario imagine une session de formation autour de la collecte, de la vérification et de la documentation des données. Il ne correspond à aucune activité réellement menée.',
                'published_at' => '2026-10-12 10:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 03 — Présentation d’un prototype cartographique',
                'summary' => 'Exemple fictif pour la démonstration : une maquette de carte aide à visualiser des informations territoriales.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Dans cette histoire inventée, une équipe présente une maquette cartographique et recueille des propositions d’amélioration. Aucune carte ni donnée réelle n’est utilisée.',
                'published_at' => '2026-09-17 14:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 04 — Rencontre autour du protocole de veille',
                'summary' => 'Exemple fictif pour la démonstration : des partenaires échangent sur les étapes d’un protocole de veille partagé.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Le récit met en scène une rencontre de travail destinée à comparer des méthodes et à clarifier les rôles. Cette rencontre n’a pas réellement eu lieu.',
                'published_at' => '2026-08-03 08:30:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 05 — Échange sur la qualité des informations',
                'summary' => 'Exemple fictif pour la démonstration : une discussion porte sur la cohérence, la traçabilité et la qualité des données.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Le scénario décrit un échange technique sur la qualité des informations, les contrôles à effectuer et la manière de documenter les corrections.',
                'published_at' => '2026-06-22 11:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 06 — Séance de prise en main du tableau de bord',
                'summary' => 'Exemple fictif pour la démonstration : une équipe apprend à parcourir les indicateurs et les filtres d’un tableau de bord.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Dans cette situation inventée, des utilisateurs s’exercent à consulter un tableau de bord, à choisir des filtres et à interpréter des indicateurs de test.',
                'published_at' => '2026-04-15 09:30:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 07 — Réunion de planification annuelle',
                'summary' => 'Exemple fictif pour la démonstration : une réunion permet de proposer des priorités et un calendrier de travail.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Le récit présente une réunion de planification durant laquelle les participants formulent des priorités et un calendrier indicatif.',
                'published_at' => '2025-12-10 10:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 08 — Présentation d’un guide méthodologique',
                'summary' => 'Exemple fictif pour la démonstration : un guide de travail est présenté et commenté par un groupe de participants.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Le scénario imagine la présentation d’un guide méthodologique, suivie d’une collecte de remarques pour une prochaine version.',
                'published_at' => '2025-10-02 13:30:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 09 — Atelier sur la protection des données',
                'summary' => 'Exemple fictif pour la démonstration : les participants discutent de principes de confidentialité et de gestion des accès.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Il décrit un atelier imaginaire consacré à la confidentialité, à la limitation des accès et aux bonnes pratiques de traitement des informations.',
                'published_at' => '2025-08-14 09:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 10 — Consultation sur les besoins des utilisateurs',
                'summary' => 'Exemple fictif pour la démonstration : une consultation recueille des idées pour faciliter l’utilisation des outils.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Le récit imagine des échanges avec des utilisateurs afin de mieux comprendre leurs besoins et de proposer des améliorations aux outils.',
                'published_at' => '2025-06-11 15:00:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 11 — Séance de partage d’expériences',
                'summary' => 'Exemple fictif pour la démonstration : plusieurs équipes partagent des méthodes et des pistes d’apprentissage.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Dans ce scénario, plusieurs équipes se retrouvent pour partager des expériences, des difficultés et des idées à tester.',
                'published_at' => '2025-04-23 10:30:00',
            ],
            [
                'title' => 'EXEMPLE FICTIF 12 — Bilan d’une phase pilote',
                'summary' => 'Exemple fictif pour la démonstration : une équipe récapitule les enseignements d’une phase pilote inventée.',
                'content' => 'Cet article est entièrement fictif et sert uniquement à présenter la page Actualités du programme. Ce bilan imaginaire illustre la manière de résumer les objectifs, les apprentissages et les prochaines étapes d’une phase pilote.',
                'published_at' => '2025-02-05 08:00:00',
            ],
        ];

        foreach ($examples as $index => $example) {
            $number = $index + 1;
            $source = public_path("assets/img/elements/{$number}.png");
            $contents = file_get_contents($source);

            if ($contents === false) {
                throw new RuntimeException("Impossible de lire l’image de démonstration {$source}.");
            }

            $imagePath = sprintf('program-news/demo-fictif-%02d.png', $number);
            if (!Storage::disk('public')->put($imagePath, $contents)) {
                throw new RuntimeException("Impossible d’enregistrer l’image de démonstration {$imagePath}.");
            }

            ProgramNews::query()->updateOrCreate(
                ['title' => $example['title']],
                [
                    ...$example,
                    'image_path' => $imagePath,
                    'is_published' => false,
                ]
            );
        }
    }
}
