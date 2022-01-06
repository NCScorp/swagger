<?php

namespace Nasajon\Atendimento\AppBundle\EventListener;

use Aws\Sns\SnsClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationsEventListener implements EventSubscriberInterface {

    /**
     *
     * @var SnsClient
     */
    private $sns;

    /**
     *
     * @var string
     */
    private $topic;

    public function __construct(SnsClient $sns, $topic) {
        $this->sns = $sns;
        $this->topic = $topic;
    }

    public static function getSubscribedEvents() {
        return array(
            \Nasajon\Atendimento\AppBundle\Event\Event::FOLLOWUP_CREATE_TYPE => 'handler',
            \Nasajon\Atendimento\AppBundle\Event\Event::ATENDIMENTO_CLIENTE_CREATE_TYPE => 'handler',
            \Nasajon\Atendimento\AppBundle\Event\Event::ATENDIMENTO_ADMIN_ATRIBUICAO_UPDATE_TYPE => 'handler',
        );
    }

    public function handler($event, $eventname) {
        $this->sns->publish([
            "TopicArn" => $this->topic,
            "Message" => json_encode([
                "type" => $eventname,
                "subject" => $event->getSubject(),
                "arguments" => $event->getArguments()
            ])
        ]);
    }

}
