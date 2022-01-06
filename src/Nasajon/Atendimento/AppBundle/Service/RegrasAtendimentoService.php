<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosfilasRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosregrasRepository;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\MDABundle\Repository\Atendimento\Admin\EnderecosemailsRepository;
use Nasajon\MDABundle\Repository\Ns\ClientesRepository;

/**
 * Classe responsável por avaliar as regras e executar as ações quando um atendimento é criado
 *
 * @author Rodrigo Dirk <rodrigodirk@nasajon.com.br>
 */
class RegrasAtendimentoService {

    /**
     *
     * @var AtendimentosregrasRepository
     */
    protected $regrasRepo;

    /**
     *
     * @var EnderecosemailsRepository
     */
    protected $enderecosemailsRepo;
    private $tenant;

    private $filasRepository;

    /**
     *
     * @param AtendimentosregrasRepository $regrasRepo
     * @param EnderecosemailsRepository $enderecosemailsRepo
     */
    public function __construct(AtendimentosregrasRepository $regrasRepo, EnderecosemailsRepository $enderecosemailsRepo, ClientesRepository $clientesRepo, AtendimentosfilasRepository $filasRepository) {
        $this->regrasRepo = $regrasRepo;
        $this->enderecosemailsRepo = $enderecosemailsRepo;
        $this->clientesRepo = $clientesRepo;
        $this->filasRepository = $filasRepository;
    }

    /**
     *
     * @param string $tenant
     * @param Solicitacoes $atendimento
     * @return mixed
     */
    public function run($tenant, Solicitacoes &$atendimento) {
        $this->tenant = $tenant;

        $clienteObj = null;

        if ($atendimento->getCliente()) {
            $clienteObj = $this->clientesRepo->find($atendimento->getCliente()->getCliente(), $tenant);
        }

        $regras = $this->regrasRepo->findAll($tenant);
        $autoreply = null;

        foreach ($regras as $regra) {
            $regraObj = $this->regrasRepo->find($regra['atendimentoregra'], $tenant);

            if ($this->evaluateCondicoesIn($regraObj['condicoes_in'], $atendimento, $clienteObj) && $this->evaluateCondicoesEx($regraObj['condicoes_ex'], $atendimento, $clienteObj)) {
                foreach ($regraObj['acoes'] as $acao) {
                    if ($acao['acao'] == 'enviar_resposta_automatica') {
                        $autoreply = $acao['atendimentoregraacao'];
                    } else {
                        $this->action($acao, $atendimento);
                    }
                }
                if ($regraObj['naoexecutarregrasubsequente']) {
                    break;
                }
            }
        }
        return $autoreply;
    }

    /**
     * Valida se todas as condições são satisfeitas
     *
     * @param array $condicoes
     * @return boolean
     */
    protected function evaluateCondicoesEx($condicoes, $atendimento, $cliente) {
        foreach ($condicoes as $condicao) {
            if (!$this->evaluate($condicao, $atendimento, $cliente)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida se qualquer uma das condições é satisfeita
     *
     * @param array $condicoes
     * @return boolean
     */
    protected function evaluateCondicoesIn($condicoes, $atendimento, $cliente) {
        foreach ($condicoes as $condicao) {
            if ($this->evaluate($condicao, $atendimento, $cliente)) {
                return true;
            }
        }
        return empty($condicoes) ? true : false;
    }

    /**
     * Valida se uma condição é satisfeita
     * @param array $condicao
     * @return boolean
     */
    protected function evaluate($condicao, Solicitacoes $atendimento, $cliente) {

        switch (true) {
            case ($condicao['campo'] == 'situacao'):
                return $this->evaluateSituacao($condicao, $atendimento->getSituacao());
            case ($condicao['campo'] == 'sintoma'):
                return $this->evaluateString($condicao, strip_tags(html_entity_decode($atendimento->getSintoma())));
            case ($condicao['campo'] == 'email'):
                return $this->evaluateString($condicao, $atendimento->getEmail());
            case ($condicao['campo'] == 'canalemail'):
                    return $this->evaluateCanalemail($condicao, $atendimento->getCanalEmail());
            case ($condicao['campo'] == 'representante'):
                return $this->evaluateRepresentante($condicao, $cliente);
            case ($condicao['campo'] == 'representantetecnico'):
                return $this->evaluateRepresentantetecnico($condicao, $cliente);
            case ($condicao['campo'] == 'status_suporte'):
                return $this->evaluateStatusSuporte($condicao, $cliente);
            case (StringUtils::isGuid($condicao['campo'])):
                return $this->evaluateCampoGuid($condicao, $atendimento, $cliente);
        }


        return false;
    }

    /**
     * Valida se uma condição é satisfeita'
     *
     * @param array $condicao
     * @param mixed $atendimento
     * 
     * @return boolean
     */
    protected function evaluateCampoGuid($condicao, $atendimento, $cliente) {
        if ($condicao['tipoentidade'] === 1) {
            return $this->evaluateClassificadores($condicao, $cliente);
        }else if ($condicao['tipoentidade'] === 2) {
            return $this->evaluateCampoCustomizado($condicao, $atendimento->getCamposcustomizados());
        }else{
            throw new \Exception('Não há um identificador para o tipo da condição de sla');
        }
    }

    /**
     * Avalia a condição do tipo 'reclame_aqui'
     *
     * @param array $condicao
     * @param array $cliente
     * 
     * @return boolean
     */
    protected function evaluateClassificadores($condicao, $cliente) {
        if(!empty($cliente)) { 
            if(count($cliente['classificadores']) > 0){
            foreach($cliente['classificadores'] as $classificador){
                if( $classificador['classificador'] == $condicao['campo'] ){
                switch ($condicao['operador']) {
                    case 'includes':
                        return strpos(strtolower($classificador['valor']), strtolower($condicao['valor'])) !== false;
                    case 'does_not_include':
                        return strpos(strtolower($classificador['valor']), strtolower($condicao['valor'])) === false;
                    case 'regex':
                        return preg_match(strtolower($condicao['valor']),  strtolower($classificador['valor'])) > 0;
                }
                }
            }
            return false;
            }
        }else {
            return false;
        }
    }

    /**
     * Avalia a regra no campo situação(Aberto/Fechado)
     *
     * @param mixed $regra
     * @param mixed $situacao
     * @return boolean
     */
    protected function evaluateSituacao($regra, $situacao) {

        return ($regra['valor'] == (!is_null($situacao) ? $situacao : 0));
    }

    /**
     * Avalia a regra no campo canalemail
     *
     * @param mixed $regra
     * @param mixed $canalemail
     * @return boolean
     */
    protected function evaluateCanalemail($regra, $canalemail) {
        $enderecoEmail = $this->enderecosemailsRepo->findByEmail($this->tenant, $canalemail);
        return ($regra['valor'] == $enderecoEmail);
    }

    /**
     * Avalia regra para campo string.
     * Verifica se a string passada contem um determinado valor, não contem um
     * determinado valor ou corresponde à expressão regular.
     *
     * @param string $regra
     * @param string $string
     * @return boolean
     */
    protected function evaluateString($regra, $string) {
        switch ($regra['operador']) {
            case 'includes':
                return strpos($string, $regra['valor']) !== false;
            case 'does_not_include':
                return strpos($string, $regra['valor']) === false;
            case 'regex':
                return (preg_match($regra['valor'], $string) > 0);
        }

        return false;
    }

    /**
     *
     * @param type $regra
     * @param array $camposcustomizados
     * @return boolean
     */
    protected function evaluateCampoCustomizado($regra, $camposcustomizados) {

        switch ($regra['operador']) {
            //Opções para campo combobox
            case 'is_set':
                return isset($camposcustomizados[$regra['campo']]);
            case 'is_not_set':
                return !isset($camposcustomizados[$regra['campo']]);
            case 'is_equal':
                return isset($camposcustomizados[$regra['campo']]) && ($camposcustomizados[$regra['campo']] == $regra['valor']);
            case 'is_not_equal':
                return isset($camposcustomizados[$regra['campo']]) && ($camposcustomizados[$regra['campo']] != $regra['valor']);
            //Opções para campo texto
            case 'includes':
            case 'does_not_include':
            case 'regex':
                return $this->evaluateString($regra, $camposcustomizados[$regra['campo']]);
        }
        return false;
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
    
    protected function evaluateStatusSuporte($condicao, $cliente) {
      return $cliente['status_suporte'] == $condicao['valor'];
    }

    protected function action($acao, Solicitacoes &$atendimento) {
        switch (true) {
            case ($acao['acao'] == 'atribuir'):
                $temMudancaAtribuicao = false;

                // Observação importante:
                //
                // O pequeno trecho de código abaixo foi comentado porque quando a regra
                // pedia o redirecionamento de uma fila para outra, ou seja, pedia um redirecionamento "automático" do chamado,
                // mas o tipo do histórico era 4, o redirecionamento não acontecia.
                // Ou seja, mesmo que a regra indicasse que o chamado deveria mudar de fila, ele não mudava.
                //
                // Um problema que pode ser gerado retirando essa verificação abaixo
                // é que, em algum momento, pode acontecer um redirecionamento cíclico.
                //
                // foreach($atendimento->getHistorico() as $h){
                //     if($h->getTipo() == 4 ) {
                //         $temMudancaAtribuicao = true;
                //     }
                // };
                
                if (!$temMudancaAtribuicao) {

                    if (StringUtils::isGuid($acao['valor'])) {
                        // Tenta encontrar a fila com o valor da ação da regra
                        $fila = $this->filasRepository->find($acao['valor'], $atendimento->getTenant());
                    
                        // Caso a fila exista, então, a atribuição é uma fila
                        // Seta o tipo do responsável web para 2
                        if ($fila) {
                            $atendimento->setResponsavelWebTipo(2);
                        }
                    }

                    $atendimento->setResponsavelWeb($acao['valor']);
                }
                break;
            case ($acao['acao'] == 'fechar_atendimento'):
                $atendimento->setSituacao(1);
                break;
            //seta a situação como 2 quando a ação da regra for definir_spam
            case ($acao['acao'] == 'definir_spam'):
                $atendimento->setSituacao(2);
                break;
            case (StringUtils::isGuid($acao['acao'])):
                $cc = $atendimento->getCamposcustomizados();
                $cc[$acao['acao']] = $acao['valor'];
                $atendimento->setCamposcustomizados($cc);
                break;
        }
    }

}
