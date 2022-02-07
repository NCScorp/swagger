<?php

namespace AppBundle\Controller\Web;

use LogicException;
use Nasajon\MDABundle\Request\Filter;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\Web\ConfiguracoesService;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Controller\Web\ConfiguracoesController as ParentController;

class ConfiguracoesController extends ParentController
{
    /**
     * @return ConfiguracoesService;
     */
    public function getService()
    {
        return $this->get('Nasajon\MDABundle\Service\Web\ConfiguracoesService');
    }

    /**
     * @FOS\Get("/configuracoes/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields": {"chave","valor","sistema"}})
     * @return JsonResponse
    */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $estabelecimento = $this->get('nasajon_mda.fixed_attributes')->get('estabelecimento');
            $entities = $this->getService()->getConfiguracoesFormatadasPorEstabelecimento($tenant, $estabelecimento);

            return new JsonResponse($entities, JsonResponse::HTTP_OK);
        } catch(InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);            
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);            
        } catch(\Exception $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}