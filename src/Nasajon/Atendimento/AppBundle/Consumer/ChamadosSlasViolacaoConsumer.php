<?php

namespace Nasajon\Atendimento\AppBundle\Consumer;

use DateInterval;
use DateTime;
use Exception;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\Atendimento\AppBundle\Service\HoraUtilService;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Slas;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ChamadosSlasViolacaoConsumer implements ConsumerInterface {

  /**
   * @var SolicitacoesRepository 
   */
  private $solicitacoesRepo;
  
  /**
   * @var horautilservice 
   */
  private $horautilservice;

  private $logger;
  
  /**
   * 
   * @param SolicitacoesRepository $solicitacoesRepository
   */
  public function __construct(SolicitacoesRepository $solicitacoesRepository, HoraUtilService $horautilservice, LoggerInterface $logger) {
    $this->solicitacoesRepo = $solicitacoesRepository;
    $this->horautilservice = $horautilservice;

    $this->logger = $logger;
  }

  public function execute(AMQPMessage $msg) {

    $data = json_decode($msg->getBody());

    if($data->tenant == 49){
      return;
    }

    try {

      $atendimento = null;

      if (isset($data->buscar) && $data->buscar) {
        
        $data2 = $this->solicitacoesRepo->findQuery($data->atendimento, $data->tenant);
        
        $atendimento = new Solicitacoes();
        $atendimento->setTenant($data->tenant);
        $atendimento->setAtendimento($data2['atendimento']);
        $atendimento->setDataUltimaResposta($data2['data_ultima_resposta']);
        $atendimento->setCreatedAt($data2['created_at']);
        $atendimento->setQtdRespostas($data2['qtd_respostas']);
        
        $sla = new Slas();
        $sla->setSla($data2['sla']);
        $sla->setTempoPrimeiroAtivo($data2['tempo_primeiro_ativo']);
        $sla->setTempoPrimeiro($data2['tempo_primeiro']);
        $sla->setTempoProximoAtivo($data2['tempo_proximo_ativo']);
        $sla->setTempoProximo($data2['tempo_proximo']);
        $sla->setTempoResolucaoAtivo($data2['tempo_resolucao_ativo']);
        $sla->setTempoResolucao($data2['tempo_resolucao']);
        $atendimento->setSla($sla);
      } else {
        $atendimento = new Solicitacoes();
        $atendimento->setTenant($data->tenant);
        $atendimento->setAtendimento($data->atendimento);
        $atendimento->setDataUltimaResposta($data->data_ultima_resposta);
        $atendimento->setCreatedAt($data->datacriacao);
        $atendimento->setQtdRespostas($data->qtd_respostas);

        $sla = new Slas();
        $sla->setSla($data->sla);
        $sla->setTempoPrimeiroAtivo($data->tempo_primeiro_ativo);
        $sla->setTempoPrimeiro($data->tempo_primeiro);
        $sla->setTempoProximoAtivo($data->tempo_proximo_ativo);
        $sla->setTempoProximo($data->tempo_proximo);
        $sla->setTempoResolucaoAtivo($data->tempo_resolucao_ativo);
        $sla->setTempoResolucao($data->tempo_resolucao);

        $atendimento->setSla($sla);
      }
      
      $atendimento->setProximaviolacaosla($this->calculaProximaViolacao($atendimento));
      $this->solicitacoesRepo->atendimentoAlterarViolacaoSla($atendimento->getTenant(), $atendimento);

    } catch (Exception $e) {

      $this->logger->error('CHAMADOS - VIOLAÇÃO DE SLAS', 
        [
          'Tenant' => $data->tenant ? $data->tenant : 'Não consta na mensagem',
          'Atendimento' => $data->atendimento ? $data->atendimento : 'Não consta na mensagem',
          'SLAS' => $data->sla ? $data->sla :  'Não consta na mensagem',
          'Erro' => $e->getMessage()
        ]);
      
      throw new Exception($e);
    }
    
    echo $atendimento->getAtendimento() . " Viola SLA em " . $atendimento->getProximaviolacaosla() . "\n";
  }
  
  /**
   * 
   * @param Solicitacoes $atendimento
   * @return string
   */
  private function calculaProximaViolacao($atendimento) {
    
    if ($atendimento->getSla()->getTempoPrimeiroAtivo() && $atendimento->getQtdRespostas() == 0) {
      $proximaviolacaosla = $this->horautilservice->proximaHoraUtil($atendimento->getTenant(), (new DateTime($atendimento->getDataUltimaResposta())), $this->horautilservice->timeToSeconds( $atendimento->getSla()->getTempoPrimeiro() ));      
    } elseif ($atendimento->getSla()->getTempoProximoAtivo() && $atendimento->getQtdRespostas() > 0) {
      $proximaviolacaosla = $this->horautilservice->proximaHoraUtil($atendimento->getTenant(), (new DateTime($atendimento->getDataUltimaResposta())), $this->horautilservice->timeToSeconds( $atendimento->getSla()->getTempoProximo() ));
    } else {
      $proximaviolacaosla = null;
    }
    
    if ($atendimento->getSla()->getTempoResolucaoAtivo()) {
      $dataviolacaoresolucao = $this->horautilservice->proximaHoraUtil($atendimento->getTenant(), (new DateTime($atendimento->getCreatedAt())), $this->horautilservice->timeToSeconds( $atendimento->getSla()->getTempoResolucao() ));

      if (!$proximaviolacaosla || $dataviolacaoresolucao < $proximaviolacaosla) {
        $proximaviolacaosla = $dataviolacaoresolucao;
      }
    }
    return (!$proximaviolacaosla) ? null : $proximaviolacaosla->format('Y-m-d H:i:s T');
    
  }

}