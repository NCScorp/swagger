<?php


namespace Nasajon\AppBundle\Repository\Servicos;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Servicos\ServicostecnicosRepository as ParentRepository;
/**
* ServicostecnicosRepository
*
*/
class ServicostecnicosRepository extends ParentRepository
{
 

    public function findAllQueryBuilder($id_grupoempresarial,$tenant, Filter $filter = null){
    
    $queryBuilder = $this->getConnection()->createQueryBuilder();
    //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
    $selectArray = array(
                            't0_.servicotecnico as servicotecnico',
                            't0_.descricao as descricao',
                            't0_.codigo as codigo',
                            't0_.tipo as tipo',
                            't0_.valor as valor',
        );
        
    if( $filter && empty($filter->getOffset()) ){
        array_push($selectArray, 'count(*) OVER() AS full_count');
    }
            $queryBuilder->select($selectArray);
    $queryBuilder->from('servicos.servicostecnicos', 't0_');
    $queryBuilder->leftJoin('t0_', 'servicos.vwservicosgrupoempresarial', 't1_', 't0_.servicovinculado = t1_.servico and t0_.tenant = t1_.tenant and t0_.id_grupoempresarial = t1_.grupoempresarial'); 
    $queryBuilder->addSelect(array(
                                't1_.servico as itemdefaturamento_servico',
                                't1_.descricaoservico as itemdefaturamento_descricaoservico',
                                't1_.servicobloqueado as itemdefaturamento_bloqueado',
                        ));    
                                    
            
    $binds = $this->findAllQueryBuilderBody($queryBuilder,$id_grupoempresarial,$tenant, $filter);
    

    return [$queryBuilder, $binds];
    }

    /**
     * @return array
     */
    public function findAll($id_grupoempresarial,$tenant, Filter $filter = null){

        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($id_grupoempresarial,$tenant, $filter);

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());

        $stmt->execute($binds);
        
        $joins = ['itemdefaturamento', ];       
        
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
    
}