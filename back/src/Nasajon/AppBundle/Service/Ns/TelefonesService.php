<?php

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\TelefonesService as ParentService;

/**
 * TelefonesService
 *
 */
class TelefonesService extends ParentService
{

    /*
        Métodos para impedir carateres não permitidos nos campos
    */
    private function validaInputs($entrada, $campo){

        switch ($campo) {

            case "ddi":
                return preg_match('/^[0-9]+$/', $entrada);

            case "ddd":
                return preg_match('/^[0-9]+$/', $entrada);

            case "telefone":
                return preg_match('/^[0-9\s-]+$/', $entrada);

            case "ramal":
                return preg_match('/^[0-9\s-]+$/', $entrada);
                
            case "observacao":
                return preg_match('/^[a-zA-Z0-9\s:À-ú-]+$/', $entrada);

        }

    }

    /**
     * @param string  $contato
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Ns\Telefones $entity
     * @return string
     * @throws \Exception
     */
    public function insert($contato, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Telefones $entity)
    {

        try {

            if ($entity->getDdi() != null && $this->validaInputs($entity->getDdi(), 'ddi') != 1 ) {
                throw new \LogicException('O DDI da listagem de Telefones do Contato só pode conter números.');
            }

            if ($entity->getDdd() != null && $this->validaInputs($entity->getDdd(), 'ddd') != 1 ) {
                throw new \LogicException('O DDD da listagem de Telefones do Contato só pode conter números.');
            }

            if ($entity->getTelefone() != null && $this->validaInputs($entity->getTelefone(), 'telefone') != 1 ) {
                throw new \LogicException('O campo Telefone da listagem de Telefones do Contato só pode conter números, espaço e hífen.');
            }

            if ($entity->getRamal() != null && $this->validaInputs($entity->getRamal(), 'ramal') != 1 ) {
                throw new \LogicException('O Ramal da listagem de Telefones do Contato só pode conter números, espaço e hífen.');
            }

            if ($entity->getDescricao() != null && $this->validaInputs($entity->getDescricao(), 'observacao') != 1 ) {
                throw new \LogicException('O campo Observação da listagem de Telefones do Contato só pode conter letras, números, espaço, dois pontos e hífen.');
            }
    
            return parent::insert($contato, $logged_user, $tenant, $entity);
     

        } catch(\Exception $e){
    
            throw $e;
        }

    }


    /**
     * @param string  $contato
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Ns\Telefones $entity
     * @return string
     * @throws \Exception
     */
    public function update($contato, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Telefones $entity)
    {
        try {

            if ($entity->getDdi() != null && $this->validaInputs($entity->getDdi(), 'ddi') != 1 ) {
                throw new \LogicException('O DDI da listagem de Telefones do Contato só pode conter números.');
            }

            if ($entity->getDdd() != null && $this->validaInputs($entity->getDdd(), 'ddd') != 1 ) {
                throw new \LogicException('O DDD da listagem de Telefones do Contato só pode conter números.');
            }

            if ($entity->getTelefone() != null && $this->validaInputs($entity->getTelefone(), 'telefone') != 1 ) {
                throw new \LogicException('O campo Telefone da listagem de Telefones do Contato só pode conter números, espaço e hífen.');
            }

            if ($entity->getRamal() != null && $this->validaInputs($entity->getRamal(), 'ramal') != 1 ) {
                throw new \LogicException('O Ramal da listagem de Telefones do Contato só pode conter números, espaço e hífen.');
            }

            if ($entity->getDescricao() != null && $this->validaInputs($entity->getDescricao(), 'observacao') != 1 ) {
                throw new \LogicException('O campo Observação da listagem de Telefones do Contato só pode conter letras, números, espaço, dois pontos e hífen.');
            }
    
            return parent::update($contato, $logged_user, $tenant, $entity);
     

        } catch(\Exception $e){
    
            throw $e;
        }
    }

}
