<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\LoginBundle\Entity\Provisao;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Symfony\Component\Security\Core\User\UserInterface;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\Atendimento\AppBundle\Repository\Cliente\UsuariosRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class ClientesVoter extends AbstractVoter {

    private $repository;
    private $fixedAttributes;
    private $provisao;
    private $configuracaoService;

    public function __construct(
        UsuariosRepository $repository, 
        ParameterBag $fixedAttributes, 
        Provisao $provisao, 
        ConfiguracoesService $configuracaoService
    ) 
    {
        $this->repository = $repository;
        $this->fixedAttributes = $fixedAttributes;        
        $this->provisao = $provisao;
        $this->configuracaoService = $configuracaoService;
    }

    protected function supports($attribute, $subject) {
        if (!in_array($attribute, array(self::VIEW, self::INDEX))) {
            return false;
        }

        if (!$subject instanceof Clientes) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }
        
        $config = $this->configuracaoService->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'EXIBE_CLIENTE');

        if ($this->provisao->exists() && $config && $config == true) {
            return true;
        }
                        
        $clientes = $this->repository->getClientesByConta($user->getUsername(), $this->fixedAttributes->get('tenant'));

        foreach ($clientes as $cliente) {
            if ($cliente['cliente_id'] == $subject->getCliente() && $config && $config == true) {
                return true;
            }
        }

        return false;
    }
}