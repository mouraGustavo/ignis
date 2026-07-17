<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsaveis_inscrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscrito_id')
                ->unique()
                ->constrained('inscritos')
                ->cascadeOnDelete();
            $table->string('nome', 255);
            $table->string('cpf', 14)->nullable();
            $table->string('parentesco', 50)->nullable();
            $table->string('telefone', 20);
            $table->string('email', 255)->nullable();
            $table->boolean('autorizado')->default(false);

            // Campo novo: foto do documento do responsavel + assinatura (guardian_document_url)
            $table->string('documento_url', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsaveis_inscrito');
    }
};
