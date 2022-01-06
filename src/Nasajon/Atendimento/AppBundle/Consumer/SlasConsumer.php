<?php

namespace Nasajon\Atendimento\AppBundle\Consumer;

use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;


class SlasConsumer implements ConsumerInterface {

  
  private $solicitacoesRepo;
  private $slasProducer;
  
  public function __construct(
          SolicitacoesRepository $solicitacoesRepository,
          Producer $slasProducer
    ) {
      $this->solicitacoesRepo = $solicitacoesRepository;
      $this->slasProducer = $slasProducer;
    }
  
  public function execute(AMQPMessage $msg){
    
    $data = json_decode($msg->getBody());
    $atendimentos = $this->solicitacoesRepo->getChamadosAbertosSemSla($data->tenant);
    foreach ($atendimentos as $atendimento) {

      $this->slasProducer->publish(
        json_encode([
            'tenant'=> $atendimento['tenant'], 
            'atendimento'=> $atendimento['atendimento']
        ])
      );
      echo "Chamado ".$atendimento['numeroprotocolo'] . ", tenant ". $atendimento['tenant']. " Enfileirado  \n";    
    }  
    
  }
  
}
