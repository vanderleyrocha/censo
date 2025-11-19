<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id('Id');
            $table->boolean('registro_unico')->nullable();
            $table->integer('prioridade')->default(10);
            $table->bigInteger('cod_inep_aluno')->nullable();
            $table->string('cpf', 11)->nullable();
            $table->string('nome', 190)->nullable();
            $table->date('dt_nascimento')->nullable();
            $table->string('cor', 100)->nullable();
            $table->string('sexo', 40)->nullable();
            $table->integer('ano_censo')->nullable();
            $table->bigInteger('cod_inep_escola')->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('escola', 100)->nullable();
            $table->string('modalidade', 100)->nullable();
            $table->string('etapa', 100)->nullable();
            $table->bigInteger('cod_turma')->nullable();
            $table->string('nome_turma', 100)->nullable();
            $table->string('estrutura_curricular', 100)->nullable();
            $table->string('tipo_mediacao', 100)->nullable();
            $table->string('tipo_atendimento', 100)->nullable();
            $table->string('localizacao', 100)->nullable();
            $table->string('dependencia', 100)->nullable();
            $table->string('local_funcionamento_turma', 100)->nullable();
            $table->string('dias_semana', 100)->nullable();
            $table->string('horario', 100)->nullable();
            $table->text('forma_organizacao')->nullable();
            $table->boolean('libras')->nullable();
            $table->string('deficiencia', 190)->nullable();
            $table->text('recursos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
