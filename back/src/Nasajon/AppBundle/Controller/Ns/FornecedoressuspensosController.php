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
use Nasajon\MDABundle\Entity\Ns\Fornecedoressuspensos;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Ns\Fornecedoressuspensos controller.
 */
class FornecedoressuspensosController extends \Nasajon\MDABundle\Controller\Ns\FornecedoressuspensosController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Get("/fornecedoressuspensos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
         */
    public function indexAction( Filter $filter = null, Request $request)
    {
        try{
                       
                        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
                        $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
             

            
            $entity = new Fornecedoressuspensos();
                                       
            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_INDEX);
            
            $entities = $this->getService()->findAll($tenant, $id_grupoempresarial, $filter);

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        }
        catch(InvalidFilterException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
        catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
    }

}
