<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin\Configuracoes;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes\TitulosType;

/**
 * @FuncaoProvisao({"ADMIN"})
 */
class TitulosController extends FOSRestController {

    const ATENDIMENTO = 'ATENDIMENTO';

    /**
     * @FOS\Get("/titulos", defaults={ "_format" = "json" })
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

    /*
     * @return \Symfony\Component\Form\Form The form
     */

    public function createCreateForm() {
        return $this->get('form.factory')
                        ->createNamedBuilder(NULL, TitulosType::class, NULL, array(
                            'method' => "PUT"
                        ))
                        ->getForm();
    }

    /**
     *
     * @FOS\Get("/titulos/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $form = $this->createCreateForm();
        return $this->render('NasajonAtendimentoAppBundle:Admin/Configuracoes/Titulos:form.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @FOS\Put("/titulos", defaults={ "_format" = "json" })
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
//            var_dump($form->getData());die();

            foreach ($form->getData() as $key => $value) {
                if ($configuracoes[$key] !== $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
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
