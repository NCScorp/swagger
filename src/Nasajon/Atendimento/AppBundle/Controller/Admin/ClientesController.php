<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Ns\ClientesController as ParentController;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\ClientesVoter;

class ClientesController extends ParentController {
    /**
     * Lists all Ns\Clientes entities.
     * @FOS\Get("/clientes/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $bloqueado = $request->get('bloqueado');                
        $entity = new Clientes();
    
        $this->denyAccessUnlessGranted(ClientesVoter::INDEX, $entity);
               
        $entities = $this->getRepository()->findAll($tenant, $bloqueado, $filter);

        $response = new JsonResponse();
        $response->setData($entities);
        
        return $response;
    }

    /**
     * @FOS\Get("/clientes/template/index.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function templateAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        if (!$this->getRepository()->verificaPermissao($tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }

        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Clientes/index.html.twig');
    }

    /**
     * Displays a form to create a new Ns\Clientes entity.
     * @FOS\Get("/clientes/template/show.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function templateShowAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        if (!$this->getRepository()->verificaPermissao($tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }
        
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Clientes/show.html.twig');
    }

//    /**
//     * Finds and displays a Ns\Clientes entity.
//     *
//     * @FOS\Get("/clientes/{id}", defaults={ "_format" = "json" })
//     */
//    public function getAction($id, Request $request) {
//        try {
//            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//            $entity = $this->getRepository()->find($id, $tenant);
//
//            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getRepository()->fillEntity($entity));
//
//            $response = new JsonResponse();
//            $response->setData($entity);
//
//            return $response;
//        } catch (NoResultException $e) {
//            throw $this->createNotFoundException('Unable to find Ns\Clientes entity.');
//        }
//    }

    /**
     * Retorna lista dos usuarios que podem criar chamados no grupo que o cliente pertence.
     *
     * @FOS\Get("/clientes/{id}/usuariosGrupos", defaults={ "_format" = "json" })
     */
    public function usuariosGruposAction( $id, Request $request) {
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $bloqueado = $request->get('bloqueado');
                            
            $entity = $this->getRepository()->getUsuariosGrupos($id, $tenant);  

            $response = new JsonResponse($entity);                        
                        return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Ns\Clientes\usuariosGrupos entity.');
        }

    }

}
