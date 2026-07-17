<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscritos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->date('data_nascimento');
            $table->text('endereco')->nullable();
            $table->string('cidade', 120)->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->foreignId('tamanho_camisa_id')
                ->nullable()
                ->constrained('tamanhos_camisa')
                ->nullOnDelete();

            // Campo novo: term_* -> checkboxes de autorizacao
            $table->jsonb('termos_aceitos')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscritos');
    }
};
