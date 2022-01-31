<?php

namespace Nasajon\AppBundle\Repository\Ns;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;

/**
 * Busca a data e a hora do servidor
 */
class DatahoraservidorRepository extends AbstractRepository {

  /**
   * @return string 
   * @throws \Exception
   */
  public function dataHoraServidor() {
    $sql_1 = "SELECT 
              data_atual as data, 
              hora_atual as hora
              FROM ns.api_dataHoraServidor();";

    $stmt_1 = $this->getConnection()->prepare($sql_1);
    $stmt_1->execute();
    return $stmt_1->fetch();

  }

}
