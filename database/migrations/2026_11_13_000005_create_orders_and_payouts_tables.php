<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('savings_plan_id')->constrained('savings_plans');
            $table->foreignId('animal_id')->constrained('animals');
            $table->foreignId('region_id')->constrained('regions'); // daerah tujuan
            $table->string('method'); // sembelih_tim | kirim_hidup
            $table->string('status')->index();
            $table->unsignedBigInteger('animal_price');
            $table->unsignedBigInteger('fee_slaughter')->default(0);
            $table->unsignedBigInteger('fee_distribution')->default(0);
            $table->unsignedBigInteger('fee_delivery')->default(0);
            $table->unsignedBigInteger('commission')->default(0);
            $table->unsignedBigInteger('total_buyer_pays');
            $table->dateTime('reserved_until')->nullable();
            $table->dateTime('wakalah_agreed_at')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_address')->nullable();
            $table->text('distribution_note')->nullable();
            $table->dateTime('peternak_ready_at')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('slaughtered_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('auto_confirm_at')->nullable();
            $table->boolean('is_disputed')->default(false);
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('peternak_profile_id')->constrained('peternak_profiles');
            $table->unsignedBigInteger('amount'); // animal_price - commission
            $table->unsignedBigInteger('commission');
            $table->string('status')->default('tertahan')->index(); // tertahan | dibayarkan | dibatalkan
            $table->dateTime('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('orders');
    }
};

