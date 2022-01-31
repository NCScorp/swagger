<?php

/*
  Sobrescrito para pegar relacionamentos do documento
 */

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\MalotesdocumentosRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

/**
 * MalotesdocumentosRepository
 *
 */
class MalotesdocumentosRepository extends ParentRepository {

    /**
     * @return array
     */
    public function findAll($tenant, $malote, $id_grupoempresarial, Filter $filter = null){

        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $malote, $id_grupoempresarial, $filter);

        //join para recuperar o nome do negócio
        $queryBuilder->leftJoin('t2_', 'crm.atcs', 't3_', 't2_.negocio = t3_.negocio and t2_.tenant = t3_.tenant'); 
        $queryBuilder->addSelect(array(
                't3_.nome as documento_negocio_nome'
        ));

        //join para recuperar o tipo de documento
        $queryBuilder->leftJoin('t2_', 'ns.tiposdocumentos', 't4_', 't2_.tipodocumento = t4_.tipodocumento and t2_.tenant = t4_.tenant'); 
        $queryBuilder->addSelect(array(
                't4_.nome as documento_tipodocumento_nome'
        ));                    

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());

        $stmt->execute($binds);
        
        $joins = ['malote', 'documento', ];       
        
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