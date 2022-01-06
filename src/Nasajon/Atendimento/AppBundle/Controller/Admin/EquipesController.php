<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\EquipesType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\EquipesController as ParentController;
use Nasajon\MDABundle\Entity\Atendimento\Equipes;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @FuncaoProvisao("ADMIN")
 */
class EquipesController extends ParentController {

    /**
     * Creates a form to create a Atendimento\Equipes entity.
     *
     * @param Equipes $entity The entity
     *
     * @return Form The form
     */
    public function createCreateForm(Equipes $entity, $method = "POST") {
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, EquipesType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add('submit', SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Displays a form to create a new Atendimento\Equipes entity.
     *
     * @FOS\Get("/equipes/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Equipes();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/Equipes:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     *
     * @FOS\Get("/equipes/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/Equipes:index.html.twig');
    }

    /**
     * Creates a new Atendimento\Equipes entity.
     *
     * @FOS\Post("/equipes/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $entity = new Equipes();

        $entity->setTenant($tenant);

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($tenant, $logged_user, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Lists all Atendimento\Equipes entities.
     *
     * @FOS\Get("/equipes/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function indexAction(Filter $filter = null, Request $request) {
        return parent::indexAction($filter, $request);
    }


    /**
     * Lista todos os usuários que já foram alocados em uma equipe.
     *
     * @FOS\Get("/equipes/usuariosalocados/")
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function listUsuariosAlocadosEquipeAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $repository = $this->getRepository();
        $retorno = $repository->listUsuariosAlocadosEquipe($tenant);
        return new JsonResponse($retorno);
    }
}
