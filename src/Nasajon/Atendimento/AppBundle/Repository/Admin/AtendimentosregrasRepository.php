<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Exception;
use Nasajon\MDABundle\Entity\Servicos\Atendimentosregras;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosregrasRepository as ParentRepository;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosregrascondicoesRepository;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosregrasacoesRepository;

/**
 * AtendimentosregrasRepository
 *
 */
class AtendimentosregrasRepository extends ParentRepository {

    /**
     * \Nasajon\MDABundle\Repository\Servicos\AtendimentosregrascondicoesRepository
     */
    protected $srvcsTndmntsrgrscndcsRpstry;

    public function __construct($connection, AtendimentosregrascondicoesRepository $srvcsTndmntsrgrscndcsRpstry, AtendimentosregrasacoesRepository $srvcsTndmntsrgrscsRpstry) {
        parent::__construct($connection, $srvcsTndmntsrgrscsRpstry);
        $this->srvcsTndmntsrgrscndcsRpstry = $srvcsTndmntsrgrscndcsRpstry;
    }

    public function fillEntity($data) {

        for ($i = 0; $i < count($data['condicoes_ex']); $i++) {
            $data['condicoes_ex'][$i] = $this->srvcsTndmntsrgrscndcsRpstry->fillEntity($data['condicoes_ex'][$i]);
        }
        $data['condicoes_ex'] = new \Doctrine\Common\Collections\ArrayCollection($data['condicoes_ex']);
        for ($i = 0; $i < count($data['condicoes_in']); $i++) {
            $data['condicoes_in'][$i] = $this->srvcsTndmntsrgrscndcsRpstry->fillEntity($data['condicoes_in'][$i]);
        }
        $data['condicoes_in'] = new \Doctrine\Common\Collections\ArrayCollection($data['condicoes_in']);
        return parent::fillEntity($data);
    }

    public function find($id, $tenant) {

        $data = parent::find($id, $tenant);

        $data['condicoes_ex'] = $this->srvcsTndmntsrgrscndcsRpstry->findAll($tenant, $id, 0);
        $data['condicoes_in'] = $this->srvcsTndmntsrgrscndcsRpstry->findAll($tenant, $id, 1);

        return $data;
    }

    public function insert($tenant, $logged_user, Atendimentosregras $entity) {

        $this->getConnection()->beginTransaction();
        try {

            $retorno = parent::insert($tenant, $logged_user, $entity);
            $entity->setAtendimentoregra($retorno['atendimentoregra']);


            foreach ($entity->getCondicoesIn() as $condicao) {
                $condicao->setAtendimentoregra($entity);
                $condicao->setTipo(1);
                $condicao->setTenant($tenant);
                $this->srvcsTndmntsrgrscndcsRpstry->insert($entity->getAtendimentoregra(), 1, $tenant, $logged_user, $condicao);
            }
            foreach ($entity->getCondicoesEx() as $condicao) {
                $condicao->setAtendimentoregra($entity);
                $condicao->setTipo(0);
                $condicao->setTenant($tenant);
                $this->srvcsTndmntsrgrscndcsRpstry->insert($entity->getAtendimentoregra(), 0, $tenant, $logged_user, $condicao);
            }

            foreach ($entity->getAcoes() as $acao) {
                $acao->setAtendimentoregra($entity);
                $acao->setTenant($tenant);
                $this->srvcsTndmntsrgrscsRpstry->insert($entity->getAtendimentoregra(), $tenant, $logged_user, $acao);
            }


            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function update($logged_user, Atendimentosregras $entity, $originalEntity = null) {

        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::update($logged_user, $entity);
            $this->deleteCondicoesEAcoes($entity);
            foreach ($entity->getCondicoesIn() as $condicao) {
                $condicao->setAtendimentoregra($entity);
                $condicao->setTipo(1);
                $condicao->setTenant($entity->getTenant());
                $this->srvcsTndmntsrgrscndcsRpstry->insert($entity->getAtendimentoregra(), 1, $entity->getTenant(), $logged_user, $condicao);
            }
            foreach ($entity->getCondicoesEx() as $condicao) {
                $condicao->setAtendimentoregra($entity);
                $condicao->setTipo(0);
                $condicao->setTenant($entity->getTenant());
                $this->srvcsTndmntsrgrscndcsRpstry->insert($entity->getAtendimentoregra(), 0, $entity->getTenant(), $logged_user, $condicao);
            }

            foreach ($entity->getAcoes() as $acao) {
                $acao->setAtendimentoregra($entity);
                $acao->setTenant($entity->getTenant());
                $this->srvcsTndmntsrgrscsRpstry->insert($entity->getAtendimentoregra(), $entity->getTenant(), $logged_user, $acao);
            }

            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function deleteCondicoesEAcoes(Atendimentosregras $entity) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                      FROM servicos.api_atendimentoregraexcluicondicoeseacoes(row(
                                :atendimentoregra
                            )::servicos.tatendimentoregraexcluir
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("atendimentoregra", $entity->getAtendimentoregra());
            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
            $retorno = $resposta;
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

}
