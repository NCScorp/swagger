<?php

namespace AppBundle\Security\Voter\Meurh;

use Nasajon\MDABundle\Entity\Meurh\Solicitacoesferias;
use AppBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SolicitacoesferiasVoter extends AbstractVoter
{
    const CRIAR_FERIAS = 'meusdados_criacao_sol_ferias';


    public function __construct(ParameterBagInterface $fixedAttributes)
    {
        parent::__construct($fixedAttributes);
    }

    protected function supports($attribute, $subject)
    {
        if($subject instanceof Solicitacoesferias 
        && parent::supports($attribute, $subject)){
            return true;
        }
        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $trabalhadorrescindido = $this->fixedAttributes->get('trabalhadorrescindido');
        if(array_key_exists($attribute, $this->getArrayPermissoes())) {
            $permissaoNecessaria = $this->getArrayPermissoes()[$attribute];
            $acoesPermitidas = $this->fixedAttributes->get("acoes_permitidas");
            if (!in_array($permissaoNecessaria, $acoesPermitidas) || in_array($permissaoNecessaria, $acoesPermitidas) && $trabalhadorrescindido) {
                return false;
            }
        }
        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    protected function getArrayPermissoes(): array
    {
        return [
            $this::UPDATE => $this::CRIAR_FERIAS,
            $this::DELETE => $this::CRIAR_FERIAS,
            $this::CREATE => $this::CRIAR_FERIAS
        ];
    }
}