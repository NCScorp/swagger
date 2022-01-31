<?php

namespace Nasajon\AppBundle\Repository\Crm;


use Nasajon\MDABundle\Repository\AbstractRepository as ParentRepository;

class AtcsRelatoriosRpsRepository extends ParentRepository
{
    // método gerado para auxiliar no desenvolvimento
    // e será usado na tarefa seguinte
    
    public function pegaContratosProvisorio($atc, $tenant, $grupoempresarial)
    {

        $sql = "select
            C.contrato,
            C.tenant,
            RFV.responsabilidadefinanceiravalor,
            RFV.responsabilidadefinanceira,
            RF.orcamento,
            RF.negocio,
            O.fornecedor
        from financas.contratos C
        -- left join lateral e limit 1para impedir gerar várias tuplas de contrato como restultado
        
        left join lateral (
            select 
                responsabilidadefinanceiravalor,
                responsabilidadefinanceira,
                contrato,
                tenant,
                id_grupoempresarial
            from crm.responsabilidadesfinanceirasvalores
            where C.contrato = responsabilidadesfinanceirasvalores.contrato
            limit 1
        ) RFV on (C.contrato = RFV.contrato and C.tenant = RFV.tenant)

        left join crm.responsabilidadesfinanceiras RF on
        (RF.responsabilidadefinanceira = RFV.responsabilidadefinanceira and RF.tenant = RFV.tenant and RF.id_grupoempresarial = RFV.id_grupoempresarial)

        left join crm.orcamentos O on
        (RF.orcamento = O.orcamento and RF.tenant = O.tenant and RF.id_grupoempresarial = O.grupoempresarial)
        
        where
        RF.negocio = :atc
        and C.tenant = :tenant
        and RFV.id_grupoempresarial = :grupoempresarial
        ";

        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':atc', $atc);
        $statement->bindValue(':tenant', $tenant);
        $statement->bindValue(':grupoempresarial', $grupoempresarial);
        $statement->execute();
        $data = $statement->fetchAll();
        return $data;
    }

    public function getDadosBaseContratos($atc, $contrato, $tenant, $grupoempresarial, $contratoTaxaAdm = false)
    {
        $sql = "select
            C.contrato,
            C.numerorps,
            c.created_at,
            C.tenant,
            RFV.responsabilidadefinanceiravalor,
            RFV.responsabilidadefinanceira,
            RF.orcamento,
            RF.negocio,
            O.fornecedor
        from financas.contratos C
        -- left join lateral e limit 1para impedir gerar várias tuplas de contrato como restultado
        
        left join lateral (
            select 
                responsabilidadefinanceiravalor,
                responsabilidadefinanceira,
                contrato,
                tenant,
                id_grupoempresarial
            from crm.responsabilidadesfinanceirasvalores
            where C.contrato = responsabilidadesfinanceirasvalores.contrato
            limit 1
        ) RFV on (C.contrato = RFV.contrato and C.tenant = RFV.tenant)

        left join crm.responsabilidadesfinanceiras RF on
        (RF.responsabilidadefinanceira = RFV.responsabilidadefinanceira and RF.tenant = RFV.tenant and RF.id_grupoempresarial = RFV.id_grupoempresarial)

        left join crm.orcamentos O on
        (RF.orcamento = O.orcamento and RF.tenant = O.tenant and RF.id_grupoempresarial = O.grupoempresarial)
        
        where
        C.contrato = :contrato
        and C.tenant = :tenant
        ";

        if($contratoTaxaAdm !== true){
            $sql .= '
            and RF.negocio = :atc
            and RFV.id_grupoempresarial = :grupoempresarial';
        }

        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':contrato', $contrato);
        $statement->bindValue(':tenant', $tenant);
        if($contratoTaxaAdm !== true){
            $statement->bindValue(':atc', $atc);
            $statement->bindValue(':grupoempresarial', $grupoempresarial);
        }
        $statement->execute();
        $data = $statement->fetch();
        return $data;
        
    }

    public function getEstabelecimento($estabelecimento, $tenant, $grupoempresarial)
    {
        $sql = "select
            e.codigo as codigo,
            e.nomefantasia as nomefantasia,
            e.razaosocial as razaosocial,
            e.cnpj_completo as cnpj_completo,
            e.inscricaoestadual as inscricaoestadual,
            e.inscricaomunicipal as inscricaomunicipal,
            e.email as email,
            e.telefonecomddd as telefonecomddd,
            -- Informações de localização
            e.tipologradouro as end_tipologradouro,
            e.logradouro as end_logradouro,
            e.numero as end_numero,
            e.complemento as end_complemento,
            e.cep as end_cep,
            e.bairro as end_bairro,
            m.uf as end_uf,
            null as end_paisnome,
            m.nome as end_municipionome
        from ns.vw_estabelecimentos as E
        left join ns.municipios M on M.ibge = E.ibge
        where
        E.estabelecimento = :estabelecimento
        and E.tenant = :tenant
        and E.grupoempresarial = :grupoempresarial
        ";
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':estabelecimento', $estabelecimento);
        $statement->bindValue(':tenant', $tenant);
        $statement->bindValue(':grupoempresarial', $grupoempresarial);
        $statement->execute();
        $data = $statement->fetch();
        return $data;
    }

    public function getItensNota($contrato, $tenant, $grupoempresarial, $contratoTaxaAdm = false)
    {
        $sql = "select
            IC.servico as itemcontrato_servico,
            IC.codigo as itemcontrato_codigo,
            IC.valor as itemcontrato_valor,
            IC.quantidade as itemcontrato_quantidade,
            IC.created_at as itemcontrato_created_at,
            RFV.valorpagar as respfin_valorpagar,
            RFV.responsavelfinanceiro as respfin_responsavelfinanceiro,
            FC.descontoglobalitensnaofaturados as contrato_desconto,
            C.nome as composicao_nome,
            C.servicotecnico as composicao_servicotecnico,
            F.codigo as familia_codigo,
            F.descricao as familia_descricao,
            CF.cfop as cfop_cfop,
            CF.descricao as cfop_descricao
        from financas.itenscontratos IC
        left join financas.contratos FC on (IC.tenant = FC.tenant and IC.contrato = FC.contrato)
        left join crm.responsabilidadesfinanceirasvalores RFV on (IC.tenant = RFV.tenant and IC.itemcontrato = RFV.itemcontrato)
        left join crm.responsabilidadesfinanceiras RF on (RFV.responsabilidadefinanceira = RF.responsabilidadefinanceira and RFV.tenant = RF.tenant and RFV.id_grupoempresarial = RF.id_grupoempresarial)
        left join crm.orcamentos O on (RF.orcamento = O.orcamento and RF.tenant = O.tenant and RF.id_grupoempresarial = O.grupoempresarial)
        left join crm.composicoes C on (C.composicao = O.composicao and C.tenant = O.tenant and C.grupoempresarial = O.grupoempresarial)
        left join estoque.familias F on (F.familia = O.familia and F.tenant = O.tenant)
        left join servicos.servicostecnicos ST on (C.servicotecnico = ST.servicotecnico and C.tenant = ST.tenant)
        left join servicos.servicoscfops SC on (SC.servico_id = ST.servicotecnico)
        left join ns.cfop CF on ( SC.cfop_id = CF.id and ST.tenant = CF.tenant )
        where
        IC.contrato = :contrato
        and IC.tenant = :tenant
        ";
        
        if($contratoTaxaAdm !== true){
            $sql .= 'and RFV.id_grupoempresarial = :grupoempresarial';
        }

        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':contrato', $contrato);
        $statement->bindValue(':tenant', $tenant);
        if($contratoTaxaAdm !== true){
            $statement->bindValue(':grupoempresarial', $grupoempresarial);
        }
        $statement->execute();
        $data = $statement->fetchAll();
        return $data;
    }

    public function getContatoPrincipalCliente($cliente, $tenant)
    {
        $sql =
        "SELECT  
            C.nome,
            C.cargo,
            C.email,
            C.primeironome,
            C.sobrenome
        FROM ns.contatos C
        where c.id_pessoa = :cliente
        and c.tenant = :tenant_cliente
        and c.principal = true
        limit 1";

        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':cliente', $cliente);
        $statement->bindValue(':tenant_cliente', $tenant);
        $statement->execute();
        $data = $statement->fetch();
        return $data;

    }

    public function getCliente($cliente, $tenant)
    {
        $sql =
        "SELECT  
            p.id as cliente_cliente,
            p.pessoa as cliente_codigo,
            p.nome as cliente_nome,
            CASE
                WHEN p.nomefantasia::text = ''::text THEN COALESCE(p.nome, 'Não informado'::character varying)::character varying(150)
                ELSE COALESCE(p.nomefantasia, p.nome, 'Não informado'::character varying)::character varying(150)
            END as cliente_nomefantasia,
            p.cnpj as cliente_cnpj,
            p.inscricaomunicipal as cliente_inscricaomunicipal,
            p.inscricaoestadual as cliente_inscricaoestadual,
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
        limit 1";

        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':cliente', $cliente);
        $statement->bindValue(':tenant_cliente', $tenant);
        $statement->execute();
        $data = $statement->fetch();
        return $data;

    }

}
