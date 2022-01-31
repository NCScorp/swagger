<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use LogicException;
use Nasajon\MDABundle\Entity\Crm\Configuracoestaxasadministrativas;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Configuracoestaxasadministrativas controller.
 */
class ConfiguracoestaxasadministrativasController extends \Nasajon\MDABundle\Controller\Crm\ConfiguracoestaxasadministrativasController
{

    /**
     * Sobrescrito para adicionar logic exception
     *
     * @FOS\Post("/configuracoestaxasadministrativas/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entity = new Configuracoestaxasadministrativas();
            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial);
            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);
            $this->denyAccessUnlessGranted(EnumAcao::CFGTAXASADM_GERENCIAR);
            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($id_grupoempresarial, $tenant, $logged_user, $entity);
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Sobrescrito para adicionar logic exception
     *
     * @FOS\Put("/configuracoestaxasadministrativas/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        try {
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $this->validateId($id);
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::CFGTAXASADM_GERENCIAR);
            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($id_grupoempresarial, $tenant, $logged_user, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Configuracoestaxasadministrativas entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Finds and displays a Crm\Configuracoestaxasadministrativas entity.
     *
     * @FOS\Get("/configuracoestaxasadministrativas/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $this->validateId($id);
            $entity = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::CFGTAXASADM_GERENCIAR);
            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }


    /**
     * Lists all CrmConfiguracoestaxasadministrativas entities.
     *
     * @FOS\Get("/configuracoestaxasadministrativas/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "seguradora","estabelecimento","configuracaotaxaadm", }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $entity = new Configuracoestaxasadministrativas();
            $this->validateFilter($filter);
            $this->denyAccessUnlessGranted(EnumAcao::CFGTAXASADM_GERENCIAR);
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
     * Deletes a Crm\Configuracoestaxasadministrativas entity.
     *
     * @FOS\Delete("/configuracoestaxasadministrativas/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $this->validateId($id);
            $entity = $this->getService()->findObject($id, $tenant, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::CFGTAXASADM_GERENCIAR);
            $this->getService()->delete($id_grupoempresarial, $tenant, $entity);
            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Configuracoestaxasadministrativas entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
