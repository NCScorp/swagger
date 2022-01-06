<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\RepresentantetecnicoController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class RepresentantetecnicoController extends ParentController {

}
