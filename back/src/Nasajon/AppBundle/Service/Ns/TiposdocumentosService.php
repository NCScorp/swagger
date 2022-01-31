<?php

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\TiposdocumentosService as ParentService;

/**
* TiposdocumentosService
*
*/
class TiposdocumentosService extends ParentService
{
  /**
   * Sobrescrito para filtrar os documentos pelo id do pai.
   * @param string $id
   * @param mixed $tenant
   * @param mixed $id_grupoempresarial

   * @return array
   * @throw \Doctrine\ORM\NoResultException
   */
  public function find($id, $tenant, $id_grupoempresarial)
  {
    $data = $this->getRepository()->find($id , $tenant, $id_grupoempresarial);
    $data['documentosnecessarios'] = $this->nsDcmntsncssrsSrvc->findAll($id, $tenant, $id_grupoempresarial);
    return $data;
  }  
}