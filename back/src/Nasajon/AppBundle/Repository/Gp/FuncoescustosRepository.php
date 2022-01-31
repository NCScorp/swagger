<?php

namespace Nasajon\AppBundle\Repository\Gp;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Gp\FuncoescustosRepository as ParentRepository;

/**
 * Método findQuery (private) substituído por overridenfindQuery para otimizar query, deixando de dar joins com views desnecessariamente.
 * Métodos find e findBy sobrescritos para usar novo método acima. Foi necessário pois findQuery original é private, por tanto, não possível de ser sobrescrito.
 */
class FuncoescustosRepository extends ParentRepository
{

    private function overridenFindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

                                t0_.funcaocusto as \"funcaocusto\" ,
                                t0_.funcao as \"funcao\" ,
                                t0_.created_at as \"created_at\" ,
                                t0_.created_by as \"created_by\" ,
                                t0_.updated_at as \"updated_at\" ,
                                t0_.updated_by as \"updated_by\" ,
                                t0_.lastupdate as \"lastupdate\" ,
                                t0_.tenant as \"tenant\" ,
                                t1_.custo as \"t1_custo\" ,
                                t1_.codigo as \"t1_codigo\" ,
                                t1_.descricao as \"t1_descricao\" ,
                                t1_.valor as \"t1_valor\" ,
                                t1_.tipocalculo as \"t1_tipocalculo\" ,
                                t1_.tipovalor as \"t1_tipovalor\" ,
                                t1_.percentual as \"t1_percentual\" ,
                                -- t1_.valorcomunidade as \"t1_valorcomunidade\" 
                                (t1_.valor || ' / '::text) || u.codigo::text AS valorcomunidade
            
                FROM gp.funcoescustos t0_

                                    -- LEFT JOIN gp.vw_custos_v3 t1_ ON t0_.custo = t1_.custo
                                    LEFT JOIN gp.custos t1_ ON t0_.custo = t1_.custo and t0_.tenant = t1_.tenant
                                    LEFT JOIN estoque.unidades u ON t1_.unidadepadrao = u.unidade and t1_.tenant = u.tenant

                     
        {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $funcao
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $funcao)
    {

        $where = $this->buildWhere();
        $data = $this->overridenFindQuery($where, [
            'id' => $id,
            'funcao' => $funcao,
            'tenant' => $tenant
        ])->fetch();

        $data = $this->adjustQueryData($data);

        return $data;
    }

    public function findBy(array $whereFields)
    {

        $where = $this->buildWhere(array_keys($whereFields));
        $query = $this->overridenFindQuery($where, $whereFields);

        if ($query->rowCount() > 1) {
            throw new \Doctrine\ORM\NonUniqueResultException();
        }

        $data = $query->fetch();
        $data = $this->adjustQueryData($data);

        return $data;
    }

}
