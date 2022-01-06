<?php

namespace Nasajon\Atendimento\AppBundle\Event;

use Exception;
use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Event\Processor\Processor;
use Nasajon\Atendimento\AppBundle\Notification\Notification;
use Nasajon\Atendimento\AppBundle\Notification\NotificationDispatcher;

class EventReceiver {

    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;

    /**
     *
     * @var NotificationDispatcher
     */
    private $dispatcher;

    public function __construct(NotificationDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     *
     * @param Event $event
     * @throws Exception
     * @return Processor
     */
    private function getProcessor(Event $event) {
        switch ($event->getType()) {
            case \Nasajon\Atendimento\AppBundle\Event\Event::FOLLOWUP_CREATE_TYPE:
                return $this->container->get('nasajon_atendimento_app_bundle.event_processor.followup');
            case \Nasajon\Atendimento\AppBundle\Event\Event::ATENDIMENTO_CLIENTE_CREATE_TYPE:
                return $this->container->get('nasajon_atendimento_app_bundle.event_processor.atendimento_cliente');
            case \Nasajon\Atendimento\AppBundle\Event\Event::ATENDIMENTO_ADMIN_ATRIBUICAO_UPDATE_TYPE:
                return $this->container->get('nasajon_atendimento_app_bundle.event_processor.atendimento_admin');
            case \Nasajon\Atendimento\AppBundle\Event\Event::ATENDIMENTO_ADMIN_CREATE_TYPE:
                return $this->container->get('nasajon_atendimento_app_bundle.event_processor.atendimento_admin');
            default:
                return null;
        }
    }

    /**
     *
     * @param Event $event
     * @return Notification
     * @throws Exception
     */
    public function proccess(Event $event) {

        $processor = $this->getProcessor($event);

        if (is_null($processor)) {
            return;
        }

        $notification = $processor->process($event);

        $this->dispatcher->dispatch($notification);

        return $notification;
    }

}
