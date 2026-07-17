<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_inscritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos');
            $table->foreignId('inscrito_id')->constrained('inscritos');
            $table->foreignId('status_id')->constrained('status_inscricao');
            $table->string('numero_inscricao', 20)->nullable();
            $table->boolean('pagamento_confirmado')->default(false);
            $table->boolean('presente')->default(false);

            // Campos novos, referentes ao fluxo de pagamento (PIX / cartao ate 2x)
            $table->string('metodo_pagamento', 20)->nullable(); // 'pix' | 'credit_card'
            $table->string('comprovante_pagamento_url', 255)->nullable();
            $table->unsignedSmallInteger('parcelas_pagamento')->nullable();
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evento_id', 'inscrito_id'], 'unique_evento_inscrito');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_inscritos');
    }
};
