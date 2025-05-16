SELECT c.nome cidade, e.id, e.nome, e.dependencia, e.situacao
FROM escolas e INNER JOIN cidades c ON c.id = e.cidade_id
WHERE e.atualizado = 0 AND e.situacao != 'Paralisada'
ORDER BY cidade, dependencia, situacao


SELECT municipio, cod_inep_escola, escola, dependencia, localizacao, tipo_atendimento, tipo_mediacao, local_funcionamento_turma, count(*)
FROM alunos
WHERE 1
GROUP BY municipio, cod_inep_escola, escola, dependencia, localizacao, tipo_atendimento, tipo_mediacao, local_funcionamento_turma
ORDER BY municipio, cod_inep_escola, escola, dependencia, localizacao, tipo_atendimento, tipo_mediacao, local_funcionamento_turma


SELECT c.nome AS cidade, e.id AS cod_inep, e.nome as escola, e.dependencia, e.situacao, e.tipo_localizacao, a.localizacao, a.tipo_atendimento, count(*)
FROM alunos a INNER JOIN escolas e ON e.id = a.cod_inep_escola INNER JOIN cidades c on c.id = e.cidade_id
WHERE tipo_atendimento = 'Escolarização e Atividade complementar' OR tipo_atendimento = 'Escolarização'
GROUP BY c.nome, e.id, e.nome, e.dependencia, e.situacao, e.tipo_localizacao, a.localizacao, a.tipo_atendimento
ORDER BY c.nome, e.id, e.nome, e.dependencia, e.situacao, e.tipo_localizacao, a.localizacao, a.tipo_atendimento


SELECT municipio, cod_inep_escola, escola, tipo_atendimento, count(*)
FROM alunos
GROUP BY municipio, cod_inep_escola, escola, tipo_atendimento
ORDER BY municipio, cod_inep_escola, escola, tipo_atendimento

SELECT municipio, cod_inep_escola, escola, tipo_atendimento, count(*)
FROM alunos
GROUP BY municipio, cod_inep_escola, escola, tipo_atendimento
ORDER BY municipio, cod_inep_escola, escola, tipo_atendimento
LIMIT 2000