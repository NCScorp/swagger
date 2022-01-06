<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Servicos\Atendimentoscamposcustomizados;

class CamposcustomizadosController extends FOSRestController
{
    /**
     *
     * @return \Nasajon\MDABundle\Repository\Servicos\AtendimentoscamposcustomizadosRepository;
     */
    public function getRepository(){
        return $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository');
    }

    /**
     * Lists all Servicos\Atendimentoscamposcustomizados entities.
     *
     * @FOS\Get("/clientescamposcustomizados/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction( Filter $filter = null, Request $request){
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $tipo = $request->get('tipo');

        //Forçado o valor para garantir que só sejam exibidos os Campos Customizados liberados para o cliente.
        $exibidoformcontato = true;

        $entity = new Atendimentoscamposcustomizados();
           
        $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
    
        $entities = $this->getRepository()->findAll($tenant,$tipo,$exibidoformcontato, $filter);

        $response = new JsonResponse();
        $response->setData($entities);
        
        return $response;

    }
}