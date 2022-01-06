<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Repository\Admin\SlasRepository;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Slas;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\MDABundle\Repository\Atendimento\Admin\EnderecosemailsRepository;
use Nasajon\MDABundle\Repository\Ns\ClientesRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class RegrasSlasService {
  
    private $regrasSlaRepo;
    private $enderecosemailsRepo;
    private $solictacoesRepo;
    private $slasViolacaoProducer;
    private $tenant;
    
    public function __construct(
            SlasRepository $regrasSlaRepo, 
            EnderecosemailsRepository $enderecosemailsRepo, 
            ClientesRepository $clientesRepo,
            SolicitacoesRepository $solictacoesRepo,
            Producer $slasViolacaoProducer
            
            ) {
        $this->regrasSlaRepo = $regrasSlaRepo;
        $this->enderecosemailsRepo = $enderecosemailsRepo;
        $this->clientesRepo = $clientesRepo;
        $this->solictacoesRepo = $solictacoesRepo;
        $this->slasViolacaoProducer = $slasViolacaoProducer;
    }
    
    
    /**
     *
     * @param string $tenant
     * @param Solicitacoes $atendimento
     * @param Slas $sla
     * @return mixed
     */
    public function run($tenant, Solicitacoes $atendimento) {
        $this->tenant = $tenant;
        $logged_user = ['nome' => 'RegrasSlaService'];
        
        $clienteObj = null;
        if ($atendimento->getCliente()) {
            $clienteObj = $this->clientesRepo->find($atendimento->getCliente()->getCliente(), $tenant);
            $atendimento->setCliente($clienteObj);
        }
        
        $regras = $this->regrasSlaRepo->findAll($tenant);
        
        
        foreach ($regras as $regra) {
            $regraObj = $this->regrasSlaRepo->find($regra['sla'], $tenant);
            if (
                    $this->evaluateCondicoesIn($regraObj['condicoes_in'], $atendimento, $clienteObj) && 
                    $this->evaluateCondicoesEx($regraObj['condicoes_ex'], $atendimento, $clienteObj)
            ) {
                $atendimento->setSla($regra['sla']);
                $this->solictacoesRepo->atendimentoAlterarSla($tenant, $logged_user, $atendimento);
                $this->slasViolacaoProducer->publish(
                        json_encode([
                      'tenant'=> $tenant, 
                      'atendimento'=> $atendimento->getAtendimento(),
                      'qtd_respostas' => $atendimento->getQtdRespostas(),
                      'data_ultima_resposta' => $atendimento->getDataUltimaResposta(),
                      'datacriacao' => $atendimento->getCreatedAt(),
                      'sla' => $regra['sla'], 
                      'tempo_primeiro_ativo' => $regra['tempo_primeiro_ativo'],
                      'tempo_primeiro' => $regra['tempo_primeiro'],
                      'tempo_proximo_ativo' => $regra['tempo_primeiro_ativo'],
                      'tempo_proximo' => $regra['tempo_primeiro'],
                      'tempo_resolucao_ativo' => $regra['tempo_resolucao_ativo'],
                      'tempo_resolucao' => $regra['tempo_resolucao'],
                      'buscar' => false
                  ])
                );
                
                echo " Atendimento #".$atendimento->getNumeroprotocolo()." SLA ".$regra['nome']."\n"; 
                break;
            }
        }
    }
    
    
    /**
     * Valida se todas as condições são satisfeitas
     *
     * @param array $condicoes
     * @return boolean
     */
    protected function evaluateCondicoesIn($condicoes, $atendimento, $cliente) {
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
    protected function evaluateCondicoesEx($condicoes, $atendimento, $cliente) {
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
                return $this->evaluateString($condicao, strip_tags($atendimento->getSintoma()));
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
            case ($condicao['campo'] == 'datacriacao'):
                return $this->evaluateDataCriacao($condicao, $atendimento->getCreatedAt());
            case (StringUtils::isGuid($condicao['campo'])):
                return $this->evaluateCampoGuid($condicao, $atendimento);
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
    protected function evaluateCampoGuid($condicao, $atendimento) {
        if ($condicao['tipoentidade'] === 1) {
            return $this->evaluateClassificadores($condicao, $atendimento->getCliente());
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
      if(count($cliente['classificadores']) > 0){
        foreach($cliente['classificadores'] as $classificadores){
          if( $classificadores['classificador'] == $condicao['campo'] ){
            switch ($condicao['operador']) {
                case 'includes':
                    return strpos(strtolower($classificadores['valor']), strtolower($condicao['valor'])) !== false;
                case 'does_not_include':
                    return strpos(strtolower($classificadores['valor']), strtolower($condicao['valor'])) === false;
                case 'regex':
                    return preg_match(strtolower($condicao['valor']),  strtolower($classificadores['valor'])) > 0;
            }
          }
        }
        return false;
      }
    }
    
    /**
     * Avalia a regra no campo Data Criação
     *
     * @param mixed $regra
     * @param mixed $dataCriacao
     * @return boolean
     */
    protected function evaluateDataCriacao($regra, $dataCriacao) {
        switch ($regra['operador']) {
            case 'is_before':
                return strtotime($regra['valor']) <= strtotime($dataCriacao) ;
              case 'is_after':
                return strtotime($regra['valor']) >= strtotime($dataCriacao) ;
        }
        return false;
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

        // Valida se o campo da regra está contido nos campos customizados.
        // Isso previne a exception de Undefined Index, caso o campo customizado tenha sido excluído.
        if (!isset($camposcustomizados[$regra['campo']])) {
            return false;
        }

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
                $atendimento->setResponsavelWeb($acao['valor']);
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
