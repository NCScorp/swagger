<?php

namespace Nasajon\AppBundle\Service\Servicos;

use Nasajon\MDABundle\Service\Servicos\PessoasmunicipiosService as ParentService;

/**
 * Sobrescrita para tratar quando o construtor via lookup envia objeto
 */
class PessoasmunicipiosService extends ParentService
{

  /**
   * @param string $id
   * @param mixed $tenant
   * @param mixed $pessoa Nasajon/MDABundle/Entity/Ns/Clientes
   * @param mixed $id_grupoempresarial
          
   * @return array
   * @throw \Doctrine\ORM\NoResultException
   */
  public function find($id, $tenant, $pessoa, $id_grupoempresarial)
  {
    //Pegando atributo do objeto
    if (is_object($pessoa) && !empty($pessoa->getCliente())) {
      $pessoa = $pessoa->getCliente();
    }
    //----

    $data = $this->getRepository()->find($id, $tenant, $pessoa, $id_grupoempresarial);

    return $data;
  }
}
