<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sectionId = DB::table('veille_sections')->where('key', 'home_severity')->value('id');
        DB::table('veille_sections')
            ->where('key', 'home_features')
            ->where('eyebrow', 'La veille')
            ->update(['eyebrow' => 'Veille active', 'updated_at' => now()]);

        if ($sectionId === null) {
            return;
        }

        $updates = [
            ['old_title' => 'Faible < 3', 'title' => 'Faible', 'value' => '< 3'],
            ['old_title' => 'Modérée · 3–8', 'title' => 'Modérée', 'value' => '3 – 8'],
            ['old_title' => 'Élevée > 8', 'title' => 'Élevée', 'value' => '> 8'],
        ];

        foreach ($updates as $update) {
            DB::table('veille_cards')
                ->where('section_id', $sectionId)
                ->where('title', $update['old_title'])
                ->update([
                    'title' => $update['title'],
                    'value' => $update['value'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('veille_sections')
            ->where('key', 'home_features')
            ->where('eyebrow', 'Veille active')
            ->update(['eyebrow' => 'La veille', 'updated_at' => now()]);

        $sectionId = DB::table('veille_sections')->where('key', 'home_severity')->value('id');

        if ($sectionId === null) {
            return;
        }

        $updates = [
            ['title' => 'Faible', 'value' => '< 3', 'old_title' => 'Faible < 3', 'old_value' => '01'],
            ['title' => 'Modérée', 'value' => '3 – 8', 'old_title' => 'Modérée · 3–8', 'old_value' => '02'],
            ['title' => 'Élevée', 'value' => '> 8', 'old_title' => 'Élevée > 8', 'old_value' => '03'],
        ];

        foreach ($updates as $update) {
            DB::table('veille_cards')
                ->where('section_id', $sectionId)
                ->where('title', $update['title'])
                ->where('value', $update['value'])
                ->update([
                    'title' => $update['old_title'],
                    'value' => $update['old_value'],
                    'updated_at' => now(),
                ]);
        }
    }
};
