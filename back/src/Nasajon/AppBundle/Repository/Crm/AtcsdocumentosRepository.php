<?php


namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Crm\AtcsdocumentosRepository as ParentRepository;

/**
 * AtcsdocumentosRepository
 *
 */
class AtcsdocumentosRepository extends ParentRepository
{

    public function getAtcsDocumentosSemMalotes($id, $id_grupoempresarial, $tenant)
    {
        $sql = "SELECT 
                    N.NEGOCIO AS ID_NEGOCIO, 
                    N.CODIGO AS CODIGO_NEGOCIO, 
                    N.NOME AS NOME_NEGOCIO, 
                    ND.NEGOCIODOCUMENTO AS ID_DOCUMENTO,
                    TD.NOME AS NOME_DOCUMENTO,
                    NTDR.CREATED_AT AS DATA_RECEBIMENTO, 
                    NTDR.COPIASIMPLES AS COPIASIMPLES,
                    NTDR.COPIAAUTENTICADA AS COPIAAUTENTICADA,
                    NTDR.ORIGINAL AS ORIGINAL,
                    ND.STATUS AS SITUACAODOCUMENTO,
                    ( CASE WHEN ND.STATUS = 0 THEN 'Pendente'
                        WHEN ND.STATUS = 1 THEN 'Recebido'
                        WHEN ND.STATUS = 2 THEN 'Pré-Aprovado'
                        WHEN ND.STATUS = 3 THEN 'Enviado para Requisitante'
                        WHEN ND.STATUS = 4 THEN 'Aprovado'
                        WHEN ND.STATUS = 5 THEN 'Recusado'
                    ELSE 'Não suportado' END ) AS SITUACAODOCUMENTO_NOME,
                    ND.URL AS URL_DOCUMENTO	
                FROM CRM.ATCSDOCUMENTOS ND
                JOIN NS.TIPOSDOCUMENTOS TD ON TD.TIPODOCUMENTO = ND.TIPODOCUMENTO AND TD.TENANT = ND.TENANT
                JOIN CRM.ATCS N ON N.NEGOCIO = ND.NEGOCIO AND N.TENANT = ND.TENANT
                JOIN CRM.ATCSTIPOSDOCUMENTOSREQUISITANTES NTDR ON N.NEGOCIO = NTDR.NEGOCIO AND ND.TIPODOCUMENTO = NTDR.TIPODOCUMENTO AND ND.TENANT = NTDR.TENANT
                LEFT JOIN CRM.MALOTESDOCUMENTOS MD ON MD.DOCUMENTO = ND.NEGOCIODOCUMENTO AND MD.TENANT = ND.TENANT
                WHERE ND.TENANT = :TENANT
                  AND ND.ID_GRUPOEMPRESARIAL = :ID_GRUPO
                  AND N.CLIENTE = :CLIENTE
                  AND N.STATUS NOT IN (3, 4) 
                  AND ND.STATUS <> 5 
                  AND MD.MALOTEDOCUMENTO IS NULL
                ORDER BY N.CODIGO;
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(":TENANT", $tenant);
        $stmt->bindValue(":CLIENTE", $id);
        $stmt->bindValue(":ID_GRUPO", $id_grupoempresarial);

        $stmt->execute();

        $listaDocumentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $listaDocumentos;
    }

    /**
     * Sobrescrito para trazer dados do malote na listagem
     *
     */
    public function findAllQueryBuilder($tenant, $id_grupoempresarial, Filter $filter = null)
    {

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.negociodocumento as negociodocumento',
            't0_.status as status',
            't0_.created_at as created_at',
            't0_.updated_at as updated_at',
            't0_.url as url',
            't0_.descricao as descricao',
            't0_.tipomime as tipomime',
        );

        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);

        $queryBuilder->from('crm.atcsdocumentos', 't0_');

        $queryBuilder->leftJoin('t0_', 'crm.vwatcssimplesecomseguradora_v8', 't1_', 't0_.negocio = t1_.negocio and t0_.tenant = t1_.tenant');
        $queryBuilder->addSelect(array(
            't1_.datacriacao as negocio_datacriacao',
            't1_.negocio as negocio_negocio',
            't1_.codigo as negocio_codigo',
            't1_.nome as negocio_nome',
            't1_.camposcustomizados as negocio_camposcustomizados',
            't1_.status as negocio_status',
            't1_.possuiseguradora as negocio_possuiseguradora',
            't1_.dataedicao as negocio_dataedicao',
        ));

        $queryBuilder->leftJoin('t0_', 'ns.tiposdocumentos', 't2_', 't0_.tipodocumento = t2_.tipodocumento and t0_.tenant = t2_.tenant');
        $queryBuilder->addSelect(array(
            't2_.tipodocumento as tipodocumento_tipodocumento',
            't2_.nome as tipodocumento_nome',
            't2_.emissaonoprocesso as tipodocumento_emissaonoprocesso',
        ));

        $queryBuilder->leftJoin('t0_', 'crm.malotesdocumentos', 't3_', 't0_.negociodocumento = t3_.documento and t0_.tenant = t3_.tenant');
        $queryBuilder->leftJoin('t3_', 'crm.malotes', 't4_', 't3_.malote = t4_.malote and t3_.tenant = t4_.tenant');

        $queryBuilder->addSelect(array(
            't4_.status as maloteStatus',
            't4_.codigo as codigo',
        ));

        $queryBuilder->leftJoin('t0_', 'crm.atcstiposdocumentosrequisitantes', 't5_', 't5_.negocio = t0_.negocio AND t5_.tenant = t0_.tenant AND t5_.tipodocumento = t0_.tipodocumento');
                        
        $queryBuilder->addSelect(array(
            't5_.copiasimples as copiasimples', 
	        't5_.copiaautenticada as copiaautenticada', 
            't5_.original as original',
            't5_.requisitantecliente as requisitantecliente',
            't5_.requisitantefornecedor as requisitantefornecedor',
            't5_.requisitantenegocio as requisitantenegocio',
            't5_.requisitanteapolice as requisitanteapolice'
        ));




        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, $filter);

        return [$queryBuilder, $binds];
    }

    /**
     * Sobrescrito para trazer dados do malote na listagem
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
    {

        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $id_grupoempresarial, $filter);

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());

        $stmt->execute($binds);

        $joins = ['negocio', 'tipodocumento', 'malotedocumento', 'malote'];

        $result = array_map(function ($row) use ($joins) {
            if (count($joins) > 0) {
                foreach ($row as $key => $value) {
                    $parts = explode("_", $key);
                    $prefix = array_shift($parts);

                    if (in_array($prefix, $joins)) {
                        $row[$prefix][join("_", $parts)] = $value;
                        unset($row[$key]);
                    }
                }
            }
            return $row;
        }, $stmt->fetchAll());

        return $result;
    }
}
