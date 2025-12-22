SELECT cod_inep_aluno, cpf, nome, dt_nascimento, cod_inep_escola, municipio, escola, modalidade, etapa, nome_turma, tipo_mediacao, tipo_atendimento
FROM alunos 
WHERE registro_unico = 1
ORDER BY nome

SELECT cod_inep_aluno, cpf, nome, cod_inep_escola,  modalidade, etapa, nome_turma, tipo_mediacao, tipo_atendimento, count(*) AS total
FROM alunos 
WHERE 1
GROUP BY cod_inep_aluno, cpf, nome, cod_inep_escola, modalidade, etapa, nome_turma, tipo_mediacao, tipo_atendimento
having total > 1

SELECT id, cod_inep_aluno, cpf, nome, dt_nascimento, cod_inep_escola, municipio, escola, etapa, nome_turma, tipo_mediacao, tipo_atendimento
FROM alunos 
WHERE registro_unico = 1
ORDER BY nome

SELECT cod_inep_escola, municipio, escola, localizacao, dependencia, count(*)
FROM alunos 
WHERE registro_unico = 1
GROUP BY cod_inep_escola, municipio, escola, localizacao, dependencia
ORDER BY municipio, escola


SELECT c.nome Município, e.id INEP, e.nome Escola, e.dependencia Dependência, e.zona Zona, 
    (SELECT count(*) FROM alunos a WHERE a.cod_inep_escola = e.id AND a.registro_unico = 1) AS Alunos,
    (SELECT nome from servidores s WHERE s.id = e.responsavel_censo) AS Responsável
FROM escolas e JOIN cidades c ON c.id = e.cidade_id
WHERE e.situacao = 'Em funcionamento'
LIMIT 2000

SELECT c.nome Município, e.id INEP, e.nome Escola, e.dependencia Dependência, e.zona Zona, 
    (SELECT nome from servidores s WHERE s.id = e.responsavel_censo) AS Responsável
FROM escolas e JOIN cidades c ON c.id = e.cidade_id
WHERE e.situacao = 'Em funcionamento'
LIMIT 2000

SELECT u.name, u.email, s.matricula FROM users u JOIN servidores s ON s.id = u.servidor_id WHERE 1

SELECT * FROM cidades WHERE estado_id = 1 ORDER BY regional_id, nome;

SELECT id, matricula, nome, funcao, usuario, email
FROM servidores
WHERE 1

SELECT id, name, email, password, servidor_id FROM users WHERE 1

SELECT cod_inep_aluno, registro_unico, nome, cod_inep_escola, municipio, escola, modalidade, etapa, nome_turma, estrutura_curricular, tipo_mediacao, tipo_atendimento 
FROM alunos 
WHERE cod_inep_aluno = 123178683088

SELECT ESTUDANTE_NOME, status, situacao_escola, situacao_mec
FROM frequencia_pendente_mec
WHERE CO_ENTIDADE = 12007617 AND MES_REFERENCIA = 3 AND NOT turma is null AND situacao_escola = 'Ativa'
ORDER by situacao_escola, ESTUDANTE_NOME 

SELECT c.nome municipio, e.id cd_inep_escola, e.nome, e.zona, e.dependencia, e.situacao, e.total_registros_importados_2025, e.nova, e.encontrada
FROM escolas e JOIN cidades c ON c.id = e.cidade_id
WHERE e.encontrada = 1 OR e.situacao = 'Em Funcionando em 2025' OR e.total_registros_importados_2025 > 0
ORDER BY municipio, e.dependencia, e.nome


SELECT DISTINCT cod_inep_escola, nome_escola, nome, data_nascimento, cpf, cor_raca, nome_turma, poder_publico_responsavel, tipo_veiculo_transporte_escolar
FROM alunos_censo_2025
WHERE municipio = 'Marechal Thaumaturgo' AND dependencia_administrativa  = 'Municipal' AND usa_transporte_escolar = 'sim'
ORDER BY nome_escola, nome

alunos_censo_2025.tipo_turma
1. Curricular (etapa de ensino)
2. Atendimento educacional especializado (AEE)
3. Atividade complementar
4. Curricular (etapa de ensino) com Atividade Complementar


-- AEE município
SELECT id, municipio, dependencia_administrativa, localizacao_escola, cod_inep_escola, nome_escola, codigo_turma, nome_turma, etapa_ensino,
	local_funcionamento_diferenciado_da_turma, dias_semana_horario, tipo_aee, classe_especial, etapa_agregada, organizacao_curricular_turma, cod_inep_aluno, nome, cpf
FROM alunos_censo_2025
WHERE municipio = 'Rio Branco' AND dependencia_administrativa = 'Municipal' AND tipo_aee is NOT null
ORDER BY localizacao_escola, nome_escola

UPDATE alunos_censo_2025
SET tipo_aee = NULL
WHERE tipo_aee = '--'

UPDATE alunos_censo_2025
SET prioridade = 1
WHERE tipo_turma = 'Curricular (etapa de ensino)';

UPDATE alunos_censo_2025
SET prioridade = 2
WHERE tipo_turma = 'Curricular (etapa de ensino) com Atividade Complementar';

UPDATE alunos_censo_2025
SET prioridade = 4
WHERE tipo_turma = 'Atividade complementar';

UPDATE alunos_censo_2025
SET prioridade = 3
WHERE tipo_turma = 'Atendimento educacional especializado (AEE)';

ALTER TABLE alunos_censo_2025 ADD prioridade TINYINT(1) NULL AFTER atividade_complementar;
ALTER TABLE alunos_censo_2025 ADD registro_unico TINYINT(1) NULL AFTER prioridade;
ALTER TABLE alunos_censo_2025 ADD cpf_valido TINYINT(1) NULL AFTER registro_unico;

            'cod_inep_escola',
            'nome_escola',
            'municipio',
            'dependencia_administrativa',
            'prioridade',
            'registro_unico',
            'cpf_valido',

SELECT nome_escola, etapa_ensino, count(*)
FROM alunos_censo_2025
WHERE cod_inep_escola in (12006440, 12001791) AND tipo_turma = 'Curricular (etapa de ensino)'
GROUP BY nome_escola, etapa_ensino

SELECT id, municipio, dependencia_administrativa, localizacao_escola, cod_inep_escola, nome_escola, codigo_turma, nome_turma, etapa_ensino,
	local_funcionamento_diferenciado_da_turma, dias_semana_horario, tipo_aee, classe_especial, etapa_agregada, organizacao_curricular_turma, cod_inep_aluno, nome, cpf
FROM alunos_censo_2025
WHERE cod_inep_aluno = 110000679479
ORDER BY localizacao_escola, nome_escola

SELECT ano, rede, local, sum(infantil), sum(creche), sum(pre), sum(ef1), sum(ef2), sum(ef_multi), sum(medio), sum(profissional), sum(eja)
FROM `escolas_rurais` 
WHERE 1
GROUP BY ano, rede, local
ORDER BY ano, rede, local

SELECT ano, rede, local, sum(infantil), sum(creche), sum(pre), sum(ef1), sum(ef2), sum(ef_multi), sum(medio), sum(profissional), sum(eja)
FROM `escolas_rurais` 
WHERE NOT (escola LIKE '%INDIGENA%' OR escola LIKE '%INDÍGENA%')
GROUP BY ano, rede, local
ORDER BY ano, rede, local

