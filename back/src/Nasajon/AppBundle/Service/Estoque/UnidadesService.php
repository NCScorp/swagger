<?php

namespace Nasajon\AppBundle\Service\Estoque;

use Exception;
use Nasajon\MDABundle\Service\Estoque\UnidadesService as ParentService;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;


/**
* UnidadesService
*
*/
class UnidadesService  extends ParentService
{
       
    private function codigoValido($codigo) {
        return preg_match('/^[a-zA-Z0-9-]+$/', $codigo) ? false : true;
    }

    /**
      * @param string  $logged_user
      * @param string  $tenant
      * @param string  $id_grupoempresarial
      * @param \Nasajon\MDABundle\Entity\Estoque\Unidades $entity
      * @return string
      * @throws \Exception
    */
    public function insert($logged_user,$tenant,$id_grupoempresarial, \Nasajon\MDABundle\Entity\Estoque\Unidades $entity){
        try {

            if ($this->codigoValido($entity->getCodigo())) {
                throw new \LogicException('O código da unidade só pode conter números, letras e hifens.');
            }
    
            return parent::insert($logged_user, $tenant, $id_grupoempresarial, $entity);
     

        } catch(\Exception $e){
    
            throw $e;
        }

    }


    /**
    * @param string  $logged_user
    * @param string  $tenant
    * @param \Nasajon\MDABundle\Entity\Estoque\Unidades $entity
    * @return string
    * @throws \Exception
    */
    public function update($logged_user,$tenant, \Nasajon\MDABundle\Entity\Estoque\Unidades $entity){
        try {

            if ($this->codigoValido($entity->getCodigo())) {
                throw new \LogicException('O código da unidade só pode conter números, letras e hifens.');
            }
        
            return parent::update($logged_user, $tenant, $entity);

        }catch(\Exception $e){
            throw $e;
        }

    }
    
    
}