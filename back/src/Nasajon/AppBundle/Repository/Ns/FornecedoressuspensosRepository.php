<?php

/*
  Sobrescrito para usar filtro notMapped
 */

namespace Nasajon\AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\Ns\FornecedoressuspensosRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

/**
 * FornecedoressuspensosRepository
 *
 */
class FornecedoressuspensosRepository extends ParentRepository {

    /**
   * Sobrescrito para tratar filtro notMapped
   * @throws \LogicException
   */
    protected function fornecedoresfilterFilter(){

    }

  /**
   * 
   * Sobrescrito para tratar filtro notMapped
   * @param type $queryBuilder
   * @throws \LogicException
   */
  protected function fornecedoresfilterJoin($queryBuilder) {
     /*Modifica o select do repository adicionando a cláusula distinct em fornecedoressuspensos evitando assim repetição*/
      $selectModificado = json_decode(str_replace('\\u0000', '', json_encode((array) $queryBuilder)), true);
      $selectModificado['Doctrine\DBAL\Query\QueryBuildersqlParts']['select'][0] = "distinct t0_.fornecedorsuspenso as fornecedorsuspenso";
      $queryBuilder->select($selectModificado['Doctrine\DBAL\Query\QueryBuildersqlParts']['select']);
  }

}
