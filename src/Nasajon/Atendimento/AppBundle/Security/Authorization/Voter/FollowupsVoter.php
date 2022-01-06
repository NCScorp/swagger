<?php

use Nasajon\Atendimento\AppBundle\Repository\Cliente\UsuariosRepository;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Followups;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Followups as FollowupsAdmin;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

class FollowupsVoter extends AbstractVoter {

    private $repository;
    private $tenant;

    public function __construct(UsuariosRepository $repository, ParameterBag $attributesBag) {
        $this->repository = $repository;
        $this->tenant = $attributesBag->get('tenant');
    }

    protected function supports($attribute, $subject) {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW))) {
            return false;
        }

        if (!($subject instanceof Followups || $subject instanceof FollowupsAdmin)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject->getTenant() != $this->fixedAttributes->get('tenant')) {
            return false;
        }

        if ($user->temPermissao($this->fixedAttributes->get('tenant_codigo'), 'admin')) {
            return true;
        }

        $clientes = $this->repository->getClientesByConta($user->getUsername(), $this->tenant);
        foreach ($clientes as $cliente) {
            if ($cliente['cliente_id'] == $subject->getCliente()->getId()) {

                return true;
            }
        }
        return false;
    }

}
