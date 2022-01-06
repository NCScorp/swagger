<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Ns\Clientes;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Ns\Clientes\ClassificadoresRepository as ParentRepository;

/**
 * ClassificadoresRepository
 *
 */
class ClassificadoresRepository extends ParentRepository {

    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $cliente,  Filter $filter = null) {
        $binds = [];
        $where = [];

        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

        $where[] = $queryBuilder->expr()->eq("t0_.cliente", "?");
        $binds[] = $cliente;

        $where[] = $queryBuilder->expr()->isNotNull("t0_.valor");

        list($filters, $filtersBinds) = $this->proccessFilter($filter);
        $binds = array_merge($binds, $filtersBinds);

        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }
        
        if (!empty($filters)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }

        return $binds;
    }
}
