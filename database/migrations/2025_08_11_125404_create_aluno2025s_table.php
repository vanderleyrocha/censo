<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aluno2025', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cod_inep_escola');
            $table->string('nome_escola');
            $table->string('municipio');
            $table->string('localizacao_escola');
            $table->string('dependencia_administrativa');
            $table->bigInteger('cod_inep_aluno');
            $table->string('nome')->nullable();
            $table->date('nascimento')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('nacionalidade', 200)->nullable();
            $table->string('raca', 100)->nullable();
            $table->boolean('povo_indigena')->nullable();
            $table->string('sexo', 30)->nullable();
            $table->string('tipo_deficiencia', 255)->nullable();
            $table->string('transtorno_aprendizagem', 255)->nullable();
            $table->string('recursos_saeb', 255)->nullable();
            $table->string('tipo_atendimento_especializado', 255)->nullable();
            $table->string('espaco_diferente', 255)->nullable();
            $table->string('localizacao_residencia', 255)->nullable();
            $table->string('localizacao_diferente', 255)->nullable();
            $table->string('transporte_escolar', 255)->nullable();
            $table->string('transporte_publico', 255)->nullable();
            $table->string('tipo_veiculo', 255)->nullable();
            $table->string('etapa_vinculo', 255)->nullable();
            $table->string('codigo_matricula', 255)->nullable();
            $table->string('codigo_turma', 255)->nullable();
            $table->string('nome_turma', 255)->nullable();
            $table->string('tipo_mediacao', 255)->nullable();
            $table->string('etapa_agregada', 255)->nullable();
            $table->string('etapa_ensino', 255)->nullable();
            $table->string('forma_organizacao', 255)->nullable();
            $table->string('local_diferenciado', 255)->nullable();
            $table->string('horario_funcionamento', 255)->nullable();
            $table->string('carga_horaria_semanal', 255)->nullable();
            $table->string('turma_aee', 255)->nullable();
            $table->string('turma_bilingue', 255)->nullable();
            $table->string('turma_alternancia', 255)->nullable();
            $table->text('area_conhecimento')->nullable();
            $table->text('organizacao_curricular')->nullable();
            $table->string('itinerario_formativo', 255)->nullable();
            $table->string('tipo_ftp', 255)->nullable();
            $table->string('codigo_nome_ftp', 255)->nullable();
            $table->string('atividade_complementar', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno2025');
    }
};
