<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Admin\EnderecosemailsController as ParentController;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Enderecosemails;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao("ADMIN")
 */
class EnderecosemailsController extends ParentController {

    /**
     * 
     * @FOS\Get("/enderecosemails/template/index.html", defaults={ "_format" = "html" })     
     */
    public function templateAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Enderecosemails/index.html.twig');
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Enderecosemails entity.
     *
     * @FOS\Get("/enderecosemails/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Enderecosemails();
        $form = $this->createCreateForm($entity);
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Enderecosemails/form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

}
