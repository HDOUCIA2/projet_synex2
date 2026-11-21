<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conflict_events', function (Blueprint $table) {
            $table->unsignedInteger('source_row')->nullable()->change();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $sourceRow = (int) (DB::table('conflict_events')->max('source_row') ?? 0);

        foreach (DB::table('conflict_events')->whereNull('source_row')->orderBy('id')->pluck('id') as $id) {
            DB::table('conflict_events')->where('id', $id)->update(['source_row' => ++$sourceRow]);
        }

        Schema::table('conflict_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->unsignedInteger('source_row')->nullable(false)->change();
        });
    }
};
