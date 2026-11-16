<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->unique()->constrained('regions')->cascadeOnDelete();
            $table->boolean('supports_sembelih_tim')->default(true);
            $table->boolean('supports_kirim_hidup')->default(true);
            $table->unsignedBigInteger('delivery_fee')->default(0); // ongkir zona
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('fee_rules', function (Blueprint $table) {
            $table->id();
            $table->string('animal_type')->unique(); // kambing | domba | sapi
            $table->unsignedBigInteger('fee_slaughter'); // ujrah sembelih
            $table->unsignedBigInteger('fee_distribution'); // ujrah distribusi
            $table->unsignedBigInteger('commission_peternak'); // komisi platform dari peternak
            $table->timestamps();
        });

        Schema::create('slaughter_quotas', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('quota');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slaughter_quotas');
        Schema::dropIfExists('fee_rules');
        Schema::dropIfExists('service_zones');
    }
};
