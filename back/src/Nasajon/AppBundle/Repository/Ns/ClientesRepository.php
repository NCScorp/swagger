<?php

/*
  Sobrescrito para aplicar filtro notMapped
 */

namespace Nasajon\AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\Ns\ClientesRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

/**
 * FornecedoresRepository
 *
 */
class ClientesRepository extends ParentRepository {
  /**
   * Sobrescrito para tratar filtro notMapped
   * @throws \LogicException
   */
  protected function tiposatividadesfilterFilter() {
    return "join1_.tipoatividade";
  }

  /**
   * 
   * Sobrescrito para tratar filtro notMapped
   * @param type $queryBuilder
   * @throws \LogicException
   */
  protected function tiposatividadesfilterJoin($queryBuilder) {
     $queryBuilder->leftJoin('t0_', 'ns.pessoastiposatividades', 'join1_', 't0_.cliente = join1_.pessoa and t0_.tenant = join1_.tenant');
     /*Implementado para corrigir a entrada dupla na lista de clientes ao adicionar dois tiposatividades no mesmo cliente
      Modifica o select do repository adicionando a cláusula distinct em clientes evitando assim repetição*/
     $selectModificado = json_decode(str_replace('\\u0000', '', json_encode((array) $queryBuilder)), true);
     $selectModificado['Doctrine\DBAL\Query\QueryBuildersqlParts']['select'][0] = "distinct t0_.cliente as cliente";
     $queryBuilder->select($selectModificado['Doctrine\DBAL\Query\QueryBuildersqlParts']['select']);
  }

  /**
   * Sobrescrito para remover registros duplicados do resultado, causadas pelo join OneToMany de tiposatividades
   * @param string $tenant
   * @param Filter $filter
   * @return array
   */

}