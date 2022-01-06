<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Ns;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Ns\ClassificadoresController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class ClassificadoresController extends ParentController {

}
