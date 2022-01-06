<?php

namespace Nasajon\Atendimento\AppBundle\Consumer;

use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ChamadosSlasRegistraViolacaoSlaConsumer implements ConsumerInterface {
    
    private $chamadosRepo;

    public function __construct(SolicitacoesRepository $chamadosRepo) {
        $this->chamadosRepo = $chamadosRepo;
    }

    public function execute(AMQPMessage $msg) {
        $data = json_decode($msg->getBody());

        $entityArr = $this->chamadosRepo->find($data->atendimento, $data->tenant);
        $entity = $this->chamadosRepo->fillEntity($entityArr);
        $result = $this->chamadosRepo->AtendimentoRegistraUltimaViolacaoSla($data->tenant, $entity);

    }
}