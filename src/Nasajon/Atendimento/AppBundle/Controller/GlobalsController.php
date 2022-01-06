<?php

namespace Nasajon\Atendimento\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\GlobalsVoter;
use Nasajon\Atendimento\AppBundle\Entity\HelperGlobals;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GlobalsController extends BaseController {

    public function getAnonimousConfig(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');

        $configArray = [];

        if (array_key_exists('TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO', $configuracoes)) {
            $configArray['TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO'] = $configuracoes['TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO'];

        } else {

            // Se não existir essa configuração, ou caso ela esteja falsa, redirecionar para o login
            $configArray['TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO'] = false;
        }

        $configArray['usuario'] = [
            'email' => 'anonimous@anonimous',
            'nome' => 'Anonimous',
            'clientes' => [],
            'equipes' => [],
            'provisao' => [],
            'indisponivel' => false,
            'camposcustomizados' => [],
            'USUARIO_SEM_CLIENTE_CRIAR_CHAMADO' => false,
            'CHAMADOS_VISIVELCLIENTE_PADRAO' => false,
            'URL_AVALIACAO_RESPOSTA_CHAMADO' => false,
            'TERMO_ACEITO' => true,
            'IS_ANONIMOUS' => true,
        ];

        $configArray['TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE'] = $configuracoes['TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE'];
        $configArray['TIMEZONE'] = $configuracoes['TIMEZONE'];

        $configArray['tenant'] = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');

        $response = new JsonResponse($configArray);

        $response->setCallback('nsj.globals.setGlobals');

        return $response;
    }

    /**
     * @Route("/globals", name="nsdefault_globals", defaults={ "_format" = "json" })
     */
    public function globalsAction(Request $request) {
        
        try {

            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');

            $usuariosDisponibilidadesService = $this->get('nasajon_atendimento_app_bundle.usuarios_dispinibilidades_service');
            $usuarioIndisponivel = $usuariosDisponibilidadesService->verificarSeUsuarioIndisponivel($this->getUser()->getUsername(), $this->getTenant());

            $termoAceito = $this->get('nasajon_atendimento_app_bundle.termos_repository')->checkTermoAceito($logged_user['email'], $tenant);
            
            $configArray = array(
                'tenant' => $request->get('tenant'),
                'usuario' => [
                    'email' => $this->getUser()->getUsername(),
                    'nome' => $logged_user['nome'],
                    'clientes' => $this->getClientes(),
                    'equipes' => $this->getEquipes(),
                    'provisao' => $this->getProvisao()->getFuncaoCodigo(),
                    'indisponivel' => $usuarioIndisponivel,
                    'camposcustomizados' => $this->getCamposCustomizadosDisponiveisCliente(),
                    'USUARIO_SEM_CLIENTE_CRIAR_CHAMADO' => $configuracoes['USUARIO_SEM_CLIENTE_CRIAR_CHAMADO'],
                    'CHAMADOS_VISIVELCLIENTE_PADRAO' => $configuracoes['CHAMADOS_VISIVELCLIENTE_PADRAO'],
                    'URL_AVALIACAO_RESPOSTA_CHAMADO' => $configuracoes['URL_AVALIACAO_RESPOSTA_CHAMADO'],
                    'TERMO_ACEITO' => $termoAceito,
                ],
                'TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL' => $configuracoes['TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL'],
                'TITULOS_EXIBIR_PARA_CLIENTE_BOLETO' => $configuracoes['TITULOS_EXIBIR_PARA_CLIENTE_BOLETO'],
                'USUARIOSDISP_QUEM_DETERMINA' => $configuracoes['USUARIOSDISP_QUEM_DETERMINA'],
                'USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS' => $configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS'],
                'USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO' => $configuracoes['USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO'],
                'USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS' => $configuracoes['USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS'],
                'USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA' => $configuracoes['USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA'],
                'DISPONIBILIZAR_ARQUIVOS' => $configuracoes['DISPONIBILIZAR_ARQUIVOS'],
                // 'USUARIO_SEM_EQUIPE_PODE_ACESSAR_CHAMADOS_CLIENTES' => $configuracoes['USUARIO_SEM_EQUIPE_PODE_ACESSAR_CHAMADOS_CLIENTES'],
                'HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS' => $configuracoes['HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS'],
                'ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO' => $configuracoes['ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO'],
                'PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE' => $configuracoes['PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE'],
                'TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE' => $configuracoes['TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE'],
                'CADASTRAR_CONTATO_SEM_CLIENTE' => $configuracoes['CADASTRAR_CONTATO_SEM_CLIENTE'],
                'TITULO_ASSUNTO' => $configuracoes['TITULO_ASSUNTO'],
                'PLACEHOLDER_ASSUNTO' => $configuracoes['PLACEHOLDER_ASSUNTO'],
                'TIMEZONE' => $configuracoes['TIMEZONE'],
                'ARTIGO_TAG_OBRIGATORIO' => $configuracoes['ARTIGO_TAG_OBRIGATORIO'],
                'TERMO_HABILITADO' => $configuracoes['TERMO_HABILITADO'],
                'CLIENTE_SITUACAO_SEM_RESTRICAO_LABEL' => $configuracoes['CLIENTE_SITUACAO_SEM_RESTRICAO_LABEL'],
                'CHAMADO_SINTOMA_LABEL' => $configuracoes['CHAMADO_SINTOMA_LABEL'],
                'CHAMADO_SINTOMA_OBSERVACAO' => $configuracoes['CHAMADO_SINTOMA_OBSERVACAO'],
                'CHAMADO_POR_EMAIL_HTML' => $configuracoes['CHAMADO_POR_EMAIL_HTML'],
                'ATENDENTE_CRIA_CHAMADO_SEM_CLIENTE' => $configuracoes['ATENDENTE_CRIA_CHAMADO_SEM_CLIENTE'],
                'ENVIA_EMAIL_CRIACAO_CHAMADO_ADMIN' => $configuracoes['ENVIA_EMAIL_CRIACAO_CHAMADO_ADMIN'],
                'EXIBE_CLIENTE' => $configuracoes['EXIBE_CLIENTE']
            );

            if (array_key_exists('GRUPOS_EMPRESARIAIS_ATIVOS', $configuracoes)) {
                $configArray['GRUPOS_EMPRESARIAIS_ATIVOS'] = $configuracoes['GRUPOS_EMPRESARIAIS_ATIVOS'];
            }

            if (array_key_exists('TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO', $configuracoes)) {
                $configArray['TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO'] = $configuracoes['TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO'];
            }

            // Configuração responsável por listar somente os chamados que determinado usuário criou, na visão do cliente
            // 0 - Usuários do mesmo cliente visualizam todos os chamados
            // 1 - Cada usuário visualiza seu próprio chamado, exceto o Administrador
            if (!array_key_exists('ACESSO_CHAMADOS_VISAO_CLIENTE', $configuracoes)) {

                // Caso não exista a configuração do acesso aos chamados, seta como 0 (Usuários do mesmo cliente visualizam todos os chamados)
                $configArray['ACESSO_CHAMADOS_VISAO_CLIENTE'] = 0;
            } else {
                // Caso exista, seta o valor da configuração
                $configArray['ACESSO_CHAMADOS_VISAO_CLIENTE'] = $configuracoes['ACESSO_CHAMADOS_VISAO_CLIENTE'];
            }

            /**
             * Caso não exista a configuração de habilitação do CCO, seta como 1 (CCO Habilitado)
             * Caso exista, seta o valor da configuração
             */
            if (!array_key_exists('HABILITAR_CCO_CHAMADOS', $configuracoes)) {
                $configArray['HABILITAR_CCO_CHAMADOS'] = "1";
            } else {
                $configArray['HABILITAR_CCO_CHAMADOS'] = $configuracoes['HABILITAR_CCO_CHAMADOS'];
            }
            
            $configArray['PORTAL_TITULO'] = $configuracoes['PORTAL_TITULO'];
            $configArray['PORTAL_DESCRICAO'] = $configuracoes['PORTAL_DESCRICAO'];
            $configArray['CHAMADOS_DESCRICAO'] = $configuracoes['CHAMADOS_DESCRICAO'];
            $configArray['ARTIGO_DESCRICAO'] = $configuracoes['ARTIGO_DESCRICAO'];
            $configArray['TITULOS_DESCRICAO'] = $configuracoes['TITULOS_DESCRICAO'];
            $configArray['DOWNLOADS_DESCRICAO'] = $configuracoes['DOWNLOADS_DESCRICAO'];

            $response = new JsonResponse($configArray);

            $response->setCallback('nsj.globals.setGlobals');

            return $response;

        } catch (AccessDeniedException $e) {
         
            // Não possui permissão para as configurações Globais
        }


        if (!$this->getUser()) {
            $this->denyAccessUnlessGranted(GlobalsVoter::ANONYMOUS, new HelperGlobals());
        }

        return $this->getAnonimousConfig($request);
    }

    /**
     * @Route("/globalsc", name="nsdefault_globals_cliente", defaults={ "_format" = "json" })
     */
    public function indexAction(Request $request) {
        $response = new JsonResponse(array(
            'tenant' => $request->get('tenant'),
            'usuario' => [
                'email' => $this->getUser()->getUsername(),
                'nome' => $this->getUser()->getNome(),
                'clientes' => $this->getClientes(),
            ]
        ));
        $response->setCallback('nsj.globals.setGlobals');

        return $response;
    }



}
