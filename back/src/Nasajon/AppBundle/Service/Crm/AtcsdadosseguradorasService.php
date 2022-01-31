<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes;
use LogicException;
use Nasajon\MDABundle\Service\Crm\AtcsdadosseguradorasService as ParentService;

/**
 * AtcsdadosseguradorasService
 *
 */
class AtcsdadosseguradorasService extends ParentService
{
    // /**
    //  * @var \Nasajon\MDABundle\Repository\Crm\AtcsdadosseguradorasRepository
    //  */
    // protected $repository;

    public $templatespropostasdocumentosService;
    public $tiposdocumentosService;
    public $atcstiposdocumentosrequisitantesService;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\AtcsdadosseguradorasRepository $repository,
        $templatespropostasdocumentosService,
        $tiposdocumentosService,
        $atcstiposdocumentosrequisitantesService
    ) {
        parent::__construct($repository);
        $this->templatespropostasdocumentosService = $templatespropostasdocumentosService;
        $this->tiposdocumentosService = $tiposdocumentosService;
        $this->atcstiposdocumentosrequisitantesService = $atcstiposdocumentosrequisitantesService;
    }

    private function excluirDocumentosRequisitantes($tenant, $id_grupoempresarial, $documentos){
        array_map(function($documento) use($tenant, $id_grupoempresarial){
          $obj = $this->atcstiposdocumentosrequisitantesService->fillEntity($documento);
          $obj->setExcluirTipoDocumentoRequisitanteExterno(true);
          $this->atcstiposdocumentosrequisitantesService->delete($tenant, $id_grupoempresarial, $obj);
        }, $documentos);
    }


    /**
     * @param string  $tenant
        * @param string  $id_grupoempresarial
                * @param \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras $entity
        * @return string
        * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras $entity)
    {
        if($this->getRepository()->apoliceEmUsoNoAtc($entity->getNegocio(), $entity->getApolice(), $tenant)){
            throw new LogicException("Não é possível remover uma apólice em uso no atendimento.");
        }

       
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('requisitanteapolice.templateproposta', 'eq', $entity->getApolice()->getTemplateProposta()));
        $documentosParaExcluir = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $entity->getNegocio(), $id_grupoempresarial, $filter);
        $this->excluirDocumentosRequisitantes($tenant, $id_grupoempresarial, $documentosParaExcluir);
            
        return [parent::delete($tenant, $id_grupoempresarial, $entity),$entity->getNegociodadosseguradora()];
    }

    private function salvarDocumentosRequisitante($tenant, $logged_user, $atcId, $requisitante, $id_grupoempresarial, $documentos){
        array_map(function($documento) use($logged_user, $tenant, $atcId, $requisitante, $id_grupoempresarial){
          $obj = $this->montaEntidadeAtcsTiposDocumentosRequisitantes($tenant, $atcId, $requisitante, $id_grupoempresarial, $documento);
          $this->atcstiposdocumentosrequisitantesService->insert($atcId, $tenant, $logged_user, $id_grupoempresarial, $obj);
        }, $documentos);
    }
    
    private function montaEntidadeAtcsTiposDocumentosRequisitantes($tenant, $atc, $requisitante, $id_grupoempresarial, $dadosDocumento){
        $obj = new Atcstiposdocumentosrequisitantes();
        isset($dadosDocumento['tipodocumento']) ? $obj->setTipodocumento($this->tiposdocumentosService->fillEntity($dadosDocumento['tipodocumento'])) : null;
        isset($dadosDocumento['copiasimples']) ? $obj->setCopiasimples($dadosDocumento['copiasimples']) : null;
        isset($dadosDocumento['copiaautenticada']) ? $obj->setCopiaautenticada($dadosDocumento['copiaautenticada']) : null;
        isset($dadosDocumento['original']) ? $obj->setOriginal($dadosDocumento['original']) : null;
        isset($dadosDocumento['permiteenvioemail']) ? $obj->setPermiteenvioemail($dadosDocumento['permiteenvioemail']) : null;
        isset($dadosDocumento['tipodocumento']) ? $obj->setTenant($tenant) : null;
        isset($dadosDocumento['naoexibiremrelatorios']) ? $obj->setNaoexibiremrelatorios($dadosDocumento['naoexibiremrelatorios']) : false;
        $obj->setNegocio($atc);
        $obj->setRequisitanteapolice($requisitante);
        isset($dadosDocumento['id_grupoempresarial']) ? $obj->setIdGrupoempresarial($id_grupoempresarial) : null;
        return $obj;
    }

    /**
   * @param string  $atc
        * @param string  $logged_user
        * @param string  $tenant
        * @param string  $id_grupoempresarial
                * @param \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras $entity
        * @return string
        * @throws \Exception
    */
    public function insert($atc,$logged_user,$tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras $entity) {
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->insert($atc,$logged_user,$tenant,  $id_grupoempresarial, $entity);

            $requisitanteApolice = $entity->getApolice();
            $tiposDocumentosApolice = $this->templatespropostasdocumentosService->findAll($tenant,  $id_grupoempresarial, $requisitanteApolice->getTemplateproposta(), null);
            
            $this->salvarDocumentosRequisitante($tenant, $logged_user, $atc, $requisitanteApolice, $id_grupoempresarial, $tiposDocumentosApolice);
                                                
            $this->getRepository()->commit();

            return $response;

        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $logged_user
        * @param string  $tenant
        * @param string  $id_grupoempresarial
        * @param \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras $entity
        * @return string
        * @throws \Exception
    */
    public function update($logged_user,$tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras $entity, $originalEntity = NULL){
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->update($logged_user,$tenant, $id_grupoempresarial, $entity);

            $templatePropostaOriginal = null;
            if (isset($originalEntity)) {
                $templatePropostaOriginal = is_array($originalEntity->getApolice()) ? 
                    $originalEntity->getApolice()['templateproposta'] :  $originalEntity->getApolice()->getTemplateProposta();
            }
            if(isset($originalEntity) && $templatePropostaOriginal !== $entity->getApolice()->getTemplateProposta()){
                $filter = new Filter();
                $filter->addToFilterExpression(new FilterExpression('requisitanteapolice.templateproposta', 'eq', $originalEntity->getApolice()->getTemplateProposta()));
                $documentosParaExcluir = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $originalEntity->getNegocio(), $id_grupoempresarial, $filter);
                $this->excluirDocumentosRequisitantes($tenant, $id_grupoempresarial, $documentosParaExcluir);
            
                $filter = new Filter();
                $filter->addToFilterExpression(new FilterExpression('requisitanteapolice.templateproposta', 'eq', $entity->getApolice()->getTemplateProposta()));
                $novosDocumentos = $this->templatespropostasdocumentosService->findAll($tenant, $id_grupoempresarial, $entity->getApolice()->getTemplateProposta(), $filter);
                $this->salvarDocumentosRequisitante($tenant, $logged_user, $entity->getNegocio(), $entity->getApolice(), $id_grupoempresarial, $novosDocumentos);
            }
                                                        
            $this->getRepository()->commit();

            return $response;

        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }

    }
}