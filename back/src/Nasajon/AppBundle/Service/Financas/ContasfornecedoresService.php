<?php

namespace Nasajon\AppBundle\Service\Financas;

use Exception;
use Nasajon\MDABundle\Service\Financas\ContasfornecedoresService as ParentService;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
/**
 * Sobrescrito para modal de Dados bancários vir preenchido
 */
class ContasfornecedoresService extends ParentService
{
    public $financasBancosService;
    
    public function __construct(\Nasajon\MDABundle\Repository\Financas\ContasfornecedoresRepository $repository, $financasBancosService){
        $this->repository = $repository;       
        $this->financasBancosService = $financasBancosService;        
       
    }
  /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $fornecedor
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     * Sobreescrito para lookup de dados bancários vir preenchido
     */
    public function find($id , $tenant, $fornecedor){
        $data = parent::find($id , $tenant, $fornecedor);
        $filter = new Filter();
        $filterExpression = $filter->getFilterExpression();
        array_push($filterExpression, new FilterExpression('numero', 'eq', $data['banco']));
        $filter->setFilterExpression($filterExpression);
        $banco = $this->financasBancosService->findAll($tenant, $filter);

        //Se tiver banco, então uso ele para atribuir o seu conteúdo ao campo, se não, atribuo null
        if ($banco){
            $data['id_banco'] = $banco[0];
        }
        else{
            $data['id_banco'] = null;
        }
        
        return $data;
    }

    /**
     * @return array
     */
    public function findAll($tenant,$fornecedor,$excluida = "", Filter $filter = null) {

        $lstDados = parent::findAll($tenant, $fornecedor, $excluida, $filter);

        $listaDadosBancarios = [];
        
        
        foreach ($lstDados as &$item) {
            array_push($listaDadosBancarios, $this->find($item['contafornecedor'], $tenant, $fornecedor));
        }
        
        return $listaDadosBancarios;
            
    }
}