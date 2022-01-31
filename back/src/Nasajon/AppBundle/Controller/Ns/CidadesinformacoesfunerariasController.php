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
use Nasajon\MDABundle\Entity\Ns\Cidadesinformacoesfunerarias;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Ns\Cidadesinformacoesfunerarias controller.
 */
class CidadesinformacoesfunerariasController extends \Nasajon\MDABundle\Controller\Ns\CidadesinformacoesfunerariasController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted, para implementar o permissionamento
     *
     * @FOS\Get("/cidadesinformacoesfunerarias/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "municipio","cidadeinformacaofuneraria", }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::CIDADESINFORMACOESFUNERARIAS_INDEX);
        return parent::indexAction($filter, $request);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted, para implementar o permissionamento
     *
     * @FOS\Get("/cidadesinformacoesfunerarias/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::CIDADESINFORMACOESFUNERARIAS_GET);
        return parent::getAction($id, $request);
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted, para implementar o permissionamento
     *
     * @FOS\Post("/cidadesinformacoesfunerarias/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EnumAcao::CIDADESINFORMACOESFUNERARIAS_CREATE);
        return parent::createAction($request);
    }


    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted, para implementar o permissionamento
     *
     * @FOS\Put("/cidadesinformacoesfunerarias/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EnumAcao::CIDADESINFORMACOESFUNERARIAS_PUT);
        return parent::putAction($request, $id);
    }

}
