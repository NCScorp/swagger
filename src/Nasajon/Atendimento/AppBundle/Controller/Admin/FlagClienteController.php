<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Admin\FlagclienteType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Admin\FlagclienteController as ParentController;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Flagcliente;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao("ADMIN")
 */
class FlagClienteController extends ParentController {

    /**
     * 
     * @FOS\Get("/flagcliente/template/index.html", defaults={ "_format" = "html" })     
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/FlagCliente:index.html.twig');
    }

    /**
     * Creates a form to create a Atendimento\Admin\Flagcliente entity.
     *
     * @param Flagcliente $entity The entity
     *
     * @return Form The form
     */
    public function createCreateForm(Flagcliente $entity, $method = "POST") {
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, FlagclienteType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add('submit', SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Flagcliente entity.
     *
     * @FOS\Get("/flagcliente/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Flagcliente();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/FlagCliente:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Edits an existing Atendimento\Admin\Flagcliente entity.
     *
     * @FOS\Put("/flagcliente/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getRepository()->find($id, $tenant);
            $entity = $this->getRepository()->fillEntity($entityArr);
            $entityOld = $this->getRepository()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

            $editForm = $this->createCreateForm($entity, "PUT");
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $this->getRepository()->updateWithCollections($tenant, $logged_user, $entity, $entityOld);

                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Flagcliente entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                                "message" => $e->getMessage(),
                                "entity" => $e->getEntity(),
            ]));
        }
    }

}
