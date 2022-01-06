<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\RestBundle\Controller\Annotations as FOS;

class VinculoController extends Controller {

  const KEY = "nasajonsistemas";

  /**
   * @FOS\Post("/vinculo", defaults={ "_format" = "json" })
   */
  public function createAction(Request $request) {
    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
    $tenantCodigo = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');
    $tenantNome = $this->get('nasajon_mda.fixed_attributes')->get('tenant_nome');

    $form = $this->get('form.factory')->createNamedBuilder(null, FormType::class, [], ['csrf_protection' => false, 'allow_extra_fields' => true])
            ->add('cnpj', TextType::class, array('required' => true))
            ->getForm();
    $form->handleRequest($request);

    if (!$form->isSubmitted()) {
      return new JsonResponse(["error" => "Nenhum dado recebido.", "code" => '001'], JsonResponse::HTTP_BAD_REQUEST);
    } elseif (!$form->isValid()) {
      return new JsonResponse(["error" => "Ocorreu um erro ao validar os dados do formulário.", "code" => '002'], JsonResponse::HTTP_BAD_REQUEST);
    } else {
      $cnpj = $form->get('cnpj')->getData();

      $cliente = $this->get('nasajon_mda.ns_clientes_repository')->buscaClientePorCnpjCpf($cnpj, $tenant);

      if (!$cliente) {
        return new JsonResponse(["error" => "CNPJ/CPF não encontrado.", "code" => '003'], JsonResponse::HTTP_BAD_REQUEST);
      }

      if (empty($cliente['emails'])) {
        return new JsonResponse(["error" => "Email de cobrança não cadastrado.", "code" => '004'], JsonResponse::HTTP_BAD_REQUEST);
      }

      $entity = new \Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios();
      $entity->setConta($this->getUser()->getUsername());
      $entity->setFuncao('A');
      $entity->setNotificar(FALSE);
      $entity->setPendente(TRUE);

      //Cria registro na tabela
      $resposta = $this->get('nasajon_mda.atendimento_cliente_usuarios_repository')->insert($cliente['id'], $tenant, ["nome" => $this->getUser()->getNome(), "email" => $this->getUser()->getUsername()], $entity);
      $token = JWT::encode(["id" => $resposta['clientefuncao'], "exp" => strtotime("+2 days")], self::KEY);

      $codigoTenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');

      //Envia email
      $this->get('nasajon_sdk.diretorio')->enviaEmail([
          'to' => $cliente['emails'],
          'split' => true,
          'from' => sprintf("%s <%s>", $tenantNome , $tenantCodigo . "@". getenv('email_subdomain') ),
          'codigo' => 'atendimento_email_autoprovisao_cliente',
          'tenant' => $tenant,
          'tags' => [
              'email' => $this->getUser()->getUsername(),
              'nome' => $this->getUser()->getNome(),
              'link' => $this->generateUrl('aprovar', ["id" => $resposta['clientefuncao'], "token" => $token, "tenant" => $codigoTenant], UrlGeneratorInterface::ABSOLUTE_URL),
              'cnpj' => $cliente['cnpj']
          ]
      ]);

      $emailsSaida = array_map(function($email) {
        return StringUtils::ofuscateEmail($email);
      }, $cliente['emails']);


      return new JsonResponse(["emails" => $emailsSaida], JsonResponse::HTTP_OK);
    }
  }

  /**
   * @FOS\Get("/vinculo/aprovar")
   */
  public function aprovarAction(Request $request) {
    $id = $request->get('id');
    $token = $request->get('token');
    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
    $codigoTenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');

    if (!empty($token)) {

      try {
        $jwt = JWT::decode($token, self::KEY, array('HS256'));

        if ($jwt->id == $id) {

          $entity = new \Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios();
          $entity->setClientefuncao($id);

          $this->get('nasajon_mda.atendimento_cliente_usuarios_repository')->clienteFuncaoAprovarPorEmail($tenant, $entity);
          return $this->redirect('/' . $codigoTenant . '/');
        } else {
          throw $this->createNotFoundException("Token inválido, expirado ou já aprovado.");
        }
      } catch (\Firebase\JWT\ExpiredException $ex) {
        
      } catch (Exception $ex) {
        
      }
    } else {
      throw $this->createNotFoundException("Token não encontrado.");
    }
  }

}
