<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\UsuariosController as UsuariosParentController;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios;
use Nasajon\MDABundle\Form\Atendimento\Cliente\UsuariosType;

class UsuariosController extends UsuariosParentController {


    /**
     *
     * @FOS\Get("/usuarios/autoprovisionamento", defaults={ "_format" = "html" })
     */
    public function autoprovisionamentoAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $token = $request->get('token');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $cliente_id = base64_decode($token);

        $entity = new \Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios();

        $entity->setTenant($tenant);
        $entity->setConta($logged_user['email']);
        $entity->setFuncao('A');
        try {
            $this->getRepository()->insert($cliente_id, $tenant, $logged_user, $entity);
        } catch (\Exception $e) {
            
        }
        return $this->redirectToRoute('atendimento_cliente_index', [ "tenant" => $request->get('tenant')]);
    }


}
