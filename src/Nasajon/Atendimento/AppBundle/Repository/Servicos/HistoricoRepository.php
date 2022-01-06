<?php
namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Doctrine\DBAL\Connection;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\Servicos\Atendimentos\HistoricoRepository as ParentRepository;

class HistoricoRepository extends ParentRepository 
{
    /** @var ConfiguracoesService */
    private $configService;

    public function __construct(Connection $connection, ConfiguracoesService $configService)
    {
        parent::__construct($connection);
        $this->configService = $configService;
    }

    public function findAllQueryBuilder($tenant, $atendimento,  Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select([
            't0_.atendimentohistorico as atendimentohistorico',
            't0_.tipo as tipo',
            't0_.valornovo as valornovo',
            't0_.valorantigo as valorantigo',
            't0_.created_at AT TIME ZONE ? as created_at',
            't0_.created_by as created_by',
            't0_.mesclado_a as mesclado_a',
            't0_.atendimento as atendimento',
            't0_.lastupdate as lastupdate',
        ]);
        $queryBuilder->from('servicos.atendimentoshistoricos', 't0_');
                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $atendimento, $filter);
        
        return [$queryBuilder, $binds];
    }

    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $atendimento, Filter $filter = null)
    {
        $timezone = $this->configService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
        $timezone = $timezone ?: "America/Sao_Paulo";

        $binds = [];
        $where = [];

        $binds[] = $timezone;

        $queryBuilder->addOrderBy("t0_.lastupdate", "DESC");

        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

        $where[] = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq("t0_.mesclado_a", "?"),
                $queryBuilder->expr()->eq("t0_.tipo", 6)
            ),
            $queryBuilder->expr()->eq("t0_.atendimento", "?")
        );

        $binds[] = $atendimento;
        $binds[] = $atendimento;
        
        list($filters, $filtersBinds) = $this->proccessFilter($filter);

        $binds = array_merge($binds, $filtersBinds);

        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }

        if (!empty($filters)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }

        return $binds;
    }
}