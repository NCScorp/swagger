<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\MDABundle\Controller\Atendimento\Cliente\UsuariosController as ParentController;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UsuariosController extends ParentController {

    /**
     * @FOS\Get("/{cliente}/usuarios/duplicidade")
     */
    public function duplicidadeAction($cliente, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $cliente = $request->get('cliente');
        $conta = $request->get('conta');
        $entity = new \Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios();
        $entity->setCliente($cliente);
        $entities = $this->getRepository()->findAll($tenant, $cliente, null, $conta);
        $response = new JsonResponse();
        $response->setData($entities);
        return $response;
    }

    /**
     *
     * @FOS\Post("/usuarios/adiciona", defaults={ "_format" = "html" })
     */
    public function adicionaAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $entity = new \Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios();

        $entity->setTenant($tenant);
        $entity->setConta($request->get('conta'));
        $entity->setFuncao('U');
        try {
            $this->getRepository()->insert($request->get('cliente'), $tenant, $logged_user, $entity);
        } catch (\Exception $e) {
            
        }
        return new JsonResponse();
    }

    /**
     * Lists all Atendimento\Cliente\Usuarios entities.
     *
     * @FOS\Get("/{cliente}/usuarios/json")
     */
    public function listaUsuariosAction($cliente, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $entity = new \Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios();
        $entity->setCliente($cliente);

        $entities = $this->getRepository()->findAll($tenant, $cliente);

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }

}
