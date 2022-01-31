<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Service\Crm\PropostascapitulosService as ParentService;

/**
 * Sobrescrita para tratar quando o construtor via lookup envia objeto
 */
class PropostascapitulosService extends ParentService{

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $proposta
     * @param mixed $id_grupoempresarial     
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */

    public function find($id, $tenant, $proposta, $id_grupoempresarial){
        
        //Pegando atributo do objeto
        if (is_object($proposta) && !empty($proposta->getProposta())) {
            $proposta = $proposta->getProposta();
        }
        //----
        
        $data = $this->getRepository()->find($id , $tenant, $proposta, $id_grupoempresarial);

        return $data;
    }
}
