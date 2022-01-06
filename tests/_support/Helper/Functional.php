<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Functional extends \Codeception\Module {

  // public function amSamlLoggedInAs($username) {
  //   $container = $this->getModule('Symfony')->grabService('kernel')->getContainer();


  //   $user = (new \Nasajon\LoginBundle\Security\User\ContaUser($username, 'Usuário logado.'))
  //       ->addRole('ROLE_ADMIN')
  //       ->addRole('ROLE_USER')
  //       ->setSistemaAtual(271, 'provisao');


  //   $firewall = 'secured_area';

  //   $token = new \LightSaml\SpBundle\Security\Authentication\Token\SamlSpToken($user->getRoles(), $firewall, [], $user);

  //   $session = $container->get('session');

  //   $session->set('main_saml_request_state_', []);
  //   $session->set('samlsso', new \LightSaml\State\Sso\SsoState());

  //   $session->set('_security_' . $firewall, serialize($token));
  //   $session->save();

  //   $this->getModule('Symfony')->setCookie($session->getName(), $session->getId());
   
  // }

  public function amLoggedInAs($username) {

    $container = $this->getModule('Symfony')->grabService('kernel')->getContainer();

    $token = [
        'tipo' => 'conta',
        'conta' => [
            'nome' => 'Nome',
            'email' => $username,
            'tenants' => [
                    [
                    'id' => 47,
                    'codigo' => 'gednasajon',
                    'administrador' => true,
                    'sistemas' => [
                      'atendimento' => [
                            'id' => 271,
                            'codigo' => 'atendimento',
                            'nome' => 'Atendimento',
                            'funcoes' => [
                                    ['codigo' => 'admin', 'id' => 397, 'nome' => 'Admin']
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    $diretorio = \Codeception\Util\Stub::make(\Nasajon\SDK\Diretorio\DiretorioClient::class, ['validateApiKey' => $token]);
    $container->set('Nasajon\SDK\Diretorio\DiretorioClient', $diretorio);

  }

  public function sendRaw($method, $uri, $parameters, $files, $server, $content) {

    return json_decode($this->getModule('Symfony')->_request($method, $uri, $parameters, $files, $server, $content), true);
  }

}
