<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Cliente\FollowupsType;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\FollowupsController as FollowupsParentController;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Followups;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FollowupsController extends FollowupsParentController {

    /**
     * @inheritDoc
     */
    public function createCreateForm(Followups $entity, $method = "POST") {
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, FollowupsType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add( 'submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Displays a form to create a new Atendimento\Cliente\SolicitacoesRespostas entity.
     *
     * @FOS\Get("/followups/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Followups();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Cliente:Followups/form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new Atendimento\Cliente\Followups entity.
     *
     * @FOS\Post("/{atendimento}/followups/", defaults={ "_format" = "json" })
     */
    public function createAction($atendimento, Request $request) {
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $constructors = $this->verificateConstructors($tenant, $atendimento);

        $entity = new Followups();

        $entity->setTenant($tenant);
        $entity->setAtendimento($constructors['atendimento']);
        $entity->setCanal('portal');
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $entity->setHistorico($request->get('historico'));

        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($atendimento, $logged_user, $tenant, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

}
