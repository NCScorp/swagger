<?php

namespace Nasajon\AppBundle\DTO\Crm;

use Nasajon\AppBundle\Service\Relatorios\RelatorioPainelMarketingService;

/**
 * Classe referente aos classificadores do painel de marketing
 */
class PainelMarketingClassificadorDTO {
    public $entidade;
    public $id;
    public $nome;
    public $prenegocios = [];
    public $negociosqualificados = [];
    public $negociosdesqualificados = [];

    public function __construct(
        $entidade,
        $id,
        $nome
    ){
        $this->entidade = $entidade;
        $this->id = $id;
        $this->nome = $nome;

        if ($entidade != RelatorioPainelMarketingService::PMCE_EHCLIENTE && $id == null) {
            $this->nome = 'Não preenchido';
        }
    }
}