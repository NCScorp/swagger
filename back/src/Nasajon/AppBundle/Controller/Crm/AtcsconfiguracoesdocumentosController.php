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
use Nasajon\MDABundle\Entity\Crm\Atcsconfiguracoesdocumentos;
use Nasajon\MDABundle\Entity\Crm\Atcsconfiguracoestemplatesemails;
use Nasajon\MDABundle\Controller\Crm\AtcsconfiguracoesdocumentosController as ParentController;
use Nasajon\MDABundle\Service\Crm\AtcsconfiguracoestemplatesemailsService;

/**
 * Crm\Atcsconfiguracoesdocumentos controller.
 */
class AtcsconfiguracoesdocumentosController extends ParentController
{

    /**
     * Retorna o template de email padrão
     *
     * @FOS\Get("/atcsconfiguracoesdocumentos/templateemailpadrao/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function retornaTemplateEmailAction()
    {
        try {

            $templateEmailPadrao = [
                'nome' => 'Padrão',
                // 'codigo' => '1' //Considerando 1 como código padrão do template de email padrão
                'codigo' => 'crmweb_relatorio_email' // Código presente na tabela de maladireta.templatesemails
            ];

            $arrTemplateEmailPadrao[] = $templateEmailPadrao;

            $response = new JsonResponse();
            $response->setData($arrTemplateEmailPadrao);

            return $response;

        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
