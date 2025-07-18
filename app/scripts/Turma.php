<?php

namespace App\Scripts;

class Turma
{
    public $codigo;
    public $nome;
    public $mediacao_pedagogica;
    public $atendimento;
    public $estrutura_curricular;
    public $local_funcionamento;
    public $dias_semana;
    public $horario_atendimento;
    public $modalidade;
    public $etapa;
    public $organizacao;
    public $libras;

    public $alunos;

    public function __construct(Escola $escola)
    {
        $linha = $escola->getLinha($escola->linha_atual, "A", "Código da turma:");
        $this->codigo = $escola->getValue($linha++, "B");
        $this->nome = $escola->getValue($linha++, "B");
        $this->mediacao_pedagogica = $escola->getValue($linha++, "B");
        $this->atendimento = $escola->getValue($linha++, "B");
        $this->estrutura_curricular = $escola->getValue($linha++, "B");
        $this->local_funcionamento = $escola->getValue($linha++, "B");
        $this->dias_semana = $escola->getValue($linha++, "B");
        $this->horario_atendimento = $escola->getValue($linha++, "B");
        $this->modalidade = $escola->getValue($linha++, "B");
        $this->etapa = $escola->getValue($linha++, "B");
        $this->organizacao = $escola->getValue($linha++, "B");
        $this->libras = $escola->getValue($linha++, "B");
        $this->alunos = [];
        $escola->linha_atual = $linha;

        $linha = $escola->getLinha($linha, "A", "Ordem");
        $linha++;
        while ($linha <= $escola->highestRow && is_numeric($escola->getValue($linha, "A"))) {
            $cod_inep_aluno = intval($escola->getValue($linha, "B"));
            if ($cod_inep_aluno > 0) {
                $nome_aluno = $escola->getValue($linha, "C");

                $aluno = [
                    'ano_censo' => 2024,
                    'cod_inep_escola' => $escola->escola_id,
                    'escola' => $escola->escola_nome,
                    'municipio' => $escola->escola_municipio,
                    'uf' => $escola->escola_uf,
                    'localizacao' => $escola->escola_localizacao,
                    'dependencia' => $escola->escola_dependencia,
                    'cod_turma' => $this->codigo,
                    'nome_turma' => $this->nome,
                    'tipo_mediacao' => $this->mediacao_pedagogica,
                    'tipo_atendimento' => $this->atendimento,
                    'estrutura_curricular' => $this->estrutura_curricular,
                    'local_funcionamento_turma' => $this->local_funcionamento,
                    'dias_semana' => $this->dias_semana,
                    'horario' => $this->horario_atendimento,
                    'modalidade' => $this->modalidade,
                    'etapa' => $this->etapa,
                    'forma_organizacao' => $this->organizacao,
                    'libras' => ($this->libras == "Sim" ? 1 : 0),
                    'cod_inep_aluno' => $cod_inep_aluno,
                    'nome' => $nome_aluno,
                    'dt_nascimento' => Format::dateBRtoEn($escola->getValue($linha, "D")),
                    'cor' => $escola->getValue($linha, "E"),
                    'sexo' => $escola->getValue($linha, "F"),
                    'deficiencia' => $escola->getValue($linha, "G"),
                    'recursos' => $escola->getValue($linha, "H"),
                    'cpf' => Format::digitOnly($escola->getValue($linha, "I"))
                ];

                $this->alunos[] = $aluno;
            }
            $linha++;
        }

        $escola->linha_atual = $linha;
    }
}