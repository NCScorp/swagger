<?php

namespace AppBundle\Service\Ns;


use Nasajon\MDABundle\Service\Ns\ConfiguracoesService as ParentService;
use AppBundle\Enum\Ns\configuracoesGeraisEnum as ConfiguracoesGeraisEnum;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Request\Filter;

class ConfiguracoesService extends ParentService
{    
    public function estabelecimentoModulosHabilitados($tenant, $estabelecimento) {
        $campo = ConfiguracoesGeraisEnum::CAMPOSGERAIS_DIAS_PARA_CALCULO;
        $grupo = ConfiguracoesGeraisEnum::GRUPO_PERSONALIZACAO_CLIENTE;
        $aplicacao = ConfiguracoesGeraisEnum::APLICACAO_PERSONA;

        $filter = new Filter();
        $filterExpression = $filter->getFilterExpression();
        array_push($filterExpression, new FilterExpression('campo', 'eq', $campo));
        array_push($filterExpression, new FilterExpression('grupo', 'eq', $grupo));
        array_push($filterExpression, new FilterExpression('aplicacao', 'eq', $aplicacao));
        array_push($filterExpression, new FilterExpression('estabelecimento', 'eq', $estabelecimento));
        $filter->setFilterExpression($filterExpression);


        $configuracao = $this->getRepository()->findAll($tenant, $filter);

        $valor = array_key_exists(0, $configuracao) ? $configuracao[0]['valor'] : null;

        if ($valor != null && $valor === 'true') {
            $resultado['salariosobdemanda'] = true;
            return $resultado;
        }

        $resultado['salariosobdemanda'] = false;

        return $resultado;
    }

    public function getConfiguracaoByCampo($campo, $tenant, $estabelecimento) {
        return $this->getRepository()->getConfiguracaoByCampo($campo, $tenant, $estabelecimento);
    }
}
