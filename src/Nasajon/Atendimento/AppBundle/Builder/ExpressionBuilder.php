<?php

namespace Nasajon\Atendimento\AppBundle\Builder;

use Doctrine\DBAL\Query\Expression\CompositeExpression;

abstract class ExpressionBuilder {

    abstract function isNull($campo);

    abstract function isNotNull($campo);

    abstract function eq($campo, $valor);

    abstract function neq($campo, $valor);
    
    abstract function lt($campo, $valor);
    
    abstract function matches($campo, $valor);
    
    abstract function notMatches($campo, $valor);

    abstract function nomeCampo($alias, $campo);

    abstract function andComposite($array);

    abstract function orComposite($array);

    /**
     * 
     * @param array $arrayFilter
     * @param string $alias
     * @return CompositeExpression
     */
    public function build($arrayFilter, $alias) {
      
        if ($arrayFilter == null || $arrayFilter['todosclientes']) {
            return;
        }

        foreach ($arrayFilter as $key => $value) {
            if (strstr($key, 'in') && is_array ($value)) {
                $in = $this->conditions($value, $alias);
            } else if (strstr($key, 'ex') && is_array ($value)) {
                $ex = $this->conditions($value, $alias);
            }
        }
        
        $result = [];
        if (!empty($in)) {
            $result[] = $this->orComposite($in);
        }
        if (!empty($ex)) {
            $result[] = $this->andComposite($ex);
        }
        
        if(empty($in) && empty($ex))
            return true;
        
        return $this->andComposite($result);
    }
    
    /**
     * 
     * @param array $arrayFilter
     * @param string $alias
     * @return CompositeExpression
     */
    public function buildList($arrayFilter, $alias) {      
      if ($arrayFilter == null) return;      
      $result = [];
      
      foreach ($arrayFilter as $item){
        foreach ($item as $key => $value) {
          if (strstr($key, 'in') && is_array ($value)) {
              $in = $this->conditions($value, $alias);
          } else if (strstr($key, 'ex') && is_array ($value)) {
              $ex = $this->conditions($value, $alias);
          }
        }
        
        if (!empty($in)) {
            $result[] = $this->orComposite($in);
        }
        if (!empty($ex)) {
            $result[] = $this->andComposite($ex);
            
        }
        
        if(empty($in) && empty($ex))
            return null;
      }
      
      return $this->andComposite($result);
    }

    protected function conditions($condicoes, $alias) {
        $where = [];
        foreach ($condicoes as $condicao) {
            $where[] = $this->condition($condicao, $alias);
        }
        return $where;
    }

    /**
     * Valida se uma condição é satisfeita
     * @param array $condicao
     * @return boolean
     */
    protected function condition($condicao, $alias) {

        switch (true) {
            case ($condicao['campo'] != 'bloqueado' && $condicao['campo'] != 'representantetecnico'):
                return $this->evaluateString($condicao['operador'], $condicao['campo'], $condicao['valor'], $alias);
            case ($condicao['campo'] == 'bloqueado'):
                return $this->evaluateBoolean($condicao, $alias);    
            case ($condicao['campo'] == 'representantetecnico'):
                return $this->evaluateString($condicao['operador'], 'representante_tecnico', $condicao['valor'], $alias);
        }

        return null;
    }

    protected function evaluateString($operador, $campo, $valor, $alias) {
        switch ($operador) {
            case 'is_set':
                return $this->isNotNull($this->nomeCampo($alias, $campo));
            case 'is_not_set':
                return $this->isNull($this->nomeCampo($alias, $campo));
            case 'is_equal':
                return $this->eq($this->nomeCampo($alias, $campo), "'" . $valor . "'");
            case 'matches':
                return $this->matches($this->nomeCampo($alias, $campo), "'" . $valor . "'");
            case 'not_matches':
                return $this->notMatches($this->nomeCampo($alias, $campo), "'" . $valor . "'");
            case 'is_not_equal':
                return $this->neq($this->nomeCampo($alias, $campo), "'" . $valor . "'");
            case 'less_than':
                return $this->lt($this->nomeCampo($alias, $campo), "'" . $valor . "'");
        }

        return false;
    }

    protected function evaluateBoolean($condicao, $alias) {

        switch ($condicao['operador']) {
            case 'is_equal':
                return $this->eq($this->nomeCampo($alias, $condicao['campo']), ($condicao['valor'] == '1') ? 'true' : 'false');
            case 'is_not_equal':
                return $this->neq($this->nomeCampo($alias, $condicao['campo']), $condicao['valor']);
        }
        return false;
    }

    /**
     * Creates a comparison expression.
     *
     * @param mixed  $x        The left expression.
     * @param string $operator One of the ExpressionBuilder::* constants.
     * @param mixed  $y        The right expression.
     *
     * @return string
     */
    public function comparison($x, $operator, $y) {
        return $x . ' ' . $operator . ' ' . $y;
    }

}
