<?php
namespace Nasajon\Atendimento\AppBundle\Security\Authorization\Voter;

use Nasajon\MDABundle\Entity\Atendimento\Admin\Arquivos;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Arquivos as ClienteArquivos;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArquivosVoter extends AbstractVoter 
{
    private $fixedAttributes;
    private $configuracaoService;

    public function __construct(
        ParameterBag $fixedAttributes, 
        ConfiguracoesService $configuracaoService
    ) 
    {
        $this->fixedAttributes = $fixedAttributes;        
        $this->configuracaoService = $configuracaoService;
    }

    protected function supports($attribute, $subject) 
    {
        if (!in_array($attribute, array(self::VIEW, self::INDEX, self::CREATE, self::UPDATE, self::DELETE))) {
            return false;
        }

        if ($subject instanceof Arquivos || $subject instanceof ClienteArquivos) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) 
    {
        $config = $this->configuracaoService->get($this->fixedAttributes->get('tenant'), 'ATENDIMENTO', 'DISPONIBILIZAR_ARQUIVOS');

        if (!$config || $config != true) {
            return false;
        }
                        
        return true;
    }
}