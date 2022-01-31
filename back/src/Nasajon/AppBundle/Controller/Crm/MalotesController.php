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
use Nasajon\MDABundle\Entity\Crm\Malotes;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Malotes controller.
 */
class MalotesController extends \Nasajon\MDABundle\Controller\Crm\MalotesController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Get("/malotes/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Malotes();

            $this->denyAccessUnlessGranted(EnumAcao::MALOTES_INDEX);

            $entities = $this->getService()->findAll($tenant, $id_grupoempresarial, $filter);

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
     * Finds and displays a Crm\Malotes entity.
     *
     * @FOS\Get("/malotes/{id}", defaults={ "_format" = "json" })
     */
    public function getAction( $id, Request $request)
    {
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $this->validateId($id);
            $entity = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getService()->fillEntity($entity));
            $response = new JsonResponse($entity);                        
            return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }
        
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Post("/malotes/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Malotes();

            $entity->setTenant($tenant);

            $entity->setIdGrupoempresarial($id_grupoempresarial);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::MALOTES_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($logged_user, $tenant, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Put("/malotes/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::MALOTES_PUT);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($logged_user, $tenant, $id_grupoempresarial, $entity, $originalEntity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Malotes entity.');
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
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * 
     * @FOS\Post("/malotes/{id}/maloteAprova")
     */
    public function maloteAprovaAction($id, Request $request)
    {

        try {


            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::MALOTES_APROVAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createAprovarForm($entity, 'Post', 'maloteAprova');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getService()->maloteAprova($logged_user, $tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * 
     * @FOS\Post("/malotes/{id}/maloteEnviar")
     */
    public function maloteEnviarAction($id, Request $request)
    {

        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::MALOTES_ENVIAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createEnviarForm($entity, 'Post', 'maloteEnviar');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getService()->maloteEnviar($tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * 
     * @FOS\Post("/malotes/{id}/malotecancelaenvio")
     */
    public function malotecancelaenvioAction($id, Request $request)
    {

        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::MALOTES_CANCELARENVIO);

            $this->getService()->malotecancelaenvio($tenant, $id_grupoempresarial, $entity);
            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
    * @FOS\Post("/malotes/{id}/maloteEditarEnvio")
    */
    public function maloteEditarEnvioAction( $id, Request $request) {

        try {
                
                            
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');        
                
            
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $formFactory = $this->get('form.factory');

            $form = $this->createEditarenvioForm($entity, 'Post', 'maloteEditarEnvio');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $this->getService()->maloteEditarEnvio($tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();
                                                    
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }

        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }
                catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }  
}
