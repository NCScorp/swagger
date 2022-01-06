<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Resumos;
use Nasajon\MDABundle\Controller\Atendimento\Admin\ResumosController as ResumosControllerParentController;

/**
 * @FuncaoProvisao({"ADMIN"})
 */
class ResumosController extends ResumosControllerParentController {
    
    
}