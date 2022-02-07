<?php

namespace AppBundle\Exception;

class SolicitacaoAlteracaoVtException extends \Exception
{ 
    public function __construct(string $message, int $code = 0)
    {
        $message = empty($message) ? "Não é possível salvar uma solicitação sem tarifas" : $message;
        
        parent::__construct($message, $code);
    }
}
