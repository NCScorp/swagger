<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Event\Notification;

interface Processor {

    const ROUTE_ADMIN = 'index';
    const ROUTE_CLIENTE = 'atendimento_cliente_index';

    /**
     *
     * @param Event $event
     * @return Notification
     */
    public function process(Event $event);

    public function notificationBuilder(\Nasajon\ModelBundle\Entity\Ns\Tenants $tenant, $users, $template, $atendimento, $route, $extraData = []);
}
