<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Form as Form;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Crm\Orcamentos;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Orcamentos controller.
 */
class OrcamentosController extends \Nasajon\MDABundle\Controller\Crm\OrcamentosController
{


    /**
     * Sobreescrito para permissionamento de orçamentos
     * 
     * @FOS\Post("/orcamentos/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {
            // Injeto service de fornecedores envolvidos
            $service = $this->getService();
            $service->fornecedoresEnvolvidosService = $this->get('Nasajon\MDABundle\Service\Crm\FornecedoresenvolvidosService');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Orcamentos();
            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);
            $entity->setAtualizardescontosglobais(true);
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                // Visto que o service de propostasitens já injeta orçamentos, atribuo service manualmente para evitar referencia cruzada.
                $repository->propostaItemService = $this->get('Nasajon\MDABundle\Service\Crm\PropostasitensService');
                $retorno = $repository->insert($tenant, $id_grupoempresarial, $logged_user, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edits an existing Crm\Orcamentos entity.
     *
     * @FOS\Put("/orcamentos/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        try {
            // Injeto service de fornecedores envolvidos
            $service = $this->getService();
            $service->fornecedoresEnvolvidosService = $this->get('Nasajon\MDABundle\Service\Crm\FornecedoresenvolvidosService');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_CREATE);
            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');


            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $entity->setAtualizardescontosglobais(true);
                $retorno = $this->getService()->update($tenant, $id_grupoempresarial, $logged_user, $entity);
                return new JsonResponse($retorno);
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Orcamentos entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * 
     * @FOS\Post("/orcamentos/createBulk/", defaults={ "_format" = "json" })
     */
    public function createBulkAction(Request $request)
    {
        //todo criar o bulk e bulk type
        //todo ajustar rota e os params
        //##### ajustar alí
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new \Nasajon\AppBundle\Entity\Crm\OrcamentosBulk();
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_CREATE);
            $form = $this->get('form.factory');
            $namedBuilder = $form->createNamedBuilder(NULL, \Nasajon\AppBundle\Form\Crm\OrcamentosBulkType::class, $entity, array(
                'method' => 'POST',
                'action' => 'insert',
            ));
            $form = $namedBuilder->getForm();

            $form->handleRequest($request);
            if ($form->isValid()) {
                $retorno = $this->getService()->templateGeraItensLote($logged_user, $tenant, $entity); //#####
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @FOS\Post("/orcamentos/{id}/renegociar")
     */
    public function renegociarAction($id, Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_APROVAR);
            $entity->setMotivo($request->request->get('motivo'));

            $this->getService()->renegociar($logged_user, $tenant, $id_grupoempresarial, $entity);
            return new JsonResponse();
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }
    /**
     * @FOS\Post("/orcamentos/{id}/reprovar")
     */
    public function reprovarAction($id, Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_APROVAR);
            $entity->setMotivo($request->request->get('motivo'));

            $this->getService()->reprovar($logged_user, $tenant, $id_grupoempresarial, $entity);
            return new JsonResponse();
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * @FOS\Post("/orcamentos/{id}/enviar")
     */
    public function enviarAction($id, Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_ENVIAR);

            $this->getService()->enviar($logged_user, $tenant, $id_grupoempresarial, $entity);
            return new JsonResponse();
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }
    /**
     * @FOS\Post("/orcamentos/{id}/aprovar")
     */
    public function aprovarAction($id, Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_APROVAR);

            $this->getService()->aprovar($logged_user, $tenant, $id_grupoempresarial, $entity);
            return new JsonResponse();
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }


    /**
     * Lists all CrmOrcamentos entities.
     *
     * @FOS\Get("/{atc}/{fornecedor}/orcamentos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "propostaitem","propostaitemfamilia","propostaitemfuncao","execucaodeservico","itemfaturamento","status","fornecedor","propostaitem.negocio","orcamento", }})
     */
    public function listagemOrcamentoFichafinanceiraAction($atc, $fornecedor, Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $entity = new Orcamentos();
            // $this->validateFilter($filter);
            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
            $entities = $this->getService()->findAllOrcamentosFichafinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial);
            $response = new JsonResponse();
            $response->setData($entities);
            return $response;
        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Deletes a Crm\Orcamentos entity.
     *
     * @FOS\Delete("/orcamentos/{id}", defaults={ "_format" = "json" })
    */
    public function deleteAction( Request $request, $id)
    {
        try{
            // Injeto service de fornecedores envolvidos
            $service = $this->getService();
            $service->fornecedoresEnvolvidosService = $this->get('Nasajon\MDABundle\Service\Crm\FornecedoresenvolvidosService');
            $service->propostasItensService = $this->get('Nasajon\MDABundle\Service\Crm\PropostasItensService');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            
            $this->validateId($id);
            
            $entity = $this->getService()->findObject($id , $tenant, $id_grupoempresarial);
            $entity->setAtualizardescontosglobais(true);
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $entity);

            // Se não tem familia, o item excluído é um serviço (propostaitem). Desvinculo o fornecedor desse serviço
            if(!$entity->getFamilia()){

                // Dados da proposta item usados na chamada do desvincular fornecedor
                $propostaItemId = $entity->getPropostaitem();
                $atcId= $entity->getAtc();
                $dadosPropItem = $service->propostasItensService->findSemProposta($propostaItemId, $tenant, $atcId, $id_grupoempresarial);
                $objPropostaItem = $service->propostasItensService->findObject($propostaItemId, $tenant, $atcId, $dadosPropItem['proposta'], $id_grupoempresarial);                
                /**
                 * Utilizo a função de desvincular o fornecedor porém passo um parametro para ele levar em conta que deve deletar somente
                 * composicoes(servicos). Não deletando em cascata ( servico e produtos ). E passo o logged_user para registrar o deleted_by
                 */
                $objPropostaItem->setDeletarsomentecomposicoes(true);
                $service->propostasItensService->propostasItensDesvincularFornecedor($tenant, $objPropostaItem, $id_grupoempresarial, false, $logged_user);

                $retorno = [];

            }
            // Se tem familia, é um produto. Posso somente apagar o orçamento
            else{
                $retorno = $this->getService()->delete($tenant,$id_grupoempresarial,$logged_user, $entity);
            }

            return new JsonResponse($retorno);
            
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Crm\Orcamentos entity.');
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }
    }
}
