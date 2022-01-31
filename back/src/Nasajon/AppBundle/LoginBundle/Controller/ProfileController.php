<?php

namespace Nasajon\AppBundle\LoginBundle\Controller;

use Nasajon\LoginBundle\Controller\ProfileController as ParentController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;

class ProfileController extends ParentController
{

    /**
     * @var \Nasajon\AppBundle\Repository\TenantsRepository
     */
    protected $tenantRepository;
    protected $container;
    protected $tokenStorage;

    public function __construct(\Nasajon\AppBundle\Repository\TenantsRepository $repository,
                                \Symfony\Component\DependencyInjection\ContainerInterface $container,
                                TokenStorageInterface $tokenStorage )
    {
        $this->tenantRepository = $repository;
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
    }

    public function getConfiguracoes()
    {
        return $this->get('Nasajon\MDABundle\Service\Web\ConfiguracoesService');
    }

    private function getConfiguracoesSistema($tenant)
    {
        $filter = new Filter();
        $arrFilterExpression = [new FilterExpression('sistema', 'eq', 'CRMWEB')];
        $filter->setFilterExpression($arrFilterExpression);
        return $this->getConfiguracoes()->findAll($tenant, $filter);
    }

    /**
     * Método sobrescrito para desativar a url de logout
     * @Route("/profile", name="profile", methods={"GET","POST","HEAD"})
     */
    public function indexAction(Request $request)
    {       
        $usuario = $this->getUser();

        $organizacoes = [];
        foreach ($usuario->getPermissoes() as $codigo => $permissao) {

            $tenant = $usuario->getTenants()[$codigo];

            //Busca os Grupos Empresariais vinculados ao Tenant
            $grupos = $this->getListGrupoEmpresarial($tenant->getId());

            //Busca as permissões do Admin
            $id_sistema = $this->container->getParameter('diretorio_sistema_id');
            $lstPermissoes =  $this->tokenStorage->getToken()->getUser()->getAcoesPermitidas($tenant->getCodigo(), $id_sistema);
            $lstPermissoes = $this->fillPermissoesV3Necessarias($lstPermissoes);
            $configuracoes = $this->getConfiguracoesSistema($tenant->getId());

            $organizacao = [
                "nome" => $tenant->getNome(),
                "codigo" => $tenant->getCodigo(),
                "id" => $tenant->getId(),
                "logo" => $tenant->getLogo(),
                "gruposempresariais" => $grupos, //Propriedade adicionada com os dados do Grupo Empresarial, estabelecimento e conjuntos.
                "permissoes" => $lstPermissoes,  // Propriedade adicionada com as permissoes do usuario.
                "configuracoes" => $configuracoes // Lista com as configurações do sistema 
            ];

            $data = array_shift($organizacao['gruposempresariais']);  
            
            array_unshift($organizacao['gruposempresariais'],$data);

            if (in_array($this->container->getParameter('login_bundle.permission_type'), ['admin','grupo'])) {
                $funcao = is_array($permissao) ? strtolower(array_pop($permissao)) : $permissao;
                $organizacao["funcao"] = is_array($permissao) ? strtolower(array_pop($permissao)) : $permissao;
                //$organizacao['url'] = $this->generateUrl('index', ["tenant" => $tenant->getCodigo(), "funcao" => $funcao], UrlGeneratorInterface::ABSOLUTE_URL);
                $organizacao['url'] = $this->generateUrl('index', ["tenant" => $tenant->getCodigo(), 'grupoempresarial' => $data['codigo'] , "funcao" => $funcao], UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $organizacao['url'] = $this->generateUrl('index', ["tenant" => $tenant->getCodigo()], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            // Se a organização só possui um elemento e esse elemento é nulo, seto um array vazio nos gruposempresariais
            if (sizeof($organizacao['gruposempresariais']) == 1 && $organizacao['gruposempresariais'][0] == null) {
                $organizacao['gruposempresariais'] = [];
            } 

            $organizacoes[] = $organizacao;

            unset($tenant);
        }

        $links = [];

     
        $links['gp'] = $this->container->getParameter('gp_url');
    

       
        $profile = [
            "conta_url" => "https://conta.nasajon.com.br",
            // "logout_url" => $this->generateUrl('logout', [], UrlGeneratorInterface::ABSOLUTE_URL),
            "logout_url" => "",
            "username" => $usuario->getUsername(),
            "email" => $usuario->getEmail(),
            "nome" => $usuario->getNome(),
            "organizacoes" => $organizacoes,
            "roles" => $usuario->getRoles(),
            "links" => $links,           
        ];
        
        return new JsonResponse($profile, JsonResponse::HTTP_OK);
    }

    private function getListGrupoEmpresarial($tenant)
    {                        
            
            //$grupoempresarial = $this->tenantRepository->findConjuntosByGrupoEmpresarial($tenant);
            $grupoempresarial = $this->tenantRepository->findGruposEmpresariaisByTenant($tenant);
            $lstGruposEmpresariais = 
                    array_values(
                        array_unique(
                            array_map(function ($item) use ($grupoempresarial){
                                $grupo['grupoempresarial'] = $item['grupoempresarial'];
                                $grupo['codigo'] = $item['codigo'];
                                $grupo['descricao'] = $item['descricao'];
                                $grupo['conjuntos'] = [];
                                // array_values(
                                //     array_unique(
                                //             array_map(function ($item) use ($grupoempresarial) {
                                                
                                //                 $conjunto['conjunto'] = $item['idconjunto'];
                                //                 $conjunto['tipoconjunto'] = $item['tipoconjunto'];

                                //                 return  $conjunto;

                                //             },  array_filter($grupoempresarial, 
                                //                     function($item) use ($grupo) {
                                //                         return $item['idgrupoempresarial'] == $grupo['grupoempresarial'];
                                //                 })
                                //             )
                                //     ,SORT_REGULAR)
                                // );
                                
                                return $grupo;
                
                                        }, $grupoempresarial)
                        ,SORT_REGULAR)
                    );

        return $lstGruposEmpresariais;

    }

    private function fillPermissoesV3Necessarias($lstPermissoes)
    {
        $lstPermissoes = array_merge($lstPermissoes, [
            'ns_series_create',
            'ns_series_put',
            'ns_series_delete',
            'ns_series_index',
            'servicos_tecnicos_create',
            'servicos_tecnicos_put',
            'servicos_tecnicos_delete',
            'servicos_tecnicos_index',
            'servicos_tiposmanutencoes_create',
            'servicos_tiposmanutencoes_put',
            'servicos_tiposmanutencoes_delete',
            'servicos_tiposmanutencoes_index',
            'servicos_tiposordensservicos_create',
            'servicos_tiposordensservicos_put',
            'servicos_tiposordensservicos_delete',
            'servicos_tiposordensservicos_index',
            'servicos_tiposservicos_create',
            'servicos_tiposservicos_put',
            'servicos_tiposservicos_delete',
            'servicos_tiposservicos_index',
            'servicos_equipes_create',
            'servicos_equipes_put',
            'servicos_equipes_delete',
            'servicos_equipes_index',
            'gp_projetosetapas_gerenciar',
            'financas_projetos_templates_gerenciar',
            'servicos_ordensservicostemplates_gerenciar',
            'servicos_operacoesordensservicos_index',
            'servicos_operacoesordensservicos_get',
            'servicos_operacoesordensservicos_create',
            'servicos_operacoesordensservicos_put',
            'servicos_operacoesordensservicos_delete',
            'servicos_servicostecnicos_index',
            'servicos_servicostecnicos_get',
            'servicos_servicostecnicos_create',
            'servicos_servicostecnicos_put',
            'servicos_servicostecnicos_delete'
        ]);
        return $lstPermissoes;
    }
}
