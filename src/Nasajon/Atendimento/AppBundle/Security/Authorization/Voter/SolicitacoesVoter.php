<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\MDABundle\Entity\Atendimento\Cliente\Solicitacoes;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes as SolicitacoesAdmin;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\UsuariosRepository;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SolicitacoesVoter extends AbstractVoter {

    const CLOSE = 'close';

    private $repository;
    private $fixedAttributes;

    public function __construct(UsuariosRepository $repository, ParameterBag $fixedAttributes) {
        $this->repository = $repository;
        $this->fixedAttributes = $fixedAttributes;
    }

    protected function supports($attribute, $subject) {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::INDEX, self::CREATE, self::DELETE, self::CLOSE))) {
            return false;
        }

        if (!($subject instanceof Solicitacoes || $subject instanceof SolicitacoesAdmin)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (empty($subject->getAtendimento())) {
            return true;
        }
        if ($subject->getTenant() != $this->fixedAttributes->get('tenant')) {
            return false;
        }

        if ($user->temPermissao($this->fixedAttributes->get('tenant_codigo'))) {
            return true;
        }


        if (!empty($subject->getCliente()) && !empty($subject->getCliente()->getCliente())) {
            $clientes = $this->repository->getClientesByConta($user->getUsername(), $this->fixedAttributes->get('tenant'));
            foreach ($clientes as $cliente) {
                if ($cliente['cliente_id'] == $subject->getCliente()->getCliente()) {
                    return true;
                }
            }
        } elseif (strcasecmp($subject->getCreatedBy()['email'], $user->getUsername()) == 0) {
            return true;
        }

        return false;
    }

}
