<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos_simaed', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id();
            $table->unsignedTinyInteger('alunos_encontrados')->nullable();
            $table->unsignedTinyInteger('cadastros_encontrados')->nullable();
            $table->bigInteger('ra')->nullable();
            $table->boolean('ra_diferente')->nullable();
            $table->bigInteger('cadastro_id')->nullable();
            $table->string('cd_inep', 30)->nullable();
            $table->string('cadastro_censo', 100)->nullable();
            $table->boolean('aluno_sem_cd_inep')->nullable();
            $table->boolean('cd_inep_erro')->nullable();
            $table->boolean('cd_inep_diferente_censo')->nullable();
            $table->string('cd_inep_censo', 30)->nullable();
            $table->string('nu_cpf', 15)->nullable();
            $table->boolean('cpf_igual_censo')->nullable();
            $table->boolean('matricula_duplicada')->nullable();
            $table->boolean('cpf_diferente_censo')->nullable();
            $table->string('cpf_censo', 11)->nullable();
            $table->boolean('aluno_sem_cpf')->nullable();
            $table->boolean('nu_cpf_valido')->nullable();
            $table->boolean('cpf_duplicado')->nullable();
            $table->boolean('mesmo_cpf_aluno_responsavel')->nullable();
            $table->string('nis', 15)->nullable();
            $table->boolean('nis_invalido')->nullable();
            $table->boolean('aluno_sem_nis')->nullable();
            $table->string('pis', 15)->nullable();
            $table->string('nome', 190)->nullable();
            $table->string('nascimento', 15)->nullable();
            $table->date('dt_nascimento')->nullable();
            $table->boolean('sem_nascimento_censo')->nullable();
            $table->boolean('nascimento_diferente_censo')->nullable();
            $table->boolean('sem_nascimento_simaed')->nullable();
            $table->string('nascimento_censo', 10)->nullable();
            $table->boolean('nascimento_simaed_erro')->nullable();
            $table->boolean('nascimento_censo_erro')->nullable();
            $table->string('sexo', 15)->nullable();
            $table->string('pai', 190)->nullable();
            $table->string('mae', 190)->nullable();
            $table->string('mae_censo', 200)->nullable();
            $table->boolean('aluno_nome_mae_errado')->nullable();
            $table->boolean('aluno_sem_nome_mae')->nullable();
            $table->string('municipio', 190)->nullable();
            $table->bigInteger('censo')->nullable();
            $table->string('escola', 190)->nullable();
            $table->string('periodo', 190)->nullable();
            $table->string('nivel', 190)->nullable();
            $table->string('modalidade1', 200)->nullable();
            $table->string('modalidade2', 200)->nullable();
            $table->string('etapa', 190)->nullable();
            $table->integer('etapa_sgp')->nullable();
            $table->string('turma', 190)->nullable();
            $table->string('cor', 30)->nullable();
            $table->string('endereco', 190)->nullable();
            $table->string('numero', 60)->nullable();
            $table->string('bairro', 190)->nullable();
            $table->string('municipio_endereco', 190)->nullable();
            $table->string('uf', 60)->nullable();
            $table->string('cep', 15)->nullable();
            $table->string('transporte_escolar', 60)->nullable();
            $table->string('nome_social', 190)->nullable();
            $table->string('possui_deficiencia', 60)->nullable();
            $table->string('deficiencia', 190)->nullable();
            $table->string('origem_informacao_aee', 190)->nullable();
            $table->string('telefone', 60)->nullable();
            $table->string('celular', 60)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('responsavel', 190)->nullable();
            $table->string('responsavel_tel', 60)->nullable();
            $table->string('responsavel_cpf', 60)->nullable();
            $table->bigInteger('aluno_id_responsavel_cpf')->nullable();
            $table->boolean('responsavel_cpf_invalido')->nullable();
            $table->boolean('responsavel_sem_cpf')->nullable();
            $table->string('nu_identidade', 60)->nullable();
            $table->string('orgao_expedidor', 100)->nullable();
            $table->string('uf_identidade', 190)->nullable();
            $table->string('data_expedicao', 15)->nullable();
            $table->string('bolsa_familia', 15)->nullable();
            $table->string('cartao_sus', 15)->nullable();
            $table->string('turno', 190)->nullable();
            $table->string('data_matricula', 20)->nullable();
            $table->string('situacao_matricula', 190)->nullable();
            $table->string('situacao_mec', 100)->nullable();
            $table->string('data_encerramento', 20)->nullable();
            $table->integer('ano_referencia')->nullable();
            $table->string('naturalidade', 190)->nullable();
            $table->string('uf_naturalidade', 190)->nullable();
            $table->boolean('sem_naturalidade')->nullable();
            $table->string('nacionalidade', 190)->nullable();
            $table->boolean('sem_nacionalidade')->nullable();
            $table->text('obs')->nullable();
            $table->string('tipo_matricula', 100)->nullable();
            $table->timestamps();

            // Índices
            $table->index('ra');
            $table->index('nu_cpf');
            $table->index('censo');
            $table->index(['ra', 'censo']);
            $table->index(['nome', 'situacao_matricula']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos_simaed');
    }
};
