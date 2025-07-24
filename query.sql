SELECT c.nome `Município`, e.id INEP, e.nome Escola, e.dependencia `Dependência`, e.zona Zona, (SELECT nome from servidores s WHERE s.id = e.responsavel_censo) AS `Responsável`
FROM escolas e JOIN cidades c ON c.id = e.cidade_id
WHERE e.situacao = 'Em funcionamento'
LIMIT 2000

(1,Administrador do sistema,admin,2025-07-24 09:07:03,2025-07-24 09:07:03),
(2,Administrador Estadual,state,2025-07-24 09:07:03,2025-07-24 09:07:03),
(3,Administrador Regional,regional,2025-07-24 09:07:03,2025-07-24 09:07:03),
(4,Técnico Censo Escolar,school,2025-07-24 09:07:03,2025-07-24 09:07:03)
