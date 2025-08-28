-- Script para adicionar o artigo da Feira de Profissões no banco de dados
-- Execute este script após criar as tabelas necessárias

-- Certifique-se de que existe pelo menos um usuário administrador
-- Se não existir, primeiro crie um usuário usando o cadastro.php

-- Obtenha o ID do primeiro administrador para associar o artigo
SET @admin_id = (SELECT id FROM usuarios WHERE email = 'admin@entrelinhas.com' LIMIT 1);
IF @admin_id IS NULL THEN
    SET @admin_id = (SELECT id FROM usuarios LIMIT 1);
END IF;

-- Inserir o artigo sobre a Feira de Profissões
INSERT INTO artigos (
    titulo, 
    conteudo, 
    categoria, 
    imagem, 
    id_usuario, 
    status, 
    data_criacao, 
    data_publicacao
) VALUES (
    'SESI SALTO PROMOVE FEIRA DE PROFISSÕES 2025',
    '<p><em>Idealizada pela professora Nilceia Ragazzi, o evento promete oferecer aos educandos e educandas "um olhar para o amanhã".</em></p>
<p><strong>Por Ana Carolina Gatti, professora de Língua Portuguesa do SESI.</strong></p>
<p>Nos dias 4 e 5 de setembro, o Centro Educacional SESI da cidade de Salto realizará a 7ª Feira de Profissões da unidade. O evento, que ocorrerá das 8h às 12h, é organizado anualmente com o objetivo de apoiar os estudantes na escolha de seu futuro profissional, um momento decisivo no projeto de vida.</p>
<p>Nilceia Ragazzi, docente da área de Geografia, em colaboração com os demais professores, é responsável por esse projeto que se tornou uma tradição no calendário institucional. Aguardada pela comunidade escolar, a feira promove orientação vocacional e de carreira, além de proporcionar a interação dos estudantes com instituições de ensino da região e profissionais das áreas de interesse. Neste ano, o evento ocorrerá em dois dias e contará com a participação de faculdades públicas e privadas, que apresentarão projetos, cursos e possibilidades de carreira.</p>
<p>Além dessas iniciativas, os alunos e alunas do SESI Salto poderão participar de atividades práticas, dinâmicas interativas e palestras inspiradoras. Em 2025, a feira contará com a participação de egressas da unidade, profissionais da Faculdade SENAI e palestras de encerramento: na quinta-feira, Elaine Fidêncio (RH e Gestão de Pessoas) apresentará "Você no protagonismo: construa o seu caminho"; e, na sexta-feira, Francisco Petros discorrerá sobre "Os desafios da política brasileira frente ao mercado financeiro mundial".</p>
<p>A Feira é um momento de notável importância para os jovens, por oferecer a oportunidade de conhecerem diferentes áreas de atuação, sanar dúvidas com especialistas e fomentar a autopercepção. Visando a promoção da reflexão sobre interesses e talentos individuais, o convite está aberto aos estudantes, e a participação de todos é fundamental para tornar o evento significativo na trajetória profissional e acadêmica de cada um deles.</p>
<p>As inscrições estão abertas até o dia 29/08, no link abaixo: <a href="https://forms.gle/QabJ6Q3SFUNy5LQ4A" target="_blank">https://forms.gle/QabJ6Q3SFUNy5LQ4A</a></p>',
    'Educação',
    '../assets/images/artigos/f623caca-1049-4588-babc-48f8e30bb31f_page-0001.jpg',
    @admin_id,
    'aprovado',
    NOW(),
    NOW()
);
