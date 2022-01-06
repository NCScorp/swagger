TRUNCATE TABLE
atendimento.categorias,
atendimento.clientesfuncoes,
atendimento.equipes,
atendimento.artigos,
atendimento.arquivos,
atendimento.flagsclientes,
servicos.atendimentosfilas,
atendimento.enderecosemails,
ns.pessoas,
crm.assuntos,
servicos.atendimentoscamposcustomizados,
servicos.atendimentos,
atendimento.equipesusuarios CASCADE;

-- Cadastro de Clientes

INSERT INTO ns.pessoas (id, pessoa, nome, nomefantasia, tenant, clienteativado, cnpj, tipocontrolepagamento, situacaopagamento, tipoclientepagamento, bloqueado) VALUES ('8247a4ae-a95f-4656-babd-7324f6896698', '1', 'Desenvolvimento', 'Desenvolvimento', 47, 1, '64.976.329/0001-75', 1, 1, 1, FALSE);

INSERT INTO ns.pessoas (id, pessoa, nome, nomefantasia, tenant, clienteativado, cnpj, tipocontrolepagamento, situacaopagamento, tipoclientepagamento, bloqueado) VALUES ('45df9468-8062-432b-a897-c180b0c0b8f5', '2', 'Suporte', 'Suporte', 47, 1, '64.976.329/0001-76', 2, 2, 2, FALSE);

INSERT INTO ns.pessoas (id, pessoa, nome, nomefantasia, tenant, clienteativado, cnpj, tipocontrolepagamento, situacaopagamento, tipoclientepagamento, bloqueado) VALUES ('79ea5bef-1142-4f24-9361-1fb80cc0d4a3', '3', 'Infraestrutura', 'Infraestrutura', 47, 1, '64.976.329/0001-77', 2, 2, 2, TRUE);

-- Cadastro de arquivos

INSERT INTO atendimento.arquivos(titulo, nomearquivo, caminho, tamanho, situacao, todosclientes, tenant, created_by, created_at, descricao, ordem)
VALUES ('Arquivo 1', 'Arquivo ', 'https://www.nasajon.com.br', 32, 1, 1, 47, '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 'Arquivo 1', 0);

INSERT INTO atendimento.arquivos(titulo, nomearquivo, caminho, tamanho, situacao, todosclientes, tenant, created_by, created_at, descricao, ordem)
VALUES ('Arquivo 2', 'Arquivo ', 'https://www.nasajon.com.br', 64, 1, 1, 47, '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 'Arquivo 2', 1);

INSERT INTO atendimento.arquivos(titulo, nomearquivo, caminho, tamanho, situacao, todosclientes, tenant, created_by, created_at, descricao, ordem)
VALUES ('Arquivo 3', 'Arquivo ', 'https://www.nasajon.com.br', 128, 1, 1, 47, '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 'Arquivo 3', 2);

INSERT INTO atendimento.arquivos(titulo, nomearquivo, caminho, tamanho, situacao, todosclientes, regravisualizacao, tenant, created_by, created_at, descricao, ordem)
VALUES ('Arquivo 4', 'Arquivo ', 'https://www.nasajon.com.br', 256, 1, 0, '(:status_suporte = ''Ativo'')', 47, '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 'Arquivo 4', 3);

INSERT INTO atendimento.arquivos(titulo, nomearquivo, caminho, tamanho, situacao, todosclientes, regravisualizacao, tenant, created_by, created_at, descricao, ordem)
VALUES ('Arquivo 5', 'Arquivo ', 'https://www.nasajon.com.br', 512, 1, 0, '(:status_suporte = ''Ativo'')', 47, '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 'Arquivo 5', 4);

-- Cadastro de categorias

INSERT INTO atendimento.categorias(categoria, titulo, tenant, created_at, lastupdate, created_by, ordem)
VALUES ('06851d3a-c43e-4df5-a289-877bbe1ee080', 'Atendimento Web', 47, now(), now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 0);

INSERT INTO atendimento.categorias(categoria, titulo, tenant, created_at, lastupdate, created_by, ordem)
VALUES ('0fb8d990-662d-4ad1-aaaa-0ea120f3122d', 'Ponto Web', 47, now(), now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 1);

INSERT INTO atendimento.categorias(categoria, titulo, tenant, created_at, lastupdate, created_by, ordem)
VALUES ('7e40f721-5e34-433f-8311-b467c775179a', 'Condomínio Web', 47, now(), now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 2);

INSERT INTO atendimento.categorias(categoria, titulo, tenant, created_at, lastupdate, created_by, ordem)
VALUES ('b1104f94-d122-4b6e-875b-3e34db8cfc49', 'Avaliação e Desempenho', 47, now(), now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 3);

INSERT INTO atendimento.categorias(categoria, titulo, tenant, created_at, lastupdate, created_by, ordem)
VALUES ('cf3f7eb8-9454-4cf2-b711-3c5b01c6d566', 'Portal do Funcionário', 47, now(), now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 4);

INSERT INTO atendimento.categorias(categoria, titulo, tenant, created_at, lastupdate, created_by, ordem)
VALUES ('d12f98b2-3bef-4874-8247-8197a76b7dd9', 'Multinotas', 47, now(), now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 5);

INSERT INTO atendimento.categorias (categoria, titulo, tenant, created_at, updated_at, lastupdate, created_by, updated_by, ordem, tipo, categoriapai, descricao, status, tipoordenacao)
VALUES('17fc0802-b1be-45f0-a39f-f8c3a6a328ba', 'Atendimento Sub 1', 47, '2019-09-19 10:34:37.000', NULL, '2019-09-19 13:34:37.045', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', NULL, 0, 2, '06851d3a-c43e-4df5-a289-877bbe1ee080', 'Subcategoria de Atendimento', 1, NULL);

INSERT INTO atendimento.categorias (categoria, titulo, tenant, created_at, updated_at, lastupdate, created_by, updated_by, ordem, tipo, categoriapai, descricao, status, tipoordenacao)
VALUES('50b6e5f8-218e-4b78-8275-4330121a2c5a', 'Atendimento Sec 1', 47, '2019-09-19 10:36:40.000', NULL, '2019-09-19 13:36:39.856', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', NULL, 0, 3, '17fc0802-b1be-45f0-a39f-f8c3a6a328ba', 'Seção da Subcategoria Atendimento Sec 1', 1, 2);

-- Cadastro de assuntos

INSERT INTO crm.assuntos (codigo, descricao, tenant) VALUES (1, 'Reunião', 47);

INSERT INTO crm.assuntos (codigo, descricao, tenant) VALUES (2, 'Teleconferência', 47);

INSERT INTO crm.assuntos (codigo, descricao, tenant) VALUES (3, 'Cobrança', 47);

-- Cadastro de equipes

INSERT INTO atendimento.equipes(nome, todosclientes, created_at, created_by, tenant)
VALUES ('Desenvolvimento', 1, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 47);

INSERT INTO atendimento.equipes(nome, todosclientes, created_at, created_by, tenant)
VALUES ('Suporte', 1, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 47);

INSERT INTO atendimento.equipes(nome, todosclientes, created_at, created_by, tenant)
VALUES ('Infraestrutura', 1, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 47);

-- Cadastro Endereços de e-mail

INSERT INTO atendimento.enderecosemails(email, ativo, created_at, created_by, tenant)
VALUES ('localhost@nasajon.com.br', true, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, 47);

-- Cadastro de Filas de Chamados

INSERT INTO servicos.atendimentosfilas(tenant, nome, created_at, created_by, lastupdate, ordem)
VALUES (47, 'Atendimento Web', now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 0);

INSERT INTO servicos.atendimentosfilas(tenant, nome, created_at, created_by, lastupdate, ordem)
VALUES (47, 'Condomínio Web', now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 1);

INSERT INTO servicos.atendimentosfilas(tenant, nome, created_at, created_by, lastupdate, ordem)
VALUES (47, 'Ponto Web', now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 2);

INSERT INTO servicos.atendimentosfilas(tenant, nome, created_at, created_by, lastupdate, ordem)
VALUES (47, 'Avaliação e Desempenho', now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 3);

INSERT INTO servicos.atendimentosfilas(tenant, nome, created_at, created_by, lastupdate, ordem)
VALUES (47, 'Portal do funcionário', now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 4);

INSERT INTO servicos.atendimentosfilas(tenant, nome, created_at, created_by, lastupdate, ordem)
VALUES (47, 'Multinotas', now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, now(), 5);

-- Cadastro de Campo Customizado

INSERT INTO servicos.atendimentoscamposcustomizados(tenant, label, descricao, tipo, exibidoformcontato, obrigatorio, ordem, created_at, nome, opcoes, created_by, habilitar_busca)
VALUES (47, 'Sistema', 'Sistemas da Nasajon', 'CB', TRUE, TRUE, 0, now(), 'sistema', '["Atendimento Web","Condom\u00ednio Web","Ponto Web","Multinotas","Portal do funcion\u00e1rio","Avalia\u00e7\u00e3o e Desempenho"]', '{"nome":"Lucas Vasconcelos","email":"lucasvasconcelos@nasajon.com.br"}'::json, FALSE);

-- Cadastro de flags cliente

INSERT INTO atendimento.flagsclientes(titulo, descricao, cor, corfundo, icone, created_by, created_at, lastupdate, tenant)
VALUES ('Bloqueado', 'Clientes bloqueados', 'FFFFFF', 'bf0c0c', 'ban-circle', '{"nome":"Lucas Vasconcelos","email":"lucasvasconcelos@nasajon.com.br"}'::json, now(), now(), 47);

-- Cadastrar usuários em clientes

INSERT INTO atendimento.clientesfuncoes(cliente, conta, funcao, tenant, created_at, created_by, notificar, pendente)
VALUES ('8247a4ae-a95f-4656-babd-7324f6896698', 'lucasvasconcelos@nasajon.com.br', 'A', 47, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, FALSE, FALSE);

INSERT INTO atendimento.clientesfuncoes(cliente, conta, funcao, tenant, created_at, created_by, notificar, pendente)
VALUES ('8247a4ae-a95f-4656-babd-7324f6896698', 'wilsonsantos@nasajon.com.br', 'A', 47, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, FALSE, FALSE);

INSERT INTO atendimento.clientesfuncoes(cliente, conta, funcao, tenant, created_at, created_by, notificar, pendente)
VALUES ('8247a4ae-a95f-4656-babd-7324f6896698', 'leonardoguimaraes@nasajon.com.br', 'U', 47, now(), '{"nome": "Lucas Vasconcelos", "email": "lucasvasconcelos@nasajon.com.br"}'::json, FALSE, FALSE);

UPDATE ns.tenants SET codigo = 'gednasajon' WHERE tenant = 47; 

INSERT INTO atendimento.equipes (equipe, nome, todosclientes, created_at, updated_at, created_by, updated_by, tenant)
VALUES('0d18b4f7-b1ac-410e-90fc-1db5bf94a990', 'Equipe 1', 1, '2019-09-18 17:55:06.000', '2019-09-18 17:55:42.000', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', 47);

INSERT INTO atendimento.equipesusuarios (equipeusuario, equipe, usuario, usuariotipo, created_at, updated_at, created_by, updated_by, tenant)
VALUES('3a3bf902-cdb3-4bb2-9358-e5bc0d483629', '0d18b4f7-b1ac-410e-90fc-1db5bf94a990', 'wilsonsantos@nasajon.com.br', 'A', '2019-09-18 17:55:42.000', '2019-09-18 17:55:42.000', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', 47);

INSERT INTO atendimento.equipesusuarios (equipeusuario, equipe, usuario, usuariotipo, created_at, updated_at, created_by, updated_by, tenant)
VALUES('97d5fa85-0ef5-4fba-8e32-57ff133ea065', '0d18b4f7-b1ac-410e-90fc-1db5bf94a990', 'ryansalles@nasajon.com.br', 'U', '2019-09-18 17:55:42.000', '2019-09-18 17:55:42.000', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', 47);

Delete from web.configuracoes where sistema = 'ATENDIMENTO' and tenant = 47;

INSERT INTO web.configuracoes (configuracao,chave,valor,tenant,sistema,lastupdate) VALUES
('00bc7740-aa58-4b54-bb89-8b0f69b65241','TITULOS_EXIBIR_PARA_CLIENTE','0',47,'ATENDIMENTO','2019-12-18 08:28:09.277')
,('268cf470-dcde-47e4-b758-bfda735eeac5','PORTAL_TITULO','Titulinho',47,'ATENDIMENTO','2020-01-21 14:26:32.972')
,('4e9d21df-bde6-4d03-9138-86c222fd8840','PORTAL_DESCRICAO','<p>Fala, mano!</p>',47,'ATENDIMENTO','2020-01-21 14:26:32.978')
,('a1d547c9-7069-4043-989a-e064d590994c','ARTIGO_TEXTO','Base de Conhecimento',47,'ATENDIMENTO','2019-12-18 08:28:09.277')
,('a6d10d07-513c-4df0-b6b2-f656bfa8ed44','USUARIO_SEM_CLIENTE_CRIAR_CHAMADO','1',47,'ATENDIMENTO','2020-01-28 19:06:05.406')
,('77f21c56-8fcb-40f4-b74d-d441eba6ee4d','TITULOS_EXIBIR_SEGUINTES_SITUACOES','[0,1,2,3,4,5,6,7]',47,'ATENDIMENTO','2020-01-23 15:30:04.875')
,('a73c14d3-8d16-4fe2-b872-3ca6bea9bed3','USUARIOSDISP_HABILITADO','0',47,'ATENDIMENTO','2019-12-18 08:28:09.277')
,('893bd77b-690f-400a-99c2-07b753beb58a','TITULO_PERIODO_DE_EXIBICAO_PASSADO','12',47,'ATENDIMENTO','2019-12-18 08:28:09.277')
,('58dcf29e-855f-44e5-8ac5-9424062fe8a0','CHAMADOS_DESCRICAO','',47,'ATENDIMENTO','2019-12-18 08:28:09.277')
,('71180987-b6ae-4207-987d-5429e46832f9','DOWNLOADS_DESCRICAO','',47,'ATENDIMENTO','2019-12-18 08:28:09.277')
;
INSERT INTO web.configuracoes (configuracao,chave,valor,tenant,sistema,lastupdate) VALUES
('9ba4d037-3e84-40a0-9d9b-35157f733925','TITULOS_EXIBIR_PARA_CLIENTE_BOLETO','1',47,'ATENDIMENTO','2020-01-23 15:30:04.289')
,('3111edd0-1cb3-49c9-9a1b-67ad5342cb81','ARTIGO_DESCRICAO','<p>Artigos da base</p>',47,'ATENDIMENTO','2020-01-28 19:07:08.774')
,('3fc20de0-c0d0-4f6a-b1d9-557c88b2a0e6','CHAMADOS_VISIVELCLIENTE_PADRAO','1',47,'ATENDIMENTO','2020-01-28 19:06:05.411')
,('9b5f1d2e-eeb0-4c66-8350-d83203ef7932','USUARIO_SEM_EQUIPE_PODE_ACESSAR_CHAMADOS_CLIENTES','0',47,'ATENDIMENTO','2020-01-28 19:06:05.417')
,('4fb28fcb-3119-4c88-8abe-d2de4d1067e8','URL_AVALIACAO_RESPOSTA_CHAMADO',NULL,47,'ATENDIMENTO','2020-01-28 18:21:01.773')
,('f649910c-e920-426b-a2a3-ac7c92f8c66d','HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS','2',47,'ATENDIMENTO','2020-01-28 19:06:05.419')
,('3346d2f3-d7e0-4722-bd0f-d2a75e9b3ba4','DISPONIBILIZAR_ARQUIVOS','1',47,'ATENDIMENTO','2020-01-28 19:06:05.420')
,('33afb637-15a5-478d-824e-836d1cf33687','USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS','0',47,'ATENDIMENTO','2020-01-28 19:17:08.189')
,('3b08cc81-5f8f-43cf-acf9-1ebc4e85b15f','USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO','0',47,'ATENDIMENTO','2020-01-28 19:17:08.192')
,('b94306cd-a770-4061-9e34-c1f8a9a1a8c3','TITULOS_DESCRICAO','<p>teste Títulos teste teste</p>',47,'ATENDIMENTO','2020-01-28 19:09:00.332')
;
INSERT INTO web.configuracoes (configuracao,chave,valor,tenant,sistema,lastupdate) VALUES
('447e0f2f-8b50-4dd1-a930-f52286ef26e4','USUARIOSDISP_QUEM_DETERMINA','1',47,'ATENDIMENTO','2020-01-28 19:17:08.186')
,('8e8ee060-59e9-49d4-ad01-7a61b66bef18','ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO','1',47,'ATENDIMENTO','2020-01-28 19:06:05.422')
,('caa663c1-64c8-4686-8535-5b4a94065d4a','USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS','0',47,'ATENDIMENTO','2020-01-28 19:17:08.194')
,('c325a4a1-f700-40a7-b497-5305be040402','USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA','0',47,'ATENDIMENTO','2020-01-28 19:17:08.195')
,('1dce078c-7771-4488-88aa-63f533405758','TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE','Buscar',47,'ATENDIMENTO','2020-01-22 18:24:34.536')
,('ffea5828-c6ed-400b-bd01-08b525bf8a20','ENVIA_EMAIL_CRIACAO_CHAMADO_ADMIN','0',47,'ATENDIMENTO','2020-01-28 19:06:05.430')
,('9609192d-3caa-432d-85e7-e59fa6c95ede','EXIBE_CLIENTE','1',47,'ATENDIMENTO','2020-01-28 19:06:05.431')
,('7793116b-2bd7-4411-8d19-29000249c281','CHAMADO_SINTOMA_LABEL','Sintoma descrição',47,'ATENDIMENTO','2020-01-28 18:57:06.182')
,('a4df3c6e-04c9-4595-8c42-1eff742d32d3','TERMO_HABILITADO','1',47,'ATENDIMENTO','2020-01-24 12:53:47.025')
,('42a1df60-4e5c-4c2b-ba7f-6dbdcebf4ad0','TERMO_TEXTO','<p>teste 333333</p>',47,'ATENDIMENTO','2020-01-24 12:53:47.027')
;
INSERT INTO web.configuracoes (configuracao,chave,valor,tenant,sistema,lastupdate) VALUES
('7f731f98-3992-46c2-9b00-eaf22d959cca','CRIAR_CHAMADO_EMAIL_HTML','0',47,'ATENDIMENTO','2020-01-17 15:19:14.764')
,('090e8dc2-c4a2-4115-b713-571ff74e67da','CLIENTE_SITUACAO_SEM_RESTRICAO_LABEL','Liberação!',47,'ATENDIMENTO','2020-01-28 18:54:59.094')
,('c3f9d65a-dda2-411c-a40f-fc2b97bd7761','CADASTRAR_CONTATO_SEM_CLIENTE','1',47,'ATENDIMENTO','2020-01-28 19:06:05.423')
,('7d8df8d0-0ba2-4214-9a7d-03e4aa69025e','ARTIGO_TAG_OBRIGATORIO','0',47,'ATENDIMENTO','2020-01-28 19:06:05.424')
,('98afb3e0-dc4b-46a2-b0aa-8e61285c3f95','PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE','Fala',47,'ATENDIMENTO','2020-01-21 14:26:56.752')
,('bbce612c-6063-41da-b591-683af523d5f6','GRUPOS_EMPRESARIAIS_ATIVOS','',47,'ATENDIMENTO','2020-01-17 15:19:14.764')
,('9b172d8b-c8a0-4bd2-a4a2-79248a292404','CHAMADO_POR_EMAIL_HTML','1',47,'ATENDIMENTO','2020-01-28 19:06:05.428')
,('a851444e-20ca-4f1b-b922-4bdce57bf64d','CHAMADO_SINTOMA_OBSERVACAO','(Favor nos enviar além da descrição, as imagens.  Você pode copiar e colar na descrição ou anexar. Desta forma teremos um melhor entendimento da situação e a resposta será mais assertiva)',47,'ATENDIMENTO','2020-01-28 18:57:43.451')
,('cc7343a9-e032-4caa-b7dc-24f852401b43','PLACEHOLDER_ASSUNTO','Digite o nome, cara! Por favor!',47,'ATENDIMENTO','2020-02-10 18:07:29.132')
,('d8753315-a10f-4443-97ec-7308b1b0da1a','ATENDENTE_CRIA_CHAMADO_SEM_CLIENTE','1',47,'ATENDIMENTO','2020-01-28 19:06:05.429')
;
INSERT INTO web.configuracoes (configuracao,chave,valor,tenant,sistema,lastupdate) VALUES
('1deddcaf-ba18-4193-964f-6bcda321d353','TIMEZONE','America/Sao_Paulo',47,'ATENDIMENTO','2020-01-28 19:23:38.327')
,('075f5d32-30c2-46b2-a2ce-d0c5805b0851','TITULO_ASSUNTO','Assunto do chamado.',47,'ATENDIMENTO','2020-01-28 18:47:29.300')
,('3072f74b-c76b-45d5-a4ef-3bbe732f1edb','TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL','1',47,'ATENDIMENTO','2020-01-23 15:30:04.564')
;

UPDATE web.configuracoes 
SET valor = 1
WHERE chave = 'USUARIO_SEM_CLIENTE_CRIAR_CHAMADO'
AND tenant = 47;

INSERT INTO servicos.atendimentos (datacriacao,dataconclusao,situacao,tempototal,versao,participante,tipofollowup,criador,responsavel,atendimento,prioridadeatendimento,lastupdate,referenciaexterna,contato,dddtelefone1,telefone1,dddtelefone2,telefone2,email,endereco,instancia,servicotecnico_id,tecnico_id,tipoordemservico_id,parado,sintoma,observacao,tipomanutencao,datavisita,hora_inicio,hora_fim,estabelecimento_id,qtd_os_gerada,operacaoordemservico,tenant,atendimentofila,camposcustomizados,resumo,responsavel_web,ativo,ultima_resposta_admin,data_ultima_resposta_admin,data_ultima_resposta,updated_at,ultima_resposta_resumo,responsavel_web_tipo,created_by,updated_by,canal,canal_email,visivelparacliente,resumo_admin,qtd_respostas,qtd_respostas_outros,busca,data_ultima_resposta_cliente,data_adiamento,adiado,data_abertura,sla,proximaviolacaosla,ultimaviolacaosla,mesclado_a,mesclado_por,mesclado_em) VALUES
(now(),NULL,0,NULL,1,null,NULL,NULL,NULL,'aaaaaaaa-6270-4ec8-a722-8136b6784a9f'::uuid,NULL,now(),NULL,NULL,NULL,NULL,NULL,NULL,'jeffersonsilva@nasajon.com.br',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,47,NULL,null,NULL,'jeffersonsilva@nasajon.com.br',NULL,true,now(),now(),now(),'Sobre o assunto muito sério',1,'{"nome":"Jefferson Silva","email":"jeffersonsilva@nasajon.com.br"}','{"nome":"Jefferson Silva","email":"jeffersonsilva@nasajon.com.br"}','manual',NULL,true,'Sobre o assunto muito sério',1,0,null,NULL,NULL,false,now(),NULL,NULL,NULL,NULL,NULL,NULL);