<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Nasajon\Atendimento\AppBundle\Entity\HelperGlobals;

class GlobalsVoter extends AbstractVoter {

    const ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';
    
    private $configuracoes;

    private $fixedAttributes;

    public function __construct(ParameterBag $fixedAttributes, ConfiguracoesService $conf) {

        $this->configuracoes = $conf;
        $this->fixedAttributes = $fixedAttributes;        
    }

    protected function supports($attribute, $subject) {
        if (!in_array($attribute, array(self::ANONYMOUS))) {
            return false;
        }

        if (!$subject instanceof HelperGlobals) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {

        $conf = $this->configuracoes->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO');
        
        if (!$conf) {
            return false;
        }

        if ($conf === "false") {
            return false;
        }
        
        return true;
    }

}