<?php

namespace Nasajon\Atendimento\AppBundle\Builder;

use Doctrine\DBAL\Query\Expression\CompositeExpression;

class PHPExpressionBuilder extends ExpressionBuilder {

    public function eq($campo, $valor) {
        return $this->comparison($campo, "==", $valor);
    }

    public function neq($campo, $valor) {
        return $this->comparison($campo, "!=", $valor);
    }

    public function lt($campo, $valor) {
        return $this->comparison($campo, "<", $valor);
    }

    public function matches($campo, $valor) {
        return $this->comparison($campo, "matches", "'".str_replace("'","#",$valor)."'");//"/".."/");
    }
    
    public function notMatches($campo, $valor) {
        return $this->comparison($campo, "matches", "'#^".str_replace("'","",$valor)."#'");//"/".."/");
    }

    public function isNotNull($campo) {
        return $campo . " != null";
    }

    public function isNull($campo) {
        return $campo . " == null";
    }

    public function nomeCampo($alias, $campo) {
        return $alias ? ($alias . '.' . $campo) : $campo;
    }

    public function andComposite($array) {
        return new CompositeExpression("&&", $array);
    }

    public function orComposite($array) {
        return new CompositeExpression("||", $array);
    }

}
