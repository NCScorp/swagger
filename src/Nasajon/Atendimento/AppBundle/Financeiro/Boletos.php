<?php

namespace Nasajon\Atendimento\AppBundle\Financeiro;

use Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface para geração de boletos
 */
interface Boletos {

    /**
     * @param Titulos $titulo titulo a partir do qual vai ser gerada a segunda via do boleto
     * 
     * @return Response
     */
    public function gerar(Titulos $titulo);
}
