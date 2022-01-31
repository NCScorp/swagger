<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Crm\AtcsdadosseguradorasRepository as ParentService;

/**
 * AtcsdadosseguradorasRepository
 *
 */
class AtcsdadosseguradorasRepository extends ParentService
{
    public function apoliceEmUsoNoAtc($atc, $apolice, $tenant)
    {
        $sql_1 = "
        SELECT propostaitem FROM crm.propostasitens 
        WHERE negocio = :negocio 
        AND id_apolice = :apolice
        AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("negocio", $atc);
        $stmt_1->bindValue("apolice", $apolice->getTemplateproposta());
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['propostaitem']) ){
            return true;
        }
        return false;
    }


    public function findAllQueryBuilder($atc,$tenant, $id_grupoempresarial, Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
                                't0_.negociodadosseguradora as negociodadosseguradora',
                                't0_.titularnome as titularnome',
                                't0_.nomefuncionarioseguradora as nomefuncionarioseguradora',
                                't0_.negocio as negocio',
                                't0_.sinistro as sinistro',
                                't0_.apoliceconfirmada as apoliceconfirmada',
                                't0_.apolicetipo as apolicetipo',
                                't0_.titulartipodocumento as titulartipodocumento',
                                't0_.titularcnpj as titularcnpj',
                                't0_.titularcpf as titularcpf',
                                't0_.titularcontatos as titularcontatos',
                                't0_.id_grupoempresarial as id_grupoempresarial',
                                't0_.titularmatricula as titularmatricula',
                                't0_.valorautorizado as valorautorizado' ,
                                't0_.valorapolice as valorapolice',
                                't0_.beneficiario as beneficiario'
            );
         
        if( $filter && empty($filter->getOffset()) ){
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
                $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.atcsdadosseguradoras', 't0_');
            $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't1_', 't0_.seguradora = t1_.id and t0_.tenant = t1_.tenant'); 
            $queryBuilder->addSelect(array(
                                    't1_.id as seguradora_cliente',
                                    't1_.nome as seguradora_razaosocial',
                                    't1_.nomefantasia as seguradora_nomefantasia',
                                    't1_.cnpj as seguradora_cnpj',
                                    't1_.diasparavencimento as seguradora_diasparavencimento',
                                    'case when coalesce(t1_.seguradora, false) = true THEN 1::bigint else 0::bigint end as seguradora_tipo',
                                    't1_.id_formapagamento as seguradora_formapagamentoguid',
                                    't1_.anotacao as seguradora_anotacao',
                            ));    
            $queryBuilder->leftJoin('t0_', 'crm.templatespropostasgrupos', 't2_', 't0_.produtoseguradora = t2_.templatepropostagrupo and t0_.tenant = t2_.tenant'); 
            $queryBuilder->addSelect(array(
                                    't2_.templatepropostagrupo as produtoseguradora_templatepropostagrupo',
                                    't2_.nome as produtoseguradora_nome',
                                    't2_.cliente as produtoseguradora_cliente',
                            ));    
            $queryBuilder->leftJoin('t0_', 'crm.templatespropostas', 't3_', 't0_.apolice = t3_.templateproposta and t0_.tenant = t3_.tenant'); 
            $queryBuilder->addSelect(array(
                                    't3_.templateproposta as apolice_templateproposta',
                                    't3_.nome as apolice_nome',
                                    't3_.templatepropostagrupo as apolice_templatepropostagrupo',
                                    't3_.valorapolice as apolice_valorapolice',
                    
                            ));    
            $queryBuilder->leftJoin('t0_', 'crm.vinculos', 't4_', 't0_.titularvinculo = t4_.vinculo and t0_.tenant = t4_.tenant'); 
            $queryBuilder->addSelect(array(
                                    't4_.vinculo as titularvinculo_vinculo',
                                    't4_.nome as titularvinculo_nome',
                            ));    
                                        
                
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$atc,$tenant, $id_grupoempresarial, $filter);
        

        return [$queryBuilder, $binds];
    }

    private function overridenfindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

                    t0_.negociodadosseguradora as \"negociodadosseguradora\" ,
                    t0_.negocio as \"negocio\" ,
                    t0_.sinistro as \"sinistro\" ,
                    t0_.apoliceconfirmada as \"apoliceconfirmada\" ,
                    t0_.apolicetipo as \"apolicetipo\" ,
                    t0_.titularcontatos as \"titularcontatos\" ,
                    t0_.titularcnpj as \"titularcnpj\" ,
                    t0_.titularcpf as \"titularcpf\" ,
                    t0_.titulartipodocumento as \"titulartipodocumento\" ,
                    t0_.titularnome as \"titularnome\" ,
                    t0_.created_at as \"created_at\" ,
                    t0_.created_by as \"created_by\" ,
                    t0_.updated_at as \"updated_at\" ,
                    t0_.updated_by as \"updated_by\" ,
                    t0_.tenant as \"tenant\" ,
                    t0_.id_grupoempresarial as \"id_grupoempresarial\" ,
                    t0_.nomefuncionarioseguradora as \"nomefuncionarioseguradora\" ,
                    t0_.titularmatricula as \"titularmatricula\" ,
                    t0_.valorautorizado as \"valorautorizado\" ,
                    t0_.valorapolice as \"valorapolice\" ,
                    t0_.beneficiario as \"beneficiario\" ,
                    t2_.templatepropostagrupo as \"t2_templatepropostagrupo\" ,
                    t2_.nome as \"t2_nome\" ,
                    t2_.cliente as \"t2_cliente\" ,
                    t3_.templateproposta as \"t3_templateproposta\" ,
                    t3_.nome as \"t3_nome\" ,
                    t3_.templatepropostagrupo as \"t3_templatepropostagrupo\" ,
                    t3_.valorapolice as \"t3_valorapolice\" ,
                    t4_.vinculo as \"t4_vinculo\" ,
                    t4_.nome as \"t4_nome\" ,
                    t1_.id as \"t1_cliente\" ,
                    t1_.nome as \"t1_razaosocial\" ,
                    t1_.nomefantasia as \"t1_nomefantasia\" ,
                    t1_.cnpj as \"t1_cnpj\" ,
                    t1_.diasparavencimento as \"t1_diasparavencimento\" ,
                    CASE WHEN COALESCE(t1_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT END as \"t1_tipo\" ,
                    t1_.id_formapagamento as \"t1_formapagamento\" ,
                    t1_.id_formapagamento as \"t1_formapagamentoguid\" ,
                    t1_.anotacao as \"t1_anotacao\" 

                FROM crm.atcsdadosseguradoras t0_
                LEFT JOIN crm.templatespropostasgrupos t2_ ON t0_.produtoseguradora = t2_.templatepropostagrupo  and t0_.tenant = t2_.tenant
                LEFT JOIN crm.templatespropostas t3_ ON t0_.apolice = t3_.templateproposta  and t0_.tenant = t3_.tenant
                LEFT JOIN crm.vinculos t4_ ON t0_.titularvinculo = t4_.vinculo  and t0_.tenant = t4_.tenant
                --LEFT JOIN ns.vw_clientes_v2 t1_ ON t0_.seguradora = t1_.cliente
                LEFT JOIN ns.pessoas t1_ ON t0_.seguradora = t1_.id and t0_.tenant = t1_.tenant
                    
        {$where}" ;

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }


    /**
     * @param string $id
     * @param mixed $atc
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
     public function find($id , $atc, $tenant, $id_grupoempresarial){
    
        $where = $this->buildWhere();
        $data = $this->overridenfindQuery($where, [
            'id' => $id                    ,
            'negocio' => $atc
                            ,
            'tenant' => $tenant
                            ,
            'id_grupoempresarial' => $id_grupoempresarial
                        ])->fetch();
        $data = $this->adjustQueryData($data);
        
        return $data;
    }
}
