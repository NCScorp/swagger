<?php
namespace Nasajon\Atendimento\AppBundle\Repository\Admin;
use Nasajon\MDABundle\Repository\Atendimento\Admin\SlascondicoesRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

class SlascondicoesRepository extends ParentRepository {
    public function findAllQueryBuilder($tenant, $sla, $tipo,  Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
            't0_.tipoentidade as tipoentidade',
            't0_.slacondicao as slacondicao',
            't0_.campo as campo',
            't0_.tipo as tipo',
            't0_.operador as operador',
            't0_.valor as valor'
        ));
        $queryBuilder->from('atendimento.slascondicoes', 't0_');
        $queryBuilder->leftJoin('t0_', 'servicos.atendimentoscamposcustomizados',
                                't1_','t0_.campo = t1_.atendimentocampocustomizado::text');
        $queryBuilder->addSelect([
            't1_.label as camponome'
        ]);

        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $sla, $tipo,  $filter);

        return [$queryBuilder, $binds];
    }
}