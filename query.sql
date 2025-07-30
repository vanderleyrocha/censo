SELECT c.nome Município, e.id INEP, e.nome Escola, e.dependencia Dependência, e.zona Zona, (SELECT nome from servidores s WHERE s.id = e.responsavel_censo) AS Responsável
FROM escolas e JOIN cidades c ON c.id = e.cidade_id
WHERE e.situacao = 'Em funcionamento'
LIMIT 2000

(1,Administrador do sistema,admin,2025-07-24 09:07:03,2025-07-24 09:07:03),
(2,Administrador Estadual,state,2025-07-24 09:07:03,2025-07-24 09:07:03),
(3,Administrador Regional,regional,2025-07-24 09:07:03,2025-07-24 09:07:03),
(4,Técnico Censo Escolar,school,2025-07-24 09:07:03,2025-07-24 09:07:03)

SELECT u.name, u.email, s.matricula FROM users u JOIN servidores s ON s.id = u.servidor_id WHERE 1

SELECT * FROM cidades WHERE estado_id = 1 ORDER BY regional_id, nome;

SELECT id, matricula, nome, funcao, usuario, email
FROM servidores
WHERE 1

SELECT id, name, email, password, servidor_id FROM users WHERE 1