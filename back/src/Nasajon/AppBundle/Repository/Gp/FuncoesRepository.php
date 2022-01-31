<?php
/*
     Sobrescrita do código gerado pelo MDA
*/

namespace Nasajon\AppBundle\Repository\Gp;

use Nasajon\MDABundle\Repository\Gp\FuncoesRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
/**
* FuncoesRepository
*
*/
class FuncoesRepository extends ParentRepository
{
 
    /**
     * @return array
     */
    public function findAll($tenant, Filter $filter = null){

        $result = parent::findAll($tenant, $filter);

        // Removendo duplicidade
        return array_values(array_unique($result,SORT_REGULAR));
                
    }
    
    
}