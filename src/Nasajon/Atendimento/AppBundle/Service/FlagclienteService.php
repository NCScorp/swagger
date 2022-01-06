<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Repository\Atendimento\Admin\FlagclienteRepository;

/**
 * Classe responsável por avaliar as regras e executar as ações quando um atendimento é criado
 *
 * @author Rodrigo Dirk <rodrigodirk@nasajon.com.br>
 */
class FlagclienteService {

    /**
     *
     * @var FlagclienteRepository
     */
    protected $flagsRepository;

    /**
     *
     * @param FlagclienteRepository $flagsRepository
     */
    public function __construct(FlagclienteRepository $flagsRepository) {
        $this->flagsRepository = $flagsRepository;
    }

    /**
     *
     * @param string $tenant
     * @param array $cliente
     * @return mixed
     */
    public function run($tenant, $cliente) {
        $avisos = [];
        $flags = $this->flagsRepository->findAll($tenant);

        foreach ($flags as $flag) {
            $fullflag = $this->flagsRepository->find($flag['flagcliente'], $tenant);

            if ($this->evaluateCondicoesIn($fullflag['condicoes_in'], $cliente) && $this->evaluateCondicoesEx($fullflag['condicoes_ex'], $cliente)) {
                $avisos[] = $flag;
            }
        }
        return $avisos;
    }

    /**
     * Valida se todas as condições são satisfeitas
     *
     * @param array $condicoes
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateCondicoesEx($condicoes, $cliente) {
        foreach ($condicoes as $condicao) {
            if (!$this->evaluate($condicao, $cliente)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida se qualquer uma das condições é satisfeita
     *
     * @param array $condicoes
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateCondicoesIn($condicoes, $cliente) {
        foreach ($condicoes as $condicao) {
            if ($this->evaluate($condicao, $cliente)) {
                return true;
            }
        }
        return empty($condicoes) ? true : false;
    }

    /**
     * Valida se uma condição é satisfeita
     * @param array $condicao
     * @param array $cliente
     * @return boolean
     */
    protected function evaluate($condicao, $cliente) {

        switch ($condicao['campo']) {
            case 'vendedor':
                return $this->evaluateVendedor($condicao, $cliente);
            case 'bloqueado':
                return $this->evaluateBloqueado($condicao, $cliente);
            case 'estado':
                return $this->evaluateEstado($condicao, $cliente);
            case 'fidelidade':
                return $this->evaluateFidelidade($condicao, $cliente);
            case 'representante':
                return $this->evaluateRepresentante($condicao, $cliente);
            case 'representantetecnico':
                return $this->evaluateRepresentantetecnico($condicao, $cliente);
            case 'cliente_ofertas':
                return $this->evaluateClienteAtivos($condicao, $cliente);
            case 'email':
                return $this->evaluateEmail($condicao, $cliente);
            case 'email_cobranca':
                return $this->evaluateEmailCobranca($condicao, $cliente);
            case 'status_suporte':
                return $this->evaluateStatusSuporte($condicao, $cliente);
            default:
                return $this->evaluateClassificadores($condicao, $cliente);
        }

        return false;
    }

    /**
     * Avalia a condição do tipo 'estado'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateEstado($condicao, $cliente) {

        switch ($condicao['operador']) {
            case 'is_set':
                
                foreach ($cliente['enderecos'] as $endereco) {
                    if (isset($endereco['uf'])) {
                        return true;
                    }
                }

                return false;
            case 'is_not_set':
                return !count($cliente['enderecos']);

            case 'is_equal':
                if (!count($cliente['enderecos'])) {
                    return false;
                }

                // Verifica se algum dos endereços tem a mesma UF da condição
                foreach ($cliente['enderecos'] as $endereco) {
                    if (strtolower($endereco['uf']) == strtolower($condicao['valor'])) {
                        return true;
                    }
                }

                return false;
            case 'is_not_equal':
                if (!count($cliente['enderecos'])) {
                    return true;
                }

                $qtdeUfsNotMatch = 0;

                // Verifica se algum dos endereços tem a mesma UF da condição
                foreach ($cliente['enderecos'] as $endereco) {
                    if (strtolower($endereco['uf']) != strtolower($condicao['valor'])) {
                        $qtdeUfsNotMatch++;
                    }
                }

                // Condição IN - Equivale à lógica OR - ||
                // Ou seja, se qualquer uma das UFs for diferente, retorna true
                if ($condicao['tipo'] == 1) {
                    return $qtdeUfsNotMatch;
                }

                // Para a condição EX - Equivale à lógica AND - &&
                // Como um cliente pode ter mais de um endereço, 
                // verifica se a quantidade de UFs que não são iguais equivale ao número de endereços do cliente.
                // Caso não seja igual, retorna false
                return $qtdeUfsNotMatch == count($cliente['enderecos']);
        }
        return false;
    }

    /**
     * Avalia a condição do tipo 'vendedor'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateVendedor($condicao, $cliente) {

        switch ($condicao['operador']) {
            case 'is_set':
                return isset($cliente['vendedor']);
            case 'is_not_set':
                return !isset($cliente['vendedor']);
            case 'is_equal':
                return isset($cliente['vendedor']) && ($cliente['vendedor']['vendedor'] == $condicao['valor']);
            case 'is_not_equal':
                return isset($cliente['vendedor']) && ($cliente['vendedor']['vendedor'] != $condicao['valor']);
        }
        return false;
    }

    /**
     * Avalia a condição do tipo 'bloqueado'
     *
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateBloqueado($condicao, $cliente) {

        return ($condicao['valor'] == (!is_null($cliente['bloqueado']) ? $cliente['bloqueado'] : 0));
    }
    
    /**
     * Avalia a condição do tipo 'reclame_aqui'
     *
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */    
    protected function evaluateClassificadores(array $condicao, array $cliente): bool 
    {
        switch ($condicao['operador']) {
            case 'is_set':
                return count($cliente['classificadores']) > 0 && !is_null($cliente['classificadores']);
            case 'is_not_set':
                return !(count($cliente['classificadores']) > 0 || is_null($cliente['classificadores']));
            case 'is_equal':
                if (count($cliente['classificadores']) == 0) {
                    return false;
                }
                
                $qtdeItensMatch = 0;

                foreach($cliente['classificadores'] as $classificadores) {
                    $qtdeItensMatch += ($classificadores['classificador'] === $condicao['valor']) ? 1 : 0;
                }

                // Condições do tipo In (equivalente à lógica OR - ||)
                if ($condicao['tipo'] == 1) {
                    return $qtdeItensMatch > 0;
                } 
                
                // Condições do tipo Ex (equivalente à lógica AND - && )
                return $qtdeItensMatch;// == count($cliente['classificadores']);

            case 'is_not_equal':
                if (count($cliente['classificadores']) == 0) {
                    return true;
                }

                $qtdeItensNotMatch = 0;

                foreach($cliente['classificadores'] as $classificadores) {
                    if ($classificadores['classificador'] != $condicao['valor']) {
                        $qtdeItensNotMatch++;
                    }
                }

                // Condição do tipo In (equivalente à lógica OR - ||)
                // Retorna true se qualquer um dos classificadores for diferente
                if ($condicao['tipo'] == 1) {
                    return $qtdeItensNotMatch;
                }

                // Condição do tipo Ex (equivalente à lógica AND - &&)
                // Como um cliente pode ter mais de um classificador,
                // verifica se a quantidade de Classificadores que não são iguais equivale ao número de classificadores do cliente.
                // Caso não seja igual, retorna false
                return $qtdeItensNotMatch == count($cliente['classificadores']);
        }

        return false;
    }
    
    /**
     * Avalia a condição do tipo 'fidelidade'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateFidelidade($condicao, $cliente) {
        return (isset($cliente['datacliente']) && intval((time() - strtotime($cliente['datacliente'])) / 86400) > $condicao['valor'] );
    }

    /**
     * Avalia a condição do tipo 'representante'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateRepresentante($condicao, $cliente) {
        switch ($condicao['operador']) {
            case 'is_set':
                return isset($cliente['representante']) && !is_null($cliente['representante']);
                ;
            case 'is_not_set':
                return !isset($cliente['representante']) || is_null($cliente['representante']);
            case 'is_equal':
                return isset($cliente['representante']) && ($cliente['representante']['representantecomercial'] == $condicao['valor']);
            case 'is_not_equal':
                return isset($cliente['representante']) && ($cliente['representante']['vendedor'] != $condicao['valor']);
        }
        return false;
    }

    /**
     * Avalia a condição do tipo 'representantetecnico'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateRepresentantetecnico($condicao, $cliente) {
        switch ($condicao['operador']) {
            case 'is_set':
                return isset($cliente['representante_tecnico']) && !is_null($cliente['representante_tecnico']);
            case 'is_not_set':
                return !isset($cliente['representante_tecnico']) || is_null($cliente['representante_tecnico']);
            case 'is_equal':
                return isset($cliente['representante_tecnico']) && ($cliente['representante_tecnico']['representantetecnico'] == $condicao['valor']);
            case 'is_not_equal':
                return isset($cliente['representante_tecnico']) && ($cliente['representante_tecnico']['representantetecnico'] != $condicao['valor']);
        }
        return false;
    }
    
    
    /**
     * Avalia a condição do tipo 'cliente_ofertas'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateClienteAtivos($condicao, $cliente) {        
        $ativos = [];
        
        foreach ($cliente['ativos'] as $ativo) {
            foreach ($ativo['ofertas'] as $oferta) {
                if (!empty($oferta['oferta_servicocatalogo']) && $oferta['oferta_ativa'] == true) {
                    $ativos[] = $oferta['oferta_servicocatalogo'];
                }
            }
        }

        if (empty($ativos)) {
            return false;
        }
        
        switch ($condicao['operador']) {
            case 'matches':
                return in_array($condicao['valor'], $ativos);
            case 'not_matches':
                return !in_array($condicao['valor'], $ativos);
        }
        return false;
    }

    /**
     * Avalia a condição do tipo 'email'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateEmail(array $condicao, array $cliente): bool 
    {
        switch ($condicao['operador']) {
            case 'is_set':
                return isset($cliente['email']);
            case 'is_not_set':
                return !isset($cliente['email']);
            case 'is_equal':
                return isset($cliente['email']) && ($cliente['email'] === $condicao['valor']);
            case 'is_not_equal':
                return !(isset($cliente['email']) && ($cliente['email'] === $condicao['valor']));
        }

        return false;
    }

    /**
     * Avalia a condição do tipo 'email_cobranca'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateEmailCobranca(array $condicao, array $cliente): bool 
    {
        switch ($condicao['operador']) {
            case 'is_set':
                return isset($cliente['emailcobranca']);
            case 'is_not_set':
                return !isset($cliente['emailcobranca']);
            case 'is_equal':
                return isset($cliente['emailcobranca']) && ($cliente['emailcobranca'] === $condicao['valor']);
            case 'is_not_equal':
                return !(isset($cliente['emailcobranca']) && ($cliente['emailcobranca'] === $condicao['valor']));
        }

        return false;
    }


    /**
     * Avalia a condição do tipo 'status_suporte'
     * 
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateStatusSuporte(array $condicao, array $cliente): bool 
    {
        switch ($condicao['operador']) {
            case 'is_equal':
                return isset($cliente['status_suporte']) && ($cliente['status_suporte'] === $condicao['valor']);
            case 'is_not_equal':
                return !(isset($cliente['status_suporte']) && ($cliente['status_suporte'] === $condicao['valor']));
        }

        return false;
    }
}
