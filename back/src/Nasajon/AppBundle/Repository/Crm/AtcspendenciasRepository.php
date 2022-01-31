<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Crm\AtcspendenciasRepository as ParentRepository;

/**
 * AtcspendenciasRepository
 *
 */
class AtcspendenciasRepository extends ParentRepository
{
    //Sobrescrito para colocar ordenação personalizada na consulta da listagem.
    public function findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, Filter $filter = null)
    {                
        $queryBuilder->addOrderBy("t2_.ordem", "ASC");      
        $queryBuilder->addSelect('count(*) OVER() AS full_count');          
        $bind = parent::findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, $filter);

        if ($filter !== null && !empty($filter->getOffset()['paginate'])) {
            $queryBuilder->setFirstResult((int) $filter->getOffset()['paginate']);
        }

        return $bind;
    }


    public function processOffset(Filter $filter = null) {
        $where = [];
        $binds = [];
        return [$where, $binds];
    }


    /**
     * O Findall foi sobrescrito para retirar a função validateofsset, como foi alterado a forma de criar paginação
     * algumas funções também foram sobrescritas como a processoffset retirando a antiga forma de fazer a paginação
     * @param $tenant
     * @param $id_grupoempresarial
     * @param Filter|null $filter
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
    {

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant,$id_grupoempresarial, $filter);

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());

        $stmt->execute($binds);

        $joins = ['negociopendencialista', 'prioridade', 'negocio', ];

        $result = array_map(function($row) use($joins){
            if(count($joins) > 0){
                foreach ($row as $key => $value) {
                    $parts = explode("_", $key);
                    $prefix = array_shift($parts);

                    if (in_array($prefix , $joins)) {
                        $row[$prefix][join("_",$parts)] = $value;
                        unset($row[$key]);
                    }
                }
            }
            $row['created_by'] = json_decode($row['created_by'], true);
            $row['fechadapor'] = json_decode($row['fechadapor'], true);
            return $row;
        },$stmt->fetchAll());

        return $result;
    }

}
