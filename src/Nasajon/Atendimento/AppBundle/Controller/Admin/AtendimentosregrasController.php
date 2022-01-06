<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Admin\AtendimentosregrasType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\AtendimentosregrasController as AtendimentosregrasParentController;
use Nasajon\MDABundle\Entity\Servicos\Atendimentosregras;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao("ADMIN")
 */
class AtendimentosregrasController extends AtendimentosregrasParentController {

    /**
     * Creates a form to create a Servicos\Atendimentosregras entity.
     *
     * @param Atendimentosregras $entity The entity
     *
     * @return Form The form
     */
    public function createCreateForm(Atendimentosregras $entity, $method = "POST") {

        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, AtendimentosregrasType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add( 'submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Displays a form to create a new Servicos\Atendimentosregras entity.
     *
     * @FOS\Get("/atendimentosregras/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Atendimentosregras();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/Atendimentosregras:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     *
     * @FOS\Get("/atendimentosregras/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/Atendimentosregras:index.html.twig');
    }

    /**
     *
     * @FOS\Post("/atendimentosregras/{id}/", defaults={ "_format" = "json" })
     */
    public function reordenarAction($id, Request $request) {
        try {
            $ordem = $request->get('ordem');
            $entity = new Atendimentosregras();
            $entity->setAtendimentoregra($id);
            $entity->setOrdem($ordem);

            $this->getRepository()->reordenar($entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Erro ao reordenar.');
        }
    }

}
