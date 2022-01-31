<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Repository\Ns;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Ns\CidadesinfofunerariasfornecedoresRepository as ParentRepository;

/**
* CidadesinfofunerariasfornecedoresRepository
*
*/
class CidadesinfofunerariasfornecedoresRepository extends ParentRepository{

    /**
     * Sobrescrito para ajustar duplicidade de dados que vem da view de fornecedores, por não ter join com tenant e grupoempresarial
     */
    public function findAllQueryBuilder($tenant, $id_grupoempresarial,$cidadeinformacaofuneraria, Filter $filter = null){
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.cidadeinfofunerariafornecedor as cidadeinfofunerariafornecedor',
            't0_.id_cidadefuneraria as cidadeinformacaofuneraria',
            't0_.ordem as ordem',
            't0_.updated_at as updated_at',
        );
        
        if( $filter && empty($filter->getOffset()) ){
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }

        $queryBuilder->select($selectArray);
        $queryBuilder->from('ns.cidadesinfofunerariasfornecedores', 't0_');
        $queryBuilder->leftJoin('t0_', 'ns.vw_fornecedores_v2', 't1_', 't0_.id_fornecedor = t1_.fornecedor AND t0_.tenant = t1_.tenant AND t0_.id_grupoempresarial = t1_.id_grupoempresarial'); 
        $queryBuilder->addSelect(array(
            't1_.fornecedor as fornecedor_fornecedor',
            't1_.nomefantasia as fornecedor_nomefantasia',
            't1_.nome as fornecedor_razaosocial',
            't1_.cnpj as fornecedor_cnpj',
            't1_.pessoa as fornecedor_codigofornecedores',
            't1_.status as fornecedor_status',
            't1_.esperapagamentoseguradora as fornecedor_esperapagamentoseguradora',
            't1_.diasparavencimento as fornecedor_diasparavencimento',
            't1_.anotacao as fornecedor_anotacao',
        ));    
                                        
                
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant,$id_grupoempresarial,$cidadeinformacaofuneraria, $filter);
        

        return [$queryBuilder, $binds];
    }
}