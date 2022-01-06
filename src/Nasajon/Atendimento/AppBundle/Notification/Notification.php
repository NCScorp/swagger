<?php

namespace Nasajon\Atendimento\AppBundle\Notification;

class Notification {

    private $tenant;
    private $user;
    private $template;
    private $data;
    private $sender;

    public function getTemplate() {
        return $this->template;
    }

    public function getData() {
        return $this->data;
    }

    public function setTemplate($template) {
        $this->template = $template;
        return $this;
    }

    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    public function getTenant() {
        return $this->tenant;
    }

    public function getUser() {
        return $this->user;
    }

    public function setTenant($tenant) {
        $this->tenant = $tenant;
        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function getSender() {
        return $this->sender;
    }

    public function setSender($sender) {
        $this->sender = $sender;
        return $this;
    }

}
