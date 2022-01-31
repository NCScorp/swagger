<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Functional extends \Codeception\Module {

    private $tenant_codigo = 'gednasajon';
    private $tenant = 47;

    public function amSamlLoggedInAs($username, $permissoes = [], $administrador = 0) {
        $container = $this->getModule('Symfony')->grabService('kernel')->getContainer();


        $tenant = new \Nasajon\LoginBundle\Entity\Tenant(
        [
            'id' => $this->tenant,
            'codigo' => $this->tenant_codigo,
            'nome' => 'Nasajon',
            "administrador" => $administrador,
            'sistemas' => [
                294 => [
                    'id' => 294,
                    'codigo' => 'crm',
                    'nome' => 'CRM Web',
                    'permissoes' => $permissoes,
                    'entidades' => [],
                    'funcoes' => [
                        'ADMIN' => [
                            'codigo' => 'admin',
                            'id' => 1,
                            'nome' => 'Admin'
                        ],
                        'USUARIO' => [
                            'codigo' => 'usuario',
                            'id' => 2,
                            'nome' => 'Usuário'
                        ]
                    ]
                ]
             ]
        ]
        );
        $user = (new \Nasajon\LoginBundle\Security\User\ContaUser($username, 'Usuário logado.'))
                ->setTenants(array($this->tenant_codigo => $tenant))
                ->addRole('ROLE_TENANTS')
                ->addRole('ROLE_CONTAS')
                ->setSistemaAtual(294, 'crm');

        $resourceOwner = \Codeception\Util\Stub::make(\Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner::class, ['getEmail' => $username], $this);

        $keycloak = \Codeception\Util\Stub::make(\Stevenmaguire\OAuth2\Client\Provider\Keycloak::class, ['getResourceOwner' => $resourceOwner], $this);
        $container->set(\Stevenmaguire\OAuth2\Client\Provider\Keycloak::class, $keycloak);

        $provider = \Codeception\Util\Stub::make(\Nasajon\LoginBundle\Provider\SamlProvider::class, ['loadUserByUsername' => $user], $this);
        // $container->set('nasajon_login_bundle.saml_provider', $provider);
        $container->set('Nasajon\LoginBundle\Provider\SamlProvider', $provider);

        $this->getModule('REST')->haveHttpHeader('Authorization', 'TOKEN');
        $this->getModule('REST')->haveHttpHeader('Content-Type', 'application/json');

        // $firewall = 'secured_area';

        // $token = new \LightSaml\SpBundle\Security\Authentication\Token\SamlSpToken($user->getRoles(), $firewall, [], $user);

        // $session = $container->get('session');

        // $session->set('main_saml_request_state_', []);
        // $session->set('samlsso', new \LightSaml\State\Sso\SsoState());

        // $session->set('_security_' . $firewall, serialize($token));
        // $session->save();

        // $this->getModule('Symfony')->setCookie($session->getName(), $session->getId());
    }

    public function amLoggedInAs($username) {

        $container = $this->getModule('Symfony')->grabService('kernel')->getContainer();

        $token = [
            'tipo' => 'conta',
            'conta' => [
                'nome' => 'Nome',
                'email' => $username,
                'tenants' => [],
                'permissoes' => [
                    'tenants' => true
                ]
            ]
        ];
        $diretorio = \Codeception\Util\Stub::make(\Nasajon\SDK\Diretorio\DiretorioClient::class, ['validateApiKey' => $token]);
        $container->set('nasajon_sdk.diretorio', $diretorio);
    }

    public function sendRaw($method, $uri, $parameters, $files, $server, $content) {

        return json_decode($this->getModule('Symfony')->_request($method, $uri, [], $files, $server, json_encode($parameters)), true);
    }
    
    /**
     * Autor: agarzon 
     * Repositório: https://gist.github.com/agarzon/686e477949311ae215ce
     * 
     * Delete entries from $table where $criteria conditions
     * Use: $I->deleteFromDatabase('users', ['id' => '111111', 'banned' => 'yes']);
     *
     * @param  string $table    tablename
     * @param  array $criteria conditions. See seeInDatabase() method.
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    public function deleteFromDatabase($table, $criteria)
    {
        $dbh = $this->getModule('Db')->_getDbh();
        $query = "delete from %s where %s";
        $params = [];
        foreach ($criteria as $k => $v) {
            $params[] = "$k = ?";
        }
        $params = implode(' AND ', $params);
        $query = sprintf($query, $table, $params);
        $this->debugSection('Query', $query, json_encode($criteria));
        $sth = $dbh->prepare($query);
        return $sth->execute(array_values($criteria));
    }
    
     public function deleteAllFromDatabase($table)
    {
        $dbh = $this->getModule('Db')->_getDbh();
        $query = "delete  from %s";
        $query = sprintf($query, $table);
        $this->debugSection('Query', $query);
        $sth = $dbh->prepare($query);
        return $sth->execute(array_values([]));
    }
    
    /**
     * Gerador de UUID V4
     * Autor: Andrew Moore
     * Origem: https://www.php.net/manual/en/function.uniqid.php#94959
     * 
     * @return type
     */
    public function generateUuidV4(){
      return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    }


}