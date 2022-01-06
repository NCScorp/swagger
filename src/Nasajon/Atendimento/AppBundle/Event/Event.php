<?php

namespace Nasajon\Atendimento\AppBundle\Event;

use JMS\Serializer\Annotation as JMS;

class Event {

    const FOLLOWUP_CREATE_TYPE = 'followup.create';
    const ATENDIMENTO_CLIENTE_CREATE_TYPE = 'atendimento_cliente.create';
    const ATENDIMENTO_ADMIN_ATRIBUICAO_UPDATE_TYPE = 'atendimento_admin_atribuicao.update';
    const ATENDIMENTO_ADMIN_CREATE_TYPE = 'atendimento_admin.create';

    /**
     *
     * @JMS\Type("string")
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("string")
     * @var string
     */
    private $subject;

    /**
     * @JMS\Type("array")
     * @var array
     */
    private $arguments;

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function getArguments() {
        return $this->arguments;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function setArguments($arguments) {
        $this->arguments = $arguments;
        return $this;
    }

}
