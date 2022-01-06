<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\ModelBundle\Entity\Ns\Tenants;

class AtendimentoAdminProcessor extends AbstractProcessor implements Processor {

    const TEMPLATE_ATRIBUIDO = "atendimento_admin_atribuido";
    const TEMPLATE_CRIADO = "atendimento_admin_novo";

    /**
     *
     * @var SolicitacoesRepository
     */
    private $atendimentosRepository;
    
    /**
     *
     * @var type
     */
    private $acoesRepository; 

    public function __construct(SolicitacoesRepository $atendimentosRepository, $acoesRepository, $subdomain, $em, $router) {
        parent::__construct($subdomain, $em, $router);
        $this->atendimentosRepository = $atendimentosRepository;
        $this->acoesRepository = $acoesRepository;
    }

    /**
     * @inheritDoc
     */
    public function process(Event $event) {
        $notifications = [];

        $atendimento = $this->atendimentosRepository->find($event->getSubject(), $event->getArguments()['tenant']);
        $ultimohistorico = (array_key_exists('historico', $atendimento) && count($atendimento['historico']) > 0) ? $atendimento['historico'][0] : null;
        $tenant = $this->getEm()->getRepository("Nasajon\ModelBundle\Entity\Ns\Tenants")->find($atendimento['tenant']);
        
        if ($ultimohistorico && $ultimohistorico['tipo'] == 4) {
            $notifications[] = $this->processAtribuicao($tenant, $atendimento, $ultimohistorico);
        }
        
        if ($ultimohistorico && $ultimohistorico['tipo'] == 6){
            $notifications[] = $this->processCriacao($tenant, $atendimento, $ultimohistorico);
        }
        
        return $notifications;
    }

    /**
     * @inheritDoc
     */
    public function processAtribuicao(Tenants $tenant, $atendimento, $ultimohistorico) {
        return $this->notificationBuilder($tenant, $ultimohistorico['valornovo']['responsavel'], self::TEMPLATE_ATRIBUIDO, $atendimento, Processor::ROUTE_ADMIN);
    }
    
    /**
     * @inheritDoc
     */
    public function processCriacao(Tenants $tenant, $atendimento){
        return $this->notificationBuilder($tenant, $atendimento['email'], self::TEMPLATE_CRIADO, $atendimento, Processor::ROUTE_ADMIN);
    }

}
