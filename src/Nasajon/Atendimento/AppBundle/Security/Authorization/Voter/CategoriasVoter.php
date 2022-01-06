<?php

namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\MDABundle\Entity\Atendimento\Categorias;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Nasajon\Atendimento\AppBundle\Repository\Atendimento\CategoriasRepository as RepositoryCategorias;

class CategoriasVoter extends AbstractVoter {

    const ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';
    
    private $configuracoes;

    private $fixedAttributes;

    private $categoriasRepository;

    public function __construct(ParameterBag $fixedAttributes, ConfiguracoesService $conf, RepositoryCategorias $repositoryCategorias) {

        $this->configuracoes = $conf;
        $this->fixedAttributes = $fixedAttributes;        
        $this->categoriasRepository = $repositoryCategorias;
    }

    public static function isGuid($string) {
        return preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $string);
    }

    protected function supports($attribute, $subject) {
        if (!in_array($attribute, array(self::ANONYMOUS))) {
            return false;
        }

        if (!$subject instanceof Categorias) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {

        $conf = $this->configuracoes->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO');
        
        if (!$conf) {
            return false;
        }
        
        $isGuid = $this->isGuid($subject->getCategoria());

        if ($subject->getCategoria() && !$isGuid) {

            return false;
        }

        if ($isGuid) {
            $publico = $this->categoriasRepository->findCategoriaPublica($subject->getCategoria(), $subject->getTenant());

            if (!$publico) {
                return false;
            }
        }

        return true;
    }

}