<?php


namespace Nasajon\AppBundle\Service\Servicos;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Servicos\ServicostecnicosService as ParentService;

/**
* ServicostecnicosService
*
*/
class ServicostecnicosService extends ParentService
{
                
   /**
     * @return array
     */
    public function findAll($id_grupoempresarial,$tenant, Filter $filter = null){

        $result = parent::findAll($id_grupoempresarial, $tenant, $filter);

        // Removendo duplicidade
        return array_values(array_unique($result,SORT_REGULAR));
            
    }

}