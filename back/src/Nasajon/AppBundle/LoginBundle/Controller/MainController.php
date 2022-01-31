<?php
namespace Nasajon\AppBundle\LoginBundle\Controller;
use Nasajon\AppBundle\Repository\Ns\TenantsRepository;
use Nasajon\LoginBundle\Controller\MainController as ParentController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class MainController extends ParentController
{
    /**
     * @var \Nasajon\AppBundle\Repository\Ns\TenantsRepository
     */
    protected $tenantRepository;
    public function __construct(\Nasajon\AppBundle\Repository\TenantsRepository $repository)
    {
        $this->tenantRepository = $repository;
    }
    /**
     * @Route("/", name="main_index")
     * @Route("/", name="default")
     * @Template("NasajonLoginBundle:Main:selecionarperfil.html.twig")
     * @Method({"GET"})
     */
    public function mainAction(Request $request)
    {
        $provisoes = $this->getUser()->getPermissoes();
        $codigoGrupo = $request->get('grupoempresarial');
        if (!$codigoGrupo) {
            $codigoTenant = array_keys($provisoes)[0];
            $grupos = $this->getListGrupoEmpresarial($codigoTenant);
            $codigoGrupo = $grupos[0]['codigo'];
        }
        if (empty($provisoes)) {
            return $this->redirecionaSemPermissao();
        } else {
            if ($this->container->getParameter('login_bundle.permission_type') === 'admin') {
                return $this->redirect($this->generateUrl('index', array(
                    'tenant' => array_keys($provisoes)[0],
                )));
            }
            //envia o usuário para a url principal da sua aplicação
            if ($this->container->getParameter('login_bundle.permission_format') == 'tenant') {
                return $this->redirect($this->generateUrl('index', array('tenant' => array_keys($provisoes)[0], 'grupoempresarial' => $codigoGrupo)));
            } else {
                return $this->redirect($this->generateUrl('index', array('tenant' => array_keys($provisoes)[0], 'funcao' => strtolower(array_values(array_values($provisoes)[0])[0]))));
            }
        }
    }
    
    private function getListGrupoEmpresarial($codigotenant)
    {
        $grupoempresarial = $this->tenantRepository->findGruposEmpresariaisByCodigoTenant($codigotenant);
        return $grupoempresarial;
    }

}