<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace AppBundle\Repository\Persona;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Persona\TarifasconcessionariasvtsRepository as ParentRepository;

class TarifasconcessionariasvtsRepository extends ParentRepository
{
    protected $filterFieldsOverwritten = [];

    public function __construct(\Doctrine\DBAL\Connection $connection){
        parent::__construct($connection);

        $this->setFilterFieldsOverwritten([
            'tarifaconcessionariavt' => 't0_.tarifaconcessionariavt',
        ]);
    }


    public function setFilterFieldsOverwritten($filterFields) {
        $this->filterFieldsOverwritten = $filterFields;
        return $this;
    }

    public function processFilterExpression($filter) {
        $filters = [];
        $binds = [];

        if (!is_null($filter) && !empty($filter->getFilterExpression())) {
            $queryBuilder = $this->getConnection()->createQueryBuilder();
            foreach ($filter->getFilterExpression() as $filterExpression) {
                if( $filterExpression->getCondition() == 'isNull' || $filterExpression->getCondition() == 'isNotNull'){
                    $filters[$filterExpression->getField()][$filterExpression->getCondition()][] = $queryBuilder->expr()->{$filterExpression->getCondition()}($this->filterFieldsOverwritten[$filterExpression->getField()]);
                } else {
                    $filters[$filterExpression->getField()][$filterExpression->getCondition()][] = $queryBuilder->expr()->{$filterExpression->getCondition()}($this->filterFieldsOverwritten[$filterExpression->getField()], "?");

                    $binds[] = $filterExpression->getValue();
                }
            }

            $filters = array_map(function($filtro) use($queryBuilder) {
                $filter = array_reduce($filtro, function($and, $expressions) use($queryBuilder) {
                    $ors = array_reduce($expressions, function($or, $expression) use($queryBuilder) {
                        return $queryBuilder->expr()->andX($or, $expression);
                    });
                    return $queryBuilder->expr()->andX($and, $ors);
                });
                return $filter;
            }, $filters);
        }
        return [$filters, $binds];
    }
}