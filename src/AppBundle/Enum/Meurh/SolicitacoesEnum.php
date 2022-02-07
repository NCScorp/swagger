<?php

namespace AppBundle\Enum\Meurh;

/**
 * Enum para ser usado nas solicitacoes
 */
abstract class SolicitacoesEnum
{
    const SITUACAO_RASCUNHO   = -1;
    const SITUACAO_PENDENTE   = 0;
    const SITUACAO_APROVADO   = 1;
    const SITUACAO_REPROVADO  = 2;
}
