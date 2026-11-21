<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veille_cards', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('icon');
        });

        DB::table('veille_sections')
            ->where('key', 'home_features')
            ->where('description', 'Les incidents, les réponses de paix, les acteurs, les violences basées sur le genre et la gravité rapportée.')
            ->update([
                'description' => 'Cinq angles complémentaires pour comprendre la situation sur le terrain.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('veille_sections')
            ->where('key', 'home_features')
            ->where('description', 'Cinq angles complémentaires pour comprendre la situation sur le terrain.')
            ->update([
                'description' => 'Les incidents, les réponses de paix, les acteurs, les violences basées sur le genre et la gravité rapportée.',
                'updated_at' => now(),
            ]);

        Schema::table('veille_cards', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
