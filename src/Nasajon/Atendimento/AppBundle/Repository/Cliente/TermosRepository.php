<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Doctrine\ORM\NoResultException;
use Exception;
use Nasajon\MDABundle\Repository\AbstractRepository;
use PDO;

/**
 * TermosRepository
 *
 */
class TermosRepository extends AbstractRepository {
  
   public function checkTermoAceito($conta, $tenant){
        $sql = "SELECT count(*) FROM atendimento.termosaceitos WHERE tenant = :tenant AND conta = :conta";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'tenant' => $tenant,
            'conta' => $conta
        ]);
        $response = $stmt->fetchColumn(0);
        return $response;
   }
    
   public function termoAceitar($tenant, $logged_user) {
        $sql = "select * from atendimento.api_termoaceitar(row(
                  :conta,
                  :nome,
                  :created_by,
                  :tenant
              )::atendimento.ttermoaceitar);
";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'conta' => $logged_user['email'],
            'nome' => $logged_user['nome'],
            'created_by' => json_encode($logged_user),
            'tenant' => $tenant
        ]);
        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $response;;
    }
  
}