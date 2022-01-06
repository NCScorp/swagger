<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Admin\SolicitacoesAlteraClienteType;
use Nasajon\Atendimento\AppBundle\Form\Admin\SolicitacoesType;
use Nasajon\Atendimento\AppBundle\Form\Admin\SolicitacoesUpdateType;
use Nasajon\Atendimento\AppBundle\Form\SolicitacoesCamposcustomizadosType;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\SolicitacoesVoter;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Admin\SolicitacoesController as ParentController;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\MDABundle\Entity\Servicos\Atendimentosobservadores;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class SolicitacoesController extends ParentController {

    /**
     * Creates a form to create a Atendimento\Admin\Solicitacoes entity.
     *
     * @param Solicitacoes $entity The entity
     *
     * @return Form The form
     */
    public function createCreateForm(Solicitacoes $entity, $method = "POST", $controller = "tndmnt_dmn_slctcs_frm_cntrllr") {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);

        if ($method == "PUT") {
            $formType = SolicitacoesUpdateType::class;
        } else {
            $formType = SolicitacoesType::class;
        }

        return $this->get('form.factory')
                        ->createNamedBuilder(NULL, $formType, $entity, array(
                            'method' => $method,
                            'controller' => $controller,
                            'camposcustomizados' => $camposcustomizados
                        ))
                        ->getForm();
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Solicitacoes entity.
     *
     * @FOS\Get("/atendimentos/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $permissaoUsuarioService = $this->get('nasajon_atendimento_app_bundle.permissao_usuario_service');

        if(!$permissaoUsuarioService->verificaPermissaoAcessoSolicitacoes($logged_user['email'], $tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }

        $entity = new Solicitacoes();
        $form = $this->createCreateForm($entity);
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Solicitacoes/form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     *
     * @FOS\Get("/atendimentos/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $permissaoUsuarioService = $this->get('nasajon_atendimento_app_bundle.permissao_usuario_service');

        if(!$permissaoUsuarioService->verificaPermissaoAcessoSolicitacoes($logged_user['email'], $tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }

        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Solicitacoes/index.html.twig');
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Solicitacoes entity.
     *
     * @FOS\Get("/atendimentos/template/show.html", defaults={ "_format" = "html" })
     */
    public function templateShowAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $permissaoUsuarioService = $this->get('nasajon_atendimento_app_bundle.permissao_usuario_service');

        if(!$permissaoUsuarioService->verificaPermissaoAcessoSolicitacoes($logged_user['email'], $tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }

        $entity = new Solicitacoes();
        $form = $this->createCreateForm($entity, "PUT", "tndmnt_dmn_slctcs_shw_cntrllr");
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Solicitacoes/show.html.twig', array(
                    'form' => $form->createView(),
                    'camposcustomizados' => $camposcustomizados
        ));
    }

    /**
     * Define atendimento como SPAM, setando a situação como 2
     *
     * @FOS\Put("/atendimentos/{id}/definirSpam" , defaults={ "_format" = "json" })
     */
    public function definirSpamAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
            
            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entity = $this->getRepository()->findObject($id, $tenant);
              $this->denyAccessUnlessGranted(SolicitacoesVoter::CLOSE, $entity);
              $entity->setSituacao(2);
              $this->getRepository()->alterarSituacao($logged_user, $tenant, $entity);
              return new JsonResponse();
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }            
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     * Fecha um atendimento
     *
     * @FOS\Put("/atendimentos/{id}/fechar" , defaults={ "_format" = "json" })
     */
    public function fecharAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
            
            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entity = $this->getRepository()->findObject($id, $tenant);
              $this->denyAccessUnlessGranted(SolicitacoesVoter::CLOSE, $entity);
              $entity->setSituacao(1);

              $this->getRepository()->alterarSituacao($logged_user, $tenant, $entity);
              return new JsonResponse();
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     * Abre um atendimento
     *
     * @FOS\Put("/atendimentos/{id}/visibilidade" , defaults={ "_format" = "json" })
     */
    public function alteravisibilidadeAction(Request $request, $id) {
        try {
            $visivel = $request->get('visivel');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
            
            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entity = $this->getRepository()->findObject($id, $tenant);
              $this->denyAccessUnlessGranted(SolicitacoesVoter::UPDATE, $entity);
              $entity->setVisivelparacliente($visivel);

              $this->getRepository()->alterarVisibilidade($logged_user, $tenant, $entity);
              return new JsonResponse();
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     * Abre um atendimento
     *
     * @FOS\Put("/atendimentos/{id}/abrir" , defaults={ "_format" = "json" })
     */
    public function abrirAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
            
            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entity = $this->getRepository()->findObject($id, $tenant);
              $this->denyAccessUnlessGranted(SolicitacoesVoter::CLOSE, $entity);
              $entity->setSituacao(0);

              $this->getRepository()->alterarSituacao($logged_user, $tenant, $entity);
              return new JsonResponse();
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     *
     * @FOS\Put("/atendimentos/{id}/atrubuir" , defaults={ "_format" = "json" })
     */
    public function atribuirAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);            
            $temEquipe = $this->get('nasajon_mda.atendimento_equipesusuarios_repository')->verificarSeUsuarioTemEquipe($request->get('responsavel_web'), $tenant);
            
            //Se o usuário tem equipe ou se for uma fila ou se for uma atribuição para vazio
            if ($temEquipe || StringUtils::isGuid($request->get('responsavel_web')) || empty($request->get('responsavel_web'))) {
                if (!empty($configuracoes['USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO']) || !$indisponivel) {
                    $entityArr = $this->getRepository()->find($id, $tenant);
                    $entity = $this->getRepository()->fillEntity($entityArr);
      
                    $this->denyAccessUnlessGranted(SolicitacoesVoter::UPDATE, $entity);
      
                    if ($request->get('lastupdate') != $entity->getLastupdate()) {
                      $response =  new JsonResponse();
                      $response->setStatusCode(409);
                      $response->setData([
                          'message' => 'O que você está alterando já foi alterado anteriormente por outra pessoa, deseja sobrescrever com as suas as informações?',
                          'entity' => [
                              'lastupdate' => $entity->getLastupdate()
                          ]
                      ]);
      
                      return $response;
                    }
                    
                    $entity->setResponsavelWeb($request->get('responsavel_web'));
                    $this->getRepository()->alterarResponsavelWeb($logged_user, $tenant, $entity);
                    return new JsonResponse();
                  } else {
                    $response =  new JsonResponse();
                    $response->setStatusCode(401);
                    $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
                    return $response;
                  }
            } else {
                $response =  new JsonResponse();
                $response->setStatusCode(400);
                $response->setData(['message' => 'Não é possível atribuir chamados para usuários sem equipe.']);
                return $response;
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     *
     * @FOS\Put("/atendimentos/{id}/contato" , defaults={ "_format" = "json" })
     */
    public function contatoAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
            
            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entityArr = $this->getRepository()->find($id, $tenant);
              $entity = $this->getRepository()->fillEntity($entityArr);

              $this->denyAccessUnlessGranted(SolicitacoesVoter::UPDATE, $entity);

              if ($request->get('lastupdate') != $entity->getLastupdate()) {
                  $response =  new JsonResponse();
                  $response->setStatusCode(409);
                  $response->setData([
                      'message' => 'O que você está alterando já foi alterado anteriormente por outra pessoa, deseja sobrescrever com as suas as informações?',
                      'entity' => [
                          'lastupdate' => $entity->getLastupdate()
                      ]
                  ]);

                  return $response;
              }

              $email = $request->get('email');
              $entity->setEmail($email);
              $this->getRepository()->alterarEmailContato($logged_user, $tenant, $entity);
              return new JsonResponse();
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     * Lista todas as solicitações atribuidas ao usuário.
     *
     * @FOS\Get("/atendimentos/abertos", defaults={ "_format" = "json" })
     */
    public function abertosAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $entities = $this->get('nasajon_atendimento_app_bundle.atendimentos_service')->abertos($tenant, $this->getUser()->getUsername());

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }
    
    /**
     * Retorna se encaminha email
     *
     * @FOS\Post("/atendimentos/{atendimento}/encaminharhistorico")
     */
    public function encaminharhistoricoAction(Request $request, $atendimento) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $tenantCodigo = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');
        $tenantNome = $this->get('nasajon_mda.fixed_attributes')->get('tenant_nome');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $email = $request->get('email');
        $tags =  [];
        $mensagem = $request->get('mensagem');
        
        $atendimento = $this->getRepository()->find($atendimento, $tenant);

        try{

          /* 
            Esse foreach é necessário para que o objeto se adeque ao template do e-mail
            Quem criou o template, criou no seguinte formato
            {% elseif historico['tipo'] == 6 %}

                        {% if historico['valornovo']['tipo'] == 0 %}
            
            Porém não se atentou que a propriedade 'tipo' fica dentro de $historico['valornovo']['followup']['tipo']

            Para não ter que alterar o template, optou-se por fazer essa alteração mais simples a nível de código
          */ 
          foreach ($atendimento['historico'] as &$historico) {
            if ($historico['tipo'] == 6) {
              $historico['valornovo']['tipo'] = $historico['valornovo']['followup']['tipo'];
            }
          }
        
          $tags['protocolo'] = $atendimento['numeroprotocolo'];
          $tags['resumo'] = $atendimento['resumo'];
          $tags['mensagem'] = $mensagem;
          $tags['main_url'] = $_SERVER['atendimento_url'] . $this->generateUrl('nasajon_atendimento_app_admin_index_index', ['tenant'=> $tenantCodigo]);
          $tags['atendimento'] = $atendimento;
          
          // Altera para chamar o envio de e-mail do Diretório
          $this->get('nasajon_sdk.diretorio')->enviaEmail([
                'to' => $email,
                'split' => true,
                'from' => $tenantCodigo . "@". getenv('email_subdomain'),
                'codigo' => 'atendimento_email_encaminhar_historico',
                'tenant' => $tenant,
                'tags' => $tags,
                'split' => true
            ]);
        
          $valornovo = array('historicoencaminhado'=> implode(',', $email));

          $this->getRepository()->adicionaHistorico($logged_user, $tenant, $atendimento, 10, json_encode($valornovo), null);
                        
          return new JsonResponse();
          
        }catch (Exception $e){
           $response =  new JsonResponse();
          $response->setStatusCode(503);
          $response->setData(['message' => 'Histórico não pode ser encaminhado']);
          return $response;
        }
    }

    /**
     * Retorna se usuário observa o atendimento
     *
     * @FOS\Post("/atendimentos/{atendimento}/observar")
     */
    public function observarAction(Request $request, $atendimento) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
        $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);

        if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
          try {
            $entity = $this->getRepository()->find($atendimento, $tenant);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getRepository()->fillEntity($entity));
          } catch (NoResultException $e) {
              throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
          }

          try {
              $usuarioObservador = $this->get('nasajon_mda.servicos_atendimentosobservadores_repository')->getUsuarioObservador($tenant, $atendimento, $logged_user['email']);
              $this->get('nasajon_mda.servicos_atendimentosobservadores_repository')->delete($usuarioObservador);

              return new JsonResponse();
          } catch (NoResultException $ex) {
              $this->get('nasajon_mda.servicos_atendimentosobservadores_repository')->insert($atendimento, $logged_user, $tenant, new Atendimentosobservadores());

              return new JsonResponse(NULL, JsonResponse::HTTP_CREATED);
          }
        } else {
          $response =  new JsonResponse();
          $response->setStatusCode(401);
          $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
          return $response;
        }
    }

    /**
     * Retorna se atendimento não encontrado
     *
     * @FOS\Get("/atendimentos/404", defaults={ "_format" = "html" })
     */
    public function notfoundAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Solicitacoes/404.html.twig');
    }
    
    /**
     * Busca o Guid do atendimento pelo número do protocolo
     *
     * @FOS\Post("/solicitacoes/", defaults={ "_format" = "json" })
     */
    public function getAtendimentoByProtocoloAction(Request $request)
    {
      return new JsonResponse($this->getRepository()->getAtendimentoByProtocolo($request->get('numeroprotocolo'), $this->get('nasajon_mda.fixed_attributes')->get('tenant')));
    }

    /**
     * Creates a new Atendimento\Admin\Solicitacoes entity.
     *
     * @FOS\Post("/atendimentos/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request) {
      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
      $configuracoesService = $this->get('modelbundle.service.configuracoes');
      $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
      $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
      $temEquipe = $this->get('nasajon_mda.atendimento_equipesusuarios_repository')->verificarSeUsuarioTemEquipe($request->get('responsavel_web'), $tenant);

      if ($temEquipe || StringUtils::isGuid($request->get('responsavel_web')) || empty($request->get('responsavel_web'))) {
        if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
            $situacao = null;
            $responsavel_web = null;
            $cliente = null;
            $created_at = null;
            $camposcustomizados = null;
            $visivelparacliente = null;
            $canal = null;
            $adiado = null;
            $orderfield = null;
            $qtd_respostas = null; 
            $ultima_resposta_admin = null;
            $created_at_ini = null;
            $created_at_fim = null;
    
            $constructors = $this->verificateConstructors($tenant, $situacao, $responsavel_web, $cliente, $created_at, $camposcustomizados, $visivelparacliente, $canal, $adiado, $orderfield, $qtd_respostas, $ultima_resposta_admin,$created_at_ini,$created_at_fim);
    
            $entity = new Solicitacoes();
    
            $entity->setTenant($tenant);
            $entity->setCanal('manual');
    
            $form = $this->createCreateForm($entity);
            $form->handleRequest($request);
    
            $entity->setSintoma($request->get('sintoma'));
    
            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);
    
            if ($form->isValid()) {
                $repository = $this->getRepository();
                $retorno = $repository->insert($tenant, $logged_user, $entity);
    
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
          } else {
            $response =  new JsonResponse();
            $response->setStatusCode(401);
            $response->setData(['erro' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
            return $response;
          }
      } else {
        $response =  new JsonResponse();
        $response->setStatusCode(400);
        $response->setData(['erro' => 'Não é possível criar um chamado atribuído para usuários sem equipe.']);
        return $response;
      }
    }

//    /**
//     * Edits an existing Atendimento\Admin\Solicitacoes entity.
//     *
//     * @FOS\Put("/atendimentos/{id}", defaults={ "_format" = "json" })
//     */
//    public function putAction(Request $request, $id) {
//        try {
//            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
//
//            $entityArr = $this->getRepository()->find($id, $tenant);
//            $entity = $this->getRepository()->fillEntity($entityArr);
//
//            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);
//
//            $editForm = $this->createCreateForm($entity, 'PUT');
//            $editForm->handleRequest($request);
//
//            if ($editForm->isValid()) {
//                $this->getRepository()->update($logged_user, $entity);
//
//                return new JsonResponse();
//            } else {
//                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
//            }
//        } catch (\Doctrine\ORM\NoResultException $e) {
//            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
//        } catch (\Doctrine\ORM\OptimisticLockException $e) {
//            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
//                                'message' => $e->getMessage(),
//                                'entity' => $e->getEntity(),
//            ]));
//        }
//    }

    /**
     *
     * @FOS\Put("/atendimentos/{id}/cliente" , defaults={ "_format" = "json" })
     */
    public function clienteAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);

            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entityArr = $this->getRepository()->find($id, $tenant);
              $entity = $this->getRepository()->fillEntity($entityArr);
              $this->denyAccessUnlessGranted(SolicitacoesVoter::UPDATE, $entity);
              $editForm = $this->get('form.factory')
                      ->createNamedBuilder(NULL, SolicitacoesAlteraClienteType::class, $entity, array(
                          'method' => "PUT",
                          'controller' => "tndmnt_dmn_slctcs_frm_cntrllr"
                      ))
                      ->getForm();
              $editForm->handleRequest($request);

              if ($editForm->isValid()) {

                  $this->getRepository()->alterarCliente($logged_user, $tenant, $entity);

                  return new JsonResponse();
              } else {
                  return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
              }
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }

    /**
     *
     * @FOS\Put("/atendimentos/{id}/campocustomizado" , defaults={ "_format" = "json" })
     */
    public function campocustomizadoAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $configuracoesService = $this->get('modelbundle.service.configuracoes');
            $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
            $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);

            if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
              $entityArr = $this->getRepository()->find($id, $tenant);
              $entity = $this->getRepository()->fillEntity($entityArr);

              $this->denyAccessUnlessGranted(SolicitacoesVoter::UPDATE, $entity);

              $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);

              $editForm = $this->get('form.factory')
                      ->createNamedBuilder(NULL, SolicitacoesCamposcustomizadosType::class, $entity->getCamposcustomizados(), array(
                          'camposcustomizados' => $camposcustomizados,
                          'method' => "PUT",
                          'label' => false,
                          'model' => 'tndmnt_dmn_slctcs_frm_cntrllr.entity.camposcustomizados',
                          'ng-disabled' => 'tndmnt_dmn_slctcs_frm_cntrllr.submitting',
                          'ng-change' => 'tndmnt_dmn_slctcs_frm_cntrllr.save()',
                          'destino' => 'admin'
                      ))
                      ->getForm();
              $editForm->handleRequest($request);
              
              $camposRequisicao = $request->request->all();

              $idsCamposCustomizadosRequisicao = [];

              foreach ($camposRequisicao as $key => $value) {
                $idsCamposCustomizadosRequisicao[] = $key;
              }

              $camposCustomizadosObrigatorios = array_map(function($campo) {
                return $campo;
              }, array_filter($camposcustomizados, function($campo) {
                return $campo['obrigatorio'];
              }));

              $diff = array_diff(array_map(function($campo) { return $campo['atendimentocampocustomizado']; }, $camposCustomizadosObrigatorios), 
                                $idsCamposCustomizadosRequisicao);

              if ($editForm->isValid() && !count($diff)) {
                  $entity->setCamposcustomizados($editForm->getData());

                  $this->getRepository()->alterarCampoCustomizado($logged_user, $tenant, $entity);

                  return new JsonResponse();
              } else {
                $erros = $editForm->getErrors(true);

                $qtde = count($diff);

                if ($qtde) {
                  $mensagem = $qtde > 1 ? "Os campos " : "O campo ";
                  
                  foreach ($diff as $campoObrigatorio) {
                    $str = array_values(array_filter($camposCustomizadosObrigatorios, function($campo) use ($campoObrigatorio) {
                      return ($campo['atendimentocampocustomizado'] == $campoObrigatorio);
                    }))[0]['label'];

                    $mensagem .= "$str, ";
                  }

                  $mensagem .= $qtde > 1 ? "são obrigatórios e devem ser preenchidos." : "é obrigatório e deve ser preenchido.";

                  return new JsonResponse(['message' => $mensagem], 500);
                }

                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
              }
            } else {
              $response =  new JsonResponse();
              $response->setStatusCode(401);
              $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
              return $response;
            }
            
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
        }
    }
    
    /**
     * @FOS\Post("/solicitacoes/{id}/adiar")
     */
    public function adiarAction($id, Request $request) {
      try {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
        $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);

        if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
            
          $entity = $this->getRepository()->findObject($id, $tenant);
          $entity->setAdiado($request->get('adiado'));
          $timezone = new \DateTimeZone($configuracoes['TIMEZONE']);
          $dataAdiamento =  new \DateTime($request->get('data_adiamento'));
          $dataAdiamento->setTimezone($timezone);
          $entity->setDataAdiamento(date_format($dataAdiamento, 'Y-m-d H:i:sP'));
          if ($logged_user['email'] === $entity->getResponsavelWeb()) {
            $this->getRepository()->adiar($tenant, $logged_user, $entity);
            return new JsonResponse();
          } else {
            $response = new JsonResponse();
            $response->setStatusCode(400);
            $response->setData(['message' => 'Não é possível adiar um chamado que não está atribuído a você']);
            return $response;
          }
        } else {
          $response =  new JsonResponse();
          $response->setStatusCode(401);
          $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
          return $response;
        }
      } catch (NoResultException $e) {
          throw $this->createNotFoundException('Unable to find Atendimento\Admin\Solicitacoes entity.');
      }
    }

    /**
     * @FOS\Post("/solicitacoes/mesclar")
     */
    public function mesclarAction(Request $request){
      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

      $chamados = $request->get('chamados');
      $chamadoDestino = $request->get('chamadoDestino');

      $chamadosRepo = $this->getRepository();

      $chamados = '{'. implode(',', $chamados).'}';

      $chamadosRepo->mesclarChamados($chamadoDestino, $chamados, $logged_user ,$tenant);

      $response =  new JsonResponse();
      return $response;
    }

    /**
     * @FOS\Post("/solicitacoes/{id}/atendimentoAlterarResumo")
     * @FuncaoProvisao({"ADMIN"})
     */
    public function atendimentoAlterarResumoAction( $id, Request $request) {

      try {
             
                                          $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');                  
                                                                                                                                                                                                                                                                                                                                                          
          $entityArr = $this->getRepository()->find($id, $tenant);
          
          $entity = $this->getRepository()->fillEntity($entityArr);

          $this->denyAccessUnlessGranted(SolicitacoesVoter::UPDATE, $entity);
          
          $formFactory = $this->get('form.factory');

          $form = $formFactory->createNamedBuilder(null, \Symfony\Component\Form\Extension\Core\Type\FormType::class, $entity, ['csrf_protection' => false, 'allow_extra_fields' => true])
                                      ->add('atendimento')
                                              ->add('resumo', \Symfony\Component\Form\Extension\Core\Type\TextType::class)
                                              ->add('updated_by')
                                      ->getForm();

          $form->handleRequest($request);

          if ($form->isValid()) {

              try{
                  $this->getRepository()->atendimentoAlterarResumo($tenant,$entity);
                  return new JsonResponse();
              }
              catch (\Exception $e) {
                  return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData(["message" => $e->getMessage()]));
              }
          }else{
              return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
          }

      } catch (NoResultException $e) {
          throw $this->createNotFoundException('Unable to find \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes entity.');
      }
  }    
}
