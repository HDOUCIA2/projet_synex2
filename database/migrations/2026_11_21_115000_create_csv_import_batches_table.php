<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csv_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('sha256', 64)->unique();
            $table->string('original_name');
            $table->unsignedInteger('imported_rows');
            $table->unsignedInteger('skipped_rows');
            $table->foreignId('imported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csv_import_batches');
    }
};
