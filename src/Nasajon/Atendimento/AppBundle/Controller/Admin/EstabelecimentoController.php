<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Ns\EstabelecimentoController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class EstabelecimentoController extends ParentController {
    
}
