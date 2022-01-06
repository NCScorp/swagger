<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Ns\ContatosController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class ContatosController extends ParentController {


}
