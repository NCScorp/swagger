<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


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
use Nasajon\MDABundle\Entity\Ns\Tiposatividades;
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\MDABundle\Controller\Ns\TiposatividadesController as NsTiposatividadesController;

/**
 * Ns\Tiposatividades controller.
 */
class TiposatividadesController extends NsTiposatividadesController
{

    /**
     * Lists all NsTiposatividades entities.
     *
     * @FOS\Get("/tiposatividades/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Tiposatividades();

            $this->denyAccessUnlessGranted(EnumAcao::TIPOSATIVIDADES_INDEX);

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
     * Creates a new Ns\Tiposatividades entity.
     *
     * @FOS\Post("/tiposatividades/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');            

            $entity = new Tiposatividades();

            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial);  

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::TIPOSATIVIDADES_CREATE);

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
     * Edits an existing Ns\Tiposatividades entity.
     *
     * @FOS\Put("/tiposatividades/{id}", defaults={ "_format" = "json" })
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

            $this->denyAccessUnlessGranted(EnumAcao::TIPOSATIVIDADES_PUT);

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getService()->update($logged_user, $tenant, $id_grupoempresarial, $entity, $originalEntity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Ns\Tiposatividades entity.');
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
}
