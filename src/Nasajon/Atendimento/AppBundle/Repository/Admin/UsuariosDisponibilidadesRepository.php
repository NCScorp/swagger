<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\ORM\NoResultException;
use Exception;
use Nasajon\MDABundle\Repository\AbstractRepository;
use PDO;

/**
 * UsuariosDisponibilidadesRepository
 *
 */
class UsuariosDisponibilidadesRepository extends AbstractRepository {


    public function listaIndisponiveis($tenant) {
        $sql = "SELECT usuario FROM atendimento.usuariosindisponiveis WHERE tenant = :tenant AND excluido = false";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            'tenant' => $tenant
        ]);

        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $response;
    }

    public function verificarSeUsuarioIndisponivel($usuario, $tenant) {
        $sql = "SELECT true FROM atendimento.usuariosindisponiveis WHERE usuario = :usuario AND tenant = :tenant AND excluido = false";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            'usuario' => $usuario,
            'tenant' => $tenant
        ]);

        $response = $stmt->fetchColumn();
        return $response;
    }

    public function alterarDisponibilidade($usuario, $logged_user, $tenant, $status) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                        FROM atendimento.api_usuariodisponibilidadealterar(row(
                                :usuario,
                                :tenant,
                                :excluido,
                                :created_by
                            )::atendimento.tusuariodisponibilidadealterar
            )";

            $stmt_1 = $this->getConnection()->prepare($sql_1);

            $stmt_1->bindValue("usuario", $usuario);
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("excluido", (int)$status);
            $stmt_1->bindValue("created_by", json_encode($logged_user));

            $stmt_1->execute();
            $retorno = $stmt_1->fetchColumn();

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

    public function historico($usuario, $created_at, $tenant) {
      $binds = [ 'usuario' => $usuario, 'tenant' => $tenant ];
      $filter = "";
      
      if (!empty($created_at)) {
        $filter =  "AND ui.created_at < :created_at";
        $binds['created_at'] = $created_at;
      }
      
      $sql = "SELECT ui.created_at, ui.updated_at, e.nome, eu.usuariotipo
              FROM atendimento.usuariosindisponiveis ui
              LEFT JOIN atendimento.equipesusuarios eu on eu.usuario = ui.usuario
              LEFT JOIN atendimento.equipes e on e.equipe = eu.equipe
              WHERE ui.usuario = :usuario AND ui.tenant = :tenant ".$filter."
              ORDER BY created_at DESC
              LIMIT 30";
      
      $stmt = $this->getConnection()->prepare($sql);
      $stmt->execute($binds);
      return $stmt->fetchAll();
      }
}
