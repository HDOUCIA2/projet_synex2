<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('summary', 500);
            $table->longText('content');
            $table->string('image_path');
            $table->dateTime('published_at');
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_news');
    }
};
