<?php

namespace AppBundle\Security\Voter\Meurh;

use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos;
use AppBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SolicitacoesalteracoesenderecosVoter extends AbstractVoter
{
    const CRIAR_ENDERECO = 'meusdados_criacao_sol_alteracao_dados_cadastrais';
    const EXCLUIR_ENDERECO = 'meusdados_criacao_sol_alteracao_dados_cadastrais';


    public function __construct($fixedAttributes)
    {
        parent::__construct($fixedAttributes);
    }

    protected function supports($attribute, $subject)
    {
        if($subject instanceof Solicitacoesalteracoesenderecos 
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
            $this::UPDATE => $this::CRIAR_ENDERECO,
            $this::DELETE => $this::EXCLUIR_ENDERECO,
            $this::CREATE => $this::CRIAR_ENDERECO
        ];
    }
}