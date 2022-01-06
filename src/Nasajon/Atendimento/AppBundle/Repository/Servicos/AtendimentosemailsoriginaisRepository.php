<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Nasajon\MDABundle\Repository\Servicos\AtendimentosemailsoriginaisRepository as ParentRepository;

class AtendimentosemailsoriginaisRepository extends ParentRepository {

    public function findOriginal($id, $tenant, $tipo) {
        $sql = "";

        switch ($tipo) {
            case "1":
                $sql = "SELECT * FROM servicos.atendimentosemailsoriginais WHERE atendimento = :id AND tenant = :tenant AND followup IS NULL";
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->execute(['id' => $id, 'tenant' => $tenant]);
                break;
            case "6":
                $sql = "SELECT * FROM servicos.atendimentosemailsoriginais WHERE followup = :id AND tenant = :tenant";
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->execute(['id' => $id, 'tenant' => $tenant]);
                break;
        }

        $response = $stmt->fetchAll();
        return $response;
    }

}
