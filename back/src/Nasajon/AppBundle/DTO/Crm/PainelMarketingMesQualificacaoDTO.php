<?php

namespace Nasajon\AppBundle\DTO\Crm;

/**
 * Classe referente a cada mês de qualificação dos dados do PainelMarketingMes
 */
class PainelMarketingMesQualificacaoDTO {
    public $anomes;
    public $negociosqualificados = 0;
    public $negociosdesqualificados = 0;

    public function __construct(
        $anomes
    ){
        $this->anomes = $anomes;
    }
}
