<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\Atendimento\AppBundle\Repository\Admin\SlascondicoesRepository;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Nasajon\MDABundle\Repository\Atendimento\Admin\SlasRepository as ParentRepository;

class SlasRepository extends ParentRepository {

    /*
     * \Nasajon\Atendimento\AppBundle\Repository\Admin\SlascondicoesRepository
     */

    public $slacondicoesRepo;
    
    /*
     * \OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    protected $slasProducer;

    public function __construct(
            \Doctrine\DBAL\Connection $connection, 
            SlascondicoesRepository $slacondicoesRepo, 
            Producer $slasProducer
        ){
        parent::__construct($connection);
        $this->slacondicoesRepo = $slacondicoesRepo;
        $this->slasProducer = $slasProducer;
        $this->setFilters([]);
    }

    public function find($id, $tenant) {

        $data = parent::find($id, $tenant);

        $data['condicoes_ex'] = $this->slacondicoesRepo->findAll($tenant, $id, 0);
        $data['condicoes_in'] = $this->slacondicoesRepo->findAll($tenant, $id, 1);

        return $data;
    }
    
    public function fillEntity($data) {

        for ($i = 0; $i < count($data['condicoes_ex']); $i++) {
            $data['condicoes_ex'][$i] = $this->slacondicoesRepo->fillEntity($data['condicoes_ex'][$i]);
        }
        $data['condicoes_ex'] = new \Doctrine\Common\Collections\ArrayCollection($data['condicoes_ex']);
        for ($i = 0; $i < count($data['condicoes_in']); $i++) {
            $data['condicoes_in'][$i] = $this->slacondicoesRepo->fillEntity($data['condicoes_in'][$i]);
        }
        $data['condicoes_in'] = new \Doctrine\Common\Collections\ArrayCollection($data['condicoes_in']);
        return parent::fillEntity($data);
    }

    public function insert($tenant, $logged_user, \Nasajon\MDABundle\Entity\Atendimento\Admin\Slas $entity) {

        $this->getConnection()->beginTransaction();
        try {

            $retorno = parent::insert($tenant, $logged_user, $entity);
            $entity->setSla($retorno['sla']);


            foreach ($entity->getCondicoesIn() as $condicao) {
                $condicao->setSla($entity);
                $condicao->setTipo(1);
                $condicao->setTenant($tenant);
                $this->slacondicoesRepo->insert($entity->getSla(), 1, $tenant, $logged_user, $condicao);
            }
            foreach ($entity->getCondicoesEx() as $condicao) {
                $condicao->setSla($entity);
                $condicao->setTipo(0);
                $condicao->setTenant($tenant);
                $this->slacondicoesRepo->insert($entity->getSla(), 0, $tenant, $logged_user, $condicao);
            }
            
            $this->slasProducer->publish(
              json_encode([
                  'tenant'=> $tenant, 
                  'sla'=> $entity->getSla()
              ])
            );

            $this->getConnection()->commit();
            
                        
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }


    public function update($tenant, $logged_user, \Nasajon\MDABundle\Entity\Atendimento\Admin\Slas $entity) {
        $this->getConnection()->beginTransaction();
        try {

            $retorno = parent::update($tenant, $logged_user, $entity);
            $this->deleteCondicoes($tenant, $entity);

            foreach ($entity->getCondicoesIn() as $condicao) {

                $condicao->setSla($entity);
                $condicao->setTipo(1);
                $condicao->setTenant($entity->getTenant());
                $this->slacondicoesRepo->insert($entity->getSla(), 1, $tenant, $logged_user, $condicao);
            }
            foreach ($entity->getCondicoesEx() as $condicao) {
                $condicao->setSla($entity);
                $condicao->setTipo(0);
                $condicao->setTenant($entity->getTenant());
                $this->slacondicoesRepo->insert($entity->getSla(), 0, $tenant, $logged_user, $condicao);
            }

            $this->getConnection()->commit();
            
            $this->slasProducer->publish(
              json_encode([
                  'tenant'=> $tenant, 
                  'sla'=> $entity->getSla()
              ])
            );
            
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }
        
        public function deleteCondicoes($tenant,  \Nasajon\MDABundle\Entity\Atendimento\Admin\Slas $entity) {

            $this->getConnection()->beginTransaction();
            try {
    
                $sql_1 = "SELECT mensagem
                          FROM atendimento.api_slacondicoesexcluir(row(
                                    :sla,
                                    :tenant
                                )::atendimento.tslaexcluir
                );";
    
                $stmt_1 = $this->getConnection()->prepare($sql_1);
                $stmt_1->bindValue("sla", $entity->getSla());
                $stmt_1->bindValue("tenant", $tenant);
                $stmt_1->execute();
                $retorno = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
                $this->getConnection()->commit();
            } catch (Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }
    

}