<?php
namespace AppBundle\Exception;
class ActionException extends \Exception
{ 
    public function __construct(string $message = "", int $code = 0)
    {
        $message = empty($message) ? "Solicitação não realizada." : $message;
        
        parent::__construct($message, $code);
    }
}