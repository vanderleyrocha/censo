<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos_iftp', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id();
            $table->string('municipio', 150)->nullable();
            $table->string('nome_escola_origem', 150)->nullable();
            $table->string('nome_aluno', 150)->nullable();
            $table->string('parceiro', 30)->nullable();
            $table->string('nome_escola_parceira', 150)->nullable();
            $table->string('curso', 50)->nullable();
            $table->string('serie', 20)->nullable();
            $table->string('cpf', 15)->nullable();
            $table->bigInteger('codigo_escola_estadual')->nullable();
            $table->string('nome_escola_estadual', 150)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->bigInteger('id_inep')->nullable();
            $table->string('obs', 200)->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índice
            $table->index('nome_aluno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos_iftp');
    }
};
