<?php
namespace AppBundle\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

class PeriodoAquisitivo extends Constraint
{
    public $message = 'Você pode criar rascunhos, mas não pode enviar marcações nesse período, pois você possui períodos aquisitivos anteriores com pendências de envios.';
}