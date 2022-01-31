<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Crm\MalotesService as ParentService;
use Nasajon\MDABundle\Request\FilterExpression;

/**
 * Sobrescrita para recuperação dos documentos do malote
 */
class MalotesService extends ParentService
{

//Metodo para converter número do status para string
private function getStatus($entidade)
{
    if (isset($entidade['status'])) {
        switch ($entidade['status']) {
        case 0:
            return 'Novo';
        case 1:
            return 'Enviado';
        case 2:
            return 'Aceito';
        case 3:
            return 'Aceito Parcialmente';
        case 4:
            return 'Recusado';
        case 5:
            return 'Fechado';
        }
    }
}
    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $id_grupoempresarial
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant, $id_grupoempresarial){

        $data = $this->getRepository()->find($id , $tenant, $id_grupoempresarial);
        $data['statusLabel'] = $this->getStatus($data);
        $filter = new Filter();
        $filterExpression = $filter->getFilterExpression();
        array_push($filterExpression, new FilterExpression('malote', 'eq', $id));

        $filter->setFilterExpression($filterExpression);

        $data['documentos'] = $this->crmMltsdcmntsSrvc->findAll($tenant, $data['malote'], $id_grupoempresarial, $filter);
        return $data;
    }
}