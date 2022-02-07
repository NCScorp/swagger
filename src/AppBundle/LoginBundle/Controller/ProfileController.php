<?php
namespace AppBundle\LoginBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nasajon\LoginBundle\Controller\ProfileController as ParentController;
use AppBundle\Service\Ns\ConfiguracoesService as ConfiguracoesService;
use AppBundle\Service\PermissoesService as PermissoesService;
use AppBundle\Service\Ns\EstabelecimentosService as EstabelecimentosService;
use Nasajon\LoginBundle\Entity\Estabelecimento;
use Nasajon\LoginBundle\Entity\GrupoEmpresarial;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Enum\Persona\TipoTrabalhadorEnum;
use AppBundle\Enum\Persona\SubTipoTrabalhadorEnum;
use Nasajon\MDABundle\Service\Persona\CargosService as CargosService;
use AppBundle\Service\Persona\TrabalhadoresService as TrabalhadoresService;
use AppBundle\Repository\Ns\TenantsRepository;
use Exception;
use AppBundle\Service\Ns\TenantsService;
use Nasajon\SDK\Diretorio\DiretorioClient;
use AppBundle\Service\Web\ConfiguracoesService as WebConfiguracoesService;
use Symfony\Component\Validator\Constraints\Date;

class ProfileController extends ParentController
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \AppBundle\Service\Ns\ConfiguracoesService
     */
    protected $configuracoesService;

    /**
     * @var \AppBundle\Service\Web\ConfiguracoesService
     */
    protected $configuracoeswebService;
    
    /**
     * @var \AppBundle\Service\PermissoesService
     */
    protected $PermissoesService;

    /**
     * @var \AppBundle\Service\Ns\EstabelecimentosService
     */
    protected $estabelecimentosService;

    /**
     * @var \AppBundle\Service\Persona\TrabalhadoresService
     */
    protected $trabalhadoresService;

    /**
     * @var \AppBundle\Service\Persona\CargosService
     */
    protected $cargosService;

    /**
     * @var \AppBundle\Service\Ns\TenantsService
     */
    protected $tenantService;

    /**
     * @var \Nasajon\SDK\Diretorio\DiretorioClient
     */
    protected $sdk;

    public function __construct(EstabelecimentosService $estabelecimentosService, ConfiguracoesService $configuracoesService,TokenStorageInterface $tokenStorage, PermissoesService $permissoesService, TrabalhadoresService $trabalhadoresService, CargosService $cargosService, TenantsService $tenantService, DiretorioClient $sdk, WebConfiguracoesService $configuracoeswebService)
    {
        $this->configuracoesService = $configuracoesService;
        $this->tokenStorage = $tokenStorage;
        $this->permissoesService = $permissoesService;
        $this->estabelecimentosService = $estabelecimentosService;
        $this->trabalhadoresService = $trabalhadoresService;
        $this->cargosService = $cargosService;
        $this->tenantService = $tenantService;
        $this->sdk = $sdk;
        $this->configuracoeswebService = $configuracoeswebService;
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
            $estabelecimentos = sizeof($usuario->getTenants()[$codigo]->getGruposEmpresariais()) > 0 ? 
            $this->getListEstabelecimentos($tenant->getId(), $codigo, $usuario->getEmail()) : 
            array();
            $trabalhadores = $this->getListTrabalhadores($tenant->getId(), $usuario->getEmail(), $estabelecimentos);

            $id = $tenant->getId();
            //Busca as permissões do Admin
            $estabelecimentos = array_map(function($estabelecimento) use ($usuario, $codigo, $id) {
                $funcoes = $usuario->getFuncoesByEstabelecimento($codigo, $estabelecimento["estabelecimento"]);
                $estabelecimento["permissoes"] = $this->permissoesService->getPermissoesByFuncoes($funcoes);
                $estabelecimento['configuracoes'] = $this->configuracoeswebService->getConfiguracoesFormatadasPorEstabelecimento($id, $estabelecimento["estabelecimento"]);
                return $estabelecimento;
            }, $estabelecimentos);

            if($tenant->getGruposEmpresariais() != NULL) {
                $gruposempresariais = array_map(function($grupoempresarial){
                    return $grupoempresarial->toArray();
                }, $tenant->getGruposEmpresariais());
            } else {
                $gruposempresariais = array();
            }
            $organizacao = [
                "nome" => $tenant->getNome(),
                "codigo" => strtolower($tenant->getCodigo()),
                "id" => $tenant->getId(),
                "logo" => $tenant->getLogo(),
                "estabelecimentos" => $estabelecimentos,
                "trabalhadores" => $trabalhadores,
                "grupos_empresariais" => $gruposempresariais
            ];
            if (in_array($this->container->getParameter('login_bundle.permission_type'), ['admin','grupo'])) {
                $funcao = is_array($permissao) ? strtolower(array_pop($permissao)) : $permissao;
                $organizacao["funcao"] = is_array($permissao) ? strtolower(array_pop($permissao)) : $permissao;
                // $organizacao['url'] = $this->generateUrl('index', ["tenant" => $tenant->getCodigo(), "funcao" => $funcao], UrlGeneratorInterface::ABSOLUTE_URL);
                $organizacao['url'] = "http://localhost";
                // $organizacao['url'] = $this->generateUrl('index', ["tenant" => $tenant->getCodigo(), 'grupoempresarial' => $organizacao['gruposempresariais'][0]['codigo'], "funcao" => $funcao], UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                //$organizacao['url'] = $this->generateUrl('index', ["tenant" => $tenant->getCodigo()], UrlGeneratorInterface::ABSOLUTE_URL);
                $organizacao['url'] = "http://localhost";
            }
            $organizacoes[] = $organizacao;
            unset($tenant);
        }
        $semgrupoacesso = false;
        $contanaoencontrada = false;
        $email = $usuario->getEmail();
        $tenant_ids = array_map(function($organizacao) {
            return $organizacao["id"];
        }, $organizacoes);
        $semgrupoacesso = true;
        $trabalhadores =  $this->trabalhadoresService->getTrabalhadorByIdentificacaoNasajon($email);
        if (!$trabalhadores || count($trabalhadores) == 0) {
            $contanaoencontrada = true;
        } else {
            $tenantspesquisados = [];
            foreach ($trabalhadores as $cod => $trab) {
                if(!in_array($trab['codigotenant'],$tenantspesquisados)) {
                    $codigo = $trab['codigotenant'];
                    $tenantencontrado = $this->tenantService->getRetornoProfile($codigo);
                    $ids = [];
                    $retorno = $this->sdk->getInformacoesTenant($tenantencontrado['id']);
                    $tenantencontrado['logo'] = $retorno['logo'];
                    $tenantencontrado['nome'] = $retorno['nome'];
                    foreach ($tenantencontrado['gruposempresariais'] as $cod => $grupo) {
                        foreach ($grupo['empresas'] as $codemp => $empresa) {
                            $ids = array_merge (array_map(function($estabelecimento) {
                                return $estabelecimento['id'];
                            }, $empresa['estabelecimentos']), $ids);
                        }
                    }
                    $estabelecimentos = sizeof($tenantencontrado['gruposempresariais']) > 0 ? 
                    $this->getListEstabelecimentosV2($tenantencontrado['id'], $codigo, $email, $ids) : 
                    array();
                    $trabalhadoresarray = $this->getListTrabalhadores($tenantencontrado['id'], $email, $estabelecimentos);
                    $id = $tenantencontrado['id'];
                    $estabelecimentos = array_map(function($estabelecimento) use ($usuario, $codigo, $id) {
                        $estabelecimento["permissoes"] = [];
                        $estabelecimento["configuracoes"] = $this->configuracoeswebService->getConfiguracoesFormatadasPorEstabelecimento($id, $estabelecimento["estabelecimento"]);
                        return $estabelecimento;
                    }, $estabelecimentos);
                    $organizacao = [
                        "nome" => $tenantencontrado['nome'],
                        "codigo" => strtolower($tenantencontrado['codigo']),
                        "id" => $tenantencontrado['id'],
                        "logo" => $tenantencontrado['logo'],
                        "estabelecimentos" => $estabelecimentos,
                        "trabalhadores" => $trabalhadoresarray,
                        "grupos_empresariais" => $tenantencontrado['gruposempresariais']
                    ];
                    $tenantspesquisados[] = $trab['codigotenant'];
                    if(array_search($tenantencontrado['id'],$tenant_ids) === false){
                        $organizacoes[] = $organizacao;
                    }
                    unset($tenant);    
                }
            }
        }
        
    
        $profile = [
            "conta_url" => "https://conta.nasajon.com.br",
            // "logout_url" => $this->generateUrl('logout', [], UrlGeneratorInterface::ABSOLUTE_URL),
            "logout_url" => "",
            "username" => $usuario->getUsername(),
            "email" => $usuario->getEmail(),
            "nome" => $usuario->getNome(),
            "organizacoes" => $organizacoes,
            "roles" => $usuario->getRoles(),
            "semgrupoacesso" => $semgrupoacesso,
            "contanaoencontrada" => $contanaoencontrada
        ];
        
        return new JsonResponse($profile, JsonResponse::HTTP_OK);
    }

    private function getListEstabelecimentos($tenant, $codigo, $contanasajon)
    {
        try {

            $estabelecimentos_id = array_map(function($estabelecimento){
                return $estabelecimento->getId();
            }, $this->getUser()->getEstabelecimentosPermitidos($codigo));

            $estabelecimentos = $this->estabelecimentosService->findEstabelecimentosByEmailTrabalhador($tenant, $contanasajon, $estabelecimentos_id);
            $lstEstabelecimentos =
            array_values(
                array_unique(
                    array_map(function ($item) use ($tenant){
                        $estabelecimento['estabelecimento'] = $item['estabelecimento'];
                        $estabelecimento['nomefantasia'] = $item['nomefantasia'];
                        $estabelecimento['modulosHabilitados'] = $this->configuracoesService->estabelecimentoModulosHabilitados($tenant, $item['estabelecimento']);
                        return $estabelecimento;
                        
                    }, $estabelecimentos)
                , SORT_REGULAR)
            );
            
            return $lstEstabelecimentos;  
        } catch(Exception $e) {
            return array();
        }
    }


    private function getListEstabelecimentosV2($tenant, $codigo, $contanasajon, $estabelecimentospermitidos)
    {
        try {
            $ids = array_map(function($estabelecimento){
                return $estabelecimento;
            }, $estabelecimentospermitidos);
            $estabelecimentos = $this->estabelecimentosService->findEstabelecimentosByEmailTrabalhador($tenant, $contanasajon, $ids);
            $lstEstabelecimentos =
            array_values(
                array_unique(
                    array_map(function ($item) use ($tenant){
                        $estabelecimento['estabelecimento'] = $item['estabelecimento'];
                        $estabelecimento['nomefantasia'] = $item['nomefantasia'];
                        $estabelecimento['modulosHabilitados'] = $this->configuracoesService->estabelecimentoModulosHabilitados($tenant, $item['estabelecimento']);
                        return $estabelecimento;
                        
                    }, $estabelecimentos)
                , SORT_REGULAR)
            );
            
            return $lstEstabelecimentos;  
        } catch(Exception $e) {
            return array();
        }
    }

    private function getListTrabalhadores($tenant, $contanasajon, $estabelecimentos) {
        try {
            $estabelecimentos = array_values(
                array_unique(
                    array_map(function ($item) {
                        $estabelecimento = $item['estabelecimento'];
                        return $estabelecimento;
                        
                    }, $estabelecimentos)
                , SORT_REGULAR)
            );

            $trabalhadores = $this->estabelecimentosService->findEstabelecimentosByEmailTrabalhador($tenant, $contanasajon, $estabelecimentos);
            $lstTrabalhadores = 
            array_values(
                array_unique(
                    array_map(function ($item) use ($tenant) {
                        $trabalhador['estabelecimento'] = $item['estabelecimento'];
                        $trabalhador['trabalhador'] = $item['trabalhador'];
                        $infoTrabalhador = $this->trabalhadoresService->getInfoTrabalhador($item['trabalhador'], $tenant);
                        
                        if ($infoTrabalhador['tipo'] !== null) {
                            $trabalhador['cargoinfo'] = "{$infoTrabalhador['cargo_nome']} {$infoTrabalhador['nivelcargo_nome']} (".TipoTrabalhadorEnum::TIPOS[$infoTrabalhador['tipo']].")";
                        }

                        if ($infoTrabalhador['subtipo'] !== null) {
                            $trabalhador['cargoinfo'] = "{$infoTrabalhador['cargo_nome']} {$infoTrabalhador['nivelcargo_nome']} (".TipoTrabalhadorEnum::TIPOS[$infoTrabalhador['tipo']]." - ".SubTipoTrabalhadorEnum::SUBTIPOS[$infoTrabalhador['subtipo']].")";
                        }

                        if (!is_null($infoTrabalhador['datarescisao'])) {
                            $dataRescisao = new \DateTimeImmutable($infoTrabalhador['datarescisao']);
                            $trabalhador['cargoinfo'] .= ($dataRescisao > new \DateTimeImmutable()) ? "" : " - Rescindido";
                        }
            
                        $trabalhador['dataadmissao'] = $infoTrabalhador['dataadmissao'];
                        $trabalhador['datarescisao'] = $infoTrabalhador['datarescisao'];

                        return $trabalhador;
            
                    }, $trabalhadores),
                    SORT_REGULAR
                )
            );

            usort($lstTrabalhadores, function($a, $b)
            {   
                if (is_null($a['datarescisao']) && is_null($b['datarescisao'])) {
                    return 0;
                }
                else if (!is_null($a['datarescisao']) && is_null($b['datarescisao'])) {
                    return 1;
                } else if (is_null($a['datarescisao']) && !is_null($b['datarescisao'])) {
                    return -1;
                } else {
                    return ($a['datarescisao'] > $b['datarescisao']) ? -1 : 1;
                }
            });

            return $lstTrabalhadores;
        } catch(Exception $e) {
            return array();
        }
    }
}