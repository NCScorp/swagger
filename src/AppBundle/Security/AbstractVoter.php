<?php

namespace AppBundle\Security;

use Nasajon\MDABundle\Security\Authorization\AbstractVoter as ParentVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class AbstractVoter extends ParentVoter
{
    protected $fixedAttributes;

    public function __construct($fixedAttributes)
    {
        $this->fixedAttributes = $fixedAttributes;
    }

    protected function supports($attribute, $subject)
    {
        $permissoes = [$this::VIEW, $this::UPDATE, $this::INDEX, $this::DELETE, $this::CREATE];
        if(in_array($attribute, $permissoes)){
            return true;
        }

        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if(method_exists($subject, "getTenant")) {
            if(!is_null($subject->getTenant()) 
                && ($subject->getTenant() !== $this->fixedAttributes->get("tenant"))){
                return false;
            }
        }

        if(method_exists($subject, "getGrupoempresarial")) {
            if(!is_null($subject->getGrupoempresarial())
                && ($subject->getGrupoempresarial() !== $this->fixedAttributes->get("grupoempresarial")))
            return false;
        }

        if(method_exists($subject, "getEstabelecimento")) {
            if(!is_null($subject->getEstabelecimento())
                && ($subject->getEstabelecimento() !== $this->fixedAttributes->get("estabelecimento")))
            return false;
        }

        if(array_key_exists($attribute, $this->getArrayPermissoes())) {
            $permissaoNecessaria = $this->getArrayPermissoes()[$attribute];
            $acoesPermitidas = $this->fixedAttributes->get("acoes_permitidas");
            if (!in_array($permissaoNecessaria, $acoesPermitidas)) {
                return false;
            }
        }
        return true;
    }

    protected function getFixedAttributes()
    {
        return $this->fixedAttributes;
    }


    /**
     * Deve retornar um array associativo onde a chave é o parâmetro $attribute recebido pela função voteOnAttribute
     * e o valor é uma permissão do usuário. Para casos em que, dada uma entidade, um $attribute 
     * específico necessita de uma, e somente uma, permissão.
     */
    protected abstract function getArrayPermissoes() : array;
}