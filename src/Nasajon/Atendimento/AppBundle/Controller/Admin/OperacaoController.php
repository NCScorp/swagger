<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\OrdensServico\OperacaoController as ParentController;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OperacaoController extends ParentController {

    /**
     * 
     * @FOS\Get("/operacao/template/index.html", defaults={ "_format" = "html" })     
     */
    public function templateAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/OrdensServico/Operacao/index.html.twig');
    }

}
