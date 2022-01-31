<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Entity\Crm\Atcstiposdocumentosrequisitantes;
use Nasajon\MDABundle\Entity\Ns\Tiposdocumentos;
use Nasajon\MDABundle\Entity\Crm\Atcscontasapagar;
use Nasajon\MDABundle\Entity\Ns\Fornecedores;
use Nasajon\MDABundle\Service\Crm\FornecedoresenvolvidosService as ParentService;
use LogicException;
use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidosanexos;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FornecedoresenvolvidosService
 *
 */
class FornecedoresenvolvidosService extends ParentService
{
    protected $orcamentosService = null;

    public $fornecedoresdocumentosService;
    public $tiposdocumentosService;
    public $atcstiposdocumentosrequisitantesService;
    /**
     * Service utilizado para upload de arquivos
     */
    protected $uploadFilesService;

    /**
     * @var Nasajon\AppBundle\Service\Common\DocumentosFopService
     */
    protected $documentosFopService;

    /**
     * Service utilizado para relatórios do atendimento comercial
     */
    protected $atcsRelatoriosService;

    protected $atcsRepository;
    public $atcscontasapagarService;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\FornecedoresenvolvidosRepository $repository,
        $crmFrncdrsnvlvdsnxsSrvc,
        $orcamentosService,
        $fornecedoresdocumentosService,
        $tiposdocumentosService,
        $atcstiposdocumentosrequisitantesService,
        $uploadFilesService,
        $atcsRelatoriosService,
        $documentosFopService,
        $atcsRepository,
        $propostasitensService,
        $atcscontasapagarService
    ) {
        parent::__construct($repository, $crmFrncdrsnvlvdsnxsSrvc);
        $this->orcamentosService = $orcamentosService;
        $this->fornecedoresdocumentosService = $fornecedoresdocumentosService;
        $this->tiposdocumentosService = $tiposdocumentosService;
        $this->atcstiposdocumentosrequisitantesService = $atcstiposdocumentosrequisitantesService;
        $this->uploadFilesService = $uploadFilesService;
        $this->atcsRelatoriosService = $atcsRelatoriosService;
        $this->documentosFopService = $documentosFopService;
        $this->atcsRepository = $atcsRepository;
        $this->propostasitensService = $propostasitensService;
        $this->atcscontasapagarService = $atcscontasapagarService;
    }

    /**
     * Sobrescrito para adicionar a propriedade 'acionamentorespostaprazoflag' ao retorno, que informa se o prazo para resposta do prestador do serviço expirou
     * @param string $id
     * @param mixed $tenant
     * @param mixed $atc
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $atc, $id_grupoempresarial)
    {
        $fornecedorEnvolvido = parent::find($id, $tenant, $atc, $id_grupoempresarial);

        $fornecedorEnvolvido['acionamentorespostaprazoflag'] = $this->isPrazoRespostaExpirado($fornecedorEnvolvido);

        return $fornecedorEnvolvido;
    }

    /**
     * Sobrescrito para adicionar a propriedade 'acionamentorespostaprazoflag' ao retorno, que informa se o prazo para resposta do prestador do serviço expirou
     * @return array
     */
    public function findAll($tenant, $atc, $id_grupoempresarial, Filter $filter = null)
    {
        $arrFornecedoresEnvolvidos = parent::findAll($tenant, $atc, $id_grupoempresarial, $filter);

        foreach ($arrFornecedoresEnvolvidos as &$fornecedorEnvolvido) {
            // Para cada entidade fornecedor envolvido, adiciono informação de prazo da resposta do prestador
            $fornecedorEnvolvido['acionamentorespostaprazoflag'] = $this->isPrazoRespostaExpirado($fornecedorEnvolvido);
        }

        return $arrFornecedoresEnvolvidos;
    }

    /**
     * Retorna se o prazo de resposta do prestador de serviço está expirado
     */
    private function isPrazoRespostaExpirado($entity)
    {
        $dataAtual = new \DateTime();
        $dataPrazo = new \DateTime($entity['acionamentodata']);
        $dataPrazo->add(new \DateInterval('PT' . floatval($entity['acionamentorespostaprazo']) . 'M'));

        //Se já passou do prazo e o fornecedor ainda não respondeu, retorno que está expirado
        return ($dataPrazo < $dataAtual) && !$entity['acionamentoaceito'];
    }

    /**
     * Sobrescrito para adicionar validações antes de cancelar o acionamento do fornecedor
     * @return string
     */
    public function delete($negocio,$id_grupoempresarial, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity)
    {
        $SITUACAO_ORCAMENTO_ABERTO = 0;
        try {
            $this->getRepository()->begin();

            // Defino filtro para buscar orçamento do fornecedor com situação diferente de Aberto.
            $filter = new Filter();
            $expressions = [];
            $expressions[] = new FilterExpression('fornecedor', 'eq', $entity->getFornecedor()->getFornecedor());
            $expressions[] = new FilterExpression('atc', 'eq', $entity->getNegocio()->getNegocio());
            $expressions[] = new FilterExpression('status', 'neq', $SITUACAO_ORCAMENTO_ABERTO);
            $filter->setFilterExpression($expressions);

            $orcamentosFornecedor = $this->orcamentosService->findAll($tenant, $entity->getIdGrupoempresarial(), $filter);

            if ($orcamentosFornecedor != null && count($orcamentosFornecedor) > 0) {
                throw new LogicException("Não é possível cancelar um acionamento que não esteja com o orçamento aberto.", 1);
            }

            $filter2 = new Filter();
            $filter2->addToFilterExpression(new FilterExpression('requisitantefornecedor.fornecedor', 'eq', $entity->getFornecedor()->getFornecedor()));
            $documentosParaExcluir = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $entity->getNegocio()->getNegocio(), $entity->getIdGrupoempresarial(), $filter2);
            $this->excluirDocumentosFornecedor($tenant, $entity->getIdGrupoempresarial(), $documentosParaExcluir);

            $response = $this->getRepository()->delete($negocio,$id_grupoempresarial, $logged_user, $tenant,  $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }


    /**
     * Salva uma lista de documentos requisitados por um fornecedor
     */
    private function salvarDocumentosFornecedor($tenant, $logged_user, $atcId, $requisitante, $id_grupoempresarial, $documentos)
    {
        array_map(function ($documento) use ($logged_user, $tenant, $atcId, $requisitante, $id_grupoempresarial) {
            $obj = $this->montaEntidadeAtcsTiposDocumentosFornecedor($tenant, $atcId, $requisitante, $id_grupoempresarial, $documento);
            $this->atcstiposdocumentosrequisitantesService->insert($atcId, $tenant, $logged_user, $id_grupoempresarial, $obj);
        }, $documentos);
    }

    /**
     * Excluir uma lista de documentos requisitados por um fornecedor
     */
    private function excluirDocumentosFornecedor($tenant, $id_grupoempresarial, $documentos)
    {
        array_map(function ($documento) use ($tenant, $id_grupoempresarial) {
            $obj = $this->atcstiposdocumentosrequisitantesService->fillEntity($documento);
            $obj->setExcluirTipoDocumentoRequisitanteExterno(true);
            $this->atcstiposdocumentosrequisitantesService->delete($tenant, $id_grupoempresarial, $obj);
        }, $documentos);
    }


    private function montaEntidadeAtcsTiposDocumentosFornecedor($tenant, $atc, $requisitante, $id_grupoempresarial, $dadosDocumento)
    {
        $obj = new Atcstiposdocumentosrequisitantes();
        isset($dadosDocumento['tipodocumento']) ? $obj->setTipodocumento($this->tiposdocumentosService->fillEntity($dadosDocumento['tipodocumento'])) : null;
        isset($dadosDocumento['copiasimples']) ? $obj->setCopiasimples($dadosDocumento['copiasimples']) : null;
        isset($dadosDocumento['copiaautenticada']) ? $obj->setCopiaautenticada($dadosDocumento['copiaautenticada']) : null;
        isset($dadosDocumento['original']) ? $obj->setOriginal($dadosDocumento['original']) : null;
        isset($dadosDocumento['permiteenvioemail']) ? $obj->setPermiteenvioemail($dadosDocumento['permiteenvioemail']) : $obj->setPermiteenvioemail(false);
        isset($dadosDocumento['tipodocumento']) ? $obj->setTenant($tenant) : null;
        isset($dadosDocumento['naoexibiremrelatorios']) ? $obj->setNaoexibiremrelatorios($dadosDocumento['naoexibiremrelatorios']) : false;
        $obj->setNegocio($atc);
        //define o tipo de requisitante
        $obj->setRequisitantefornecedor($requisitante);
        isset($dadosDocumento['id_grupoempresarial']) ? $obj->setIdGrupoempresarial($id_grupoempresarial) : null;
        return $obj;
    }

    /**
     * @param string  $atc
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($atc, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity)
    {
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->insert($atc, $logged_user, $tenant, $id_grupoempresarial,  $entity);

            // $this->persistChildFornecedorenvolvidosanexo(null, $entity->getFornecedorenvolvidosanexo()->toArray(), $entity, $tenant, $logged_user);
            $requisitanteFornecedor = $entity->getFornecedor();

            $tiposDocumentosFornecedor = $this->fornecedoresdocumentosService->findAll($tenant, $requisitanteFornecedor->getFornecedor(), $id_grupoempresarial);
            $this->salvarDocumentosFornecedor($tenant, $logged_user, $atc, $requisitanteFornecedor, $id_grupoempresarial, $tiposDocumentosFornecedor);
            if($entity->getProposta() != null) {
                $proposta = $entity->getProposta();
                $orcamentos = $this->propostasitensService->criaOrcamentos($atc, $requisitanteFornecedor, $proposta, $logged_user, $tenant, $id_grupoempresarial);
                $response['orcamentos'] = $orcamentos;
            }

            // throw new \Exception('pediu pra parar');
            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }


    /**
     * Retorna lista de fornecedores envolvidos no atendimento, com detalhes de advertências e contatos.
     * Dados utilizados no Accordion de Prestadores de Serviço envolvidas, na ediçãod o Atendimento comercial
     */
    public function findAllAtcFornecedoresDetalhes($tenant, $atc, $id_grupoempresarial)
    {
        $dados = $this->getRepository()->findAllAtcFornecedoresDetalhes($tenant, $atc, $id_grupoempresarial);

        foreach ($dados as &$fornecedor) {
            // Insiro url da logo do fornecedor
            if (trim($fornecedor['path_logo']) !== '') {
                $fornecedor['path_logo'] = $this->uploadFilesService->getUrl($fornecedor['path_logo']);
            }
        }

        return $dados;
    }

    /**
     * Retorna lista de fornecedores envolvidos no atendimento, com detalhes de advertências e contatos.
     * Dados utilizados no Accordion de Prestadores de Serviço envolvidas, na ediçãod o Atendimento comercial
     */
    public function findAllFornecedoresenvolvidosFichafinanceira($tenant, $atc, $proposta, $id_grupoempresarial, $fornecedor = null)
    {
        $fornecedoresEnvolvidos = $this->getRepository()->findAllFornecedoresenvolvidosFichafinanceira($atc, $tenant, $id_grupoempresarial, $fornecedor);

        foreach ($fornecedoresEnvolvidos as $key => $fornecedorEnvolvido) {
            $fornecedoresEnvolvidos[$key]['acionamentorespostaprazoflag'] = $this->isPrazoRespostaExpirado($fornecedorEnvolvido);
            $fornecedoresEnvolvidos[$key]['statusString'] = $this->getStatusFichaFinanceira($fornecedorEnvolvido);
        }
        return $fornecedoresEnvolvidos;
    }

    /**
     * Metodo para converter número do status para string
     */
    public function getStatusFichaFinanceira($fornecedorEnvolvido)
    {
        if (isset($fornecedorEnvolvido['status'])) {
            switch ($fornecedorEnvolvido['status']) {
                case 0:
                    return 'Aberto';
                case 1:
                    return 'Enviado';
                case 2:
                    return 'Aprovado';
                case 3:
                    return 'Renegociando';
                case 4:
                    return 'Recusado';
            }
        }
    }

    /**
     * @param string  $negocio
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity
     * @return string
     * @throws \Exception
     */
    public function aprovarOrcamentosNegocioFornecedor($atc, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity)
    {
        try {
            $this->getRepository()->begin();
            $podeAprovarOrcamento = $this->orcamentosService->podeAprovarOrcamento($tenant, $id_grupoempresarial, $atc, $entity->getFornecedor()->getFornecedor());
            $motivoNaoPodeAprovar = "Não é possível aprovar orçamento, pois já existe contrato gerado para o fornecedor";

            // Se tem estabelecimento, verifico se algum serviço do tipo externo/prestadora acionada está sem o prestador terceirizado preenchido
            $arrDadosGerarContasPagar = [];
            if ($podeAprovarOrcamento && $entity->getFornecedor()->getEstabelecimentoid() != null) {
                $arrDadosFicha = $this->findAllFornecedoresenvolvidosFichafinanceira($tenant, $atc, null, $id_grupoempresarial, $entity->getFornecedor()->getFornecedor());

                for ($i=0; $i < count($arrDadosFicha); $i++) { 
                    $dadoFicha = $arrDadosFicha[$i];
                    
                    if (
                        $dadoFicha['orcamento_composicao'] != null
                        && (
                            $dadoFicha['orcamento_servicotipo'] == 1 // Serviço Externo.
                            || $dadoFicha['orcamento_servicotipo'] == 2 // Serviço Prestadora Acionada
                        )
                    ) {
                        // Se prestador terceirizado não estiver preenchido, saio e não deixo preencher orçamento
                        if ($dadoFicha['orcamento_prestador_terceirizado'] == null) {
                            $podeAprovarOrcamento = false;
                            $motivoNaoPodeAprovar = "Não é possível aprovar orçamento, pois existem serviços sem o prestador terceirizado preenchido.";
                            break;
                        }

                        $arrDadosGerarContasPagar[] = $dadoFicha;
                    }
                }
            }
            
            //Verifica se pode aprovar o orçamento
            if ( $podeAprovarOrcamento ) {
                $this->orcamentosService->aprovarOrcamentosDoFornecedor($atc, $entity->getFornecedor(), $logged_user, $tenant, $id_grupoempresarial);

                // Se tem estabelecimento
                if ($entity->getFornecedor()->getEstabelecimentoid() != null) {
                    // Crio registros de contas a pagar
                    foreach ($arrDadosGerarContasPagar as $dadoContaPagar) {
                        // Crio objeto do fornecedor
                        $prestadorTerceirizado = new Fornecedores();
                        $prestadorTerceirizado->setFornecedor($dadoContaPagar['orcamento_prestador_terceirizado']);
                        $prestadorTerceirizado->setNomefantasia($dadoContaPagar['orcamento_prestador_terceirizado_nomefantasia']);

                        // Busco tipo de documento do fornecedor
                        $filter = new Filter();
                        $filter->setFilterExpression([
                            new FilterExpression('prestador', 'eq', $prestadorTerceirizado->getFornecedor())
                        ]);
                        
                        $arrTiposDocumento = $this->tiposdocumentosService->findAll($tenant, $id_grupoempresarial, $filter);
                        $tipoDocumentoExiste = ($arrTiposDocumento != null && count($arrTiposDocumento) > 0);
        
                        $tipoDocumentoArr = null;
                        // Se o tipo de documento não existir, eu crio
                        if ($tipoDocumentoExiste) {
                            $tipoDocumentoArr = $arrTiposDocumento[0];
                        } else {
                            $novoTipoDocumento = new Tiposdocumentos();
                            $novoTipoDocumento->setNome( 'Nota Fiscal - ' . $prestadorTerceirizado->getNomefantasia() );
                            $novoTipoDocumento->setEmissaonoprocesso( true );
                            $novoTipoDocumento->setPrestador( $prestadorTerceirizado->getFornecedor() );
        
                            $tipoDocumentoArr = $this->tiposdocumentosService->insert($logged_user, $tenant, $id_grupoempresarial, $novoTipoDocumento);
                        }
                        $tipoDocumentoObj = $this->tiposdocumentosService->fillEntity($tipoDocumentoArr);
                        
                        $documentoAtcExiste = false;
                        $documentoAtcObj = null;
                        // Se o tipo de documento existe, verifico se já está no atendimento.
                        if ($tipoDocumentoExiste) {
                            $filter = new Filter();
                            $filter->setFilterExpression([
                                new FilterExpression('tipodocumento.tipodocumento', 'eq', $tipoDocumentoObj->getTipoDocumento())
                            ]);
                            $arrDocumentosAtc = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $atc, $id_grupoempresarial, $filter);
                            $documentoAtcExiste = ($arrDocumentosAtc != null && count($arrDocumentosAtc) > 0);
                            $documentoAtcObj = $this->atcstiposdocumentosrequisitantesService->fillEntity($arrDocumentosAtc[0]);
                        }
        
                        // Se o documento do fornecedor não existe no atendimento, eu crio.
                        if (!$documentoAtcExiste) {
                            $novoAtcDocumento = new Atcstiposdocumentosrequisitantes();
                            $novoAtcDocumento->setTipodocumento( $tipoDocumentoObj );
                            $novoAtcDocumento->setNegocio( $atc );
                            $novoAtcDocumento->setRequisitantefornecedor( $prestadorTerceirizado );
                            $novoAtcDocumento->setPedirinformacoesadicionais( true );
                            $novoAtcDocumento->setPermiteenvioemail( false );
                            $novoAtcDocumento->setCopiasimples( false );
                            $novoAtcDocumento->setCopiaautenticada( false );
                            $novoAtcDocumento->setOriginal( false );
        
                            $documentoAtcArr = $this->atcstiposdocumentosrequisitantesService->insert($atc, $tenant, $logged_user, $id_grupoempresarial, $novoAtcDocumento);
                            $documentoAtcObj = $this->atcstiposdocumentosrequisitantesService->fillEntity($documentoAtcArr);
                        }
                        
                        // Crio registro de conta a pagar para o serviço do fornecedor
                        $contaPagarObj = new Atcscontasapagar();
                        $contaPagarObj->setAtc( $atc );
                        $contaPagarObj->setPrestador( $prestadorTerceirizado );
                        $contaPagarObj->setOrcamento( $dadoContaPagar['orcamento'] );
                        $contaPagarObj->setServico( $dadoContaPagar['orcamento_composicao'] );
                        $contaPagarObj->setDescricao( $dadoContaPagar['orcamento_descricao'] );
                        $contaPagarObj->setQuantidade( 1 );

                        $this->atcscontasapagarService->insert($tenant, $id_grupoempresarial, $atc, $logged_user, $contaPagarObj);
                    }
                }
            } else {
                throw new LogicException($motivoNaoPodeAprovar, 1);
            }
            
            $this->getRepository()->commit();

            return;
        } catch(LogicException $e ) {
            $this->getRepository()->rollBack();
            throw $e;           
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /** 
     * Reabre os orçamentos do fornecedor em um atendimento comercial
     * @param string  $negocio
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity
     * @return string
     * @throws \Exception
     */
    public function reabrirOrcamentosNegocioFornecedor($negocio, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity){
        try {
            $this->getRepository()->begin();
            $podeReabrirOrcamento = $this->orcamentosService->podeReabrirOrcamento($tenant, $id_grupoempresarial, $negocio, $entity->getFornecedor()->getFornecedor());
            $motivoNaoPodeReabrir = "Orçamento não pode ser reaberto, pois já foi gerada responsabilidade financeira para o fornecedor.";

            $arrDadosFicha = $this->findAllFornecedoresenvolvidosFichafinanceira($tenant, $negocio, null, $id_grupoempresarial, $entity->getFornecedor()->getFornecedor());
            
            $filter = new Filter();
            $arrFilterExpression = [];
            for ($i=0; $i < count($arrDadosFicha); $i++) { 
                $dadoFicha = $arrDadosFicha[$i];
                
                if (
                    $dadoFicha['orcamento_composicao'] != null
                    && (
                        $dadoFicha['orcamento_servicotipo'] == 1 // Serviço Externo.
                        || $dadoFicha['orcamento_servicotipo'] == 2 // Serviço Prestadora Acionada
                    )
                ) {
                    $arrFilterExpression[] = new FilterExpression('orcamento', 'eq', $dadoFicha['orcamento']);
                }
            }
            $filter->setFilterExpression($arrFilterExpression);
            $arrContapagar = [];
            $arrAtcsDocsRequisitantes = [];
            // Se pode reabrir orçamento e possui estabelecimento, verifico se possui contas a pagar para o fornecedor processadas ou com documento preenchido, no atendimento
            if ($podeReabrirOrcamento && $entity->getFornecedor()->getEstabelecimentoid() != null) {
                $arrContapagar = $this->atcscontasapagarService->findAll($tenant, $id_grupoempresarial, $negocio, $filter);

                $filterAtcDocRequisitante = new Filter();
                $arrFilterExpressionAtcDocRequisitante = [];
                for ($i=0; $i < count($arrContapagar); $i++) { 
                    $dadoContapagar = $arrContapagar[$i];

                    // Adiciono filtros para buscar documentos requisitados do atendimento, para excluir após reabrir o orçamento
                    $arrFilterExpressionAtcDocRequisitante[] = new FilterExpression('tipodocumento.prestador', 'eq', $dadoContapagar['prestador']['fornecedor']);
                    $arrFilterExpressionAtcDocRequisitante[] = new FilterExpression('requisitantefornecedor.fornecedor', 'eq', $dadoContapagar['prestador']['fornecedor']);

                    // Se algum documetno já foi informado para uma conta a pagar, não permito reabrir.
                    if ( $dadoContapagar['negociodocumento'] != null ) {
                        $podeReabrirOrcamento = false;
                        $motivoNaoPodeReabrir = "Orçamento não pode ser reaberto, pois existe conta a pagar com documento informado.";
                        break;
                    }
                }

                if ($podeReabrirOrcamento && count($arrFilterExpressionAtcDocRequisitante) > 0 ) {
                    $filterAtcDocRequisitante->setFilterExpression($arrFilterExpressionAtcDocRequisitante);
                    $arrAtcsDocsRequisitantes = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $negocio, $id_grupoempresarial, $filterAtcDocRequisitante);
                }
            }
            
            //Verifica se pode aprovar o orçamento
            if ( $podeReabrirOrcamento ) {
                $this->orcamentosService->reabrirOrcamentosDoFornecedor($negocio, $entity->getFornecedor(), $logged_user, $tenant, $id_grupoempresarial);

                // Excluo contas a pagar atreladas
                foreach ($arrContapagar as $dadoContapagar) {
                    $contaPagarObj = $this->atcscontasapagarService->fillEntity($dadoContapagar);
                    $this->atcscontasapagarService->delete($tenant, $id_grupoempresarial, $logged_user, $contaPagarObj);
                }

                // Excluo documentos requisitados do atendimento que tem o prestador requisitante do do contas a pagar
                foreach ($arrAtcsDocsRequisitantes as $atcDocRequisitanteArr) {
                    $atcDocRequisitanteObj = $this->atcstiposdocumentosrequisitantesService->fillEntity($atcDocRequisitanteArr);
                    $atcDocRequisitanteObj->setExcluirTipoDocumentoRequisitanteExterno(true);
                    $this->atcstiposdocumentosrequisitantesService->delete($tenant, $id_grupoempresarial, $atcDocRequisitanteObj);
                }
            } else {
                throw new LogicException($motivoNaoPodeReabrir, 1);
            }
            
            $this->getRepository()->commit();
        } catch(LogicException $e ) {
            $this->getRepository()->rollBack();
            throw $e;           
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        } 
    }

    /**
     * Retorna as configurações de desconto do fornecedor envolvido
     */
    public function buscarConfiguracaoDescontoFichaFinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial){
        return $this->getRepository()->buscarConfiguracaoDescontoFichaFinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial);
    }

    /** 
     * Calcula os valores de desconto dos orçamentos
     */
    public function calcularDescontoGlobalOrcamentos($tenant, $id_grupoempresarial, $atc, $entity, $configDescontos){
        $qtdOrcamentos = $configDescontos['qtd_orcamentos_faturar'];
        $valorOrcamento = $configDescontos['total_orcamento_faturar'];

        // Calculo o valor do desconto global
        $descontoglobalValor = $entity->getDescontoglobal();

        // Se for do tipo 2(percetual), busco o valor total do orçamento para fazer o calculo
        if ($entity->getDescontoglobaltipo() == 2) {
            $descontoglobalValor = ($valorOrcamento / 100) * $entity->getDescontoglobal();
            $descontoglobalValor = round($descontoglobalValor, 2);
        }
        
        // Calculo valor do desconto unitario
        $descontoUnit = 0;
        $descontoUnitResto = 0;

        if ($qtdOrcamentos > 0) {
            $descontoUnit = (($descontoglobalValor * 1) / $qtdOrcamentos);
            $descontoUnit = round($descontoUnit, 2);
            $descontoUnitResto = $descontoglobalValor - ($descontoUnit * ($qtdOrcamentos - 1));
        }

        // Retorno informações de desconto preenchidas
        return [
            'descontoglobalunitario' => $descontoUnit,
            'descontoglobalresto' => $descontoUnitResto
        ];
    }

    /**
    * @param string  $negocio
    * @param string  $logged_user
    * @param string  $tenant
    * @param string  $id_grupoempresarial
    * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity
    * @return string
    * @throws \Exception
    */
    public function fornecedorenvolvidoatualizarconfiguracaodescontos($negocio,$logged_user,$tenant,$id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity){
        try {
            // Busco configurações de desconto
            $configDescontos = $this->buscarConfiguracaoDescontoFichaFinanceira($negocio, $entity->getFornecedor()->getFornecedor(), $tenant, $id_grupoempresarial);
            $valoresDesconto = null;

            // Se não estiver com status aberto, saio da função
            if ($configDescontos['orcamentostatus'] != 0) {
                throw new LogicException("A configuração de descontos só pode ser atualizada caso o orçamento esteja aberto.", 1);
                return;
            }

            // Se possui desconto global com valor maior que zero, calculo descontos e preencho na entidade
            if ($entity->getPossuidescontoglobal() && $entity->getDescontoglobal() > 0) {
                $valoresDesconto = $this->calcularDescontoGlobalOrcamentos(
                    $tenant, $id_grupoempresarial, $negocio, $entity, $configDescontos
                );
                $entity->setDescontoglobalunitario($valoresDesconto['descontoglobalunitario']);
                $entity->setDescontoglobalresto($valoresDesconto['descontoglobalresto']);
                $entity->setDescontoglobalrestoorcamento($configDescontos['orcamento']);
            }

            try {
                $this->getRepository()->begin();
    
                // Atualizo configuração de descontos
                $response = $this->getRepository()->fornecedorenvolvidoatualizarconfiguracaodescontos($negocio, $logged_user, $tenant, $id_grupoempresarial, $entity);

                // Atualizo descontos do atendimento.
                $response = $this->getRepository()->fornecedorenvolvidoatualizardescontoorcamentos($negocio,$logged_user,$tenant,$id_grupoempresarial, $entity);
                $response = [
                    'descontoglobalunitario' => $entity->getDescontoglobalunitario(),
                    'descontoglobalresto' => $entity->getDescontoglobalresto(),
                    'descontoglobalrestoorcamento' => $entity->getDescontoglobalrestoorcamento()
                ];

                $this->getRepository()->commit();

                return $response;
    
            }catch(\Exception $e){
                $this->getRepository()->rollBack();
                throw $e;
            }
        } catch(\Exception $e){
            throw $e;
        }
    }

    /**
    * @param string  $negocio
    * @param string  $logged_user
    * @param string  $tenant
    * @param string  $id_grupoempresarial
    * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity
    * @return string
    * @throws \Exception
    */
    public function fornecedorenvolvidoatualizardescontoorcamentos($negocio, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity){
        try {
            // Busco configurações de desconto
            $configDescontos = $this->buscarConfiguracaoDescontoFichaFinanceira($negocio, $entity->getFornecedor()->getFornecedor(), $tenant, $id_grupoempresarial);

            // Se estiver configurado para desconto global, calculo descontos e chamo função de banco
            if ($configDescontos['possuidescontoglobal'] && $configDescontos['descontoglobal'] > 0) {
                $entity->setDescontoglobal($configDescontos['descontoglobal']);
                $entity->setDescontoglobaltipo($configDescontos['descontoglobaltipo']);
                // Calculo descontos
                $valoresDesconto = $this->calcularDescontoGlobalOrcamentos(
                    $tenant, $id_grupoempresarial, $negocio, $entity, $configDescontos
                );
                $entity->setPossuidescontoparcial($configDescontos['possuidescontoparcial']);
                $entity->setPossuidescontoglobal($configDescontos['possuidescontoglobal']);
                $entity->setDescontoglobalunitario($valoresDesconto['descontoglobalunitario']);
                $entity->setDescontoglobalresto($valoresDesconto['descontoglobalresto']);
                $entity->setDescontoglobalrestoorcamento($configDescontos['orcamento']);
                
                // Chamo função de atualizar desconto
                $response = parent::fornecedorenvolvidoatualizardescontoorcamentos($negocio, $logged_user, $tenant, $id_grupoempresarial, $entity);
            }

            return $entity;
        }catch(\Exception $e){
            throw $e;
        }
    }
}
