DO $$
  DECLARE  
    _TENANT integer := 47;  
    _TENANTCODIGO character varying := 'gednasajon';
    _ESTABELECIMENTO_UUID uuid := '39836516-7240-4fe5-847b-d5ee0f57252d';


    -- ns.configuracao - COM ESTABELECIMENTO
    _nsconfiguracaoestab varchar [];
    _NSCONFIGURACOESESTAB varchar[] := array[
      ['0', 'true', '2', '1', 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', _TENANT::varchar],
      ['0', 'true', '2', '1', '39836516-7240-4fe5-847b-d5ee0f57252d', _TENANT::varchar]
    ];

    -- ns
    _tipologradouro varchar [];
    _TIPOSLOGRADOUROS varchar[] := array[
        ['RPE', 'Rua de Pedestre', 'RPE', '2019-01-07 11:46:48.813223'],
        ['RPR', 'Margem', 'RPR', '2019-01-07 11:46:48.813223'],
        ['RTN', 'Retorno', 'RTN', '2019-01-07 11:46:48.813223'],
        ['RTT', 'Rotatória', 'RTT', '2019-01-07 11:46:48.813223'],
        ['SEG', 'Segunda Avenida', 'SEG', '2019-01-07 11:46:48.813223'],
        ['R', 'Rua', 'R', '2019-01-07 11:46:48.813223'],
        ['AV', 'Avenida', 'AV', '2020-09-07 11:46:48.813223']
    ];

    _pais varchar [];
    _PAISES varchar[] := array[
        ['1504', NULL, '2009-01-01', '2018-05-31', 'GUERNSEY, ILHA DO CANAL (INCLUI ALDERNEY E SARK)', '80', '109', NULL, '2019-01-07 11:46:48.813223'],
        ['2003', NULL, '2017-01-01', NULL, 'CURACAO', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['0153', NULL, '2017-01-01', NULL, 'ALAND, ILHAS', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['0420', NULL, '2017-01-01', NULL, 'ANTARTICA', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['0990', NULL, '2017-01-01', NULL, 'BONAIRE', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['1023', NULL, '2017-01-01', NULL, 'BOUVET, ILHA', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['2925', NULL, '2017-01-01', NULL, 'ILHAS GEORGIA DO SUL E SANDWICH DO SUL', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['3212', NULL, '2017-01-01', NULL, 'GUERNSEY, ILHA DO CANAL (INCLUI ALDERNEY E SARK)', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['3433', NULL, '2017-01-01', NULL, 'ILHA HEARD E ILHAS MCDONALD', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['3930', NULL, '2017-01-01', NULL, 'JERSEY, ILHA DO CANAL', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['4898', NULL, '2017-01-01', NULL, 'MAYOTTE (ILHAS FRANCESAS)', NULL, NULL, NULL, '2019-01-07 11:46:48.813223'],
        ['0973', '022', '2009-01-01', NULL, 'BOLIVIA', '22', '22', '097', '2019-01-07 11:46:48.813223'],
        ['0981', '134', '2009-01-01', NULL, 'BOSNIA-HERZEGOVINA (REPUBLICA DA)', '51', '134', '098', '2019-01-07 11:46:48.813223'],
        ['1015', '179', '2009-01-01', NULL, 'BOTSUANA', '70', '179', '101', '2019-01-07 11:46:48.813223'],
        ['1058', '010', '2009-01-01', NULL, 'BRASIL', '10', '10', '105', '2019-01-07 11:46:48.813223'],
        ['6939', NULL, '2017-01-01', NULL, 'SAO BARTOLOMEU', NULL, NULL, NULL, '2019-01-07 11:46:48.813223']
    ];

    _municipio varchar [];
    _MUNICIPIOS varchar[] := array[
        ['3304409', '00005887', '00007040', 'RJ', NULL, 'Rio Claro', NULL, 'false', '1', '3458b9ab-7d48-4e2f-8104-4b5449bea9dd', '2019-06-27 17:23:17.429485'],
        ['3304508', '00005889', '00007041', 'RJ', NULL, 'Rio das Flores', NULL, 'false', '1', '26ebed07-31f8-40a6-baa3-710a9346f823', '2019-06-27 17:23:17.429485'],
        ['3304524', '00002921', '00007042', 'RJ', NULL, 'Rio das Ostras', NULL, 'false', '1', '22de2164-cf36-460a-882a-e7fb40bb7381', '2019-06-27 17:23:17.429485'],
        ['3304557', '00006001', '00007043', 'RJ', '21', 'Rio de Janeiro', NULL, 'false', '1', '6281bf3b-5937-42b4-8d28-4148808555f5', '2019-06-27 17:23:17.429485']
    ];

  BEGIN 

    /* Base: tenant, grupoempresarial, empresa e estabelecimento */ 
    IF NOT EXISTS (SELECT codigo FROM ns.tenants WHERE tenant = _TENANT ) THEN
      INSERT INTO ns.tenants (codigo, tenant) VALUES (_TENANTCODIGO, _TENANT);
    ELSE
      update ns.tenants set codigo ='gednasajon' where tenant = _TENANT;
    END IF;

    IF NOT EXISTS (SELECT * FROM ns.gruposempresariais WHERE tenant = _TENANT AND ( codigo='Nasajon' OR grupoempresarial='95cd450c-30c5-4172-af2b-cdece39073bf')) THEN
      insert into ns.gruposempresariais (grupoempresarial, codigo, descricao, tenant) values ('95cd450c-30c5-4172-af2b-cdece39073bf','Nasajon', 'Nasajon Sistemas LTDA', _TENANT);
    END IF;

    IF NOT EXISTS (SELECT codigo FROM ns.empresas WHERE tenant = _TENANT AND (codigo='Nasajon Sistemas LTDA' OR empresa='431bc005-9894-4c86-9dcd-7d1da9e2d006')) THEN
      insert into ns.empresas (empresa, codigo, raizcnpj, ordemcnpj, razaosocial, tenant, grupoempresarial) values ('431bc005-9894-4c86-9dcd-7d1da9e2d006','Nasajon Sistemas LTDA', '33856147', '000137', 'FUNERARIA MARACANA', _TENANT, '95cd450c-30c5-4172-af2b-cdece39073bf');
    END IF;

    IF NOT EXISTS (SELECT codigo FROM ns.estabelecimentos WHERE tenant = _TENANT AND (codigo='nasajonrj' OR estabelecimento='b7ba5398-845d-4175-9b5b-96ddcb5fed0f')) THEN
      insert into ns.estabelecimentos (estabelecimento, codigo, raizcnpj, ordemcnpj, tenant, empresa, nomefantasia) values ('b7ba5398-845d-4175-9b5b-96ddcb5fed0f', 'Nasajon RJ', '27915735', '000100',  _TENANT,'ab93da91-e98a-4e7c-acc7-d89d8303b98f', 'Nasajon RJ');
    END IF;
    IF NOT EXISTS (SELECT codigo FROM ns.estabelecimentos WHERE tenant = _TENANT AND (codigo='nasajonsp' OR estabelecimento='fd10805a-567b-4bd2-a99f-f8916032c001')) THEN
      insert into ns.estabelecimentos (estabelecimento, codigo, raizcnpj, ordemcnpj, tenant, empresa, nomefantasia) values ('fd10805a-567b-4bd2-a99f-f8916032c001', 'Nasajon SP', '27915735', '000525',  _TENANT,'ab93da91-e98a-4e7c-acc7-d89d8303b98f', 'Nasajon SP');
    END IF;
    IF NOT EXISTS (SELECT codigo FROM ns.estabelecimentos WHERE tenant = _TENANT AND (codigo='nasajonsp' OR estabelecimento='39836516-7240-4fe5-847b-d5ee0f57252d')) THEN
      insert into ns.estabelecimentos (estabelecimento, codigo, raizcnpj, ordemcnpj, tenant, empresa, nomefantasia) values ('39836516-7240-4fe5-847b-d5ee0f57252d', 'gednasajon', '27915735', '000525',  _TENANT,'431bc005-9894-4c86-9dcd-7d1da9e2d006', 'Nasajon Sistemas LTDA');
    END IF;
  

    -- ns.configuracoes - COM ESTABELECIMENTO
    FOREACH _nsconfiguracaoestab SLICE 1 IN ARRAY _NSCONFIGURACOESESTAB
    LOOP
        IF NOT EXISTS (SELECT campo FROM ns.configuracoes WHERE campo = _nsconfiguracaoestab[1]::int AND valor = _nsconfiguracaoestab[2]::text AND grupo = _nsconfiguracaoestab[3]::int AND aplicacao = _nsconfiguracaoestab[4]::int AND estabelecimento = _nsconfiguracaoestab[5]::uuid AND tenant = _nsconfiguracaoestab[6]::bigint) THEN
            insert into ns.configuracoes (campo, valor, grupo, aplicacao, estabelecimento, tenant) values
            (_nsconfiguracaoestab[1]::int, _nsconfiguracaoestab[2]::text, _nsconfiguracaoestab[3]::int, _nsconfiguracaoestab[4]::int, _nsconfiguracaoestab[5]::uuid, _nsconfiguracaoestab[6]::bigint);
        END IF;
    END LOOP;

    -- ns
    FOREACH _tipologradouro SLICE 1 IN ARRAY _TIPOSLOGRADOUROS
    LOOP
        IF NOT EXISTS (SELECT tipologradouro FROM ns.tiposlogradouros WHERE tipologradouro = _tipologradouro[1] ) THEN
            insert into ns.tiposlogradouros (tipologradouro, descricao, tipologradouroesocial, lastupdate) values (
                _tipologradouro[1], _tipologradouro[2], _tipologradouro[3], _tipologradouro[4]::timestamp);
        END IF;
    END LOOP;

    FOREACH _pais SLICE 1 IN ARRAY _PAISES
    LOOP
        IF NOT EXISTS (SELECT pais FROM ns.paises WHERE pais = _pais[1] ) THEN
            insert into ns.paises (pais, codigonacionalidadesirett, dataini, datafim, nome, codigorais, nacionalidadecnis, codigoesocial, lastupdate) values
              (_pais[1], _pais[2], _pais[3]::date, _pais[4]::date, _pais[5], _pais[6], _pais[7], _pais[8], _pais[9]::timestamp);
        END IF;
    END LOOP;

    FOREACH _municipio SLICE 1 IN ARRAY _MUNICIPIOS
    LOOP
        IF NOT EXISTS (SELECT ibge FROM ns.municipios WHERE ibge = _municipio[1] ) THEN
            insert into ns.municipios (ibge, federal, estadual, uf, ddd, nome, aliquotaiss, zfm, versao, id, lastupdate) values
              (_municipio[1], _municipio[2], _municipio[3], _municipio[4], _municipio[5], _municipio[6], _municipio[7]::numeric, _municipio[8]::boolean, _municipio[9]::bigint,_municipio[10]::uuid, _municipio[11]::timestamp);
        END IF;
    END LOOP;


    /* CARGOS E NIVEIS */

    IF NOT EXISTS (SELECT cargo FROM persona.cargos WHERE cargo = 'ae09d570-d910-457f-8ecf-4fe6204f61bd') THEN
      INSERT INTO persona.cargos (cargo, codigo,  nome, estabelecimento, tenant, lastupdate ) 
      VALUES ('ae09d570-d910-457f-8ecf-4fe6204f61bd', '1111',  'Programador', 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', _TENANT, now());
    END IF;
    IF NOT EXISTS (SELECT nivelcargo FROM persona.niveiscargos WHERE nivelcargo = '932fc1cd-16b3-494e-9701-3dbb8d1e73b7') THEN
      INSERT INTO persona.niveiscargos (nivelcargo, codigo,  cargo, tenant, lastupdate ) 
      VALUES ('932fc1cd-16b3-494e-9701-3dbb8d1e73b7', 'SR',  'ae09d570-d910-457f-8ecf-4fe6204f61bd', _TENANT, now());
    END IF;

    /* CARGOS E NIVEIS FIM */

    -- criar mais um colaborador

    INSERT INTO persona.trabalhadores (codigo, nome, identificacaonasajon, datanascimento, nis, tipo, dataadmissao, empresa, estabelecimento, departamento, lotacao, nivelcargo, tenant)
    VALUES ('000099', 'Thiago Moraes', 'thiagomoraes@nasajon.com.br', '1980-01-11', '13328957609', 1, '2020-01-01', '431bc005-9894-4c86-9dcd-7d1da9e2d006', '39836516-7240-4fe5-847b-d5ee0f57252d',
    (select departamento from persona.departamentos limit 1), (select lotacao from persona.lotacoes limit 1), (select nivelcargo from persona.niveiscargos limit 1), 47);

    INSERT INTO persona.trabalhadores (codigo, nome, identificacaonasajon, datanascimento, nis, tipo, dataadmissao, empresa, estabelecimento, departamento, lotacao, nivelcargo, tenant)
    VALUES ('000999', 'Renan Vinagre', 'renanvinagre@nasajon.com.br', '1980-01-11', '13328957609', 1, '2020-01-01','431bc005-9894-4c86-9dcd-7d1da9e2d006', '39836516-7240-4fe5-847b-d5ee0f57252d',
    (select departamento from persona.departamentos limit 1), (select lotacao from persona.lotacoes limit 1), (select nivelcargo from persona.niveiscargos limit 1), 47);

    /* TARIFAS CONCESSIONARIAS VTS */
    IF NOT EXISTS (SELECT tarifaconcessionariavt FROM persona.tarifasconcessionariasvts WHERE tarifaconcessionariavt = '9745fc74-900f-42c9-9eef-b56bb186f252') THEN
      INSERT INTO persona.concessionariasvts  (concessionariavt, codigo, nome, tenant) VALUES ('02e27ab9-bf31-4044-bddb-8bd9560656cc', 'VLT', 'VLT', 47);
      INSERT INTO persona.tarifasconcessionariasvts (tarifaconcessionariavt, codigo, descricao, valor, tipo, tenant, concessionariavt)
      VALUES ('9745fc74-900f-42c9-9eef-b56bb186f252', 'TR1', 'Ônibus', '5.50', '1', _TENANT, '02e27ab9-bf31-4044-bddb-8bd9560656cc');
    END IF;
  
    IF NOT EXISTS (SELECT tarifaconcessionariavttrabalhador FROM persona.tarifasconcessionariasvtstrabalhadores WHERE tarifaconcessionariavttrabalhador = '9745fc74-900f-42c9-9eef-b56bb186f253') THEN
        INSERT INTO persona.tarifasconcessionariasvtstrabalhadores  (tarifaconcessionariavttrabalhador,trabalhador, tarifaconcessionariavt, quantidade, tenant) VALUES ('9745fc74-900f-42c9-9eef-b56bb186f253',(select trabalhador from persona.trabalhadores where nome = 'Renan Vinagre'), '9745fc74-900f-42c9-9eef-b56bb186f252', 15, 47);
    END IF;

    IF NOT EXISTS (SELECT tarifaconcessionariavttrabalhador FROM persona.tarifasconcessionariasvtstrabalhadores WHERE tarifaconcessionariavttrabalhador = '9745fc74-900f-42c9-9eef-b56bb186f254') THEN
            INSERT INTO persona.tarifasconcessionariasvtstrabalhadores  (tarifaconcessionariavttrabalhador,trabalhador, tarifaconcessionariavt, quantidade, tenant) VALUES ('9745fc74-900f-42c9-9eef-b56bb186f254',(select trabalhador from persona.trabalhadores where nome = 'Thiago Moraes'), '9745fc74-900f-42c9-9eef-b56bb186f252', 15, 47);
    END IF;



    /* FIM TARIFAS CONCESSIONARIAS VTS */

    /* BANCO */

    IF NOT EXISTS (SELECT banco FROM financas.bancos WHERE banco = '43826037-c40b-462b-ac9e-6c4cce496177') THEN
      INSERT INTO financas.bancos (codigo, nome, numero, banco, codigoispb, tenant) 
      VALUES ('115', 'Itaú', '289', '43826037-c40b-462b-ac9e-6c4cce496177', '212', '47');
    END IF;

    /* BANCO FIM  */

    /* AGENCIA */

    IF NOT EXISTS (SELECT agencia FROM financas.agencias WHERE agencia = '43826037-c40b-462b-ac9e-6c4cce496177') THEN
      INSERT INTO financas.agencias (codigo, nome, agencianumero, digitoverificador, logradouro, numero, bairro, cidade, estado, cep, contato, telefone, dddtel, agencia, banco, tenant) 
      VALUES ('123456', 'Centro', '234567', '12', 'AV. Rio branco', '10000', 'Centro', 'Rio de Janeiro', 'RJ', '24450290', 'Zé', '99999999', '21', '43826037-c40b-462b-ac9e-6c4cce496177', '43826037-c40b-462b-ac9e-6c4cce496177', '47');
    END IF;

    /* AGENCIA FIM  */	

    /* TRABALHADORES */

    IF NOT EXISTS (SELECT trabalhador FROM persona.trabalhadores WHERE trabalhador = 'b7ba5398-845d-4175-9b5b-96ddcb5fed05') THEN
      INSERT INTO persona.trabalhadores (trabalhador, tipo, codigo,  nome, datanascimento, nivelcargo, identificacaonasajon, tenant, lastupdate, estabelecimento, agencia, numerocontasalario, numerocontasalariodv) 
      VALUES ('b7ba5398-845d-4175-9b5b-96ddcb5fed05', '2', '111111',  'Gisele Carneiro', '12/12/2000', '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', 'giselecarneiro@nasajon.com.br', _TENANT, now(), '39836516-7240-4fe5-847b-d5ee0f57252d', '43826037-c40b-462b-ac9e-6c4cce496177', '123456', '12');
    END IF;

    IF NOT EXISTS (SELECT trabalhador FROM persona.trabalhadores WHERE trabalhador = '1c92fe9f-dbf2-4006-96e2-237c1cac5447') THEN
      INSERT INTO persona.trabalhadores (trabalhador, tipo, codigo,  nome, datanascimento, nivelcargo, identificacaonasajon, tenant, lastupdate, estabelecimento, agencia, numerocontasalario, numerocontasalariodv) 
      VALUES ('1c92fe9f-dbf2-4006-96e2-237c1cac5447', '2', '111111',  'Gisele Carneiro', '12/12/2000', '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', 'giselecarneiro@nasajon.com.br', _TENANT, now(), 'fd10805a-567b-4bd2-a99f-f8916032c001', '43826037-c40b-462b-ac9e-6c4cce496177', '123457', '10');
    END IF;

    -- DADOS QUE TODOS TRABALHADORES PRECISAM
    update persona.trabalhadores set agencia = '43826037-c40b-462b-ac9e-6c4cce496177';
    update persona.trabalhadores set numerocontasalario = '123456';
    update persona.trabalhadores set numerocontasalariodv = '09';
    update persona.trabalhadores set salarioliquidoestimado = 3000;

    -- Atualizar os dados de endereço
    update persona.trabalhadores set
      logradouro = 'Avenida Rio Branco',
      numero = '45',
      complemento = 'Sala 1804',
      cep = '20090003',
      bairro = 'Centro',
      email = 'giselecarneiro@nasajon.com.br',
      dddtel = '21',
      telefone = '999888777',
      dddcel = '21',
      celular = '888777666',
      tipologradouro = 'AV',
      municipioresidencia = '3304409',
      paisresidencia = '1058'
    where trabalhador = 'b7ba5398-845d-4175-9b5b-96ddcb5fed05';

    /* TRABALHADORES FIM  */

    

    /* INFORMES */

    IF NOT EXISTS (SELECT informerendimento FROM meurh.informesrendimentos WHERE informerendimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed07') THEN
      INSERT INTO meurh.informesrendimentos (informerendimento, trabalhador, ano, caminhodocumento, tenant, created_by, updated_by, substituicao,anocalendario,anoexercicio) VALUES ('b7ba5398-845d-4175-9b5b-96ddcb5fed07', 'b7ba5398-845d-4175-9b5b-96ddcb5fed05', '2020', 'meurh/gednasajon/nasajon/meurh/solicitacoes/admissao/-1155092384/Atestado Medico.pdf', '47', '{"email":"giselecarneiro@nasajon.com.br"}', '{"email":"giselecarneiro@nasajon.com.br"}', true,'2020','2021');
    END IF;

    IF NOT EXISTS (SELECT informerendimento FROM meurh.informesrendimentos WHERE informerendimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0a') THEN
      INSERT INTO meurh.informesrendimentos (informerendimento, trabalhador, ano, caminhodocumento, tenant, created_by, updated_by, substituicao,anocalendario,anoexercicio) VALUES ('b7ba5398-845d-4175-9b5b-96ddcb5fed0a', '1c92fe9f-dbf2-4006-96e2-237c1cac5447', '2020', 'meurh/gednasajon/nasajon/meurh/solicitacoes/admissao/-1155092384/Atestado Medico.pdf', '47', '{"email":"giselecarneiro@nasajon.com.br"}', '{"email":"giselecarneiro@nasajon.com.br"}', false,'2020','2021');
    END IF;

    /* INFORMES FIM */    
    /* RECIBOSPAGAMENTOS */
    
    IF NOT EXISTS (SELECT recibopagamento FROM meurh.recibospagamentos WHERE recibopagamento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed12') THEN
      INSERT INTO meurh.recibospagamentos (recibopagamento, trabalhador, mes, ano, cargo, nivelcargo, calculo, caminhodocumento, liquido, tenant, created_by, updated_by, substituicao, datapagamento) VALUES ('b7ba5398-845d-4175-9b5b-96ddcb5fed12', 'b7ba5398-845d-4175-9b5b-96ddcb5fed05', '01', '2020', 'ae09d570-d910-457f-8ecf-4fe6204f61bd', '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', 'Fo', 'recibopagamento.pdf', '3000', '47', '{"email":"giselecarneiro@nasajon.com.br"}', '{"email":"giselecarneiro@nasajon.com.br"}', false, now());
    END IF;

    IF NOT EXISTS (SELECT recibopagamento FROM meurh.recibospagamentos WHERE recibopagamento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed13') THEN
      INSERT INTO meurh.recibospagamentos (recibopagamento, trabalhador, mes, ano, cargo, nivelcargo, calculo, caminhodocumento, liquido, tenant, created_by, updated_by, substituicao, datapagamento) VALUES ('b7ba5398-845d-4175-9b5b-96ddcb5fed13', '1c92fe9f-dbf2-4006-96e2-237c1cac5447', '02', '2020', 'ae09d570-d910-457f-8ecf-4fe6204f61bd', '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', 'Fo', 'recibopagamento.pdf', '3000', '47', '{"email":"giselecarneiro@nasajon.com.br"}', '{"email":"giselecarneiro@nasajon.com.br"}', false, now());
    END IF;

    /* RECIBOSPAGAMENTOS FIM  */

    /* SOLICITACOESSALARIOSSOBDEMANDA */
    
    IF NOT EXISTS (SELECT solicitacao FROM meurh.solicitacoessalariossobdemanda WHERE solicitacao = '1bec7c7b-3a24-4115-be12-e014a4159179') THEN
      INSERT INTO meurh.solicitacoessalariossobdemanda (solicitacao, tenant, created_by, updated_by, situacao, valor, trabalhador, tiposolicitacao, estabelecimento, codigo, provedorestabelecimento) VALUES ('1bec7c7b-3a24-4115-be12-e014a4159179', '47', '{"email":"caiocosta@nasajon.com.br"}', '{"email":"caiocosta@nasajon.com.br"}', '0', '300', 'b7ba5398-845d-4175-9b5b-96ddcb5fed05', '8','b7ba5398-845d-4175-9b5b-96ddcb5fed0f','47', true);
    END IF;

    IF NOT EXISTS (SELECT solicitacao FROM meurh.solicitacoessalariossobdemanda WHERE solicitacao = '4bec7c7b-3a24-4115-be12-e014a4159179') THEN
      INSERT INTO meurh.solicitacoessalariossobdemanda (solicitacao, tenant, created_by, updated_by, situacao, valor, trabalhador, tiposolicitacao, estabelecimento, codigo, provedorestabelecimento) VALUES ('4bec7c7b-3a24-4115-be12-e014a4159179', '47', '{"email":"caiocosta@nasajon.com.br"}', '{"email":"caiocosta@nasajon.com.br"}', '2', '1200', '1c92fe9f-dbf2-4006-96e2-237c1cac5447', '8','b7ba5398-845d-4175-9b5b-96ddcb5fed0f','47', true);
    END IF;

    /* SOLICITACOESSALARIOSSOBDEMANDA FIM */

    /* SOLICITACOESALTERAÇOESENDERECOS */
    
    IF NOT EXISTS (SELECT solicitacao FROM meurh.solicitacoesalteracoesenderecos WHERE solicitacao = '8d8e6058-6aaf-4993-a77d-da3dec1c86c7') THEN
      INSERT INTO meurh.solicitacoesalteracoesenderecos (solicitacao, tenant,tiposolicitacao,situacao,estabelecimento,justificativa,codigo,origem,trabalhador,tipologradouro,logradouro,numero,complemento,cep,municipioresidencia,bairro,paisresidencia,email,dddtel,telefone,dddcel,celular)
        VALUES ('8d8e6058-6aaf-4993-a77d-da3dec1c86c7', 47,5,1,'39836516-7240-4fe5-847b-d5ee0f57252d','Mudança',1,2,'b7ba5398-845d-4175-9b5b-96ddcb5fed05','AV','Avenida Rio Branco','45','Sala 1804','20090003','3304409','Centro','1058','giselecarneiro@nasajon.com.br','21','999888777','21','888777666');
    END IF;

    -- Tipo Documento Colaborador
    IF NOT EXISTS (SELECT tipodocumentocolaborador FROM persona.tiposdocumentoscolaboradores WHERE tipodocumentocolaborador = '1eec40b1-7c3d-4d21-8943-e8cda8d308d0') THEN
      INSERT INTO persona.tiposdocumentoscolaboradores (tipodocumentocolaborador,descricao,lastupdate,tenant)
        VALUES ('1eec40b1-7c3d-4d21-8943-e8cda8d308d0', 'Comprovante Residência','2020-09-09',47);
    END IF;
        IF NOT EXISTS (SELECT tipodocumentocolaborador FROM persona.tiposdocumentoscolaboradores WHERE tipodocumentocolaborador = '1F621F85-A7C5-4730-9A8E-E0D8EFBB9F38') THEN
      INSERT INTO persona.tiposdocumentoscolaboradores (tipodocumentocolaborador,descricao,lastupdate,tenant)
        VALUES ('1F621F85-A7C5-4730-9A8E-E0D8EFBB9F38', 'Atestado Médico','2020-09-09',47);
    END IF;
    IF NOT EXISTS (SELECT tipodocumentocolaborador FROM persona.tiposdocumentoscolaboradores WHERE tipodocumentocolaborador = 'AA9674B9-E6D3-4014-B6DE-9B23C372F795') THEN
      INSERT INTO persona.tiposdocumentoscolaboradores (tipodocumentocolaborador,descricao,lastupdate,tenant)
        VALUES ('AA9674B9-E6D3-4014-B6DE-9B23C372F795', 'RG','2020-09-09',47);
    END IF;

    -- Tipos Documentos Requeridos
    IF NOT EXISTS (SELECT tipodocumentorequerido FROM meurh.tiposdocumentosrequeridos WHERE tipodocumentorequerido = '8CCEAC7B-8CEA-4ED3-B212-CF48F3F1FD7C') THEN
      INSERT INTO meurh.tiposdocumentosrequeridos (tipodocumentorequerido, tipodocumentocolaborador, tiposolicitacao, obrigatorio, tenant, estabelecimento, created_by, created_at, updated_by, updated_at)
        VALUES ('8CCEAC7B-8CEA-4ED3-B212-CF48F3F1FD7C', '1eec40b1-7c3d-4d21-8943-e8cda8d308d0', 2, true, 47, '39836516-7240-4fe5-847b-d5ee0f57252d', '{"email":"caiocosta@nasajon.com.br"}', now(), '{"email":"caiocosta@nasajon.com.br"}', now());
    END IF;
    IF NOT EXISTS (SELECT tipodocumentorequerido FROM meurh.tiposdocumentosrequeridos WHERE tipodocumentorequerido = 'A3A02EBB-0E09-40CB-BF58-383DD92ECD35') THEN
      INSERT INTO meurh.tiposdocumentosrequeridos (tipodocumentorequerido, tipodocumentocolaborador, tiposolicitacao, obrigatorio, tenant, estabelecimento, created_by, created_at, updated_by, updated_at)
        VALUES ('A3A02EBB-0E09-40CB-BF58-383DD92ECD35', '1F621F85-A7C5-4730-9A8E-E0D8EFBB9F38', 2, true, 47, '39836516-7240-4fe5-847b-d5ee0f57252d', '{"email":"caiocosta@nasajon.com.br"}', now(), '{"email":"caiocosta@nasajon.com.br"}', now());
    END IF;
    IF NOT EXISTS (SELECT tipodocumentorequerido FROM meurh.tiposdocumentosrequeridos WHERE tipodocumentorequerido = 'EC09C0E0-EE8C-4C74-8E3D-1724396A818D') THEN
      INSERT INTO meurh.tiposdocumentosrequeridos (tipodocumentorequerido, tipodocumentocolaborador, tiposolicitacao, obrigatorio, tenant, estabelecimento, created_by, created_at, updated_by, updated_at)
        VALUES ('EC09C0E0-EE8C-4C74-8E3D-1724396A818D', 'AA9674B9-E6D3-4014-B6DE-9B23C372F795', 7, true, 47, '39836516-7240-4fe5-847b-d5ee0f57252d', '{"email":"caiocosta@nasajon.com.br"}', now(), '{"email":"caiocosta@nasajon.com.br"}', now());
    END IF;

    -- Documento vinculado a solicitação
    IF NOT EXISTS (SELECT documentocolaborador FROM persona.documentoscolaboradores WHERE documentocolaborador = 'b5381824-d677-4de6-9821-23350d07bc88') THEN
      insert into persona.documentoscolaboradores (tipodocumentocolaborador, documentocolaborador, urldocumento, bindocumento, tenant, trabalhador, solicitacao)
        values ('1eec40b1-7c3d-4d21-8943-e8cda8d308d0', 'b5381824-d677-4de6-9821-23350d07bc88', '47/teste.txt', 'T2k=', 47, 'b7ba5398-845d-4175-9b5b-96ddcb5fed05', '8d8e6058-6aaf-4993-a77d-da3dec1c86c7');
    END IF;
    /* SOLICITACOESALTERAÇOESENDERECOS FIM */

    /* VALORESSOLICITACOESPORPERIODO
     *
     * Deve acompanhar os inserts de solicitações acima
     */

    IF NOT EXISTS (SELECT valorsolicitacaoporperiodo FROM meurh.valoressolicitacoesporperiodo WHERE valorsolicitacaoporperiodo = 'db4c0384-7617-4d94-ad32-4f4a2e697327') THEN
      INSERT INTO meurh.valoressolicitacoesporperiodo (valorsolicitacaoporperiodo, tenant, estabelecimento, trabalhador, ano, mes, valordisponivel, valorpendente, valoraprovado, valorbloqueado, created_by) VALUES ('db4c0384-7617-4d94-ad32-4f4a2e697327', _TENANT, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', 'b7ba5398-845d-4175-9b5b-96ddcb5fed05', CAST(to_char(CURRENT_DATE, 'YYYY') AS INTEGER), CAST(to_char(CURRENT_DATE, 'MM') AS INTEGER), 1700, 300, 500, 600, '{"email":"caiocosta@nasajon.com.br"}');
    END IF;

    /* VALORESSOLICITACOESPORPERIODO FIM */

    /* SOLICITACOESSEQUENCIAS */

    IF NOT EXISTS (SELECT tenant FROM meurh.solicitacoessequencias where tenant = _TENANT) THEN
        INSERT INTO meurh.solicitacoessequencias (tenant, ultimocodigo) VALUES (_TENANT, 1);
    END IF;

    /* SOLICITACOESSEQUENCIAS FIM */

    /* WEB.CONFIGURACOES */

    update web.configuracoes set valor = 20.0 where chave = 'SOLICITACAO_SALARIO_SOBDEMANDA_VALOR_MIN';


    update persona.trabalhadores set inicioperiodoaquisitivoferias = '2017-01-01', estabelecimento = _ESTABELECIMENTO_UUID
    where tenant = _TENANT;

    update web.configuracoes set valor = '{"financeiras": [], "estabelecimentos": ["b7ba5398-845d-4175-9b5b-96ddcb5fed0f","fd10805a-567b-4bd2-a99f-f8916032c001", "39836516-7240-4fe5-847b-d5ee0f57252d"]}' where chave = 'SOLICITACAO_SALARIO_SOBDEMANDA_PROVEDOR';


    IF NOT EXISTS (SELECT chave FROM web.configuracoes WHERE chave = 'TIMEZONE' AND sistema = 'PERSONACLIENTE') THEN
        insert into web.configuracoes (chave, valor, tenant, sistema) values
          ('TIMEZONE', 'America/Sao_Paulo', _TENANT::bigint, 'PERSONACLIENTE');
    END IF;

    /* WEB.CONFIGURACOES FIM */
  END 
$$;