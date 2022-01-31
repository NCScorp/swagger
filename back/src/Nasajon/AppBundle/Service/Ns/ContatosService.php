<?php

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\ContatosService as ParentService;

class ContatosService extends ParentService{

    private function validaInputs($texto){
        return preg_match('/^[a-zA-Z0-9\s:À-ú-]+$/', $texto);
    }

    /**
     * Sobrescrito para setar o id da pessoa na entity, que estava sendo enviado vazio
     * @param string  $pessoa
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Ns\Contatos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($pessoa, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Contatos $entity)
    {
        try {

            if ($entity->getNome() != null && $this->validaInputs($entity->getNome()) != 1) {
                throw new \LogicException('O Nome só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getPrimeironome() != null && $this->validaInputs($entity->getPrimeironome()) != 1) {
                throw new \LogicException('O Primeiro Nome só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getSobrenome() != null && $this->validaInputs($entity->getSobrenome()) != 1) {
                throw new \LogicException('O Sobrenome só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getCargo() != null && $this->validaInputs($entity->getCargo()) != 1) {
                throw new \LogicException('O Cargo só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getSetor() != null && $this->validaInputs($entity->getSetor()) != 1) {
                throw new \LogicException('O Setor só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getObservacao() != null && $this->validaInputs($entity->getObservacao()) != 1) {
                throw new \LogicException('A Observação só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            $this->getRepository()->begin();

            $response = $this->getRepository()->insert($pessoa, $logged_user, $tenant,  $entity);
            
            /*Setando o id da pessoa na entity*/
            $entity->setPessoa($response['pessoa']);
            /* ------ */
            
            $this->persistChildTelefones(null, $entity->getTelefones()->toArray(), $entity, $logged_user, $tenant);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    
    /**
     * Sobrescrito para pegar o id da pessoa
     * @param array $oldList
     * @param array $newList
     * @param type $entity
     * @param type $logged_user
     * @param type $tenant
     */
    protected function persistChildTelefones($oldList, $newList, $entity, $logged_user, $tenant) {
        
        if(!$oldList){
            $oldList = [];
        }
        
        $newIds = array_map(function (&$telefone) use ($entity){
            $telefone->setIdPessoa($entity->getPessoa());
            return $telefone->getTelefoneId();
        }, $newList);
        
        
        while ($item = array_pop($oldList)){ 
            
            $id = $item->getTelefoneId(); 
            $index = array_search($id, $newIds); 
            
            if ($index === false){ 

                $this->nsTlfnsSrvc->delete($item);

            }else {
                $newitem = $newList[$index]; 
                
                array_splice($newList, $index, 1);
                array_splice($newIds, $index, 1);
                $this->nsTlfnsSrvc->update($entity->getContato(), $logged_user, $tenant, $newitem);
            }
            
            unset($index);
            unset($item);
            unset($newitem);
        }
        /* Sobrescrito para pegar o id da pessoa */
        foreach ($newList as $item) {
            $this->nsTlfnsSrvc->insert($entity->getContato(), $logged_user, $tenant, $item);
        }
        /* ---------------------- */
    }
    /**
     * Sobrescrito para pegar corretamente a entidade original
     * @param type $pessoa
     * @param type $logged_user
     * @param type $tenant
     * @param \Nasajon\MDABundle\Entity\Ns\Contatos $entity
     * @param type $originalEntity
     * @return type
     * @throws \Exception
     */
    public function update($pessoa,$logged_user,$tenant, \Nasajon\MDABundle\Entity\Ns\Contatos $entity, $originalEntity = null ){
        try {

            if ($entity->getNome() != null && $this->validaInputs($entity->getNome()) != 1) {
                throw new \LogicException('O Nome só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getPrimeironome() != null && $this->validaInputs($entity->getPrimeironome()) != 1) {
                throw new \LogicException('O Primeiro Nome só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getSobrenome() != null && $this->validaInputs($entity->getSobrenome()) != 1) {
                throw new \LogicException('O Sobrenome só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getCargo() != null && $this->validaInputs($entity->getCargo()) != 1) {
                throw new \LogicException('O Cargo só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getSetor() != null && $this->validaInputs($entity->getSetor()) != 1) {
                throw new \LogicException('O Setor só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            if ($entity->getObservacao() != null && $this->validaInputs($entity->getObservacao()) != 1) {
                throw new \LogicException('A Observação só pode conter letras, números, espaço, dois pontos e hífen.');
            }

            /* sobrescrito para pegar corretamente a entidade original */
            $originalEntity = $this->fillEntity($this->find($entity->getContato(), $tenant, $pessoa));
            /* ------ */
            
            $this->getRepository()->begin();
            
            $response = $this->getRepository()->update($pessoa, $logged_user, $tenant, $entity, $originalEntity );
            
            $this->persistChildTelefones($originalEntity->getTelefones()->toArray(), $entity->getTelefones()->toArray(), $entity, $logged_user, $tenant);
                
            $this->getRepository()->commit();
            
            return $response;

        }catch(\Exception $e){
             $this->getRepository()->rollBack();
         throw $e;
        }  
    }
}

