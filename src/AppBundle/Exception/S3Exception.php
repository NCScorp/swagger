<?php

namespace AppBundle\Exception;

class S3Exception extends \Exception
{ 
    public function __construct(string $message = "", int $code = 0)
    {
        $message = empty($message) ? "Não foi possível escrever ou recuperar esse dado" : $message;

        parent::__construct($message, $code);
    }
}