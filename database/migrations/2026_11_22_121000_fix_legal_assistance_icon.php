<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('care_services')
            ->where('slug', 'assistance-juridique')
            ->where('icon', 'bx-scale')
            ->update(['icon' => 'bx-briefcase', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('care_services')
            ->where('slug', 'assistance-juridique')
            ->where('icon', 'bx-briefcase')
            ->update(['icon' => 'bx-scale', 'updated_at' => now()]);
    }
};
