<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class GerenciaEquipeController extends FOSRestController {

    /**
     *
     * @FOS\Get("gerenciaequipe/templates/index.html", defaults={ "_format" = "html" })
     *
     */
    public function templateAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/GerenciaEquipe/index.html.twig');
    }
    
    /**
     *
     * @FOS\Get("gerenciaequipe/templates/show.html", defaults={ "_format" = "html" })
     *
     */
    public function templateShowAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/GerenciaEquipe/show.html.twig');
    }

    /**
     *
     * @FOS\Get("gerenciaequipe/{id}/", defaults={ "_format" = "json" })
     *
     */
    public function getEquipeByUsuarioAction(Request $request, $id) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $equipe = $this->get('nasajon_mda.atendimento_equipes_repository')->find($id, $tenant);

        return new JsonResponse($equipe);
    }

     /**
     *
     * @FOS\Post("/gerenciaequipe/adicionausuario/{id}", defaults={ "_format" = "html" })
     */
    public function adicionaAction(Request $request, $id) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        //$equipe = $this->get('nasajon_mda.atendimento_equipes_repository')->find($id, $tenant);
        $usuario = $request->get('usuario');
        $usuariotipo = $request->get('usuariotipo');


        $entity = new \Nasajon\MDABundle\Entity\Atendimento\Equipesusuarios();

        $entity->setUsuario($usuario);
        $entity->setUsuariotipo($usuariotipo);

        $response = $this->get('nasajon_mda.atendimento_equipesusuarios_repository')->insert($id, $tenant, $logged_user, $entity);

        return new JsonResponse($response, JsonResponse::HTTP_CREATED);
    }

    /**
     *
     *
     * @FOS\Delete("/gerenciaequipe/{id}/usuario", defaults={ "_format" = "json" })
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = new \Nasajon\MDABundle\Entity\Atendimento\Equipesusuarios();
            $entity->setUsuario($request->get('usuario'));

            $response = $this->get('nasajon_mda.atendimento_equipesusuarios_repository')->delete($id, $tenant,   $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Não foi possível excluir o usuário da equipe');
        }
    }
}