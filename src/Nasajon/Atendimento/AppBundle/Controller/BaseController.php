<?php

namespace Nasajon\Atendimento\AppBundle\Controller;

use Nasajon\Atendimento\AppBundle\Repository\UsuariosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController extends Controller {

    var $queryParams = array();
    private $tenant;

    public function getRequestParamArray() {
        return array(
            'tenant' => $this->getRequest()->get('tenant'),
            'funcao' => $this->getRequest()->get('funcao')
        );
    }

    public function nsjGenerateUrl($url, $params = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
        return $this->generateUrl($url, array_merge($this->getRequestParamArray(), $params), $referenceType);
    }

    /**
     * 
     * @return \Nasajon\LoginBundle\Entity\Provisao
     */
    public function getProvisao() {
        return $this->get('nasajon_loginbundle_provisao');
    }

    public function getTenant() {
        if (!$this->tenant) {

            $this->tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        }
        return $this->tenant;
    }

    public function getClientes() {

        $user = $this->getUser()->getUsername();

        $rep = $this->get('nasajon_mda.atendimento_cliente_usuarios_repository');
        $clientes = $rep->getClientesByConta($user, $this->getTenant());

        return $clientes;
    }
    
    public function getEquipes() {
        $user = $this->getUser()->getUsername();
        $rep = $this->get('nasajon_mda.atendimento_equipes_repository');
        $equipe = $rep->getEquipes($this->getTenant(), $user);
        return $equipe;
    }
    
    public function getCamposCustomizadosDisponiveisCliente() {
        $rep = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository');
        $camposCustomizados = $rep->findAll($this->getTenant(), "", TRUE, null);
        return $camposCustomizados;
    }

}
