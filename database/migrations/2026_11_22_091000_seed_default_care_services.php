<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('care_services')->insertOrIgnore([
            [
                'name' => 'Soins de santé',
                'slug' => 'soins-de-sante',
                'description' => 'Orientation vers des services de santé adaptés.',
                'icon' => 'bx-plus-medical',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Assistance juridique',
                'slug' => 'assistance-juridique',
                'description' => 'Information et orientation juridique.',
                'icon' => 'bx-briefcase',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Soutien psychosocial',
                'slug' => 'soutien-psychosocial',
                'description' => 'Orientation vers un accompagnement psychosocial.',
                'icon' => 'bx-heart',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hébergement',
                'slug' => 'hebergement',
                'description' => 'Information sur les possibilités d’hébergement.',
                'icon' => 'bx-home-heart',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Réinsertion économique',
                'slug' => 'reinsertion-economique',
                'description' => 'Orientation vers des dispositifs d’autonomisation.',
                'icon' => 'bx-leaf',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        // The default categories are retained if this seed migration is rolled back.
    }
};
