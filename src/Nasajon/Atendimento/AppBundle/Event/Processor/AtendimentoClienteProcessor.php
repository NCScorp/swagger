<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\UsuariosDisponibilidadesRepository;
use Nasajon\Atendimento\AppBundle\Repository\Ns\ClientesRepository;
use Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosfilasobservadoresRepository;
use Nasajon\ModelBundle\Entity\Ns\Tenants;

class AtendimentoClienteProcessor extends AbstractProcessor implements Processor {

    const TEMPLATE = "atendimento_cliente_novo";
    const TEMPLATE_AUTOREPLY = "atendimento_cliente_novo_autoreply";
    const TEMPLATE_OBSERVADOR_FILA = "atendimento_cliente_novo_observador_fila";

    /**
     *
     * @var SolicitacoesRepository
     */
    private $atendimentosRepository;

    /**
     *
     * @var UsuariosDisponibilidadesRepository
     */
    private $usuariosDisponibilidadesRepository;

    /**
     *
     * @var AtendimentosfilasobservadoresRepository
     */
    private $observadoresfilasRepository;

    /**
     *
     * @var type
     */
    private $acoesRepository;

    /**
     *
     * @var ClientesRepository
     */
    private $clienteRepository;

    /**
     *
     * @var \Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository
     */
    private $equipeRepository;

    /**
     *
     * @var EquipeClienteFilterService
     */
    private $equipeFilter;

    /**
     *
     * @var \Nasajon\ModelBundle\Services\ConfiguracoesService;
     */
    private $confService;

    /**
     *
     * @var array
     */
    private $cacheEquipe = [];

    public function __construct(SolicitacoesRepository $atendimentosRepository, AtendimentosfilasobservadoresRepository $observadoresfilasRepository, UsuariosDisponibilidadesRepository $usuariosDisponibilidadesRepository, $acoesRepository, $clienteRepository, $equipeRepository, $equipeFilter, ConfiguracoesService $confService, $subdomain, $em, $router) {
        parent::__construct($subdomain, $em, $router);
        $this->atendimentosRepository = $atendimentosRepository;
        $this->observadoresfilasRepository = $observadoresfilasRepository;
        $this->acoesRepository = $acoesRepository;
        $this->clienteRepository = $clienteRepository;
        $this->equipeRepository = $equipeRepository;
        $this->equipeFilter = $equipeFilter;
        $this->usuariosDisponibilidadesRepository = $usuariosDisponibilidadesRepository;
        $this->confService = $confService;
    }

    /**
     * @inheritDoc
     */
    public function process(Event $event) {
        $notifications = [];

        // Verifico se existe autoreply e fila para evitar executar queries desnecessárias
        if ((isset($event->getArguments()['autoreply']) && !is_null($event->getArguments()['autoreply'])) || (isset($event->getArguments()['fila']) && !is_null($event->getArguments()['fila']))) {

            $atendimento = $this->atendimentosRepository->find($event->getSubject(), $event->getArguments()['tenant']);
            if (!is_null($atendimento['cliente'])) {
                $atendimento['cliente'] = $this->clienteRepository->find($atendimento['cliente']['cliente'], $atendimento['tenant']);
            }


            $tenant = $this->getEm()->getRepository("Nasajon\ModelBundle\Entity\Ns\Tenants")->find($atendimento['tenant']);

            if (isset($event->getArguments()['autoreply']) && !is_null($event->getArguments()['autoreply'])) {
                $notifications[] = $this->processAutoreply($tenant, $atendimento, $event->getArguments()['autoreply']);
            }

            if (isset($event->getArguments()['fila']) && !is_null($event->getArguments()['fila'])) {
                $notifications[] = $this->processObservadorFila($tenant, $atendimento, $event->getArguments()['fila']);
            }
        }
        return $notifications;
    }

    /**
     * @inheritDoc
     */
    public function processAutoreply(Tenants $tenant, $atendimento, $acaoId) {
        $acao = $this->acoesRepository->find($acaoId, $tenant->getId(), null);
        $notification_data = [
            "mensagem" => $acao['valor']
        ];

        return $this->notificationBuilder($tenant, $atendimento['email'], self::TEMPLATE_AUTOREPLY, $atendimento, Processor::ROUTE_CLIENTE, $notification_data);
    }

    /**
     * @inheritDoc
     */
    public function processObservadorFila(Tenants $tenant, $atendimento, $fila) {
        $observadores = $this->observadoresfilasRepository->findAllWithEquipe($tenant->getTenant(), $fila);

        $equipeRepo = $this->equipeRepository;
        $equipeFilter = $this->equipeFilter;
        $cacheEquipe = $this->cacheEquipe;
        $observadores = array_filter($observadores, function($observador) use($equipeRepo, $equipeFilter, $atendimento, $cacheEquipe) {
            switch ($observador['todosclientes']) {
                case '1':
                    return true;
                case '0':
                    if (!isset($cacheEquipe[$atendimento['cliente']['cliente']])) {
                        $cacheEquipe[$atendimento['cliente']['cliente']] = $equipeFilter->evaluate($equipeRepo->find($observador['equipe'], $atendimento['tenant']), $atendimento['cliente']);
                    }
                    return $cacheEquipe[$atendimento['cliente']['cliente']];
                default:
                    return false;
            }
        });

        $observadores = array_map(function($observador) {
            return $observador['usuario'];
        }, $observadores);

        // Configurações do Módulo de Disponibilidade de Usuários
        $mudNotificacoesUsuario = $this->confService->get($tenant->getTenant(), 'ATENDIMENTO', 'USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS');
        if (is_null($mudNotificacoesUsuario)) {
            $usuariosIndisponiveis = $this->usuariosDisponibilidadesRepository->listaIndisponiveis($tenant->getTenant());
            $observadores = array_diff($observadores, $usuariosIndisponiveis);
        }

        return $this->notificationBuilder($tenant, $observadores, self::TEMPLATE_OBSERVADOR_FILA, $atendimento, Processor::ROUTE_ADMIN);
    }

}
