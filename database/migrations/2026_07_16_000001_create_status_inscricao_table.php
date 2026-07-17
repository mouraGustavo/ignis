<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_inscricao', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50)->unique();
            $table->string('descricao', 255)->nullable();
            $table->smallInteger('ordem');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_inscricao');
    }
};
