<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes\UsuariosDisponibilidadesType;


/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class UsuariosDisponibilidadesController extends FOSRestController {

const ATENDIMENTO = 'ATENDIMENTO';

    /**
     * @return \Nasajon\MDABundle\Repository\Atendimento\EquipesRepository;
     */
    public function getRepository()
    {
        return $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository');
    }

    /*
     * @return \Symfony\Component\Form\Form The form
     */

    public function createCreateForm() {
        return $this->get('form.factory')
                        ->createNamedBuilder(NULL, UsuariosDisponibilidadesType::class, NULL, array(
                            'method' => "PUT"
                        ))
                        ->getForm();
    }

    /**
     * Displays a form to create a new Atendimento\Cliente\Solicitacoes entity.
     *
     * @FOS\Get("/configuracoes/template/form.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN"})
     */
    public function configuracoesTemplateFormAction(Request $request) {
        $form = $this->createCreateForm();
        return $this->render('NasajonAtendimentoAppBundle:Admin/Configuracoes/UsuariosDisponibilidade:form.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @FOS\Get("/lista", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN"})
     */
    public function listaAction() {
//        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//        $configuracoesService = $this->get('modelbundle.service.configuracoes');
//        $configuracoes = $configuracoesService->get($tenant, self::ATENDIMENTO);
//
//        if (!$configuracoes) {
//            throw $this->createNotFoundException('Não foi possível carregar suas configurações.');
//        }

        return $this->render('NasajonAtendimentoAppBundle:Admin/UsuariosDisponibilidades:index.html.twig', array());
    }

    /**
     * @FOS\Get("/historico", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN"})
     */
    public function historicoTemplateAction() {
        return $this->render('NasajonAtendimentoAppBundle:Admin/UsuariosDisponibilidades:historico.html.twig', array());
    }

    /**
     * @FOS\Get("/configuracoes", defaults={ "_format" = "json" })
     */
    public function configuracoesGetAction() {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, self::ATENDIMENTO);

        if (!$configuracoes) {
            throw $this->createNotFoundException('Não foi possível carregar suas configurações.');
        }
        return new JsonResponse($configuracoes);
    }

    /**
     * @FOS\Put("/configuracoes", defaults={ "_format" = "json" })
     * @FuncaoProvisao({"ADMIN"})
     */
    public function configuracoesPutAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tenant = $em->getRepository('Nasajon\ModelBundle\Entity\Ns\Tenants')->find($this->get('nasajon_mda.fixed_attributes')->get('tenant'));

        $form = $this->createCreateForm();
        $form->handleRequest($request);

        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant->getId(), self::ATENDIMENTO);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($form->getData() as $key => $value) {
                if ($configuracoes[$key] !== $value) {
                    $em->getRepository('Nasajon\ModelBundle\Entity\Configuracoes')->updateConfiguracao(
                            $tenant, self::ATENDIMENTO, $key, $value
                    );
                }
            }
            $em->flush();
            return new JsonResponse();
        } else {
            return new JsonResponse([ "erro" => "Formulário com erro"], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Lista todos os usuários indisponíveis
     *
     * @FOS\Get("/lista/indisponiveis/")
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function listaIndisponiveisAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $repository = $this->getRepository();
        $retorno = $repository->listaIndisponiveis($tenant);
        return new JsonResponse($retorno);
    }

    /**
     * Verifica se usuário está indisponível
     *
     * @FOS\Get("/verificar")
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function verificarUsuarioAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $usuariosDisponibilidadesService = $this->get('nasajon_atendimento_app_bundle.usuarios_dispinibilidades_service');
        $retorno = $usuariosDisponibilidadesService->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
        return new Response($retorno);
    }


    /**
     * Edits an existing Atendimento\Admin\Artigos entity.
     *
     * @FOS\Put("/alterar", defaults={ "_format" = "json" })
     */
    public function alterarAction(Request $request) {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $usuario = $request->get('usuario');
            $status = $request->get('status');

            $this->getRepository()->alterarDisponibilidade($usuario, $logged_user, $tenant, $status);

            return new JsonResponse();

        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Artigos entity.');
        }
    }

    /**
     * Lista o histórico de disponibilidade do usuário
     *
     * @FOS\Get("/historico/{email}", defaults={ "_format" = "json" })
     */
    public function historicoAction(Request $request, $email) {
        try {
          return new JsonResponse($this->getRepository()->historico($email, $request->get('created_at'), $this->get('nasajon_mda.fixed_attributes')->get('tenant')));
          
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Artigos entity.');
        }
    }

}
