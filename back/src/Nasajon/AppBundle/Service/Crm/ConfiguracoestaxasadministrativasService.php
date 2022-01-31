<?php

namespace Nasajon\AppBundle\Service\Crm;

use LogicException;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\ConfiguracoestaxasadministrativasService as ParentService;

class ConfiguracoestaxasadministrativasService extends ParentService
{

    /**
     * @param string  $id_grupoempresarial
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Configuracoestaxasadministrativas $entity
     * @return string
     * @throws \Exception
     */
    public function insert($id_grupoempresarial, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Crm\Configuracoestaxasadministrativas $entity)
    {
        try {
            $this->getRepository()->begin();
            $response = $this->getRepository()->insert($id_grupoempresarial, $tenant, $logged_user,  $entity);
            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            if(strstr($e->getMessage(), 'uk_crm_conftaxaadmin_configuracao_unica')){
                throw new LogicException('Não é permitido ter mais de uma taxa para o mesmo estabelecimento e seguradora.');
            }
            throw $e;
        }
    }


    /**
     * @param string  $id_grupoempresarial
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Configuracoestaxasadministrativas $entity
     * @return string
     * @throws \Exception
     */
    public function update($id_grupoempresarial, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Crm\Configuracoestaxasadministrativas $entity)
    {
        try {
            $this->getRepository()->begin();
            $response = $this->getRepository()->update($id_grupoempresarial, $tenant, $logged_user,  $entity);
            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            if(strstr($e->getMessage(), 'uk_crm_conftaxaadmin_configuracao_unica')){
                throw new LogicException('Não é permitido ter mais de uma taxa para o mesmo estabelecimento e seguradora.');
            }
            throw $e;
        }
    }
}
