<?php

namespace AppBundle\Controller\Meurh;

# Não remover.

use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Nasajon\MDABundle\Controller\Meurh\SolicitacoesalteracoesenderecosController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Type\InvalidFilterException;
use LogicException;
use Nasajon\MDABundle\Type\InvalidIdException;
use AppBundle\Traits\Meurh\SolicitacoesdocumentosTrait;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowControllerTrait;

class SolicitacoesalteracoesenderecosController extends ParentController
{
    use SolicitacoesdocumentosTrait;
    use WorkflowControllerTrait;

    //Todos os métodos foram sobrescritos de forma a padronizar as rotas 

    /**
     * Lists all Meurh\Solicitacoesalteracoesenderecos entities.
     *
     * @FOS\Get("/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "situacao","estabelecimento","solicitacao", }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        return parent::indexAction($filter, $request);
    }

    /**
     * Finds and displays a Meurh\Solicitacoesalteracoesenderecos entity.
     *
     * @FOS\Get("/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        return parent::getAction($id, $request);
    }

    /**
     * Creates a new Meurh\Solicitacoesalteracoesenderecos entity.
     *
     * @FOS\Post("/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {

            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');


            $entity = new Solicitacoesalteracoesenderecos();

            $entity->setTenant($tenant);
            $entity->setTrabalhador($trabalhador);

            if ($request->get('situacao') == -1) {
                $entity->setSituacao($request->get('situacao'));
                // $entity->setJustificativa('rascunho');  
                $retorno = $this->getService()->draftInsert($trabalhador, $tenant, $logged_user, $entity);
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                $form = $this->createDefaultForm($entity, 'POST', 'insert');
                $form->handleRequest($request);

                $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

                if ($form->isValid()) {
                    $repository = $this->getService();
                    $retorno = $repository->insert($trabalhador, $tenant, $logged_user, $entity);

                    return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
                } else {
                    return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
                }
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edits an existing Meurh\Solicitacoesalteracoesenderecos entity.
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

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                if ($entity->getSituacao() == -1) {
                    $this->getService()->abrir($tenant, $logged_user, $entity);
                    return new JsonResponse();
                } else {
                    $retorno = $this->getService()->update($tenant, $logged_user, $entity);
                    return new JsonResponse($retorno);
                }
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Meurh\Solicitacoesalteracoesenderecos entity.');
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
     * Deletes a Meurh\Solicitacoesalteracoesenderecos entity.
     * @FOS\Delete("/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
            $this->validateId($id);
            $entity = $this->getService()->findDraftObject($id, $tenant);
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $entity);
            $this->getService()->delete($tenant, $entity);
            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Meurh\Solicitacoesalteracoesenderecos entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
