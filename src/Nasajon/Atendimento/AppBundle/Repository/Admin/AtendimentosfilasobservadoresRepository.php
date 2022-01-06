<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\MDABundle\Repository\Servicos\AtendimentosfilasobservadoresRepository as ParentRepository;

/**
 * AtendimentosfilasobservadoresRepository
 *
 */
class AtendimentosfilasobservadoresRepository extends ParentRepository {
    
    public function findAllWithEquipe($tenant, $atendimentofila){
        $sql = "SELECT af.usuario, eu.equipe, e.todosclientes
                FROM servicos.atendimentosfilasobservadores af
                LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = af.usuario AND af.tenant = eu.tenant 
                LEFT JOIN atendimento.equipes e ON e.equipe = eu.equipe
                WHERE af.atendimentofila = :atendimentofila                
                AND af.tenant = :tenant";
        
        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            "tenant" => $tenant,
            "atendimentofila" => $atendimentofila
        ]);
        
        return $stmt->fetchAll();
    }
}
