<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin\Configuracoes;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes\CustomizacoesType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao("ADMIN")
 */
class CustomizacoesController extends Controller {

    const ATENDIMENTO = 'ATENDIMENTO';

    /*
     * @return \Symfony\Component\Form\Form The form
     */

    public function createCreateForm() {
        return $this->get('form.factory')
                        ->createNamedBuilder(NULL,  CustomizacoesType::class, NULL, array(
                            'method' => "PUT"
                        ))
                        ->getForm();
    }

    /**
     * Displays a form to create a new Atendimento\Cliente\Solicitacoes entity.
     *
     * @FOS\Get("/configuracoes/template/customizacoes.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN"})
     */
    public function templateFormAction(Request $request) {
        $form = $this->createCreateForm();
        return $this->render('NasajonAtendimentoAppBundle:Admin/Configuracoes/Portal:customizacoes.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @FOS\Get("/configuracoes/customizacoes", defaults={ "_format" = "json" })
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function getAction() {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, self::ATENDIMENTO);

        if (!$configuracoes) {
            throw $this->createNotFoundException('Não foi possível carregar suas configurações.');
        }
        return new JsonResponse($configuracoes);
    }

    /**
     * @FOS\Put("/configuracoes/customizacoes", defaults={ "_format" = "json" })
     * @FuncaoProvisao({"ADMIN"})
     */
    public function putAction(Request $request) {
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

}