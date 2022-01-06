<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Nasajon\MDABundle\Entity\Servicos\ServicoTecnico;
use Nasajon\MDABundle\Repository\Servicos\ServicoTecnico\ChecklistRepository;
use Nasajon\MDABundle\Repository\Servicos\ServicoTecnicoRepository as ParentRepository;

/**
 * ServicoTecnicoRepository
 *
 */
class ServicoTecnicoRepository extends ParentRepository {

    /**
     * \Nasajon\MDABundle\Repository\Servicos\ServicoTecnico\ChecklistRepository
     */
    protected $srvcsSrvcTcncChcklstRpstry;

    public function __construct($connection, $srvcsSrvcTcncChcklstRpstry) {
        parent::__construct($connection, $srvcsSrvcTcncChcklstRpstry);
        $this->srvcsSrvcTcncChcklstRpstry = $srvcsSrvcTcncChcklstRpstry;
        $this->setFilters([
            'codigo' => 'codigo',
            'descricao' => 'descricao',
            'valor' => 'valor',
        ]);
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Servicos\ServicoTecnico $entity
     * @return string
     * @throws \Exception
     */
    public function insert($tenant, \Nasajon\MDABundle\Entity\Servicos\ServicoTecnico $entity) {

        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::insert($tenant, $entity);
            $entity->setServicotecnico($retorno['servicotecnico']);
            if ($entity->getServicostecnicoschecklist()) {
                $this->deleteChecklist($tenant, $entity);
                foreach ($entity->getServicostecnicoschecklist() as $checklist) {
                    $checklist->setServicotecnico($entity);
                    $checklist->setTenant($tenant);
                    $this->srvcsSrvcTcncChcklstRpstry->insert($entity->getServicotecnico(), $tenant, $checklist);
                }
            }
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Servicos\ServicoTecnico $entity
     * @return string
     * @throws \Exception
     */
    public function update($tenant, \Nasajon\MDABundle\Entity\Servicos\ServicoTecnico $entity, $originalEntity = null) {
        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::update($tenant, $entity);
            foreach ($entity->getServicostecnicoschecklist() as $checklist) {
                $this->deleteChecklist($tenant, $entity);
                $checklist->setServicotecnico($entity);
                $checklist->setTenant($tenant);
                $this->srvcsSrvcTcncChcklstRpstry->insert($entity->getServicotecnico(), $tenant, $checklist);
            }
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Servicos\ServicoTecnico $entity
     * @return string
     * @throws \Exception
     */
    public function deleteChecklist($tenant, \Nasajon\MDABundle\Entity\Servicos\ServicoTecnico $entity) {
        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
            FROM servicos.api_servicotecnicochecklistexcluirtodos(row(
                                :servicotecnico,
                                :tenant
                            )::servicos.tservicotecnicochecklistexcluirtodos
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);

            $stmt_1->bindValue("servicotecnico", $entity->getServicotecnico());

            $stmt_1->bindValue("tenant", $tenant);


            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $retorno = $resposta;


            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

}
