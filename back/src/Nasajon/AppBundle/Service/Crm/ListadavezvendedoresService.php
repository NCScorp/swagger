<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Service\Crm\ListadavezvendedoresService as ParentService;

class ListadavezvendedoresService extends ParentService
{

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores $entity
     * @return string
     * @throws \Exception
     */
    public function insert($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores $entity)
    {
        //fazer contagem dos intens e por na variavel.
        $itens = $entity->getItens()->toArray();
        $quantidade = count($itens);
        $entity->setTotalmembros($quantidade);
        return parent::insert($tenant, $id_grupoempresarial, $logged_user, $entity);
    }


    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores $entity
     * @return string
     * @throws \Exception
     */
    public function update($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores $entity, $originalEntity = null)
    {

        //fazer contagem dos intens e por na variavel.
        $itens = $entity->getItens()->toArray();
        $quantidade = count($itens);
        $entity->setTotalmembros($quantidade);
        return parent::update($tenant, $id_grupoempresarial, $logged_user, $entity, $originalEntity);
    }


    /**
    * @param string  $tenant
    * @param string  $id_grupoempresarial
    * @param \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores $entity
    * @return string
    * @throws \Exception
    */
    public function delete($tenant,$id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores $entity){
        try {
            $this->getRepository()->begin();

            $this->persistChildItens($entity->getItens()->toArray(), [], $entity, $tenant, $id_grupoempresarial, null);
            $response = $this->getRepository()->delete($tenant,$id_grupoempresarial,  $entity);                                                                    
            $this->getRepository()->commit();

            return $response;

        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

}
