
<?php

require '../../vendor/autoload.php';

use App\Utils\Format;
use PhpOffice\PhpSpreadsheet\IOFactory;


try {

    function processFile($file_name, PDO $pdo, &$processadas_anteriormente, &$nao_processadas)
    {
        try {
            if (file_exists($file_name)) {
                $spreadsheet = IOFactory::load($file_name);
                $worksheet = $spreadsheet->getActiveSheet();

                $escola = new Escola($file_name, $worksheet);

                if ($escola->dados_por_turma == 0) {
                    $nao_processadas++;
                } else {

                    try {
                        $stmt = $pdo->prepare("SELECT * FROM escolas WHERE id = ?");
                        $stmt->execute([$escola->escola_id]);
                        $escola_record = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$escola_record) {

                            $stmt = $pdo->prepare("SELECT * FROM cidades WHERE nome = ? AND estado_id = 12");
                            $stmt->execute([$escola->escola_nome]);
                            $cidade = $stmt->fetch(PDO::FETCH_ASSOC);

                            $stmt = $pdo->prepare("INSERT INTO escolas (
                                id, nome, zona, cidade_id, dependencia, situacao, atualizado, created_at
                            ) VALUES (
                                :id, :nome, :zona, :cidade_id, :dependencia, :situacao, :atualizado, :created_at
                            )");
                            $stmt->execute([
                                $escola->escola_id ?? 0,
                                $escola->escola_nome ?? "",
                                $escola->escola_localizacao ?? "",
                                (!$cidade) ? 0 : $cidade["id"],
                                $escola->escola_dependencia ?? "",
                                "Em funcionamento",
                                1,
                                date("Y-m-d H:i:s")  // created_at
                            ]);

                            $stmt = $pdo->prepare("SELECT * FROM escolas WHERE id = ?");
                            $stmt->execute([$escola->escola_id]);
                            $escola_record = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    } catch (\Exception $e) {
                        $erro = $e->getMessage();
                        $linha = $e->getLine();
                        echo "\n\n {$file_name} \n Linha: $linha, Escola: {$escola->escola_id} - {$escola->escola_nome} \n Erro: $erro";
                        die();
                    }
                    try {

                        if (!$escola_record['atualizado']) {
                            $escola->getTurmas();
                            try {
                                $pdo->beginTransaction();
                                foreach ($escola->turmas as $turma) {
                                    foreach ($turma->alunos as $aluno) {

                                        $stmt = $pdo->prepare("SELECT 1 FROM alunos WHERE cod_inep_aluno = ? AND cod_turma = ?");
                                        $stmt->execute([$aluno['cod_inep_aluno'], $aluno['cod_turma']]);
                                        if (!$stmt->fetch()) {
                                            $stmt = $pdo->prepare("INSERT INTO alunos (
                                                ano_censo, cod_inep_escola, escola, municipio, uf, localizacao, 
                                                dependencia, cod_turma, nome_turma, tipo_mediacao, tipo_atendimento, 
                                                estrutura_curricular, local_funcionamento_turma, dias_semana, horario, 
                                                modalidade, etapa, forma_organizacao, libras, cod_inep_aluno, nome, 
                                                dt_nascimento, cor, sexo, deficiencia, recursos, cpf
                                            ) VALUES (
                                                :ano_censo, :cod_inep_escola, :escola, :municipio, :uf, :localizacao,
                                                :dependencia, :cod_turma, :nome_turma, :tipo_mediacao, :tipo_atendimento,
                                                :estrutura_curricular, :local_funcionamento_turma, :dias_semana, :horario,
                                                :modalidade, :etapa, :forma_organizacao, :libras, :cod_inep_aluno, :nome,
                                                :dt_nascimento, :cor, :sexo, :deficiencia, :recursos, :cpf
                                            )");
                                            $stmt->execute($aluno);
                                        }
                                    }
                                }
                                $stmt = $pdo->prepare("UPDATE escolas SET atualizado = 1 WHERE id = ?");
                                $stmt->execute([$escola->escola_id]);
                                $pdo->commit();
                            } catch (\Exception $e) {
                                echo "\nErro ao processar aluno
                                    \nNome: {$aluno['nome']}
                                    \nEscola: {$escola->escola_id} - {$escola->escola_nome}
                                    \nLinha: " . $e->getLine() .
                                    "\nMessage: " . $e->getMessage();
                                foreach ($aluno as $key => $aluno) {
                                    "\nKey {$key} - Nome: {$aluno['nome']}";
                                }
                                $pdo->rollBack();
                            }
                        } else {
                            $processadas_anteriormente++;
                        }
                    } catch (\Exception $e) {
                        $erro = $e->getMessage();
                        $linha = $e->getLine();
                        echo "\n\n {$file_name} \n Linha: $linha, Escola: {$escola->escola_id} - {$escola->escola_nome} \n Erro: $erro";
                        die();
                    }
                    flush();
                    
                }

                unset($escola);
                $worksheet->disconnectCells();
                $spreadsheet->disconnectWorksheets();
                unset($worksheet);
                unset($spreadsheet);
                gc_collect_cycles();
            }
        } catch (\Exception $e) {
            $erro = $e->getMessage();
            $linha = $e->getLine();
            echo "\n\nErro ao tentar ler o arquivo {$file_name} \n Linha: $linha \n Erro: $erro";
            // die();
        }
    }

    function processExcelFiles($directory, PDO $pdo)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::LEAVES_ONLY);
        $count = 0;
        $processadas_anteriormente = 0;
        $nao_processadas = 0;
        foreach ($files as $file) {
            if (!$file->isDir() && in_array($file->getExtension(), ['xlsx', 'xls'])) {

                $file_name = $file->getPathname();
                processFile($file_name, $pdo, $processadas_anteriormente, $nao_processadas);
                $count++;
                echo "\rTotal de arquivos {$count} - Processados anteriormente: {$processadas_anteriormente} - Descartados: {$nao_processadas}";
                flush();
            }
        }
    }

    // Start processing files
    set_time_limit(0);
    ini_set('memory_limit', '1024M');

    // Create PDO connection
    $pdo = new PDO('mysql:host=localhost;dbname=censo', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $baseDir = '.\\outros';
    echo "\n{$baseDir}";
    processExcelFiles($baseDir, $pdo);

    echo "\n\nProcessing completed successfully!\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

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
