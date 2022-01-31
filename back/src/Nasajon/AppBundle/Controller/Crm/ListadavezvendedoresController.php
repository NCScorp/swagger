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
use Nasajon\MDABundle\Entity\Crm\Listadavezvendedores;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Listadavezvendedores controller.
 */
class ListadavezvendedoresController extends \Nasajon\MDABundle\Controller\Crm\ListadavezvendedoresController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/listadavezvendedores/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZVENDEDORES_INDEX);
        return parent::indexAction($filter, $request);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/listadavezvendedores/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZVENDEDORES_GET);
        return parent::getAction($id, $request);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Post("/listadavezvendedores/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZVENDEDORES_CREATE);
        return parent::createAction($request);
    }


    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Put("/listadavezvendedores/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZVENDEDORES_PUT);
        return parent::putAction($request, $id);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Delete("/listadavezvendedores/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZVENDEDORES_DELETE);
        return parent::deleteAction($request, $id);
    }

}
