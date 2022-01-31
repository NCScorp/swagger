<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Crm\OrcamentosRepository as ParentRepository;

/*
 * Método findAllQueryBuilder sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 * Método findAll sobrescrito para mudar referencia do guid do fornecedor para id de ns pessoas.
 */
class OrcamentosRepository extends ParentRepository{

    /**
     * Sobrescrita para otimização, deixando de trazer alguns campos e fazer join com algumas views
     */
    public function findAllQueryBuilder($tenant,$id_grupoempresarial, Filter $filter = null){
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.orcamento as orcamento',
            't0_.propostaitem as propostaitem',
            't0_.valor as valor',
            't0_.acrescimo as acrescimo',
            't0_.desconto as desconto',
            't0_.descontoglobal as descontoglobal',
            't0_.atc as atc',
            't0_.composicao as composicao',
            't0_.familia as familia',
            't0_.descricaomanual as descricaomanual',
            't0_.descricao as descricao',
            't0_.custo as custo',
            't0_.motivo as motivo',
            't0_.status as status',
            't0_.faturar as faturar',
            't0_.faturamentotipo as faturamentotipo',
            't0_.updated_at as updated_at',
            't0_.valorreceber as valorreceber',
            't0_.tipoatualizacao as tipoatualizacao',
        );
         
        if( $filter && empty($filter->getOffset()) ){
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.orcamentos', 't0_');
        // Join com fornecedores
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't1_', 't0_.fornecedor = t1_.id and t0_.tenant = t1_.tenant'); 
        $queryBuilder->addSelect(array(
            't1_.id as fornecedor_fornecedor',
            "CASE
                WHEN t1_.nomefantasia::text = ''::text THEN COALESCE(t1_.nome, 'Não informado'::character varying)::character varying(150)
                ELSE COALESCE(t1_.nomefantasia, t1_.nome, 'Não informado'::character varying)::character varying(150)
            END AS fornecedor_nomefantasia",
            't1_.nome as fornecedor_razaosocial',
            't1_.cnpj as fornecedor_cnpj',
            't1_.pessoa as fornecedor_codigofornecedores',
            't1_.esperapagamentoseguradora as fornecedor_esperapagamentoseguradora',
            't1_.diasparavencimento as fornecedor_diasparavencimento',
            't1_.anotacao as fornecedor_anotacao',
            't1_.estabelecimentoid as fornecedor_estabelecimentoid',
        ));
        // Join com itens de faturamento
        $queryBuilder->leftJoin('t0_', 'servicos.servicos', 't2_', 't0_.itemfaturamento = t2_.id and t0_.tenant = t2_.tenant'); 
        $queryBuilder->addSelect(array(
            't2_.id AS itemfaturamento_servico',
            't2_.descricao AS itemfaturamento_descricaoservico',
            'COALESCE(t2_.bloqueado, 0) AS itemfaturamento_bloqueado',
        ));
                
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant,$id_grupoempresarial, $filter);

        return [$queryBuilder, $binds];
    }

    /**
     * Sobrescrito para substituir campo 'fornecedor'(view de fornecedores) por id(Da tabelade ns.pessoas)
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null){
        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant,$id_grupoempresarial, $filter);

        // Sobrescrição da função: Localiza referencia da view (fornecedores) e muda para referencia da tabela ns.pessoas(id)
        $sql = $queryBuilder->getSQL();
        $sql = str_replace("t1_.fornecedor", "t1_.id", $sql);
        // Fim de sobrescrição

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute($binds);
        
        $joins = ['fornecedor', 'itemfaturamento', ];       
        
        $result = array_map(function($row) use($joins){
            if(count($joins) > 0){
                foreach ($row as $key => $value) {
                    $parts = explode("_", $key);                    
                    $prefix = array_shift($parts);

                    if (in_array($prefix , $joins)) {
                        $row[$prefix][join("_",$parts)] = $value;
                        unset($row[$key]);
                    }
                }
            }
                        return $row;
        },$stmt->fetchAll());
        
        return $result;
    }

    public function getValorTotalAutorizadoApolices($tenant, $id_grupoempresarial, $id_atc)
    {
        
        $sql = 'SELECT SUM(COALESCE(VALORAUTORIZADO, 0)) AS TOTAL_AUTORIZADO_APOLICE
                FROM CRM.ATCSDADOSSEGURADORAS
                WHERE TENANT = :tenant
                  AND ID_GRUPOEMPRESARIAL = :grupoempresarial
                  AND NEGOCIO = :atc;';

        $bindQuery = [
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial,
            'atc' => $id_atc
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchColumn();
        return $data; 
    } 


    public function getValorTotalAutorizadoOrcamentos($tenant, $id_grupoempresarial, $id_atc)
    {
        
        $sql = 'SELECT  SUM(COALESCE(ORC.VALORRECEBER, 0)) AS TOTAL_AUTORIZADO_ORCAMENTO
                FROM CRM.ORCAMENTOS ORC
                JOIN CRM.PROPOSTASITENS PI ON  ORC.PROPOSTAITEM = PI.PROPOSTAITEM  AND ORC.TENANT = PI.TENANT AND ORC.GRUPOEMPRESARIAL = PI.ID_GRUPOEMPRESARIAL
                WHERE ORC.TENANT = :tenant
                  AND ORC.GRUPOEMPRESARIAL = :grupoempresarial
                  AND ORC.PROPOSTAITEMFAMILIA IS NULL 
                  AND ORC.PROPOSTAITEMFUNCAO IS NULL 
                  AND COALESCE(ORC.EXECUCAODESERVICO, FALSE) = FALSE
                  AND PI.NEGOCIO = :atc;';

        $bindQuery = [
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial,
            'atc' => $id_atc
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchColumn();
        return $data; 

    }

    /**
     * @param string  $atc
     * @param string  $fornecedor
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @return array 
     * @throws \Exception
     */
    public function findAllOrcamentosFichafinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial)
    {
        $sql_1 = "
        select 
            (case when 
                coalesce (ORC.execucaodeservico,false) = true 
                then 'Execução do Serviço' else PI.nome end
            ) as nome,
            ORC.*,
            PI.itemdefaturamentovalor,
            PI.itemcontrato,
            PI.observacaocomposicao,
            AREAS.permiteorcamentozerado,
            FORN.estabelecimentoid
        from crm.orcamentos ORC
        join crm.propostasitens PI on ORC.propostaitem = PI.propostaitem and ORC.tenant = PI.tenant and ORC.grupoempresarial = PI.id_grupoempresarial 
        join crm.atcs ATCS on PI.negocio = ATCS.negocio and PI.tenant = ATCS.tenant and PI.id_grupoempresarial = ATCS.id_grupoempresarial
        join crm.atcsareas AREAS on ATCS.area = AREAS.negocioarea and ATCS.tenant = AREAS.tenant and ATCS.id_grupoempresarial = AREAS.id_grupoempresarial

        join ns.vw_fornecedores_v3 FORN on ORC.fornecedor = FORN.fornecedor and ORC.tenant = FORN.tenant and ORC.grupoempresarial = FORN.id_grupoempresarial

        where ORC.tenant = :tenant
        and ORC.grupoempresarial = :grupoempresarial
        and PI.negocio = :atc 
        and ORC.fornecedor = :fornecedor 
        and ORC.propostaitemfamilia is null
        and ORC.propostaitemfuncao is null

        union all 

        select F.descricao as nome,
            ORC.*,
            PI.itemdefaturamentovalor,
            PI.itemcontrato,
            PI.observacaocomposicao,
            AREAS.permiteorcamentozerado,
            FORN.estabelecimentoid
        from crm.orcamentos ORC
        join crm.propostasitens PI on ORC.propostaitem = PI.propostaitem and ORC.tenant = PI.tenant and ORC.grupoempresarial = PI.id_grupoempresarial 
        join crm.atcs ATCS on PI.negocio = ATCS.negocio and PI.tenant = ATCS.tenant and PI.id_grupoempresarial = ATCS.id_grupoempresarial
        join crm.atcsareas AREAS on ATCS.area = AREAS.negocioarea and ATCS.tenant = AREAS.tenant and ATCS.id_grupoempresarial = AREAS.id_grupoempresarial
        join crm.propostasitensfamilias PIF on PIF.propostaitem = PI.propostaitem and PIF.tenant = PI.tenant and PIF.grupoempresarial = PI.id_grupoempresarial 
        join estoque.familias F on F.familia = PIF.familia and F.tenant = PIF.tenant 
        join ns.vw_fornecedores_v3 FORN on ORC.fornecedor = FORN.fornecedor and ORC.tenant = FORN.tenant and ORC.grupoempresarial = FORN.id_grupoempresarial
        where ORC.tenant = :tenant
        and ORC.grupoempresarial = :grupoempresarial
        and PI.negocio = :atc 
        and ORC.fornecedor = :fornecedor 
        and ORC.propostaitemfamilia is not null
        and ORC.propostaitemfuncao is null


        union all 

        select FU.descricao as nome,
            ORC.*,
            PI.itemdefaturamentovalor,
            PI.itemcontrato,
            PI.observacaocomposicao,
            AREAS.permiteorcamentozerado,
            FORN.estabelecimentoid
        from crm.orcamentos ORC
        join crm.propostasitens PI on ORC.propostaitem = PI.propostaitem and ORC.tenant = PI.tenant and ORC.grupoempresarial = PI.id_grupoempresarial 
        join crm.atcs ATCS on PI.negocio = ATCS.negocio and PI.tenant = ATCS.tenant and PI.id_grupoempresarial = ATCS.id_grupoempresarial
        join crm.atcsareas AREAS on ATCS.area = AREAS.negocioarea and ATCS.tenant = AREAS.tenant and ATCS.id_grupoempresarial = AREAS.id_grupoempresarial
        join crm.propostasitensfuncoes PIF on PIF.propostaitem = PI.propostaitem and PIF.tenant = PI.tenant and PIF.grupoempresarial = PI.id_grupoempresarial 
        join gp.funcoes FU on FU.funcao = PIF.funcao and FU.tenant = PIF.tenant 
        join ns.vw_fornecedores_v3 FORN on ORC.fornecedor = FORN.fornecedor and ORC.tenant = FORN.tenant and ORC.grupoempresarial = FORN.id_grupoempresarial
        where ORC.tenant = :tenant
        and ORC.grupoempresarial = :grupoempresarial
        and PI.negocio = :atc 
        and ORC.fornecedor = :fornecedor 
        and ORC.propostaitemfamilia is null
        and ORC.propostaitemfuncao is not null;";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("atc", $atc);
        $stmt_1->bindValue("fornecedor", $fornecedor);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("grupoempresarial", $id_grupoempresarial);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Verifica se o fornecedor possui orçamentos no atendimento com contrato gerado 
     * @return boolean
     */
    public function fornecedorPossuiOrcamentoComContrato($tenant, $id_grupoempresarial, $atc, $fornecedor){
        $sql_1 = "
            select count(t0_.responsabilidadefinanceira) as qtd
            from crm.responsabilidadesfinanceirasvalores t0_
            left join crm.responsabilidadesfinanceiras t1_ 
                on (t0_.responsabilidadefinanceira = t1_.responsabilidadefinanceira and t0_.tenant = t1_.tenant )
            left join crm.orcamentos t2_ 
                on (t1_.orcamento = t2_.orcamento and t1_.tenant = t2_.tenant )
            left join crm.fornecedoresenvolvidos t3_ 
                on (t2_.fornecedor = t3_.fornecedor and t2_.tenant = t3_.tenant)
            where t0_.tenant = :tenant
              and t0_.id_grupoempresarial = :id_grupoempresarial
              and t2_.fornecedor = :fornecedor
              and t1_.negocio = :atc;";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("atc", $atc);
        $stmt_1->bindValue("fornecedor", $fornecedor);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("id_grupoempresarial", $id_grupoempresarial);
        $stmt_1->execute();
        $result = $stmt_1->fetch();

        return $result['qtd'] > 0;
    }

    public function findOrcamento($id, $tenant, $id_grupoempresarial)
    {

        $where = "WHERE t0_.orcamento = :id
                        AND t0_.tenant = :tenant  
                        AND t0_.grupoempresarial = :grupoempresarial 
        ";
        $sql = '
        select
        t0_.orcamento as "orcamento" ,
        t0_.atc as "atc" ,
        t0_.composicao as "composicao" ,
        t0_.familia as "familia" ,
        t0_.descricaomanual as "descricaomanual" ,
        t0_.descricao as "descricao" ,
        t0_.custo as "custo" ,
        t0_.valorunitario as "valorunitario" ,
        t0_.quantidade as "quantidade" ,
        t0_.servicotipo as "servicotipo" ,
        t0_.propostaitem as "propostaitem" ,
        t0_.valor as "valor" ,
        t0_.status as "status" ,
        t0_.created_at as "created_at" ,
        t0_.created_by as "created_by" ,
        t0_.updated_at as "updated_at" ,
        t0_.updated_by as "updated_by" ,
        t0_.tenant as "tenant" ,
        t0_.acrescimo as "acrescimo" ,
        t0_.desconto as "desconto" ,
        t0_.descontoglobal as "descontoglobal" ,
        t0_.acrescimomotivo as "acrescimomotivo" ,
        t0_.descontomotivo as "descontomotivo" ,
        t0_.motivo as "motivo" ,
        t0_.execucaodeservico as "execucaodeservico" ,
        t0_.faturar as "faturar" ,
        t0_.faturamentotipo as "faturamentotipo" ,
        t0_.fornecedorterceirizado as "fornecedorterceirizado" ,
        t0_.valorreceber as "valorreceber" ,
        t0_.tipoatualizacao as "tipoatualizacao" ,
        t0_.grupoempresarial as "id_grupoempresarial" ,
        t1_.id as "t1_fornecedor" ,
        t1_.nomefantasia as "t1_nomefantasia" ,
        t1_.nome as "t1_razaosocial" ,
        t1_.cnpj as "t1_cnpj" ,
        t1_.pessoa as "t1_codigofornecedores" ,
        fornecedoressuspensos.tipo as suspensao_tipo,
        case
            when fornecedoressuspensos.tipo = 1 then 1::text
            when fornecedoressuspensos.tipo = 2 then 2::text
            else 0::text
        end as t1_status,
        t1_.esperapagamentoseguradora as "t1_esperapagamentoseguradora" ,
        t1_.diasparavencimento as "t1_diasparavencimento" ,
        t1_.estabelecimentoid as "t1_estabelecimentoid" ,
        t1_.funcionarioativado as "t1_funcionarioativado" ,
        t1_.contribuinteindividualativado as "t1_contribuinteindividualativado" ,
        t1_.anotacao as "t1_anotacao" ,
        t2_.servico as "t2_servico" ,
        t2_.descricao as "t2_descricaoservico" ,
        coalesce(t2_.bloqueado, 0) as "t2_bloqueado"
    from
        crm.orcamentos t0_
    left join ns.pessoas t1_ on
        t1_.id = t0_.fornecedor
        and t0_.tenant = t1_.tenant
    left join ns.enderecos enderecos on
        enderecos.id_pessoa = t1_.id
        and enderecos.tipoendereco = 0
        and enderecos.tenant = t1_.tenant
    left join ns.fornecedoressuspensos fornecedoressuspensos on
        fornecedoressuspensos.fornecedor_id = t1_.id
        and fornecedoressuspensos.tenant = t1_.tenant
    left join servicos.servicos t2_ on
        t2_.id = t0_.itemfaturamento
        and t0_.tenant = t2_.tenant
    ' . $where;

        $data = $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial,
        ])->fetch();

        $data = $this->adjustQueryData($data);

        return $data;
    }




    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity
     * @return string 
     * @throws \Exception
     */
    public function insert($tenant, $id_grupoempresarial, $logged_user,  \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity)
    {
        $sql_1 = "SELECT mensagem
            FROM crm.api_orcamentoNovo_v7(row(
                            :tenant,
                            :grupoempresarial,
                            :created_by,
                            :fornecedor,
                            :atc,
                            :composicao,
                            :familia,
                            :proposta,
                            :propostaitem,
                            :custo,
                            :valorunitario,
                            :quantidade,
                            :valorreceber,
                            :desconto,
                            :descontomotivo,
                            :acrescimo,
                            :acrescimomotivo,
                            :descricaomanual,
                            :descricao,
                            :itemfaturamento,
                            :faturamentotipo,
                            :tipoatualizacao
                        )::crm.torcamentonovo_v7
            );";

        $stmt_1 = $this->getConnection()->prepare($sql_1);


        $stmt_1->bindValue("tenant", $tenant);

        $stmt_1->bindValue("grupoempresarial", $id_grupoempresarial);

        $stmt_1->bindValue("created_by", json_encode($logged_user));

        $stmt_1->bindValue("fornecedor", ($entity->getFornecedor()) ? $entity->getFornecedor()->getFornecedor() : NULL);

        $stmt_1->bindValue("atc", $entity->getAtc());

        $stmt_1->bindValue("composicao", $entity->getComposicao());

        $stmt_1->bindValue("familia", $entity->getFamilia());

        $stmt_1->bindValue("proposta", $entity->getProposta());

        $stmt_1->bindValue("propostaitem", $entity->getPropostaitem());

        $stmt_1->bindValue("custo", $entity->getCusto());

        $stmt_1->bindValue("valorunitario", $entity->getValorunitario());

        $stmt_1->bindValue("quantidade", $entity->getQuantidade());

        $stmt_1->bindValue("valorreceber", $entity->getValorreceber());

        $stmt_1->bindValue("desconto", $entity->getDesconto());

        $stmt_1->bindValue("descontomotivo", $entity->getDescontomotivo());

        $stmt_1->bindValue("acrescimo", $entity->getAcrescimo());

        $stmt_1->bindValue("acrescimomotivo", $entity->getAcrescimomotivo());

        $stmt_1->bindValue("descricaomanual", $entity->getDescricaomanual(), \PDO::PARAM_BOOL);

        $stmt_1->bindValue("descricao", $entity->getDescricao());

        $stmt_1->bindValue("itemfaturamento", ($entity->getItemfaturamento()) ? $entity->getItemfaturamento()->getServico() : NULL);

        $stmt_1->bindValue("faturamentotipo", $entity->getFaturamentotipo());

        $stmt_1->bindValue("tipoatualizacao", $entity->getTipoatualizacao());

        $stmt_1->execute();
        $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entity);

        $retorno = $resposta;

        $entity->setOrcamento($resposta);

        $retorno = $this->findOrcamento($retorno, $tenant, $id_grupoempresarial);


        return $retorno;
    }


    public function find($id, $tenant, $id_grupoempresarial)
    {
        $where = $this->buildWhere();

        $sql = "
                SELECT
                    t0_.orcamento as orcamento ,
                    t0_.atc as atc ,
                    t0_.composicao as composicao ,
                    t0_.familia as familia ,
                    t0_.descricaomanual as descricaomanual ,
                    t0_.descricao as descricao ,
                    t0_.custo as custo ,
                    t0_.valorunitario as valorunitario ,
                    t0_.quantidade as quantidade ,
                    t0_.servicotipo as servicotipo ,
                    t0_.propostaitem as propostaitem ,
                    t0_.valor as valor ,
                    t0_.status as status ,
                    t0_.created_at as created_at ,
                    t0_.created_by as created_by ,
                    t0_.updated_at as updated_at ,
                    t0_.updated_by as updated_by ,
                    t0_.tenant as tenant ,
                    t0_.acrescimo as acrescimo ,
                    t0_.desconto as desconto ,
                    t0_.descontoglobal as descontoglobal ,
                    t0_.acrescimomotivo as acrescimomotivo ,
                    t0_.descontomotivo as descontomotivo ,
                    t0_.motivo as motivo ,
                    t0_.execucaodeservico as execucaodeservico ,
                    t0_.faturar as faturar ,
                    t0_.faturamentotipo as faturamentotipo ,
                    t0_.fornecedorterceirizado as fornecedorterceirizado ,
                    t0_.valorreceber as valorreceber ,
                    t0_.tipoatualizacao as tipoatualizacao ,
                    t0_.grupoempresarial as id_grupoempresarial ,
                    t1_.id as t1_fornecedor ,
                    t1_.nomefantasia as t1_nomefantasia ,
                    t1_.nome as t1_razaosocial ,
                    t1_.cnpj as t1_cnpj ,
                    municipios.nome as t1_municipionome ,
                    t1_.pessoa as t1_codigofornecedores ,
			        CASE
			            WHEN fornecedoressuspensos.tipo = 1 THEN '1'::text
			            WHEN fornecedoressuspensos.tipo = 2 THEN '2'::text
			            ELSE '0'::text
			        END as t1_status ,
                    t1_.esperapagamentoseguradora as t1_esperapagamentoseguradora ,
                    t1_.diasparavencimento as t1_diasparavencimento ,
                    t1_.estabelecimentoid as t1_estabelecimentoid ,
                    t1_.funcionarioativado as t1_funcionarioativado ,
                    t1_.contribuinteindividualativado as t1_contribuinteindividualativado ,
                    t1_.anotacao as t1_anotacao ,
                    t1_.id_formapagamento_fornecedor as t1_formapagamento ,
                    t2_.servico as t2_servico ,
                    t2_.descricaoservico as t2_descricaoservico ,
                    t2_.servicobloqueado as t2_bloqueado 
                FROM crm.orcamentos t0_ 
                LEFT JOIN ns.pessoas t1_ on t1_.id = t0_.fornecedor AND t1_.tenant = t0_.tenant
                LEFT JOIN ns.enderecos enderecos ON enderecos.id_pessoa = t1_.id AND enderecos.tipoendereco = 0 AND enderecos.tenant = t1_.tenant
                LEFT JOIN ns.fornecedoressuspensos fornecedoressuspensos ON fornecedoressuspensos.fornecedor_id = t1_.id AND fornecedoressuspensos.tenant = t1_.tenant
                LEFT JOIN ns.pessoaslogos pl ON pl.id_pessoa = t1_.id AND pl.tenant = t1_.tenant AND pl.ativo = true AND pl.tenant = t1_.tenant
                LEFT JOIN ns.municipios municipios ON municipios.ibge::text = enderecos.ibge::text                     
                LEFT JOIN servicos.vwservicosgrupoempresarial_v2 t2_ ON t0_.itemfaturamento = t2_.servico   and t0_.tenant = t2_.tenant   and t0_.grupoempresarial = t2_.grupoempresarial
                    
        {$where}";

        $data = $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'tenant' => $tenant,
            'id_grupoempresarial' => $id_grupoempresarial
        ])->fetch();

        $data = $this->adjustQueryData($data);

        return $data;
    }

}
