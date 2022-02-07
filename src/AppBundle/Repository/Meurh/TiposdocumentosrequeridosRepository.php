<?php

namespace AppBundle\Repository\Meurh;

use Nasajon\MDABundle\Request\Filter;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\Meurh\TiposdocumentosrequeridosRepository as ParentRepository;

class TiposdocumentosrequeridosRepository extends ParentRepository
{   
    /**
     * @return array
     */
    public function findAllConfiguracoes(int $tenant, string $estabelecimento)
    {
        $sql = "SELECT
                    *
                FROM
                    meurh.tiposdocumentosrequeridos
                WHERE
                    tenant = :tenant AND estabelecimento = :estabelecimento";

        return $this->getConnection()->executeQuery($sql, [
            'estabelecimento' => $estabelecimento,
            'tenant' => $tenant
        ])->fetchAll();
    }

    /**
     * @return array
     */
    public function findAllQueryBuilderBody($queryBuilder, $tenant, $tiposolicitacao, Filter $filter = null)
    {
        $binds = [];
        $where = [];
        
        if ($filter && !empty($filter->getOrder())) {
            foreach ($filter->getOrder() as $column => $direction) {
                $queryBuilder->addOrderBy("t0_.{$this->getOrders()[$column]}", strtoupper($direction));
            }
        }

        $queryBuilder->addOrderBy("t0_.obrigatorio", "DESC");
        $queryBuilder->addOrderBy("t1_.descricao", "ASC");
        $queryBuilder->setMaxResults(20);

        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;       
            
        $where[] = $queryBuilder->expr()->eq("t0_.tiposolicitacao", "?");
        $binds[] = $tiposolicitacao;
        
        list($offsets, $offsetsBinds) = $this->processOffset($filter);

        $where = array_merge($where, $offsets);
        $binds = array_merge($binds, $offsetsBinds);

        list($filters, $filtersBinds) = $this->processFilter($filter);
        $binds = array_merge($binds, $filtersBinds);
        
        list($filtersExpression, $filtersBinds) = $this->processFilterExpression($filter);
        $binds = array_merge($binds, $filtersBinds);

        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }

        if (!empty($filters)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }

        if (!empty($filtersExpression)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_AND, $filtersExpression));
        }

        return $binds;
    }

    /**
     * @return array
     */
    public function findAllQueryBuilder($tenant, $tiposolicitacao, Filter $filter = null)
    {    
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        $selectArray = [
            't0_.tipodocumentorequerido as tipodocumentorequerido',
            't0_.tiposolicitacao as tiposolicitacao',
            't0_.obrigatorio as obrigatorio',
            't0_.tenant as tenant'
        ];
         
        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }

        $queryBuilder->select($selectArray);
        $queryBuilder->from('meurh.tiposdocumentosrequeridos', 't0_');
        
        $queryBuilder->leftJoin('t0_', 'persona.tiposdocumentoscolaboradores', 't1_', 't0_.tipodocumentocolaborador = t1_.tipodocumentocolaborador   and t0_.tenant = t1_.tenant'); 
        $queryBuilder->addSelect([
            't1_.tipodocumentocolaborador as tipodocumentocolaborador_tipodocumentocolaborador',
            't1_.descricao as tipodocumentocolaborador_descricao',
            't1_.tenant as tipodocumentocolaborador_tenant'
        ]);    
                                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $tiposolicitacao, $filter);

        return [$queryBuilder, $binds];
    }

    /**
     * @return array
     */
    public function findAll($tenant,$tiposolicitacao, Filter $filter = null){

        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant,$tiposolicitacao, $filter);

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $stmt->execute($binds);
        
        $joins = ['tipodocumentocolaborador', ];       
        
        $result = $this->ajustaSubEntidades($stmt->fetchAll(), $joins);
        
        return $result;
    }

    /**
     * @return array
     */
    private function ajustaSubEntidades(array $dados, array $joins)
    {
        return  array_map(
            function($row) use ($joins) {
                if (count($joins) > 0) {
                    foreach ($row as $key => $value) {
                        $parts = explode("_", $key);                    
                        $prefix = array_shift($parts);

                        if (in_array($prefix , $joins)) {
                            $row[$prefix][join("_",$parts)] = $value;
                            unset($row[$key]);
                        }
                    }
                }

                return $row;
            },
            $dados
        );
    }
}