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
use Nasajon\MDABundle\Entity\Crm\Propostascapitulos;
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\MDABundle\Controller\Crm\PropostascapitulosController as CrmPropostascapitulosController;

/**
 * Crm\Propostascapitulos controller.
 */
class PropostascapitulosController extends CrmPropostascapitulosController
{

    /**
     * Creates a new Crm\Propostascapitulos entity.
     *
     * @FOS\Post("/{proposta}/propostascapitulos/", defaults={ "_format" = "json" })
     */
    public function createAction($proposta, Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Propostascapitulos();

            $entity->setTenant($tenant);
            $entity->setProposta($proposta);
            $entity->setIdGrupoempresarial($id_grupoempresarial);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASCAPITULOS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($proposta, $logged_user, $tenant, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Edits an existing Crm\Propostascapitulos entity.
     *
     * @FOS\Put("/{proposta}/propostascapitulos/{id}", defaults={ "_format" = "json" })
     */
    public function putAction($proposta, Request $request, $id)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $proposta, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASCAPITULOS_CREATE);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($logged_user, $tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Propostascapitulos entity.');
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
     * Deletes a Crm\Propostascapitulos entity.
     *
     * @FOS\Delete("/{proposta}/propostascapitulos/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction($proposta, Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $proposta, $id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASCAPITULOS_CREATE);

            $this->getService()->delete($tenant, $id_grupoempresarial, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Propostascapitulos entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
