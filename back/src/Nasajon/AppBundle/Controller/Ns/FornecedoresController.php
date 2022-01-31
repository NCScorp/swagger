<?php

namespace Nasajon\AppBundle\Controller\Ns;

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
use Nasajon\MDABundle\Entity\Ns\Fornecedores;
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\AppBundle\Form\Ns\FornecedoresDefaultType;

/**
 * Ns\Fornecedores controller.
 */
class FornecedoresController extends \Nasajon\MDABundle\Controller\Ns\FornecedoresController
{

    /**
     * Sobrescrito para usar o FORM sobrescrito
     *
     * @param Fornecedores $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultForm(Fornecedores $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(NULL, FornecedoresDefaultType::class, $entity, array(
                'method' => $method,
                'action' => $action,
            ))
            ->getForm();
        return $form;
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Get("/fornecedores/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "tiposatividadesfilter","status","municipionome","fornecedor", "funcionarioativado", "contribuinteindividualativado" }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');



            $entity = new Fornecedores();

            $this->validateFilter($filter);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_INDEX);

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
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Post("/fornecedores/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = new Fornecedores();

            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial);


            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($id_grupoempresarial, $logged_user, $tenant, $entity);

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
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Put("/fornecedores/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        try {

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_PUT);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($id_grupoempresarial, $logged_user, $tenant, $entity, $originalEntity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Ns\Fornecedores entity.');
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
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * @FOS\Post("/fornecedores/{id}/suspender")
     */
    public function suspenderAction($id, Request $request)
    {

        try {


            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');


            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_SUSPENDER);

            $formFactory = $this->get('form.factory');

            $form = $this->createSuspenderForm($entity, 'Post', 'suspender');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $action = 'suspender';
                if ($entity->getStatus() != "0") {
                    return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
                }
                $this->getService()->suspender($logged_user, $tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * @FOS\Post("/fornecedores/{id}/fornecedorreativar")
     */
    public function fornecedorreativarAction($id, Request $request)
    {

        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');


            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_REATIVAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createFornecedorreativarForm($entity, 'Post', 'fornecedorreativar');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $action = 'fornecedorreativar';
                if ($entity->getStatus() == "0") {
                    return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
                }
                $this->getService()->fornecedorreativar($tenant, $logged_user, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * @FOS\Post("/fornecedores/{id}/fornecedoradvertir")
     */
    public function fornecedoradvertirAction($id, Request $request)
    {

        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');


            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_ADVERTIR);

            $formFactory = $this->get('form.factory');

            $form = $this->createFornecedoradvertirForm($entity, 'Post', 'fornecedoradvertir');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getService()->fornecedoradvertir($tenant, $logged_user, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Finds and displays a Ns\Fornecedores entity.
     *
     * @FOS\Get("/fornecedores/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_GET);

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

}
