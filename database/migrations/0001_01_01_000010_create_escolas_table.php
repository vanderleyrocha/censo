<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escolas', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id();
            $table->string('nome', 120);
            $table->string('endereco', 120)->nullable();
            $table->string('bairro', 120)->nullable();
            $table->string('zona', 120)->nullable();
            $table->foreignId('cidade_id');
            $table->string('dependencia', 120)->nullable();
            $table->string('situacao', 120)->nullable();
            $table->string('regulamentacao', 120)->nullable();
            $table->string('tipo_localizacao', 120)->nullable();
            $table->string('modalidade', 100)->nullable();
            $table->string('portaria', 100)->nullable();
            $table->integer('ano_adesao')->nullable();
            $table->string('email', 250)->nullable();
            $table->boolean('atualizado')->default(false);
            $table->integer('responsavel_censo')->nullable();
            $table->integer('t_aluno_2024')->nullable();
            $table->integer('alunos_censo_2024')->nullable();
            $table->integer('alunos_simaed')->nullable();
            $table->integer('total_registros_importados_2025')->nullable();
            $table->enum('nova', ['Sim', 'Não'])->default('Não');
            $table->boolean('encontrada')->nullable();
            $table->timestamps();

            // Chave estrangeira (agora compatível)
            $table->foreign('cidade_id')->references('id')->on('cidades');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escolas');
    }
};
