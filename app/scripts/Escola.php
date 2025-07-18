<?php

namespace App\Scripts;

class Escola
{
    public $file_name;

    public $worksheet;
    public $escola_id;
    public $escola_nome;
    public $escola_uf;
    public $escola_municipio;
    public $escola_localizacao;
    public $escola_dependencia;

    public $highestRow;

    public $turmas;

    public $linha_atual;

    public $dados_por_turma;

    public $processada;

    public function __construct($file_name, $worksheet)
    {
        $this->file_name = $file_name;
        $this->worksheet = $worksheet;

        $this->highestRow = $worksheet->getHighestRow();

        $this->dados_por_turma = $this->getLinha(1, "A", "Informações da Turma");

        if ($this->dados_por_turma  !== false) {
            $linha = $this->getLinha(1, "A", "Código da escola:");

            $this->escola_id = $this->getValue($linha, "B");

            if (!is_numeric($this->escola_id)) {
                $this->dados_por_turma = false;
            } else {
                $linha++;
                $this->escola_nome = $this->getValue($linha++, "B");
                $this->escola_uf = $this->getValue($linha++, "B");
                $this->escola_municipio = $this->getValue($linha++, "B");
                $this->escola_localizacao = $this->getValue($linha++, "B");
                $this->escola_dependencia = $this->getValue($linha++, "B");

                $this->linha_atual = $linha;
            }
        }
    }

    public function getLinha($linha_inicial, $coluna, $txt, $max = 100): int
    {
        $linha = $linha_inicial;
        $abort = false;
        while (($linha <= $this->highestRow) && $this->worksheet->getCell("{$coluna}{$linha}")->getValue() != $txt) {
            $linha++;
            if ($linha > ($linha_inicial + $max)) {
                $abort = true;
                break;
            }
        }
        if ($abort) {
            return 0;
        }
        return $linha;
    }

    public function getValue($linha, $coluna)
    {
        return $this->worksheet->getCell("{$coluna}{$linha}")->getValue();
    }

    public function getTurmas()
    {
        while ($this->linha_atual <= $this->highestRow) {
            $this->turmas[] = new Turma($this);
        }
    }
}