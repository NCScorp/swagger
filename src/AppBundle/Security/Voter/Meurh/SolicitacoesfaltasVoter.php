<?php

namespace AppBundle\Security\Voter\Meurh;

use Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas;
use AppBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class SolicitacoesfaltasVoter extends AbstractVoter
{
    const CRIAR_FALTAS = 'meusdados_criacao_sol_falta';
    const EXCLUIR_FALTAS = 'meusdados_criacao_sol_falta';

    protected $gestoresTrabalhadores;

    public function __construct(ParameterBagInterface $fixedAttributes)
    {
        parent::__construct($fixedAttributes);
    }

    protected function supports($attribute, $subject)
    {
        if($subject instanceof Solicitacoesfaltas 
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
            $this::UPDATE => $this::CRIAR_FALTAS,
            $this::DELETE => $this::EXCLUIR_FALTAS,
            $this::CREATE => $this::CRIAR_FALTAS
        ];
    }
}