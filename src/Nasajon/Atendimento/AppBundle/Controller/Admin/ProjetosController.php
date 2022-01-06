<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\ProjetosController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class ProjetosController extends ParentController {
    
}
