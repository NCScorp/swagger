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
use Nasajon\MDABundle\Entity\Crm\Historicospadrao;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Historicospadrao controller.
 */
class HistoricospadraoController extends \Nasajon\MDABundle\Controller\Crm\HistoricospadraoController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Get("/historicospadrao/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "tipo", }})
     */

    public function indexAction( Filter $filter = null, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
             

            $entity = new Historicospadrao();

            $this->validateFilter($filter);

            $this->denyAccessUnlessGranted(EnumAcao::HISTORICOSPADRAO_INDEX);

            $entities = $this->getService()->findAll($tenant,$id_grupoempresarial, $filter);

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
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Get("/historicospadrao/{id}", defaults={ "_format" = "json" })
     */

    public function getAction( $id, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::HISTORICOSPADRAO_GET);

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
        
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Post("/historicospadrao/", defaults={ "_format" = "json" })
     */

    public function createAction(Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Historicospadrao();

            $entity->setTenant($tenant);

            $entity->setIdGrupoempresarial($id_grupoempresarial);


            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::HISTORICOSPADRAO_CREATE);

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
     * @FOS\Put("/historicospadrao/{id}", defaults={ "_format" = "json" })
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

            $this->denyAccessUnlessGranted(EnumAcao::HISTORICOSPADRAO_PUT);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($logged_user, $tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Historicospadrao entity.');
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
     * @FOS\Delete("/historicospadrao/{id}", defaults={ "_format" = "json" })
     */

    public function deleteAction(Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::HISTORICOSPADRAO_DELETE);

            $this->getService()->delete($tenant, $id_grupoempresarial, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Historicospadrao entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
