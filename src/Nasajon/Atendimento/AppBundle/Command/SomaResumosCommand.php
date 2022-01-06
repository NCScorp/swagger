<?php

namespace Nasajon\Atendimento\AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Nasajon\Atendimento\AppBundle\Repository\Admin\ResumosRepository;


class SomaResumosCommand extends Command {  
  
  protected static $defaultName = 'app:soma-resumos';
  
  private $input;
  private $output;
  
  
  const ENTIDADES = [
    'artigos_gostaram',
    'artigos_naogostaram',
    'categorias_filhos',
    'artigos_publicados',
    'artigos_nao_publicados'
  ];
  
  
  
  protected function configure (){
    $this->setDescription('Soma tabela de resumo')
         ->setHelp('Executando esse script, as tabelas de resumos serão somadas agrupando os dados de contagem pelo identificador da entidade');
  }
  
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;
    $this->processaResumos();
  }
  
  public function processaResumos(){
      $resumoRepo = $this->getApplication()->getKernel()->getContainer()->get("nasajon_mda.atendimento_admin_resumos_repository");
      array_map(function($entidade) use ($resumoRepo) {  
          $resumoRepo->somaResumos(47, $entidade);
          $this->output->writeln($entidade);
      }
      , $this::ENTIDADES);
  }
  
}
