<?php

namespace AppBundle\Traits\Meurh;

use ReflectionObject;
use AppBundle\Enum\SolicitacoesLabelsEnum;

trait SolicitacoeshistoricosTrait {
    public function findAntigos($novo, $velho) {
        $novo = $this->normalizaObjeto($novo);
        $velho = $this->normalizaObjeto($velho);

        $resultado = $this->arrayRecursiveDiff($velho, $novo);
        return json_encode($resultado);
    }

    public function findNovos($novo, $velho) {
        $novo = $this->normalizaObjeto($novo);
        $velho = $this->normalizaObjeto($velho);
        
        $resultado = $this->arrayRecursiveDiff($novo, $velho);
        return json_encode($resultado);
    }
    
    private function normalizaObjeto($entity){
        $response = [];

        $reflectionObject = new ReflectionObject($entity);
        foreach ($reflectionObject->getProperties() as $property){
          $property->setAccessible(true);

          if(strcmp($property->getName(), "trabalhador") != 0) {
            if (is_object($property->getValue($entity))) {
              $response[$property->getName()] = $this->normalizaObjeto($property->getValue($entity));
            } else if (is_array($property->getValue($entity))) {
              $propertyArray = $property->getValue($entity);
              
              $propertyArray = array_map( function ($element) {
                return is_object($element) ? $this->normalizaObjeto($element) : $element;
              }, $propertyArray);
              $response[$property->getName()] = $propertyArray;
            } else {
              $response[$property->getName()] =  !is_int($property->getValue($entity)) && (is_float($property->getValue($entity))) ? $this->toNumber($property->getValue($entity)) : $property->getValue($entity);
            }
          } 
        }
        return $response;
    }

    private function arrayRecursiveDiff($aArray1, $aArray2) { 
        $return = array("campos" => array()); 
    
        foreach ($aArray1 as $mKey => $mValue) { 
          if (array_key_exists($mKey, $aArray2)) {
            $diferenca = $this->compararValores($mKey, $mValue, $aArray2[$mKey]);
 
            if(!is_null($diferenca)) {
              $diferenca["label"] = SolicitacoesLabelsEnum::labels[$mKey];
              array_push($return["campos"], $diferenca);
            }
          }
        }

        return $return;
    }

    private function toNumber($value) {
        if(is_numeric($value) && (fmod($value, 1) == 0)) {
            return (int) $value;
        }
        return number_format((float)$value, 2, '.', '');
    }
}