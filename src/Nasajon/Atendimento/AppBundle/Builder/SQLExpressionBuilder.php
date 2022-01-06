<?php

namespace Nasajon\Atendimento\AppBundle\Builder;

use Doctrine\DBAL\Query\Expression\CompositeExpression;

class SQLExpressionBuilder extends \Nasajon\Atendimento\AppBundle\Builder\ExpressionBuilder {

    public function eq($campo, $valor) {
        return $this->comparison($campo, "=", $valor);
    }

    public function neq($campo, $valor) {
        return $this->comparison($campo, "<>", $valor);
    }
    
    public function lt($campo, $valor) {
        return $this->comparison($campo, "<", $valor);
    }
    
    public function matches($campo, $valor) {
        return $this->comparison($valor, "IN", "($campo)");
    }
    
    public function notMatches($campo, $valor) {
        return $this->comparison($valor, "NOT IN", "(".$campo.")");
    }

    public function isNotNull($campo) {
        return $campo . ' IS NOT NULL';
    }

    public function isNull($campo) {
        return $campo . ' IS NULL';
    }

    public function nomeCampo($alias, $campo) {
        return $alias ? ($alias . '.' . $campo) : $campo;
    }

    public function andComposite($array) {
        return new CompositeExpression(CompositeExpression::TYPE_AND, $array);
    }

    public function orComposite($array) {
        return new CompositeExpression(CompositeExpression::TYPE_OR, $array);
    }

}
