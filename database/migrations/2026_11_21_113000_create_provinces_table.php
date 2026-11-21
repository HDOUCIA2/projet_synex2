<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('conflict_events')
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->distinct()
            ->pluck('province')
            ->each(fn (string $name) => DB::table('provinces')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
