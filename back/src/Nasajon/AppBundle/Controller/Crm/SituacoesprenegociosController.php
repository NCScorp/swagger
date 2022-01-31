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
use Nasajon\MDABundle\Entity\Crm\Situacoesprenegocios;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Situacoesprenegocios controller.
 */
class SituacoesprenegociosController extends \Nasajon\MDABundle\Controller\Crm\SituacoesprenegociosController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/situacoesprenegocios/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::SITUACOESPRENEGOCIOS_INDEX);
        return parent::indexAction($filter, $request);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/situacoesprenegocios/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::SITUACOESPRENEGOCIOS_GET);
        return parent::getAction($id, $request);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Post("/situacoesprenegocios/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::SITUACOESPRENEGOCIOS_CREATE);
        return parent::createAction($request);

    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Put("/situacoesprenegocios/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EnumAcao::SITUACOESPRENEGOCIOS_PUT);
        return parent::putAction($request, $id);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Delete("/situacoesprenegocios/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EnumAcao::SITUACOESPRENEGOCIOS_DELETE);
        return parent::deleteAction($request, $id);
    }
}
