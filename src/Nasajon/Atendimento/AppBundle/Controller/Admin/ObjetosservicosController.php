<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\ObjetosservicosController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class ObjetosservicosController extends ParentController  {
    
}
