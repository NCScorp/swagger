<?php

namespace Nasajon\AppBundle\Service\Crm;

use LogicException;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Service\Crm\ResponsabilidadesfinanceirasService as ParentService;

/**
* ResponsabilidadesfinanceirasService
*
*/
class ResponsabilidadesfinanceirasService extends ParentService
{
    public function salvarResponsabilidadeFinanceira($atc,$tenant,$logged_user, $id_grupoempresarial, \Nasajon\AppBundle\Entity\Crm\ResponsabilidadesFinanceirasEmLote $entity, \Nasajon\AppBundle\Entity\Crm\ResponsabilidadesFinanceirasEmLote $entityOriginal){
        try {
            $this->getRepository()->begin();
            $this->validarDados($entity);

            $this->persistChildResponsabilidadesFinanceiras(
                $entityOriginal->getResponsabilidadesfinanceiras()->toArray(),
                $entity->getResponsabilidadesfinanceiras()->toArray(), 
                $entity, $logged_user, $tenant, $id_grupoempresarial, $atc
            );
            
            $this->getRepository()->commit();
        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }
 
    protected function persistChildResponsabilidadesFinanceiras($oldList, $newList, $entity, $logged_user, $tenant, $id_grupoempresarial, $atc) {
        if(!$oldList){
            $oldList = [];
        }
        $newIds = array_map(function ($entity) {
            return $entity->getResponsabilidadefinanceira();
        }, $newList);

        while ($item = array_pop($oldList)) {
            $id = $item->getResponsabilidadefinanceira();
            $index = array_search($id, $newIds);

            if ($index === false) {
                $this->delete($tenant, $logged_user, $item);
            } else {
                $newitem = $newList[$index];
                $originalItem = $this->findObject($newitem->getResponsabilidadefinanceira(), $tenant, $atc, $id_grupoempresarial);
                array_splice($newList, $index, 1);
                array_splice($newIds, $index, 1);
                $this->update($tenant, $logged_user, $id_grupoempresarial, $newitem, $originalItem);
            }

            unset($index);
            unset($item);
            unset($newitem);
        }

        //Insiro os novos elementos
        foreach ($newList as $item) {
            $this->insert($entity->getNegocio(), $tenant, $logged_user, $id_grupoempresarial, $item);
        }
    }

    /**
     * @param string  $negocio
     * @param string  $tenant
     * @param string  $logged_user
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Responsabilidadesfinanceiras $entity
     * @return string
     * @throws \Exception
     */
    public function insert($negocio, $tenant, $logged_user, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Responsabilidadesfinanceiras $entity)
    {
        if($entity->getFaturamentotipo() == '2'){
            $entity->setGeranotafiscal(false);
        } elseif ($entity->getFaturamentotipo() == '3' || $entity->getFaturamentotipo() == '4' ){
            $entity->setGeranotafiscal(true);
        } elseif ($entity->getFaturamentotipo() == '1') {
            throw new LogicException("Não é possível gerar responsabilidade para orçamentos marcados para não gerar nota.", 1);
        }
        return parent::insert($negocio, $tenant, $logged_user, $id_grupoempresarial, $entity);
    }


    /**
     * Sobrescrito para incluir os dados de rateio da entitdade responsabilidadesfinanceirasvalores
     * @return array
     */
    public function findAll($tenant,$atc,$id_grupoempresarial, Filter $filter = null){
        $retorno = parent::findAll($tenant,$atc,$id_grupoempresarial,$filter);

        foreach ($retorno as &$responsabilidade) {
            $dados = $this->crmRspnsblddsfnncrsvlrsSrvc->findAll(
                $tenant, $id_grupoempresarial, $responsabilidade['responsabilidadefinanceira']);
            $responsabilidade['responsabilidadesfinanceirasvalores'] = $dados;
        }

        return $retorno;
    }

    protected function validarDados($entity){
        $valido = true;
        $arrResponsabilidadesFinanceirasNew = $entity->getResponsabilidadesfinanceiras()->toArray();
        
        foreach ($arrResponsabilidadesFinanceirasNew as $responsabilidadeFinanceira) {
            $valorTotal = 0;

            foreach ($responsabilidadeFinanceira->getResponsabilidadesfinanceirasvalores()->toArray() as $responsabilidadeValor) {
                $valorTotal += round($responsabilidadeValor->getValorpagar(), 2);
            }

            if ($valorTotal != $responsabilidadeFinanceira->getValorservico()) {
                $valido = false;
                break;
            }
        }

        if (!$valido) {
            throw new LogicException('Total do valor rateado diferente do valor de serviço');
        }
    }
}