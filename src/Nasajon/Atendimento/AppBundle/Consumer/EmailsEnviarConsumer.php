<?php

namespace Nasajon\Atendimento\AppBundle\Consumer;

use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Nasajon\SDK\Diretorio\DiretorioClient;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

// docker exec -ti atendimento_app_1 app/console rabbitmq:consume emails_enviar
class EmailsEnviarConsumer implements ConsumerInterface {

    public function __construct(DiretorioClient $diretorio, Producer $emailsProcessar)
    {
        $this->diretorio = $diretorio;
        $this->emailsProcessar = $emailsProcessar;
    }
    
    public function execute(AMQPMessage $msg) {

        $data = json_decode($msg->getBody(), true);

        if (!count($data['to']) || (count($data['to']) && !$data['to'][0]) || !StringUtils::isEmail($data['to'][0])) {
            return;
        }

        $this->diretorio->enviaEmail([
            'to' => $data['to'],
            'split' => true,
            'from' => $data['from'],
            'codigo' => $data['codigo'],
            'tags' => $data['tags'],
            'tenant' => $data['tenant']
        ]);

    }
}