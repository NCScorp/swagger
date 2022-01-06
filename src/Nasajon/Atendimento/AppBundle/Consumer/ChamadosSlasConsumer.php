<?php

namespace Nasajon\Atendimento\AppBundle\Consumer;

use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\Atendimento\AppBundle\Service\RegrasSlasService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ChamadosSlasConsumer implements ConsumerInterface {

  /**
   *
   * @var SolicitacoesRepository 
   */
  private $solicitacoesRepo;

  /**
   *
   * @var RegrasSlasService 
   */
  private $regrasSlasService;

  /**
   * 
   * @param SolicitacoesRepository $solicitacoesRepository
   * @param RegrasSlasService $regrasSlaService
   */
  public function __construct(
  SolicitacoesRepository $solicitacoesRepository, RegrasSlasService $regrasSlaService
  ) {
    $this->solicitacoesRepo = $solicitacoesRepository;
    $this->regrasSlasService = $regrasSlaService;
  }

  public function execute(AMQPMessage $msg) {
    $data = json_decode($msg->getBody());
    $entity = $this->solicitacoesRepo->find($data->atendimento, $data->tenant);
    $atendimento = $this->solicitacoesRepo->fillEntity($entity);
    $this->regrasSlasService->run($data->tenant, $atendimento);
  }

}
