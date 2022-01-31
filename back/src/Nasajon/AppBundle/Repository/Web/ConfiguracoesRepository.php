<?php


namespace Nasajon\AppBundle\Repository\Web;

use Nasajon\MDABundle\Repository\AbstractRepository; 
use Nasajon\MDABundle\Repository\Web\ConfiguracoesRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
/**
* ConfiguracoesRepository
*
*/
class ConfiguracoesRepository extends ParentRepository
{
 

         public function findAllQueryBuilder($tenant, Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
                                't0_.configuracao as configuracao',
                                't0_.chave as chave',
                                't0_.valor as valor',
                                't0_.tenant as tenant',
                                't0_.sistema as sistema',
                                't0_.id_grupoempresarial as id_grupoempresarial',
            );
         
        if( $filter && empty($filter->getOffset()) ){
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
                $queryBuilder->select($selectArray);
        $queryBuilder->from('web.configuracoes', 't0_');
        $queryBuilder->leftJoin('t0_', 'ns.gruposempresariais', 't1_', 't0_.tenant = t1_.tenant  and t0_.id_grupoempresarial = t1_.grupoempresarial'); 
        $queryBuilder->addSelect(array(
                                't1_.codigo as grupoempresarial_codigo',
                        ));    
                        
                
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $filter);
        

        return [$queryBuilder, $binds];
    }

 
}