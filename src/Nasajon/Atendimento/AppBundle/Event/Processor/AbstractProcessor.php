<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use Nasajon\Atendimento\AppBundle\Notification\Notification;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\ModelBundle\Entity\Ns\Tenants;
use Doctrine\ORM\EntityManager;

abstract class AbstractProcessor {

    private $subdomain;

    /**
     *
     * @var EntityManager
     */
    private $em;
    private $router;

    public function __construct($subdomain, EntityManager $em, $router) {
        $this->subdomain = $subdomain;
        $this->em = $em;
        $this->router = $router;
    }

    public function notificationBuilder(Tenants $tenant, $users, $template, $atendimento, $route, $extraData = []) {

        $notification = new Notification();
        $notification->setTemplate($template);
        $notification->setTenant($tenant);
        $notification->setSender([
            "nome" => $tenant->getNome(),
            "email" => $tenant->getCodigo() . "@" . $this->subdomain
        ]);
        $notification->setUser($users);

        $data = [
            "main_url" => $this->router->generate($route, ['tenant' => $tenant->getCodigo()], UrlGeneratorInterface::ABSOLUTE_URL),
            "protocolo" => $atendimento['numeroprotocolo'],
            "resumo" => StringUtils::removeln($atendimento['resumo']),
            "atendimento" => $atendimento
        ];
        $notification->setData(array_merge($data, $extraData));

        return $notification;
    }

    public function getSubdomain() {
        return $this->subdomain;
    }

    public function getEm() {
        return $this->em;
    }

    public function getRouter() {
        return $this->router;
    }

    public function setSubdomain($subdomain) {
        $this->subdomain = $subdomain;
        return $this;
    }

    public function setEm(EntityManager $em) {
        $this->em = $em;
        return $this;
    }

    public function setRouter($router) {
        $this->router = $router;
        return $this;
    }

}
