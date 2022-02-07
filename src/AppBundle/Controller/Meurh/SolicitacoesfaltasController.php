<?php

namespace AppBundle\Controller\Meurh;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Controller\Meurh\SolicitacoesfaltasController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas;
use AppBundle\Enum\Meurh\SolicitacoesEnum;
use AppBundle\Traits\Meurh\SolicitacoesdocumentosTrait;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowControllerTrait;
use Nasajon\MDABundle\Type\InvalidIdException;

class SolicitacoesfaltasController extends ParentController
{
  use SolicitacoesdocumentosTrait;
  use WorkflowControllerTrait;

  /**
   * Lists all Meurh\Solicitacoesfaltas entities.
   *
   * @FOS\Get("/")
   * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "situacao","created_at","solicitacao", }})
   */
  public function indexAction(Filter $filter = null, Request $request)
  {
    return parent::indexAction($filter, $request);
  }

  /**
   * Finds and displays a Meurh\Solicitacoesfaltas entity.
   *
   * @FOS\Get("/{id}", defaults={ "_format" = "json" })
   */
  public function getAction($id, Request $request)
  {
    return parent::getAction($id, $request);
  }
  /**
   * Sobrescrito para usar DefaultType que atenda as necessidades do formulário para o campo de justificativa
   *
   * @FOS\Post("/", defaults={ "_format" = "json" })
   */
  public function createAction(request $request)
  {
    try {

      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
      $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

      $entity = new Solicitacoesfaltas();

      $entity->setTenant($tenant);

      // Se é rascunho
      if ($request->get('situacao') == SolicitacoesEnum::SITUACAO_RASCUNHO) {
        $entity->setSituacao($request->get('situacao'));
        $entity->setOrigem(2);
        $retorno = $this->getService()->draftInsert($trabalhador, $tenant, $logged_user, $entity);
        return new JsonResponse(["id" => $retorno], JsonResponse::HTTP_CREATED);
      } else {
        $form = $this->get('form.factory')
          ->createNamedBuilder(NULL, \AppBundle\Form\Meurh\SolicitacoesfaltasDefaultType::class, $entity, array(
            'method' => "POST",
            'action' => 'insert'
          ))
          ->getForm();
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
   * Edits an existing Meurh\Solicitacoesfaltas entity.
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

      $entityArr = $this->getService()->findDraft($id, $tenant);        

      if ($entityArr["situacao"] != SolicitacoesEnum::SITUACAO_RASCUNHO) {
        $entityArr = $this->getService()->find($id, $tenant, $trabalhador);
      }

      $entity = $this->getService()->fillEntity($entityArr);


      $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

      $editForm = $this->get('form.factory')
        ->createNamedBuilder(NULL, \AppBundle\Form\Meurh\SolicitacoesfaltasDefaultType::class, $entity, array(
          'method' => "PUT",
          'action' => 'update'
        ))
        ->getForm();
      $editForm->handleRequest($request);

      if ($editForm->isValid()) {
        $retorno = $this->getService()->update($trabalhador, $tenant, $logged_user, $entity);
        return new JsonResponse($retorno);
      } else {
        return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
      }
    } catch (\Doctrine\ORM\NoResultException $e) {
      throw $this->createNotFoundException('Unable to find Meurh\Solicitacoesfaltas entity.');
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
   * Deletes a Meurh\Solicitacoesfaltas entity.
   *
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
        throw $this->createNotFoundException('Unable to find Meurh\Solicitacoesfaltas entity.');
    } catch (InvalidIdException $e) {
        return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
    } catch (RepositoryException $e) {
        return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
