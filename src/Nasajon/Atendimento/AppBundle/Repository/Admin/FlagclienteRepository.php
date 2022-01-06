<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Exception;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Flagcliente;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Flagclientecondicao;
use Nasajon\MDABundle\Repository\Atendimento\Admin\FlagclientecondicaoRepository;
use Nasajon\MDABundle\Repository\Atendimento\Admin\FlagclienteRepository as ParentRepository;

/**
 * FlagclienteRepository
 *
 */
class FlagclienteRepository extends ParentRepository {

    private $condicaoRepository;

    public function __construct($connection, FlagclientecondicaoRepository $condicaoRepository) {
        parent::__construct($connection);
        $this->condicaoRepository = $condicaoRepository;
    }

    public function fillEntity($data) {

        for ($i = 0; $i < count($data['condicoes_ex']); $i++) {
            $data['condicoes_ex'][$i] = $this->condicaoRepository->fillEntity($data['condicoes_ex'][$i]);
        }
        $data['condicoes_ex'] = new \Doctrine\Common\Collections\ArrayCollection($data['condicoes_ex']);
        for ($i = 0; $i < count($data['condicoes_in']); $i++) {
            $data['condicoes_in'][$i] = $this->condicaoRepository->fillEntity($data['condicoes_in'][$i]);
        }
        $data['condicoes_in'] = new \Doctrine\Common\Collections\ArrayCollection($data['condicoes_in']);
        return parent::fillEntity($data);
    }

    public function find($id, $tenant) {

        $data = parent::find($id, $tenant);

        $data['condicoes_ex'] = $this->condicaoRepository->findAll($tenant, $id, 0);
        $data['condicoes_in'] = $this->condicaoRepository->findAll($tenant, $id, 1);

        return $data;
    }

    private function filterCondicoes(array $condicoes) {
        $filtrado = array_filter($condicoes, function(Flagclientecondicao $condicao) {
            return ($condicao->getFlagclientecondicao() != null);
        });
        return array_map(function(Flagclientecondicao $condicao) {
            return $condicao->getFlagclientecondicao();
        }, $filtrado);
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param Flagcliente $entity
     * @return string
     * @throws Exception
     */
    public function updateWithCollections($tenant, $logged_user, Flagcliente $entity, Flagcliente $entityOld) {

        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::update($tenant, $logged_user, $entity);

            $oldEx = $this->filterCondicoes($entityOld->getCondicoesEx()->toArray());
            $newEx = $this->filterCondicoes($entity->getCondicoesEx()->toArray());
            foreach ($entity->getCondicoesEx()->toArray() as $condicao) {
                if (in_array($condicao->getFlagclientecondicao(), $oldEx)) {
                    $this->condicaoRepository->update($tenant, $logged_user, $condicao);
                } else {
                    $this->condicaoRepository->insert($entity->getFlagcliente(), 0, $tenant, $logged_user, $condicao);
                }
            }
            unset($condicao);
            $toDeleteEx = array_diff($oldEx, $newEx);
            foreach ($toDeleteEx as $toDelete) {
                $condicao = new Flagclientecondicao();
                $condicao->setFlagclientecondicao($toDelete);
                $this->condicaoRepository->delete($tenant, $condicao);
                unset($condicao);
            }

            $oldIn = $this->filterCondicoes($entityOld->getCondicoesIn()->toArray());
            $newIn = $this->filterCondicoes($entity->getCondicoesIn()->toArray());
            foreach ($entity->getCondicoesIn()->toArray() as $condicao) {
                if (in_array($condicao->getFlagclientecondicao(), $oldIn)) {
                    $this->condicaoRepository->update($tenant, $logged_user, $condicao);
                } else {
                    $this->condicaoRepository->insert($entity->getFlagcliente(), 1, $tenant, $logged_user, $condicao);
                }
            }
            unset($condicao);
            $toDeleteIn = array_diff($oldIn, $newIn);
            foreach ($toDeleteIn as $toDelete) {
                $condicao = new Flagclientecondicao();
                $condicao->setFlagclientecondicao($toDelete);
                $this->condicaoRepository->delete($tenant, $condicao);
                unset($condicao);
            }


            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Atendimento\Admin\Flagcliente $entity
     * @return string
     * @throws \Exception
     */
    public function insert($tenant, $logged_user, \Nasajon\MDABundle\Entity\Atendimento\Admin\Flagcliente $entity) {

        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::insert($tenant, $logged_user, $entity);

            foreach ($entity->getCondicoesEx()->toArray() as $condicao) {
                $this->condicaoRepository->insert($retorno['flagcliente'], 0, $tenant, $logged_user, $condicao);
            }
            foreach ($entity->getCondicoesIn()->toArray() as $condicao) {
                $this->condicaoRepository->insert($retorno['flagcliente'], 1, $tenant, $logged_user, $condicao);
            }

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

}
