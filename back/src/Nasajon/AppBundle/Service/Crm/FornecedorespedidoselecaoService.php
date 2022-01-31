<?php


namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Crm\FornecedorespedidoselecaoService as ParentService;

/**
* FornecedorespedidoselecaoService
*
*/
class FornecedorespedidoselecaoService extends ParentService
{
                   
   /**
     * @return array
     */
    public function findAll($tenant,$id_grupoempresarial, Filter $filter = null){

        $arrFornecedores = parent::findAll($tenant,$id_grupoempresarial, $filter);
        $arrFornecedoresAux = [];
        $arrRetorno = [];
        
        foreach ($arrFornecedores as $fornecedor) {
            // Se ainda não adicionou esse fornecedor, retorno true
            if (!in_array($fornecedor['fornecedor'], $arrFornecedoresAux)) {
                $arrFornecedoresAux[] = $fornecedor['fornecedor'];
                $arrRetorno[] = $fornecedor;
            }
            
        }
               
        return $arrRetorno;
    }   
}