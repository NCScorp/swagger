<?php

namespace AppBundle\Security\Voter\Meurh;

use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts;
use AppBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SolicitacoesalteracoesvtsVoter extends AbstractVoter
{
    const CRIAR_ALTERACAO_VT = 'meusdados_criacao_sol_alteracao_vt';
    const EXCLUIR_ALTERACAO_VT = 'meusdados_criacao_sol_alteracao_vt';


    public function __construct($fixedAttributes)
    {
        parent::__construct($fixedAttributes);
    }

    protected function supports($attribute, $subject)
    {
        if($subject instanceof Solicitacoesalteracoesvts 
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
            $this::UPDATE => $this::CRIAR_ALTERACAO_VT,
            $this::DELETE => $this::EXCLUIR_ALTERACAO_VT,
            $this::CREATE => $this::CRIAR_ALTERACAO_VT
        ];
    }
}