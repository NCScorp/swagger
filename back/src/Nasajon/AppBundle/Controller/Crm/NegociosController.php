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
use Nasajon\MDABundle\Entity\Crm\Negocios;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Negocios controller.
 */
class NegociosController extends \Nasajon\MDABundle\Controller\Crm\NegociosController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/negocios/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "prenegocio", "tipoqualificacaopn", "clientecaptador", "vendedor", "documento", }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Negocios();

            $this->validateFilter($filter);

            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_INDEX);

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
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/negocios/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_GET);

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Post("/negocios/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entity = new Negocios();

            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
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
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Put("/negocios/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_PUT);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($tenant, $id_grupoempresarial, $logged_user, $entity, $originalEntity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Negocios entity.');
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
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Delete("/negocios/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_DELETE);

            $deleteForm = $this->createDefaultForm($entity, 'DELETE', 'delete');
            $deleteForm->handleRequest($request);

            if ($deleteForm->isValid()) {
                $this->getService()->delete($tenant, $id_grupoempresarial, $logged_user, $entity);

                return new JsonResponse();
            } else {
                return $this->handleView($this->view($deleteForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Negocios entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     * 
     * @FOS\Post("/negocios/{id}/preNegocioQualificar")
     */
    public function preNegocioQualificarAction($id, Request $request)
    {

        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO);

            $formFactory = $this->get('form.factory');

            $form = $this->createQualificacaoForm($entity, 'Post', 'preNegocioQualificar');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getService()->preNegocioQualificar($tenant, $id_grupoempresarial, $logged_user, $entity);
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
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     * 
     * @FOS\Post("/negocios/{id}/preNegocioDesqualificar")
     */
    public function preNegocioDesqualificarAction($id, Request $request)
    {

        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO);

            $formFactory = $this->get('form.factory');

            $form = $this->createDesqualificacaoForm($entity, 'Post', 'preNegocioDesqualificar');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getService()->preNegocioDesqualificar($tenant, $id_grupoempresarial, $logged_user, $entity);
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
     * Busca o próximo vendedor da lista da vez de acordo com as regras configuradas
     *
     * @FOS\Get("/negocios/{id}/buscar-vendedor-da-lista-da-vez", defaults={ "_format" = "json" })
    */
    public function buscarVendedorDaListaDaVezAction($id, Request $request){
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $listadavezvendedoritem = $this->getService()->buscarVendedorDaListaDaVez($tenant, $id_grupoempresarial, $entity);
            
            $response = new JsonResponse($listadavezvendedoritem);
            
            return $response;
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
