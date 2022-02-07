<?php

namespace Mock;

use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

class KeycloakProviderMock extends Keycloak
{
    public function __construct()
    {
    }

    public function getResourceOwner(AccessToken $token)
    {
        $username = "rodrigodirk@nasajon.com.br";
        $resourceOwner = \Codeception\Util\Stub::make(\Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner::class, ['getEmail' => $username], $this);
        return $resourceOwner;
    }
}