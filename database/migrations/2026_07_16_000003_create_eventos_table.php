<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('local', 255)->nullable();
            $table->decimal('valor_inscricao', 10, 2)->default(0);
            $table->integer('limite_vagas')->default(150);
            $table->integer('idade_minima')->default(14);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // CHECK constraints apenas no PostgreSQL (SQLite de testes não suporta ALTER CONSTRAINT).
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE eventos ADD CONSTRAINT chk_data_evento CHECK (data_fim >= data_inicio)');
            DB::statement('ALTER TABLE eventos ADD CONSTRAINT chk_limite_vagas CHECK (limite_vagas > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
