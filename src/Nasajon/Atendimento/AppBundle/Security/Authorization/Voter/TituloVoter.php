<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\Atendimento\AppBundle\Repository\Cliente\UsuariosRepository;
use Nasajon\LoginBundle\Entity\Provisao;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Nasajon\ModelBundle\Services\ConfiguracoesService;

class TituloVoter extends AbstractVoter {

    /**
     *
     * @var UsuariosRepository
     */
    private $repository;

    /**
     *
     * @var ParameterBag
     */
    private $fixedAttributes;

    /**
     *
     * @var Provisao
     */
    private $provisao;

    /**
     *
     * @var \Nasajon\ModelBundle\Services\ConfiguracoesService;
     */
    private $confService;

    public function __construct(UsuariosRepository $repository, ParameterBag $fixedAttributes, Provisao $provisao, ConfiguracoesService $confService) {
        $this->repository = $repository;
        $this->fixedAttributes = $fixedAttributes;
        $this->provisao = $provisao;
        $this->confService = $confService;
    }

    protected function supports($attribute, $subject) {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::INDEX, self::CREATE, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Titulos) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

//        if (!$this->confService->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'TITULOS_EXIBIR_PARA_CLIENTE) {
        if (!$this->confService->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL') || !$this->confService->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'TITULOS_EXIBIR_PARA_CLIENTE_BOLETO')) {
            return false;
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
