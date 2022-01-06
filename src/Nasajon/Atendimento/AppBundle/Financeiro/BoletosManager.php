<?php

namespace Nasajon\Atendimento\AppBundle\Financeiro;

use Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos;

/**
 * 
 */
final class BoletosManager {

    private static $_boletosMap = array(
        '341' => 'Nasajon\Atendimento\AppBundle\Financeiro\Boletos\Itau',
        '237' => 'Nasajon\Atendimento\AppBundle\Financeiro\Boletos\Bradesco',
    );

    /**
     * Private constructor. This class cannot be instantiated.
     */
    private function __construct() {        
    }

    public static function gerar(Titulos $titulo) {
        
        if(isset(self::$_boletosMap[$titulo->getCodigobanco()])){
            $boletoClass = self::$_boletosMap[$titulo->getCodigobanco()];            
            $boleto = new $boletoClass();
            return $boleto->gerar($titulo);
        }
        
        throw new \Exception(sprintf("Banco não suportado %d.", $titulo->getCodigobanco()));
        
    }

}
