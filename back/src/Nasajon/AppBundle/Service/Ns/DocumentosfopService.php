<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\DocumentosfopService as ParentService;

/**
* DocumentosfopService
*
*/
class DocumentosfopService extends ParentService
{           
   /**
     * @return array
     */
    public function findAll( Filter $filter = null){
        // Adiciono filtro de sistema(0 - Comuns aos sistemas e Sistema registrado na variável de ambiente)
        if ($filter == null) {
            $filter = new Filter();
        }
        $expressions = $filter->getFilterExpression();
        // Filtro documentos referentes a todos os sistemas e o sistema atual
        $expressions[] = new FilterExpression('sistema', 'eq', getenv('diretorio_sistema_id'));
        // Filtro documentos referentes a todos os tenants. O filtro referente ao tenant atual já é montado ao passar pelo controller
        $expressions[] = new FilterExpression('tenant', 'eq', '0');

        // Seto expressões de filtros
        $filter->setFilterExpression($expressions);

        return parent::findAll($filter);

    }
} 