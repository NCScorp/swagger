<?php

namespace Nasajon\AppBundle\Service\Crm;

use LogicException;
use Nasajon\MDABundle\Service\Crm\TemplatespropostasgruposService as ParentService;

/**
 * Sobrescrita para tratar quando o construtor via lookup envia objeto
 */
class TemplatespropostasgruposService extends ParentService
{

  /**
   * @param string $id
   * @param mixed $tenant
   * @param mixed $id_grupoempresarial
   * @param mixed $cliente
        
    * @return array
    * @throw \Doctrine\ORM\NoResultException
    */
  public function find($id, $tenant, $id_grupoempresarial, $cliente)
  {
    //Pegando atributo do objeto
    if (is_object($cliente) && !empty($cliente->getCliente())) {
      $cliente = $cliente->getCliente();
    }
    //----

    $data = $this->getRepository()->find($id, $tenant, $id_grupoempresarial, $cliente);

    return $data;
  }

  /**
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Crm\Templatespropostasgrupos $entity
   * @return string
   * @throws \Exception
   */
  public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatespropostasgrupos $entity)
  {
    try {
      $this->getRepository()->begin();

      $podeExcluir = $this->getRepository()->getPodeExcluirTemplatePropostaGrupo($tenant, $entity->getTemplatepropostagrupo());
      if (!$podeExcluir) {
        throw new LogicException("Não foi possível excluir o grupo pois existem apólices vinculados a ele.", 1);
      }

      $response = $this->getRepository()->delete($tenant, $id_grupoempresarial, $entity);

      $this->getRepository()->commit();

      return $response;
    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      throw $e;
    }
  }
}
