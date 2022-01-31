<?php
namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\AbstractRepository as ParentRepository;

/**
 * Repository utilizado para buscar dados utilizados na geração de relatórios do atendimento comercial
 */
class AtcsRelatoriosRepository extends ParentRepository
{
    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Atendimento
     *  - Estabelecimento
     *  - Fornecedor
     *  - Cliente
     */
    public function getEntidades($tenant, $id_grupoempresarial, $atc, $fornecedor, $cliente){
        $sql = "
            -- Informações do atendimento principal
            with tb_atc as (
                select
                    -- Dados gerais
                    a.negocio as atc_negocio,
                    a.tenant as atc_tenant,
                    a.id_grupoempresarial as atc_grupoempresarial,
                    a.codigo as atc_codigo,
                    a.nome as atc_nome,
                    a.created_by as atc_created_by,
                    a.created_at as atc_created_at,
                    a.possuiseguradora as atc_possuiseguradora,
                    a.observacoes as atc_observacoes,
                    a.area as atc_area, -- Area do atendimento, fazer join com crm.negociosareas
                    a.origem as atc_origem, -- Mídia de origem, fazer join com crm.midiasorigem
                    a.tiposacionamento as atc_tiposacionamento, -- Tipo de acionamento, fazer join com crm.tiposacionamento
                    a.negociopai as atc_negociopai, -- Atendimento Pai, fazer join com crm.negocios
                    a.estabelecimento as atc_estabelecimento,
                    -- Campos de localização
                    a.localizacaocidadeestrangeira as atc_localizacaocidadeestrangeira,
                    a.localizacaocep as atc_localizacaocep,
                    a.localizacaobairro as atc_localizacaobairro,
                    a.localizacaorua as atc_localizacaorua,
                    a.localizacaonumero as atc_localizacaonumero,
                    a.localizacaocomplemento as atc_localizacaocomplemento,
                    a.localizacaoreferencia as atc_localizacaoreferencia,
                    a.localizacao as atc_localizacao,
                    a.localizacaopais as atc_localizacaopais,
                    a.localizacaomunicipio as atc_localizacaomunicipio,
                    a.localizacaoestado as atc_localizacaoestado,
                    a.localizacaotipologradouro as atc_localizacaotipologradouro,
                    m.nome as atc_localizacaomunicipiosepultamento
                from crm.atcs a
                left join ns.municipios m on
                    m.ibge = a.localizacaomunicipiosepultamento
                where a.negocio = :atc
                  and a.tenant = :tenant
                  and a.id_grupoempresarial = :grupoempresarial
            ),
            -- Informações do atendimento pai
            tb_atc_com_pai as (
                select
                    n.*,
                    np.codigo as atcpai_codigo,
                    np.nome as atcpai_nome
                from tb_atc as n
                left join crm.atcs as np on
                    n.atc_negociopai is not null and
                    np.negocio = n.atc_negociopai and
                    np.tenant = n.atc_tenant and
                    np.id_grupoempresarial = n.atc_grupoempresarial
            ),
            -- Informações do Estabelecimento
            tb_atc_com_estabelecimento as (
                select
                    n.*,
                    e.codigo as estabelecimento_codigo,
                    e.razaosocial as estabelecimento_razaosocial,
                    e.nomefantasia as estabelecimento_nomefantasia,
                    e.cnpj_completo as estabelecimento_cnpj_completo,
                    e.email as estabelecimento_email,
                    e.telefonecomddd as estabelecimento_telefonecomddd,
                    -- Informações de localização
                    e.tipologradouro as estabelecimento_end_tipologradouro,
                    e.logradouro as estabelecimento_end_logradouro,
                    e.numero as estabelecimento_end_numero,
                    e.complemento as estabelecimento_end_complemento,
                    e.cep as estabelecimento_end_cep,
                    e.bairro as estabelecimento_end_bairro,
                    m.uf as estabelecimento_end_uf, -- Onde está
                    null as estabelecimento_end_paisnome, -- Onde está
                    m.nome as estabelecimento_end_municipionome -- Onde está
                from tb_atc_com_pai as n
                inner join ns.vw_estabelecimentos as e on
                    n.atc_estabelecimento = e.estabelecimento and
                    e.tenant = n.atc_tenant and
                    e.grupoempresarial = n.atc_grupoempresarial
                left join ns.municipios m on
                    m.ibge = e.ibge
            ),
            -- Informações do fornecedor
            tb_fornecedor as (
                SELECT DISTINCT 
                    p.id as fornecedor_fornecedor,
                    p.pessoa as fornecedor_codigo,
                    p.nome as fornecedor_nome,
                    CASE
                        WHEN p.nomefantasia::text = ''::text THEN COALESCE(p.nome, 'Não informado'::character varying)::character varying(150)
                        ELSE COALESCE(p.nomefantasia, p.nome, 'Não informado'::character varying)::character varying(150)
                    END as fornecedor_nomefantasia,
                    p.cnpj as fornecedor_cnpj,
                    p.inscricaomunicipal as fornecedor_inscricaomunicipal,
                    p.anotacao as fornecedor_anotacao,
                    CASE
                        WHEN fs.tipo = 1 THEN '1'::text
                        WHEN fs.tipo = 2 THEN '2'::text
                        ELSE '0'::text
                    END as fornecedor_status,
                    p.esperapagamentoseguradora as fornecedor_esperapagamentoseguradora,
                    -- Endereço local
                    endl.tipologradouro as fornecedor_endlocal_tipologradouro,
                    endl.logradouro as fornecedor_endlocal_logradouro,
                    endl.numero as fornecedor_endlocal_numero,
                    endl.complemento as fornecedor_endlocal_complemento,
                    endl.cep as fornecedor_endlocal_cep,
                    endl.bairro as fornecedor_endlocal_bairro,
                    endl.uf as fornecedor_endlocal_uf,
                    endl.pais as fornecedor_endlocal_pais,
                    ml.nome as fornecedor_endlocal_municipio,
                    endl.referencia as fornecedor_endlocal_referencia,
                    endl.nome as fornecedor_endlocal_nome,
                    -- Endereço de Cobrança
                    endc.tipologradouro as fornecedor_endcob_tipologradouro,
                    endc.logradouro as fornecedor_endcob_logradouro,
                    endc.numero as fornecedor_endcob_numero,
                    endc.complemento as fornecedor_endcob_complemento,
                    endc.cep as fornecedor_endcob_cep,
                    endc.bairro as fornecedor_endcob_bairro,
                    endc.uf as fornecedor_endcob_uf,
                    endc.pais as fornecedor_endcob_pais,
                    mc.nome as fornecedor_endcob_municipio,
                    endc.referencia as fornecedor_endcob_referencia,
                    endc.nome as fornecedor_endcob_nome
                FROM ns.pessoas p
                INNER JOIN ns.conjuntosfornecedores cf on
                    p.id = :fornecedor and
                    p.tenant = :tenant_fornecedor and
                    cf.registro = p.id and
                    cf.tenant = p.tenant
                INNER JOIN ns.conjuntos c on
                    cf.conjunto = c.conjunto and
                    cf.tenant = c.tenant
                INNER JOIN ns.estabelecimentosconjuntos ec on
                    c.conjunto = ec.conjunto and
                    c.tenant = ec.tenant
                INNER JOIN ns.estabelecimentos est on
                    est.estabelecimento = ec.estabelecimento and
                    est.tenant = ec.tenant
                INNER JOIN ns.empresas emp on
                    emp.empresa = est.empresa and
                    emp.tenant = est.tenant
                LEFT JOIN ns.enderecos endl ON 
                    endl.id_pessoa = p.id and
                    endl.tipoendereco = 0 and
                    endl.tenant = p.tenant
                LEFT JOIN ns.enderecos endc ON 
                    endc.id_pessoa = p.id and
                    endc.tipoendereco = 2 and
                    endc.tenant = p.tenant
                LEFT JOIN ns.fornecedoressuspensos fs on
                    fs.fornecedor_id = p.id and
                    fs.tenant = p.tenant
                LEFT JOIN ns.municipios ml on
                    ml.ibge = endl.municipio
                LEFT JOIN ns.municipios mc on
                    mc.ibge = endc.municipio
                limit 1
            ),
            -- Informações do cliente
            tb_cliente as (
                SELECT DISTINCT 
                    p.id as cliente_cliente,
                    p.pessoa as cliente_codigo,
                    p.nome as cliente_nome,
                    CASE
                        WHEN p.nomefantasia::text = ''::text THEN COALESCE(p.nome, 'Não informado'::character varying)::character varying(150)
                        ELSE COALESCE(p.nomefantasia, p.nome, 'Não informado'::character varying)::character varying(150)
                    END as cliente_nomefantasia,
                    p.cnpj as cliente_cnpj,
                    p.inscricaomunicipal as cliente_inscricaomunicipal,
                    p.anotacao as cliente_anotacao,
                    -- Endereço local
                    endl.tipologradouro as cliente_endlocal_tipologradouro,
                    endl.logradouro as cliente_endlocal_logradouro,
                    endl.numero as cliente_endlocal_numero,
                    endl.complemento as cliente_endlocal_complemento,
                    endl.cep as cliente_endlocal_cep,
                    endl.bairro as cliente_endlocal_bairro,
                    endl.uf as cliente_endlocal_uf,
                    endl.pais as cliente_endlocal_pais,
                    ml.nome as cliente_endlocal_municipio,
                    endl.referencia as cliente_endlocal_referencia,
                    endl.nome as cliente_endlocal_nome,
                    -- Endereço de Cobrança
                    endc.tipologradouro as cliente_endcob_tipologradouro,
                    endc.logradouro as cliente_endcob_logradouro,
                    endc.numero as cliente_endcob_numero,
                    endc.complemento as cliente_endcob_complemento,
                    endc.cep as cliente_endcob_cep,
                    endc.bairro as cliente_endcob_bairro,
                    endc.uf as cliente_endcob_uf,
                    endc.pais as cliente_endcob_pais,
                    mc.nome as cliente_endcob_municipio,
                    endc.referencia as cliente_endcob_referencia,
                    endc.nome as cliente_endcob_nome
                FROM ns.pessoas p
                JOIN ns.conjuntosclientes cc on
                    p.id = :cliente and
                    p.tenant = :tenant_cliente and
                    cc.registro = p.id and
                    cc.tenant = p.tenant
                JOIN ns.conjuntos c on
                    cc.conjunto = c.conjunto and
                    cc.tenant = c.tenant
                JOIN ns.estabelecimentosconjuntos ec on
                    c.conjunto = ec.conjunto and
                    ec.permissao and
                    c.tenant = ec.tenant
                JOIN ns.estabelecimentos est on
                    est.estabelecimento = ec.estabelecimento and
                    est.tenant = ec.tenant
                JOIN ns.empresas emp on
                    emp.empresa = est.empresa and
                    emp.tenant = est.tenant
                LEFT JOIN ns.enderecos endl ON 
                    endl.id_pessoa = p.id and
                    endl.tipoendereco = 0 and
                    endl.tenant = p.tenant
                LEFT JOIN ns.enderecos endc ON 
                    endc.id_pessoa = p.id and
                    endc.tipoendereco = 2 and
                    endc.tenant = p.tenant
                LEFT JOIN ns.municipios ml on
                    ml.ibge = endl.municipio
                LEFT JOIN ns.municipios mc on
                    mc.ibge = endc.municipio
                limit 1
            )
            select
                -- Dados do atendimento e estabelecimento
                atc.*,
                a.nome as atc_area_nome,
                a.localizacao as atc_area_localizacao,
                m.codigo as atc_midia_nome,
                ta.nome as atc_tipoacionamento_nome,
                -- Dados de Fornecedor
                f.*,
                -- Dados de cliente
                c.*
            from tb_atc_com_estabelecimento atc
            left join crm.atcsareas a on
                a.negocioarea = atc.atc_area and
                a.tenant = atc.atc_tenant
            left join crm.midiasorigem m on
                m.midiaorigem = atc.atc_origem and
                m.tenant = atc.atc_tenant
            left join crm.tiposacionamentos ta on
                ta.tiposacionamento = atc.atc_tiposacionamento and
                ta.tenant = atc.atc_tenant
            inner join tb_fornecedor f on
                1 = 1
            inner join tb_cliente c on
                1 = 1";

        $bindQuery = [
            'atc' => $atc,
            'tenant' => $tenant,
            'fornecedor' => $fornecedor,
            'tenant_fornecedor' => $tenant,
            'cliente' => $cliente,
            'tenant_cliente' => $tenant,
            'grupoempresarial' => $id_grupoempresarial
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Responsáveis financeiros do atendimento
     */
    public function getResponsaveisFinanceiros($tenant, $id_grupoempresarial, $atc){
        $sql = "
            select
                arf.principal, 
                p.nome
            from crm.atcsresponsaveisfinanceiros arf
            inner join ns.pessoas p on
                p.id = arf.responsavelfinanceiro and
                p.tenant = arf.tenant
            where arf.negocio = :atc
              and arf.tenant = :tenant";

        $bindQuery = [
            'atc' => $atc,
            'tenant' => $tenant
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Contatos do fornecedor
     */
    public function getContatosFornecedores($tenant, $id_grupoempresarial, $fornecedor){
        $sql = "
            select 
                -- Dados do contato
                c.id as contato_id, 
                c.nome as contato_nome,
                c.primeironome as contato_primeironome,
                c.sobrenome as contato_sobrenome,
                coalesce(c.principal, false) as contato_principal,
                c.cargo as contato_cargo,
                c.setor as contato_setor,
                c.email as contato_email,
                c.observacao as contato_observacao,
                -- Dados do telefone
                t.id as contato_tel_id,
                t.ddi as contato_tel_ddi,
                t.ddd as contato_tel_ddd, 
                t.telefone as contato_tel_numero, 
                t.ramal as contato_tel_ramal,
                coalesce(t.principal, false) as contato_tel_principal,
                t.descricao as contato_tel_observacao
            from ns.contatos c
            left join ns.telefones t on
                t.contato = c.id and
                t.tenant = c.tenant
            where c.id_pessoa = :fornecedor
              and c.tenant = :tenant
            order by contato_id";

        $bindQuery = [
            'fornecedor' => $fornecedor,
            'tenant' => $tenant
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Dados bancários do fornecedor
     */
    public function getDadosBancariosFornecedor($tenant, $id_grupoempresarial, $fornecedor){
        $sql = "
            select 
                cf.agencianome,
                cf.agencianumero,
                cf.agenciadv,
                cf.contanumero,
                cf.contadv,
                coalesce(cf.padrao, false) as conta_principal,
                case
                    when (cf.tipoconta = 1) then 'CONTA CORRENTE'
                    when (cf.tipoconta = 2) then 'CONTA POUPANÇA'
                    else ''
                end as conta_tipo,
                b.nome as banco_nome, 
                b.numero as banco_numero
            from financas.contasfornecedores cf
            left join financas.bancos b on 
                b.numero = cf.banco and
                b.tenant = cf.tenant
            where cf.id_fornecedor = :fornecedor
              and cf.tenant = :tenant";

        $bindQuery = [
            'fornecedor' => $fornecedor,
            'tenant' => $tenant
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Tipos de atividades do fornecedor
     */
    public function getTiposAtividadesFornecedor($tenant, $id_grupoempresarial, $fornecedor){
        $sql = "
            select 
                ta.nome, 
                ta.descricao
            from ns.pessoastiposatividades pta
            inner join ns.tiposatividades ta on
                ta.tipoatividade = pta.tipoatividade and
                ta.tenant = pta.tenant
            where pta.pessoa = :fornecedor
            and pta.tenant = :tenant";

        $bindQuery = [
            'fornecedor' => $fornecedor,
            'tenant' => $tenant
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Seguros do atendimento
     */
    public function getSegurosAtendimento($tenant, $id_grupoempresarial, $atc){
        $sql = "
            select
                tpg.nome as produto,
                case
                    when (ads.apolicetipo = 0) then 'FÍSICA'
                    when (ads.apolicetipo = 1) then 'JURÍDICA'
                    else ''
                end as tipoapolice,
                ads.apolice as apolice_id,
                tp.nome as apolice,
                ads.sinistro,
                ads.nomefuncionarioseguradora,
                ads.titularnome,
                v.nome as titularvinculo
            from crm.atcsdadosseguradoras ads -- Dados do seguro
            inner join crm.templatespropostas tp on -- Apólice
                tp.templateproposta = ads.apolice and
                tp.tenant = ads.tenant
            inner join crm.templatespropostasgrupos tpg on -- Produto
                tpg.templatepropostagrupo = ads.produtoseguradora and
                tpg.tenant = ads.tenant
            left join crm.vinculos v on -- Vínculo com o segurado
                v.vinculo = ads.titularvinculo and
                v.tenant = ads.tenant
            where ads.negocio = :atc
              and ads.tenant = :tenant
              and ads.id_grupoempresarial = :grupoempresarial;";

        $bindQuery = [
            'atc' => $atc,
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Serviços Orçados para o fornecedor
     */
    public function getServicosOrcadosAtendimento($tenant, $id_grupoempresarial, $atc, $fornecedor){
        
        $sql = "
        select 
            case
                when (o.familia is not null) then fa.descricao
                when (o.composicao is not null) then cmp.nome
            end as servico_nome,
            o.valorunitario as servico_valor,
            p.id_apolice as servico_apolice,
            o.quantidade as quantidade,
            o.desconto as descontoparcial,
            fe.descontoglobal as descontoglobal,
            o.valorreceber as valorreceber
        from crm.orcamentos o
        left join crm.propostasitens p on 
            p.propostaitem = o.propostaitem and
            p.negocio = :atc and
            p.tenant = :tenant and
            p.id_grupoempresarial = :grupoempresarial and
            p.tenant = o.tenant
        left join estoque.familias fa on
            fa.familia = o.familia and
            fa.tenant = o.tenant
        left join crm.composicoes cmp on 
            cmp.composicao = o.composicao and
            cmp.tenant = o.tenant
        left join crm.fornecedoresenvolvidos fe on
            fe.negocio = :atc and
            fe.fornecedor = :fornecedor and
            fe.tenant = o.tenant
        where
            o.fornecedor = :fornecedor
            and o.atc = :atc
            and
            (
                (o.familia is not null) or
                (o.composicao is not null)
            )
        ";

        $bindQuery = [
            'atc' => $atc,
            'fornecedor' => $fornecedor,
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }

    /**
     * Retorna os seguintes dados utilizados para montar XML de relatório dos fornecedores:
     *  - Documentos do atendimento
     */
    public function getDocumentosAtendimento($tenant, $id_grupoempresarial, $atc){
        $sql = "
            select td.nome, tdr.copiasimples, tdr.copiaautenticada, tdr.original, max(ad.updated_at) as data_recebimento
            from crm.atcstiposdocumentosrequisitantes tdr
            left join ns.tiposdocumentos td on
                td.tipodocumento = tdr.tipodocumento and
                td.tenant = tdr.tenant
            left join crm.atcsdocumentos ad on
                tdr.tipodocumento = ad.tipodocumento and
                tdr.tenant = ad.tenant and
                tdr.id_grupoempresarial = ad.id_grupoempresarial
            where tdr.negocio = :atc
              and tdr.tenant = :tenant
              and tdr.id_grupoempresarial = :grupoempresarial
              and tdr.naoexibiremrelatorios = false
            group by td.nome, tdr.copiasimples, tdr.copiaautenticada, tdr.original;";

        $bindQuery = [
            'atc' => $atc,
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }
}
