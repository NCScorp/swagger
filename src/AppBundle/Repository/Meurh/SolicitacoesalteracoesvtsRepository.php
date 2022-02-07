<?php

namespace AppBundle\Repository\Meurh;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowRepositoryTrait;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesalteracoesvtsRepository as ParentRepository;

class SolicitacoesalteracoesvtsRepository extends ParentRepository
{
	use WorkflowRepositoryTrait;

	/**
	 * @param string  $tenant
	 * @param string  $logged_user
	 * @return string 
	 * @throws \Exception
	 */
	public function update($tenant, $logged_user, Solicitacoesalteracoesvts $entity, $originalEntity = null)
	{
		parent::update($tenant, $logged_user, $entity, $originalEntity);
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
					t0_.estabelecimento as \"estabelecimento\",
					t0_.tiposolicitacao as \"tiposolicitacao\",
					t0_.codigo as \"codigo\",
					t0_.justificativa as \"motivo\",
					t0_.observacao as \"observacao\",
					t0_.origem as \"origem\",
					t0_.situacao as \"situacao\" ,
					t0_.created_at as \"created_at\",
					t0_.created_by as \"created_by\",
					t0_.updated_at as \"updated_at\",
					t0_.updated_by as \"updated_by\",
					t0_.lastupdate as \"lastupdate\",
					t0_.tenant as \"tenant\",
					t0_.wkf_data as \"wkf_data\",
					t0_.wkf_estado as \"wkf_estado\",
					t1_.trabalhador as \"t1_trabalhador\",
					t1_.agencia as \"t1_agencia\",
					t1_.numerocontasalario as \"t1_numerocontasalario\",
					t1_.numerocontasalariodv as \"t1_numerocontasalariodv\",
					t1_.salarioliquidoestimado as \"t1_salarioliquidoestimado\",
					t1_.estabelecimento as \"t1_estabelecimento\",
					t1_.nome as \"t1_nome\" 
				FROM meurh.solicitacoes t0_
				INNER JOIN persona.trabalhadores t1_ ON t0_.trabalhador = t1_.trabalhador AND t0_.tenant = t1_.tenant          
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
			't0_.codigo as codigo',
			't0_.situacao as situacao',
			't0_.origem as origem',
			't0_.created_at as created_at',
			't0_.estabelecimento as estabelecimento',
			't0_.wkf_data as wkf_data',
			't0_.wkf_estado as wkf_estado'
		];
         
        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }

		$queryBuilder->select($selectArray);
        $queryBuilder->from('meurh.solicitacoes', 't0_');
        
		$queryBuilder->leftJoin('t0_', 'persona.trabalhadores', 't1_', 't0_.trabalhador = t1_.trabalhador   and t0_.tenant = t1_.tenant  '); 
		$queryBuilder->addSelect([
			't1_.trabalhador as trabalhador_trabalhador',
			't1_.numerocontasalario as trabalhador_numerocontasalario',
			't1_.numerocontasalariodv as trabalhador_numerocontasalariodv',
			't1_.salarioliquidoestimado as trabalhador_salarioliquidoestimado',
			't1_.estabelecimento as trabalhador_estabelecimento'
		]);    
                                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $trabalhador, $filter);

        return [$queryBuilder, $binds];
    }
}