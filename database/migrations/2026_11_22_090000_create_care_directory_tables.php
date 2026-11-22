<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('care_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('structure_type', 80);
            $table->text('description')->nullable();
            $table->string('province', 100)->nullable()->index();
            $table->string('department', 100)->nullable()->index();
            $table->string('locality', 120)->nullable();
            $table->string('address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('opening_hours', 180)->nullable();
            $table->boolean('is_free')->default(false);
            $table->text('confidentiality_note')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('show_on_public_map')->default(false);
            $table->boolean('show_public_detail')->default(false);
            $table->timestamps();
        });

        Schema::create('care_center_service', function (Blueprint $table) {
            $table->foreignId('care_center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('care_service_id')->constrained()->cascadeOnDelete();
            $table->primary(['care_center_id', 'care_service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_center_service');
        Schema::dropIfExists('care_centers');
        Schema::dropIfExists('care_services');
    }
};
