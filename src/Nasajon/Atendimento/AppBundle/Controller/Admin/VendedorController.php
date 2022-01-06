<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\VendedorController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class VendedorController extends ParentController {
    
}
