<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->json('prevention_page_content')->nullable();
        });

        Schema::create('prevention_initiatives', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('type', 32);
            $table->date('initiative_date')->nullable();
            $table->string('location', 180)->nullable();
            $table->string('province', 100);
            $table->string('source', 180)->nullable();
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('records')->default(1);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'initiative_date']);
        });

        Schema::create('prevention_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('description', 500)->nullable();
            $table->string('file_path');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        $now = now();
        DB::table('prevention_initiatives')->insert([
            [
                'title' => 'Campagnes de prévention documentées à Doba',
                'type' => 'campaign',
                'initiative_date' => '2026-02-06',
                'location' => 'Doba, département de Monts de Lam',
                'province' => 'Logone Oriental',
                'source' => 'Tchadinfos',
                'note' => 'Quatre enregistrements datés distincts dans le CSV ; les lignes ne fournissent pas de description narrative exploitable.',
                'records' => 4,
                'display_order' => 1,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Arbitrage documenté à Beyaman',
                'type' => 'arbitration',
                'initiative_date' => '2025-11-29',
                'location' => 'Beyaman, département de Kouh Est',
                'province' => 'Logone Oriental',
                'source' => 'Tchad Infos',
                'note' => null,
                'records' => 1,
                'display_order' => 2,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Arbitrage documenté à Nderguigui',
                'type' => 'arbitration',
                'initiative_date' => '2025-10-08',
                'location' => 'Nderguigui, département de Mandoul central',
                'province' => 'Mandoul',
                'source' => 'Tchad Infos',
                'note' => null,
                'records' => 1,
                'display_order' => 3,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Accord de paix enregistré à Baké',
                'type' => 'agreement',
                'initiative_date' => '2025-09-26',
                'location' => 'Baké, département de Kouh Ouest',
                'province' => 'Logone Oriental',
                'source' => 'Tchad Infos',
                'note' => null,
                'records' => 1,
                'display_order' => 4,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Arbitrage documenté à Bebolo',
                'type' => 'arbitration',
                'initiative_date' => '2025-09-23',
                'location' => 'Balé / Bebolo, département de Lac Iro',
                'province' => 'Moyen-Chari',
                'source' => 'RESAP-MC',
                'note' => null,
                'records' => 1,
                'display_order' => 5,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('prevention_resources');
        Schema::dropIfExists('prevention_initiatives');

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('prevention_page_content');
        });
    }
};
