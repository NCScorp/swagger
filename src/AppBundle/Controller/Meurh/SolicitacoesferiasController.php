<?php

namespace AppBundle\Controller\Meurh;

use Exception;
use LogicException;
use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Request\Filter;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesferias;
use AppBundle\Traits\Meurh\SolicitacoesdocumentosTrait;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use AppBundle\Repository\Meurh\SolicitacoesferiasRepository;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Controller\Meurh\SolicitacoesferiasController as ParentController;

class SolicitacoesferiasController extends ParentController
{
    use SolicitacoesdocumentosTrait;
    use WorkflowControllerTrait;

    /**
     * @return SolicitacoesferiasRepository
     */
    public function getRepository() {
        return $this->get('Nasajon\MDABundle\Repository\Meurh\SolicitacoesferiasRepository');
    }

    /**
     * @FOS\Get("/feriados/", defaults={ "_format" = "json" })
     * @return JsonResponse
     */
    public function feriadosAction()
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        return new JsonResponse($this->getRepository()->findHolidays($tenant));
    }

    /**
     * Lists all Meurh\Solicitacoesferias entities.
     * @FOS\Get("/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "estabelecimento","situacao", }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
            $this->validateFilter($filter);
            $entities = $this->getService()->findAll($tenant, $trabalhador, $filter);
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
     * Finds and displays a Meurh\Solicitacoesferias entity.
     * @FOS\Get("/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        try{        
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
            $this->validateId($id);
            $entity = $this->getService()->find($id , $tenant, $trabalhador);
            $response = new JsonResponse($entity);                        
            return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }
    }

    /**
     * Creates a new Meurh\Solicitacoesferias entity.
     * @FOS\Post("/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $estabelecimento = $this->get('nasajon_mda.fixed_attributes')->get('estabelecimento');                     
        $entity = new Solicitacoesferias();        
        $entity->setTenant($tenant);
        $entity->setEstabelecimento($estabelecimento);
        $entity->setTrabalhador($trabalhador);
        /** Sobrescrevendo para trocar o form e mudar a validação*/
        /** Início sobrescrita */
        $form = $this->get('form.factory')->createNamedBuilder(NULL, \AppBundle\Form\Meurh\SolicitacoesferiasDefaultType::class, $entity, array(
            'method' => "POST",
            'action' => 'insert'
        ))->getForm();
        /** Fim sobrescrita */
        $form->handleRequest($request);
        $estabelecimento = $this->get('nasajon_mda.fixed_attributes')->get('estabelecimento');
        $entity->setEstabelecimento($estabelecimento);
        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if (!$form->isValid()) {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
        
        try {
            $repository = $this->getService();
            $retorno = $repository->insert($trabalhador, $tenant, $logged_user, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Edits an existing Meurh\Solicitacoesferias entity.
     * @FOS\Put("/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');                    
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');                      
        $this->validateId($id);
        $entityArr = $this->getService()->find($id, $tenant, $trabalhador);
        $entity = $this->getService()->fillEntity($entityArr);         
        $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);  
        /** Sobrescrevendo para trocar o form e mudar a validação*/
        /** Início sobrescrita */
        $editForm = $this->get('form.factory')->createNamedBuilder(NULL, \AppBundle\Form\Meurh\SolicitacoesferiasDefaultType::class, $entity, array(
            'method' => "PUT",
            'action' => 'update'
        ))->getForm();
        /** Fim sobrescrita */
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->getService()->update($tenant,$logged_user, $entity);
            return new JsonResponse();
        }else{
            return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Deletes a Meurh\Solicitacoesferias entity.
     * @FOS\Delete("/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        return parent::deleteAction($request, $id);
    }

    /**
     * @FOS\Post("/solicitacoesferias/{id}/cancelar")
     */
    public function cancelarAction($id, Request $request) 
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');    
            $entityArr = $this->getService()->find($id , $tenant, $trabalhador);
            $entity = $this->getService()->fillEntity($entityArr);

            if (is_object($entity->getTrabalhador())) {
                $entity->setTrabalhador($trabalhador);
            }

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $this->getService()->cancelar($logged_user, $tenant, $entity);

            return new JsonResponse();
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (\DomainException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }                

    /**
     * @FOS\Get("/periodos/{id}")
     */
    public function listaPeriodosAction($id, Request $request)
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $this->validateId($id);
        $entity = new Solicitacoesferias();        
        $entity = $this->getService()->listaPeriodosAquisitivosAbertos($tenant,$id);
        $response = new JsonResponse($entity);
        return $response;   
    }


    /**
     * @FOS\Get("/agrupado/{id}", defaults={ "_format" = "json" })
     */
    public function getAgrupadoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $this->validateId($id);
            $entity = new Solicitacoesferias();        
            $entity = $this->getService()->findSolicitacoesAgrupadasPorPeriodo($tenant,$id);
            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return $this->handleView($this->view([], JsonResponse::HTTP_NOT_FOUND));
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}