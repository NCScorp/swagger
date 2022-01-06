<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Exception;
use Nasajon\MDABundle\Entity\Servicos\Atendimentosfilas;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosfilasRepository as ParentRepository;

/**
 * AtendimentosfilasRepository
 *
 */
class AtendimentosfilasRepository extends ParentRepository {

    public function findObject($id, $tenant) {
        $data = $this->find($id, $tenant);
        $entity = $this->fillEntity($data);

        //Comentando esse trecho pois a informação dos Observadores já existe dentro da entity original, então esse loop duplicava esta informação.

        // foreach ($data['observadores'] as $observador) {
        //     $entity->addObservadore($this->srvcsTndmntsflsbsrvdrsRpstry->fillEntity($observador));
        // }

        return $entity;
    }

    public function countSolicitacoes($tenant, $atendimentofila) {

        try {

            $sql = "SELECT COUNT(*) as count "
                    . "FROM servicos.atendimentos "
                    . "WHERE responsavel_web = :atendimentofila "
                    . " AND responsavel_web_tipo = 2 "
                    . " AND tenant = :tenant ";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue('atendimentofila', $atendimentofila);
            $stmt->bindValue('tenant', $tenant);
            $stmt->execute();
            $resposta = $stmt->fetchColumn();
        } catch (Exception $e) {
            throw $e;
        }

        return $resposta;
    }

    public function deleteUpdate(Atendimentosfilas $entity, $novafila, $updated_by) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                      FROM servicos.api_atendimentofilaexcluirereatribuir(row(
                                :atendimentofila,
                                :novafila,
                                :updated_by
                            )::servicos.tatendimentofilaexcluirereatribuir
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);

            $stmt_1->bindValue("atendimentofila", $entity->getAtendimentofila());
            $stmt_1->bindValue("novafila", $novafila);
            $stmt_1->bindValue("updated_by", $updated_by);

            $stmt_1->execute();

            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $this->getConnection()->commit();

            return $resposta;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function insert($tenant, $logged_user, Atendimentosfilas $entity) {

        $this->getConnection()->beginTransaction();
        try {

            $retorno = parent::insert($tenant, $logged_user, $entity);
            $entity->setAtendimentofila($retorno);

            // Insere novos observadores de fato
            foreach ($entity->getObservadores() as $observador) {
                $observador->setAtendimentofila($entity->getAtendimentofila());
                $observador->setTenant($entity->getTenant());
                $this->srvcsTndmntsflsbsrvdrsRpstry->insert($observador->getAtendimentofila()['atendimentofila'], $observador->getTenant(), $logged_user, $observador);
            }

            $this->getConnection()->commit();

            return $retorno;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function update($logged_user, Atendimentosfilas $entityNew, $originalEntity = null) {

        $this->getConnection()->beginTransaction();
        try {

            $entityOld = $this->findObject($entityNew->getAtendimentofila(), $entityNew->getTenant());

            $retorno = parent::update($logged_user, $entityNew);

            // Remove os observadores que foram retirados
            foreach ($entityOld->getObservadores() as $observadorOld) {
                $remover = true;
                foreach ($entityNew->getObservadores() as $observadorNew) {
                    if ($observadorNew->getUsuario() == $observadorOld->getUsuario()) {
                        $remover = false;
                    }
                }
                if ($remover) {
                    $this->srvcsTndmntsflsbsrvdrsRpstry->delete($observadorOld);
                }
            }

            // Insere somente os novos observadores de fato
            foreach ($entityNew->getObservadores() as $observadorNew) {
                $inserir = true;
                foreach ($entityOld->getObservadores() as $observadorOld) {
                    if ($observadorNew->getUsuario() == $observadorOld->getUsuario()) {
                        $inserir = false;
                    }
                }
                if ($inserir) {
                    $observadorNew->setAtendimentofila($entityNew->getAtendimentofila());
                    $observadorNew->setTenant($entityNew->getTenant());
                    $this->srvcsTndmntsflsbsrvdrsRpstry->insert($observadorNew->getAtendimentofila(), $observadorNew->getTenant(), $logged_user, $observadorNew);
                }
            }

            $this->getConnection()->commit();

            return $retorno;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }
    
    public function exists($id, $tenant) {
      $sql = "SELECT COUNT(*) AS qtd FROM servicos.atendimentosfilas WHERE atendimentofila = :id AND tenant = :tenant;";
      $stmt = $this->getConnection()->prepare($sql);
      $stmt->bindValue("id", $id);
      $stmt->bindValue("tenant", $tenant);
      $stmt->execute();
      return $stmt->fetchColumn();
    }

}
