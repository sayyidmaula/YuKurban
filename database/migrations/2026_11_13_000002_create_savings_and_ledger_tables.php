<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // ekonomis | standar | premium
            $table->unsignedBigInteger('min_price');
            $table->unsignedBigInteger('max_price');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('savings_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('tier_id')->nullable()->constrained('price_tiers')->nullOnDelete();
            $table->unsignedBigInteger('target_amount');
            $table->unsignedSmallInteger('target_year');
            $table->unsignedBigInteger('monthly_amount');
            $table->string('status')->default('menabung')->index(); // menabung | dibeli | ditutup
            $table->boolean('autodebet_enabled')->default(false);
            $table->unsignedTinyInteger('autodebet_day')->nullable(); // 1 - 28
            $table->unsignedBigInteger('balance_cached')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('savings_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('va_code', 16)->unique();
            $table->string('status')->default('menunggu')->index(); // menunggu | berhasil | gagal | kedaluwarsa
            $table->dateTime('expires_at')->index();
            $table->dateTime('paid_at')->nullable();
            $table->string('source')->default('manual'); // manual | autodebet
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index(); // setoran | pembelian | pengembalian_dana | penarikan | koreksi
            $table->string('direction', 10); // masuk | keluar
            $table->unsignedBigInteger('amount'); // bigint positif
            $table->unsignedBigInteger('balance_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['savings_plan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('payment_invoices');
        Schema::dropIfExists('savings_plans');
        Schema::dropIfExists('price_tiers');
    }
};

