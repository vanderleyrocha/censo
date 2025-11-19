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
        Schema::create('aluno_censo_2025_correcao', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id();
            $table->bigInteger('cod_inep_escola')->nullable();
            $table->string('nome_escola', 255)->nullable();
            $table->string('municipio', 255)->nullable();
            $table->string('localizacao_escola', 255)->nullable();
            $table->string('dependencia_administrativa', 255)->nullable();
            $table->string('ordem')->nullable();
            $table->bigInteger('cod_inep_aluno')->nullable();
            $table->string('nome', 255)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('nacionalidade', 100)->nullable();
            $table->string('cor_raca', 50)->nullable();
            $table->string('povo_indigena', 50)->nullable();
            $table->string('sexo', 20)->nullable();
            $table->text('tipo_deficiencia')->nullable();
            $table->text('tipo_transtorno')->nullable();
            $table->text('recursos_saeb')->nullable();
            $table->text('tipo_aee')->nullable();
            $table->string('escolarizacao_outro_espaco', 100)->nullable();
            $table->string('localizacao_residencia', 50)->nullable();
            $table->string('localizacao_diferenciada_residencia', 100)->nullable();
            $table->string('usa_transporte_escolar', 10)->nullable();
            $table->string('poder_publico_responsavel', 50)->nullable();
            $table->string('tipo_veiculo_transporte_escolar', 250)->nullable();
            $table->string('etapa_aluno_turma_multi', 100)->nullable();
            $table->string('codigo_matricula', 50)->nullable();
            $table->string('codigo_turma', 50)->nullable();
            $table->string('nome_turma', 255)->nullable();
            $table->string('tipo_mediacao', 100)->nullable();
            $table->string('tipo_turma', 100)->nullable();
            $table->string('etapa_agregada', 100)->nullable();
            $table->string('etapa_ensino', 100)->nullable();
            $table->string('formas_organizacao_turma', 100)->nullable();
            $table->text('local_funcionamento_diferenciado_da_turma')->nullable();
            $table->text('dias_semana_horario')->nullable();
            $table->string('carga_horaria_semanal', 20)->nullable();
            $table->string('classe_especial', 10)->nullable();
            $table->string('classe_bilingue_surdos', 10)->nullable();
            $table->string('turma_formacao_alternancia', 10)->nullable();
            $table->text('areas_conhecimento')->nullable();
            $table->text('organizacao_curricular_turma')->nullable();
            $table->text('areas_itinerario_formativo')->nullable();
            $table->string('tipo_curso_ftp', 100)->nullable();
            $table->string('codido_nome_curso_ftp', 255)->nullable();
            $table->text('atividade_complementar')->nullable();
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index('cod_inep_aluno');
            $table->index('cpf');
            $table->index('codigo_matricula');
            $table->index('codigo_turma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno_censo_2025_correcao');
    }
};