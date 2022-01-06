<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Doctrine\ORM\NoResultException;
use Nasajon\Atendimento\AppBundle\Form\Admin\CamposcustomizadosType;
use Nasajon\MDABundle\Controller\Servicos\AtendimentoscamposcustomizadosController;
use Nasajon\MDABundle\Entity\Servicos\Atendimentoscamposcustomizados;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;

/**
 * @FuncaoProvisao("ADMIN")
 */
class CamposcustomizadosController extends AtendimentoscamposcustomizadosController {

    /**
     * Lists all Servicos\Atendimentoscamposcustomizados entities.
     *
     * @FOS\Get("/atendimentoscamposcustomizados/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function indexAction(Filter $filter = null, Request $request) {
        return parent::indexAction($filter, $request);
    }

    /**
     * Creates a form to create a Servicos\Atendimentoscamposcustomizados entity.
     *
     * @param Atendimentoscamposcustomizados $entity The entity
     *
     * @return Form The form
     */
    public function createCreateForm(Atendimentoscamposcustomizados $entity, $method = "POST") {
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, CamposcustomizadosType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add( 'submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Displays a form to create a new Servicos\Atendimentoscamposcustomizados entity.
     *
     * @FOS\Get("/atendimentoscamposcustomizados/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Atendimentoscamposcustomizados();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/Camposcustomizados:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     *
     * @FOS\Get("/atendimentoscamposcustomizados/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/Camposcustomizados:index.html.twig');
    }

    /**
     *
     * @FOS\Post("/atendimentoscamposcustomizados/{id}/", defaults={ "_format" = "json" })
     */
    public function reordenarAction($id, Request $request) {
        try {
            $ordem = $request->get('ordem');
            $entity = new Atendimentoscamposcustomizados();
            $entity->setAtendimentocampocustomizado($id);
            $entity->setOrdem($ordem);

            $this->getRepository()->reordenar($entity);

            return new JsonResponse();
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Erro ao reordenar.');
        }
    }

}
