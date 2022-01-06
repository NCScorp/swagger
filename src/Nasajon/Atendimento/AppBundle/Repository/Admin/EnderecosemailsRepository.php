<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Repository\Atendimento\Admin\EnderecosemailsRepository as ParentRepository;

class EnderecosemailsRepository extends ParentRepository {

    public function findByEmail($tenant, $email) {
        try {
            $sql = " SELECT enderecoemail FROM atendimento.enderecosemails WHERE tenant = :tenant and ativo = true AND email = :email ";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue('email', $email);
            $stmt->bindValue('tenant', $tenant);
            $stmt->execute();
            $resposta = $stmt->fetchColumn();
        } catch (Exception $ex) {
            throw $e;
        }

        return $resposta;
    }

}
