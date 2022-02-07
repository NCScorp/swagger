<?php
namespace AppBundle\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

class Marcacao extends Constraint
{
    public $message = 'Data de início de gozo inválida';
}