<?php
namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Crm\AtcstiposdocumentosrequisitantesRepository as ParentRepository;

/**
 * Método findAllQueryBuilder sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 */
class AtcstiposdocumentosrequisitantesRepository extends ParentRepository
{

    public function findAllQueryBuilder($tenant, $atc, $id_grupoempresarial, Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.negociotipodocumentorequisitante as negociotipodocumentorequisitante',
            't0_.requisitantenegocio as requisitantenegocio',
            't0_.copiasimples as copiasimples',
            't0_.copiaautenticada as copiaautenticada',
            't0_.original as original',
            't0_.permiteenvioemail as permiteenvioemail',
            't0_.created_by as created_by',
            't0_.created_at as created_at',
            't0_.negocio as negocio',
            't0_.requisitantecontaspagar as requisitantecontaspagar',
            't0_.pedirinformacoesadicionais as pedirinformacoesadicionais',
            't0_.naoexibiremrelatorios as naoexibiremrelatorios',
        );

        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.atcstiposdocumentosrequisitantes', 't0_');
        $queryBuilder->leftJoin('t0_', 'ns.tiposdocumentos', 't1_', 't0_.tipodocumento = t1_.tipodocumento and t0_.tenant = t1_.tenant');
        $queryBuilder->addSelect(array(
            't1_.tipodocumento as tipodocumento_tipodocumento',
            't1_.nome as tipodocumento_nome',
            't1_.emissaonoprocesso as tipodocumento_emissaonoprocesso',
            't1_.prestador as tipodocumento_prestador',
        ));
        // $queryBuilder->leftJoin('t0_', 'ns.vw_clientes_v2', 't2_', 't0_.requisitantecliente = t2_.cliente');
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't2_', 't0_.requisitantecliente = t2_.id and t0_.tenant = t2_.tenant');
        $queryBuilder->addSelect(array(
            // 't2_.cliente as requisitantecliente_cliente',
            't2_.id as requisitantecliente_cliente',
            't2_.nome as requisitantecliente_razaosocial',
            't2_.cnpj as requisitantecliente_cnpj',
            't2_.nomefantasia as requisitantecliente_nomefantasia',
            't2_.diasparavencimento as requisitantecliente_diasparavencimento',
            // 't2_.tipo as requisitantecliente_tipo',
            'CASE 
                WHEN COALESCE(t2_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT
            END	AS requisitantecliente_tipo',
            // 't2_.formapagamento as requisitantecliente_formapagamentoguid',
            't2_.id_formapagamento as requisitantecliente_formapagamentoguid',
            't2_.anotacao as requisitantecliente_anotacao',
        ));
        //$queryBuilder->leftJoin('t0_', 'ns.vw_fornecedores', 't3_', 't0_.requisitantefornecedor = t3_.fornecedor');
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't3_', 't0_.requisitantefornecedor = t3_.id and t0_.tenant = t3_.tenant');
        $queryBuilder->addSelect(array(
            //'t3_.fornecedor as requisitantefornecedor_fornecedor',
            't3_.id as requisitantefornecedor_fornecedor',
            't3_.nomefantasia as requisitantefornecedor_nomefantasia',
            't3_.nome as requisitantefornecedor_razaosocial',
            't3_.cnpj as requisitantefornecedor_cnpj',
            't3_.pessoa as requisitantefornecedor_codigofornecedores',
            //'t3_.status as requisitantefornecedor_status',
            't3_.esperapagamentoseguradora as requisitantefornecedor_esperapagamentoseguradora',
            't3_.diasparavencimento as requisitantefornecedor_diasparavencimento',
            't3_.anotacao as requisitantefornecedor_anotacao',
        ));

        $queryBuilder->leftJoin('t0_', 'crm.templatespropostas', 't4_', 't0_.requisitanteapolice = t4_.templateproposta and t0_.tenant = t4_.tenant'); 
        $queryBuilder->addSelect(array(
                                't4_.templateproposta as requisitanteapolice_templateproposta',
                                't4_.nome as requisitanteapolice_nome',
                                't4_.templatepropostagrupo as requisitanteapolice_templatepropostagrupo',
                                't4_.valorapolice as requisitanteapolice_valorapolice',
                        ));    
                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $atc, $id_grupoempresarial, $filter);
        return [$queryBuilder, $binds];
    }

    private function overridenfindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

                    t0_.negociotipodocumentorequisitante as \"negociotipodocumentorequisitante\" ,
                    t0_.negocio as \"negocio\" ,
                    t0_.requisitantenegocio as \"requisitantenegocio\" ,
                    t0_.copiasimples as \"copiasimples\" ,
                    t0_.copiaautenticada as \"copiaautenticada\" ,
                    t0_.original as \"original\" ,
                    t0_.permiteenvioemail as \"permiteenvioemail\" ,
                    t0_.status as \"status\" ,
                    t0_.tenant as \"tenant\" ,
                    t0_.created_at as \"created_at\" ,
                    t0_.created_by as \"created_by\" ,
                    t0_.updated_at as \"updated_at\" ,
                    t0_.updated_by as \"updated_by\" ,
                    t0_.id_grupoempresarial as \"id_grupoempresarial\" ,
                    t0_.requisitantecontaspagar as \"requisitantecontaspagar\" ,
                    t0_.pedirinformacoesadicionais as \"pedirinformacoesadicionais\" ,
                    t0_.naoexibiremrelatorios as \"naoexibiremrelatorios\" ,
                    t4_.templateproposta as \"t4_templateproposta\" ,
                    t4_.nome as \"t4_nome\" ,
                    t4_.templatepropostagrupo as \"t4_templatepropostagrupo\" ,
                    t4_.valorapolice as \"t4_valorapolice\" ,
                    t3_.id as \"t3_fornecedor\" ,
                    t3_.nomefantasia as \"t3_nomefantasia\" ,
                    t3_.nome as \"t3_razaosocial\" ,
                    t3_.cnpj as \"t3_cnpj\" ,
                    t3_.pessoa as \"t3_codigofornecedores\" ,
                    --t3_.status as \"t3_status\" ,
                    t3_.esperapagamentoseguradora as \"t3_esperapagamentoseguradora\" ,
                    t3_.diasparavencimento as \"t3_diasparavencimento\" ,
                    t3_.estabelecimentoid as \"t3_estabelecimentoid\" ,
                    t3_.anotacao as \"t3_anotacao\" ,
                    t1_.tipodocumento as \"t1_tipodocumento\" ,
                    t1_.nome as \"t1_nome\" ,
                    t1_.emissaonoprocesso as \"t1_emissaonoprocesso\" ,
                    t1_.prestador as \"t1_prestador\" ,
                    t2_.id as \"t2_cliente\" ,
                    t2_.nome as \"t2_razaosocial\" ,
                    t2_.nomefantasia as \"t2_nomefantasia\" ,
                    t2_.cnpj as \"t2_cnpj\" ,
                    t2_.diasparavencimento as \"t2_diasparavencimento\" ,
                    CASE WHEN COALESCE(t2_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT END AS \"t2_tipo\" ,
                    t2_.id_formapagamento as \"t2_formapagamento\" ,
                    t2_.id_formapagamento as \"t2_formapagamentoguid\" ,
                    t2_.anotacao as \"t2_anotacao\" 
            
                FROM crm.atcstiposdocumentosrequisitantes t0_

                LEFT JOIN crm.templatespropostas t4_ ON t0_.requisitanteapolice = t4_.templateproposta and t0_.tenant = t4_.tenant
                LEFT JOIN ns.pessoas t3_ ON t0_.requisitantefornecedor = t3_.id and t0_.tenant = t3_.tenant
                INNER JOIN ns.tiposdocumentos t1_ ON t0_.tipodocumento = t1_.tipodocumento and t0_.tenant = t1_.tenant
                LEFT JOIN ns.pessoas t2_ ON t0_.requisitantecliente = t2_.id and t0_.tenant = t2_.tenant
                     
        {$where}" ;

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $atc
     * @param string  $id_grupoempresarial
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant, $atc, $id_grupoempresarial){
    
        $where = $this->buildWhere();
        /* Método findQuery (private) substituído por overridenfindQuery para otimizar query, deixando de dar joins com views desnecessariamente.*/
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

    /**
     * @return array
     * Sobrescrito para usar a coluna id na consulta e não a coluna cliente, como o MDA gera
     */
    public function findAll($tenant,$atc, $id_grupoempresarial, Filter $filter = null){
        $this->validateOffset($filter);
        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant,$atc, $id_grupoempresarial, $filter);
        $sql = $queryBuilder->getSQL();
        //localiza referencia da view (cliente) e muda para referencia da tabela ns.pessoas (id)
        $sql = str_replace("t2_.cliente", "t2_.id", $sql);
        $sql = str_replace("t3_.fornecedor", "t3_.id", $sql);
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($binds);
        $joins = ['tipodocumento', 'requisitantecliente', 'requisitantefornecedor', 'requisitanteapolice', ];       
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
}