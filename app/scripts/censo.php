
<?php

require '../../vendor/autoload.php';

use App\Utils\Format;
use PhpOffice\PhpSpreadsheet\IOFactory;


try {

    // Start processing files
    set_time_limit(0);
    ini_set('memory_limit', '1024M');

    // Create PDO connection
    $pdo = new PDO('mysql:host=localhost;dbname=u815349007_censo', 'u815349007_censo', '5*r09HGyZ');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $baseDir = '.\\outros';
    echo "\n{$baseDir}";
    processExcelFiles($baseDir, $pdo);

    echo "\n\nProcessing completed successfully!\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

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
