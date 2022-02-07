<?php

namespace AppBundle\Repository\Meurh;

use Doctrine\DBAL\Connection;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowRepositoryTrait;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesfaltasRepository as ParentRepository;

class SolicitacoesfaltasRepository extends ParentRepository
{
	use WorkflowRepositoryTrait;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection);

		$this->setLinks([
			[
				'field' => 'trabalhador',
				'entity' => 'Nasajon\MDABundle\Entity\Persona\Trabalhadores',
				'alias' => 't1_',
				'identifier' => 'trabalhador',
				'type' => 2
			],
		]);
	}

	public function findDraft($id, $tenant)
	{
		$where = "WHERE t0_.solicitacao = :id AND t0_.tenant = :tenant";

		$sql =
			"SELECT 
                t0_.solicitacao as \"solicitacao\" ,
                t0_.estabelecimento as \"estabelecimento\" ,
                t0_.tiposolicitacao as \"tiposolicitacao\" ,
                t0_.codigo as \"codigo\" ,
                t0_.justificativa as \"justificativa\" ,
                t0_.observacao as \"observacao\" ,
                t0_.origem as \"origem\" ,
                t0_.situacao as \"situacao\" ,
                t0_.created_at as \"created_at\" ,
                t0_.created_by as \"created_by\" ,
                t0_.updated_by as \"updated_by\" ,
                t0_.updated_at as \"updated_at\" ,
                t0_.lastupdate as \"lastupdate\" ,
                t0_.tenant as \"tenant\" ,
                t0_.data as \"data\" ,
                t0_.justificada as \"justificada\" ,
                t0_.descontaponto as \"descontaponto\" ,
                t0_.compensacao as \"compensacao\" ,
                t0_.mesdescontocalculo as \"mesdescontocalculo\" ,
                t0_.anodescontocalculo as \"anodescontocalculo\" ,
                t0_.trabalhador as \"trabalhador\"
            FROM meurh.solicitacoesfaltas t0_
			$where";

		$binds = [
			"id" => $id,
			"tenant" => $tenant
		];
	
		$data = $this->getConnection()->executeQuery($sql, $binds)->fetch();

		return $data;
	}

	/**
	 * @param string  $tenant
	 * @param string  $logged_user
	 * @return string 
	 * @throws \Exception
	 */
	public function draftInsert($trabalhador, $tenant, $logged_user, Solicitacoesfaltas $entity)
	{
		$sql_1 = "SELECT mensagem FROM meurh.api_Solicitacaofaltarascunho(
					row(
						:tenant,
						:estabelecimento,
						:origem,
						:created_by
					)::meurh.tsolicitacaofaltarascunho
        		);";

		$stmt_1 = $this->getConnection()->prepare($sql_1);

		$stmt_1->bindValue("tenant", $tenant);
		$stmt_1->bindValue("origem", $entity->getOrigem());
		$stmt_1->bindValue("created_by", json_encode($logged_user));
		$stmt_1->bindValue("estabelecimento", $entity->getEstabelecimento());
		$stmt_1->execute();

		$retorno = $this->processApiReturn($stmt_1->fetchColumn(), $entity);
		$entity->setSolicitacao($retorno);

		return $retorno;
	}

	/**
	 * @param string  $trabalhador
	 * @param string  $tenant
	 * @param string  $logged_user
	 * @return string 
	 * @throws \Exception
	 */
	public function update($trabalhador, $tenant, $logged_user, Solicitacoesfaltas $entity)
	{
		$response = parent::update($trabalhador, $tenant, $logged_user, $entity);
		$retorno["solicitacao"] = $entity->getSolicitacao();

		return $retorno;
	}

	public function find($id, $tenant, $trabalhador)
	{
		$where = $this->buildWhere();
		$whereFields = [
			'id' => $id,
			'tenant' => $tenant,
			'trabalhador' => $trabalhador
		];

		$data = $this->findQuery($where, $whereFields)->fetch();

		return $this->adjustQueryData($data);
	}

	private function findQuery(string $where, array $whereFields)
	{
		$sql = "SELECT
					t0_.solicitacao as \"solicitacao\",
					t0_.trabalhador as \"trabalhador\",
					t0_.estabelecimento as \"estabelecimento\",
					t0_.tiposolicitacao as \"tiposolicitacao\",
					t0_.codigo as \"codigo\",
					t0_.justificativa as \"justificativa\",
					t0_.observacao as \"observacao\",
					t0_.origem as \"origem\",
					t0_.situacao as \"situacao\",
					t0_.created_at as \"created_at\",
					t0_.created_by as \"created_by\",
					t0_.updated_at as \"updated_at\",
					t0_.updated_by as \"updated_by\",
					t0_.lastupdate as \"lastupdate\",
					t0_.tenant as \"tenant\",
					t0_.datas as \"datas\",
					t0_.justificada as \"justificada\",
					t0_.tipojustificativa as \"tipojustificativa\",
					t0_.descontaponto as \"descontaponto\",
					t0_.compensacao as \"compensacao\",
					t0_.mesdescontocalculo as \"mesdescontocalculo\",
					t0_.anodescontocalculo as \"anodescontocalculo\",
					t0_.wkf_data as \"wkf_data\",
					t0_.wkf_estado as \"wkf_estado\",
					t1_.nome as \"t1_nome\",
					t1_.trabalhador as \"t1_trabalhador\" 
				FROM meurh.solicitacoesfaltas t0_
				LEFT JOIN persona.trabalhadores t1_ ON t0_.trabalhador = t1_.trabalhador AND t0_.tenant = t1_.tenant              
				{$where}";

		return $this->getConnection()->executeQuery($sql, $whereFields);
	}

	/**
	 * @param int $tenant
	 * @param string $trabalhador
	 * @return array
	 */
	public function findAllQueryBuilder($tenant, $trabalhador, Filter $filter = null)
	{
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        $selectArray = [
			't0_.solicitacao as solicitacao',
			't0_.estabelecimento as estabelecimento',
			't0_.codigo as codigo',
			't0_.situacao as situacao',
			't0_.datas as datas',
			't0_.created_at as created_at',
			't0_.origem as origem',
			't0_.wkf_data as wkf_data',
			't0_.wkf_estado as wkf_estado'
		];
         
        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }

		$queryBuilder->select($selectArray);
        $queryBuilder->from('meurh.solicitacoesfaltas', 't0_');
                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $trabalhador, $filter);

        return [$queryBuilder, $binds];
    }
}