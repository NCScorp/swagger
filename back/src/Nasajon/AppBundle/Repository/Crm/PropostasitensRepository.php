<?php


namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\PropostasitensRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;


/*
 *Sobrescrito para não passar o updated_by no delete
 * Método findAllQueryBuilder sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 * Método findAll sobrescrito para mudar referencia do guid do fornecedor para id de ns pessoas.
 */
class PropostasitensRepository extends ParentRepository {

    /**
    * @param string  $tenant
    * @param string  $proposta
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity
    * @return string 
    * @throws \Exception
    */
    public function delete($tenant, $proposta, $logged_user,  \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity){

                    
        $sql_1 = "SELECT mensagem
        FROM crm.api_propostaItemExcluir_v2(row(
                        :propostaitem,
                        :tenant,
                        :proposta,
                        :deleted_by
                    )::crm.tpropostaitemexcluir_v2
        );";

        $stmt_1 = $this->getConnection()->prepare($sql_1);      
        $stmt_1->bindValue("propostaitem", $entity->getPropostaitem()); 
        $stmt_1->bindValue("tenant", $tenant);          
        $stmt_1->bindValue("proposta", $proposta);
        $stmt_1->bindValue("deleted_by", json_encode($logged_user));
        $stmt_1->execute();
        $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entity);
        $retorno = $resposta;                     
        return $retorno;
    }

    public function findAllQueryBuilder($tenant, $atc, $proposta, $id_grupoempresarial, Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.propostaitem as propostaitem',
            't0_.nomeservicoalterado as nomeservicoalterado',
            't0_.nome as nome',
            't0_.previsaodatahorainicio as previsaodatahorainicio',
            't0_.previsaodatahorafim as previsaodatahorafim',
            't0_.itemdefaturamentovalor as itemdefaturamentovalor',
            't0_.quantidade as quantidade',
            't0_.camposcustomizados as camposcustomizados',
            't0_.escolhacliente as escolhacliente',
            't0_.observacaocomposicao as observacaocomposicao',
            't0_.id_apolice as id_apolice',
            't0_.valorapolice as valorapolice',
            't0_.created_at as created_at',
        );
        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.propostasitens', 't0_');
        $queryBuilder->leftJoin('t0_', 'crm.propostascapitulos', 't1_', 't0_.propostacapitulo = t1_.propostacapitulo and t0_.tenant = t1_.tenant');
        $queryBuilder->addSelect(array(
            't1_.propostacapitulo as propostacapitulo_propostacapitulo',
            't1_.nome as propostacapitulo_nome',
            't1_.proposta as propostacapitulo_proposta',
            't1_.pai as propostacapitulo_pai',
            't1_.possuifilho as propostacapitulo_possuifilho',
        ));
        $queryBuilder->leftJoin('t0_', 'crm.composicoes', 't3_', 't0_.composicao = t3_.composicao and t0_.tenant = t3_.tenant');
        $queryBuilder->addSelect(array(
            't3_.composicao as composicao_composicao',
            't3_.nome as composicao_nome',
            't3_.codigo as composicao_codigo',
            't3_.descricao as composicao_descricao',
        ));
        $queryBuilder->leftJoin('t0_', 'crm.propostasitensfamilias', 't9_', 't0_.propostaitem = t9_.propostaitemfamilia and t0_.tenant = t9_.tenant');
        $queryBuilder->addSelect(array(
            't9_.propostaitemfamilia as propostasitensfamilias_propostaitemfamilia',
            't9_.quantidade as propostasitensfamilias_quantidade',
            't9_.propostaitem as propostasitensfamilias_propostaitem',
            't9_.valor as propostasitensfamilias_valor',
        ));
        $queryBuilder->leftJoin('t0_', 'crm.propostasitensfuncoes', 't10_', 't0_.propostaitem = t10_.propostaitemfuncao and t0_.tenant = t10_.tenant');
        $queryBuilder->addSelect(array(
            't10_.propostaitemfuncao as propostasitensfuncoes_propostaitemfuncao',
            't10_.quantidade as propostasitensfuncoes_quantidade',
            't10_.propostaitem as propostasitensfuncoes_propostaitem',
        ));
        // $queryBuilder->leftJoin('t0_', 'gp.vw_tarefasordensservicos', 't4_', 't0_.tarefa = t4_.tarefa');
        $queryBuilder->leftJoin('t0_', 'gp.tarefas', 't4_', 't0_.tarefa = t4_.tarefa and t0_.tenant = t4_.tenant');
        $queryBuilder->addSelect(array(
            't4_.tarefa as tarefa_tarefa',
            't4_.previsaoinicio as tarefa_previsaoinicio',
            't4_.previsaotermino as tarefa_previsaotermino',
            // 't4_.situacaostr as tarefa_situacaostr',
            "CASE t4_.situacao
                WHEN 0 THEN 'Pendente'::text
                WHEN 1 THEN 'Aberto'::text
                WHEN 2 THEN 'Em Andamento'::text
                WHEN 3 THEN 'Parado'::text
                WHEN 4 THEN 'Fechado'::text
                WHEN 5 THEN 'Cancelado'::text
                ELSE NULL::text
            END AS situacaostr",
            't4_.situacao as tarefa_situacao',
            't4_.numero as tarefa_numerotarefa',
            // 't4_.possui_ordemservico as tarefa_possui_ordemservico',
            "( SELECT COALESCE(count(tos.ordemservico), 0::bigint) > 0
                FROM gp.tarefasordensservicos tos
                WHERE tos.tarefa = t4_.tarefa AND tos.tenant = t4_.tenant AND tos.ordemservico IS NOT NULL) AS possui_ordemservico"
        ));
        // $queryBuilder->leftJoin('t0_', 'ns.vw_fornecedores', 't6_', 't0_.fornecedor = t6_.fornecedor');
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't6_', 't0_.fornecedor = t6_.id and t0_.tenant = t6_.tenant');
        $queryBuilder->addSelect(array(
            // 't6_.fornecedor as fornecedor_fornecedor',
            't6_.id as fornecedor_fornecedor',
            // 't6_.nomefantasia as fornecedor_nomefantasia',
            "CASE
                WHEN t6_.nomefantasia::text = ''::text THEN COALESCE(t6_.nome, 'Não informado'::character varying)::character varying(150)
                ELSE COALESCE(t6_.nomefantasia, t6_.nome, 'Não informado'::character varying)::character varying(150)
            END AS fornecedor_nomefantasia",
            't6_.nome as fornecedor_razaosocial',
            't6_.cnpj as fornecedor_cnpj',
            't6_.pessoa as fornecedor_codigofornecedores',
            // "'t6_.status' as fornecedor_status",
            't6_.esperapagamentoseguradora as fornecedor_esperapagamentoseguradora',
            't6_.diasparavencimento as fornecedor_diasparavencimento',
            't6_.anotacao as fornecedor_anotacao',
            't6_.estabelecimentoid as fornecedor_estabelecimentoid',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.fornecedoressuspensos', 'fornecedoressuspensos', 't6_.id = fornecedoressuspensos.fornecedor_id and t6_.tenant = fornecedoressuspensos.tenant');
        $queryBuilder->addSelect(array(
            // 't13_.orcamento as fornecedor_status',
            "CASE
                WHEN fornecedoressuspensos.tipo = 1 THEN '1'::text
                WHEN fornecedoressuspensos.tipo = 2 THEN '2'::text
                ELSE '0'::text
            END AS fornecedor_status",
            // CASE
            //     WHEN fornecedoressuspensos.tipo = 1 THEN '1'::text
            //     WHEN fornecedoressuspensos.tipo = 2 THEN '2'::text
            //     ELSE '0'::text
            // END AS status,
            // LEFT JOIN ns.fornecedoressuspensos fornecedoressuspensos ON fornecedoressuspensos.fornecedor_id = pessoas.id
        ));
        $queryBuilder->leftJoin('t0_', 'crm.orcamentos', 't11_', 't0_.servicoorcamento = t11_.orcamento and t0_.tenant = t11_.tenant');
        $queryBuilder->addSelect(array(
            't11_.orcamento as servicoorcamento_orcamento',
            't11_.valor as servicoorcamento_valor',
            't11_.acrescimo as servicoorcamento_acrescimo',
            't11_.desconto as servicoorcamento_desconto',
            't11_.motivo as servicoorcamento_motivo',
            't11_.status as servicoorcamento_status',
            't11_.faturar as servicoorcamento_faturar',
            't11_.updated_at as servicoorcamento_updated_at',
            't11_.valorreceber as servicoorcamento_valorreceber',
            't11_.tipoatualizacao as servicoorcamento_tipoatualizacao',
            't11_.execucaodeservico as servicoorcamento_execucaodeservico',
        ));
        // Adiciono 2 campos para verificar se a propostaitem possui familia e função. Isso será utilizado na Tree de pedidos do Front-end
        $queryBuilder->addSelect(array(
            "CASE
                WHEN (
                    select propostaitemfuncao
                    from crm.propostasitensfuncoes 
                    where propostaitem = t0_.propostaitem
                      and tenant = t0_.tenant
                    limit 1
                ) is not null THEN TRUE
                ELSE FALSE
            END as possuifuncoes",
            "CASE
                WHEN (
                    select propostaitemfamilia
                    from crm.propostasitensfamilias 
                    where propostaitem = t0_.propostaitem
                      and tenant = t0_.tenant
                    limit 1
                ) is not null THEN TRUE
                ELSE FALSE
            END as possuifamilias",
        ));
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $atc, $proposta, $id_grupoempresarial, $filter);
        return [$queryBuilder, $binds];
    }

    /**
     * @return array
     */
    public function findAll($tenant,$atc,$proposta,$id_grupoempresarial, Filter $filter = null){
        $this->validateOffset($filter);
        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant,$atc,$proposta,$id_grupoempresarial, $filter);
        $sql = $queryBuilder->getSQL();
        //localiza referencia da view (cliente) e muda para referencia da tabela ns.pessoas (id)
        $sql = str_replace("t6_.fornecedor", "t6_.id", $sql);
        $stmt = $this->getConnection()->prepare($sql);
        // $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $stmt->execute($binds);
        $joins = ['propostacapitulo', 'composicao', 'propostasitensfamilias', 'propostasitensfuncoes', 'tarefa', 'fornecedor', 'servicoorcamento'];
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
            $row['camposcustomizados'] = json_decode($row['camposcustomizados'], true);
            return $row;
        },$stmt->fetchAll());
        return $result;
    }

    /**
     * Sobrescrito para utilizar a função findQuery modificada.
     */
    public function find($id , $tenant, $atc, $proposta, $id_grupoempresarial){
    
        $where = $this->buildWhere();
        $data = $this->findQuery($where, [
                'id' => $id                    ,
                    'negocio' => $atc
                                    ,
                    'proposta' => $proposta
                                    ,
                    'tenant' => $tenant
                                    ,
                    'id_grupoempresarial' => $id_grupoempresarial
                                ])->fetch();

        $data = $this->adjustQueryData($data);
        
        return $data;
    }

    /**
     * Sobrescrito para utilizar a função findQuery modificada.
     */
    public function findBy(array $whereFields){
    
        $where = $this->buildWhere(array_keys($whereFields));
        $query = $this->findQuery($where, $whereFields);
        
        if( $query->rowCount() > 1 ){
            throw new \Doctrine\ORM\NonUniqueResultException();
        }
        
        $data = $query->fetch();
        $data = $this->adjustQueryData($data);
        
        return $data;
    }

    protected function findQuery(string $where, array $whereFields)
    {
        $sql = "SELECT
            t0_.propostaitem as \"propostaitem\" ,
            t0_.nomeservicoalterado as \"nomeservicoalterado\" ,
            t0_.nome as \"nome\" ,
            t0_.codigo as \"codigo\" ,
            t0_.descricao as \"descricao\" ,
            t0_.datahorainicio as \"datahorainicio\" ,
            t0_.datahorafim as \"datahorafim\" ,
            t0_.previsaodatahorainicio as \"previsaodatahorainicio\" ,
            t0_.previsaodatahorafim as \"previsaodatahorafim\" ,
            t0_.valor as \"valor\" ,
            t0_.quantidade as \"quantidade\" ,
            t0_.created_at as \"created_at\" ,
            t0_.created_by as \"created_by\" ,
            t0_.updated_at as \"updated_at\" ,
            t0_.tenant as \"tenant\" ,
            t0_.camposcustomizados as \"camposcustomizados\" ,
            t0_.escolhacliente as \"escolhacliente\" ,
            t0_.itemdefaturamentovalor as \"itemdefaturamentovalor\" ,
            t0_.id_grupoempresarial as \"id_grupoempresarial\" ,
            t0_.observacaocomposicao as \"observacaocomposicao\" ,
            t0_.id_apolice as \"id_apolice\" ,
            t0_.valorapolice as \"valorapolice\" ,
            t1_.propostacapitulo as \"t1_propostacapitulo\" ,
            t1_.nome as \"t1_nome\" ,
            t1_.proposta as \"t1_proposta\" ,
            t1_.pai as \"t1_pai\" ,
            t1_.possuifilho as \"t1_possuifilho\" ,
            t2_.proposta as \"t2_proposta\" ,
            t2_.valor as \"t2_valor\" ,
            t2_.numero as \"t2_numero\" ,
            t2_.negocio as \"t2_negocio\" ,
            t2_.updated_at as \"t2_updated_at\" ,
            t2_.status as \"t2_status\" ,
            t3_.composicao as \"t3_composicao\" ,
            t3_.nome as \"t3_nome\" ,
            t3_.codigo as \"t3_codigo\" ,
            t3_.descricao as \"t3_descricao\" ,
            t3_.servicotecnico as \"t3_servicotecnico\" ,
            t7_.propostaitemfamilia as \"t7_propostaitemfamilia\" ,
            t7_.quantidade as \"t7_quantidade\" ,
            t7_.propostaitem as \"t7_propostaitem\" ,
            t7_.valor as \"t7_valor\" ,
            t7_.familia as \"t7_familia\" ,
            -- t7_.itemcontrato as \"t7_itemcontrato\" ,
            -- t7_.itemcontratoAPagar as \"t7_itemcontratoAPagar\" ,
            t7_.templateproposta as \"t7_templateproposta\" ,
            t7_.templatepropostacomposicaofamilia as \"t7_templatepropostacomposicaofamilia\" ,
            t8_.propostaitemfuncao as \"t8_propostaitemfuncao\" ,
            t8_.quantidade as \"t8_quantidade\" ,
            t8_.propostaitem as \"t8_propostaitem\" ,
            t8_.funcao as \"t8_funcao\" ,
            -- t8_.itemcontrato as \"t8_itemcontrato\" ,
            -- t8_.itemcontratoAPagar as \"t8_itemcontratoAPagar\" ,
            t8_.templateproposta as \"t8_templateproposta\" ,
            t8_.templatepropostacomposicaofuncao as \"t8_templatepropostacomposicaofuncao\" ,
            t10_.templatepropostacapitulocomposicao as \"t10_templatepropostacomposicao\" ,
            t10_.templatepropostacapitulo as \"t10_templatepropostacapitulo\" ,
            t10_.composicao as \"t10_composicao\" ,
            t10_.nome as \"t10_nome\" ,
            t10_.descricao as \"t10_descricao\" ,
            t10_.observacao as \"t10_observacao\" ,
            t11_.orcamento as \"t11_orcamento\" ,
            t11_.fornecedor as \"t11_fornecedor\" ,
            t11_.propostaitem as \"t11_propostaitem\" ,
            t11_.propostaitemfamilia as \"t11_propostaitemfamilia\" ,
            t11_.propostaitemfuncao as \"t11_propostaitemfuncao\" ,
            t11_.itemfaturamento as \"t11_itemfaturamento\" ,
            t11_.valor as \"t11_valor\" ,
            t11_.acrescimo as \"t11_acrescimo\" ,
            t11_.desconto as \"t11_desconto\" ,
            t11_.motivo as \"t11_motivo\" ,
            t11_.status as \"t11_status\" ,
            t11_.faturar as \"t11_faturar\" ,
            t11_.updated_at as \"t11_updated_at\" ,
            t11_.valorreceber as \"t11_valorreceber\" ,
            t11_.tipoatualizacao as \"t11_tipoatualizacao\" ,
            -- t7_.datacriacao as \"t7_datacriacao\" ,
            t5_.created_at as \"t5_datacriacao\" ,
            t5_.negocio as \"t5_negocio\" ,
            t5_.codigo as \"t5_codigo\" ,
            t5_.nome as \"t5_nome\" ,
            t5_.area as \"t5_area\" ,
            t5_.negociopai as \"t5_negociopai\" ,
            t5_.cliente as \"t5_cliente\" ,
            t5_.camposcustomizados as \"t5_camposcustomizados\" ,
            t5_.localizacaopais as \"t5_localizacaopais\" ,
            t5_.localizacaoestado as \"t5_localizacaoestado\" ,
            t5_.localizacaomunicipio as \"t5_localizacaomunicipio\" ,
            t5_.status as \"t5_status\" ,
            t5_.possuiseguradora as \"t5_possuiseguradora\" ,
            -- t7_.contrato as \"t7_contrato\" ,
            t5_.projeto as \"t5_projeto\" ,
            -- t7_.dataedicao as \"t7_dataedicao\" ,
            t5_.updated_at as \"t5_dataedicao\" ,
            t5_.created_by as \"t5_created_by\" ,
            t6_.id as \"t6_fornecedor\" ,
            CASE
                WHEN t6_.nomefantasia::text = ''::text THEN COALESCE(t6_.nome, 'Não informado'::character varying)::character varying(150)
                ELSE COALESCE(t6_.nomefantasia, t6_.nome, 'Não informado'::character varying)::character varying(150)
            END as \"t6_nomefantasia\" ,
            t6_.nome as \"t6_razaosocial\" ,
            t6_.cnpj as \"t6_cnpj\" ,
            t6_.pessoa as \"t6_codigofornecedores\" ,
            -- t6_.status as \"t8_status\" ,
            t6_.esperapagamentoseguradora as \"t6_esperapagamentoseguradora\" ,
            t6_.diasparavencimento as \"t6_diasparavencimento\" ,
            t6_.estabelecimentoid as \"t6_estabelecimentoid\" ,
            t6_.anotacao as \"t6_anotacao\" ,
            t4_.tarefa as \"t4_tarefa\" ,
            t4_.previsaoinicio as \"t4_previsaoinicio\" ,
            t4_.previsaotermino as \"t4_previsaotermino\" ,
            -- t4_.situacaostr as \"t4_situacaostr\" ,
            CASE t4_.situacao
                WHEN 0 THEN 'Pendente'::text
                WHEN 1 THEN 'Aberto'::text
                WHEN 2 THEN 'Em Andamento'::text
                WHEN 3 THEN 'Parado'::text
                WHEN 4 THEN 'Fechado'::text
                WHEN 5 THEN 'Cancelado'::text
                ELSE NULL::text
            END AS t4_situacaostr,
            t4_.situacao as \"t4_situacao\" ,
            -- t4_.numerotarefa as \"t4_numerotarefa\" ,
            t4_.numero as \"t4_numerotarefa\" ,
            -- t4_.possui_ordemservico as \"t4_possui_ordemservico\" ,
            ( SELECT COALESCE(count(tos.ordemservico), 0::bigint) > 0
                FROM gp.tarefasordensservicos tos
                WHERE tos.tarefa = t4_.tarefa AND tos.tenant = t4_.tenant 
                AND tos.ordemservico IS NOT NULL) AS t4_possui_ordemservico
            -- t5_.itemcontrato as \"t5_itemcontrato\" ,
            -- t5_.contrato as \"t5_contrato\" ,
            -- t6_.itemcontrato as \"t6_itemcontrato\" ,
            -- t6_.contrato as \"t6_contrato\" 

        FROM crm.propostasitens t0_
            LEFT JOIN crm.propostascapitulos t1_ ON t0_.propostacapitulo = t1_.propostacapitulo and t0_.tenant = t1_.tenant 
            LEFT JOIN crm.propostas t2_ ON t0_.proposta = t2_.proposta and t0_.tenant = t2_.tenant
            LEFT JOIN crm.composicoes t3_ ON t0_.composicao = t3_.composicao and t0_.tenant = t3_.tenant
            LEFT JOIN crm.propostasitensfamilias t7_ ON t0_.propostaitem = t7_.propostaitemfamilia and t0_.tenant = t7_.tenant
            LEFT JOIN crm.propostasitensfuncoes t8_ ON t0_.propostaitem = t8_.propostaitemfuncao and t0_.tenant = t8_.tenant
            LEFT JOIN crm.templatespropostascapituloscomposicoes t10_ ON t0_.templatepropostacomposicao = t10_.templatepropostacapitulocomposicao and t0_.tenant = t10_.tenant
            LEFT JOIN crm.orcamentos t11_ ON t0_.servicoorcamento = t11_.orcamento and t0_.tenant = t11_.tenant
            -- LEFT JOIN crm.vwnegociossimplesecomseguradora_v6 t7_ ON t0_.negocio = t7_.negocio
            LEFT JOIN crm.atcs t5_ ON t0_.negocio = t5_.negocio and t0_.tenant = t5_.tenant
            LEFT JOIN ns.pessoas t6_ ON t0_.fornecedor = t6_.id and t0_.tenant = t6_.tenant
            -- LEFT JOIN gp.vw_tarefasordensservicos t4_ ON t0_.tarefa = t4_.tarefa
            LEFT JOIN gp.tarefas t4_ ON t0_.tarefa = t4_.tarefa and t0_.tenant = t4_.tenant
        {$where}" ;

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    public function localizaContratoPagamento($tenant, $atcId, $fornecedorId)
    {
        $sql_1 = "select
            distinct t1_.contratoapagar,
            t4_.tipocontrato,
            t3_.fornecedor
        from crm.responsabilidadesfinanceirasvalores t1_
        join crm.responsabilidadesfinanceiras t2_ on ( t1_.responsabilidadefinanceira = t2_.responsabilidadefinanceira and t1_.tenant = t2_.tenant)
        join crm.orcamentos t3_ on ( t2_.orcamento = t3_.orcamento and t1_.tenant = t3_.tenant)
        join financas.contratos t4_ on ( t1_.contratoapagar = t4_.contrato and t1_.tenant = t4_.tenant )
        where t1_.tenant = :tenant
        and t1_.contratoapagar is not null
        and t3_.fornecedor = :fornecedor
        and t2_.negocio = :negocio;";

        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue(":tenant", $tenant);
        $stmt_1->bindValue(":fornecedor", $fornecedorId); 
        $stmt_1->bindValue(":negocio", $atcId);
        $stmt_1->execute();
        
        $contratos = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);

        return $contratos;
    }

    public function findSemProposta($propostaitem, $tenant, $negocio, $id_grupoempresarial)
    {
        $sql_1 = "select propostaitem, composicao, proposta from crm.propostasitens where 
        tenant = :tenant and negocio = :negocio and 
        id_grupoempresarial = :id_grupoempresarial and propostaitem = :propostaitem;";

        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue(":tenant", $tenant);
        $stmt_1->bindValue(":propostaitem", $propostaitem); 
        $stmt_1->bindValue(":negocio", $negocio); 
        $stmt_1->bindValue(":id_grupoempresarial", $id_grupoempresarial); 
        $stmt_1->execute();
        
        $propostasitem = $stmt_1->fetch(\PDO::FETCH_ASSOC);
        return $propostasitem;
    }
}