<?php
namespace Nasajon\AppBundle\Service\Crm;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\PropostasService as ParentService;
use LogicException;
/**
 * Sobrescrito por causa da label de status
 */
class PropostasService extends ParentService {

  public $crmPropostascapitulosService;
  public $crmPropostasitensService;

  public function __construct(\Nasajon\MDABundle\Repository\Crm\PropostasRepository $repository, $crmPropostascapitulosService, $crmPropostasitensService ){
    $this->repository = $repository;    
    $this->crmPropostascapitulosService = $crmPropostascapitulosService; 
    $this->crmPropostasitensService = $crmPropostasitensService;  
            
   
}
  /**
   * "0":"Novo","1":"Enviado", "2":"Confirmado", "3": "Recusado"
   * @param type $status
   * @return string
   */
  private function getStatus($status) {
    if (isset($status)) {
      switch ($status) {
        case 0:
          return 'Novo';
        case 1:
          return 'Enviado';
        case 2:
          return 'Confirmado';
        case 3:
          return 'Recusado';
      }
    }
  }
  /**
   * @return array
   */
  public function findAll($tenant, $atc, $id_grupoempresarial, Filter $filter = null) {

    $this->getRepository()->validateOffset($filter);

    $propostas = array_map(function($proposta){
      $proposta['statusLabel'] = $this->getStatus($proposta['status']);   
      return $proposta;
    }, $this->getRepository()->findAll($tenant, $atc, $id_grupoempresarial, $filter));

    return $propostas;
  }


  public function geraItemContratoPagamento ($contratoAPagar, &$responsabilidade, $fornecedor, $id_grupoempresarial, $logged_user, $tenant) {
    $itemContratoAPagar = $this->crmPropostasitensService->geraItemContratoPagamento ($contratoAPagar, $responsabilidade, $fornecedor, $id_grupoempresarial, $logged_user, $tenant);
    return $itemContratoAPagar;
  }
  

  public function geraItensContratos($tenant, $logged_user, $id_grupoempresarial,  $atc, $contrato, $cliente){
    try {
      $propostasitens = $this->getPropostasItens($tenant, $atc, $id_grupoempresarial);
      if(count($propostasitens) == 0 ){
        return [];
      }
      foreach($propostasitens as $propostaitem){
        $this->crmPropostasitensService->geraItemContrato($contrato, $propostaitem, $cliente, $id_grupoempresarial, $logged_user, $tenant, $atc);
      }
      return $propostasitens;
    } catch (LogicException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw $e;
    }  
  }

  public function geraItemContrato($tenant, $logged_user, $id_grupoempresarial, $atc, $contrato, $cliente, &$responsabilidade, $contratanteResponsabilidadeFinanceira, $valorResponsabilidadeFinanceira){
    try{
      return $this->crmPropostasitensService->geraItemContrato($contrato, $responsabilidade, $cliente, $id_grupoempresarial, $logged_user, $tenant, $atc, $contratanteResponsabilidadeFinanceira, $valorResponsabilidadeFinanceira);

    } catch (LogicException $e) {
        throw $e;
    }  
  }

    /**
   * Pega Propostas itens dentro do negócio. 
   * Pela regra de negócio em um atendimento tem apenas 1 pedido que na verdade é a proposta com status de aprovado
   * @todo garantir que só há um pedido (por enqaunto pegamos a primeira proposta, otimizar e melhorar legibilidade
   * @param integer $tenant
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $atc 
   * @return array \Nasajon\MDABundle\Entity\Crm\Propostasitens $propostasitens 
   */
  public function getPropostasItens($tenant, $atc, $id_grupoempresarial){
    $propostasitens = [];

    $proposta = $this->findAll($tenant, $atc, $id_grupoempresarial);
    if(count($proposta) == 0 ){
      return [];
    }
    $pedido = $proposta[0];  
    
    $propostasitens = array_map(function($propostaitem){
        return $this->crmPropostasitensService->fillEntity($propostaitem);
    }, $this->crmPropostasitensService->findAll($tenant, $atc, $pedido['proposta'], $id_grupoempresarial));
    
    foreach ($propostasitens as $key => $propostaitem) {
      $familiasfuncoes = $this->crmPropostasitensService->findFamiliasFuncoes($propostaitem->getPropostaitem(), $tenant, $id_grupoempresarial);
      $propostasitens[$key]->setPropostasitensfamilias($familiasfuncoes['familias']);
      $propostasitens[$key]->setPropostasitensfuncoes($familiasfuncoes['funcoes']);
    }

    return $propostasitens;

  }
}