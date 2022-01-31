<?php

namespace Nasajon\AppBundle\Repository\Financas;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Financas\ProjetosRepository as ParentRepository;


/*
 * Método findQuery sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 */
class ProjetosRepository extends ParentRepository
{

    private function overridenfindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

                                t0_.projeto as \"projeto\" ,
                                t0_.nome as \"nome\" ,
                                t0_.datainicio as \"datainicio\" ,
                                t0_.datafim as \"datafim\" ,
                                t0_.situacao as \"situacao\" ,
                                t0_.tenant as \"tenant\" ,
                                t0_.codigo as \"codigo\" ,
                                t0_.tempoadquirido as \"tempoadquirido\" ,
                                t0_.responsavel_conta_nasajon as \"responsavel_conta_nasajon\" ,
                                t0_.valor as \"valor\" ,
                                t0_.origem as \"origem\" ,
                                t0_.tipodocumentovinculado as \"tipodocumentovinculado\" ,
                                t0_.sincronizaescopo as \"sincronizaescopo\" ,
                                t0_.sincronizasolicitacao as \"sincronizasolicitacao\" ,
                                t0_.tempoprevisto as \"tempoprevisto\" ,
                                t2_.estabelecimento as \"t2_estabelecimento\" ,
                                t2_.nomefantasia as \"t2_nomefantasia\" ,
                                t3_.grupoempresarial as \"t2_id_grupoempresarial\" ,
                                -- t1_.cliente as \"t1_cliente\" ,
                                t1_.id as \"t1_cliente\" ,
                                t1_.nome as \"t1_razaosocial\" ,
                                t1_.nomefantasia as \"t1_nomefantasia\" ,
                                t1_.cnpj as \"t1_cnpj\" ,
                                t1_.diasparavencimento as \"t1_diasparavencimento\" ,
                                -- t1_.tipo as \"t1_tipo\" ,
                                CASE 
                                    WHEN COALESCE(t1_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT
                                END	AS t1_tipo,
                                -- t1_.formapagamento as \"t1_formapagamento\" ,
                                t1_.id_formapagamento as \"t1_formapagamento\" ,
                                -- t1_.formapagamento as \"t1_formapagamentoguid\" ,
                                t1_.id_formapagamento as \"t1_formapagamentoguid\" ,
                                t1_.anotacao as \"t1_anotacao\" 
            
                --FROM gp.vw_projetos_v5 t0_
                FROM financas.projetos t0_

                                    -- LEFT JOIN servicos.vwestabelecimentos t2_ ON t0_.estabelecimento_id = t2_.estabelecimento
                                    LEFT JOIN ns.estabelecimentos t2_ ON t0_.estabelecimento_id = t2_.estabelecimento and t0_.tenant = t2_.tenant
                                    LEFT JOIN ns.empresas t3_ ON t2_.empresa = t3_.empresa and t2_.tenant = t3_.tenant

                            -- LEFT JOIN ns.vw_clientes_v2 t1_ ON t0_.cliente_id = t1_.cliente
                            LEFT JOIN ns.pessoas t1_ ON t0_.cliente_id = t1_.id and t0_.tenant = t1_.tenant

                     
        {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant){
    
        $where = $this->buildWhere();
        $data = $this->overridenfindQuery($where, [
                'id' => $id,
                'tenant' => $tenant ])->fetch();

        $data = $this->adjustQueryData($data);
        
        return $data;
    }
}
