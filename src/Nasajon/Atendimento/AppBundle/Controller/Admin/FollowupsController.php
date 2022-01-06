<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Admin\FollowupsType;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\SolicitacoesVoter;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Admin\FollowupsController as ParentController;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Followups;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class FollowupsController extends ParentController  {

    /**
     * @inheritDoc
     */
    public function createCreateForm(Followups $entity, $method = "POST") {
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, FollowupsType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add( 'submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Creates a new Atendimento\Admin\Followups entity.
     *
     * @FOS\Post("/{atendimento}/followups/", defaults={ "_format" = "json" })
     */
    public function createAction($atendimento, Request $request) {
      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
      $tenantCodigo = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');
      $tenantNome = $this->get('nasajon_mda.fixed_attributes')->get('tenant_nome');
      $configuracoesService = $this->get('modelbundle.service.configuracoes');
      $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
      $indisponivel = $this->get('nasajon_atendimento_app_bundle.usuariosdisponibilidades_repository')->verificarSeUsuarioIndisponivel($logged_user['email'], $tenant);
      
      if (!empty($configuracoes['USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS']) || !$indisponivel) {
        $entity = new Followups();

        $entity->setTenant($tenant);
        $entity->setAtendimento($atendimento);
        $entity->setCanal('manual');
        
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $entity->setHistorico($request->get('historico'));
        $entity->setArtigo($request->get('artigo'));

        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $repository->insert($atendimento, $logged_user, $tenant, $entity);
            
            $copiaoculta = $configuracoes['HABILITAR_CCO_CHAMADOS'] == '1' ? $request->get('copiaoculta') : null;
            $arrayAtendimento = $this->get('nasajon_mda.atendimento_admin_solicitacoes_repository')->find($atendimento, $tenant);
            $emailsProcessarProducer = $this->get("old_sound_rabbit_mq.emails_processar_producer");

            // Envia o chamado para a fila de processamento de e-mails.
            $emailsProcessarProducer->publish(
                json_encode([
                    'tenant' => $tenant, 
                    'atendimento' => $atendimento,
                    'atendente_respondendo_chamado' => true,
                    'tags' => [
                        'protocolo' => $arrayAtendimento['numeroprotocolo'],
                        'resumo' => $arrayAtendimento['resumo'],
                        'main_url' => $_SERVER['atendimento_url'] . $this->generateUrl('nasajon_atendimento_app_admin_index_index', ['tenant'=> $tenantCodigo]),
                        'atendimento' => $arrayAtendimento,
                    ],
                    'copia_oculta' => $copiaoculta,
                    'responsavel_tipo' => -1,
                    // Seta o tipo do followup, o tipo 1 é Comentário.
                    // Isso vai determinar se o followup vai ou não ser enviado para o Cliente por e-mail.
                    'tipo_followup' => $entity->getTipo()
                ])
            );

            $fechar_atendimento = (int) $request->get('fechar_atendimento');

            if ($fechar_atendimento) {
                $entityAtendimento = $this->get('nasajon_mda.atendimento_admin_solicitacoes_repository')->findObject($atendimento, $tenant);
                $this->denyAccessUnlessGranted(SolicitacoesVoter::CLOSE, $entityAtendimento);
                $entityAtendimento->setSituacao(1);
                $this->get('nasajon_mda.atendimento_admin_solicitacoes_repository')->alterarSituacao($logged_user,$tenant,  $entityAtendimento);
            }

            return new JsonResponse([], JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
      } else {
        $response =  new JsonResponse();
        $response->setStatusCode(401);
        $response->setData(['message' => 'Usuário logado está indisponível no momento e não pode interagir no chamado.']);
        return $response;        
      }
    }

}
