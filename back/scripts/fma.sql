DO $$ DECLARE _FMA integer := 569;

BEGIN
/* Preparação do tenant */
--CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE
OR REPLACE FUNCTION uuid_generate_v4() RETURNS uuid AS '$libdir/uuid-ossp',
'uuid_generate_v4' LANGUAGE c VOLATILE STRICT COST 1;

ALTER FUNCTION uuid_generate_v4() OWNER TO postgres;

/* Tenants */
INSERT INTO
  ns.tenants (codigo, tenant)
VALUES
  ('fma', _FMA);

/* Configurações */
insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'INTEGRACAO_GP',
    1,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'DIASPARAVENCIMENTO',
    30,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'GP__TEMPOADQUIRIDO',
    50000,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'GP_TIPOPROJETOPADRAO',
    '62b143c7-edf6-4923-bcfe-3d45eb33d761',
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generatev4(),
    'GP_PROJETOESCOPOEXECUCOES',
    1,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generatev4(),
    'GP_PROJETOESCOPOTIPO',
    6,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generatev4(),
    'GP_PROJETOESCOPOTEMPOADQUIRIDO',
    0,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'PRESTADORPRAZOSELECAO',
    4320,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'PRESTADORALERTA',
    60,
    _FMA,
    'CRMWEB',
    now()
  );

insert into
  web.configuracoes (
    configuracao,
    chave,
    valor,
    tenant,
    sistema,
    lastupdate
  )
values
  (
    uuid_generate_v4(),
    'PRESTADORPRAZOACIONAMENTORESPOSTA',
    1,
    _FMA,
    'CRMWEB',
    now()
  );

/* */
insert into
  ns.cnaes (cnae, descricao)
values
  ('9603303', 'Serviços de sepultamento');

insert into
  ns.cnaes (cnae, descricao)
values
  ('9603304', 'Serviços de funerárias');

insert into
  ns.cnaes (cnae, descricao)
values
  (
    '6511102',
    'Seguro de vida e não-vida: planos para auxilio funeral'
  );

insert into
  ns.paises (pais, nome)
values
  ('001', 'Brasil');

insert into
  ns.estados (uf, nome)
values
  ('RJ', 'Rio de Janeiro');

insert into
  ns.municipios (id, nome, pais, estado)
values
  (
    '0f381248-1f3c-46e9-b075-3445d7de6288',
    'Caxias',
    '001',
    'RJ'
  );

insert into
  ns.cidadesinformacoesfunerarias (cidade, tenant)
values
  ('0f381248-1f3c-46e9-b075-3445d7de6288', _FMA);

insert into
  ns.municipios (id, nome, pais, estado)
values
  (
    'adc67791-c178-47f0-81e8-522e2864c3b2',
    'Niterói',
    '001',
    'RJ'
  );

insert into
  ns.cidadesinformacoesfunerarias (cidade, tenant)
values
  ('adc67791-c178-47f0-81e8-522e2864c3b2', _FMA);

insert into
  ns.pessoas (id, pessoa, nome, nomefantasia, cnpj, tenant)
values
  (
    '460f64b5-e296-4ec6-8833-b93edd9310a7',
    '10',
    'FMA',
    'FMA',
    '33856147000137',
    _FMA
  );

insert into
  ns.gruposempresariais (grupoempresarial, codigo, descricao, tenant)
values
  (
    '3964bfdc-e09e-4386-9655-5296062e632d',
    'FMA',
    'FMA',
    _FMA
  );

insert into
  ns.empresas (
    empresa,
    codigo,
    raizcnpj,
    ordemcnpj,
    razaosocial,
    tenant,
    grupoempresarial
  )
values
  (
    'ab93da91-e98a-4e7c-acc7-d89d8303b98f',
    'FMA',
    '33856147',
    '000137',
    'FUNERARIA MARACANA',
    _FMA,
    '3964bfdc-e09e-4386-9655-5296062e632d'
  );

insert into
  ns.estabelecimentos (
    estabelecimento,
    codigo,
    raizcnpj,
    ordemcnpj,
    tenant,
    empresa,
    id_pessoa
  )
values
  (
    'b7ba5398-845d-4175-9b5b-96ddcb5fed0f',
    'FMA',
    '33856147',
    '000137',
    _FMA,
    'ab93da91-e98a-4e7c-acc7-d89d8303b98f',
    '460f64b5-e296-4ec6-8833-b93edd9310a7'
  );

insert into
  ns.conjuntos (conjunto, descricao, cadastro, codigo, tenant)
VALUES
  (
    '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
    'FMA',
    '1',
    'FMA',
    _FMA
  );

insert into
  ns.conjuntosclientes (conjuntocliente, registro, conjunto, tenant)
values
  (
    '7a9eceff-c3ee-4136-897a-1101eded393e',
    '460f64b5-e296-4ec6-8833-b93edd9310a7',
    '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
    _FMA
  );

--registro = pessoa_id
insert into
  ns.conjuntosfornecedores (conjuntofornecedor, registro, conjunto, tenant)
values
  (
    '4847d34d-3823-446e-94e3-9b7f110c0e02',
    '460f64b5-e296-4ec6-8833-b93edd9310a7',
    '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
    _FMA
  );

insert into
  ns.estabelecimentosconjuntos (
    estabelecimento,
    conjunto,
    cadastro,
    tenant,
    permissao
  )
values
  (
    'b7ba5398-845d-4175-9b5b-96ddcb5fed0f',
    '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
    '1',
    _FMA,
    true
  );

insert into
  financas.bancos (codigo, nome, numero, tenant, banco)
values
  (
    '10',
    'Banco do Brasil',
    '272',
    _FMA,
    '697704b8-a81d-4def-a840-9e40455664be'
  );

insert into
  ns.empresas (empresa, codigo, razaosocial, tenant)
values
  (
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    'F01',
    'FUN BOM JESUS',
    _FMA
  );

insert into
  ns.pessoas (
    pessoa,
    id,
    nomefantasia,
    cnpj,
    inscricaomunicipal,
    tenant
  )
values
  (
    '1',
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    'Funerária Bom Jesus',
    '83593810000126',
    '222222',
    _FMA
  );

insert into
  ns.estabelecimentos (
    nomefantasia,
    raizcnpj,
    ordemcnpj,
    inscricaomunicipal,
    cep,
    logradouro,
    numero,
    complemento,
    bairro,
    tenant,
    empresa,
    cnae,
    codigo,
    id_pessoa
  )
values
  (
    'Funerária Bom Jesus',
    '83593810',
    '000126',
    '222222',
    '11111111',
    'Avenida rio Branco',
    '100',
    'Predio 15 sala 12',
    'Centro',
    _FMA,
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    '9603304',
    'FUN001',
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638'
  );

insert into
  ns.contatos (
    id,
    nome,
    cargo,
    primeironome,
    sobrenome,
    id_pessoa,
    tenant
  )
values
  (
    '509d8af9-0f33-4030-9d3b-7538adbbc8e9',
    'Vendas',
    'Gerente',
    'Carlos',
    'Silva',
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    _FMA
  );

insert into
  ns.telefones (
    id,
    ddi,
    ddd,
    telefone,
    ramal,
    contato,
    descricao,
    tenant
  )
values
  (
    '509d8af9-0f33-4030-9d3b-7538adbbc8e9',
    '55',
    '21',
    '11111111',
    '10',
    '509d8af9-0f33-4030-9d3b-7538adbbc8e9',
    'Principal',
    _FMA
  );

insert into
  ns.telefones (
    id,
    ddi,
    ddd,
    telefone,
    ramal,
    contato,
    descricao,
    tenant
  )
values
  (
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    '55',
    '21',
    '22222222',
    '11',
    '509d8af9-0f33-4030-9d3b-7538adbbc8e9',
    'Mesa ao lado',
    _FMA
  );

insert into
  financas.contasfornecedores (
    contafornecedor,
    banco,
    agencianumero,
    agenciadv,
    agencianome,
    contanumero,
    contadv,
    id_fornecedor,
    padrao,
    tenant
  )
values
  (
    '509d8af9-0f33-4030-9d3b-7538adbbc8e9',
    'BB',
    '1111111',
    '21',
    'centro',
    '12345',
    '11',
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    'true',
    _FMA
  );

insert into
  financas.contasfornecedores (
    contafornecedor,
    banco,
    agencianumero,
    agenciadv,
    agencianome,
    contanumero,
    contadv,
    id_fornecedor,
    padrao,
    tenant
  )
values
  (
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    'BB',
    '2222222',
    '22',
    'madureira',
    '12341',
    '12',
    '8d3b5470-4c75-4dd0-8576-0bb61d1f8638',
    'false',
    _FMA
  );

/* cliente: seguradora */
insert into
  ns.pessoas (
    id,
    pessoa,
    nome,
    nomefantasia,
    cnpj,
    tenant,
    clienteativado
  )
values
  (
    'f6309917-d2d0-4751-ba0b-44ffc2c8c9cd',
    '10',
    'Tempo Assist',
    'TEMPO ASSISTI',
    '06977739000134',
    _FMA,
    1
  );

insert into
  ns.enderecos(cidade_id, id_pessoa)
values
  (
    'adc67791-c178-47f0-81e8-522e2864c3b2',
    'f6309917-d2d0-4751-ba0b-44ffc2c8c9cd'
  );

insert into
  ns.enderecos(cidade_id, id_pessoa)
values
  (
    'adc67791-c178-47f0-81e8-522e2864c3b2',
    '460f64b5-e296-4ec6-8833-b93edd9310a7'
  );

insert into
  ns.conjuntosclientes (registro, conjunto, tenant)
values
  (
    'f6309917-d2d0-4751-ba0b-44ffc2c8c9cd',
    '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
    _FMA
  );

insert into
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'b12f35fb-d9a3-4002-9d6c-90126263803f',
    'AGE',
    'Agente funerário',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

insert into
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a9d6eefd-0d89-4c58-9cb0-1567cd40d0a2',
    'Urnas de luxo fam.1',
    'Urna luxo visor varão',
    _FMA,
    '1000.00'
  );

insert into
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '96c93b1b-4250-4af0-af3c-9278457f8ff2',
    'Urnas simples fam.10',
    'Urna visor',
    _FMA,
    '800.00'
  );

insert into
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '73dbcc64-87fd-4233-a0d8-419a1403e627',
    'Urnas simples fam.11',
    'Urna para cinzas',
    _FMA,
    '700.00'
  );

insert into
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6cc26af2-20cf-4c53-97b8-be1bdf13cc6b',
    'Coroa de flores fam.11',
    'Coroa de flores Rosas',
    _FMA,
    '500.00'
  );

insert into
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
values
  (
    'adc67791-c178-47f0-81e8-522e2864c3b2',
    'Velório Simples 1',
    'VELS1',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

insert into
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
values
  (
    'e0604d2f-e39f-480d-89e6-d1ee55617435',
    'Velório Luxo 2',
    'VELL2',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

insert into
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
values
  (
    '9028ff5e-4203-4d8b-8466-1faba3fd282d',
    'Sepultamento',
    'SEP1',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

insert into
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
values
  (
    '96c93b1b-4250-4af0-af3c-9278457f8ff2',
    'Cremação',
    'CRE1',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

/* Tipos atividades */
insert into
  ns.tiposatividades(tipoatividade, nome, descricao, tenant)
values
  (
    '66eab2c7-dce2-469c-aef9-a0347f755a16',
    'Funerárias',
    'Serviços funerários',
    _FMA
  );

insert into
  ns.tiposatividades(tipoatividade, nome, descricao, tenant)
values
  (
    'cd113949-10bf-4265-a17d-4c37eeb77701',
    'Seguradora',
    'Seguradora',
    _FMA
  );

insert into
  ns.pessoastiposatividades (pessoa, tipoatividade)
VALUES
  (
    'f6309917-d2d0-4751-ba0b-44ffc2c8c9cd',
    'cd113949-10bf-4265-a17d-4c37eeb77701'
  );

-- seguradora
insert into
  ns.pessoastiposatividades (pessoa, tipoatividade)
VALUES
  (
    '460f64b5-e296-4ec6-8833-b93edd9310a7',
    '66eab2c7-dce2-469c-aef9-a0347f755a16'
  );

-- funerária
/* midia */
insert into
  crm.midias (midia, nome, tenant)
values
  (
    '30a4b0ea-3ddf-4117-8f90-a58b35837ebe',
    'telefone',
    _FMA
  );

/* area */
insert into
  crm.negociosareas (negocioarea, nome, tenant, localizacao)
values
  (
    '3f12ccf5-0c97-4cb0-912c-eebaa4648b42',
    'Funeral',
    _FMA,
    'true'
  );

insert into
  crm.negociosareas (negocioarea, nome, tenant)
values
  (
    'f253f9ec-2ada-4af2-be06-7f669aa1492c',
    'Saúde',
    _FMA
  );

insert into
  crm.negociosareas (negocioarea, nome, tenant)
values
  (
    '269c5dd4-022b-4a65-a259-6d9369bec276',
    'Pet',
    _FMA
  );

insert into
  crm.negociosareas (negocioarea, nome, tenant)
values
  (
    '18f1ba76-147a-46b4-8193-4677d7027195',
    'Outros',
    _FMA
  );

/* campos customizados */
---------------- Dados do PET ----------------
INSERT INTO
  ns.camposcustomizados (
    campocustomizado,
    nome,
    "label",
    descricao,
    validacoes,
    opcoes,
    created_at,
    updated_at,
    created_by,
    updated_by,
    tenant,
    tipo,
    crmnegocio,
    crmnegocioobrigatorio,
    crmnegociohabilitatooltip,
    crmnegocioexibenalistagem,
    crmnegociosvisualizacao,
    objeto,
    visible,
    tamanho,
    secao
  )
VALUES
  (
    'da4e094d-4ab5-479d-9275-1ba16d6120c3',
    'PET',
    'Dados do PET',
    'Dados do PET',
    null,
    null,
    now(),
    now(),
    '{"nome":"gisele"}',
    '{"nome":"gisele"}',
    _FMA,
    'OB',
    true,
    false,
    false,
    true,
    true,
    '
  [
    {"nome":"nome","label":"PET","descricao":"Nome do PET","tipo":"TE","tamanho":"8", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"especie","label":"Espécie","descricao":"Espécie do animal","tipo":"TE","tamanho":"4", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"sexo","label":"Sexo","descricao":"","tipo":"CB","opcoes":["Femea","Macho"],"tamanho":"2", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {
      "validacoes":[{"validacao": "min","mensagem": "Valor inválido","valor": 0.01}],
      "nome":"peso","label":"Peso","descricao":"","tipo":"NB","tamanho":"2","visible":"true"
    },
    { 
      "validacoes":[{"validacao": "min", "mensagem": "Valor inválido","valor": 0.01}],
      "nome":"altura","label":"Altura","descricao":"","tipo":"NB","tamanho":"2","visible":"true"
    },
    {"nome":"porte","label":"Porte","descricao":"","tipo":"CB","opcoes":["Pequeno","Médio","Grande"],"tamanho":"2", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"raca","label":"Raça","descricao":"Raça do animal","tipo":"TE","tamanho":"4", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"causadofalecimento","label":"Causa do falecimento","descricao":"","tipo":"CB","opcoes":["Violenta","Natural"],"tamanho":"2", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"detalhesdofalecimento","label":"Detalhes do falecimento","descricao":"Detalhes do falecimento","tipo":"TE","tamanho":"12", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"datafalecimento","label":"Data do falecimento","descricao":"","tipo":"DT","tamanho":"4","visible":"true"},
    {"nome":"horafalecimento","label":"Hora do falecimento","descricao":"","tipo":"HR","tamanho":"4","visible":"true"}
  ]
  ',
    '"negocioarea == \"3f12ccf5-0c97-4cb0-912c-eebaa4648b42\""',
    12,
    'dadosPet'
  );

/* campos customizados */
---------------- Dados do Paciente ----------------
INSERT INTO
  ns.camposcustomizados (
    campocustomizado,
    nome,
    "label",
    descricao,
    validacoes,
    opcoes,
    created_at,
    updated_at,
    created_by,
    updated_by,
    tenant,
    tipo,
    crmnegocio,
    crmnegocioobrigatorio,
    crmnegociohabilitatooltip,
    crmnegocioexibenalistagem,
    crmnegociosvisualizacao,
    objeto,
    visible,
    tamanho,
    secao
  )
VALUES
  (
    'f6875c86-7d50-4194-9387-a605e5b41c5a',
    'Paciente',
    'Dados do Paciente',
    'Dados do Paciente',
    null,
    null,
    now(),
    now(),
    '{"nome":"gisele"}',
    '{"nome":"gisele"}',
    _FMA,
    'OB',
    true,
    false,
    false,
    true,
    true,
    '
  [
    {"nome":"nome","label":"Paciente","descricao":"Nome do Paciente","tipo":"TE","tamanho":"12", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"},
    {"nome":"ocorrencia","label":"Ocorrência","descricao":"Descrição da ocorrência","tipo":"TE","tamanho":"12", "crmnegocioexibenalistagem":"true", "crmnegociosvisualizacao" : "true","visible":"true"}
  ]
  ',
    '"negocioarea == \"f253f9ec-2ada-4af2-be06-7f669aa1492c\""',
    12,
    'dadosPaciente'
  );

/* campos customizados */
---------------- Dados do Falecido ----------------
INSERT INTO
  ns.camposcustomizados (
    campocustomizado,
    nome,
    "label",
    descricao,
    validacoes,
    opcoes,
    created_at,
    updated_at,
    created_by,
    updated_by,
    tenant,
    tipo,
    crmnegocio,
    crmnegocioobrigatorio,
    crmnegociohabilitatooltip,
    crmnegocioexibenalistagem,
    crmnegociosvisualizacao,
    objeto,
    visible,
    tamanho,
    secao
  )
VALUES
  (
    '16865f28-2d5d-41a4-9a81-1326dd72dfb8',
    'falecido',
    'Dados do Falecido',
    'Dados do falecido',
    null,
    null,
    now(),
    now(),
    '{"nome":"gisele"}',
    '{"nome":"gisele"}',
    _FMA,
    'OB',
    true,
    false,
    false,
    true,
    true,
    '
  [
    {
      "tipo":"TE","crmnegocioexibenalistagem":"true","tamanho":"4","label":"Falecido","disabled":"true","visible":"true","nome":"nome","crmnegociosvisualizacao":"true","descricao":"Nome do falecido"
    },
    {
      "opcoes":["Ateu","Cristão Católico","Cristão Protestante","Judeu"],"tipo":"CB","nome":"religiao","tamanho":"4","label":"Religião","visible":"true","descricao":""
    },
    {
      "opcoes":["Sepultamento","Cremação"],"tipo":"CB","nome":"tipo","tamanho":"4","label":"Tipo","visible":"true","descricao":""
    },
    {
      "opcoes":["Solteiro(a)","Casado(a)","Viúvo(a)"],"tipo":"CB","nome":"estadocivil","tamanho":"2","label":"Estado Civil","visible":"true","descricao":""
    },
    {
      "tipo":"NB","nome":"peso","tamanho":"1","label":"Peso","visible":"true",
      "validacoes":[{"validacao":"min","valor":1}],"descricao":""
    },
    {
      "tipo":"NB","nome":"altura","tamanho":"1","label":"Altura","visible":"true",
      "validacoes":[{"validacao":"min","valor":1}],"descricao":""
    },
    {
      "opcoes":["Feminino","Masculino"],"tipo":"CB","crmnegocioexibenalistagem":"true","tamanho":"2","label":"Gênero","visible":"true","nome":"genero","crmnegociosvisualizacao":"true","descricao":""
    },
    {
      "tipo":"DT","nome":"datanascimento","tamanho":"3","label":"Nascimento","visible":"true","descricao":""
    },
    {
      "tipo":"NB","nome":"idade","tamanho":"1","label":"Idade","disabled":"true","visible":"true",
      "validacoes":[{"validacao":"min","valor":0}],"descricao":""
    },
    {
      "tipo":"CH","nome":"possuifilhos","tamanho":"2","label":"Possui filhos","visible":"true","descricao":"Indicador de que possui filhos"
    },
    {
      "tipo":"CH","nome":"declaracaoObito","tamanho":"2","label":"Declaracao de Óbito","visible":"true","descricao":""
    },
    {
      "opcoes":["Natural","Violenta"],"tipo":"CB","nome":"causadofalecimento","tamanho":"6","label":"Tipo do falecimento","visible":"true","descricao":""
    },
    {
      "tipo":"DT","nome":"datafalecimento","tamanho":"3","label":"Data","visible":"true","descricao":""
    },
    {
      "tipo":"HR","nome":"horafalecimento","tamanho":"3","label":"Hora","visible":"true","descricao":""
    },
    {
      "tipo":"TE","nome":"detalhesdofalecimento","tamanho":"12","label":"Detalhes do falecimento","visible":"true","descricao":""
    }
  ]
  ',
    '"negocioarea == \"3f12ccf5-0c97-4cb0-912c-eebaa4648b42\""',
    12,
    'dadosFalecido'
  );

---------------- Dados do Responsavel ----------------
INSERT INTO
  ns.camposcustomizados (
    campocustomizado,
    nome,
    "label",
    descricao,
    validacoes,
    opcoes,
    created_at,
    updated_at,
    created_by,
    updated_by,
    tenant,
    objeto,
    visible,
    tamanho,
    secao,
    desativar,
    tipo,
    crmnegocio,
    crmnegocioobrigatorio,
    crmnegociohabilitatooltip,
    crmnegocioexibenalistagem,
    crmnegociosvisualizacao,
    duplicavel
  )
VALUES
  (
    '19954da7-2dad-4ac1-857f-66a816751de1',
    'responsavel',
    'Dados do responsavel',
    'Dados do responsavel',
    NULL,
    NULL,
    '2019-06-26 19:00:23.000',
    '2019-06-26 19:00:23.000',
    '{"nome":"joao"}',
    '{"nome":"joao"}',
    _FMA,
    '
  [
    {
      "tipo":"TE","nome":"nomeCompleto","tamanho":"6","label":"Responsável principal","visible":"true","crmnegociosvisualizacao":"true","descricao":"Nome completo"
    },
    {
      "opcoes":["Física","Jurídica"],"tipo":"CB","nome":"tipoPessoa","tamanho":"3","label":"Tipo Pessoa","visible":"true","descricao":"Tipo Pessoa"
    },
    {
      "opcoes":["Pai","Tio","Filho","Primo","Sobrinho","Avó","Neto","Avô"],"tipo":"CB","nome":"vinculoComFalecido","tamanho":"3","label":"Vínculo com falecido","visible":"camposcustomizados["19954da7-2dad-4ac1-857f-66a816751de1"]["indexcampocustomizado"].tipoPessoa === "Física"","descricao":""
    },
    {
      "tipo":"TE","nome":"cargoContato","tamanho":"3","label":"Cargo contato","visible":"camposcustomizados[""19954da7-2dad-4ac1-857f-66a816751de1""][""indexcampocustomizado""].tipoPessoa === "Jurídica"","descricao":"Cargo contato"
    },
     {
      "tipo":"TE","nome":"email","label":"E-mail","descricao":"E-mail","tamanho":"6" ,visible:"true"
    },
    {
      "tipo":"TE","nome":"telefone","tamanho":"3","label":"Telefone","visible":"true","crmnegociosvisualizacao":"true","descricao":"Telefone"
    },
    {
      "tipo":"TE","nome":"telefonesecundario","tamanho":"3","label":"Telefone secundário","visible":"true","crmnegociosvisualizacao":"true","descricao":"Telefone secundário"
    },
   
    {
      "tipo":"TE","nome":"nomeCompleto2","tamanho":"6","label":"Outro Responsável","visible":"true","crmnegociosvisualizacao":"true","descricao":"Nome completo"
    },
    {
      "opcoes":["Física","Jurídica"],"tipo":"CB","nome":"tipoPessoa2","tamanho":"3","label":"Tipo Pessoa","visible":"true","descricao":"Tipo Pessoa"
    },
    {
      "opcoes":["Pai","Tio","Filho","Primo","Sobrinho","Avó","Neto","Avô"],"tipo":"CB","nome":"vinculoComFalecido2","tamanho":"3","label":"Vínculo com falecido","visible":"camposcustomizados["19954da7-2dad-4ac1-857f-66a816751de1"]["indexcampocustomizado"].tipoPessoa2 === "Física"","descricao":""
    },
    {
      "tipo":"TE","nome":"cargoContato2","tamanho":"3","label":"Cargo contato","visible":"camposcustomizados["19954da7-2dad-4ac1-857f-66a816751de1"]["indexcampocustomizado"].tipoPessoa2 === "Jurídica"","descricao":"Cargo contato"
    },
    {
      "tipo":"TE","nome":"email","label":"E-mail","descricao":"E-mail","tamanho":"6", visible:"true"
    },
    {
      "tipo":"TE","nome":"telefone2","tamanho":"3","label":"Telefone","visible":"true","crmnegociosvisualizacao":"true","descricao":"Telefone"
    },
    {
      "tipo":"TE","nome":"telefonesecundario2","tamanho":"3","label":"Telefone secundário","visible":"true","crmnegociosvisualizacao":"true","descricao":"Telefone secundário"
    },
    
  ]
  ',
    '"true"',
    12,
    'dadosResponsavel',
    false,
    'OB',
    true,
    true,
    false,
    false,
    true,
    true
  );

-- Dados Contatos
INSERT INTO
  ns.camposcustomizados (
    campocustomizado,
    nome,
    "label",
    descricao,
    validacoes,
    opcoes,
    created_at,
    updated_at,
    created_by,
    updated_by,
    tenant,
    tipo,
    crmnegocio,
    crmnegocioobrigatorio,
    crmnegociohabilitatooltip,
    crmnegocioexibenalistagem,
    objeto,
    visible,
    tamanho,
    secao
  )
VALUES
  (
    '5a05898b-c6c3-46dd-9057-aa55f495c00d',
    'contatos',
    'Contatos',
    '',
    null,
    null,
    now(),
    now(),
    '{"nome":"gisele"}',
    '{"nome":"gisele"}',
    _FMA,
    'OB',
    true,
    false,
    false,
    true,
    '
  [
   	{"nome":"email","label":"E-mail","descricao":"","tipo":"TE","tamanho":"12","duplicavel":"false", "visible":true},
	{"nome":"contatos","label":"Contatos do Titular","descricao":"","tipo":"OB","tamanho":"12","duplicavel":"true", "visible":true, 
    	"objeto":[
		  {"nome":"tipo","label":"Tipo","descricao":"","tipo":"CB","opcoes":			["Principal","Whatsapp","Casa","Trabalho"],"tamanho":"6","duplicavel":"false", "visible":true},
      {"nome":"telefone","label":"Telefone","descricao":"","tipo":"TE","tamanho":"6","duplicavel":"false", "visible":true}
	    ]
    }
  ]
  ',
    'possuiseguradora',
    12,
    'contatos'
  );

  -- Dados do Velório e Sepultamento
INSERT INTO ns.camposcustomizados (campocustomizado, nome, "label", descricao, validacoes, opcoes, created_at, updated_at, created_by, updated_by, tenant, 
     tipo, crmnegocio, crmnegocioobrigatorio, crmnegociohabilitatooltip, crmnegocioexibenalistagem, objeto, visible, tamanho, secao)
VALUES('5bff7500-062b-4984-ba49-2c5db44ed2c3', 'dadosdoveloriosepultamento', 'Dados do Velório e Sepultamento', '', null,  null, now(), now(), '{"nome":"Breno"}', '{"nome":"Breno"}', 611,
        'OB', true, false, false, true, '[
    {
      "tipo":"TE","nome":"localvelorio","label":"Local do velório","descricao":"Local do velório","tamanho":"6","visible":"true"
    },
    {
      "nome":"datavelorio","label":"Data do velório","descricao":"Data do velório","tipo":"DT","tamanho":"3","visible":"true"
    },
    {
      "nome":"horavelorio","label":"Hora do velório","descricao":"Hora do velório","tipo":"HR","tamanho":"3","visible":"true"
    },
    {
      "tipo":"TE","nome":"localsepultamento","label":"Local do Sepultamento","descricao":"Local do Sepultamento","tamanho":"6", "visible":"true"
    },
    {
      "nome":"datasepultamento","label":"Data do Sepultamento","descricao":"Data do Sepultamento","tipo":"DT","tamanho":"3","visible":"true"
    },
    {
      "nome":"horasepultamento","label":"Hora do Sepultamento","descricao":"Hora do Sepultamento","tipo":"HR","tamanho":"3","visible":"true"
    }
  ]', 'negocioarea == "fe596681-6b32-4277-8ca3-ffec01aac4c7"', 12, 'dadosdoveloriosepultamento');


-- Dados do Translado
INSERT INTO ns.camposcustomizados(campocustomizado, nome, "label", descricao, validacoes, opcoes, created_at, updated_at,
    created_by, updated_by, tenant, tipo, crmnegocio, crmnegocioobrigatorio, crmnegociohabilitatooltip, crmnegocioexibenalistagem,
    objeto, visible, tamanho, secao)
VALUES('6f39c3bf-252d-4a79-bbb4-60673a8f8ce6', 'dadostranslado', 'Dados do Translado', '', null, null, now(), now(),
       '{"nome":"Breno"}', '{"nome":"Breno"}', 611, 'OB', true, false, false, true, '[
    {
      "tipo":"TA","nome":"infotranslado","label":"Informações","descricao":"Informações do translado","tamanho":"12", visible:true
    }
  ]', 'negocioarea == "fe596681-6b32-4277-8ca3-ffec01aac4c7"', 12, 'dadostranslado');

/* negocios */
insert into
  crm.negocios (
    negocio,
    nome,
    area,
    origem,
    camposcustomizados,
    tenant
  )
values
  (
    '417c3140-736d-4e87-97d8-2a6819fc5243',
    'Funeral da Maria da Silva 1',
    '3f12ccf5-0c97-4cb0-912c-eebaa4648b42',
    '30a4b0ea-3ddf-4117-8f90-a58b35837ebe',
    '{"da4e094d-4ab5-479d-9275-1ba16d6120c3" : {"nome" : "Maria da Silva", "genero":"Feminino"}, "19954da7-2dad-4ac1-857f-66a816751de1" : {"nomeCompleto" : "joão da Silva"}}',
    _FMA
  );

insert into
  crm.negocios (
    negocio,
    nome,
    area,
    origem,
    camposcustomizados,
    tenant
  )
values
  (
    '30a4b0ea-3ddf-4117-8f90-a58b35837ebe',
    'Funeral da Maria da Silva 2',
    '3f12ccf5-0c97-4cb0-912c-eebaa4648b42',
    '30a4b0ea-3ddf-4117-8f90-a58b35837ebe',
    '{"da4e094d-4ab5-479d-9275-1ba16d6120c3" : {"nome" : "Maria da Silva"}, "19954da7-2dad-4ac1-857f-66a816751de1" : {"nomeCompleto" : "joão da Silva"}}',
    _FMA
  );

--responsavel financeiro insert into ;
---------------- Funções ----------------
INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '151de598-35ac-4e99-95cd-2a524bc48626',
    'Anestesista',
    'Anestesista',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '8fbf5fc8-10bb-4223-a786-b9865832f2c4',
    'Assistente funerário',
    'Assistente funerário',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '5f6c42f9-90b3-43ef-bb32-6e6fedb9aa4d',
    'Assistente Social',
    'Assistente Social',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '5b9f2b01-1d2d-4b13-80bc-9f3ca610f188',
    'Auxilar de Cirurgia',
    'Auxilar de Cirurgia',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '6292d7e1-c38e-4baa-8f67-7170a9d5adde',
    'Cirurgião',
    'Cirurgião',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '9202cd75-47ba-4678-bcc5-e68cf652bbe9',
    'Copeira',
    'Copeira',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'ac9562c6-03ac-4d59-8228-dc68b1aeeed1',
    'Coveiro',
    'Coveiro',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '21a347f1-919c-4573-a3c9-d35eb3a7cff5',
    'Despachante',
    'Despachante',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'd2c35055-99df-44af-b960-57b368f32f44',
    'Despachante Aduaneiro',
    'Despachante Aduaneiro',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '91633a4d-9818-4eb5-9b74-c41e1d2e37e0',
    'Enfermeiro',
    'Enfermeiro',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'aeda6cd3-926c-4af1-8ba8-6f9fdb140c56',
    'Equipe Médica',
    'Equipe Médica',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '35a8c016-4eaf-4349-8f9b-f64b0c74d684',
    'Equipe Ortopédica',
    'Equipe Ortopédica',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'c4126819-b500-408e-91d9-7dca1a631d38',
    'Garçon',
    'Garçon',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '80dcb8ec-e30d-4edc-895e-b94abafaeedb',
    'Médico',
    'Médico',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'ac15e57a-d566-46de-a221-af5a11b28acc',
    'Médico Cardiologista',
    'Médico Cardiologista',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '9d9f7ed1-04cf-4a62-8fd5-7b9e163d34a7',
    'Médico Clínico Geral',
    'Médico Clínico Geral',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '2e818b75-1058-4337-8408-3d9a782cee06',
    'Moto-boy',
    'Moto-boy',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    '25ff9929-bb6a-4632-ae61-15e63be9a80b',
    'Músico',
    'Músico',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

INSERT INTO
  gp.funcoes (
    funcao,
    codigo,
    descricao,
    tenant,
    created_by,
    updated_by
  )
values
  (
    'f487b2e2-fd85-471e-82e6-ffdc0e063fbf',
    'Padre',
    'Padre',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}'
  );

---------------- Catalogos e catalogoscapitulos ----------------
INSERT INTO
  crm.catalogos (catalogo, nome, publicadoem, tenant)
values
  (
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    'FMA 2019',
    now(),
    _FMA
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '7c41aa94-70d7-4e13-bd47-9a647ddc2b6b',
    '1',
    'Coroas',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    'c1e5f17a-1448-4ed9-afd0-0215912eee53',
    '1',
    'Arranjos',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '1534a934-70a3-4fb6-9e69-7e2df40b8366',
    '1',
    'Bandeiras',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    'ff79cb6c-8356-41bd-8a60-1b1d2a59c890',
    '1',
    'Exames',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '8a20c42e-6bad-4e6c-9255-30dc9801f2eb',
    '1',
    'Flores',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '4ea0983d-c2d0-4e60-9bf7-d5501c47410d',
    '1',
    'Urna',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '360c8f0d-7939-4d35-a4d0-cf46e51db5de',
    '1',
    'Alimentação',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    'bc22bd65-3144-490b-a03e-dfc286b51c55',
    '1',
    'Vinho',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '360c8f0d-7939-4d35-a4d0-cf46e51db5de'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '552f6fe6-3739-4b3d-a7d7-9b54f18f7d5c',
    '1',
    'Café',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '360c8f0d-7939-4d35-a4d0-cf46e51db5de'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '461f8aa0-d481-4b04-a7b0-3f78107d9cdc',
    '1',
    'Biscoitos',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '360c8f0d-7939-4d35-a4d0-cf46e51db5de'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '47318f89-df73-4a52-a0f8-5fef2fa6cdf7',
    '1',
    'Coroas de Flores',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '7c41aa94-70d7-4e13-bd47-9a647ddc2b6b'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '8f2e8f1d-fce1-4597-89af-6b868231ec79',
    '1',
    'Arranjo de Flores',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    'c1e5f17a-1448-4ed9-afd0-0215912eee53'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '1b8bf32b-e8f1-4ecd-8f10-7ae0e2ba5d56',
    '1',
    'Caixinha',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '4950abd0-57cf-4b49-a248-3d11032a444d',
    '1',
    'Convites',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    'f2fc91c3-535d-4ace-a6f6-6caa0d2645f7',
    '1',
    'Descarte',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    'db3a13e6-0793-4b96-b446-b6c41cc7ecc6',
    '1',
    'Frete',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '2c5a4865-6eec-4814-b702-023b1c903170',
    '1',
    'Lacre',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    'f02a53b2-2f4a-439b-ae5c-c3992526a330',
    '1',
    'Manto',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '56684e76-00fc-4e21-b83d-03c89c38fd3a',
    '1',
    'Coroas de Flores Artificiais',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '47318f89-df73-4a52-a0f8-5fef2fa6cdf7'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    'a4326d29-b169-4019-a060-b0d589799880',
    '1',
    'Coroa de Flores Naturais e Artificiais',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '56684e76-00fc-4e21-b83d-03c89c38fd3a'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '6568d262-dc95-4e05-a908-0a6ec83cc403',
    '1',
    'Coroa de Flores Artificial',
    _FMA,
    false,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '56684e76-00fc-4e21-b83d-03c89c38fd3a'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    'ebabd841-bbf5-4e0f-a811-65de66a7f801',
    '1',
    'Coroa de Flores Artificiais Grande',
    _FMA,
    false,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '56684e76-00fc-4e21-b83d-03c89c38fd3a'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '1',
    'Coroa de Flores Artificiais Média',
    _FMA,
    false,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '56684e76-00fc-4e21-b83d-03c89c38fd3a'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '1df15833-6260-4fd3-824d-ba595f88fef3',
    '1',
    'Placa',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '655d019a-bfca-4e3d-9601-18340e75190c',
    '1',
    'Plaqueta',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    'ff79cb6c-8356-41bd-8a60-1b1d2a59c890'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    'd1c678e6-4f0a-40c8-82ce-41a64eca0426',
    '1',
    'Raio X',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    'ff79cb6c-8356-41bd-8a60-1b1d2a59c890'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '9eb716d4-8712-4c97-b38c-4f155af0e46d',
    '1',
    'Ressonância Magnética',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    'ff79cb6c-8356-41bd-8a60-1b1d2a59c890'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '28b20221-4808-482d-acea-44e3e684203e',
    '1',
    'Rosas',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    '8a20c42e-6bad-4e6c-9255-30dc9801f2eb'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '20c6d4db-314a-4462-9c6d-73e4545f1309',
    '1',
    'Roupa',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '54675573-154b-4bbd-8557-433d1e1799ad',
    '1',
    'Saco',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '04eb9b45-bc10-447f-9f72-ac4026eb367b',
    '1',
    'Taxas',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo,
    pai
  )
values
  (
    '9144ed6e-6f93-4665-8d09-35b6fef25b96',
    '1',
    'Tomografia',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202',
    'ff79cb6c-8356-41bd-8a60-1b1d2a59c890'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    'e6b3d8f5-ede4-4394-8cd6-527ba9eb6626',
    '1',
    'Vedação',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

INSERT INTO
  crm.catalogoscapitulos (
    catalogocapitulo,
    numero,
    nome,
    tenant,
    possuifilho,
    created_by,
    updated_by,
    catalogo
  )
values
  (
    '1360a0af-2843-42c4-851a-2866db95c99e',
    '1',
    'Véu',
    _FMA,
    true,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    'c99b4468-bc54-42c0-8e2a-e953db31e202'
  );

---------------- Composicoes ----------------
INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '477f242c-6a48-4c8c-b870-eda1d0fc2cef',
    'Anúncios de Rádio',
    'ANUN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bd9ffd96-5430-49d6-b17b-f3eb407e551f',
    'Anúncios em Jornal',
    'ANUN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '39f2f999-dc5d-4e9c-9583-db6cdecd75e1',
    'Capelas',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e2788755-6c6f-4e0b-b7ea-5ba9d1829af2',
    'Carros para Família',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9ecb83f7-3c08-4f1c-a6cb-24a5c7904482',
    'Carros Particulares',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a49e1869-67ed-4fc9-8cf1-1def44dd1346',
    'Documentações',
    'DOCU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '47b7ba91-a7b8-4cc1-bf4a-f300eb88d9d2',
    'Embalsamamentos',
    'EMBA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b140c4db-9999-414a-82a0-dce677307f03',
    'Exumações',
    'EXUM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b41938b3-0243-46d5-a555-5224d464f67c',
    'Higienizações',
    'HIGI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '187f150e-d898-4eca-a5b6-38942decac16',
    'Lages',
    'LAGE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '494f8db5-da66-414b-aed3-f8cb666a837a',
    'Ornamentações',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fcd62955-8bf8-4b03-a50b-de002676277d',
    'Remoções',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '35b6bc1a-ac4d-4af9-822b-4689ce076992',
    'Seguranças',
    'SEGU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '953332c6-2054-4a26-b62a-b534ebfce5a0',
    'Serviços de Buffet',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c76458d3-5f44-44f6-bb21-7b161fee28f0',
    'Tanatopraxias',
    'TANA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3dbc0b75-f752-4be4-b726-0d13d24ea4b9',
    'Traslados Terrestres',
    'TRAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c6caf9f7-8e6c-4a0a-8e8b-73b83a0bcc9b',
    'Ventiladores',
    'VENT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '533ab9ee-6769-497f-a5e6-8eee1cd06e6c',
    'Via certidão óbito',
    'VIA ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '42637cf1-1033-4583-b0f4-c87cd1cbb025',
    'Buffet',
    'BUFF',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cebb7e18-4003-439e-bb23-bd8a26dd3a75',
    'Transportes',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3f7dee92-fb7c-4217-9ddf-83c3ccbff5d5',
    'Abdômen total (ultrassonografia)',
    'ABDÔ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6dc49205-e574-4e3a-a0cc-8156f9c272f4',
    'Acompanhamento',
    'ACOM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c82c6ddf-c1eb-4699-a5ca-42021e85d6c4',
    'Acondicionamento (Maquiagem e higienização)',
    'ACON',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '144d8a46-c451-4ddc-8a0e-55b9eac12006',
    'Adiantamento de Fundos',
    'ADIA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '167b19a9-b739-47f9-bdfd-24d8915ccb18',
    'Adiantamento dinheiro',
    'ADIA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '011571dc-24cc-49ea-929e-959fc8890226',
    'Aluguel de Caminhão',
    'ALUG',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8b825832-2fcc-4187-9c56-90108ce5b461',
    'Aluguel de jazigo municipal',
    'ALUG',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '035ad29b-cc96-489e-a4be-4c4af122fbd0',
    'Aluguel de Jazigo particular',
    'ALUG',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2b65ccb7-4873-489f-8318-68b2b934f981',
    'Aluguel de Moletas',
    'ALUG',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5dcc6f2c-30ce-405a-8683-675f25047566',
    'Análise da coleta',
    'ANÁL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '10c5fd76-72e7-4d67-ba3d-cde416503a14',
    'Anúncio de Falecimento (carro)',
    'ANUN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fd028350-e54f-4ea4-a0e7-65d2d2b8179d',
    'Anúncio de falecimento(moto)',
    'ANUN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cc9f69e6-c527-4ada-85c9-67a27bb0cf91',
    'Anúncio de Rádio (nota de falecimento)',
    'ANUN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9e55d810-f13d-4c11-8006-6d5e1fa56009',
    'Aplicação de inseticida',
    'APLI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7c33941a-dd6f-4ca7-8fff-ba5badf94975',
    'Aplicação de Material',
    'APLI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '89d4977a-825a-406f-a604-175b2ac17df7',
    'Apostilamento',
    'APOS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '46467b6e-b57b-4676-8027-9451d259c7d6',
    'Aquisição do Terreno',
    'AQUI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f19efa38-cd27-4e44-b35d-38901457888a',
    'Arrumação do Tumulo',
    'ARRU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6131da31-d079-4fdd-8b8b-304eaf703cbe',
    'Aspiração',
    'ASPI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bb30f791-ff1c-427d-982e-89bcdbce4c97',
    'Aspiração Infantil',
    'ASPI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd364a978-09f4-42d6-82dd-7cb1166f4998',
    'Assistência a Segurado',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '58c730ba-805b-4347-a1fa-b8b0b1701f98',
    'Assistência ao velório',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '555cd3b2-5f6a-4d37-96e8-d0e9452d1d54',
    'Assistência da Funerária',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b1c2f40a-148e-4784-8342-67a0ad0e9056',
    'Assistência da Funerária + Paramentos',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f29fcc87-680d-4f97-bb5d-25c6eb468e59',
    'Assistência enfermeira / coral / copeira',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f53236cc-79bb-4794-a08c-ecdb9ef81715',
    'Assistência Funeral',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '28248697-0a57-493c-a54e-28219655e0b7',
    'Assistência Funeral a Outros Municípios',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7f5629ec-cba2-444a-b23c-764a2746fe47',
    'Assistência Médica',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd8baa2f9-264f-466a-82cd-38949bad26c7',
    'Atendimento Odontológico',
    'ATEN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '78b87cd3-948c-4704-89ee-789610bc106a',
    'Autenticação',
    'AUTE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '158ee679-4e35-4d25-8a04-9333710a7d50',
    'Auxilio para Cremação',
    'AUXI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8c6a201c-0e8c-4cda-b335-2a2e8de4b414',
    'Barraca para Velório',
    'BARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a1982d58-b72d-4d00-9218-9112c43aaf09',
    'Cadeiras',
    'CADE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '55acfbab-8a5a-4734-8ebe-e6c8c1e9887c',
    'Câmara Ardente',
    'CÂMA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5f803ee5-8fa2-47bb-bd1b-9e8460d3b6b5',
    'Caminhão para entrega das coroas',
    'CAMI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a74c5abd-9985-4518-96f6-05ea4e40ed60',
    'Cântico com orquestra',
    'CÂNT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b80bdd13-574b-481c-9ed0-a2deb1051f1c',
    'Captação de Documentos',
    'CAPT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c36bdf7d-0b70-45a6-bc3b-dbabb59d8cd8',
    'Carreto (Essa/Caixão)',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '23241bf8-0311-4d8a-8b1a-f02b92613b98',
    'Carrinho',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5717c5ec-0324-4bca-9249-648ad660ae7d',
    'Carro com Motorista para Familiares',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a31f3e68-25d4-4de3-9f9c-9f8e7277b0c5',
    'Carro de Som',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c423ff18-4759-448e-b834-9d635f7b0ee8',
    'Carro Elétrico',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '37288a10-c6b0-4355-b5a1-6f88b5219c0d',
    'Carro Funebre para Família',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cc3e0627-77a7-42a8-b3a8-5e51d347e2c1',
    'Carro para família',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '309185f6-8455-4fc1-bf3a-11a221759059',
    'Carro para Sepultamento',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cae2e21c-b37f-4518-8dfc-3100f51253f1',
    'Castiçais',
    'CAST',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bfa01eb5-9c7a-431a-8ac3-6eae8247dacb',
    'Cerimonial',
    'CERI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd37913e0-aaa8-40c9-ad5d-8d3a16d70026',
    'Cerimonial com Violino',
    'CERI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '055be1df-d7a8-4c20-90df-ebcf4323dcc3',
    'Cerimonial Padrão',
    'CERI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '62e053ee-cf56-4b37-8a4b-46ea076d1768',
    'Cirurgia',
    'CIRU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '38cfe9f0-c7be-49a3-a0f0-d10c7ad631d2',
    'Coagulograma',
    'COAG',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5a987600-f747-4be8-9ffb-920f937bca76',
    'Coche',
    'COCH',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8e66a1ad-9996-425a-9b9e-99bb98d55822',
    'Coleta de documentos',
    'COLE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4f9d9e6e-a89e-4fa6-a18b-fddbe61d8add',
    'Colocação de tampa',
    'COLO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a44739f7-fdfd-49c9-b232-01587cf12bee',
    'Compra de carneira',
    'COMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3b3afe79-2238-44d6-90f2-eb4aefad82fb',
    'Compra de Gaveta',
    'COMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'aa83e766-2ba1-4126-aa00-18232aae9dd1',
    'Compra de Jazigo',
    'COMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '71b296aa-8030-4668-8594-a1de32a1fee1',
    'Compra de Passagens',
    'COMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '09c33edd-04a0-4d0f-a7b1-8db0c74bedf1',
    'Compra de Terreno',
    'COMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '37ce9d99-140b-4cd8-9642-a70db4a77314',
    'Concessão (Mudança de nome)',
    'CONC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd0b53902-b38e-4f93-8edd-0941447f3dcc',
    'Concessão de terreno',
    'CONC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '27fde3df-be4b-4856-98d7-daaf9b9f67ff',
    'Conduções',
    'COND',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '68702b83-9aef-4ea4-9310-4e09cc9d5040',
    'Construção de divisoria do jazigo',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7206892e-212b-4979-b716-a2c524698e4f',
    'Construção de gaveta',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0eae2b4f-748f-4faf-94e4-d2d33988df08',
    'Construção de Jazigo',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a75c192e-5b7d-4cf9-a0df-4887fad90f9f',
    'Construção Estrado p/ jazigo',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '922b10d3-300b-4f20-a51d-787f30cf2295',
    'Consulta Clínico Geral',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '526139a8-345b-4d43-93c3-4339cd5199fc',
    'Consulta com otorrinolaringologista',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '49079fa7-7b7e-4120-89a7-8a0fc5e413c0',
    'Consulta com Pediatra',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e4a32e21-16d1-4e42-9bba-af3b35789a8e',
    'Consulta de Emergência',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '83ef6e80-88b4-4179-bef7-18b58b63cdcc',
    'Consulta de retorno',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8b6ee3d4-aa8c-4c32-bc0b-73501280dc8d',
    'Consulta e Procedimentos',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b5ce5c9a-aef2-4b76-a99c-b64754e5f39a',
    'Consulta Fisioterápica',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '429721f2-bf02-4d3e-a485-2e22c6c61412',
    'Consulta Médica',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '48866c47-0298-442e-b24d-cde121e9367b',
    'Consulta Ortopedista',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5325a490-b643-4291-b6df-2cbe7f1be349',
    'Consulta simples',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '13e63153-b83b-4bdc-8b21-f863a09ade1b',
    'Contrução de carneira',
    'CONT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5e065c7a-e7e9-42f8-a7f3-4b9be694f33e',
    'Coral',
    'CORA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '04d9118d-c15d-4491-89d2-aeb0d9a0a4c0',
    'Corte de Gaveta',
    'CORT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4a299b26-bc82-4df7-bb09-7f0c8ef304e4',
    'Cortejo',
    'CORT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '89d5aeaa-9870-4b84-b5c4-843e82e84bf5',
    'Cremação',
    'CREM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2faee20f-d745-4f16-a765-49f79c4ae640',
    'Cremação + Capela Particular',
    'CREM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4662de36-c334-4e3d-89ed-b43a785ff4b2',
    'Custo de Serviço Túmulo Executado a Terceiros',
    'CUST',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0c206e25-b7bd-4bf3-b74b-c38314955a2e',
    'CVF Concessão',
    'CVF ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '76f19fd0-36d1-43b5-b97d-de9f110f3d56',
    'Data Show',
    'DATA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e09901fd-4b4f-4698-b68d-9f68bef9ff8e',
    'Declaração de Óbito',
    'DECL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bcd74d76-6738-42a0-81c4-2f9be2599467',
    'Declaratoria',
    'DECL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e2edf2d5-49fe-4293-8535-3d62c205f1dc',
    'Declaratório publica',
    'DECL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6349ff11-229c-4ea7-9c2b-30df155e0edf',
    'Decoração',
    'DECO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bd41d6af-5698-4d37-ad30-80bdef50239f',
    'Decreto Municipal',
    'DECR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a3273230-ec9a-47fa-912d-73042003d144',
    'Demarcação de Jazigo',
    'DEMA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ac077ff2-d925-4bd9-897f-9d6ca0c1b571',
    'Depilação',
    'DEPI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '50230f03-e25b-46aa-b7ae-4f4d7dddf204',
    'Descarte de roupa',
    'DESC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b5e86d28-ac25-4c40-aca1-e6b84658d6f0',
    'Descarte de Urna',
    'DESC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '17c9d972-4b25-4122-95b3-a026214bc130',
    'Deslocamento',
    'DESL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cd1096fd-d4c6-45ce-9e8c-0fa50f4e6e4e',
    'Desodorização',
    'DESO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5eccda26-3552-41d7-86e2-fb5aecaa30bc',
    'Diária de Hotel',
    'DIÁR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bdb7b40a-6a49-4cbc-9ad8-e4a5455f7bcc',
    'Diária Hospitalar',
    'DIÁR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '43b40d67-d1b2-4d8c-b34c-c395ecc569ab',
    'Eletrocardiograma',
    'ELET',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e95cf62d-e34c-48a0-9615-a65cfbe4fa91',
    'Eletrocardiograma de repouso',
    'ELET',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3fb4cea5-b6cf-45e4-9ab0-90fe2a359fbe',
    'Embalsamamento',
    'EMBA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '08195469-9a81-4104-ab70-3a1196b8da61',
    'Emissão de Alvará',
    'EMIS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'df259419-4a18-42d2-bfd2-56a9b9514674',
    'Emplacamento',
    'EMPL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b5484ffb-0796-4246-abe3-75edd1ca256b',
    'Enchimento',
    'ENCH',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f73c3a39-162c-406c-bff0-3ef9e3adcde1',
    'Enfaixamento no IML',
    'ENFA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '476d8923-55cf-4147-a8a3-ebfd10fafd62',
    'Entrada do Corpo',
    'ENTR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3faa784d-a7ca-4358-ac77-371694efa1c9',
    'Escritura Declaratória',
    'ESCR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'faa8c292-365e-4439-a1cb-60c85cb04fa9',
    'Estrado',
    'ESTR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd13cd897-7bef-406c-9829-880e5a474709',
    'Evasão de município',
    'EVAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3471db02-ef8c-406a-9874-dbcef7d015b7',
    'Exame',
    'EXAM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6b45a1cc-7f8d-415a-a536-87fbcded8ffa',
    'Exame médico',
    'EXAM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'be6ba11b-98d0-4662-9ca7-91a95808a0f1',
    'Exame Radiológico',
    'EXAM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '20360e32-43f3-4925-9409-63730e9218f9',
    'Exames Laboratoriais',
    'EXAM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '67466239-ebe0-4ad6-b5ff-f0d5a6459a9b',
    'Expediente da Sala',
    'EXPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '57470ecc-0093-4f67-a0bd-39ced03ef231',
    'Expediente por folhas á mais no processo',
    'EXPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '771140e0-881b-4f9f-a9e6-2c892a7fdbb9',
    'Extras',
    'EXTR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a5a82956-5686-4ac7-a9a0-b2e9a027fa61',
    'Exumação',
    'EXUM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '87e9382b-8ae9-4d75-98d2-89c922dbae8b',
    'Exumação Antecipada',
    'EXUM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7203b7e3-221f-43a3-8db6-87f0e49b273b',
    'Exumação e limpeza',
    'EXUM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '27485bf7-1f39-473a-8cc3-64d326182d2e',
    'Fechamento de Jazigo',
    'FECH',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'eff06b63-7ec3-441d-bdbb-ad2d2d69ded8',
    'Fechamento de jazigo municipal',
    'FECH',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8ea166fb-0af3-4e71-b296-617c48fd9a37',
    'Fechamento de Jazigo particular',
    'FECH',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9f1480b0-4617-4481-a47f-8782edddc339',
    'Filmagem',
    'FILM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '50317a6c-e8b0-4fbc-9cc5-dab209ff55b6',
    'Fisioterapia',
    'FISI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f9e3582c-e51f-4faf-9078-34caf8543464',
    'Fumigação',
    'FUMI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '51eb1ab0-d46c-4dcc-9d9d-a71004ec4bef',
    'Formolização',
    'FORM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '63c5a6e6-447b-4215-bd61-edb1776ccfb6',
    'Forração da Urna',
    'FORR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '17e1b1f4-e31e-4ab2-9b1a-e17740506e5f',
    'Geladeira',
    'GELA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6c2c7cc6-db12-4de7-987d-0cd497db7356',
    'Gravação',
    'GRAV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '276191ef-258a-4982-88c5-973cfaa2917b',
    'Gravação de CD fúnebre',
    'GRAV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bd45d92a-76ed-4889-89f4-78833613ca50',
    'Gravação de Lápide',
    'GRAV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a4baedf9-aca2-4d7d-8f7c-515d9e74c1f7',
    'Hemograma completo',
    'HEMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3aeb67e7-ebfb-4bee-9443-a02ec57260c5',
    'Higienização',
    'HIGI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '96a58293-d0bf-4dac-8cfd-747bd6653c0f',
    'Higienização + Tamponamento',
    'HIGI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '241de82b-ddef-4744-b66b-34517c5340df',
    'Higienização e Maquiagem',
    'HIGI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '54cbe99d-13e5-47ec-af86-e95aede1976e',
    'Higienização+tamponamento+Aspiração',
    'HIGI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0bb36c09-6be2-434c-9919-48245b7ebbda',
    'Homenagem',
    'HOME',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4659bcea-3a75-4564-aeb4-62ba2891923e',
    'Homenagem em Velório',
    'HOME',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1afe7ef2-7fd8-4f6e-ac39-fd652b0168ec',
    'Hospedagem',
    'HOSP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '37fe99ef-5509-4cc3-86a6-fdf57c28fc26',
    'Igreja',
    'IGRE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9ffa0ac3-45c3-413f-a191-3f7b5b32cc42',
    'Imobilização',
    'IMOB',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9a45943b-8883-481b-8383-900c66fcb655',
    'Incineração',
    'INCI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'eebf2580-c368-4080-b019-a21aa23398df',
    'Instalação de Soro',
    'INST',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1240fcf0-a759-4c08-9928-fd023f06fc0b',
    'Inumação',
    'INUM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3798f1dc-c3d5-46ef-a872-fc12ff7353df',
    'Inumação em tampa de cova de criança',
    'INUM',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8c76b45c-4e9a-47fe-87c1-637dc89dd33c',
    'Lacragem',
    'LACR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '532659d3-669f-4f21-8756-d3372c3e6869',
    'Lanche',
    'LANC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '28487a39-550e-4337-90a9-2890a0e255df',
    'Lanche e Café',
    'LANC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9cc74bd8-1c18-43fd-ac01-9dc84249cce6',
    'Lanche e Farmácia',
    'LANC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '78a345b3-0ff5-4e18-82da-a6d43aaad1ba',
    'Liberação',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ecee5c4a-12b8-4a9b-8d41-ebfc18336ad0',
    'Liberação Acesf',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4d8cba54-a5ba-417f-96cc-50d1f52697d8',
    'Liberação Aeroporto',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ad029501-37ad-4e68-a154-b72c8009f0f1',
    'Liberação Aeroporto + Traslado Terrestre',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ff4d3568-ffc7-49d0-a8c0-7c2d1e5093f9',
    'Liberação de Sepultamento',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '62487b38-4968-4fd9-836c-8ed27bc22f3a',
    'Liberação do Corpo',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ae116522-69cf-4170-b313-763abddedee1',
    'Liberação IML',
    'LIBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd8f3b2a9-edee-4423-9173-267ba502d2af',
    'Limousine para Cortejo',
    'LIMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bc0bcc80-714c-4f6a-98e8-8cd4f74033e0',
    'Limpeza de capela',
    'LIMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6576cb09-57b8-4050-bd4c-b27d505f3597',
    'Limpeza de Jazigo',
    'LIMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '72f3d40c-2455-4aba-a70b-da69d5081299',
    'Locação de Sepultura Municipal',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e4dea3ba-8661-4d99-8b1e-2af28b61d2e8',
    'Locação de Aspirador',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f9e260d7-2851-48c0-b159-b425e97116c8',
    'Locação de Automóvel',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2320b80a-267e-428c-9b36-38a8fc56a137',
    'Locação de Cinzário',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9011462f-2477-4c92-ae4b-da2c7509cd4a',
    'Locação de Columbário',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '01eb9f3a-ef64-4b38-b1af-8815ae550f4c',
    'Locação de Gaveta Municipal',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '58b5b4e8-2e67-481f-8dd7-28da2618ce1b',
    'Locação de Gaveta Particular',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '525ae007-7565-407a-abbd-bac33a0c12e3',
    'Locação de Jazigo Municipal',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2befc5b1-a907-4191-8d49-ab163bf9b118',
    'Locação de Jazigo Municipal + Capela municipal',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '06942820-2a84-4fa5-861f-c2bbfaf2d74b',
    'Locação de Jazigo Particular',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2890862e-1849-4fbe-8c46-4a76666b65dd',
    'Locação de Jazigo Particular + Capela particular',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ab385812-5bcb-4657-9bce-c1b260e64ac5',
    'Locação de jazigo particular + Taxa sepultamento',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7472ca71-9b8c-4dda-8eec-f8ff6d687e7e',
    'Locação de Muletas',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4c2fd41b-8cc4-4f8e-b84a-f99fcf331c49',
    'Locação de Nicho',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b6dc9c25-a89a-4b6a-acba-63309f53c942',
    'Locação de Sepultura Particular',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '423bb96a-9386-4132-8459-0198a1c7ec94',
    'Locação de Sepultura Rasa',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'aa2f182b-b872-405f-88db-fb27c4e817be',
    'Locação de Terreno',
    'LOCA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3c12cd60-89db-43c3-a421-06d901fe1322',
    'Manutenção de Jazigo',
    'MANU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'faf252c1-dd9c-461f-867c-c6630b8a5e40',
    'Mão de Obra',
    'MÃO ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0107fb8c-34e6-433f-a937-bbc00e60ebb9',
    'Mão de Obra do Pedreiro',
    'MÃO ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ecadff61-58a1-45b3-8f94-1f69270df46c',
    'Maquiagem',
    'MAQU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e111da23-0cf7-4bd5-a4c9-720c1528096a',
    'Massagem Corporal',
    'MASS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '79fe0d6a-a7f4-4a3e-b995-ebf0c60d03e1',
    'Medicação',
    'MEDI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1558006f-0ee6-4bd8-a282-99f15bb92e0a',
    'Medicação + Raio X',
    'MEDI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a8586ae4-d903-4cf7-942a-810fb002964a',
    'Melhoria de Sepultura rasa',
    'MELH',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1cc55552-b03f-4260-93c6-801726d4ef84',
    'Missa',
    'MISS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2f5c4606-c739-42c4-aaa3-20783f2c1027',
    'Montagem de velório',
    'MONT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bfded8e9-876c-4556-ac02-e42dd9d68f3c',
    'Nacionalização',
    'NACI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '382c54c3-1198-4a13-a8b5-3036047c499e',
    'Nebulização',
    'NEBU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0cd9f824-3698-4fd0-a023-4eaf0e14ac61',
    'Necromaquiagem',
    'NECR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd4a6f5dd-1efe-4b12-bdb4-68787e0bfa79',
    'Necrópcia',
    'NECR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3a212430-94b3-4881-87de-03e242ceac57',
    'Nota de falecimento',
    'NOTA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fe6ed7aa-cc5d-4f62-a6d1-997e1e71b223',
    'Org e Serviço de Agencia',
    'ORG ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '09f57bf0-775f-4616-87b3-8fbae30d561a',
    'Organização funeral',
    'ORGA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bdad1262-ad19-4152-a9bb-d7f0024f3d1c',
    'Organização Técnica e Serviço de Agência',
    'ORGA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4e94464c-8b7e-4246-8426-ee3fdaa3cbf2',
    'Ornamentação + 1 Coroa de flores',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3a974e9e-491a-43b4-bc01-5852f2caa295',
    'Ornamentação Cemitério',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e8ace8b7-afc6-4013-894c-4601430348fc',
    'Ornamentação de Capela',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c5b2acc9-be23-4ca2-9f61-b90f112a9ecd',
    'Ornamentação de Flores Artificiais',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ca5e02f8-2551-48a3-afdb-44939189d18c',
    'Ornamentação de Flores Naturais',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5392bbe0-fbe7-4702-846b-c53a62cb5ab9',
    'Ornamentação de Flores Naturais + Véu',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'dd2f1e99-eec1-4352-a899-1871e103a458',
    'Ornamentação de flores naturais e artificiais',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '79e80617-ee1f-4272-9d9a-87af346fd2cf',
    'Ornamentação de Flores Naturais Luxo',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3139f413-cbd4-4652-b243-50cac229c204',
    'Ornamentação + 1 Coroa de flores artificiais',
    'ORNA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '544fdeed-bb07-4de5-9880-3171c963a65f',
    'Pacote Ambulatorial',
    'PACO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '37ca4788-15be-448d-9a0f-a3c9f3364876',
    'Padre e Limpeza de Capela',
    'PADR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '03e7e23d-341a-417e-889b-9202c1b4db9b',
    'Paramentos',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '49bdbf9e-a98b-4d80-b423-8f571142d9e0',
    'Paramentos + Castiçais',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '709ebef3-07e9-46c7-b98f-0874be9ebbe6',
    'Paramentos + Coche',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ab6957e9-f8d5-44e4-84ee-0eac1f27b1bd',
    'Paramentos + Kit Café',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7c2ea6cd-7008-4927-be1d-6a7725567b36',
    'Paramentos + ornamentação de flores naturais',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '26f31b0d-3747-4384-916e-4444753ff57a',
    'Paramentos + Remoção',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4675d3b4-fc93-4358-b67b-23fd2911960b',
    'Paramentos + velas',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5416ff49-3794-4238-8f4b-ff90196da516',
    'Parasitológico',
    'PARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '14bd6490-79a9-4c33-a748-8f9831e77ecb',
    'Passagem Aérea',
    'PASS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '71fbadf9-fcce-41d3-adf0-343593a4962c',
    'Passagem Aérea Ida',
    'PASS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2ea9f9c1-3a02-448e-a8d4-15270ff1f40b',
    'Passagem Aérea volta',
    'PASS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3f5f11b7-f2fa-4fea-90b7-14d2655e819a',
    'Passagem ida',
    'PASS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'deb5fc61-ebb8-454b-957e-3b91534219b1',
    'Pessoal Fardado para Sepultamento',
    'PESS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '82dc27d4-55f5-468c-bde2-4b70354429c8',
    'Pessoal Fardado para Velório',
    'PESS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b382acdf-9403-487e-9347-2742c794c3ad',
    'Pintura de jazigo',
    'PINT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8402ad76-5fba-4e86-979f-e832dd95e565',
    'Pintura e lavagem de túmulo',
    'PINT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e55d3846-6228-4c35-b663-dfa963887f47',
    'Plantão Psicológico',
    'PLAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '14296361-431c-4bcb-bd9b-3f0193b1c479',
    'Plaqueta de Identificação da Urna',
    'PLAQ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1e3c8fd0-a9a6-4864-8970-63e063c17d0e',
    'Plaqueta de Identificação do Sepultado',
    'PLAQ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '25aeaf90-88ab-4e79-850a-da3373e1475e',
    'Plastica',
    'PLAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9334abbc-6bbc-460f-87cc-7a0a40685bdb',
    'Polimento de Granito',
    'POLI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2b84147a-6429-4c86-a418-7f0d21c8f136',
    'Polimento de lápide',
    'POLI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '32e9c21c-2f49-44c3-bb2c-1cd20920acd6',
    'Praça de sepultamento',
    'PRAÇ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e6dc9831-bc57-4a6b-95c6-8efccf540fec',
    'Preparação do Corpo',
    'PREP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e21f3d3a-a10e-45b1-891c-3f688bdbe777',
    'Procedimento Administrativo',
    'PROC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'eb44c3ef-67ea-4489-b309-dd0c27abef87',
    'Procedimento Cirúrgico',
    'PROC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'afacdec6-28d9-46cc-8d0c-b21129e0dc3a',
    'Procedimento Médico',
    'PROC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '305cb223-0e5a-404e-88eb-cd31a1432fd1',
    'Procedimentos Hospitalares',
    'PROC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9e5d0462-0a62-4a0f-a845-66da129050c3',
    'Quadra Geral Terra',
    'QUAD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0c250b50-dd0f-4dcf-9744-ef86c6e3de0e',
    'Quadra terra',
    'QUAD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a071a20c-c73e-4624-bb70-e349df316299',
    'Raio X - Costelas',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '36e68053-8379-4bf0-a23c-72fffda678f6',
    'Raio X - Face',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '37444504-e87f-4f36-aa6f-b214e8ac6e60',
    'Raio X de Pé',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b17658c8-ae06-4f0d-bbb0-f30aeaa078b7',
    'Raio X de Tornozelo',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '509d836d-5125-43a7-a0f4-29030990e552',
    'Raio X do Ombro',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a4a1f612-ea76-439a-bc6a-59381a84d933',
    'Raio X e medicamentos',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fe37394a-3356-44e2-84e9-cdace7a27ebe',
    'Raio X da Mão',
    'RAIO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c497c76b-015c-4c88-ad51-cc819ea5fe24',
    'Reabertura de Jazigo',
    'REAB',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '30c90606-772e-49bb-9e6d-fdccc58da9f4',
    'Rebaixamento da Sepultura',
    'REBA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ce25833b-ceb5-48e0-8059-544d4e6e247f',
    'Reboque',
    'REBO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e491728d-c3cc-492d-93a4-55f58e462655',
    'Recepção de Corpo',
    'RECE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e96f1787-a8fc-47d7-9986-66f04a6a9e39',
    'Reconstituição do Corpo',
    'RECO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '18385731-ce29-4557-b89a-09080f18106c',
    'Reconstituição Facial',
    'RECO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '492626db-1d13-411c-9b52-192a77bb1909',
    'Reconstrução de jazigo',
    'RECO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4f7baa5c-a8c2-4e5b-83e3-d5591fc16f0e',
    'Reforma de Jazigo',
    'REFO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c502d247-2532-4711-8af8-7b1a104c8916',
    'Remoção de Ambulância',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0f950bad-8d8c-4109-ba21-4cffce5c8b75',
    'Remoção de Barca',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bdad1096-f618-436f-a2fa-ac809d0dc0e3',
    'Remoção de despojo',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '66836287-de80-4dce-9439-3120d84e9cc1',
    'Remoção de Terra',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '381707fc-3b79-45d8-ab65-627556f2d0f1',
    'Remoção Intermunicipal',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'de848d71-c63d-410a-90c6-3002dbb2ef89',
    'Remoção Interna',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '462e275e-991f-41ed-bc9e-01763b156b36',
    'Remoção Municipal',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f9c761dd-37c0-4c56-9980-621d6d535418',
    'Remoção para IML',
    'REMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a4796027-bba9-4dae-a318-c5c42a319689',
    'Reparo de Grama',
    'REPA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '204113bd-2f61-4abd-bee0-192f96fa4f11',
    'Reparo de Jazigo',
    'REPA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '67f3d803-32f5-459c-b6bf-8f96e1b945ce',
    'Reposição de grama',
    'REPO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd9e54e44-b3d0-4a2e-8b59-661dffd18e94',
    'Reposição de Pedra',
    'REPO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b93c3865-3739-4c59-a733-5f19dac07f67',
    'Reposição de Terra',
    'REPO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0b87e0da-7d81-4efd-9ec0-b3c5f5752aaa',
    'Ressonância Magnética (com contraste)',
    'RESS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1e2e906f-807c-44f4-b9b9-f1d421012892',
    'Ressonância Magnética (sem contraste)',
    'RESS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'de5b400b-2eb6-431c-a063-3174a4cdf1ea',
    'Restauração Facial',
    'REST',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '07a6c52f-b043-454f-aa1d-d1418af62b59',
    'Retirada de corpo',
    'RETI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f0ccd48a-9b08-4a38-9205-e48acfb5c55b',
    'Retirada De Gesso',
    'RETI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd6621c9a-2deb-4b28-8654-7f4e43fd49b6',
    'Retirada de Pontos',
    'RETI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4a39eb72-b6c5-48ce-8b34-5366dbf8d7b0',
    'Retirada do Zinco',
    'RETI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1bfcd7cd-f3d3-40ad-aaca-7bac52331eb0',
    'Revestimento de Concreto.',
    'REVE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e0c45690-8c05-4930-8074-d2fda911bbd1',
    'Revestimento do Tumulo',
    'REVE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'effe6809-86c2-4ac2-b8ff-771ef13e0c77',
    'Revestimento e Mão de Obra de Jazigo',
    'REVE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0e019389-e2de-4277-8c69-0ebf1927742b',
    'Revestimento simples de urna',
    'REVE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '13e33fa1-11af-4009-9547-0276d917cd6f',
    'Sala de Cirurgia',
    'SALA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2754aa9a-d7cd-4149-9cda-9dd1e8f910d3',
    'Sala de Conservação',
    'SALA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '272048d1-14ee-40db-9a34-8cf7142357c6',
    'Sala de Gesso',
    'SALA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd992f6ed-6dd6-40dc-a675-7f89eb20cc78',
    'Sala de Observação',
    'SALA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5f564651-0c89-4875-a126-dfaa8a8adbcd',
    'Sedex',
    'SEDE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cfc491b7-cbd8-4e82-b38b-34493fe930d6',
    'Sepultamento',
    'SEPU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f6b5cc78-da1e-4111-933f-de9ea7819722',
    'Serviço Cerimonial',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6f781d81-5ef7-4f5a-a36a-d7cf5d78bfd2',
    'Serviço de Ambulância',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e80081d9-0aae-443f-a430-ccad2661f2df',
    'Serviço de Assistência',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8afb3b67-7052-44d0-9919-3f3a0cd7af0b',
    'Serviço de Batedores',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '69b4e42a-16f7-4c7d-af95-fecd962dcbe0',
    'Serviço de Buffet',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '894cea2d-6a51-45e0-89de-d4adda4dfbab',
    'Serviço de Cerimonial',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4ba658cb-1b90-45a1-b780-2fd242552f06',
    'Serviço de Copa',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '516ce799-6bb8-427b-afcb-460412d3a591',
    'Serviço de Copa e Cozinha',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '95398015-ea6b-454c-8bbd-765cd9704eb2',
    'Serviço de despachante',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '06b61483-8fe8-4612-bea4-9d14bc464aea',
    'Serviço de Enfermagem',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b8239c20-e7ef-4b94-a1a1-5d8f9f5de87a',
    'Serviço de Entrega',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6cefde0c-50ce-44c6-8aea-e7af991f555a',
    'Serviço de Funeral Completo',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9d6e16f1-eed3-49a5-bce9-bc746afe1a5c',
    'Serviço de Providência',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1b9fd89a-ab94-4c90-8ac4-fd3342cc601f',
    'Serviço de psicóloga',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '26a7e2a8-566e-4365-b4f0-5bfd3d5bd1b4',
    'Serviço de segurança',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '91245135-e9c5-4130-a3dc-ad9a08703268',
    'Serviço de Sepultamento municipal',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b2bc8802-9fce-4c61-b29c-76ecf577c5f7',
    'Serviço de Sepultamento particular',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '062068dc-d2ad-47f8-83d4-2a0d7013fe8d',
    'Serviço de Traslado Completo',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '50a2242e-72c4-4af6-9f04-f316db27df6c',
    'Serviço Fechado',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '21284eae-51a9-4bf2-b6b1-90629b95fbb1',
    'Serviço Funebre',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c50909d4-d431-4eb8-8439-3aff890cbec5',
    'Serviço Municipal',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5705dbb3-14fb-49c8-94c7-54519ac84806',
    'Serviço Prestado',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cc3241b8-9a18-4c10-bd19-fb327ef8fa5d',
    'Serviço Social',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5b961194-a66a-4231-8d06-0eb9de0d4604',
    'Serviços Advocaticios',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ad783c6a-1de0-490b-9a29-1ac682c92191',
    'Serviços Complementares',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b0023974-7deb-4cb3-bae8-2f1b394e6321',
    'Serviços de Anúncio',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b4844cdc-23b0-4328-aa87-2a78d362e389',
    'Serviços de Luto',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '33f85f3c-c9f2-4cb1-93ec-840532d95cd7',
    'Serviços e Taxas',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fde14478-73a7-4fc3-a653-d474aa0de111',
    'Serviços Funebres',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd4e7d6d0-696c-48b6-ba8c-c146c10c5b61',
    'Serviços Funerários',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2fc52ecf-0372-41d6-ba82-12850fa88184',
    'Serviços Hospitalares',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7345aab9-b1aa-4794-b5ab-3902f4dd6ee6',
    'Serviços Religiosos',
    'SERV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e9644cc4-b395-4cc6-a486-2351ec5e7bc7',
    'Sessões de Fisioterapia',
    'SESS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c15c501a-9cb3-4ccf-9de5-a36ed4d246a0',
    'Seviço de Pedreiro',
    'SEVI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c4520b93-247c-46f0-a4b1-7f7339b2bea5',
    'Sistema de Iluminação',
    'SIST',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '26d2733f-82c6-44cb-a549-5845bf7d4f3e',
    'Solda Urna Zincada',
    'SOLD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '22d014ab-b7ab-4b94-a056-dc7f9dffb2f2',
    'Soro',
    'SORO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e7a23cf0-8d41-4499-93d4-21f728d5bb4e',
    'Sprinter',
    'SPRI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7d9f0f96-8d84-4ce4-88f6-fbee3d19179f',
    'Sutura',
    'SUTU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3b59fad6-2280-4db0-b003-a5614f9c9714',
    'Tala',
    'TALA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '58c0b611-9ed5-45fc-8741-47119193bf44',
    'Tamponamento',
    'TAMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e9df842a-4904-4dc1-9afd-b4581b7e088e',
    'Tamponamento + Aspiração',
    'TAMP',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8b219591-a095-436b-b3f5-50c3b906a8fe',
    'Tanatopraxia',
    'TANA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2015558b-1640-4231-9bd1-13cb5e128ebc',
    'Tanatopraxia + Embalsamamento',
    'TANA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8782d694-c278-43f9-b9ae-6b46b69893ff',
    'Taxi',
    'TAXI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7a890f7f-88de-4135-b64f-f4f1771db711',
    'Taxi Aereo',
    'TAXI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '078d3613-144e-482c-92fd-28c1216c9628',
    'Tenda',
    'TEND',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ed9d0a9d-c16e-4d79-b116-d844324354c6',
    'Tendas, Bancos, água e copo descartáveis',
    'TEND',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c92bdc75-4e14-41bb-be38-642fe51f0ab3',
    'Toldo',
    'TOLD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd556fb4c-78d1-4721-a723-cb9c3cfc9e8e',
    'Tomografia Computadorizada',
    'TOMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cc45962c-d723-4b89-a1be-fc071a8a0455',
    'Tomografia do Crânio',
    'TOMO',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1cb59a9a-a7ea-4a75-a1c0-f8a2bc8a0ddc',
    'Tradução Juramentada',
    'TRAD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '37d28888-0478-477c-82fc-1581e6ea5c33',
    'Transformação de Jazigo',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd0945818-3b52-4074-a59e-271eca1f6046',
    'Translado Aéreo (Valor sob confirmação)',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd8ef5a80-7bd6-4b02-a044-1645ce2ee908',
    'Translado Terrestre + Remoção',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9a9feb74-e752-46d9-bd36-a5d8ec0a1a9b',
    'Transporte de pessoas',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bb3af393-5dcc-4510-8335-2ddd87d4ace7',
    'Transporte Interno',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bc2d5cb0-91bf-440b-8053-02d35b66ccaa',
    'Transportes Diversos',
    'TRAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '316205ca-cdbe-45f8-99d1-938f2a7cb990',
    'Traslado Aéreo',
    'TRAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a4ab4749-7912-4f84-9f71-69689482ee9e',
    'Traslado Aéreo + Remoção',
    'TRAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4d507623-1f88-4080-b6f4-809cd97ea7f0',
    'Traslado Maritimo',
    'TRAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '31e57178-40b1-4994-8011-7e456c2b73e3',
    'Traslado Terrestre',
    'TRAS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '56b71e6c-ef31-4d73-bdd1-6a7baddf23c7',
    'Triglicerídeos',
    'TRIG',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '61156d64-d815-40d6-a752-54e1830ab2de',
    'Troca de Urna',
    'TROC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '20857f99-00ff-4206-aae5-bfe5a09da854',
    'Van',
    'VAN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '58d9f6bc-fa0c-4020-a966-ca7299a051ed',
    'Vedação de registro civil',
    'VEDA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '36c301ec-ca37-45b0-9483-f8915f4bf540',
    'Vedação e ferragens',
    'VEDA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '638e5b29-093c-4782-8269-fcb08397a7e7',
    'Veiculo com motorista para familia',
    'VEIC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b4859489-c739-436e-a8df-2379581c6a1b',
    'Veículo com motorista para família',
    'VEÍC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fd2ee30f-531f-4cfc-85d0-2a9c508403a7',
    'Veículo de Serviço para Voltas',
    'VEÍC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8edf6a86-eaa3-45f7-b0e1-d22f374dda1a',
    'Veículo para Cortejo',
    'VEÍC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a136f8b2-0c9b-444b-9b80-a30813d431e5',
    'Veiculo para familia',
    'VEIC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '02901aec-7a2c-4a5a-b6b4-eeab3de4f284',
    'Veículo para Remoção Municipal',
    'VEÍC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '56f1921b-b39d-4f4b-ae1f-2a4d3f24af0c',
    'Veículo para Serviço Religioso',
    'VEÍC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '250462d8-2af1-47b8-9bb1-7da3fcf283da',
    'Velório municipal',
    'VELÓ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4cd84614-1a32-4fd0-a859-6bfb0c975903',
    'Velório particular',
    'VELÓ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bee22919-ecfc-4dbe-af94-5f4ec83df425',
    'Ônibus',
    'ÔNIB',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cc9aaed2-eaaf-4eab-b437-7a61cc56695a',
    'Abertura + Exumação + placa de identificação',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '018fcf82-a433-4360-8d2d-2d89da34ff97',
    'Abertura + Fechamento + Exumação',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c0ba9dfb-3ff6-4cb9-872b-c7f9e2e7c797',
    'Abertura + Sepultamento + Lápide',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c9e48b4f-0647-428b-b9a6-5b2e1765b079',
    'Abertura da Urna Zincada',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '5503b9fe-62ee-487d-b70b-57ac1bb05e9b',
    'Abertura de cova',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6128eada-103e-4f3a-bcd6-00ca410f799d',
    'Abertura de gaveta',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '46172655-e759-4e4b-aaf8-be7bcfca4fd2',
    'Abertura de jazigo municipal',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '59550e54-118b-47c4-8064-345f9214daae',
    'Abertura de jazigo Municipal + Exumação',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4bfb259a-44fc-456e-a5c1-f2054c51fe81',
    'Abert. jazigo Muni. + Exumação + Placa de Identi.',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '16b4b9c8-b330-4f63-a5d1-beff6727dffb',
    'Abert. jazigo muni. + Taxa sepultamento Municipal',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e20e366d-821e-4e7d-8e39-f4dd3f7530a7',
    'Abertura de Jazigo particular',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '645408c4-e024-4743-869e-c6f991c98cee',
    'Abertura de jazigo particular + exumação',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '551bda4d-4f94-45e2-b613-fc096ada7634',
    'Abertura e fechamento + tampão de concreto',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '53e23b62-62fe-4684-97b9-2cfe7cca9a29',
    'Abertura e fechamento de Gaveta Municipal',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '09679e5a-f613-4f3d-ae0a-6a03e5406f18',
    'Aber. Fecha. jazigo + Taxa sepultamento particular',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1556904d-ba25-4768-a86d-aa37b5d786ce',
    'Abertura e fechamento de jazigo municipal',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a15c978b-6165-45ae-9163-b83859499e10',
    'Abert. e Fechamento de jazigo municipal + Exumação',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '948dc448-9cd6-447f-a69b-9a59c325dc52',
    'Abertura e fechamento de jazigo particular',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b5d4b442-f80d-4aa2-b508-80eed4fd806d',
    'Abertura e Fechamento Municipal',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9a629fa1-05f4-4fb1-a5d2-166c05bbd944',
    'Abertura e Fechamento Particular',
    'ABER',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '29253d89-863e-4dda-8e7b-3d326b47f96a',
    'Ambulância',
    'AMBU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '368f402f-11f2-469c-b3f4-83e04359f8d6',
    'Ambulância - UTI',
    'AMBU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8f728b39-9e68-49d0-ba38-61100cf40c0c',
    'Ambulância simples',
    'AMBU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '277f7785-73ac-445f-a2e2-a04a33a1d252',
    'Anestesia',
    'ANES',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'e5dab470-d904-43ec-b7aa-f068fc4d4273',
    'Anuidade de Jazigo',
    'ANUI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4850b949-839a-4c61-b60a-ade0181d6afe',
    'Arrendamento',
    'ARRE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ac6da168-fd43-4ce7-a0f8-b641f978a7b6',
    'Assento de grama',
    'ASSE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3e475680-ed05-4153-9a07-6c47d23ff403',
    'Assessoria',
    'ASSE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9ed55660-9a17-4b3b-bbe8-66872940733c',
    'Assistência',
    'ASSI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ee27eace-75e5-4339-b006-102d896b924d',
    'Ataviamento',
    'ATAV',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '69dbb92d-00b3-486e-8907-be99f03c066d',
    'Atendimento',
    'ATEN',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '334cc1b0-feb7-4f41-b219-60bd308ce293',
    'Balsa',
    'BALS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '87857d00-b4b6-4313-8180-ae07ba310695',
    'Barco',
    'BARC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '63b47293-eb0d-43ab-aa7b-6f9ae26d294f',
    'Bebedouro',
    'BEBE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3b5d6a47-8db0-4e9f-ad69-005670c40581',
    'Capela + Abertura',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '53881e15-b532-4289-a50d-30e0efdfd0ec',
    'Capela + Exumação',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '51f9196a-4696-424a-a748-b22fe3c53227',
    'Capela + Ornamentação',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ac13501e-a036-4dfc-9a69-7d1ec7ec3a0e',
    'Capela + Taxa de Sepultamento (particular)',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6ce936d0-af38-4447-833a-42470b851f11',
    'Capela + Taxa de Sepultamento Municipal',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '05a5da72-8cca-44db-b3b7-4b331c1dc6c0',
    'Cap. + taxa sepultamento muni. + loc. jazigo muni.',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b6755a7b-86e4-434b-b36c-b2102c8be110',
    'Capela Municipal',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '1fde1e7d-4c87-451e-84de-f97a062e0923',
    'Capela Municipal + Exumação',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7b246ab7-4dd3-4470-93cc-fea6e5450aa3',
    'Capela particular',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'bf4664a6-ed55-4371-abbc-cff100dab00f',
    'Capela Particular + Cortejo',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '96e200cc-c250-489d-91dd-3da0f14b126a',
    'Capela Particular + Exumação',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '4de5d456-f198-4ad8-898f-c3b821a846d7',
    'Capela Particular + Paramentos',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c58802cc-e19c-4d82-9af3-a35d9275c30b',
    'Capela Particular + Taxa Sepultamento Particular',
    'CAPE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '735f4cd1-588b-4dd5-bc3a-b4930b08fdda',
    'Captação de Documentos',
    'CAPT',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '371021db-b958-4efc-bc63-734d6a001f20',
    'Carro Funebre',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '39e9e34d-a3fa-4886-b0f7-544dcd1cc2b6',
    'Carro particular',
    'CARR',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'ef8f33bf-ddca-4080-8c62-2a3745abb0f0',
    'Cateter',
    'CATE',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6840dd69-c4c6-43b1-b1e1-ea668d382ec5',
    'Co',
    'Co',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'abbd509e-b23e-4138-8430-ababc03b749f',
    'Construção de Jazigo + Compra de Terreno',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'cf3ad468-8683-4caa-b27a-e71282a46771',
    'Construção de Jazigo + Mão de obra do pedreiro',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'da309d8c-838c-472d-ba79-01edcfdf1c17',
    'Consulta + Exame',
    'CONS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'afdae2cb-86aa-4d2e-84a1-3814aabc51e8',
    'Creatinina',
    'CREA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '35b44c6c-2093-4a0a-9027-e0864480d870',
    'Documentação',
    'DOCU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b88017b0-b00a-4a9d-a291-f21d083fc09b',
    'Glicemia',
    'GLIC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7b3a7598-7d4a-46a0-a89a-3919f1090a7e',
    'HDL',
    'HDL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'a7350490-a4a2-4911-a322-c48fa78cadab',
    'LDL',
    'LDL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '89871d13-031b-4dba-9f23-3ebcbbeabaf7',
    'Manicure',
    'MANI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '7017470c-06c0-45c1-b6b4-be74f82cd482',
    'Maracanã',
    'MARA',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0e5bd855-c6ae-48d8-b42b-ffcf09fda5e2',
    'Prestação de Serviço',
    'PRES',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8c29b585-9574-4781-81f0-1ecd67179fda',
    'Prestação de serviço de exumação',
    'PRES',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '0e1e6242-fbbe-43da-9e85-91a50af26cd5',
    'Prestação de serviço de sepultamento',
    'PRES',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '8751b28b-006b-4275-bfca-572a39137212',
    'Quadra Geral Terra',
    'QUAD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'fd11ec44-463f-4eb4-9158-1a602792d63e',
    'Quadra terra',
    'QUAD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'dcdced61-cf52-4417-84f3-c556c3d30bab',
    'Requerimento Judicial',
    'REQU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'f3d9c19b-af05-49f6-8800-fe1139e115d3',
    'Resíduo de restos mortais',
    'RESÍ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'c0e86ae0-e259-4257-8017-369f27463f3f',
    'Resíduos',
    'RESÍ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '3d3713c9-67ba-4373-97fb-0dc57f9c3be3',
    'Saída de Corpo',
    'SAÍD',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '6781d610-eabe-44bf-9fa4-15df050300b0',
    'Titulo de Arrendamento',
    'TITU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'dd2e80e2-6a17-40e6-a149-56d1a9841e75',
    'Titulo de perpetuidade',
    'TITU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '2d4414bf-7499-4308-99d2-1be520f956ae',
    'Uso da Sala',
    'USO ',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '9282dad6-0c8c-4dc4-933b-f17b3c093b2f',
    'VLDL',
    'VLDL',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '70fe628d-2699-4630-a578-118cb4ff940e',
    'Zinco',
    'ZINC',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '94ebf213-b8a9-4898-bac9-b5049d0266b3',
    'Visita de Analise ao Paciente',
    'VISI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '64aeb544-7d8d-448c-b100-c5fadffafb8f',
    'Emissão de Certidão de Obito',
    'EMIS',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '415bf9c6-aeb3-4d70-a7f3-ed3a69f484a4',
    'TAM-Express',
    'TAM-',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'b6bac19d-b3f6-40c1-923d-6a16bae12400',
    'Cirurgião Ortopedista',
    'CIRU',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd899487c-85f8-4b21-bd06-4bbca2599b43',
    'Equipe Cirúrgica',
    'EQUI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    '902dc824-c99f-4feb-bd2d-ae00d7fea979',
    'Auxiliar',
    'AUXI',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

INSERT INTO
  crm.composicoes (
    composicao,
    nome,
    codigo,
    tenant,
    created_by,
    updated_by,
    created_at,
    updated_at
  )
VALUES
  (
    'd9d05689-06f6-48c5-a13e-328a5c263b1d',
    'Instrumentador',
    'INST',
    _FMA,
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    '{"nome":"gisele","email":"giselecarneiro@nasajon.com.br"}',
    now(),
    now()
  );

---------------- Itens familias ----------------
INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '492c4e28-e31c-4e4a-896d-e85039e2f5d0',
    'COROA',
    'Coroa de Flores Artificiais Média',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ba500407-99b0-47d4-8eaf-becaa9f9adca',
    'COROA',
    'Coroa de Flores Artificiais Grande',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b8f8eca7-a713-4972-bb04-8f3f50a1a01c',
    'COROA',
    'Coroa de Flores Artificial',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c3e3ffd5-35ca-478a-b5e1-41534c22dc8a',
    'COROA',
    'Coroa de Flores Desidratadas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd990ab4a-ffcc-4113-ba2b-fd4e5b48e48c',
    'COROA',
    'Coroa de Flores Naturais e Artificiais',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '901c413f-1fa9-4e31-a7e3-e8fdd81c8542',
    'COROA',
    'Coroa de Flores Naturais Grande',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e64b572f-7300-4792-a04d-bf047d9ea221',
    'COROA',
    'Coroa de Flores Naturais Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e88bfa3b-a4d9-4539-b321-d33e767099a6',
    'COROA',
    'Coroa de Flores Natural',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e0794cd9-74a7-4c1b-8f3e-04094ff3602f',
    'COROA',
    'Coroa de Flores Pequena',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6ad344c2-56c1-4334-aa51-87abf10c1217',
    'COROA',
    'Coroa Grande',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c9b7c681-7a14-4c6c-af70-3822511b6cc4',
    'ORQUÍ',
    'Orquídea',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f36ba4de-2964-45f9-ae40-9dd22e1a4cf9',
    'ARRAN',
    'Arranjos de Cabeceira',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '3d499160-a502-432c-b1c8-1b92334d7003',
    'BANER',
    'Baners',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '49023460-c5d8-4a80-9d9e-8afce5d7c297',
    'BUQUE',
    'Buque de Rosas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '68c4f322-22e0-47ad-bcb3-b59e22bc5a52',
    'CAIXA',
    'Caixa de Ossos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b1e8225e-589c-4665-beca-7f10bbc3cf7c',
    'CAIXA',
    'Caixas de concreto/ gaveta',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ec1fdef8-e2f0-419e-a321-13d83dd541e5',
    'CESTA',
    'Cestas de Pétalas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4535eebd-d110-4528-af69-3326ecb81ce8',
    'CORBÉ',
    'Corbélias',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'dae21836-1b10-46df-93a3-697006424999',
    'COROA',
    'Coroa de flores Desidratadas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '444fef03-7a1d-4e8d-b21f-ca6f35acec44',
    'COROA',
    'Coroas de Flores Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '944aa522-b84a-4805-b7e3-96fea7040cef',
    'COROA',
    'Coroas de Flores Naturais',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c63a58d9-9da7-4285-be56-31287d2d917e',
    'URNAS',
    'Urnas Anjinhos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '9f0f7dbb-f89f-49f0-a1f3-af34bbf1791c',
    'URNAS',
    'Urnas Zincadas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e12a03bf-cef7-4f0f-88e7-0ef3f6732531',
    'VASOS',
    'Vasos de Flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ca70a6e7-11d9-452e-91d7-a614201cc2ae',
    'BUQUE',
    'Buquet',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'bc86a511-c692-41b8-8cd1-493293de14dd',
    'VELAS',
    'Velas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd57efd9d-7db6-4905-94ac-36ada3686d51',
    'BALDE',
    'Baldes de Pétalas de Rosas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd10d23d2-2475-4c52-ad62-fb7ca915ecef',
    'BOUQU',
    'Bouquet de Rosas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'bbac3c0f-194b-49d5-ba00-02f61328b89c',
    'ARRAN',
    'Arranjos de Flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'dc4c5a61-d103-4a4a-8004-ceca3135194c',
    'COROA',
    'Coroas de Flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'dcf6d50c-cb66-4a22-b51f-38ebfc98379c',
    'ÁGUA',
    'Água',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c624d923-4a69-4f28-b2de-21f1a488f16a',
    'AGULH',
    'Agulhas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a1f4419b-51e9-448c-a8e0-e652bc13da20',
    'ATA D',
    'Ata de Conservação',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd8acff18-7b83-4f16-b5d7-48b3b1e502e1',
    'ATA D',
    'Ata de Delegacia',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5b121e94-6a24-453a-846c-ffdd1f9f5a25',
    'ATA D',
    'Ata de embalsamamento',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '36dac8c1-0ab0-4270-a35b-ad562a2ebafc',
    'ATADU',
    'Atadura de Algodão',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6e0954f4-f44b-4200-82ab-4d27db23cf0b',
    'ATADU',
    'Atadura de Crepe',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '80b352e8-fa10-46f0-8d9c-1b2c6c5cfeb7',
    'ATADU',
    'Atadura de Gesso',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '06adf9aa-c211-42fb-a239-55350deafd86',
    'ATADU',
    'Atadura Gessada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e2b83848-842f-421a-be7b-aab56d0f29e6',
    'BANCO',
    'Banco e Floreira em Granito',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '86c0e465-e0b3-467b-84e8-7c6f7d282a3d',
    'BANDE',
    'Bandeira do São Paulo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '99f4e7ba-1958-4e80-90d9-24f3a26a0081',
    'BANDE',
    'Bandeira do Vasco',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'cac6b344-d57a-4a21-aeff-7c3aeb7659df',
    'BANNE',
    'Banner',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1a096d02-3b1f-4315-bb93-0acc0b2105f3',
    'BOTA ',
    'Bota Gessada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '056839fc-4ff8-40e6-aa09-7e6f6f85733e',
    'BOTA ',
    'Bota Ortopédica',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd266716b-4957-4f82-ad75-c267ffcec8bf',
    'BROMÉ',
    'Bromélia',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd1cc9b13-b621-4055-93a6-eb667f35291d',
    'BUQUE',
    'Buque de Flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f49b6ee2-965c-40f0-aa8b-0ccd7bd1aab5',
    'CACO ',
    'Caco para piso',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a26dedd8-d5df-4486-8241-dad3dde4c70f',
    'CAIXA',
    'Caixa de Cinza',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6156c61d-61b6-4bdf-8f87-61d3b8915b5c',
    'CAIXA',
    'Caixa de Pétalas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '0a5ad0d7-c96b-45b9-9ae5-91ab97e49783',
    'CAIXA',
    'Caixa Externa',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5569925e-9eaa-4a47-8562-4f5408610699',
    'CAIXI',
    'Caixinha - Capela',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '028c2479-fcec-4e88-ade8-505cd77ac634',
    'CAIXI',
    'Caixinha cemitério Rio',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '95a80197-b2b3-48b8-a203-0cb63babf8d0',
    'CAIXI',
    'Caixinha de Cinzas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a6ca3fa2-d880-4e0b-8f9e-8a4d0d6d4a5c',
    'CAIXI',
    'Caixinha HCE',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ffb3620a-b1a1-4c1c-af7a-f8ec3d177bb5',
    'CAIXI',
    'Caixinha HFAG',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '8b3dbfa0-a192-4040-93b0-5292b51b067a',
    'CAIXO',
    'Caixote madeira',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e32b88f5-5018-405e-9c36-b9514153000a',
    'CANTO',
    'Cantoneira de inox',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'aa91cb33-c03e-4983-97fb-480fcebf77c3',
    'CAPSU',
    'Capsula de Identificação',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '50acc6bc-f72c-4c35-937a-8a9ebd9ee00d',
    'CERTI',
    'Certidão de Óbito',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '9f63af8c-3e9e-402e-bd81-a5f09335c0f5',
    'CESTA',
    'Cesta Básica',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a07314da-ef4e-4a69-a3b9-2da2f13535c0',
    'CESTA',
    'Cesta de Bombons',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '44772236-850e-42e2-9992-fd2b958442da',
    'CESTA',
    'Cesta de flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6f3f98cc-87b5-4d52-8a44-2ea4d15c7765',
    'CESTA',
    'Cesta de Petalás',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '9e5261a4-abb1-4fbd-9e06-e148307ebc62',
    'CESTA',
    'Cesta Natalidade (Bebê)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5839d2dd-503d-4970-bd09-5779b8e17836',
    'CESTA',
    'Cesta Natalidade (Mãe)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'beeb1064-4c4e-4ac1-885d-27300a5f3f69',
    'CONTR',
    'Contribuição para Frei',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b0f181db-6d9e-4cc1-b305-4dde4b032788',
    'CONVI',
    'Convites de Rádio',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b370d7be-0bfa-4420-838c-6c49cbb833c8',
    'CONVI',
    'Convites de Velório',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f13c05dd-5306-409f-84a2-a8fc36d60ed9',
    'CORBÉ',
    'Corbélia',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6e9c0813-11b1-4b33-878b-8f2a80b8673c',
    'COROA',
    'Coroa de flores de Rosas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '19a9c0ea-ad16-4b4e-a6ec-09c2d603ed32',
    'CURAT',
    'Curativo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '464c97e4-40f1-4a1d-a92a-40a53656976d',
    'DESCA',
    'Descartáveis',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '72dac33c-1c2b-4eaf-bcb9-57a6279e2ca5',
    'EDRED',
    'Edredom',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '76d88ea4-dd1f-42fe-b270-bc838858fb92',
    'ETIQU',
    'Etiqueta de identificação',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd2ee4ebe-7909-4c12-87d6-fcb7d9ee0203',
    'ETIQU',
    'Etiqueta de unidade',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'cadef2c9-684f-4632-8f02-6a3fba60fb14',
    'FAIXA',
    'Faixa da Coroa de flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '71c83e6c-6ffd-4000-a412-04dabf2624c1',
    'FERRA',
    'Ferragem Para Sepultura',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7f2b1aca-ec48-4cdf-af8c-235bc88ac83e',
    'FILTR',
    'Filtro de Gases',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '36f524c7-137e-4a6e-961a-d6af27d68f16',
    'FORRO',
    'Forro de Plástico',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ee8ab8dc-68ef-4332-b07a-de3dec3e57fd',
    'FOTO ',
    'Foto Colorida de Porcelana',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7d0f8b19-6c91-452e-bae7-9bde93b26215',
    'FOTO ',
    'Foto da Lapide',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b6d324aa-994c-4dd2-8c27-012ac34f6407',
    'FOTOS',
    'Fotos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a8ef5ff3-43f0-4bae-abd4-fb1105754bfa',
    'FRETE',
    'Frete Aéreo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'bbb59a94-3494-42f8-8113-ff5d49d61daf',
    'FUNDO',
    'Fundo Impermeável',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '54720379-1093-4704-ad02-7c3ad3597685',
    'GAVET',
    'Gaveta',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '35b74945-ceb3-45fe-9afd-25466680a8ae',
    'GAZE',
    'Gaze',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '0f146d06-f450-4bb0-be1e-123b90e8d4de',
    'GESSO',
    'Gesso',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c0cb4d77-18c4-404a-bcfc-e6b72c6a7a5f',
    'GRANI',
    'Granito para revestimento',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a3f86eed-42ad-4356-973a-4fa336162945',
    'INSEN',
    'Insenso',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6a686985-b194-4f7a-818a-bb325da38815',
    'INVOL',
    'Invol',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '92634f72-1153-4d1c-b4de-d1af66f674f0',
    'INVÓL',
    'Invólucro',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6990ebdf-1085-472c-89cf-04dae6699fc4',
    'JAZIG',
    'Jazigo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f34cdd5b-06d8-4ee0-a306-1deb561b3d65',
    'JAZIG',
    'Jazigo - Gaveta',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'fcacabaf-8d87-4c30-8f44-ea9e31aa8677',
    'KIT (',
    'Kit (arranjo de flores e bombons)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '08d76921-0c7f-4bcf-ac9b-62fec6058a41',
    'KIT (',
    'Kit (cesta de flores + cx de bombom + Água + Café)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6fae4b76-a5c3-4da6-bf8f-315da6dfba71',
    'KIT C',
    'Kit Café',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6e81a079-b5b1-418d-9f87-6befefc1f94c',
    'KIT F',
    'Kit Floricultura',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '8d23aae2-7744-4808-bda6-b4d51e3f3269',
    'LACRE',
    'Lacre da Urna',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '237de5f1-ab03-4099-bcea-2d356447c0d5',
    'LACRE',
    'Lacre de identificação',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'dc96667c-7999-48ff-98ba-c891e6c1f602',
    'LACRE',
    'Lacre do Jazigo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'deb03de7-9999-4da3-826e-9dfdcfbab2e3',
    'LACRE',
    'Lacre no Zinco',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '2d1ecc5f-6837-4fad-a95c-90886265bb78',
    'LÁPID',
    'Lápide',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'af030229-54fa-4c67-b7b9-2c40dc242d2a',
    'LÁPID',
    'Lápide + Taxa de sepultamento Particular',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '65e21fe6-1f5d-46f2-bbf5-30d4fa52d1f8',
    'LÁPID',
    'Lápide, Foto colorida em Porcelana e Letras',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '97a51496-6656-49a1-aac6-26594e023739',
    'LÁPID',
    'Lápide, Foto colorida em Porcelana, Letras e Vasos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f684fc6d-c9c6-4d81-a292-6bacdd87b984',
    'LAUDO',
    'Laudo Cadavérico',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f041f114-9544-4242-8dfb-12ff8d50a3ac',
    'LEMBR',
    'Lembrancinhas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'cbe78e87-0d7e-4a53-b63f-25a79a39522e',
    'LENÇO',
    'Lenço',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'cb1850cd-fce9-48a7-b750-f6a571787707',
    'LEQUE',
    'Leque de Rosas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f74d50b5-8edd-4dfe-8f7e-b486cb990105',
    'LETRE',
    'Letreiro',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1ab6775c-ddc8-460c-bdbb-4226a5d04ae8',
    'LIVRO',
    'Livro de Presença',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e7261c25-94c1-476d-bd06-81c39d84d121',
    'LIVRO',
    'Livro de presença',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '84bed3fe-9487-4275-99bb-12550331453f',
    'LOUSA',
    'Lousa',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c69c4f6a-faf8-4436-94d0-5db74d972713',
    'LUVAS',
    'Luvas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '98377f59-f571-4b5b-974d-7e5bc93513c5',
    'MALHA',
    'Malha Tubular',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7390d89e-c39f-4e13-87ea-ababf92084a3',
    'MANTI',
    'Mantilha',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '262be207-5113-48f5-afcb-c6880755c65e',
    'MANTO',
    'Manto + Véu',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f9d701cc-cdd0-4e28-b663-c4ea605fc468',
    'MANTO',
    'Manto de Cetim',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '3872c44e-e521-42f5-8aad-7fcf719bf70b',
    'MANTO',
    'Manto Protetor (Invol)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '60878bda-c085-432b-a86f-f23a7dfa2fa6',
    'MANTO',
    'Manto Real',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ecc9d60d-0cdb-42a7-9cae-4aedc3a84f1a',
    'MATER',
    'Material de Construção',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c3967e4a-8d42-4ee8-b59b-5293f3c0a14d',
    'MATER',
    'Material para embalsamamento',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7bc73773-9277-4881-8c1e-fa2ad58da0a5',
    'MATER',
    'Material para Formolização',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '705b194d-b39c-4138-b671-9a4f733ae6cb',
    'MEDAL',
    'Medalhão de Bronze',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '64b494d7-1d73-4091-882d-0f648959533a',
    'MEDIC',
    'Medicamentos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '36fcfb19-6a8e-4b9b-99a1-b075d0448b8c',
    'MESA ',
    'Mesa de Condolência',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7700375f-d47f-4a61-8613-f8b016bba2e0',
    'MORTA',
    'Mortalha',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5d16fb87-fff9-4d68-848b-a7029111457f',
    'PANFL',
    'Panfletos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '2bdbb607-04ef-40a3-af62-fa70fcde0c1e',
    'PEDRA',
    'Pedra de Granito',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e726f221-002a-4811-b5a1-4af46b4a9d11',
    'PETÁL',
    'Petálas de Rosas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'bb73283c-bfd3-473a-849c-61836bee1d50',
    'PLACA',
    'Placa de Granito',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '469fa683-5ac2-4879-89ea-4c68b43dacc5',
    'PLACA',
    'Placa de identificação',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b2b3a2c4-48c5-41f7-9dd0-b7975c26c1da',
    'PLACA',
    'Placa de Identificação de Jazigo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e3f11fb0-dcd6-4991-89b6-864161c69fb0',
    'PLACA',
    'Placa de Identificação do Sepultado',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '0e872d92-c510-4d31-a9b1-495effc069e2',
    'PLACA',
    'Placa Nominal',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '919d6e33-63e3-492b-8548-665c6ca05291',
    'POTE ',
    'Pote + Saco para Cinzas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7aea8ee8-bec7-464b-9ec9-63749621a4d7',
    'QUADR',
    'Quadro de lembranças',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5ae898f0-6d15-455c-b982-1d367840c805',
    'RAMAL',
    'Ramalhete',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '0411f15d-1522-4340-b2e9-10ad4ba38252',
    'RENDÃ',
    'Rendão',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '57d92ff0-49a2-4116-83a6-ee462e8f74ec',
    'ROSÁR',
    'Rosário',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '97de1074-c1fc-4961-812b-630b3a7a5a79',
    'SACO ',
    'Saco de Cal',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'dbbbd47d-1ea9-4a75-bb52-4c882c6d7b32',
    'SACO ',
    'Saco de Ossos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '36a2d660-191e-4b4b-bf3c-2303484b13a0',
    'SACO ',
    'Saco de Transporte de Avião',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a0d62124-56ea-4e78-9f71-17966e99c1f1',
    'SACO ',
    'Saco Ezolativo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c8634d44-985e-4071-a955-5c7a2d819a6c',
    'SACO ',
    'Saco para lacre',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'fa1634e1-2f18-48e0-bd7e-9edbec6a673a',
    'SANTI',
    'Santinhos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b7b70728-86dd-4a0f-93df-4c5a8c5194c0',
    'SEIVA',
    'Seiva (aroma)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'abb020c8-423a-4e6a-a10c-3628b07a2a22',
    'SERIN',
    'Seringa',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e1adbb53-501c-4c12-9676-f1a942aa2121',
    'SUPOR',
    'Suporte de lápide',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c203fd05-ac59-4046-a066-a85b518512af',
    'TAMPA',
    'Tampa de Granito',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5dc4c795-eb95-4ec3-a18d-7b336f23d957',
    'TAMPA',
    'Tampa de Jazigo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4a34c493-08f1-4664-8df9-d77c02dac974',
    'TAPET',
    'Tapete Funeral',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6310856d-904d-48df-864a-2082ccbfa6f0',
    'TERÇO',
    'Terço',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7472027a-3efd-4188-a8ae-a045203a5f09',
    'TERNO',
    'Terno',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5d5e7bc0-5f83-43db-919a-3dc2d15e36b6',
    'TERRE',
    'Terreno',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd3a5e03c-b072-4fca-8d35-0f4b79b74005',
    'TRAVE',
    'Travesseiro',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ed8aba90-53dc-4faa-a48a-eb37a82211a5',
    'TULE',
    'Tule',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '379f93bc-7aae-4bbb-96d8-e26645162084',
    'ULTRA',
    'Ultrassom de Abdome',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '41c321a7-08b7-4d5b-9490-bcd8b5f350bb',
    'ULTRA',
    'Ultrassonografia',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '51680bc7-dd9e-4b1b-81bc-cfcb3167271b',
    'UREIA',
    'Ureia',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '69be641b-cc5d-43a8-a863-8de85278dbc8',
    'URINA',
    'Urina 1',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c4ace5a0-ce84-48ea-b7f0-d1c089d03c67',
    'URNA ',
    'Urna (larga, cumprida e zincada)',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'bdcc4bad-e96e-4e9a-b4f1-c6eff2d43dc0',
    'URNA ',
    'Urna Americana',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '33f77876-9f3c-47dc-8ce7-8fbea0a05a6d',
    'URNA ',
    'Urna Anjinho',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '86bac70e-a3ca-4ba2-9e16-df19ca3cb4ef',
    'URNA ',
    'Urna Anjinho Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd5d5904f-e40d-4617-9467-8fefdefaa10c',
    'URNA ',
    'Urna Anjinho Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '3527299f-54ae-49a9-9512-e9cc5b1f06a9',
    'URNA ',
    'Urna Baleia',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '56490307-8dd1-437c-883b-fdd172238ab6',
    'URNA ',
    'Urna Baleia Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '49446945-0e83-40f5-91bc-1006baf5a4d7',
    'URNA ',
    'Urna Baleia Zincada Internacional',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1a21467e-fdda-4a77-9566-4a87d26748ae',
    'URNA ',
    'Urna Baronesa',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4eafa047-76da-48be-9bc0-f4f0769fbadd',
    'URNA ',
    'Urna Bíblica',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6fc9c074-9b49-4d6b-b720-c19febb0dcd4',
    'URNA ',
    'Urna Bignoto',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6e6937a1-db30-4ab5-b68b-cc81d067d755',
    'URNA ',
    'Urna Bresciane',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1900ec07-feaa-4177-81ca-2fad09fcecc1',
    'URNA ',
    'Urna Busquet',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4be25847-1ec0-40ed-9756-8a8b9698b6e1',
    'URNA ',
    'Urna Carioca',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f282bed8-dd18-4c28-bd4f-e98864c79965',
    'URNA ',
    'Urna Comprida',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'a7f650d4-3046-4154-a473-ae0e1fba1218',
    'URNA ',
    'Urna comprida Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ffff5885-92f6-4c00-81af-c0274fd32b5c',
    'URNA ',
    'Urna Criança',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f60dabe7-8b0d-4ea5-9aed-f3b02cc72074',
    'URNA ',
    'Urna Criança Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b2319c4c-f035-4435-8a5b-ab5078bf2b3c',
    'URNA ',
    'Urna Cruzeirinho',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e6e353b4-8b8a-443d-99b4-1f514d3c8967',
    'URNA ',
    'Urna de Cinzas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '3ee6f76c-8290-4418-bcab-46172406dad1',
    'URNA ',
    'Urna de Cremação',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '37af6228-0e50-47b5-aa08-7c0ba30beebf',
    'URNA ',
    'Urna de Ossos',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7d284dba-0b8e-4a5d-9006-dffbe6ca479c',
    'URNA ',
    'Urna de Zinco para restos mortais',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'e5e7dc7f-2322-491e-85f0-6eb4624d2d70',
    'URNA ',
    'Urna Destavada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd3fce8bb-bd5d-4a62-aa80-04bad335d2f5',
    'URNA ',
    'Urna duas tampas',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b8308edb-ff5d-4fa1-80e3-faf53b70e367',
    'URNA ',
    'Urna Ecológica',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '466a15a1-bfe4-4e7b-8561-50a565c294b9',
    'URNA ',
    'Urna Extra Larga',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b8262bc7-5498-4634-9ce1-feaa50b83f51',
    'URNA ',
    'Urna Fibrada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd3dd3e67-8d79-4983-b3c9-7f6beac99c77',
    'URNA ',
    'Urna Gorda',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '05022bdd-1ce2-4cfe-8b59-00c93e324e59',
    'URNA ',
    'Urna Gorda e Alta',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd5d397c7-54a3-4a36-b22a-25399bc12eb8',
    'URNA ',
    'Urna Gorda e Comprida',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '9734dfd3-95e1-40e7-85e6-15680a0a3f71',
    'URNA ',
    'Urna Gorda e Comprida Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4ec5482a-1a95-4018-aba2-4b68f3c962c9',
    'URNA ',
    'Urna Gorda Especial',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '3b4021ab-42f7-405a-9952-2933e25ea7bc',
    'URNA ',
    'Urna Gorda Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '3b7781f6-4f85-4bbc-9800-5e1666c1af4a',
    'URNA ',
    'Urna Gorda Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7238c222-1ad1-4e97-8fa7-3bc8ebcac99b',
    'URNA ',
    'Urna Gorda Zincada Internacional',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'fbc92e65-3067-41ba-a79d-0bef39036f7a',
    'URNA ',
    'Urna Infantil',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '568808df-2d46-4a47-b149-2d53a09526fa',
    'URNA ',
    'Urna Lacrada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '168c5e02-ebd0-428b-86d9-f8ec2e87bff6',
    'URNA ',
    'Urna Laqueada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'ec5f583e-c843-4940-8b57-8f01677f70ef',
    'URNA ',
    'Urna Larga',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '262b480b-4122-4269-a86b-a060b034387a',
    'URNA ',
    'Urna Larga Comprida e Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '73d07315-bfda-4814-87c5-216abb89c9ab',
    'URNA ',
    'Urna Larga e Comprida',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '627c1fb3-90ba-43e1-a241-92f443540926',
    'URNA ',
    'Urna Larga Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '2decf4c6-c27e-4556-9095-4f9c2e84ffcd',
    'URNA ',
    'Urna Larga Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5c974e84-a8dc-4806-99c1-e9ca8216476b',
    'URNA ',
    'Urna Leomar Ref. Napoli',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f746d5d8-c16f-448f-9178-836446a6afd6',
    'URNA ',
    'Urna Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b43466ca-69ae-480f-9470-ad5f30557cd9',
    'URNA ',
    'Urna Luxo Especial',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '62d86782-1fe0-468d-b40b-023c0a40b0fd',
    'URNA ',
    'Urna luxo larga',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '9b81bbab-056a-4345-a804-952f7aaad357',
    'URNA ',
    'Urna Redonda',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'b54d0e05-08ba-480b-b564-d0d728fe7e2d',
    'URNA ',
    'Urna Requiem',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '73429974-90b0-4159-a877-35665c4a8126',
    'URNA ',
    'Urna Semi-luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c9fa548b-f0c1-4990-a200-4de09aa3b168',
    'URNA ',
    'Urna Semi-luxo Larga',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1c31e56d-3117-48d7-aa7a-1b7d26fe09b5',
    'URNA ',
    'Urna Super Gorda',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5f0e80d3-4378-48e7-a946-3b332c7540e1',
    'URNA ',
    'Urna Super Gorda Zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1fa4b396-202d-4b46-81bc-d653f0e06da6',
    'URNA ',
    'Urna Super Larga',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c3f1c514-72b2-4b9b-8ed2-49dbf496d661',
    'URNA ',
    'Urna Super Luxo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c8539793-75fb-4479-9ab2-84bfa725de3c',
    'URNA ',
    'Urna Tanabi',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4ca834c7-5eb8-4dd0-ace6-7a91bcabfea1',
    'URNA ',
    'Urna zincada',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '7efbe778-03e7-4823-9e10-5d3ea86be91e',
    'URNA ',
    'Urna Zincada Comprida',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '643f508e-1979-4678-9e23-87d378364634',
    'URNA ',
    'Urna Zincada Criança',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '4963908d-5d2c-407c-b216-07f08bf41fff',
    'URNA ',
    'Urna Zincada Gorda',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'c57e8544-49ee-4f2a-a5ef-4522c4e7c294',
    'URNA ',
    'Urna Zincada Gorda e Comprida',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '468c023b-631f-4abd-b67f-16741fe6a3d6',
    'URNA ',
    'Urna Zincada Internacional',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6ec724b2-003b-4c52-991e-0319a9b6181e',
    'VASO ',
    'Vaso de Bronze',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '43234cc3-629a-4a13-beb4-ac83b27a5274',
    'VASO ',
    'Vaso de Flores',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'd8a82b5d-4791-4437-8275-cca830da3314',
    'VELAS',
    'Velas e Livro de Presença',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '6d239845-51fc-44a0-9621-eb550d181145',
    'VESTI',
    'Vestimenta do corpo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '1de62ca5-775e-4059-bb77-64cf14d42bd8',
    'CALAF',
    'Calafeto',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '366067da-c02d-4a10-9784-7c25cb16f0a6',
    'CARNE',
    'Carneira',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '08764043-6706-492b-87ca-f097ff96d905',
    'CARTÃ',
    'Cartão de obituário',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5d521afa-d884-44dd-8c5c-3009b7876cf7',
    'CARTI',
    'Cartilho',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '82f9da99-5d07-44a6-af27-065720016fb1',
    'CORDÃ',
    'Cordão de São Francisco',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '5a8aa921-eba2-456a-b53e-c365f2cfa743',
    'CRIST',
    'Cristo',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    '363cc3ee-a5f3-47cb-98e7-615a045b4044',
    'CRUZ',
    'Cruz',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'adf826b1-b0c0-446c-a482-d0a6bbfe65e0',
    'LAGE',
    'Lage',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f092a33c-9828-4f08-a2cb-cab1b4209347',
    'LOUSA',
    'Lousa',
    _FMA,
    '800.00'
  );

INSERT INTO
  estoque.familias (familia, codigo, descricao, tenant, valor)
VALUES
  (
    'f3327edd-3463-4d9f-91ab-dc0842b5501c',
    'MARCA',
    'Marcador de Texto',
    _FMA,
    '800.00'
  );

---------------- Negócios ----------------
INSERT INTO
  ns.sequenciasnumericas (tenant, nomesequencia, proximonumero)
values
  (_FMA, 'propostanumero', 1);

-- numero utilizado na proposta
END $$;