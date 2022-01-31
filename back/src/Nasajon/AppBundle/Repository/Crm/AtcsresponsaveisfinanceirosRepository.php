<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Crm\AtcsresponsaveisfinanceirosRepository as ParentRepository;

/**
 * Método findAllQueryBuilder sobrescrito para retirar uso da view de cliente, visando otimização.
 * Método findAll sobrescrito para mudar referencia do guid do cliente caso seja usado filtro cliente.
 * Método findQuery (private) substituído por overridenfindQuery para otimizar query, deixando de dar joins com views desnecessariamente.
 * Métodos find e findBy sobrescritos para usar novo método acima. Foi necessário pois findQuery original é private, por tanto, não possível de ser sobrescrito.
 */
class AtcsresponsaveisfinanceirosRepository extends ParentRepository
{

    public function findAllQueryBuilder($tenant, $atc, $id_grupoempresarial, Filter $filter = null)
    {

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.negocioresponsavelfinanceiro as negocioresponsavelfinanceiro',
            't0_.principal as principal',
        );

        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.atcsresponsaveisfinanceiros', 't0_');
        // $queryBuilder->leftJoin('t0_', 'ns.vw_clientes_v2', 't1_', 't0_.responsavelfinanceiro = t1_.cliente');
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't1_', 't0_.responsavelfinanceiro = t1_.id and t0_.tenant = t1_.tenant');
        $queryBuilder->addSelect(array(
            // 't1_.cliente as responsavelfinanceiro_cliente',
            't1_.id as responsavelfinanceiro_cliente',
            't1_.nome as responsavelfinanceiro_razaosocial',
            't1_.cnpj as responsavelfinanceiro_cnpj',
            't1_.nomefantasia as responsavelfinanceiro_nomefantasia',
            't1_.diasparavencimento as responsavelfinanceiro_diasparavencimento',
            // 't1_.tipo as responsavelfinanceiro_tipo',
            'CASE 
                WHEN COALESCE(t1_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT
            END	AS tipo',
            // 't1_.formapagamento as responsavelfinanceiro_formapagamentoguid',
            't1_.id_formapagamento as responsavelfinanceiro_formapagamentoguid',
        ));


        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $atc, $id_grupoempresarial, $filter);


        return [$queryBuilder, $binds];
    }

    /**
     * @return array
     */
    public function findAll($tenant, $atc, $id_grupoempresarial, Filter $filter = null){
        $this->validateOffset($filter);
        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $atc, $id_grupoempresarial, $filter);
        $sql = $queryBuilder->getSQL();
        //localiza referencia da view (cliente) e muda para referencia da tabela ns.pessoas (id)
        $sql = str_replace("t1_.cliente", "t1_.id", $sql);
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($binds);
        $joins = ['responsavelfinanceiro', ];
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
            return $row;
        },$stmt->fetchAll());
        
        return $result;
                
    }



    private function overridenfindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

            t0_.negocioresponsavelfinanceiro as \"negocioresponsavelfinanceiro\" ,
            t0_.negocio as \"negocio\" ,
            t0_.created_at as \"created_at\" ,
            t0_.created_by as \"created_by\" ,
            t0_.tenant as \"tenant\" ,
            t0_.principal as \"principal\" ,
            -- t1_.cliente as \"t1_cliente\" ,
            t1_.id as \"t1_cliente\" ,
            t1_.nome as \"t1_razaosocial\" ,
            t1_.cnpj as \"t1_cnpj\" ,
            t1_.nomefantasia as \"t1_nomefantasia\" ,
            t1_.diasparavencimento as \"t1_diasparavencimento\" ,
            -- t1_.tipo as \"t1_tipo\" ,
            'CASE 
                WHEN COALESCE(t1_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT
            END	AS tipo',
            -- t1_.formapagamento as \"t1_formapagamento\" ,
            t1_.id_formapagamento as \"t1_formapagamento\" ,
            -- t1_.formapagamento as \"t1_formapagamentoguid\" ,
            t1_.id_formapagamento as \"t1_formapagamentoguid\" ,
            t1_.anotacao as \"t1_anotacao\" 

            FROM crm.atcsresponsaveisfinanceiros t0_
            -- LEFT JOIN ns.vw_clientes_v2 t1_ ON t0_.responsavelfinanceiro = t1_.cliente
            LEFT JOIN ns.pessoas t1_ ON t0_.responsavelfinanceiro = t1_.id and t0_.tenant = t1_.tenant
        {$where}" ;
        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $atc
     * @param mixed $id_grupoempresarial
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant, $atc, $id_grupoempresarial){
        $where = $this->buildWhere();
        $data = $this->overridenfindQuery($where, [
            'id' => $id,
            'negocio' => $atc,
            'tenant' => $tenant,
            'id_grupoempresarial' => $id_grupoempresarial
        ])->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }
    
    public function findBy(array $whereFields){
        $where = $this->buildWhere(array_keys($whereFields));
        $query = $this->overridenfindQuery($where, $whereFields);
        if( $query->rowCount() > 1 ){
            throw new \Doctrine\ORM\NonUniqueResultException();
        }
        $data = $query->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }
}
