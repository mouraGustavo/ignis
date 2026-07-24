<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_inscrito_id')->constrained('evento_inscritos');
            $table->string('gateway', 20)->default('efi');
            $table->string('transaction_id')->nullable();
            $table->string('status', 30);
            $table->unsignedInteger('amount');
            $table->unsignedSmallInteger('installments')->default(1);
            $table->string('payment_method', 20);
            $table->string('authorization_code')->nullable();
            $table->string('card_mask', 30)->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamps();

            $table->index(['evento_inscrito_id', 'status']);
            $table->index('transaction_id');
        });

        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->json('payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('token');
        });

        Schema::table('evento_inscritos', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('parcelas_pagamento')->constrained('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evento_inscritos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
        });

        Schema::dropIfExists('payment_notifications');
        Schema::dropIfExists('payments');
    }
};
