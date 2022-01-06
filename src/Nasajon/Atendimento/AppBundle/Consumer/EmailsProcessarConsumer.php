<?php

namespace Nasajon\Atendimento\AppBundle\Consumer;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Nasajon\ModelBundle\Repository\Ns\TenantsRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosfilasRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosobservadoresRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Nasajon\Atendimento\AppBundle\Repository\Ns\ClientesRepository;
use Nasajon\ModelBundle\Services\ConfiguracoesService;

// docker exec -ti atendimento_app_1 app/console rabbitmq:consume emails_processar
class EmailsProcessarConsumer implements ConsumerInterface {

    /**
     * @var AtendimentosfilasRepository
     */
    private $atendimentosfilasRepository;

    private $solicitacoesRepository;

    private $tenantsRepository;

    /**
     * @var Producer
     */
    private $emailsEnviar;

    private $subdominioEmailFrom;

   /**
     * @var ClientesRepository
     */
    private $clientesRepository;

    /** @var ConfiguracoesService */
    private $configuracoesService;

    private $observadoresRepository;

    public function __construct(
        AtendimentosfilasRepository $atendimentosfilasRepository, 
        SolicitacoesRepository $solicitacoesRepository, 
        TenantsRepository $tenantsRepository,
        Producer $emailsEnviar, 
        $subdominioEmailFrom,
        ClientesRepository $clientesRepository,
        ConfiguracoesService $configuracoesService,
        AtendimentosobservadoresRepository $observadoresRepository
    )
    {
        $this->atendimentosfilasRepository = $atendimentosfilasRepository;
        $this->solicitacoesRepository = $solicitacoesRepository;
        $this->tenantsRepository = $tenantsRepository;
        $this->emailsEnviar = $emailsEnviar;
        $this->subdominioEmailFrom = $subdominioEmailFrom;
        $this->clientesRepository = $clientesRepository;

        $this->configuracoesService = $configuracoesService;

        $this->observadoresRepository = $observadoresRepository;
    }

    public function execute(AMQPMessage $msg) {
        $codigoTemplate = 'atendimento_cliente_novo_observador_fila';

        $data = json_decode($msg->getBody(), true);
        $contatos = [];
        $tags = [];

        // Carrega o objeto tenant para poder pegar o código
        $tenantObj = $this->tenantsRepository->findBy(['tenant' => $data['tenant']]);

        if (count($tenantObj) == 0) {
            throw new Exception("Não foi possível encontrar o tenant '".$data['tenant']."'");
        }

        $tenantObj = $tenantObj[0];
        $tenantNome = $tenantObj->getNome() ?: 'Atendimento Web';
        $tenantCodigo = $tenantObj->getCodigo();

        // Carrega o atendimento
        $atendimento = $this->solicitacoesRepository->find($data['atendimento'], $data['tenant']);
        $configuracoes = $this->configuracoesService->get($data['tenant'], 'ATENDIMENTO');

        // Caso a tag venha como responsável ao invés de responsável_web
        if (!(isset($data['responsavel_web'])) && isset($data['responsavel'])) {
            $data['responsavel_web'] = $data['responsavel'];
        }

        // Caso o responsável_web que chegou na mensagem não seja um guid, nem um e-mail válido, pega o responsável do próprio atendimento.
        if (!isset($data['responsavel_web']) || (!StringUtils::isGuid($data['responsavel_web']) && !StringUtils::isEmail($data['responsavel_web']))) {
            $data['responsavel_web'] = $atendimento['responsavel_web'];
        }

        $observadores = [];

        switch ($data['responsavel_tipo']) {
            // Caso o tipo do responsável web seja um atendente
            case 1: 
                if (!in_array($atendimento['atribuido_a']['label'], $contatos)) {
                    array_push($contatos, $atendimento['atribuido_a']['label']);
                }

                break;

            // Caso o tipo do responsável web seja uma fila
            case 2:

                if (StringUtils::isGuid($data['responsavel_web'])) {
                    // Carrega a fila
                    $fila = $this->atendimentosfilasRepository->findObject($data['responsavel_web'], $data['tenant']);

                    // Pega os observadores da fila
                    $observadores = $fila->getObservadores();

                    // Pega os contatos (e-mail) dos observadores
                    $observadores->map(function($value) use (&$contatos) {
                        if (!in_array($value->getUsuario(), $contatos)) {
                            array_push($contatos, $value->getUsuario());
                        }
                    });

                    // Seta os observadores
                    $observadores = $contatos;
                } else {
                    $observadores = $contatos = [$data['responsavel_web']];
                }

                break;
        }


        // Carrega a configuração de visibilidade do chamado pelos usuários do Cliente
        // 0 - Usuários do mesmo cliente visualizam todos os chamados
        // 1 - Cada usuário visualiza seu próprio chamado, exceto o Administrador
        $acessoVisaoCliente = $configuracoes['ACESSO_CHAMADOS_VISAO_CLIENTE'];

        // Encontra todos os usuários do cliente que estão setados para Notificar.
        $usuariosParaNotificar = [];

        // Caso a tag tecnico_admin_atribuindo_a_uma_fila_ou_a_outro_tecnico não esteja setada,         
        // pega os usuários do cliente a serem notificados, segundo suas regras.
        //
        // Essa tag é setada em SolicitaoesRepository, na função alterarResponsavelWeb.
        if (!(isset($data['tecnico_admin_atribuindo_a_uma_fila_ou_a_outro_tecnico']))) {
        
            if (isset($atendimento['cliente']) && isset($atendimento['cliente']['cliente'])) {
                $usuariosParaNotificar = $this->clientesRepository->findAllUsersToNotificate($atendimento['cliente']['cliente'], $data['tenant']);

                // Se o responsável não for uma fila e não existam observadores para a fila (caso seja)
                // if ($data['responsavel_tipo'] != 2 && !count($observadores)) {
                if ((isset($data['cliente_criando_chamado']) && $data['cliente_criando_chamado'] && isset($data['autoreply'])) || 
                    (isset($data['tecnico_admin_criando_chamado']) && $data['tecnico_admin_criando_chamado']) ||
                    (isset($data['atendente_respondendo_chamado']) && $data['atendente_respondendo_chamado'])) {

                        // Retorna, porque nesse caso, o tipo do Followup é um comentário, e não deve ser enviado para o cliente.
                        if (isset($data['tipo_followup']) && $data['tipo_followup'] == 1) {
                            return;
                        }

                        // Verifica se o Chamado deve estar visível para o cliente.
                        // Caso sim, pega os contatos do Cliente.
                        if ($atendimento['visivelparacliente'] == true) {
                            // Filtra todos os administradores, além do usuário criador do e-mail.
                            if ($acessoVisaoCliente) {
                                $contatos = array_map(function($usuario) {
                                    return $usuario['conta'];
                                },array_filter($usuariosParaNotificar, function($usuario) use ($atendimento) {
                                    return $usuario['funcao'] == 'A' || $usuario['conta'] == $atendimento['email'];
                                }));;

                            // Apenas pega a conta de todos os usuários e administradores.
                            } else {
                                $contatos = array_map(function($usuario) {
                                    return $usuario['conta'];
                                }, $usuariosParaNotificar);

                                // Adiciona o e-mail do contato do chamado para também receber o e-mail.
                                if (!count(array_filter($contatos, function($contato) use ($atendimento) {
                                    return $contato == $atendimento['email'];
                                }))) {
                                    $contatos[] = $atendimento['email'];
                                }
                            }
                            
                        } else {

                            // Retorna, pois o chamado não deve estar visível para o Cliente.
                            return;
                        }
                }
            } else {
                // Retorna, porque nesse caso, o tipo do Followup é um comentário, e não deve ser enviado para o cliente.
                if (isset($data['tipo_followup']) && $data['tipo_followup'] == 1) {
                    return;
                }

                // Caso o chamado não tenha cliente vinculado, pega o email do contato do chamdo.
                $contatos[] = $atendimento['email'];
            }
        }

        // Se o chamado estiver sendo criado pelo cliente, e exista uma regra de resposta automática.
        // Se houver autoreply, o valor armazenado nele é passado como mensagem para o template de e-mail
        if (isset($data['cliente_criando_chamado']) && $data['cliente_criando_chamado'] && $data['autoreply']) {
            $codigoTemplate = 'atendimento_cliente_novo_autoreply';
            $tags['mensagem'] = $data['autoreply']['valor'];

        // Se o chamado estiver sendo criado por um técnico, na visão do admin.
        } else if ($data['responsavel_tipo'] == 1 && isset($data['tecnico_admin_criando_chamado']) && $data['tecnico_admin_criando_chamado']) {
            $codigoTemplate = "atendimento_admin_novo";

        // Se o chamado estiver sendo respondido por um técnico, na visão do admin.
        } else if (isset($data['atendente_respondendo_chamado']) && $data['atendente_respondendo_chamado']) {
            // Caso existam e-mails para enviar como Cópia Oculta
            if (isset($data['copia_oculta']) && $data['copia_oculta'] && $this->validarCopiaOculta($data['copia_oculta']) && $configuracoes['HABILITAR_CCO_CHAMADOS'] == '1') {
                $this->emailsEnviar->publish(
                    json_encode([
                        'tenant'=> $data['tenant'],
                        'to' => $data['copia_oculta'],
                        'split' => true,
                        'from' => $tenantCodigo . "@". $this->subdominioEmailFrom,
                        'codigo' => 'atendimento_email_copia_oculta',
                        'tags' => $data['tags'],
                        'tenant' => $data['tenant']
                    ])
                );
            }

            $codigoTemplate = 'atendimento_resposta_admin';

        // Se o chamado estiver sendo respondido pelo cliente, e o responsável pelo chamado for um técnico
        } else if (isset($data['cliente_respondendo_chamado']) && $data['cliente_respondendo_chamado']) {
            $codigoTemplate = 'atendimento_resposta_cliente';

            if ($data['responsavel_tipo'] == 1) {
                $contatos = [$data['responsavel_web']];

            } else if ($data['responsavel_tipo'] == 2) {

                // Seta os contatos sendo os observadores da fila do chamado.
                $contatos = $observadores;
            }

        // Se o chamado estiver sendo atribuído para um técnico
        } else if (isset($data['tecnico_admin_atribuindo_a_uma_fila_ou_a_outro_tecnico']) &&  $data['responsavel_tipo'] == 1 && !StringUtils::isGuid($data['responsavel_web'])) {
            $codigoTemplate = "atendimento_admin_atribuido";
        }

        // Busca os observadores do chamado e adiciona à lista de contatos para enviar os e-mails.
        $this->buscarObservadoresDoChamado($data['atendimento'], $data['tenant'], $contatos);

        // Busca os observadores do chamado e adiciona à lista de contatos para enviar os e-mails.
        $this->buscarObservadoresDoChamado($data['atendimento'], $data['tenant'], $contatos);

        // Caso existam contatos a notificar, os mesmos são enviados para a fila de envio de e-mails
        if (count($contatos) && $contatos[0]) {
            $tags['tenantCodigo'] = $tenantCodigo;
            $tags['protocolo'] = $atendimento['numeroprotocolo'];
            $tags['resumo'] = $atendimento['resumo'];
            $tags['atendimento'] = $atendimento;

            $this->emailsEnviar->publish(
                json_encode([
                    'tenant'=> $data['tenant'],
                    'to' => $contatos,
                    'split' => true,
                    'from' => $tenantCodigo . "@". $this->subdominioEmailFrom,
                    'codigo' => $codigoTemplate,
                    'tags' => $tags,
                    'tenant' => $data['tenant']
                ])
            );
        }
    }

    // Remove algum e-mail inválido passado como cópia oculta.
    // Problema observado na tarefa 50700.
    private function validarCopiaOculta(&$emails) {
        foreach ($emails as $key => $value) {
            if (!StringUtils::isEmail($value)) {
                unset($emails[$key]);
            }
        }

        $emails = array_values($emails);

        return count($emails);
    }

    /**
     * Função criada para buscar os observadores do chamado.
     * 
     * Observador do chamado é diferente de Observador de uma fila.
     * Observador da fila é cada usuário seguidor de uma fila. Observador do Chamado é cada usuário que opta por observar um chamado específico.
     */
    private function buscarObservadoresDoChamado($atendimento, $tenant, &$contatos) {
        $observadores = $this->observadoresRepository->findAll($tenant, $atendimento);

        foreach ($observadores as $observador) {
            if (!array_filter($contatos, function($contato) use ($observador) {
                return $contato == $observador['usuario'];
            })) {
                $contatos[] = $observador['usuario'];
            }
        }
    }
}
