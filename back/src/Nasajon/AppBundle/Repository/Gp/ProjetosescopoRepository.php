<?php

namespace Nasajon\AppBundle\Repository\Gp;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;

class ProjetosescopoRepository extends AbstractRepository
{

    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        parent::__construct($connection);
        $this->setOrders([]);
        $this->setFilters([]);
        $this->setLinks([
            [
                'field' => 'projeto',
                'entity' => 'Nasajon\MDABundle\Entity\Financas\Projetos',
                'alias' => 't1_',
                'identifier' => 'projeto',
                'type' => 2
            ],
            [
                'field' => 'pai',
                'entity' => 'Nasajon\MDABundle\Entity\Gp\Projetosescopo',
                'alias' => 't2_',
                'identifier' => 'projetoescopo',
                'type' => 2
            ],

        ]);
        $this->setFields([]);
    }

    private function findQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

            t0_.projetoescopo as \"projetoescopo\" ,
            t0_.numero as \"numero\" ,
            t0_.execucoes as \"execucoes\" ,
            t0_.datainicio as \"datainicio\" ,
            t0_.datafim as \"datafim\" ,
            t0_.previsaoinicio as \"previsaoinicio\" ,
            t0_.previsaofim as \"previsaofim\" ,
            t0_.atividade_codigo as \"atividade_codigo\" ,
            t0_.atividade_descricao as \"atividade_descricao\" ,
            t0_.atividade_tipo as \"atividade_tipo\" ,
            t0_.atividade_setor as \"atividade_setor\" ,
            t0_.atividade_funcao as \"atividade_funcao\" ,
            t0_.atividade_recurso as \"atividade_recurso\" ,
            t0_.tenant as \"tenant\" ,
            t0_.created_at as \"created_at\" ,
            t0_.created_by as \"created_by\" ,
            t0_.updated_at as \"updated_at\" ,
            t0_.updated_by as \"updated_by\" ,
            --t0_.grupoinventario as \"grupoinventario\" ,
            0::integer as \"grupoinventario\" ,
            t0_.servicoid as \"servicoid\" ,
            --t0_.custoproduto as \"custoproduto\" ,
            0::numeric as \"custoproduto\" ,
            --t0_.custoindireto as \"custoindireto\" ,
            0::numeric as \"custoindireto\" ,
            --t0_.jnss as \"jnss\" ,
            0::numeric as \"jnss\" ,
            t0_.situacao as \"situacao\" ,
            t0_.roteiro as \"roteiro\" ,
            t0_.descricaoroteiro as \"descricaoroteiro\" ,
            t0_.execucoes as \"execucoesoriginais\" ,
            t0_.projetoescopo as \"link\" ,
            --t0_.recursosstr as \"recursosstr\" ,
            NULL::text as \"recursosstr\" ,
            --t0_.situacaostr as \"situacaostr\" ,
            NULL::text as \"situacaostr\" ,
            t0_.custo_execucao as \"custo_execucao\" ,
            --t0_.custo_execucao_horas as \"custo_execucao_horas\" ,
            0::integer as as \"custo_execucao_horas\" ,
            --t0_.custo_execucao_minutos as \"custo_execucao_minutos\" ,
            0::integer as \"custo_execucao_minutos\" ,
            t0_.associarroteiro as \"associarroteiro\" ,
            --t0_.dependenciasstr as \"dependenciasstr\" ,
            NULL::TEXT as  \"dependenciasstr\" ,
            t0_.ordem as \"ordem\" ,
            t0_.tipo as \"tipo\" ,
            t0_.descricao as \"descricao\" ,
            -- t0_.istarefa as \"istarefa\" ,
            t0_.tipo = 6 AND t0_.tarefa IS NOT NULL as \"istarefa\" ,
            -- t0_.numerotarefa as \"numerotarefa\" ,
            t.numero as \"numerotarefa\" ,
            -- t0_.situacaotarefa as \"situacaotarefa\" ,
            t.situacao as \"situacaotarefa\" ,
            --t0_.alocadomanualmente as \"alocadomanualmente\" ,
            FALSE::BOOLEAN as \"alocadomanualmente\" ,  
            t0_.tempoadquirido as \"tempoadquirido\" ,
            --t0_.tempoadquirido_horas as \"tempoadquirido_horas\" ,
            t0_.tempoadquirido / 60 as \"tempoadquirido_horas\" ,
            --t0_.tempoadquirido_minutos as \"tempoadquirido_minutos\" ,
            t0_.tempoadquirido % 60 as \"tempoadquirido_minutos\" ,
            --t0_.temporealizado as \"temporealizado\" ,
            0::bigint as \"temporealizado\" ,
            --t0_.temporealizadostr as \"temporealizadostr\" ,
            NULL::VARCHAR as \"temporealizadostr\" ,
            --t0_.saldohoras as \"saldohoras\" ,
            0::bigint as \"saldohoras\" ,
            --t0_.saldohorasstr as \"saldohorasstr\" ,
            NULL::VARCHAR  as \"saldohorasstr\" ,
            t0_.pertencearoteiro as \"pertencearoteiro\" ,
            t0_.execucoesroteiroescopo as \"execucoesroteiroescopo\" ,
            t1_.projeto as \"t1_projeto\" ,
            t1_.nome as \"t1_nome\" ,
            t1_.datainicio as \"t1_datainicio\" ,
            t1_.datafim as \"t1_datafim\" ,
            t1_.situacao as \"t1_situacao\" 
        FROM gp.vw_projetosescopo_v2 t0_
        INNER JOIN financas.projetos t1_ ON t0_.projeto = t1_.projeto and t0_.tenant = t1_.tenant
        LEFT JOIN gp.tarefas t ON t.tarefa = t0_.tarefa and t0_.tenant = t.tenant
        --LEFT JOIN gp.projetosescopo t2_ ON t0_.pai = t2_.projetoescopo and t0_.tenant = t2_.tenant

                     
        {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    /**
     * @param string $id
     * @param mixed $projeto
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $projeto, $tenant)
    {
        $where = $this->buildWhere();
        $data = $this->findQuery($where, [
            'id' => $id,
            'projeto' => $projeto,
            'tenant' => $tenant
        ])->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }

    public function findBy(array $whereFields)
    {
        $where = $this->buildWhere(array_keys($whereFields));
        $query = $this->findQuery($where, $whereFields);
        if ($query->rowCount() > 1) {
            throw new \Doctrine\ORM\NonUniqueResultException();
        }
        $data = $query->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }

    /**
     * Ajusta os dados retornados na consulta.
     * Faz o decode dos jsons e remove os alias dos campos
     */
    public function adjustQueryData($data)
    {
        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }
        $data['created_by'] = json_decode($data['created_by'], true);
        $data['updated_by'] = json_decode($data['updated_by'], true);
        foreach ($this->getLinks() as $link) {
            // 2 é para links de lookup
            if ($link['type'] == 2) {
                $newArr = [];
                foreach ($data as $subKey => $value) {
                    if (substr($subKey, 0, strlen($link['alias'])) === $link['alias']) {
                        $newArr[str_replace($link['alias'], "", $subKey)] = $value;
                        unset($data[$subKey]);
                    }
                }
                if (is_null($newArr[$link['identifier']])) {
                    $data[$link['field']] = null;
                } else {
                    $data[$link['field']] = $newArr;
                }
            }
        }
        return $data;
    }

    public function buildWhere(array $whereFields = [])
    {
        $where = "";
        if (empty($whereFields)) {
            $where = "WHERE t0_.projetoescopo = :id
                                                                                                                                  
                        AND t0_.tenant = :tenant 
                                                                                                                                                    
                        AND t0_.projeto = :projeto 
                    ";
        } else {
            foreach ($whereFields as $field) {
                if (!empty($where)) {
                    $where .= "AND ";
                }
                if (!isset($this->getFilters()[$field])) {
                    throw new \Doctrine\ORM\Mapping\MappingException("Campo não configurado para ser usado: {$field}");
                }
                $where .=  "{$this->getFilters()[$field]} = :{$field} \n";
            }
            $where = "WHERE {$where}";
        }
        return $where;
    }

    public function findAllQueryBuilderBody($queryBuilder, $projeto, $tenant, Filter $filter = null)
    {
        $binds = [];
        $where = [];
        if ($filter && !empty($filter->getOrder())) {
            foreach ($filter->getOrder() as $column => $direction) {
                $queryBuilder->addOrderBy("t0_.{$this->getOrders()[$column]}", strtoupper($direction));
            }
        }
        $where[] = $queryBuilder->expr()->eq("t0_.projeto", "?");
        $binds[] = $projeto;
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;
        list($filters, $filtersBinds) = $this->processFilter($filter);
        $binds = array_merge($binds, $filtersBinds);
        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }
        if (!empty($filters)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }
        return $binds;
    }
    public function findAllQueryBuilder($projeto, $tenant, Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array();
        $queryBuilder->select($selectArray);
        $queryBuilder->from('gp.vw_projetosescopo_v2', 't0_');
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $projeto, $tenant, $filter);
        return [$queryBuilder, $binds];
    }

    /**
     * @return array
     */
    public function findAll($projeto, $tenant, Filter $filter = null)
    {
        $this->validateOffset($filter);
        list($queryBuilder, $binds) = $this->findAllQueryBuilder($projeto, $tenant, $filter);
        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $stmt->execute($binds);
        $joins = [];
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
    /**
     * @return boolean
     */
    public function isUnique($projeto, $tenant, $field, $value, $id)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $selectArray = array('1');
        $queryBuilder->select($selectArray);
        $queryBuilder->from('gp.vw_projetosescopo_v2', 't0_');
        $where = [$queryBuilder->expr()->eq($this->getFields()[$field], "?")];
        $binds = [$value];
        if ($id) {
            $where[] = $queryBuilder->expr()->neq($this->getFields()['projetoescopo'], "?");
            $binds[] = $id;
        }
        $where[] = $queryBuilder->expr()->eq("t0_.projeto", "?");
        $binds[] = $projeto;
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;
        $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $stmt->execute($binds);
        return count($stmt->fetchAll()) == 0;
    }

    /**
     * @param string  $projeto
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Gp\Projetosescopo $entity
     * @return string 
     * @throws \Exception
     */
    public function insert($projeto, $logged_user, $tenant, $entityArray)
    {
        $sql_1 = "SELECT mensagem
        FROM gp.api_projetosescoponovo_v2(row(
                :projetoescopo,
                :projeto,
                :atividade,
                :pai,
                :created_by,
                :tenant,
                :numero,
                :execucoes,
                :roteiro,
                :servicoid,
                :previsaoinicio,
                :previsaofim,
                :descricaoroteiro,
                :custo_execucao,
                :associarroteiro,
                :roteirolinha,
                :ordem,
                :tipo,
                :descricao,
                :tempoadquirido,
                :pertencearoteiro,
                :execucoesroteiroescopo,
                :datainicio,
                :datafim
            )::gp.tprojetosescoponovo_v2
        );";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("projetoescopo", (isset($entityArray['projetoescopo'])) ? $entityArray['projetoescopo'] : NULL);
        $stmt_1->bindValue("projeto", $projeto);
        $stmt_1->bindValue("atividade", (isset($entityArray['atividade'])) ? $entityArray['atividade'] : NULL);
        $stmt_1->bindValue("pai", (isset($entityArray['pai'])) ? $entityArray['pai']['projetoescopo'] : NULL);
        $stmt_1->bindValue("created_by", json_encode($logged_user));
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("numero", (isset($entityArray['numero'])) ? $entityArray['numero'] : NULL);
        $stmt_1->bindValue("execucoes", (isset($entityArray['execucoes'])) ? $entityArray['execucoes'] : NULL);
        $stmt_1->bindValue("roteiro", (isset($entityArray['roteiro'])) ? $entityArray['roteiro'] : NULL, \PDO::PARAM_BOOL);
        $stmt_1->bindValue("servicoid", (isset($entityArray['servicoid'])) ? $entityArray['servicoid'] : NULL);

        $stmt_1->bindValue("previsaoinicio", empty($entityArray['previsaoinicio']) ? null : $entityArray['previsaoinicio']);
        $stmt_1->bindValue("previsaofim", empty($entityArray['previsaofim']) ? null : $entityArray['previsaofim']);

        $stmt_1->bindValue("descricaoroteiro", (isset($entityArray['descricaoroteiro'])) ? $entityArray['descricaoroteiro'] : NULL);
        $stmt_1->bindValue("custo_execucao", (isset($entityArray['custo_execucao'])) ? $entityArray['custo_execucao'] : NULL);
        $stmt_1->bindValue("associarroteiro", (isset($entityArray['associarroteiro'])) ? $entityArray['associarroteiro'] : NULL, \PDO::PARAM_BOOL);
        $stmt_1->bindValue("roteirolinha", (isset($entityArray['roteirolinha'])) ? $entityArray['roteirolinha'] : NULL);
        $stmt_1->bindValue("ordem", (isset($entityArray['ordem'])) ? $entityArray['ordem'] : NULL);
        $stmt_1->bindValue("tipo", (isset($entityArray['tipo'])) ? $entityArray['tipo'] : NULL);
        $stmt_1->bindValue("descricao", (isset($entityArray['descricao'])) ? $entityArray['descricao'] : NULL);
        $stmt_1->bindValue("tempoadquirido", (isset($entityArray['tempoadquirido'])) ? $entityArray['tempoadquirido'] : NULL);
        $stmt_1->bindValue("pertencearoteiro", (isset($entityArray['pertencearoteiro'])) ? $entityArray['pertencearoteiro'] : NULL, \PDO::PARAM_BOOL);
        $stmt_1->bindValue("execucoesroteiroescopo", (isset($entityArray['execucoesroteiroescopo'])) ? $entityArray['execucoesroteiroescopo'] : NULL);

        $stmt_1->bindValue("datainicio", empty($entityArray['datainicio']) ? null : $entityArray['datainicio']);
        $stmt_1->bindValue("datafim", empty($entityArray['datafim']) ? null : $entityArray['datafim']);

        $stmt_1->execute();
        $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entityArray);

        $sql_2 = "SELECT projetoescopo FROM gp.projetosescopo where projetoescopo = :projetoescopo and tenant = :tenant;";
        $stmt_2 = $this->getConnection()->prepare($sql_2);
        $stmt_2->bindValue("projetoescopo", $resposta);
        $stmt_2->bindValue("tenant", $tenant);

        $stmt_2->execute();
        $retorno = $stmt_2->fetch(\PDO::FETCH_ASSOC);
        return $retorno;
    }

    public function iniciar($logged_user, $tenant, $entityArray)
    {
        $this->getConnection()->beginTransaction();
        try {
            $sql_1 = "SELECT mensagem
            FROM gp.api_projetosescopoiniciar_v2(row(
                    :projetoescopo,
                    :usuario,
                    :tenant
                )::gp.tprojetosescopoiniciar_v2
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("projetoescopo", (isset($entityArray['projetoescopo']) ? $entityArray['projetoescopo'] : null));
            $stmt_1->bindValue("usuario", json_encode($logged_user));
            $stmt_1->bindValue("tenant", $tenant);

            $stmt_1->execute();
            $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entityArray);
            $retorno = $resposta;

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $retorno;
    }
}
