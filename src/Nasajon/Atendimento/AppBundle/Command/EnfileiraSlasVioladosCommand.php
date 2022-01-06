<?php

namespace Nasajon\Atendimento\AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnfileiraSlasVioladosCommand extends Command {  
  
  protected static $defaultName = 'app:enfileira-chamados-slas-violados';
  
  private $input;
  private $output;
  private $chamadosSlasVioladosProducer;
  private $chamadosRepo;
  
  
  protected function configure (){

    $this->setDescription('Soma tabela de resumo')
         ->setHelp('Executando esse script, as tabelas de resumos serão somadas agrupando os dados de contagem pelo identificador da entidade');
  }
  
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->chamadosRepo = $this->getApplication()->getKernel()->getContainer()->get("nasajon_mda.atendimento_admin_solicitacoes_repository");
    $this->chamadosSlasVioladosProducer = $this->getApplication()->getKernel()->getContainer()->get("old_sound_rabbit_mq.chamados_slas_violados_producer");
    
    $this->input = $input;
    $this->output = $output;
    $chamados  = $this->chamadosRepo->getChamadosSlasViolados();

    foreach($chamados as $chamado){
      $this->chamadosSlasVioladosProducer->publish(
        json_encode([
            'tenant'=> $chamado['tenant'], 
            'atendimento'=> $chamado['atendimento']
        ])
      );
    }
  }
  
}
