<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


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
use Nasajon\MDABundle\Entity\Crm\Propostasitensfamilias;
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\MDABundle\Controller\Crm\PropostasitensfamiliasController as CrmPropostasitensfamiliasController;

/**
 * Crm\Propostasitensfamilias controller.
 */
class PropostasitensfamiliasController extends CrmPropostasitensfamiliasController
{

    /**
     * Creates a new Crm\Propostasitensfamilias entity.
     *
     * @FOS\Post("/{propostaitem}/propostasitensfamilias/", defaults={ "_format" = "json" })
     */
    public function createAction($propostaitem, Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Propostasitensfamilias();

            $entity->setTenant($tenant);
            $entity->setPropostaitem($propostaitem);
            $entity->setIdGrupoempresarial($id_grupoempresarial);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENSFAMILIAS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($propostaitem, $logged_user, $tenant, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Edits an existing Crm\Propostasitensfamilias entity.
     *
     * @FOS\Put("/{propostaitem}/propostasitensfamilias/{id}", defaults={ "_format" = "json" })
     */
    public function putAction($propostaitem, Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $propostaitem, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENSFAMILIAS_CREATE);

            $editForm = $this->createEdicaoForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($propostaitem, $tenant, $id_grupoempresarial, $logged_user, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Propostasitensfamilias entity.');
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
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletes a Crm\Propostasitensfamilias entity.
     *
     * @FOS\Delete("/{propostaitem}/propostasitensfamilias/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction($propostaitem, Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $propostaitem, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENSFAMILIAS_CREATE);

            $this->getService()->delete($tenant, $id_grupoempresarial, $logged_user, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Propostasitensfamilias entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
