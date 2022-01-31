<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\AtcsfollowupsService as ParentService;

/**
* Insert sobrescrito para fazer a concatenação da data e hora do realizadoem
*
*/
class AtcsfollowupsService extends ParentService
{
    /**
    * @param string  $atc
    * @param string  $logged_user
    * @param string  $tenant
    * @param \Nasajon\MDABundle\Entity\Crm\Atcsfollowups $entity
    * @return string
    * @throws \Exception
    */
    public function insert($atc, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcsfollowups $entity)
    {
        try {

            //Concatenando a data com a hora no realizadoem
            $realizadoEmCompleto = $entity->getRealizadoemdata() . " " . $entity->getRealizadoemhora();
            $entity->setRealizadoem($realizadoEmCompleto);
            // -------------------

            $this->getRepository()->begin();

            $response = $this->getRepository()->insert($atc, $logged_user, $tenant, $id_grupoempresarial,  $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function findAll($atc,$tenant, $id_grupoempresarial, Filter $filter = null){

        $this->getRepository()->validateOffset($filter);

        //Para cada item da lista de followups, "separo" data e hora
        $lista = $this->getRepository()->findAll($atc,$tenant, $id_grupoempresarial, $filter);
        foreach ($lista as &$item) {
            $item['negociofollowupanexo'] = $this->crmTcsfllwpsnxsSrvc->findAll($item['negociofollowup'],$tenant);
            $dataCompleta = explode(" ", $item['realizadoem']); //todo: usar função date para fazer o slice
            $item['realizadoemdata'] = $dataCompleta[0];
            $item['realizadoemhora'] = $dataCompleta[1];
        }
        return $lista;
    }
    
    
}