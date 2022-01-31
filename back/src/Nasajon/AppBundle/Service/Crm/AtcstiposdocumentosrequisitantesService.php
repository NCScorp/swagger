<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\AtcstiposdocumentosrequisitantesService as ParentService;

class AtcstiposdocumentosrequisitantesService extends ParentService
{ 
    private $atcsDocumentosService;

    public function __construct(\Nasajon\MDABundle\Repository\Crm\AtcstiposdocumentosrequisitantesRepository $repository, $atcsDocumentosService)
    {
        parent::__construct($repository);
        $this->atcsDocumentosService = $atcsDocumentosService;
    }
    /**
     * Sobrescrito para impedir que uma entidade seja apagada
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes $entity)
    {
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->delete($tenant, $id_grupoempresarial, $entity);

            //exclui os documentos com o mesmo tipo do documento esperado que foi excluído caso não haja requisitantes externos
            if(!$entity->getExcluirTipoDocumentoRequisitanteExterno()){
                $filter = new Filter();
                $expressions = [];
                $expressions[] = new FilterExpression('tipodocumento.tipodocumento', 'eq', $entity->getTipodocumento()->getTipodocumento());
                $filter->setFilterExpression($expressions);
                $documentosEsperados = $this->findAll($tenant, $entity->getNegocio(), $id_grupoempresarial, $filter);
                if(count($documentosEsperados) == 0){
                    $expressions[] = new FilterExpression('negocio', 'eq', $entity->getNegocio());
                    $filter->setFilterExpression($expressions);
                    $documentos = $this->atcsDocumentosService->findAll($tenant, $id_grupoempresarial, $filter);
                    foreach($documentos as $documento){
                        $this->atcsDocumentosService->delete($tenant, $this->atcsDocumentosService->fillEntity($documento));
                    }
                }
            }

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param \Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes $entity
     */
    public function possuiRequisitanteExterno($entity){
        if($entity->getRequisitantecliente() !== null && $entity->getRequisitantecliente()->getCliente() !== null){
            return true;
        }
        if($entity->getRequisitantefornecedor() !== null && $entity->getRequisitantefornecedor()->getFornecedor() !== null){
            return true;
        }
        if($entity->getRequisitantenegocio()){
            return false;
        }
        return false;
    }

    /**
    * @param string  $atc
    * @param string  $tenant
    * @param string  $logged_user
            * @param \Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes $entity
    * @return string
    * @throws \Exception
    */
    public function insert($atc,$tenant,$logged_user, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes $entity){
        try {
    
            return $this->getRepository()->insert($atc,$tenant,$logged_user,  $id_grupoempresarial, $entity);

        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }

    }
}