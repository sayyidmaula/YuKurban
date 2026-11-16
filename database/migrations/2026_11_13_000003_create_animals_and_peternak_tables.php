<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('province');
            $table->string('city');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['province', 'city']);
        });

        Schema::create('peternak_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('farm_name');
            $table->text('address');
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('farm_photo_path')->nullable();
            $table->string('status')->default('menunggu')->index(); // menunggu | terverifikasi | ditolak | nonaktif
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peternak_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index(); // kambing | domba | sapi
            $table->string('title');
            $table->unsignedInteger('age_months');
            $table->decimal('weight_kg', 6, 1);
            $table->text('health_notes');
            $table->unsignedBigInteger('price');
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('status')->default('menunggu_review')->index(); // draft | menunggu_review | tayang | dipesan | terjual | ditarik
            $table->foreignId('reserved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reserved_until')->nullable()->index();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('animal_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // foto | video
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_media');
        Schema::dropIfExists('animals');
        Schema::dropIfExists('peternak_profiles');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('settings');
    }
};

