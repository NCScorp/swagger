<?php

namespace Nasajon\AppBundle\DTO\Crm;

/**
 * Classe referente a cada mês do painel de marketing
 */
class PainelMarketingMesDTO {
    public $anomes;
    public $negociostotais = 0;
    public $listaqualificacao = [];

    public function __construct(
        $anomes
    ){
        $this->anomes = $anomes;
    }

    /**
     * Contabiliza no $anoMes passado um negócio qualificado ou desqualificado de acordo com o parâmetro $qualificado
     */
    public function contabilizarQualificacao($anoMes, $qualificado){
        for ($i=0; $i < count($this->listaqualificacao); $i++) { 
            // Se encontrar o mês informado, contabilizo 1 negócio qualificado
            if ($this->listaqualificacao[$i]->anomes == $anoMes) {
                if ($qualificado) {
                    $this->listaqualificacao[$i]->negociosqualificados++;
                } else {
                    $this->listaqualificacao[$i]->negociosdesqualificados++;
                }
                
                break;
            }
        }
    }
}
