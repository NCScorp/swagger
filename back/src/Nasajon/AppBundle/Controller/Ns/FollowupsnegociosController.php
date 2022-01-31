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
use Nasajon\MDABundle\Entity\Ns\Followupsnegocios;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Ns\Followupsnegocios controller.
 */
class FollowupsnegociosController extends \Nasajon\MDABundle\Controller\Ns\FollowupsnegociosController
{

    public function verificateConstructors($tenant,$proposta){
        //sobrescrição necessária para injetar id_grupoempresarial para buscar o negócio
        $constructors  = [];
        $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
        $propostaObject = $this->get('Nasajon\MDABundle\Service\Crm\NegociosService')->findObject($proposta, $tenant, $id_grupoempresarial);
        if (!$propostaObject) {
            throw $this->createNotFoundException('Unable to find negocios entity.');
        }
        $this->denyAccessUnlessGranted('view', $propostaObject);        
        $constructors['proposta'] = $propostaObject;
        return $constructors;
    }

    /**
     * Lists all NsFollowupsnegocios entities.
     *
     * @FOS\Get("/{proposta}/followupsnegocios/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "proposta","followup", }})
    */
    public function indexAction($proposta, Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $constructors = $this->verificateConstructors($tenant,$proposta);
            $entity = new Followupsnegocios();
            $entity->setProposta($constructors['proposta']);
            $this->denyAccessUnlessGranted(EnumAcao::FOLLOWUPSNEGOCIOS_INDEX);
            $entities = $this->getService()->findAll($tenant, $proposta, $filter);
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
     * Creates a new Ns\Followupsnegocios entity.
     *
     * @FOS\Post("/{proposta}/followupsnegocios/", defaults={ "_format" = "json" })
    */
    public function createAction($proposta, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $constructors = $this->verificateConstructors($tenant,$proposta);
            $entity = new Followupsnegocios();
            $entity->setTenant($tenant);
            $entity->setProposta($constructors['proposta']);
            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);
            $this->denyAccessUnlessGranted(EnumAcao::FOLLOWUPSNEGOCIOS_CREATE);
            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($proposta, $tenant, $logged_user, $entity);
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
