<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Contatos;

class ContatosVoter extends AbstractVoter {

    const ACESSAR_CONTATOS_CLIENTES = "Acessar contatos clientes";

    private $fixedAttributes;
    private $usuariosRepository;

    public function __construct(ParameterBag $fixedAttributes, $usuariosRepository) {

        $this->fixedAttributes = $fixedAttributes;
        $this->usuariosRepository = $usuariosRepository;

    }

    protected function supports($attribute, $subject) {
        if (!$subject instanceof Contatos) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {

        $tenant = $this->fixedAttributes->get('tenant');
        $logged_user = $this->fixedAttributes->get('logged_user');

        $clientes = $this->usuariosRepository->getClientesByConta($logged_user['email'], $tenant);

        if (!$clientes) {
            return false;
        }

        if (!count($clientes)) {
            return false;
        }

        if (!count(array_filter($clientes, function($cliente) {
            return $cliente['funcao'] == 'A';
        }))) {
            return false;
        }

        return true;
    }

}