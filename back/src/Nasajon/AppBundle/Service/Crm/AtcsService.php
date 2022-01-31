<?php

namespace Nasajon\AppBundle\Service\Crm;

use LogicException;
use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\MDABundle\Entity\Ns\Enderecos;
use Nasajon\MDABundle\Entity\Crm\Composicoes;
use Nasajon\MDABundle\Entity\Financas\Contas;
use Nasajon\MDABundle\Entity\Crm\Historicoatcs;
use Nasajon\MDABundle\Entity\Financas\Projetos;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Entity\Financas\Contratos;
use Nasajon\MDABundle\Entity\Financas\Itenscontratos;
use Nasajon\MDABundle\Entity\Ns\Historicosatcsanexos;
use Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidosanexos;
use Nasajon\MDABundle\Service\Crm\AtcsService as ParentService;
use Nasajon\MDABundle\Form\Crm\PropostasitensfamiliasDefaultType;
use Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes;
use Nasajon\MDABundle\Entity\Crm\Responsabilidadesfinanceirasvalores;
use Nasajon\MDABundle\Entity\Crm\Templatespropostascapituloscomposicoes;
use Nasajon\AppBundle\Entity\Crm\TemplatespropostascapituloscomposicoesBulk;

/**
 * Sobrescrito por causa dos camposcustomizados e nome do local na tabela endereços e da view de cidades
 */
class AtcsService extends ParentService {

    public $crmAtcspendenciaslistas; 
    public $crmAtcspendencias;
    public $clientesdocumentosService;
    public $fornecedoresdocumentosService;
    public $atcstiposdocumentosrequisitantesService;
    public $tiposdocumentosService;
    public $financasContratosService;
    public $financasProjetosService;
    public $propostascapitulosService;
    protected $fornecedoresService;
    protected $crmResponsabilidadesFinanceirasService;

    /**
     * 
     * @var \Nasajon\MDABundle\Service\Web\ConfiguracoesService
     */
    private $wbCnfgrcsSrvc;

    private $container;
    private $tenantsRepository;
    private $crmPropostaItemService;


    /**
     * 
     * @var \Nasajon\MDABundle\Repository\Crm\ResponsabilidadesfinanceirasvaloresRepository;
     */
    protected $responsabilidadesfinanceirasvaloresRepository;

    protected $JMSSerializer;
    protected $documentosFopService;
    protected $crmFornecedoresEnvolvidosRepository;
    protected $crmFornecedoresEnvolvidosAnexosService;
    protected $nsDocumentosfopService;
    protected $envioEmailService;
    protected $atcsconfiguracoestemplatesemailsService;
    protected $googleMapsService;
    protected $crmTemplatePropostaCapituloSrvc;
    protected $crmTemplatePropostaCapituloComposicaoSrvc;
    protected $crmOrcamentosService;
    protected $financasItenscontratosService;
    protected $crmConfiguracoestaxasadministrativasService;
    protected $servicosItensdefaturamentoService;

    protected $atcsRelatoriosService;
    protected $atcsRelatoriosRpsService;

    /**
     * Service de upload de arquivos
     */
    protected $uploadFilesService;
    protected $fixedAttribute;

    protected $adapter;
    protected $s3BucketPath;

    protected $historicosatcsanexosService;

    /**
     * Sobrescrito para incluir chamada ao camposcustomizados service etc
     * @param \Nasajon\MDABundle\Repository\Crm\AtcsRepository $repository
     * @param type $crmTcsrspnsvsfnncrsSrvc
     * @param type $cmpscstmzdsSrvc
     */
    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\AtcsRepository $repository, 
        $crmHstrctcsSrvc, 
        $crmTcsdcmntsSrvc, 
        $crmTcsrspnsvsfnncrsSrvc, 
        $crmPrpstsSrvc, 
        $cmpscstmzdsSrvc, 
        $wbCnfgrcsSrvc, 
        \Nasajon\MDABundle\Service\Crm\AtcsdadosseguradorasService $crmTcsddssgrdrs, 
        $crmAtcspendenciaslistas, 
        $crmAtcspendencias, 
        $clientesdocumentosService, 
        $fornecedoresdocumentosService, 
        $atcstiposdocumentosrequisitantesService, 
        $tiposdocumentosService, 
        $financasContratosService, 
        $financasProjetosService, 
        $propostascapitulosService,
        $container,
        $tenantsRepository, 
        $fornecedoresService,
        $crmResponsabilidadesFinanceirasService,
        $crmResponsabilidadesFinanceirasValoresService,
        $crmPropostaItemService,
        $responsabilidadesfinanceirasvaloresRepository,
        $atcsRelatoriosService,
        $atcsRelatoriosRpsService,
        $documentosFopService,
        $crmFornecedoresEnvolvidosRepository,
        $crmFornecedoresEnvolvidosAnexosService,
        $nsDocumentosfopService,
        $JMSSerializer,
        $envioEmailService,
        \Nasajon\MDABundle\Service\Crm\AtcsconfiguracoestemplatesemailsService $atcsconfiguracoestemplatesemailsService,
        $googleMapsService,
        $crmTemplatePropostaCapituloSrvc,
        $crmTemplatePropostaCapituloComposicaoSrvc,
        $uploadFilesService,
        $crmOrcamentosService,
        $financasItenscontratosService,
        $crmConfiguracoestaxasadministrativasService,
        $servicosItensdefaturamentoService,
        $fixedAttribute,
        $adapter,
        $s3BucketPath,
        $historicosatcsanexosService
    ) {
        //parent::__construct($repository, $crmHstrctcsSrvc, $crmTcsdcmntsSrvc, $crmTcsrspnsvsfnncrsSrvc, $crmPrpstsSrvc );
        $this->repository = $repository;
        $this->crmHstrctcsSrvc = $crmHstrctcsSrvc;
        $this->crmTcsdcmntsSrvc = $crmTcsdcmntsSrvc;
        $this->crmTcsrspnsvsfnncrsSrvc = $crmTcsrspnsvsfnncrsSrvc;
        $this->cmpscstmzdsSrvc = $cmpscstmzdsSrvc;
        $this->wbCnfgrcsSrvc = $wbCnfgrcsSrvc;
        $this->crmPrpstsSrvc = $crmPrpstsSrvc;
        $this->crmTcsddssgrdrs = $crmTcsddssgrdrs;
        $this->crmAtcspendencias = $crmAtcspendencias;
        $this->clientesdocumentosService = $clientesdocumentosService;
        $this->fornecedoresdocumentosService = $fornecedoresdocumentosService;
        $this->atcstiposdocumentosrequisitantesService = $atcstiposdocumentosrequisitantesService;
        $this->tiposdocumentosService = $tiposdocumentosService;
        $this->financasContratosService = $financasContratosService;
        $this->financasProjetosService = $financasProjetosService;
        $this->propostascapitulosService = $propostascapitulosService;
        $this->container = $container;
        $this->tenantsRepository = $tenantsRepository;
        $this->fornecedoresService = $fornecedoresService;
        $this->crmResponsabilidadesFinanceirasService = $crmResponsabilidadesFinanceirasService;
        $this->crmResponsabilidadesFinanceirasValoresService = $crmResponsabilidadesFinanceirasValoresService;
        $this->crmPropostaItemService = $crmPropostaItemService;
        $this->responsabilidadesfinanceirasvaloresRepository = $responsabilidadesfinanceirasvaloresRepository;
        $this->atcsRelatoriosService = $atcsRelatoriosService;
        $this->atcsRelatoriosRpsService = $atcsRelatoriosRpsService;
        $this->documentosFopService = $documentosFopService;
        $this->crmFornecedoresEnvolvidosRepository = $crmFornecedoresEnvolvidosRepository;
        $this->crmFornecedoresEnvolvidosAnexosService = $crmFornecedoresEnvolvidosAnexosService;
        $this->nsDocumentosfopService = $nsDocumentosfopService;
        $this->JMSSerializer = $JMSSerializer;
        $this->envioEmailService = $envioEmailService;
        $this->atcsconfiguracoestemplatesemailsService = $atcsconfiguracoestemplatesemailsService;
        $this->googleMapsService = $googleMapsService;
        $this->crmTemplatePropostaCapituloSrvc = $crmTemplatePropostaCapituloSrvc;
        $this->crmTemplatePropostaCapituloComposicaoSrvc = $crmTemplatePropostaCapituloComposicaoSrvc;
        $this->uploadFilesService = $uploadFilesService;
        $this->crmOrcamentosService = $crmOrcamentosService;
        $this->financasItenscontratosService = $financasItenscontratosService;
        $this->crmConfiguracoestaxasadministrativasService = $crmConfiguracoestaxasadministrativasService;
        $this->servicosItensdefaturamentoService = $servicosItensdefaturamentoService;
        $this->fixedAttribute = $fixedAttribute;
        $this->adapter = $adapter;
        $this->s3BucketPath = $s3BucketPath;
        $this->historicosatcsanexosService = $historicosatcsanexosService;
    }

    //Metodo para converter número do status para string
    private function getStatus($entidade)
    {
        if (isset($entidade['status'])) {
            switch ($entidade['status']) {
            case 0:
                return 'Novo';
            case 1:
                return 'Em Atendimento';
            case 2:
                return 'Processando';
            case 3:
                return 'Finalizado';
            case 4:
                return 'Cancelado';
            }
        }
    }

    private function findOtimizado($id, $tenant, $id_grupoempresarial) {
        $data = $this->getRepository()->find($id, $tenant, $id_grupoempresarial);
        $data['propostas'] = $this->crmPrpstsSrvc->findAll($tenant, $id, $id_grupoempresarial);
        return $data;
    }

    /**
     * Sobrescrito para passar no nome do local (notmapped em negócio) o valor do campo nome, pais, cidade e estado vindos de da view de cidades e ns.enderecos
     * @param string $id
     * @param mixed $tenant
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $id_grupoempresarial)
    {
        $data = $this->getRepository()->find($id, $tenant, $id_grupoempresarial);

        if ($data['localizacaonome'] == '') {
            $data['localizacaonome'] = isset($data['localizacao']['nome']) ? $data['localizacao']['nome'] : null;
        }
        $data['statusLabel'] = $this->getStatus($data);
        $data['responsaveisfinanceiros'] = $this->crmTcsrspnsvsfnncrsSrvc->findAll($tenant, $id, $id_grupoempresarial);
        $data['configuracao'] = $this->wbCnfgrcsSrvc->getConfiguracao($tenant);
        $data['historiconegocios'] = $this->crmHstrctcsSrvc->findAll($tenant, $id);
        $data['propostas'] = $this->crmPrpstsSrvc->findAll($tenant, $id, $id_grupoempresarial);
        $data['negociostiposdocumentosrequisitantes'] = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $id, $id_grupoempresarial);
        //Caso o atendimento possua um negócio relacionado, adiciono o campo de rota para a edição do projeto
        if (isset($data['projeto']) && isset($data['projeto']['projeto'])) {
            $data['projeto_href'] = $this->montaRotaEdicaoProjeto($tenant, $data['projeto']['projeto']);
        }
        $data['negociosdadosseguradoras'] = $this->crmTcsddssgrdrs->findAll($id, $tenant, $id_grupoempresarial);
        return $data;
    }

    /**
     * Monta rota para a edição do projeto no sistema Gestão de Projetos
     */
    private function montaRotaEdicaoProjeto($tenant, $projeto, $tenantCompleto = null){
        $host_gp = '';

        try {
            $host_gp = $this->container->getParameter('gp_url');
        } catch (\Exception $e) {
            throw new \Exception("URL de integração com o GP não configurada.", 1);
        }

        if ($tenantCompleto === null ) {
          $tenantCompleto = $this->tenantsRepository->findOneByTenant($tenant);
        }

        // $projeto_href = 'https://' . $host_gp . '/' . $tenantCompleto['codigo'] . '/gp_projetos/' . $projeto . '/edit';
        $projeto_href = $host_gp . '/' . $tenantCompleto['codigo'] . '/gp_projetos/' . $projeto . '/edit';

        return $projeto_href;
    }

    /**
     * Sobrescrito para incluir na listagem os campos customizados
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
    {

        $this->getRepository()->validateOffset($filter);

        $camposcustomizados_filter = new Filter();
        $camposcustomizados_filter_expression = new FilterExpression('crmnegocioexibenalistagem', 'eq', 'true');
        $camposcustomizados_filter->addToFilterExpression($camposcustomizados_filter_expression);
        $camposcustomizados =  $this->cmpscstmzdsSrvc->findAll($tenant, $camposcustomizados_filter);

        $tenantCompleto = $this->tenantsRepository->findOneByTenant($tenant);

        $atcs = array_map(function ($atc) use ($camposcustomizados, $tenant, $tenantCompleto) {

            foreach ($camposcustomizados as $campo) {
                $campo_valor = !empty($atc['camposcustomizados'][$campo['campocustomizado']]) ? $atc['camposcustomizados'][$campo['campocustomizado']] : null;
                if ($campo['tipo'] == 'OB') {
                    foreach ($campo['objeto'] as $atributo) {
                        if (!empty($atributo['crmnegocioexibenalistagem']) && $atributo['crmnegocioexibenalistagem'] == 'true') {
                            $atc[$campo['nome'] . $atributo['nome']] = !empty($campo_valor[$atributo['nome']]) ? $campo_valor[$atributo['nome']] : null;
                        }
                    }
                } else {
                    $atc[$campo['nome']] = !empty($campo_valor) ? $campo_valor : null;
                }
            }

            //Caso o atendimento possua um negócio relacionado, adiciono o campo de rota para a edição do projeto
            if (isset($atc['projeto']) && isset($atc['projeto']['projeto'])) {
                $atc['projeto_href'] = $this->montaRotaEdicaoProjeto($tenant, $atc['projeto']['projeto'], $tenantCompleto);
            }

            return $atc;
        }, $this->getRepository()->findAll($tenant, $id_grupoempresarial, $filter));
        return $atcs;
    }

    /**
     * Persiste o objectlist dos dados da seguradora
     */
    protected function persistChildAtcsdadosseguradoras($oldList, $newList, $entity, $logged_user, $tenant) {
        if(!$oldList){
            $oldList = [];
        }
        $newIds = array_map(function ($entity) {
            return $entity->getNegociodadosseguradora();
        }, $newList);
        
        while ($item = array_pop($oldList)) {
            $item->setSeguradora(EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Clientes', $item->getSeguradora()));
            $item->setProdutoseguradora(EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Templatespropostasgrupos', $item->getProdutoseguradora()));
            $item->setApolice(EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Templatespropostas', $item->getApolice()));
            $item->setTitularvinculo(EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Vinculos', $item->getTitularvinculo()));
            
            $id = $item->getNegociodadosseguradora();
            $index = array_search($id, $newIds);
            if ($index === false) {
                $this->crmTcsddssgrdrs->delete($tenant, $entity->getIdGrupoempresarial(), $item);
            } else {
                $newitem = $newList[$index];
                array_splice($newList, $index, 1);
                array_splice($newIds, $index, 1);
                $this->crmTcsddssgrdrs->update($logged_user, $tenant, $entity->getIdGrupoempresarial(), $newitem, $item);
            }

            unset($index);
            unset($item);
            unset($newitem);
        }

        foreach ($newList as $item) {
            $this->crmTcsddssgrdrs->insert($entity->getNegocio(), $logged_user, $tenant, $entity->getIdGrupoempresarial(), $item);
        }
    }

    /**
     * Sobrescrito por causa dos camposcustomizados
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * @throws \Exception
     */
    public function insert($logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcs $entity)
    {
        try {
            $this->getRepository()->begin();

            /* sobrescrito */
            $entity->setCamposcustomizados(json_decode($entity->getCamposcustomizados()));
            /* ----  */

            $response = $this->getRepository()->insert($logged_user, $tenant, $id_grupoempresarial, $entity);
            $this->persistChildResponsaveisfinanceiros(null, $entity->getResponsaveisfinanceiros()->toArray(), $entity, $logged_user, $tenant, $id_grupoempresarial);

            $atcId = $response['negocio'];

            if ($entity->getPossuiseguradora()) {
                $this->persistChildAtcsdadosseguradoras(null, $entity->getNegociosdadosseguradoras()->toArray(), $entity, $logged_user, $tenant);
            }
            /* --- */

            /* Sobrescrito para  copiar as listas de pendência da área de negócio ao qual pertence */

            /* Salva listas de pendencias pre definidas na área de negócio, no negócio */
            // $negocioarea = is_object($entity->getArea()) ? $entity->getArea()->getNegocioarea() : $entity->getArea();
            // $pendenciaslistas = $this->crmAtcsareaspendenciaslistas->findAll($tenant, $negocioarea);
            // $this->copiaListasPendenciasAreaAtc($logged_user, $tenant, $negocioId, $pendenciaslistas);
            /* COMENTADO POIS NAO UTILIZAMOS MAIS--- */

            /* Sobrescrito para criar automaticamente capitulo nomeado "Pedidos" após criação de Negócio */
            $propostas = $this->findOtimizado($entity->getNegocio(), $tenant, $id_grupoempresarial);
            $entity->setCodigo($propostas['codigo']); //Seta o código pois agora ele é automatico, vem direto do banco.
            $propostas = $propostas['propostas'][0]['proposta'];
            $propostaCapitulo = new \Nasajon\MDABundle\Entity\Crm\Propostascapitulos();
            $propostaCapitulo->setNome('Pedido');
            //Capítulo criado automaticamente é o capítulo principal do atendimento
            $propostaCapitulo->setPrincipal(True);
            $propostaCapitulo = $this->propostascapitulosService->insert($propostas, $logged_user, $tenant, $id_grupoempresarial, $propostaCapitulo);
            /* --- */

            // Se possui seguradora, crio itens do pedido(propostaitens, propostaitensfuncoes e propostasitensfamilias) 
            // a partir dos dados da primeira apólice
            if ($entity->getPossuiseguradora()) {
                $arrDadosSeguradora = $entity->getNegociosdadosseguradoras()->toArray();
                $dadoSeguradoraBase = $arrDadosSeguradora[0];
                // Pego a apólice(templateproposta) do primeiro item dos dados da seguradora
                $templateProposta = $dadoSeguradoraBase->getApolice();

                // Crio objeto de criação de itens em lote
                $objCriacaoItensPelaApolice = new TemplatespropostascapituloscomposicoesBulk();

                // Busco templatespropostascapitulos da apólice(templateproposta)
                $arrTemplatePropostaCapitulo = $this->crmTemplatePropostaCapituloSrvc->findAll($tenant, $id_grupoempresarial, $templateProposta->getTemplateproposta());

                // Para cada templatepropostacapitulo, busco templatespropostascapituloscomposicoes
                foreach ($arrTemplatePropostaCapitulo as $templatePropostaCapitulo) {
                    $arrTemplatePropostaCapituloComposicao = $this->crmTemplatePropostaCapituloComposicaoSrvc->findAll($tenant, $id_grupoempresarial, $templatePropostaCapitulo['templatepropostacapitulo']);

                    // Para cada template de composição do capítulo, crio objeto de template composição à lista de criação
                    foreach ($arrTemplatePropostaCapituloComposicao as $templatePropostaCapituloComposicao) {
                        $objTemplateComposicao = new Templatespropostascapituloscomposicoes();
                        $objTemplateComposicao->setTemplateproposta($templateProposta->getTemplateproposta());
                        $objTemplateComposicao->setTemplatepropostagrupo($dadoSeguradoraBase->getProdutoseguradora()->getTemplatepropostagrupo());
                        $objTemplateComposicao->setTemplatepropostacomposicao($templatePropostaCapituloComposicao['templatepropostacomposicao']);
                        $objTemplateComposicao->setTemplatepropostacapitulo($templatePropostaCapitulo['templatepropostacapitulo']);
                        $objTemplateComposicao->setNome($templatePropostaCapituloComposicao['composicao']['nome']);
                        $objTemplateComposicao->setDescricao($templatePropostaCapituloComposicao['composicao']['descricao']);

                        $objComposicao = new Composicoes();
                        $objComposicao->setComposicao($templatePropostaCapituloComposicao['composicao']['composicao']);
                        $objTemplateComposicao->setComposicao($objComposicao);

                        // Adiciono templatecomposição
                        $objCriacaoItensPelaApolice->addTemplatescomposicoes($objTemplateComposicao);
                    }
                }

                // Chamo função que cria os itens do pedido, caso possua algum item a ser criado
                if (count($objCriacaoItensPelaApolice->getTemplatescomposicoes()) > 0) {
                    $this->crmPropostaItemService->templateGeraItensLote($atcId, $propostas, $logged_user, $tenant,  $id_grupoempresarial, $objCriacaoItensPelaApolice);
                }
            }

            /* Sobrescrito para salvar tipos de documentos pedidos pelo cliente/fornecedor/apólice */
            $requisitanteCliente = $entity->getCliente();
            $tiposDocumentosCliente = $this->clientesdocumentosService->findAll($tenant, $requisitanteCliente->getCliente(), $id_grupoempresarial, null);
            $this->salvarDocumentosRequisitante($tenant, $logged_user, $atcId, $requisitanteCliente, $id_grupoempresarial, $tiposDocumentosCliente);

            //Setando a localização após ter feito o insert para solucionar o problema do endereço duplicado
            if ($entity->getLocalizacaosalvar() == '2') {
                try {

                    $entityComLocalizacao = $this->find($entity->getNegocio(), $tenant, $id_grupoempresarial);
                    $endereco = new Enderecos();
                    $endereco->setEndereco($entityComLocalizacao['localizacao']['endereco']);
                    $entity->setLocalizacao($endereco);
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw new LogicException($e->getMessage());
        }
    }

    /**
     * Método que copia a lista de pendências (com as pendencias) pre definidas na área de negócio ao criar um negócio
     * @param string  $logged_user 
     * @param string  $tenant
     * @param string  $atc id do negócio ao qual a lista irá pertencer
     * @param array  $pendenciaslistas lista de pendencias que será copiada
     */
    private function copiaListasPendenciasAreaAtc($logged_user, $tenant, $atc, $pendenciaslistas)
    {
        array_map(function ($lista) use ($logged_user, $tenant, $atc) {

            $listapendencia = new \Nasajon\MDABundle\Entity\Crm\Atcspendenciaslistas();
            $listapendencia->setNome($lista['nome']);
            $response = $this->crmAtcspendenciaslistas->insert($atc, $logged_user, $tenant, $listapendencia);
            $atcpendencialistaId = $response['negociopendencialista'];

            $pendencias = $this->crmAtcsareaspendencias->findAll($tenant, $lista['negocioareapendencialista']);
            array_map(function ($pendencia) use ($logged_user, $tenant, $atcpendencialistaId) {

                $pendenciaatc = new \Nasajon\MDABundle\Entity\Crm\Atcspendencias();
                $pendenciaatc->setTexto($pendencia['texto']);
                $this->crmAtcspendencias->insert($atcpendencialistaId, $logged_user, $tenant, $pendenciaatc);
            }, $pendencias);
        }, $pendenciaslistas);
    }

  /**
   * Sobrescrito por causa dos camposcustomizados
   * @param string  $logged_user
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
   */
  /*-Sobrescrito, flag com default true gera histórico do negócio, caso coloque false, não gera-*/
  public function update($logged_user, $tenant, \Nasajon\MDABundle\Entity\Crm\Atcs $entity, $originalEntity = NULL, $flaghistorico = true) {
    try {
      $this->getRepository()->begin();

      /* sobrescrito */
      $entity->setCamposcustomizados(json_decode($entity->getCamposcustomizados()));
      /* sobrescrito para flaghistorico estar como true por default*/
      $entity->setFlaghistorico($flaghistorico);
      /* ----  */
      $response = $this->getRepository()->update($logged_user, $tenant, $entity, $originalEntity);
      $this->persistChildResponsaveisfinanceiros($originalEntity->getResponsaveisfinanceiros()->toArray(), $entity->getResponsaveisfinanceiros()->toArray(), $entity, $logged_user, $tenant, $entity->getIdGrupoempresarial());

      //blocos abaixo comentados devido a criação ou edicao de dados da seguradora no negocio ser realizado agora via api propria
      /* Sobrescrito para salvar automaticamente os dados referentes a seguradora */
      // if($entity->getPossuiseguradora()) {
      //   $dadoSeguro = new \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras();
      //   $dadoSeguro->setNegocio($entity->getNegocio());
      //   $dadoSeguro->setSeguradora($entity->getCliente());
      //   $dadoSeguro->setSinistro($entity->getSeguradorasinistro());
      //   $dadoSeguro->setProdutoseguradora($entity->getSeguradoraprodutoseguradora());
      //   $dadoSeguro->setApolice($entity->getSeguradoraapolice());
      //   $dadoSeguro->setApoliceconfirmada($entity->getSeguradoraapoliceconfirmada());
      //   $dadoSeguro->setApolicetipo($entity->getSeguradoraapolicetipo());
      //   $dadoSeguro->setTitularcontatos($entity->getSeguradoratitularcontatos());
      //   $dadoSeguro->setTitularcnpj($entity->getSeguradoratitularcnpj());
      //   $dadoSeguro->setTitularcpf($entity->getSeguradoratitularcpf());
      //   $dadoSeguro->setTitulartipodocumento($entity->getSeguradoratitulartipodocumento());
      //   $dadoSeguro->setTitularnome($entity->getSeguradoratitularnome());
      //   $dadoSeguro->setTitularvinculo($entity->getSeguradoratitularvinculo());
      // }
      // if($entity->getPossuiseguradora() && !$entity->getSeguradoranegociodadosseguradora()){ //cria dados seguradora
      //   $this->crmTcsddssgrdrs->insert($logged_user, $tenant, $dadoSeguro);
      // } else if ($entity->getPossuiseguradora() && $entity->getSeguradoranegociodadosseguradora()) { //edita dados seguradora
      //   $dadoSeguro->setNegociodadosseguradora($entity->getSeguradoranegociodadosseguradora());
      //   $this->crmTcsddssgrdrs->update($logged_user, $tenant, $dadoSeguro);
      // } else if(!$entity->getPossuiseguradora() && $entity->getSeguradoranegociodadosseguradora()) { //deleta dados seguradora
      //   $dadoSeguro = new \Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras();
      //   $dadoSeguro->setNegociodadosseguradora($entity->getSeguradoranegociodadosseguradora());
      //   $this->crmTcsddssgrdrs->delete($tenant, $dadoSeguro);
      // }
      /* --- */

      /* Verifica se o cliente mudou e muda os negociostiposdocumentosrequisitantes de acordo */
      $mudouRequisitante = false;
      if(isset($originalEntity) && $originalEntity->getCliente()->getCliente() !== $entity->getCliente()->getCliente()){
        $mudouRequisitante = true;
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('requisitantecliente.cliente', 'eq', $originalEntity->getCliente()->getCliente()));
        $documentosParaExcluir = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $originalEntity->getNegocio(), $entity->getIdGrupoempresarial(), $filter);
        $this->excluirDocumentosRequisitantes
          ($tenant, $entity->getIdGrupoempresarial(), $documentosParaExcluir);
        
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('requisitantecliente.cliente', 'eq', $entity->getCliente()->getCliente()));
        $novosDocumentos = $this->clientesdocumentosService->findAll($tenant, $entity->getCliente()->getCliente(), $entity->getIdGrupoempresarial(), $filter);
        $this->salvarDocumentosRequisitante($tenant, $logged_user, $entity->getNegocio(), $entity->getCliente(), $entity->getIdGrupoempresarial(), $novosDocumentos);
      }

      //exclui documentos cujo tipos são diferentes daqueles esperados pelos requisitantes
      if($mudouRequisitante){
        $this->apagarDocumentosNaoMaisEsperados($tenant, $entity->getNegocio(), $entity->getIdGrupoempresarial());
      }
      /* --- */

        // Persiste informações da seguradora
        $newListDadosSeguradora = $entity->getNegociosdadosseguradoras()->toArray();

        // Se trocou de seguradora para cliente, apagar lista de dados da seguradora
        if (!$entity->getPossuiseguradora()) {
            $newListDadosSeguradora = [];
        }

        $this->persistChildAtcsdadosseguradoras($originalEntity->getNegociosdadosseguradoras()->toArray(), $newListDadosSeguradora, $entity, $logged_user, $tenant);

      $this->getRepository()->commit();

      return $response;
    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      throw new LogicException($e->getMessage());
    }
  }

    /**
     * Salva uma lista de documentos requisitados por um cliente/fornecedor/apólice
     */
    private function salvarDocumentosRequisitante($tenant, $logged_user, $atcId, $requisitante, $id_grupoempresarial, $documentos)
    {
        array_map(function ($documento) use ($logged_user, $tenant, $atcId, $requisitante, $id_grupoempresarial) {
            $obj = $this->montaEntidadeAtcsTiposDocumentosRequisitantes($tenant, $atcId, $requisitante, $id_grupoempresarial, $documento);
            $this->atcstiposdocumentosrequisitantesService->insert($atcId, $tenant, $logged_user, $id_grupoempresarial, $obj);
        }, $documentos);
    }

    /**
     * Monta uma entidade do tipo Atcstiposdocumentosrequisitantes baseado
     * @param $tenant 
     * @param array $dadosDocumento Array com dados de: Fornecedoresdocumentos ou Clientesdocumentos
     * @param string $atc id do negócio
     * @return Atcstiposdocumentosrequisitantes
     */
    private function montaEntidadeAtcsTiposDocumentosRequisitantes($tenant, $atc, $requisitante, $id_grupoempresarial, $dadosDocumento)
    {
        $obj = new Atcstiposdocumentosrequisitantes();
        isset($dadosDocumento['tipodocumento']) ? $obj->setTipodocumento($this->tiposdocumentosService->fillEntity($dadosDocumento['tipodocumento'])) : null;
        isset($dadosDocumento['copiasimples']) ? $obj->setCopiasimples($dadosDocumento['copiasimples']) : null;
        isset($dadosDocumento['copiaautenticada']) ? $obj->setCopiaautenticada($dadosDocumento['copiaautenticada']) : null;
        isset($dadosDocumento['original']) ? $obj->setOriginal($dadosDocumento['original']) : null;
        isset($dadosDocumento['permiteenvioemail']) ? $obj->setPermiteenvioemail($dadosDocumento['permiteenvioemail']) : null;
        isset($dadosDocumento['tipodocumento']) ? $obj->setTenant($tenant) : null;
        isset($dadosDocumento['id_grupoempresarial']) ? $obj->setIdGrupoempresarial($id_grupoempresarial) : null;
        isset($dadosDocumento['naoexibiremrelatorios']) ? $obj->setNaoexibiremrelatorios($dadosDocumento['naoexibiremrelatorios']) : false;

        $obj->setNegocio($atc);
        //define o tipo de requisitante
        switch (strtolower(get_class($requisitante))) {
            case 'nasajon\mdabundle\entity\ns\clientes':
                $obj->setRequisitantecliente($requisitante);
                break;
            case 'nasajon\mdabundle\entity\ns\fornecedores':
                $obj->setRequisitantefornecedor($requisitante);
                break;
            default:
                throw new \Exception('Requisitante inválido.');
                break;
        }
        return $obj;
    }

    /*
    * Excluir uma lista de documentos requisitados por um cliente/fornecedor/apólice
    */
    private function excluirDocumentosRequisitantes($tenant,  $id_grupoempresarial, $documentos)
    {
        array_map(function ($documento) use ($tenant, $id_grupoempresarial) {
            $obj = $this->atcstiposdocumentosrequisitantesService->fillEntity($documento);
            $obj->setExcluirTipoDocumentoRequisitanteExterno(true);
            $this->atcstiposdocumentosrequisitantesService->delete($tenant,  $id_grupoempresarial, $obj);
        }, $documentos);
    }

    private function apagarDocumentosNaoMaisEsperados($tenant, $atcId, $id_grupoempresarial)
    {
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('negocio', 'eq', $atcId));
        $atcsDocumentos = $this->crmTcsdcmntsSrvc->findAll($tenant, $id_grupoempresarial, $filter);

        $documentosEsperados = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $atcId, $id_grupoempresarial, null);  
        array_map(function ($documento) use ($tenant, $documentosEsperados) {
            $excluirDocumento = true;
            array_map(function ($documentoEsperado) use (&$excluirDocumento, $documento) {
                if ($documento['tipodocumento']['tipodocumento'] == $documentoEsperado['tipodocumento']['tipodocumento']) {
                    $excluirDocumento = false;
                }
            }, $documentosEsperados);

            if ($excluirDocumento) {
                $obj = $this->crmTcsdcmntsSrvc->fillEntity($documento);
                $this->crmTcsdcmntsSrvc->delete($tenant, $obj);
            }
        }, $atcsDocumentos);
    }

    /**
     *  Busca as responsabilidades financeiras do negócio.
     *  Retorna o seguinte objeto:
     *  {
     *      cliente: ENTITY CLIENTE,
     *      arrResponsabilidadesfinanceiras: [
     *          {
     *              responsabilidadefinanceira: Entidade ResponsabilidadeFinanceira
     *              valor: VALOR RATEADO PARA O CLIENTE NESSA RESPONSABILIDADE
     *          }
     *      ]
     *  }
     */
    public function getItensPorResponsabilidadeFinanceira ($tenant, $entity) {
        try {
            // Preparo o objeto de retorno
            $retorno = [
                'cliente' => null,
                'arrResponsabilidadesfinanceiras' => []
            ];

            // Busco responsabilidades financeiras do negócio
            $responsabilidadesFinanceiras = $this->crmResponsabilidadesFinanceirasService->findAll($tenant, $entity->getNegocio(), $entity->getIdGrupoempresarial());
            
            // Busco propostas itens do negócio
            $propostasitens = $this->crmPrpstsSrvc->getPropostasItens($tenant, $entity->getNegocio(), $entity->getIdGrupoempresarial());

            // Preparo filtro de responsavel financeiro que vou utilizar para buscar rateio da responsabilidade financeira ligada ao contratante
            $filter = new Filter();
            $arrFilterExpression[] = new FilterExpression('responsavelfinanceiro', 'eq', $entity->getContratante());
            $filter->setFilterExpression($arrFilterExpression);

            // Percorro as responsabilidades financeiras, filtrando as que possuem o cliente igual ao contratante e pegando o valor do rateio
            foreach ($responsabilidadesFinanceiras as $key => $responsabilidade) {
                // Busco rateio da responsabilidade financeira ligado ao contratante.
                $arrValores = $this->crmResponsabilidadesFinanceirasValoresService->findAll(
                    $tenant, $entity->getIdGrupoempresarial(), $responsabilidade['responsabilidadefinanceira'], $filter
                );

                // Se a responsabilidade financeira não possui valor rateado para o contratante, passo para a próxima responsabilidade.
                if (count($arrValores) == 0) {
                    continue;
                }

                // Transformo o array de entity da responsabilidade financeira em um objeto
                $responsabilidadeEntity = $this->crmResponsabilidadesFinanceirasService->fillEntity($responsabilidade);
                
                // Crio objeto da responsabilidade com o valor do rateio para o contratante
                $responsabilidadeValorEntity = null;
                
                if ($retorno['cliente'] == null) {
                    $responsabilidadeValorEntity = $this->crmResponsabilidadesFinanceirasValoresService->findObject(
                        $arrValores[0]['responsabilidadefinanceiravalor'], $tenant, $entity->getIdGrupoempresarial(), 
                        $responsabilidade['responsabilidadefinanceira']
                    );
                    
                    $retorno['cliente'] = $responsabilidadeValorEntity->getResponsavelfinanceiro();
                }
                
                $valorPagar = 0;

                if ($responsabilidadeValorEntity != null){
                    $valorPagar = floatval( $responsabilidadeValorEntity->getValorpagar() );
                } else {
                    $valorPagar = floatval( $arrValores[0]['valorpagar'] );
                }

                foreach ($responsabilidadeEntity->getResponsabilidadesfinanceirasvalores() as $responsabilidadesvalor) {
                    if ($retorno['cliente'] != $responsabilidadesvalor->getResponsavelfinanceiro()){    
                        $responsabilidadeEntity->removeResponsabilidadesfinanceirasvalore($responsabilidadesvalor);
                    }
                }

                $objResponsabilidade = [
                    'responsabilidadefinanceira' => $responsabilidadeEntity,
                    'valor' => $valorPagar
                ];

                $retorno['arrResponsabilidadesfinanceiras'][] = $objResponsabilidade;
               
            }

            // Retorno dados
            return $retorno;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function geraContratoTaxaAdministrativa($tenant, $id_grupoempresarial, $logged_user,  \Nasajon\MDABundle\Entity\Crm\Atcs $entity) {
        try{
            $this->getRepository()->begin();

            $cliente = $entity->getCliente();
            $diasvencimento = $entity->getCliente()->getDiasparavencimento() ? $entity->getCliente()->getDiasparavencimento() : $this->wbCnfgrcsSrvc->getValor($tenant, 'DIASPARAVENCIMENTO');

            $filter = new Filter();
            $filterExpression = new FilterExpression('estabelecimento', 'eq', $entity->getEstabelecimento()->getEstabelecimento());
            $filter->addToFilterExpression($filterExpression);
            $filterExpression = new FilterExpression('seguradora', 'eq', $entity->getcliente()->getCliente());
            $filter->addToFilterExpression($filterExpression);
            $configuracaoTaxaAdm = $this->crmConfiguracoestaxasadministrativasService->findAll($tenant,$id_grupoempresarial, $filter);
            $configuracaoTaxaAdm = $configuracaoTaxaAdm[0];

            $itemdefaturamento = $this->servicosItensdefaturamentoService->find($configuracaoTaxaAdm['itemfaturamento']['servico'], $tenant, $id_grupoempresarial);
            
            $entity->getCliente()->setDiasparavencimento($diasvencimento);
            $contrato = new Contratos();
            $contrato->setCodigo($entity->getCodigo());
            $contrato->setDescricao($entity->getNome());
            $contrato->setContratante($cliente);
            $contrato->setEstabelecimento($entity->getEstabelecimento());
            $contrato->setMunicipioprestacao($entity->getMunicipioprestacaotaxaadm()); //municipio da request
            $contrato->setFormapagamento($entity->getFormapagamentotaxaadm()); //forma da request
            $contrato->setEmitirnotafiscal(true);
            $contrato->setTaxaadministrativa(true);
            $contrato->setAtc($entity->getNegocio());
            $contrato->setValor($entity->getValortaxaadm());
            $conta = new Contas();
            $conta->setConta($entity->getCliente()->getConta());
            $contrato->setConta($conta);

            if($contrato->getConta() == null || $contrato->getConta()->getConta() == null){
                throw new LogicException("Não existe conta configurada na ficha do cliente.");
            }

            $contrato_arr = $this->financasContratosService->insert($id_grupoempresarial, $logged_user, $tenant, $contrato);

            $contrato->setContrato($contrato_arr['contrato']);

            //gera itens para este contrato
            $itemcontrato = new Itenscontratos();
            $itemcontrato->setContrato($contrato->getContrato());
            $itemcontrato->setQuantidade(1);
            $itemcontrato->setValor($entity->getValortaxaadm()); //valor do request
            $itemcontrato->setNumerodiasparavencimento($diasvencimento);
            $itemcontrato->setDescricaoservico($itemdefaturamento['descricaoservico']);
            $itemcontrato->setItemfaturamento($itemdefaturamento['servico']);
            $itemcontrato->setIdgrupoempresarial($id_grupoempresarial);

            $itemContratoCriado = $this->financasItenscontratosService->insert(
                $id_grupoempresarial,
                $logged_user,
                $tenant,
                $itemcontrato);

            $itemcontrato->setItemcontrato($itemContratoCriado['itemcontrato']);

            $this->getRepository()->commit();
            return $entity;
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            if(strstr($e->getMessage(), 'UK_financas.contratos.numero')){
                throw new RepositoryException('Erro na sequência numéria do contrato.');
            }
            throw $e;
        } catch (LogicException $e) {
            $this->getRepository()->rollBack();
            if(strstr($e->getMessage(), 'UK_financas.contratos.numero')){
                throw new RepositoryException('Erro na sequência numéria do contrato.');
            }
            throw $e;
        }  
    }

    public function salvaContratoTaxaAdministrativa($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity) {
        try{
            $this->getRepository()->begin();

            $atcObject = $this->findObject($entity->getNegocio(),$tenant,$id_grupoempresarial);

            $atcObject->setMunicipioprestacaotaxaadm($entity->getMunicipioprestacaotaxaadm());
            $atcObject->setFormapagamentotaxaadm($entity->getFormapagamentotaxaadm());
            $atcObject->setValortaxaadm($entity->getValortaxaadm());

            $response = $this->getRepository()->salvadadotaxaadm($id_grupoempresarial,$tenant,  $atcObject);

            $this->getRepository()->commit();
            return $response;
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        } catch (LogicException $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }  
    }


    public function excluiContratoTaxaAdministrativa($tenant, $id_grupoempresarial, $logged_user,  \Nasajon\MDABundle\Entity\Crm\Atcs $entity) {
        try{
            $this->getRepository()->begin();

            $filter = new Filter();
            $filterExpression = new FilterExpression('estabelecimento', 'eq', $entity->getEstabelecimento()->getEstabelecimento());
            $filter->addToFilterExpression($filterExpression);
            $filterExpression = new FilterExpression('seguradora', 'eq', $entity->getcliente()->getCliente());
            $filter->addToFilterExpression($filterExpression);
            $configuracaoTaxaAdm = $this->crmConfiguracoestaxasadministrativasService->findAll($tenant,$id_grupoempresarial, $filter);
            $configuracaoTaxaAdm = $configuracaoTaxaAdm[0];

            $contratotaxaadm = $this->financasContratosService->findObject($entity->getContratotaxaadm()->getContrato(), $tenant, $id_grupoempresarial);
            $contratotaxaadm->setAtc($entity->getNegocio());
            $contratotaxaadm->setTaxaadministrativa(true);

            //deleta o contrato
            $response = $this->financasContratosService->delete($tenant, $contratotaxaadm);

            $this->getRepository()->commit();
            return $entity;
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        } catch (LogicException $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    private function podeGerarContrato($tenant, $id_grupoempresarial, $responsabilidadesFinanceiras, $listaOrcamentos, $propostaitemPadrao = null){
        $arrayErros = [];
        $listaItensCfopsOrcamentos = [];
        foreach ($responsabilidadesFinanceiras as $key => $responsabilidade) {
            try {
                //busco o orçamento
                $orcamento = $listaOrcamentos[$responsabilidade['responsabilidadefinanceira']->getOrcamento()];
                $orcamento->setItemfaturamento(null);
                // Não considera contrato se não tem fornecedor
                if($orcamento->getFornecedor()->getFornecedor() == null) {
                    continue;
                }

                // Não considera contrato de pagamento nem contrato de recebimento se o fornecedor terceirizado espera a seguradora.
                $fornecedor = $this->fornecedoresService->findObject($orcamento->getFornecedor()->getFornecedor(), $tenant, $id_grupoempresarial);
                $estabelecimento = $fornecedor->getEstabelecimentoid();
                $esperaSeguradora = $fornecedor->getEsperapagamentoseguradora();
                if (
                    $estabelecimento == null
                    && $esperaSeguradora !== null
                    && $esperaSeguradora === true
                ){
                    continue;
                }

                //busca composicao e itemdefaturamento para verificar se será
                if ($responsabilidade['valor'] > 0)  {
                    $orcamento->setComposicao($this->crmOrcamentosService->buscaComposicaoGeracaoContrato(
                        $orcamento, $id_grupoempresarial, $tenant, $propostaitemPadrao));

                    $itemFaturamento = $this->crmOrcamentosService->buscaItemFaturamentoGeracaoContrato(
                       $orcamento, $id_grupoempresarial, $tenant);

                    $orcamento->setItemfaturamento($itemFaturamento);
                }

                //impede a criação de contrato caso serviços tenham cfops diferentes uns dos outros
                foreach ($listaItensCfopsOrcamentos as $key => $item) {
                    if($itemFaturamento['cfopId'] !== $item['cfopId']){
                        throw new LogicException("Serviço ".$orcamento->getComposicao()['nome']." possui cfop diferente dos demais.");
                    }
                }
                if(isset($itemFaturamento)){
                    $listaItensCfopsOrcamentos[] = $itemFaturamento;
                }

                //substituo o orçamento existente pelo orçamento que tem composicao e itemdefaturamento, evitando mais queries no banco
                $listaOrcamentos[$responsabilidade['responsabilidadefinanceira']->getOrcamento()] = $orcamento;

            } catch (LogicException $e) {
                //evita duplicidade de mensagens de erro para itens da mesma composicao etc
                $arrayErros["<li>".$e->getMessage()."</li>"] = "<li>".$e->getMessage()."</li>";
            }
        }
        if($arrayErros !== []) {
            $mensagem = '';
            foreach ($arrayErros as $key => $erro) {
                $mensagem .= $erro;
            }
            $mensagem = "Erros encontrados: <ul>".$mensagem."</ul>";
            throw new \LogicException($mensagem);
        }
    }

    /**
     * Gera o contrato para o negócio
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * @throws \Exception
     * @throws \Doctrine\ORM\NoResultException
     * @todo passar a buscar os contratos de recebimento e de pagamento antes de criar novos.
     */
    public function geraContrato($tenant, $id_grupoempresarial, $logged_user,  \Nasajon\MDABundle\Entity\Crm\Atcs $entity) {
        try{
            $this->getRepository()->begin();
            //pego responsabilidades
            $dadosResponsabilidadesFinanceiras = $this->getItensPorResponsabilidadeFinanceira($tenant, $entity);

            $responsabilidadesFinanceiras = $dadosResponsabilidadesFinanceiras['arrResponsabilidadesfinanceiras'];

            // Rotina para gerar os contratos de pagamento
            $listaContratosDePagamento = [];
            $listaContratosDeRecebimento = [];
            
            $clienteId = $dadosResponsabilidadesFinanceiras['cliente'];
            $contratanteResponsabilidadeFinanceira = new Clientes();
            $contratanteResponsabilidadeFinanceira->setCliente($clienteId);

            $listaOrcamentos = [];
            $propostaitemPadrao = null; //propostaitem padrão para criação de itens de contrato de produtos sem propostaitem.

            foreach ($responsabilidadesFinanceiras as $key => $objDadosResponsabilidade) {
                $responsabilidadesFinanceiras[$key]['responsabilidadefinanceira']->responsavelFinanceiro = $objDadosResponsabilidade['responsabilidadefinanceira']->getResponsabilidadesfinanceirasvalores()->current()->getResponsavelfinanceiro();
                $orcamento = $this->crmOrcamentosService->findObject(
                    $objDadosResponsabilidade['responsabilidadefinanceira']->getOrcamento(),
                    $tenant, $id_grupoempresarial);
                
                $listaOrcamentos[$objDadosResponsabilidade['responsabilidadefinanceira']->getOrcamento()] = $orcamento;

                if($propostaitemPadrao == null && $orcamento->getPropostaitem() !== null){
                    $propostaitemPadrao = $orcamento->getPropostaitem();
                }
            }
            
            $this->podeGerarContrato($tenant, $id_grupoempresarial, $responsabilidadesFinanceiras, $listaOrcamentos, $propostaitemPadrao);

            //calculando descontos
            $restoConsideradoEmNovoContrato = false;
            $descontoPorResponsavelFinanceiro = [];
            foreach ($responsabilidadesFinanceiras as $key => $objDadosResponsabilidade) {

                $responsavelFinanceiro = $objDadosResponsabilidade['responsabilidadefinanceira']->responsavelFinanceiro;
                if(isset($descontoPorResponsavelFinanceiro[$responsavelFinanceiro])){
                    continue;
                } else {
                    $descontoPorResponsavelFinanceiro[$responsavelFinanceiro] = 0;
                }

                $orcamento = $listaOrcamentos[$objDadosResponsabilidade['responsabilidadefinanceira']->getOrcamento()];
                $filter = new Filter();
                $filter->addToFilterExpression(new FilterExpression('fornecedor', 'eq', $orcamento->getFornecedor()->getFornecedor()));
                $fornecedorEnvolvido = $this->crmFornecedoresEnvolvidosRepository->findAll($tenant, $orcamento->getAtc(), $id_grupoempresarial, $filter);

                $listaRespFinValor = $this->crmResponsabilidadesFinanceirasValoresService->findAll($tenant, $entity->getIdGrupoempresarial(), $objDadosResponsabilidade['responsabilidadefinanceira']->getResponsabilidadefinanceira());

                //calculo rateio igualitario do desconto
                $valorEmCentavos = ((int)($fornecedorEnvolvido[0]['descontoglobal']*100)); //uso centavos, assim consigo forçar int
                $resto = $valorEmCentavos % count($listaRespFinValor); //separo o resto da dvisão
                $totalDivisivelOriginal = $valorEmCentavos - $resto; //calculo o valor divisivel igualmente


                $somaDescontoExistente = 0;
                $qtdContratosGerados = 0;

                foreach ($listaRespFinValor as $key2 => $respFinValor) {
                    if(!is_null($respFinValor['contrato'])) {
                        $contrato = $this->financasContratosService->find($respFinValor['contrato'], $tenant, $id_grupoempresarial);
                        $somaDescontoExistente += 100 * $contrato['descontoglobalitensnaofaturados'];
                        $qtdContratosGerados++;
                    }
                }

                $totalDivisivelPendente = $totalDivisivelOriginal - $somaDescontoExistente; //retiro o valor ja contabilizado do meu total divisivel
                $qtdContratosNaoGerados = count($listaRespFinValor) - $qtdContratosGerados; //retiro os membros que já participaram da divisão
                $valorDescontoGlobalDividido =  (int)($totalDivisivelPendente / $qtdContratosNaoGerados);

                if($totalDivisivelPendente + $somaDescontoExistente < $valorEmCentavos && $restoConsideradoEmNovoContrato == false) {
                    //não considerou o resto ainda nos contratos gerados. vou considerar.
                    $restoConsideradoEmNovoContrato = true;
                    $valorDescontoGlobalDividido += $resto;
                }

                $desconto = $valorDescontoGlobalDividido/100;
                $descontoPorResponsavelFinanceiro[$responsavelFinanceiro] += $desconto;
            }

            foreach ($responsabilidadesFinanceiras as $key => $objDadosResponsabilidade) {
                $responsavelFinanceiro = $objDadosResponsabilidade['responsabilidadefinanceira']->responsavelFinanceiro;
                $desconto = $descontoPorResponsavelFinanceiro[$responsavelFinanceiro];
                $responsabilidadesFinanceiras[$key]['responsabilidadefinanceira']->setDescontoglobal($desconto);
            }

            //para cada responsabilidade...
            foreach ($responsabilidadesFinanceiras as $objDadosResponsabilidade) {
                $responsabilidade = $objDadosResponsabilidade['responsabilidadefinanceira'];
                $valorResponsabilidadeFinanceira = $objDadosResponsabilidade['valor'];

                //busco o orçamento
                $orcamento = $listaOrcamentos[$responsabilidade->getOrcamento()];

                //separo o fornecedor
                $fornecedorId = $orcamento->getFornecedor()->getFornecedor();
                // Não cria contrato se não tem fornecedor
                if($fornecedorId == null) {
                    continue;
                }
                
                // Não cria contrato de pagamento nem contrato de recebimento se o fornecedor terceirizado espera a seguradora.
                $fornecedor = $this->fornecedoresService->findObject($fornecedorId, $tenant, $id_grupoempresarial);
                $estabelecimento = $fornecedor->getEstabelecimentoid();
                $esperaSeguradora = $fornecedor->getEsperapagamentoseguradora();
                if (
                    $estabelecimento == null
                    && $esperaSeguradora !== null
                    && $esperaSeguradora === true
                ){
                    continue;
                }
                
                //@todo passar a buscar os contratos de recebimento e de pagamento antes de criar novos.
                //gera contrato de recebimento para a responsabilidade caso não exista
                if(!isset($listaContratosDeRecebimento[$clienteId])) {
                    $listaContratosDeRecebimento[$clienteId] = $this->geraContratoRecebimento($entity,$id_grupoempresarial, $logged_user, $tenant, $contratanteResponsabilidadeFinanceira, $responsabilidade);
                    $entity->setContrato($listaContratosDeRecebimento[$clienteId]);
                }
                //gera itens para este contrato
                if ($valorResponsabilidadeFinanceira > 0)  {
                   $itemContratoRecebimento = $this->crmOrcamentosService->geraItemContrato(
                        $orcamento,
                        $listaContratosDeRecebimento[$clienteId], //contrato
                        $responsabilidade, 
                        $id_grupoempresarial, $logged_user, $tenant,
                        $contratanteResponsabilidadeFinanceira,
                        $valorResponsabilidadeFinanceira,
                        $propostaitemPadrao
                    );
                }

                //logica para geracao de contrato e itens pagamento
                if(
                    $estabelecimento == null //sem estabelecimento quer dizer que não é o proprio usuario que relaizara o servico
                    && ($esperaSeguradora === null
                    || $esperaSeguradora === false) //se o fornecedor não espera seguradora, vamos gerar 2 contratos no total.
                ){
                    //não é FMA e não espera seguradora, verificar se tem contrato tipo 0 para este fornecedor
                    //aqui o contrato é gerado com contratante/participante == fornecedor
                    $contratoAPagar = $this->crmPropostaItemService->localizaContratoPagamento($tenant, $entity->getNegocio(), $fornecedorId);
                    if(isset($contratoAPagar[0]) && isset($contratoAPagar[0]['contratoapagar'])){
                      if(!isset($listaContratosDePagamento[$fornecedorId])){
                        $contratoAPagarObj = new Contratos();
                        $contratoAPagarObj->setContrato($contratoAPagar[0]['contratoapagar']);
                        $listaContratosDePagamento[$fornecedorId] = $contratoAPagarObj;
                      }
                    } else {
                      $listaContratosDePagamento[$fornecedorId] = $this->geraContratoPagamento($tenant, $id_grupoempresarial, $logged_user, $entity, $fornecedor);
                    }
                    $itemContratoAPagar = $this->crmOrcamentosService->geraItemContratoPagamento(
                        $orcamento,
                        $listaContratosDePagamento[$fornecedorId], //contrato
                        $responsabilidade, 
                        $fornecedor,
                        $id_grupoempresarial, $logged_user, $tenant,
                        $valorResponsabilidadeFinanceira,
                        $propostaitemPadrao
                    );
                }

                // Vinculo itens de contrato a responsabilidade financeira valor
                $key = array_keys($responsabilidade->getResponsabilidadesfinanceirasvalores()->toArray())[0];
                $responsabilidadevalor = $responsabilidade->getResponsabilidadesfinanceirasvalores()[$key];
                
                $this->crmResponsabilidadesFinanceirasValoresService->vincularItensDeContrato(
                    $id_grupoempresarial, $tenant, $logged_user, $responsabilidadevalor
                );
            }

            // if(count($propostasitens) == 0){
            //   throw new \Doctrine\ORM\NoResultException();
            // }
            
            $this->getRepository()->commit();
            return $entity;
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            if(strstr($e->getMessage(), 'UK_financas.contratos.numero')){
                throw new RepositoryException('Erro na sequência numéria do contrato.');
            }
            throw $e;
        } catch (LogicException $e) {
            $this->getRepository()->rollBack();
            if(strstr($e->getMessage(), 'UK_financas.contratos.numero')){
                throw new RepositoryException('Erro na sequência numéria do contrato.');
            }
            throw $e;
      }  
    }

    private function geraContratoPagamento ($tenant, $id_grupoempresarial, $logged_user, $entity, $fornecedor) {

        // $diasvencimento = $entity->getCliente()->getDiasparavencimento() ? $entity->getCliente()->getDiasparavencimento() : $this->wbCnfgrcsSrvc->getValor($tenant, 'DIASPARAVENCIMENTO');
        // $entity->getCliente()->setDiasparavencimento($diasvencimento);

        $contratoAPagar = new Contratos();
        $contratoAPagar->setCodigo($entity->getCodigo());
        $contratoAPagar->setDescricao($entity->getNome());
        $contratoAPagar->setFornecedor($fornecedor->getFornecedor()); //ta ok
        $contratoAPagar->setEstabelecimento($entity->getEstabelecimento());
        $contratoAPagar->setMunicipioprestacao($entity->getMunicipioprestacao());
        $contratoAPagar->setFormapagamento($entity->getFormapagamento());
        $contratoAPagar->setEmitirnotafiscal(false);
        
        $contratoId = $this->financasContratosService->insertContratoAPagar($id_grupoempresarial, $logged_user, $tenant, $contratoAPagar);
        $contratoAPagar->setContrato($contratoId);
        return $contratoAPagar;
        // $entity->setContrato($contrato);    
        // $this->getRepository()->editaContratoNoAtc($tenant, $logged_user, $entity);
    }

    private function geraContratoRecebimento($entity,$id_grupoempresarial, $logged_user, $tenant, $contratanteResponsabilidadeFinanceira, $responsabilidadeFinanceira){
        
        $diasvencimento = $entity->getCliente()->getDiasparavencimento() ? $entity->getCliente()->getDiasparavencimento() : $this->wbCnfgrcsSrvc->getValor($tenant, 'DIASPARAVENCIMENTO');
        $entity->getCliente()->setDiasparavencimento($diasvencimento);
        // $this->getRepository()->begin();
        $contrato = new Contratos();
        $contrato->setCodigo($entity->getCodigo());
        $contrato->setDescricao($entity->getNome());
        $contrato->setContratante($contratanteResponsabilidadeFinanceira);
        $contrato->setEstabelecimento($entity->getEstabelecimento());
        $contrato->setMunicipioprestacao($entity->getMunicipioprestacao());
        $contrato->setFormapagamento($entity->getFormapagamento());
        $contrato->setEmitirnotafiscal($responsabilidadeFinanceira->getGeranotafiscal());
        $contrato->setDescontoglobalitensnaofaturados($responsabilidadeFinanceira->getDescontoglobal());
        $conta = new Contas();
        $conta->setConta($entity->getCliente()->getConta());
        $contrato->setConta($conta);

        if($contrato->getConta() == null || $contrato->getConta()->getConta() == null){
            throw new LogicException("Não existe conta configurada na ficha do cliente.");
        }
        
        $contrato_arr = $this->financasContratosService->insert($id_grupoempresarial, $logged_user, $tenant, $contrato);
        $contrato->setContrato($contrato_arr['contrato']);
        $entity->setContrato($contrato);
        $this->getRepository()->editaContratoNoAtc($tenant, $logged_user, $entity);
        
        return $contrato;
    }

    /**
     * Exclui os contratos de pagamento e recebimento  associados ao contratante passado
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * @throws \Exception
    */
    public function excluiContrato($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
        // Busco responsabilidades financeiras do negócio, para procurar contratos.
        $arrResponsabilidadesFinanceiras = $this->crmResponsabilidadesFinanceirasService->findAll(
            $tenant, $entity->getNegocio(), $entity->getIdGrupoempresarial()
        );

        $contrato = null;
        $contratoapagar = null;
        $arrResponsabilidadesFinanceirasvalores = [];

        foreach ($arrResponsabilidadesFinanceiras as $respFin) {
            foreach ($respFin['responsabilidadesfinanceirasvalores'] as $respFinValor) {
                if ( ($respFinValor['responsavelfinanceiro'] == $entity->getContratante()) && 
                     ($respFinValor['valorpagar'] > 0) ) {
                    $contrato = $respFinValor['contrato'];
                    $contratoapagar = $respFinValor['contratoapagar'];
                    $arrResponsabilidadesFinanceirasvalores[] = $this->crmResponsabilidadesFinanceirasValoresService->fillEntity($respFinValor);
                }
            }
        }

        if ($contrato == null) {
            throw new LogicException("Não foi possível encontrar Contrato.", 1);
        }

        // Preencho objetos de contrato de recebimento e  pagamento
        $entityArrContrato = $this->financasContratosService->find($contrato, $tenant, $id_grupoempresarial);
        $entityContrato = $this->financasContratosService->fillEntity($entityArrContrato);

        $entityContratoAPagar = null;

        if ($contratoapagar != null) {
            $entityArrContratoAPagar = $this->financasContratosService->find($contratoapagar, $tenant, $id_grupoempresarial);
            $entityContratoAPagar = $this->financasContratosService->fillEntity($entityArrContratoAPagar);
        }

        try{
            $this->getRepository()->begin();
            
            // Removo vínculo dos contratos com as responsabilidades financeiras valores
            foreach ($arrResponsabilidadesFinanceirasvalores as $responsabilidadevalor) {
                $this->crmResponsabilidadesFinanceirasValoresService->desvincularItensDeContrato(
                    $id_grupoempresarial, $tenant, $logged_user, $responsabilidadevalor
                );
            }

            // Excluo contrato de recebimento
            $this->financasContratosService->delete($tenant, $entityContrato);

            // Excluo contrato de pagamento, caso exista
            if ($contratoapagar != null) {
                $this->financasContratosService->excluiContratoPagamento($tenant, $entityContratoAPagar);
            }

            $this->getRepository()->commit();
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

   /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * 
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
  */
  public function atcStatusNovo($tenant,$id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
    try {
      //checar se o negócio possui proposta, e se as propostas possuem propostasitens.
      $propostasitens = $this->crmPrpstsSrvc->getPropostasItens($tenant, $entity->getNegocio(), $id_grupoempresarial);
      if(!empty($propostasitens)) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      $this->getRepository()->begin();
      $response = $this->getRepository()->atcStatusNovo($tenant,$id_grupoempresarial, $logged_user,  $entity);
      $response = ['status' => intval($response), 'statusLabel' => $this->getStatus(['status' => $response])];
      $this->getRepository()->commit();
      return $response;
    }catch(\Exception $e){
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
   */
  public function atcStatusEmAtendimento($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity)
  {
    try {
      //só faz se o status for 1 em atendimento
      if($entity->getStatus() != 0 && $entity->getStatus() != 2 && $entity->getStatus() != 4 ) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      //checar se o negócio possui proposta, e se as propostas possuem propostasitens.
      $propostasitens = $this->crmPrpstsSrvc->getPropostasItens($tenant, $entity->getNegocio(), $id_grupoempresarial);
      if(empty($propostasitens)) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      $this->getRepository()->begin();
      $response = $this->getRepository()->atcStatusEmAtendimento($tenant, $id_grupoempresarial, $logged_user,  $entity);
      $response = ['status' => intval($response), 'statusLabel' => $this->getStatus(['status' => $response])];
      $this->getRepository()->commit();
      return $response;
    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
  */
  public function atcStatusFechado($tenant,$id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
    try {

      $camposMsg = [];
      $msgErro = '';
      $this->getRepository()->begin();

      // Verificando se a área do atendimento possui localização
      $possuiLocalizacao = $entity->getArea()->getLocalizacao();


      //só faz se o status for 1 em atendimento
      if($entity->getStatus() !== 1) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      
      $response = $this->getRepository()->atcStatusFechado($tenant, $id_grupoempresarial, $logged_user,  $entity);
      $response = ['status' => intval($response), 'statusLabel' => $this->getStatus(['status' => $response])];
      $this->getRepository()->commit();
      return $response;
    }catch(\Exception $e){
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
  */
  public function atcStatusReaberto($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
    try {
      if($entity->getStatus() != 2) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      $this->getRepository()->begin();
      $response = $this->atcStatusNovo($tenant, $id_grupoempresarial, $logged_user,  $entity);
      $entity->setStatus($response);
      $response = $this->atcStatusEmAtendimento($tenant, $id_grupoempresarial, $logged_user, $entity);
      $this->getRepository()->commit();
      return $response;
    } catch(\Exception $e){
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
  */
  public function atcStatusFinalizado($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
        try {
            // A VALICAÇÃO CHECA SE OS ITENS DO PEDIDO TIVERAM SUAS TAREFAS FINALIZADAS E SEUS CONTRATOS GERADOS
            //contrato deve estar faturado e serviços executados.
            $this->getRepository()->begin();
            $servicosExecutados = true;
            $servicosFaturados = true;
            $propostasitens = $this->crmPrpstsSrvc->getPropostasItens($tenant, $entity->getNegocio(), $id_grupoempresarial);
            $contratos = [];

            $contratosPropostasitens = $this->responsabilidadesfinanceirasvaloresRepository
                ->getContratosBy($entity->getNegocio(), 'negocio', $id_grupoempresarial, $tenant);

            foreach ($contratosPropostasitens as $key => $contrato) {
                if($contrato['contrato'] == null){
                    $servicosFaturados = false; //já tem item sem contrato.
                    break;
                } elseif($contrato['cancelado'] == true){
                    $servicosFaturados = false; //este item que possui este contrato, no caso, o contrato está cancelado, inválido.
                }
            }
            if(!$servicosFaturados) {
                throw new LogicException("Erro. Não é possível finalizar um atendimento antes dos serviços serem faturados.", 1);
                return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
            }
            if(!$servicosExecutados) {
                throw new LogicException("Erro. Não é possível finalizar um atendimento antes dos serviços serem executados.", 1);
                return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
            }

            $response = $this->getRepository()->atcStatusFinalizado($tenant, $id_grupoempresarial, $logged_user,  $entity);
            $response = ['status' => intval($response), 'statusLabel' => $this->getStatus(['status' => $response])];
            $this->getRepository()->commit();
            return $response;
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

  /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
  */
  public function atcStatusCancelado($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
    try {
      if($entity->getStatus() == 3 || $entity->getStatus() == 4) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      $this->getRepository()->begin();
      $dadosContratosContagem = $this->atcsContratosContagem($tenant, $id_grupoempresarial, $entity);
      
      $osCriada = false;
      $propostasitens = $this->crmPrpstsSrvc->getPropostasItens($tenant, $entity->getNegocio(), $id_grupoempresarial);
      foreach ($propostasitens as $key => $item) {
        $temOs= $item->getTarefa()->getPossuiOrdemservico();
        if($item->getTarefa()->getPossuiOrdemservico() == true){
          $osCriada = true;
        }
      }

      if($dadosContratosContagem['qtdContratosFaturados'] != 0 && $osCriada) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      $response = $this->getRepository()->atcStatusCancelado($tenant, $id_grupoempresarial, $logged_user,  $entity);
      $response = ['status' => intval($response), 'statusLabel' => $this->getStatus(['status' => $response])];
      $this->getRepository()->commit();
      return $response;
    } catch(\Exception $e) {
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  /**
   * Sobrescrito para retornar o status e statusLabel após alteração do status do negócio.
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
   * @return string
   * @throws \Exception
  */
  public function atcStatusDescancelado($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcs $entity){
    try {
      // $qtdServicosFaturados = 0;
      // $osCriada = false;
      // if($qtdServicosFaturados != 0 && $osCriada) {
      //   return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      // }
      $this->getRepository()->begin();
      $projeto = $entity->getProjeto();
      if($projeto != null && $projeto->getProjeto() != null && $projeto->getSituacao() == 1){
        throw new LogicException("Não é possível desfazer cancelamento. Este atendimento possui um projeto já cancelado.", 1);
      }
      if($entity->getStatus() != 4) {
        return ['status' => intval($entity->getStatus()), 'statusLabel' => $this->getStatus(['status' => $entity->getStatus()])];;
      }
      $response = $this->atcStatusNovo($tenant, $id_grupoempresarial, $logged_user,  $entity);
      $entity->setStatus($response['status']);
      $response = $this->atcStatusEmAtendimento($tenant, $id_grupoempresarial, $logged_user,  $entity);
      $this->getRepository()->commit();
      return $response;
    } catch(\Exception $e) {
      $this->getRepository()->rollBack();
      throw $e;
    }
  }

  public function atcsContratosContagem ($tenant, $id_grupoempresarial, $entity) {
    $qtdContratos = 0;
    $qtdContratosFaturados = 0;
    $qtdItensSemContrato = 0;

    $contratos = $this->responsabilidadesfinanceirasvaloresRepository->getContratosBy($entity->getNegocio(), 'negocio', $id_grupoempresarial, $tenant);      
      $qtdContratosFaturados = 0;
      foreach ($contratos as $key => $contrato) {
        if($contrato['cancelado'] == false) {
          $qtdContratosFaturados++;
        }
      }

      $retorno['qtdContratos'] = count($contratos);
      $retorno['qtdContratosFaturados'] = $qtdContratosFaturados;
      return $retorno;
  }

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * @throws \Exception
     */
    public function baixardocumento($tenant,$id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcs $entity)
    {
        try {
            $this->getRepository()->begin();

            $fornecedor = $entity->getFornecedorobj();
            $fornecedorId = ($fornecedor != null) ? $fornecedor->getFornecedor() : null;
            $documentofop = $entity->getDocumentofop();
            $documentofopObject = $this->nsDocumentosfopService->find($documentofop->getDocumentofop());
            $codigoRelatorio = $documentofopObject['codigodocumento'];
            
            $response = $this->gerarRelatorioFop($codigoRelatorio, $id_grupoempresarial, $tenant, $fornecedorId, $entity);
            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * @throws \Exception
     */
    public function enviaratendimentoemail($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcs $entity, $logged_user = null)
    {
        try {
            //gerar o relatório

            //iterar sobre os documentos
            $documentosemail = $entity->getDocumentosemail();
            foreach ($documentosemail as $key => $documento) {
                $documentofop = $documento->getDocumentofop();
                $fornecedorobj = $documento->getFornecedorobj();
                $fornecedorId = ($fornecedorobj != null) ? $fornecedorobj->getFornecedor() : null;
                $contratoGeracaoRps = $documento->getContratoGeracaoRps();
                $entity->setContratoGeracaoRps($contratoGeracaoRps);
                //para cada documento, pegar o codigo do documento
                $documentofopObject = $this->nsDocumentosfopService->find($documentofop->getDocumentofop());
                $codigoRelatorio = $documentofopObject['codigodocumento'];
                //gerar o relatorio
                $entity->setTiporelatorioemissao($documento->getTiporelatorioemissao());
                $pdf = $this->gerarRelatorioFop($codigoRelatorio, $id_grupoempresarial, $tenant, $fornecedorId, $entity);
                //adicionar no array dos atatchments
                $attachments[] = $pdf;
                $attachments_names[] = date("Y-m-d") . "_relatorio_$codigoRelatorio.pdf";
                $attachments_content_types[] = "application/pdf";
            }

            //pegar codigo do template a ser usado
            $templateemail = $entity->getAtcconfiguracaotemplateemail();
            $configuracaodocumento = $entity->getAtcconfiguracaodocumento();
            $templateemailObj = $this->atcsconfiguracoestemplatesemailsService->find(
                $templateemail->getAtcconfiguracaotemplateemail(),
                $configuracaodocumento,
                $tenant,
                $id_grupoempresarial
            );
            //Código do template, vem do entity
            $codigo = $templateemailObj['codigo'];

            //pegar remetente
            $remetente = $templateemailObj['atcconfiguracaodocumento']['emailpadrao'];
            // if($remetente == null || $remetente == ''){
            //   throw new \Exception("Email não cadastrado nas configurações de envio de relatório.", 1);
            // }
            
            $destinatários = $entity->getListaemails();
            
            //Tags mapeadas no template
            $tags = [
              //Função insere quebras de linha HTML nas quebras de linha da string
              'corpoMensagem' => nl2br($entity->getMensagememail()),
              'assunto_custom' => $this->buildAssuntoEmail($entity)
            ];

            //disparar email
            $dados = [
                'from' => $remetente,
                'to' => $destinatários,
                'codigo' => $codigo,
                'tags' => $tags,
                'tenant' => $tenant,
                'attachments' => $attachments,
                'attachments_names' => $attachments_names,
                'attachments_content_types' => $attachments_content_types,
            ];
            $resultadoEnvio = $this->envioEmailService->enviarEmail($dados, $tenant);

            //Criando historico
            $historico = $this->createHistoricoEmail($tenant,$id_grupoempresarial,$entity);

            //salvar os anexos
            $linksAnexos = [];
            foreach ($attachments as $key => $attachment) {
                $linksAnexos[] = $this->historicosatcsanexosService->insertRaw($historico['historiconegocio'], $tenant, $logged_user, $attachment, $attachments_names[$key], 'pdf');
            }

            return $resultadoEnvio;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * @param string  $codigoRelatorio
     * @param string  $id_grupoempresarial
     * @param int  $tenant
     * @param string  $fornecedor
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * 
     * Gera xml e dispara requisição para geração do documento no Diretório/DocumentosFop, retornando a string pdf.
     */
    private function gerarRelatorioFop($codigoRelatorio, $id_grupoempresarial, $tenant, $fornecedor, \Nasajon\MDABundle\Entity\Crm\Atcs $entity)
    {
        $atcsObject = $this->findObject($entity->getNegocio(), $tenant, $id_grupoempresarial);

        if($entity->getTiporelatorioemissao() == "3"){
            $atcsRelatorio = $this->atcsRelatoriosRpsService->montaEntidade($atcsObject, $entity->getContratoGeracaoRps(), $tenant, $id_grupoempresarial);
        } elseif ($codigoRelatorio == "REL_ATC_TO_SEGURADORA_APROVACAO") {
            $fornecedoresEnvolvidos = $this->crmFornecedoresEnvolvidosRepository->findAll($tenant, $entity->getNegocio(), $id_grupoempresarial);
            $atcsRelatorio = $this->atcsRelatoriosService->montaEntidadeAtendimentoComercial($atcsObject, $tenant, $id_grupoempresarial, $fornecedor, $fornecedoresEnvolvidos);
        } else {
            $atcsRelatorio = $this->atcsRelatoriosService->montaEntidadeAtendimentoComercial($atcsObject, $tenant, $id_grupoempresarial, $fornecedor);
        }
        
        $dadosxml = $this->JMSSerializer->serialize($atcsRelatorio, 'xml');

        $dadosPdf = $this->documentosFopService->getDocumentoFop($dadosxml, $codigoRelatorio, $tenant, $id_grupoempresarial);

        return $dadosPdf;
    }

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return string
     * @throws \Exception
     */
    public function indexRotasGoogleDirections($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcs $entity)
    {
        try {
            $this->getRepository()->begin();

            $enderecoOrigem = $entity->getEnderecoorigem();
            $enderecoDestino = $entity->getEnderecodestino();
            $response = $this->googleMapsService->getRoutesGoogleDirections($enderecoOrigem, $enderecoDestino);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * Envia os documentos do atendimento por e-mail.
     * Só serão enviados os documentos com o tipo de documento marcado como "Enviar para seguradora"
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Atcs $entity
     * @return void
     * @throws \Exception
     */
    public function enviardocumentosporemail($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcs $entity)
    {
        try {
            // Busco crm.atcstiposdocumentosrequisitantes, com opção de enviar documentos por e-mail
            $filter = new Filter();
            $expressions = [
                new FilterExpression('permiteenvioemail', 'eq', true)
            ];
            $filter->setFilterExpression($expressions);
            $arrTiposDocumentosEnviar = $this->atcstiposdocumentosrequisitantesService->findAll(
                $tenant, $entity->getNegocio(), $id_grupoempresarial, $filter
            );
            
            if (count($arrTiposDocumentosEnviar) == 0) {
                throw new LogicException('Não existem documentos para enviar.');
            }

            // Busco crm.atcsdocumentos do negócio, filtrando os tipos que permitem envio por tipo de documento
            $expressions = [];
            $expressions[] = new FilterExpression('negocio', 'eq', $entity->getNegocio());

            //Usando a condição pre-aprovada como parametro para enviar por e-mail
            $expressions[] = new FilterExpression('status', 'eq', 2);

            foreach ($arrTiposDocumentosEnviar as $tipoDocEnviar) {
                $expressions[] = new FilterExpression('tipodocumento.tipodocumento', 'eq', $tipoDocEnviar['tipodocumento']['tipodocumento']);
            }

            $filter->setFilterExpression($expressions);
            $arrDocumentosEnviar = $this->crmTcsdcmntsSrvc->findAll(
                $tenant, $id_grupoempresarial, $filter
            );
            
            if (count($arrDocumentosEnviar) == 0) {
                throw new LogicException('Não existem documentos para enviar.');
            }

            // Defino listas de anexos do e-mail
            $attachments = [];
            $attachments_names = [];
            $attachments_content_types = [];
            
            // Baixo documentos e adiciono a lista dos anexos
            $tiposEnviar = [];
            foreach ($arrDocumentosEnviar as $docEnviar) {
                $tipoDocId = $docEnviar['tipodocumento']['tipodocumento'];
                if(isset($tiposEnviar[$tipoDocId])){
                    $tiposEnviar[$tipoDocId]++;
                } else {
                    $tiposEnviar[$tipoDocId] = 1;
                }

                // Monto nome do documento
                $documentoNome = $docEnviar['tipodocumento']['nome'];
                if ($tiposEnviar[$tipoDocId] > 1) {
                    $documentoNome .= '-' . $tiposEnviar[$tipoDocId];
                }
                $documentoNome .= '.' . $docEnviar['ext'];

                // Realizo download do documento
                $documentoBinario = $this->uploadFilesService->getFileFromUrl($docEnviar['url']);

                // Incluo documento nos anexos do e-mail
                $attachments[] = $documentoBinario;
                $attachments_names[] = $documentoNome;
                $attachments_content_types[] = $docEnviar['tipomime'];
            }

            // Defino o remetente
            $remetente = null; // Null pois é enviado um remetente padrão com e-mail da nasajon por enquanto.

            // Envio e-mail
            $codigo = 'crmweb_documentos_email';

            // Pego lista de destinatários
            $destinatarios = $entity->getListaemails();
            
            //Tags mapeadas no template
            $tags = [
              //Função insere quebras de linha HTML nas quebras de linha da string
              'corpoMensagem' => nl2br($entity->getMensagememail()),
              'assunto_custom' => $this->buildAssuntoEmail($entity)
            ];

            //disparar email
            $dados = [
                'from' => $remetente,
                'to' => $destinatarios,
                'codigo' => $codigo,
                'tags' => $tags,
                'tenant' => $tenant,
                'attachments' => $attachments,
                'attachments_names' => $attachments_names,
                'attachments_content_types' => $attachments_content_types,
            ];

            $resultadoEnvio = $this->envioEmailService->enviarEmail($dados, $tenant);
            
            if ($resultadoEnvio == null || !$resultadoEnvio['sucesso']){
                throw new LogicException('Não foi possível enviar documentos por e-mail.');
            }              
            
            $this->createHistoricoEmail($tenant,$id_grupoempresarial,$entity);
            return $resultadoEnvio;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function buildAssuntoEmail( \Nasajon\MDABundle\Entity\Crm\Atcs $atc) 
    {
        $assunto = '';
        switch($atc->getIdentificacaoEnvioEmailTipo()){
            case 0 : // Cliente
                if (!empty($atc->getIdentificacaoEnvioEmailNome())) {
                    $assunto = $atc->getCliente()->getNomefantasia();
                }
                break;
            case 1: // Seguradora
                //
                if(count($atc->getNegociosdadosseguradoras())){
                    $negocioseguradora = $atc->getNegociosdadosseguradoras()[0];
                    $assunto = 'Sinistro: ' . $negocioseguradora->getSinistro();
                }
                break;  
            case 2: // Prestadora
                $assunto = ' para ' . $atc->getIdentificacaoEnvioEmailNome();
                break;
        }
        return $assunto;

    }

    private function createHistoricoEmail($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcs $atc)
    {
        //Criando o historico
        $historico = new Historicoatcs();
        $historico->setAcao('Envio de E-mail');
       
        $observacao = '';
        //Verifica se existe alguma identificação passada, por conta de usarem a mesma rota
        //prestarora/seguradora/cliente o front que determina para quem será enviado
        if(!empty($atc->getIdentificacaoEnvioEmailNome())) {
            $observacao .= sprintf('%s. ', $atc->getIdentificacaoEnvioEmailNome()) . PHP_EOL;
        }
        $observacao .= sprintf('E-mail enviado para %s. ', join($atc->getListaemails(), ', ')) . PHP_EOL;
        $observacao .= sprintf('Mensagem: %s ', $atc->getMensagememail()) . PHP_EOL;
        $historico->setObservacao($observacao);
        $logged_user = $this->fixedAttribute->get('logged_user');
        $historico = $this->crmHstrctcsSrvc->insert($atc->getNegocio(), $tenant, $logged_user, $historico);
        return $historico;
    }

    /**
     * @param string  $atc
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @return array 
     * @throws \Exception
     */
    public function findSimples($atc, $tenant, $id_grupoempresarial){
        return $this->getRepository()->findSimples($atc, $tenant, $id_grupoempresarial);
    }
}
