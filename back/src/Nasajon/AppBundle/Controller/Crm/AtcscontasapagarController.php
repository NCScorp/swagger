<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Nasajon\MDABundle\Controller\Crm\AtcscontasapagarController as ParentController;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Atcscontasapagar controller.
 */
class AtcscontasapagarController extends ParentController {
    /**
     * Lists all CrmAtcscontasapagar entities.
     *
     * @FOS\Get("/{atc}/atcscontasapagar/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "prestador","servico","orcamento","atccontaapagar", }})
    */
    public function indexAction($atc, Filter $filter = null, Request $request){
        // Valido permissão
        $this->denyAccessUnlessGranted(EnumAcao::ATCSCONTASPAGAR_GERENCIAR);
        // Chamo função original
        return parent::indexAction($atc, $filter, $request);
    }

    /**
     * Edits an existing Crm\Atcscontasapagar entity.
     *
     * @FOS\Put("/{atc}/atcscontasapagar/{id}", defaults={ "_format" = "json" })
    */
    public function putAction($atc, Request $request, $id){
        // Valido permissão
        $this->denyAccessUnlessGranted(EnumAcao::ATCSCONTASPAGAR_GERENCIAR);
        // Chamo função original
        return parent::putAction($atc, $request, $id);
    }

}
