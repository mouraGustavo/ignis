<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informacoes_saude', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscrito_id')
                ->unique()
                ->constrained('inscritos')
                ->cascadeOnDelete();
            $table->text('restricoes_alimentares')->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos')->nullable();
            $table->text('condicao_saude')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informacoes_saude');
    }
};
