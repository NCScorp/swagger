<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;

/**
 * @FuncaoProvisao({"USUARIO", "ADMIN"})
 */
class IndexController extends Controller {

    /**
     * @Route("/")
     * @Route("/{html5mode}", name="index", defaults={ "_format" = "html", "html5mode" = "home"}, requirements={"html5mode"=".+"})
     */
    public function indexAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/index.html.twig', [
                    "tenant" => $request->get('tenant')
        ]);
    }

}
