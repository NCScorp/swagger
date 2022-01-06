<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\MDABundle\Repository\Atendimento\EquipesusuariosRepository as ParentRepository;

class EquipesusuariosRepository extends ParentRepository {
    
    public function verificarSeUsuarioTemEquipe($usuario, $tenant) {
        $sql = "SELECT true FROM atendimento.equipesusuarios WHERE usuario = :usuario AND tenant = :tenant";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([ 'usuario' => $usuario, 'tenant' => $tenant ]);
        return $stmt->fetchColumn();
    }
}