<?php

namespace Nasajon\Atendimento\AppBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface {

    /**
     * Starts the authentication scheme.
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null) {

        
        
        $response = new Response();
        $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realmName));
        $response->setStatusCode(401);

        return $response;
    }

}
