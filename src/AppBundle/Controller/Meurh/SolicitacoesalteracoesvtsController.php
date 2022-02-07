<?php

namespace AppBundle\Controller\Meurh;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Controller\Meurh\SolicitacoesalteracoesvtsController as ParentController;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Traits\Meurh\SolicitacoesdocumentosTrait;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowControllerTrait;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;

class SolicitacoesalteracoesvtsController extends ParentController
{
    use SolicitacoesdocumentosTrait;
    use WorkflowControllerTrait;

    /**
     * Lists all Meurh\Solicitacoesalteracoesvts entities.
     *
     * @FOS\Get("/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : {"situacao":"situacao","estabelecimento":"estabelecimento"}})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        return parent::indexAction($filter, $request);
    }

    /**
     * Finds and displays a Meurh\Solicitacoesalteracoesvts entity.
     *
     * @FOS\Get("/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        return parent::getAction($id, $request);
    }

    /**
     * Creates a new Meurh\Solicitacoesalteracoesvts entity.
     *
     * @FOS\Post("/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {

            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');




            $constructors = $this->verificateConstructors($tenant, $trabalhador);


            $entity = new Solicitacoesalteracoesvts();

            $entity->setTenant($tenant);
            $entity->setTrabalhador($constructors['trabalhador']);
            $entity->setTiposolicitacao(4);


            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($trabalhador, $logged_user, $tenant, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edits an existing Meurh\Solicitacoesalteracoesvts entity.
     *
     * @FOS\Put("/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $trabalhador);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $retorno = $this->getService()->update($tenant, $logged_user, $entity, $originalEntity);
                return new JsonResponse($retorno);
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Meurh\Solicitacoesalteracoesvts entity.');
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
     * Deletes a Meurh\Solicitacoesalteracoesvts entity.
     *
     * @FOS\Delete("/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        return parent::deleteAction($request, $id);
    }

    /**
     * @FOS\Post("/{id}/fechar")
     */
    public function fecharAction($id, Request $request)
    {
        return parent::fecharAction($id, $request);
    }
}
