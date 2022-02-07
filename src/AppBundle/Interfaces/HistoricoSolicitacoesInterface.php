<?php

namespace AppBundle\Interfaces;

interface HistoricoSolicitacoesInterface
{
  /**
   * Compara dois valores da classe, e retorna um array com os dados diferentes ou null,
   * dependendo se é diferente ou não, respectivamente.
   * 
   * @return array
   */
  public function compararValores($key, $value1, $value2);

}