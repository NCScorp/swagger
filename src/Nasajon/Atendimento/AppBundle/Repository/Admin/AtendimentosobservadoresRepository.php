<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosobservadoresRepository as ParentRepository;

/**
 * AtendimentosobservadoresRepository
 *
 */
class AtendimentosobservadoresRepository extends ParentRepository {

    public function findAllWithEquipe($tenant, $atendimento, $confNotificacaoHabilitado) {
        
        
        $sql = "SELECT af.usuario, eu.equipe, e.todosclientes
                FROM servicos.atendimentosobservadores af
                LEFT JOIN atendimento.usuariosindisponiveis ui ON ui.usuario = af.usuario AND  ui.tenant = af.tenant AND  ui.excluido = FALSE
                LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = af.usuario AND af.tenant = eu.tenant 
                LEFT JOIN atendimento.equipes e ON e.equipe = eu.equipe                
                WHERE af.atendimento = :atendimento
                AND af.tenant = :tenant
                ";
        if(!$confNotificacaoHabilitado){
            $sql .= " AND ui.usuarioindisponivel IS NULL";
        }

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            "tenant" => $tenant,
            "atendimento" => $atendimento
        ]);

        return $stmt->fetchAll();
    }

    public function getUsuarioObservador($tenant, $atendimento, $usuario) {


        $sql = "SELECT
                t0_.atendimentoobservador as \"atendimentoobservador\"             
                FROM servicos.atendimentosobservadores t0_
                WHERE t0_.atendimento = :atendimento 
                AND t0_.usuario = :usuario 
                AND t0_.tenant = :tenant";

        $data = $this->getConnection()->executeQuery($sql, [
                    'atendimento' => $atendimento,
                    'usuario' => $usuario,
                    'tenant' => $tenant,
                ])->fetch();

        if (!$data) {
            throw new NoResultException();
        }
        return EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Servicos\Atendimentosobservadores', $data);
    }

    /**
     * @param string  $atendimento
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Servicos\Atendimentosobservadores $entity
     * @return string 
     * @throws \Exception
     */
    public function insert($atendimento, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Servicos\Atendimentosobservadores $entity) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
            FROM servicos.api_AtendimentoObservadorNovo(row(
                                :atendimento,
                                :usuario,
                                :tenant,
                                :created_by
                            )::servicos.tatendimentoobservadornovo
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);

            $stmt_1->bindValue("atendimento", $atendimento);

            $stmt_1->bindValue("usuario", $logged_user['email']);

            $stmt_1->bindValue("tenant", $tenant);

            $stmt_1->bindValue("created_by", json_encode($logged_user));


            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $retorno = $resposta;

            $entity->setAtendimentoobservador($resposta);


            $retorno = $this->find($retorno, $tenant, $atendimento);

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

}
