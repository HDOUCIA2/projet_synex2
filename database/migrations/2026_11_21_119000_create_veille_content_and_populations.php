<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veille_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page', 24);
            $table->string('key', 64)->unique();
            $table->string('eyebrow', 120)->nullable();
            $table->string('title', 180);
            $table->string('accent', 180)->nullable();
            $table->text('description')->nullable();
            $table->text('secondary_text')->nullable();
            $table->string('button_text', 120)->nullable();
            $table->string('secondary_button_text', 120)->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['page', 'display_order']);
        });

        Schema::create('veille_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('veille_sections')->cascadeOnDelete();
            $table->string('component_key', 64)->nullable();
            $table->string('title', 180);
            $table->string('value', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->string('color', 24)->default('primary');
            $table->string('animation', 24)->default('fade-up');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['section_id', 'component_key']);
            $table->index(['section_id', 'is_active', 'display_order']);
        });

        Schema::create('province_populations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('population');
            $table->string('source', 255)->nullable();
            $table->timestamps();

            $table->unique(['province_id', 'year']);
            $table->index('year');
        });

        $now = now();
        $sections = [
            ['page' => 'home', 'key' => 'home_hero', 'eyebrow' => 'Veille active', 'title' => 'Le conflit, lisible', 'accent' => 'avant la crise.', 'description' => 'Chaque incident du Tchad méridional est géolocalisé à l’échelle du village, qualifié par ses acteurs et mis en perspective avec la population concernée. Les violences basées sur le genre y sont traitées avec la même importance que les autres conflits.', 'button_text' => 'Entrer dans la salle de situation', 'secondary_button_text' => 'Voir la veille', 'display_order' => 10],
            ['page' => 'home', 'key' => 'home_preview', 'eyebrow' => null, 'title' => 'Couverture en cours', 'description' => 'Trois provinces sous veille continue. Vingt en préparation.', 'secondary_text' => 'Données d’illustration, non issues de la veille.', 'display_order' => 20],
            ['page' => 'home', 'key' => 'home_highlights', 'eyebrow' => null, 'title' => 'Repères de la veille', 'display_order' => 30],
            ['page' => 'home', 'key' => 'home_coverage', 'eyebrow' => 'Veille active', 'title' => 'La portée', 'secondary_text' => '« Ce qui commence à Toura ne s’arrête pas à Toura. »', 'display_order' => 40],
            ['page' => 'home', 'key' => 'home_features', 'eyebrow' => 'La veille', 'title' => 'Cinq lectures du terrain', 'description' => 'Les incidents, les réponses de paix, les acteurs, les violences basées sur le genre et la gravité rapportée.', 'display_order' => 50],
            ['page' => 'home', 'key' => 'home_severity', 'eyebrow' => 'Gravité rapportée', 'title' => 'Décès pour 100 000 habitants', 'description' => 'Le bilan pour 100 000 habitants. Un district peu peuplé cesse d’être invisible.', 'secondary_text' => 'Seuils indicatifs, calculés sur la sélection courante. Les données de veille étant partielles, l’indice compare des territoires entre eux, il ne mesure pas un niveau absolu de violence.', 'button_text' => 'Voir la veille', 'display_order' => 60],
            ['page' => 'home', 'key' => 'home_cta', 'eyebrow' => 'Salle de situation', 'title' => 'La salle de situation, maintenant.', 'description' => 'Données d’illustration, non issues de la veille.', 'button_text' => 'Ouvrir Synex', 'display_order' => 70],
            ['page' => 'dashboard', 'key' => 'dashboard_header', 'eyebrow' => null, 'title' => 'La salle de situation, maintenant.', 'description' => 'Les incidents du Tchad méridional, leurs acteurs, leurs lieux et leurs conséquences.', 'display_order' => 10],
            ['page' => 'dashboard', 'key' => 'dashboard_metrics', 'eyebrow' => null, 'title' => 'Indicateurs de la sélection', 'description' => 'Les chiffres évoluent selon les filtres appliqués.', 'display_order' => 20],
            ['page' => 'dashboard', 'key' => 'dashboard_charts', 'eyebrow' => null, 'title' => 'Analyses de la veille', 'description' => 'Explorer les événements recensés selon la sélection courante.', 'display_order' => 30],
            ['page' => 'dashboard', 'key' => 'dashboard_map', 'eyebrow' => null, 'title' => 'Carte des événements géolocalisés', 'description' => 'Les points suivent les filtres appliqués et se regroupent automatiquement quand la carte est dézoomée.', 'display_order' => 40],
            ['page' => 'dashboard', 'key' => 'dashboard_journal', 'eyebrow' => null, 'title' => 'Journal des événements', 'description' => null, 'display_order' => 50],
        ];

        foreach ($sections as &$section) {
            $section['accent'] ??= null;
            $section['description'] ??= null;
            $section['secondary_text'] ??= null;
            $section['button_text'] ??= null;
            $section['secondary_button_text'] ??= null;
            $section['is_active'] = true;
            $section['created_at'] = $now;
            $section['updated_at'] = $now;
        }
        unset($section);
        DB::table('veille_sections')->insert($sections);

        $cards = [
            ['section' => 'home_preview', 'title' => 'Provinces sous veille continue', 'value' => '03', 'icon' => 'bx-radar', 'color' => 'primary'],
            ['section' => 'home_preview', 'title' => 'Provinces en préparation', 'value' => '20', 'icon' => 'bx-map', 'color' => 'warning'],
            ['section' => 'home_preview', 'title' => 'Provinces suivies', 'description' => 'Logone Occidental · Logone Oriental · Mandoul · Moyen-Chari · Tandjilé', 'icon' => 'bx-map-alt', 'color' => 'success'],
            ['section' => 'home_highlights', 'title' => 'Depuis novembre 2021'],
            ['section' => 'home_highlights', 'title' => 'Géolocalisés au village'],
            ['section' => 'home_highlights', 'title' => 'Des éleveurs aux autorités'],
            ['section' => 'home_highlights', 'title' => 'Décès rapportés à la population'],
            ['section' => 'home_coverage', 'title' => 'Le socle méridional', 'value' => '03', 'description' => 'Cinq années de suivi continu, avec un protocole de qualification stabilisé et des seuils de gravité calibrés.', 'color' => 'primary'],
            ['section' => 'home_coverage', 'title' => 'Le Tchad entier', 'value' => '23', 'description' => 'Du Logone au Tibesti : une même maille sanitaire, les mêmes dénominateurs et un indice comparable partout.', 'color' => 'warning'],
            ['section' => 'home_coverage', 'title' => 'Les pays voisins', 'value' => '05', 'description' => 'Les corridors de transhumance vers le Cameroun, la Centrafrique, le Niger, le Nigeria, le Soudan.', 'color' => 'danger'],
            ['section' => 'home_features', 'title' => 'Les incidents', 'description' => 'Affrontements, rixes, opérations de sécurité. Décès, blessés, enlèvements, déplacés, comptés un par un.', 'icon' => 'bx-radar', 'color' => 'primary'],
            ['section' => 'home_features', 'title' => 'Réponses de paix', 'description' => 'Arbitrages, médiations, accords locaux. La veille suit aussi ce qui apaise.', 'icon' => 'bx-analyse', 'color' => 'warning'],
            ['section' => 'home_features', 'title' => 'Qui affronte qui', 'description' => 'Une matrice acteur contre acteur. Les dyades qui reviennent sautent aux yeux.', 'icon' => 'bx-network-chart', 'color' => 'success'],
            ['section' => 'home_features', 'title' => 'Violences basées sur le genre', 'description' => 'Un type de conflit à part entière. Agression sexuelle, viol, violence conjugale, mariage précoce, déni de ressources.', 'icon' => 'bx-shield-quarter', 'color' => 'danger'],
            ['section' => 'home_features', 'title' => 'Gravité rapportée', 'description' => 'Le bilan pour 100 000 habitants. Un district peu peuplé cesse d’être invisible.', 'icon' => 'bx-bar-chart-alt-2', 'color' => 'dark'],
            ['section' => 'home_severity', 'title' => 'Faible < 3', 'value' => '01', 'description' => 'Veille de routine. Les mécanismes locaux de médiation suffisent.', 'color' => 'success'],
            ['section' => 'home_severity', 'title' => 'Modérée · 3–8', 'value' => '02', 'description' => 'Point d’attention. Renforcement des cadres de concertation recommandé.', 'color' => 'warning'],
            ['section' => 'home_severity', 'title' => 'Élevée > 8', 'value' => '03', 'description' => 'Alerte. Priorisation des moyens et réponse coordonnée à l’échelle provinciale.', 'color' => 'danger'],
            ['section' => 'dashboard_metrics', 'component_key' => 'event_count', 'title' => 'Événements recensés', 'description' => 'Nombre d’événements dans la sélection.', 'icon' => 'bx-list-ul', 'color' => 'primary'],
            ['section' => 'dashboard_metrics', 'component_key' => 'deaths_total', 'title' => 'Décès enregistrés', 'description' => 'Total des décès renseignés.', 'icon' => 'bx-plus-medical', 'color' => 'danger'],
            ['section' => 'dashboard_metrics', 'component_key' => 'injuries_total', 'title' => 'Blessés enregistrés', 'description' => 'Total des blessés renseignés.', 'icon' => 'bx-band-aid', 'color' => 'warning'],
            ['section' => 'dashboard_metrics', 'component_key' => 'locality_count', 'title' => 'Localités documentées', 'description' => 'Nombre de localités distinctes.', 'icon' => 'bx-map', 'color' => 'success'],
            ['section' => 'dashboard_charts', 'component_key' => 'eventsByYear', 'title' => 'Événements par année', 'description' => 'Nombre d’événements recensés dans la sélection.'],
            ['section' => 'dashboard_charts', 'component_key' => 'eventsByProvince', 'title' => 'Répartition par province', 'description' => 'Part des événements recensés.'],
            ['section' => 'dashboard_charts', 'component_key' => 'eventsByType', 'title' => 'Types d’événement', 'description' => 'Les huit catégories les plus fréquentes.'],
            ['section' => 'dashboard_charts', 'component_key' => 'eventsByTheme', 'title' => 'Thématiques principales', 'description' => 'Les huit thématiques les plus fréquentes.'],
            ['section' => 'dashboard_charts', 'component_key' => 'impacts', 'title' => 'Impacts documentés', 'description' => 'Somme des valeurs renseignées dans le fichier.'],
            ['section' => 'dashboard_charts', 'component_key' => 'death_rate', 'title' => 'Décès pour 100 000 habitants', 'description' => 'Taux annuel calculé par province à partir des populations saisies par l’administration.', 'color' => 'danger'],
        ];

        $sectionIds = DB::table('veille_sections')->pluck('id', 'key');
        $rows = [];
        foreach ($cards as $index => $card) {
            $sectionKey = $card['section'];
            unset($card['section']);
            $rows[] = [
                'section_id' => $sectionIds[$sectionKey],
                'component_key' => $card['component_key'] ?? null,
                'title' => $card['title'],
                'value' => $card['value'] ?? null,
                'description' => $card['description'] ?? null,
                'icon' => $card['icon'] ?? null,
                'color' => $card['color'] ?? 'primary',
                'animation' => 'fade-up',
                'display_order' => count(array_filter($rows, fn (array $row) => $row['section_id'] === $sectionIds[$sectionKey])) + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('veille_cards')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('province_populations');
        Schema::dropIfExists('veille_cards');
        Schema::dropIfExists('veille_sections');
    }
};
