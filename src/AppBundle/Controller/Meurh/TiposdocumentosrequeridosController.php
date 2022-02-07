<?php

namespace AppBundle\Controller\Meurh;

use Nasajon\MDABundle\Controller\Meurh\TiposdocumentosrequeridosController as ParentController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Type\InvalidFilterException;
use LogicException;
use Nasajon\MDABundle\Entity\Meurh\Tiposdocumentosrequeridos;

/**
 * Meurh\Tiposdocumentosrequeridos controller.
 */
class TiposdocumentosrequeridosController extends ParentController
{
    /** 
     * @FOS\Get("/tiposdocumentosrequeridos/configuracoes/")
     * 
     * @return JsonResponse
     */
    public function configuracoesAction(Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $estabelecimento = $this->get('nasajon_mda.fixed_attributes')->get('estabelecimento');

            $entity = new Tiposdocumentosrequeridos();
            $entity->setEstabelecimento($estabelecimento);

            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);

            $entities = $this->getService()->findAllConfiguracoes($tenant, $estabelecimento);

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
