
<?php

require '../../vendor/autoload.php';

use App\Utils\Format;
use PhpOffice\PhpSpreadsheet\IOFactory;


try {

    function processFile($file_name, PDO $pdo)
    {
        $spreadsheet = IOFactory::load($file_name);
        $worksheet = $spreadsheet->getActiveSheet();

        $escola = new Escola($file_name, $worksheet);

        // echo "\n {$escola->escola_id} - {$escola->escola_nome}";

        if ($escola->dados_por_turma) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM escolas WHERE id = ?");
                $stmt->execute([$escola->escola_id]);
                $escola_record = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$escola_record) {
                    $names = explode("\\", $file_name);
                    $municipio = $names[4];
                    $nome_arquivo = end($names);
                    echo "\n\n ---- Escola não encontrada! -----";
                    echo "\n {$nome_arquivo} - {$municipio}";
                    echo "\n";
                } else {
                    if (!$escola_record['atualizado']) {
                        $escola->getTurmas();
                        try {
                            $pdo->beginTransaction();
                            foreach ($escola->turmas as $turma) {
                                foreach ($turma->alunos as $aluno) {

                                    $stmt = $pdo->prepare("SELECT 1 FROM alunos WHERE id = ?");
                                    $stmt->execute([$aluno['id']]);
                                    if (!$stmt->fetch()) {
                                        $stmt = $pdo->prepare("INSERT INTO alunos (
                                            id, ano_censo, cod_inep_escola, escola, municipio, uf, localizacao, 
                                            dependencia, cod_turma, nome_turma, tipo_mediacao, tipo_atendimento, 
                                            estrutura_curricular, local_funcionamento_turma, dias_semana, horario, 
                                            modalidade, etapa, forma_organizacao, libras, cod_inep_aluno, nome, 
                                            dt_nascimento, cor, sexo, deficiencia, recursos, cpf
                                        ) VALUES (
                                            :id, :ano_censo, :cod_inep_escola, :escola, :municipio, :uf, :localizacao,
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
                            echo "\nErro ao processar aluno {$aluno['nome']} - {$escola->escola_id} - {$escola->escola_nome}: " . $e->getMessage();
                            $pdo->rollBack();
                        }
                    } else {
                        echo "\n{$escola->escola_id} - {$escola->escola_nome}: Escola já foi atualizada";
                    }
                }
            } catch (\Exception $e) {
                echo "\n\n Erro:" . $e->getMessage();
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

    function processExcelFiles($directory, PDO $pdo)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::LEAVES_ONLY);
        $count = 0;
        foreach ($files as $file) {
            if (!$file->isDir() && in_array($file->getExtension(), ['xlsx', 'xls'])) {

                $file_name = $file->getPathname();
                processFile($file_name, $pdo);
                $count++;
                echo "\rEscolas processadas: " . $count;
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

    $stmt = $pdo->prepare("SELECT * FROM escolas WHERE id = ?");
    $stmt->execute([12011517]);
    $escola_record = $stmt->fetch(PDO::FETCH_ASSOC);
    $baseDir = '..\\..\\public\\municipios';

    processExcelFiles($baseDir, $pdo);

    echo "Processing completed successfully!\n";
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

        $this->dados_por_turma = ($this->getLinha(1, "A", "Informações da Turma") < $this->highestRow);

        $linha = $this->getLinha(1, "A", "Código da escola:");

        $this->escola_id = $this->getValue($linha++, "B");
        $this->escola_nome = $this->getValue($linha++, "B");
        $this->escola_uf = $this->getValue($linha++, "B");
        $this->escola_municipio = $this->getValue($linha++, "B");
        $this->escola_localizacao = $this->getValue($linha++, "B");
        $this->escola_dependencia = $this->getValue($linha++, "B");

        $this->linha_atual = $linha;
    }

    public function getLinha($linha_inicial, $coluna, $txt): int
    {
        $linha = $linha_inicial;
        while (($linha <= $this->highestRow) && $this->worksheet->getCell("{$coluna}{$linha}")->getValue() != $txt) {
            $linha++;
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
                    'id' => $cod_inep_aluno,
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
