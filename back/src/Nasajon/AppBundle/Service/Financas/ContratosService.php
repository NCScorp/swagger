<?php

namespace Nasajon\AppBundle\Service\Financas;

use Exception;
use Nasajon\MDABundle\Service\Financas\ContratosService as ParentService;

/**
 * Sobrescrita para tratar erro na verificação do podeExcluir, que consome o método espera true/false
 * Metodo delete sobreescrito para deletar itenscontratos para o contrato dado.
 * Metodo excluiContratoPagamento sobreescrito para deletar itenscontratos para o contrato dado.
 */
class ContratosService extends ParentService
{

  public function __construct(\Nasajon\MDABundle\Repository\Financas\ContratosRepository $repository, $fnncsTnscntrtsSrvc)
  {
    $this->repository = $repository;
    $this->fnncsTnscntrtsSrvc = $fnncsTnscntrtsSrvc;
  }

  /**
   * @todo repensar o tratamento, talvez salvar em log
   * Quando a resposta da function no banco é positiva, então é true; se for erro então é false
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Financas\Contratos $entity
   * @return string
   * @throws \Exception
   */
  public function podeExcluir($tenant, \Nasajon\MDABundle\Entity\Financas\Contratos $entity)
  {
    try {
      $this->getRepository()->podeExcluir($tenant,  $entity);
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }

  /**
   * @param string  $id_grupoempresarial
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Financas\Contratos $entity
   * @return string
   * @throws \Exception
   */
  public function delete($tenant, \Nasajon\MDABundle\Entity\Financas\Contratos $entity)
  {
    try {
      $this->getRepository()->begin();
      if(!$this->podeExcluir($tenant,$entity)){
        throw new Exception("Não é possível excluir este contrato.", 1);
      }

      $itensContratos = $entity->getItenscontratos()->toArray();
      foreach ($itensContratos as $key => $item) {
        if ($item->getContrato() !== $entity->getContrato()) {
          continue;
        }
          $this->fnncsTnscntrtsSrvc->delete($tenant, $item);
      }

      $response = $this->getRepository()->delete($tenant,  $entity);

      $this->getRepository()->commit();

      return $response;
    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  /**
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Financas\Contratos $entity
   * @return string
   * @throws \Exception
   */
  public function excluiContratoPagamento($tenant, \Nasajon\MDABundle\Entity\Financas\Contratos $entity)
  {
    try {
      $this->getRepository()->begin();
      if(!$this->podeExcluir($tenant,$entity)){
        throw new Exception("Não é possível excluir este contrato.", 1);
      }

      $itensContratos = $entity->getItenscontratos()->toArray();
      foreach ($itensContratos as $key => $item) {
        if ($item->getContrato() !== $entity->getContrato()) {
          continue;
        }
          $this->fnncsTnscntrtsSrvc->delete($tenant, $item);
      }

      $response = $this->getRepository()->excluiContratoPagamento($tenant,  $entity);

      $this->getRepository()->commit();

      return $response;
    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      throw $e;
    }
  }
}
