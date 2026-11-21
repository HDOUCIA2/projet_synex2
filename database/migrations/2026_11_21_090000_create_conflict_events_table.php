<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conflict_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_row')->unique();
            $table->string('event_date_raw', 64)->nullable();
            $table->string('month_label', 24)->nullable();
            $table->unsignedSmallInteger('event_year')->nullable();
            $table->unsignedInteger('time_period')->nullable();
            $table->string('event_type')->nullable();
            $table->string('sub_event')->nullable();
            $table->text('other_event')->nullable();
            $table->string('theme')->nullable();
            $table->string('subtheme')->nullable();
            $table->string('actor_1')->nullable();
            $table->string('association_1')->nullable();
            $table->string('interaction_1')->nullable();
            $table->string('actor_2')->nullable();
            $table->string('association_2')->nullable();
            $table->string('interaction_2')->nullable();
            $table->string('interaction_type')->nullable();
            $table->string('province')->nullable();
            $table->string('department')->nullable();
            $table->string('commune')->nullable();
            $table->string('locality')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('street')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('geoprecision')->nullable();
            $table->string('source')->nullable();
            $table->string('source_type', 80)->nullable();
            $table->text('source_url_1')->nullable();
            $table->text('source_url_2')->nullable();
            $table->text('source_url_3')->nullable();
            $table->boolean('pastoral')->nullable();
            $table->longText('event_notes')->nullable();
            $table->unsignedInteger('deaths')->nullable();
            $table->unsignedInteger('injuries')->nullable();
            $table->unsignedInteger('kidnappings')->nullable();
            $table->unsignedInteger('arrests')->nullable();
            $table->unsignedInteger('property_damage')->nullable();
            $table->unsignedInteger('sexual_violence')->nullable();
            $table->unsignedInteger('displaced_people')->nullable();
            $table->string('conflict_number', 64)->nullable();
            $table->unsignedInteger('sequence')->nullable();
            $table->timestamps();

            $table->index(['event_year', 'province']);
            $table->index(['event_type', 'theme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conflict_events');
    }
};
