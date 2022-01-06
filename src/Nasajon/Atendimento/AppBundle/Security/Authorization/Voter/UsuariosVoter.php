<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\Atendimento\AppBundle\Repository\Cliente\UsuariosRepository;
use Nasajon\LoginBundle\Entity\Provisao;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Usuarios;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UsuariosVoter extends AbstractVoter {

    private $repository;
    private $fixedAttributes;
    private $provisao;

    public function __construct(UsuariosRepository $repository, ParameterBag $fixedAttributes, Provisao $provisao) {
        $this->repository = $repository;
        $this->fixedAttributes = $fixedAttributes;
        $this->provisao = $provisao;
    }

    protected function supports($attribute, $subject) {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::INDEX, self::CREATE, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Usuarios) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        if ($this->provisao->exists()) {
            return true;
        }
        
        $clientes = $this->repository->getClientesByConta($user->getUsername(), $this->fixedAttributes->get('tenant'));

        foreach ($clientes as $cliente) {
            if ($cliente['cliente_id'] == $subject->getCliente()->getCliente() && $cliente['funcao'] == 'A') {
                return true;
            }
        }
        return false;
    }

}
