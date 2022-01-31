<?php

/*
  Sobrescrito para usar filtro notMapped
 */

namespace Nasajon\AppBundle\Repository\Ns;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\Ns\FornecedoresRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

/**
 * FornecedoresRepository
 *
 */
class FornecedoresRepository extends ParentRepository
{


    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        parent::__construct($connection);
        $this->setFilterFields([
            'fornecedor' => 't0_.id',
            'funcionarioativado' => 't0_.funcionarioativado',
            'contribuinteindividualativado' => 't0_.contribuinteindividualativado',
            'status' => 't91_.tipo',
            'municipionome' => 't93_.nome',
            'tiposatividadesfilter' => $this->tiposatividadesfilterFilter(),
        ]);

        $this->setFilters([
            'nomefantasia' => 't0_.nomefantasia',
            'razaosocial' => 't0_.nome',
            'cnpj' => 't0_.cnpj',
            'municipionome' => 't93_.nome',
        ]);

        $this->setFields([
            'fornecedor' => 't0_.fornecedor',
            'nomefantasia' => 't0_.nomefantasia',
            'razaosocial' => 't0_.nome',
            'cnpj' => 't0_.cnpj',
            'municipionome' => 't93_.nome',
            'codigofornecedores' => 't0_.pessoa',
            'status' => 't91_.tipo',
            'esperapagamentoseguradora' => 't0_.esperapagamentoseguradora',
            'diasparavencimento' => 't0_.diasparavencimento',
            'estabelecimentoid' => 't0_.estabelecimentoid',
            'funcionarioativado' => 't0_.funcionarioativado',
            'contribuinteindividualativado' => 't0_.contribuinteindividualativado',
            'anotacao' => 't0_.anotacao',
            'formapagamento' => 't0_.id_formapagamento_fornecedor',
        ]);

        $this->setOffsets([
            'nomefantasia' => [ "column" => "nomefantasia", "direction" => \Doctrine\Common\Collections\Criteria::ASC, "id" => false],

            'fornecedor' => [ "column" => "id", "direction" => \Doctrine\Common\Collections\Criteria::ASC, "id" => true],

        ]);
    }

    /**
     * Sobrescrito para tratar filtro notMapped
     * @throws \LogicException
     */
    protected function tiposatividadesfilterFilter()
    {
        return "join1_.tipoatividade";
    }

    /**
     *
     * Sobrescrito para tratar filtro notMapped
     * @param type $queryBuilder
     * @throws \LogicException
     */
    protected function tiposatividadesfilterJoin($queryBuilder)
    {
        $queryBuilder->leftJoin('t0_', 'ns.pessoastiposatividades', 'join1_', 't0_.id = join1_.pessoa and t0_.tenant = join1_.tenant');
    }

    /**
     * Consulta que informa se, dado um fornecedor, ele possui orçamento aprovado
     *
     * @return void
     */
    public function getOrcamentoEstaAprovado($tenant, $id_grupoempresarial, $negocio, $fornecedor)
    {

        $sql = 'SELECT (CASE WHEN orc.fornecedor IS NULL THEN false ELSE true END) as possuiorcamentoaprovado
              FROM crm.orcamentos orc
              JOIN crm.propostasitens propitem on propitem.propostaitem = orc.propostaitem and propitem.tenant = orc.tenant
              WHERE orc.fornecedor = :fornecedor
              AND orc.tenant = :tenant
              AND orc.status = 2
              AND propitem.negocio = :negocio
              AND propitem.id_grupoempresarial = :grupoempresarial
              LIMIT 1;
      ';

        $bindQuery = [
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial,
            'negocio' => $negocio,
            'fornecedor' => $fornecedor
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchColumn();
        return $data;
    }


    /**
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
    {

        $this->validateOffset($filter);

        list($sql, $binds) = $this->findAllQueryBuilder($tenant, $id_grupoempresarial, $filter);

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute($binds);

        $joins = ['estabelecimentoid', 'formapagamento',];

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

    public function findAllQueryBuilder($tenant, $id_grupoempresarial, Filter $filter = null)
    {

        $sqlWith = "with pessoas_unq as (select
                        distinct pessoas.id
                    from
                        ns.pessoas pessoas
                    inner join ns.conjuntosfornecedores cf on
                        cf.registro = pessoas.id
                        and cf.tenant = pessoas.tenant
                    inner join ns.conjuntos c on
                        cf.conjunto = c.conjunto
                        and c.tenant = pessoas.tenant
                    inner join ns.estabelecimentosconjuntos ec on
                        c.conjunto = ec.conjunto
                        and ec.tenant = pessoas.tenant
                    inner join ns.estabelecimentos est on
                        est.estabelecimento = ec.estabelecimento
                        and est.tenant = pessoas.tenant
                    inner join ns.empresas emp on
                        emp.empresa = est.empresa
                        and emp.tenant = pessoas.tenant
                    where
                        (pessoas.tenant = ?)
                        and (emp.grupoempresarial = ?)
                )";

        $bind_join[] = $tenant;
        $bind_join[] = $id_grupoempresarial;


        $sqlPrincipal = "select
	t0_.id as fornecedor,
	case
		when t0_.nomefantasia::text = ''::text then coalesce(t0_.nome, 'Não informado'::character varying)::character varying(150)
		else coalesce(t0_.nomefantasia, t0_.nome, 'Não informado'::character varying)::character varying(150)
	end as nomefantasia,
	t0_.nome as razaosocial,
	t0_.cnpj as cnpj,
	t93_.nome as municipionome,
	t0_.pessoa as codigofornecedores,
	case
		when t91_.tipo = 1 then '1'::text
		when t91_.tipo = 2 then '2'::text
		else '0'::text
	end as status,
	t0_.esperapagamentoseguradora as esperapagamentoseguradora,
	t0_.diasparavencimento as diasparavencimento,
	t0_.funcionarioativado as funcionarioativado,
	t0_.contribuinteindividualativado as contribuinteindividualativado,
	t0_.anotacao as anotacao,
	t8_.estabelecimento as estabelecimentoid_estabelecimento,
	t8_.nomefantasia as estabelecimentoid_nomefantasia,
	t8_.grupoempresarial as estabelecimentoid_id_grupoempresarial,
	t8_.raizcnpj as estabelecimentoid_raizcnpj,
	t8_.ordemcnpj as estabelecimentoid_ordemcnpj,
	t8_.cnpj_completo as estabelecimentoid_cnpj_completo,
	t8_.inscricaoestadual as estabelecimentoid_inscricaoestadual,
	t8_.inscricaomunicipal as estabelecimentoid_inscricaomunicipal,
	t8_.email as estabelecimentoid_email,
	t8_.site as estabelecimentoid_site,
	t8_.tipologradouro as estabelecimentoid_tipologradouro,
	t8_.logradouro as estabelecimentoid_logradouro,
	t8_.numero as estabelecimentoid_numero,
	t8_.complemento as estabelecimentoid_complemento,
	t8_.bairro as estabelecimentoid_bairro,
	t8_.cidade as estabelecimentoid_cidade,
	t8_.cep as estabelecimentoid_cep,
	t8_.ibge as estabelecimentoid_ibge,
	t8_.dddtel as estabelecimentoid_dddtel,
	t8_.telefone as estabelecimentoid_telefone,
	t8_.telefonecomddd as estabelecimentoid_telefonecomddd,
	t8_.razaosocial as estabelecimentoid_razaosocial,
	t10_.formapagamento as formapagamento_formapagamento,
	t10_.codigo as formapagamento_codigo,
	t10_.descricao as formapagamento_descricao,
	t10_.bloqueada as formapagamento_bloqueada
from
	ns.pessoas t0_
left join ns.enderecos t90_ on
	t90_.id_pessoa = t0_.id
	and t90_.tipoendereco = 0
	and t90_.tenant = t0_.tenant
left join ns.fornecedoressuspensos t91_ on
	t91_.fornecedor_id = t0_.id
	and t91_.tenant = t0_.tenant
left join ns.pessoaslogos t92_ on
	t92_.id_pessoa = t0_.id
	and t92_.tenant = t0_.tenant
	and t92_.ativo = true
	and t92_.tenant = t0_.tenant
left join ns.vw_estabelecimentos t8_ on
	t0_.estabelecimentoid = t8_.estabelecimento
	and t0_.tenant = t8_.tenant
	and t8_.grupoempresarial = ?
left join ns.formaspagamentos t10_ on
	t0_.id_formapagamento_fornecedor = t10_.formapagamento
	and t0_.tenant = t10_.tenant
left join ns.pessoastiposatividades join1_ on
	t0_.id = join1_.pessoa
	and t0_.tenant = join1_.tenant
left join ns.municipios t93_ on
	t93_.ibge::text = t90_.ibge::text
where
	(t0_.id in ( select id from pessoas_unq))";

        $bind_join[] = $id_grupoempresarial;

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, $filter);
        $sqlWhere = $queryBuilder->getSQL();
        //Removendo a parte que não é importante, deixando somente a condição posterior ao where ->
        // String vem da seguinte forma select where ....
        $sqlWhere = substr($sqlWhere, stripos($sqlWhere, "where ")+5);
        $queryFinal = sprintf("%s %s and %s", $sqlWith, $sqlPrincipal, $sqlWhere);
        $binds = array_merge($bind_join, $binds);
        return [$queryFinal, $binds];
    }


    public function findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, Filter $filter = null)
    {
        $binds = [];
        $where = [];

        if ($filter && !empty($filter->getOrder())) {
            foreach ($filter->getOrder() as $column => $direction) {
                $queryBuilder->addOrderBy("t0_.{$this->getOrders()[$column]}", strtoupper($direction));
            }
        }
        $queryBuilder->addOrderBy("t0_.nomefantasia", "ASC");
        $queryBuilder->addOrderBy("t0_.id", "ASC");


        $queryBuilder->setMaxResults(20);


        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

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
}
