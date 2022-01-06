<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Nasajon\MDABundle\Entity\Servicos\Visita\Tecnico;
use Nasajon\MDABundle\Repository\Servicos\Visita\TecnicoRepository;
use Nasajon\MDABundle\Repository\Servicos\VisitaRepository as ParentRepository;

class VisitaRepository extends ParentRepository {

    /**
     * \Nasajon\MDABundle\Repository\Servicos\Visita\TecnicoRepository
     */
    protected $srvcsVstTcncRpstry;

    public function __construct($connection, $srvcsVstTcncRpstry) {
        parent::__construct($connection, $srvcsVstTcncRpstry);
        $this->srvcsVstTcncRpstry = $srvcsVstTcncRpstry;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Servicos\Visita $entity
     * @return string 
     * @throws \Exception
     */
    public function update($tenant, $logged_user, \Nasajon\MDABundle\Entity\Servicos\Visita $entity) {
        parent::update($tenant, $logged_user, $entity);

        if ($entity->getTecnico()) {
            foreach ($entity->getTecnico() as $tecnico) {
                $tecnico->setTenant($tenant);
                $tecnico->setOrdemservicovisita($entity->getOrdemservicovisita());
                $this->srvcsVstTcncRpstry->insert($entity->getOrdemservicovisita(), $logged_user, $tenant, $tecnico);
            }
        }
    }

    private function deleteTecnicoTodos($tenant, \Nasajon\MDABundle\Entity\Servicos\Visita $entity) {
        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                      FROM servicos.api_visitatecnicoexcluirtodos(row(
                                :ordemservicovisita,
                                :tenant
                            )::servicos.tvisitatecnicoexcluirtodos
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("ordemservicovisita", $entity->getOrdemservicovisita());
            $stmt_1->bindValue("tenant", $tenant);
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
