<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\TipodeProjetoController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class TipodeProjetoController extends ParentController {
    
}
