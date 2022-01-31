<?php

/*
  Sobrescrito para remover duplicados
 */

namespace Nasajon\AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\Ns\AdvertenciasRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

/**
 * Método findAllQueryBuilder sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 * Método findAll sobrescrito para mudar referencia do guid do fornecedor.
 */
class AdvertenciasRepository extends ParentRepository {
  
  /**
   * @return array
  */
  public function findAll($tenant,$id_grupoempresarial, Filter $filter = null) {

    // $result = parent::findAll($tenant, $id_grupoempresarial, $filter);

    $this->validateOffset($filter);
    list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant,$id_grupoempresarial, $filter);
    $sql = $queryBuilder->getSQL();
    //localiza referencia da view (cliente) e muda para referencia da tabela ns.pessoas (id)
    $sql = str_replace("t1_.fornecedor", "t1_.id", $sql);
    $stmt = $this->getConnection()->prepare($sql);
    // $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
    $stmt->execute($binds);
    $joins = ['fornecedores', ];
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

    // Removendo duplicidade
    return array_values(array_unique($result,SORT_REGULAR));
  }

  public function findAllQueryBuilder($tenant,$id_grupoempresarial, Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
                            't0_.advertencia as advertencia',
                            't0_.fornecedor_id as fornecedor',
                            't0_.nome as nome',
                            't0_.created_at as created_at',
                            't0_.motivo as motivo',
                            't0_.status as status',
                            't0_.id_grupoempresarial as id_grupoempresarial',
        );
  
        if( $filter && empty($filter->getOffset()) ){
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('ns.advertencias', 't0_');
        // $queryBuilder->leftJoin('t0_', 'ns.vw_fornecedores_v2', 't1_', 't0_.fornecedor_id = t1_.fornecedor'); 
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't1_', 't0_.fornecedor_id = t1_.id and t0_.tenant = t1_.tenant'); 
        $queryBuilder->addSelect(array(
            // 't1_.fornecedor as fornecedores_fornecedor',
            't1_.id as fornecedores_fornecedor',
            // 't1_.nomefantasia as fornecedores_nomefantasia',
            "CASE
                WHEN t1_.nomefantasia::text = ''::text THEN COALESCE(t1_.nome, 'Não informado'::character varying)::character varying(150)
                ELSE COALESCE(t1_.nomefantasia, t1_.nome, 'Não informado'::character varying)::character varying(150)
            END AS fornecedores_nomefantasia",
            't1_.nome as fornecedores_razaosocial',
            't1_.cnpj as fornecedores_cnpj',
            't1_.pessoa as fornecedores_codigofornecedores',
            // 't1_.status as fornecedores_status', //movido para o novo join abaixo
            't1_.esperapagamentoseguradora as fornecedores_esperapagamentoseguradora',
            't1_.diasparavencimento as fornecedores_diasparavencimento',
            't1_.anotacao as fornecedores_anotacao',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.fornecedoressuspensos', 'fornecedoressuspensos', 't1_.id = fornecedoressuspensos.fornecedor_id  and t1_.tenant = fornecedoressuspensos.tenant');
        $queryBuilder->addSelect(array(
            "CASE
                WHEN fornecedoressuspensos.tipo = 1 THEN '1'::text
                WHEN fornecedoressuspensos.tipo = 2 THEN '2'::text
                ELSE '0'::text
            END AS fornecedor_status",
        ));

        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant,$id_grupoempresarial, $filter);
        return [$queryBuilder, $binds];
    }
}