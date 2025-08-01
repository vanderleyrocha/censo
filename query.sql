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

