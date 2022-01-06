<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Repository\Admin\FollowupsRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosobservadoresRepository;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Nasajon\Atendimento\AppBundle\Service\UsuariosDisponibilidadesService;

  class FollowupProcessor extends AbstractProcessor implements Processor {

    const TEMPLATE_PARA_ADMIN = "atendimento_resposta_cliente";
    const TEMPLATE_PARA_CLIENTE = "atendimento_resposta_admin";

    /**
     *
     * @var FollowupsRepository
     */
    private $followups;

    /**
     *
     * @var AtendimentosobservadoresRepository
     */
    private $obsRepo;

    /**
     *
     * @var SolicitacoesRepository
     */
    private $atendimentos;

    /**
     *
     * @var \Nasajon\Atendimento\AppBundle\Repository\Ns\ClientesRepository
     */
    private $clienteRepository;

    /**
     *
     * @var array
     */
    private $cacheEquipe = [];

    /**
     *
     * @var \Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository
     */
    private $equipeRepository;

    /**
     *
     * @var \Nasajon\ModelBundle\Services\ConfiguracoesService;
     */
    private $confService;

    /**
     *
     * @var \Nasajon\Atendimento\AppBundle\Service\UsuariosDisponibilidadesService;
     */
    private $usuariosDisponibilidadesService;

    /**
     *
     * @var \Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService
     */
    private $equipeFilter;
    
    /**
     *
     * @var string 
     */
    
    private $timezone;
    
    /**
     *
     * @var array 
     */
    private $diretorio = [];

    public function __construct(FollowupsRepository $followups, SolicitacoesRepository $atendimentos, AtendimentosobservadoresRepository $obsRepo, $clienteRepository, $equipeRepository, ConfiguracoesService $confService, UsuariosDisponibilidadesService $usuariosDisponibilidadesService, $equipeFilter, $subdomain, EntityManager $em, $router, $diretorio = null) {
        parent::__construct($subdomain, $em, $router);
        $this->followups = $followups;
        $this->atendimentos = $atendimentos;
        $this->obsRepo = $obsRepo;
        $this->clienteRepository = $clienteRepository;
        $this->equipeRepository = $equipeRepository;
        $this->equipeFilter = $equipeFilter;
        $this->confService = $confService;
        $this->usuariosDisponibilidadesService = $usuariosDisponibilidadesService;
        $this->diretorio = $diretorio;
    }

    /**
     * Busca todos os usuários do cliente que deverão ser avisados sobre modificações no atendimento
     *
     * @param array $atendimento
     * @param string $createdBy
     * @return array
     */
    public function getUsuariosCliente($atendimento, $createdBy) {

        $usuariosCliente = [];

        //Verifica se o cliente tem usuários que precisam ser notificados
        if (!empty($atendimento['cliente']['cliente'])) {
            if (!empty($atendimento['cliente']['usuarios'])) {
                foreach ($atendimento['cliente']['usuarios'] as $usuario) {
                    if ($usuario['notificar'] == true) {
                        array_push($usuariosCliente, $usuario['conta']);
                    }
                }
            }
        }

        return $usuariosCliente;
    }

    private function validaSubscribers($emails, $atendimento, $createdBy) {
        //Altera o formato do array para que fique mais enxuto
        $subscribers = array_map(function($email) {
            return strtolower($email['usuario']);
        }, $emails);
        
        $responsavel_web = $this->acoesUsuarioIndisponivel($atendimento);

        if (!empty($responsavel_web)) {
            array_push($subscribers, $responsavel_web);
        }

        // Garante que os usuários não se repitam na listagem
        $uniqueSubscribers = array_unique($subscribers);

        // Remove da lista o criador do follow para que o mesmo não seja notificado de algo que ele mesmo fez
        // Uso o array_values para reindexar o array para que não fiquem furos(ex: 0,1,2,4,6)
        return array_values(array_filter($uniqueSubscribers, function($usuario) use($createdBy) {
                    return ($usuario !== $createdBy);
                }));
    }

    /**
     * Busca todos os usuários que deverão ser avisados sobre modificações no atendimento
     *
     * @param array $atendimento
     * @param string $createdBy
     * @return array
     */
    public function getSubscribers($atendimento, $createdBy) {
        
        $confNotificacaoHabilitado = $this->confService->get($atendimento['tenant'], 'ATENDIMENTO', 'USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS');
        //Busca o usuarios que estão observando o atendimento
        $observadores = $this->obsRepo->findAllWithEquipe($atendimento['tenant'], $atendimento['atendimento'], $confNotificacaoHabilitado);

        $equipeRepo = $this->equipeRepository;
        $equipeFilter = $this->equipeFilter;
        $cacheEquipe = $this->cacheEquipe;

        $emails = array_filter($observadores, function($observador) use($equipeRepo, $equipeFilter, $atendimento, $cacheEquipe) {
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

        return $this->validaSubscribers($emails, $atendimento, $createdBy);
    }
    
    public function verificaProvisao($email, $tenantId){
      $profile = $this->diretorio ? $this->diretorio->getProfile($email) : false;
      if($profile){
        foreach ($profile['tenants'] as $tenant) {
          if($tenant['id'] === $tenantId){
            foreach($tenant['sistemas'] as $sistema){
              if($sistema['codigo'] === 'atendimento'){
                foreach($sistema['funcoes'] as $funcao){
                  if($funcao['codigo'] === 'admin'){
                    return true;
                  }
                }
              }
            }
          }
        }
        return false;
      }else{
        return false;
      }
    }

    public function acoesUsuarioIndisponivel($atendimento) {


        // Se o responsável pelo atendimento for um usuario
        if ($atendimento['responsavel_web_tipo'] == 1) {
            $usuarioEstaIndisponivel = $this->usuariosDisponibilidadesService->verificarSeUsuarioIndisponivel($atendimento['responsavel_web'], $atendimento['tenant']);
            $temProvisao = $this->verificaProvisao($atendimento['responsavel_web'], $atendimento['tenant']);
            
            if ($usuarioEstaIndisponivel && !$temProvisao) {
                $acao_configurada = $this->confService->get($atendimento['tenant'], 'ATENDIMENTO', 'USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA');
                $this->usuariosDisponibilidadesService->atribuicaoDosChamadosDeUsuarioIndisponibilizado($acao_configurada, $atendimento);
            }
        }


        // Caso continue sendo um usuário, adiciona ele na lista de subscribers
        if ($atendimento['responsavel_web_tipo'] == 1) {
            return $atendimento['responsavel_web'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function process(Event $event) {
       
        $follow = $this->followups->find($event->getSubject(), $event->getArguments()['tenant'], 0);
        $atendimento = $this->atendimentos->find($follow['atendimento'], $follow['tenant']);
        $this->timezone = $this->confService->get($atendimento['tenant'], 'ATENDIMENTO', 'TIMEZONE');
        $createdBy = $follow['created_by']['email'];
        $atendimento['created_at'] = new DateTime($atendimento['created_at']);
        if($this->timezone){
          $atendimento['created_at']->setTimezone(new DateTimeZone($this->timezone));
          $atendimento['created_at'] = $atendimento['created_at']->format('Y-m-d H:i:s');
        }
        $tenant = $this->getEm()->getRepository("Nasajon\ModelBundle\Entity\Ns\Tenants")->find($atendimento['tenant']);
        
        if (!is_null($atendimento['cliente'])) {
            $atendimento['cliente'] = $this->clienteRepository->find($atendimento['cliente']['cliente'], $atendimento['tenant']);
        }


        // Verifica se URL_AVALIACAO_RESPOSTA_CHAMADO existe e gera o link...
        // caso a resposta tenha sido de um atendente
        $url_avaliacao = $this->confService->get($atendimento['tenant'], 'ATENDIMENTO', 'URL_AVALIACAO_RESPOSTA_CHAMADO');
        if (!empty($url_avaliacao)) {
            for ($i = 0; $i < count($atendimento['followups']); $i++) {
                if (!$atendimento['followups'][$i]['criador']) {
                    $atendimento['followups'][$i] = $this->montaLinkAvaliacao($atendimento, $atendimento['followups'][$i], $url_avaliacao);
                }
            }
        }

        $atendimento['followups'] = array_filter($atendimento['followups'], function($followup) use($follow) {
            return (date($followup['created_at']) <= date($follow['created_at']));
        });

        $atendimento['followups'] = array_map(function($followup) {
            $followup['created_at'] = new DateTime($followup['created_at']);
            if($this->timezone){
              $followup['created_at']->setTimezone(new DateTimeZone($this->timezone));
              $followup['created_at'] = $followup['created_at']->format('Y-m-d H:i:s');
            }
            return $followup;
        }, $atendimento['followups']);


        $notifications = [];
        
        $subscribers = $this->getSubscribers($atendimento, $createdBy);
        
        if (count($subscribers) > 0) {
            $notifications[] = $this->notificationBuilder($tenant, $subscribers, self::TEMPLATE_PARA_ADMIN, $atendimento, Processor::ROUTE_ADMIN);
        }

        $usuariosCliente = array_merge((!empty($atendimento['email']) ? [$atendimento['email']] : []), $this->getUsuariosCliente($atendimento, $createdBy));

        // Criado pelo admin que não seja um comentário e que tenha de contato e atendimento visivel para cliente
        if ((!$follow['criador']) && (!empty($usuariosCliente) && $follow['tipo'] !== 1 && $atendimento['visivelparacliente'])) {

            $atendimento['followups'] = array_filter($atendimento['followups'], function($followup) {
                return ($followup['tipo'] == 0);
            });
            $notifications[] = $this->notificationBuilder($tenant, $usuariosCliente, self::TEMPLATE_PARA_CLIENTE, $atendimento, Processor::ROUTE_CLIENTE);
        }

        return $notifications;
    }

    public function montaLinkAvaliacao($atendimento, $followup, $url_avaliacao) {
        $followup['url_avaliacao'] = $url_avaliacao;
        $followup['url_avaliacao'] = str_replace("[[chamado]]", $atendimento['numeroprotocolo'], $followup['url_avaliacao']);
        if (!is_null($atendimento['cliente'])) {
            $followup['url_avaliacao'] = str_replace("[[cliente]]", $atendimento['cliente']['codigo'], $followup['url_avaliacao']);
            $followup['url_avaliacao'] = str_replace("[[razao_social]]", $atendimento['cliente']['nome'], $followup['url_avaliacao']);
            $followup['url_avaliacao'] = str_replace("[[vendedor]]", $atendimento['cliente']['vendedor']['nome'], $followup['url_avaliacao']);
        }
        $followup['url_avaliacao'] = str_replace("[[nome_atendente]]", $followup['created_by']['nome'], $followup['url_avaliacao']);
        $followup['url_avaliacao'] = str_replace("[[email_atendente]]", $followup['created_by']['email'], $followup['url_avaliacao']);
        $followup['url_avaliacao'] = str_replace("[[email_usuario]]", $atendimento['email'], $followup['url_avaliacao']);

        return $followup;
    }

}
