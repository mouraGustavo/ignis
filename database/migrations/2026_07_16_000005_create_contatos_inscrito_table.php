<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contatos_inscrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscrito_id')
                ->unique()
                ->constrained('inscritos')
                ->cascadeOnDelete();
            $table->string('email', 255)->nullable();
            $table->string('telefone', 20)->nullable();

            // Campos novos: contato de emergencia (emergency_contact_name/phone no front)
            $table->string('nome_emergencia', 255)->nullable();
            $table->string('telefone_emergencia', 20)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Indice unico parcial (somente quando email preenchido), igual ao dump original.
        DB::statement('CREATE UNIQUE INDEX idx_email_contato ON contatos_inscrito (email) WHERE (email IS NOT NULL)');
    }

    public function down(): void
    {
        Schema::dropIfExists('contatos_inscrito');
    }
};
