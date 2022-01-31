<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Entity\Crm\Propostasitens;
use Nasajon\MDABundle\Entity\Crm\Propostasitensfamilias;
use Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes;
use Nasajon\MDABundle\Entity\Gp\Tarefas;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Entity\Financas\Itenscontratos;
use Nasajon\MDABundle\Service\Crm\PropostasitensService as ParentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Entity\Financas\Projetos;
use Nasajon\MDABundle\Entity\Financas\Contratos;
use LogicException;
use Nasajon\MDABundle\Entity\Crm\Orcamentos;
use Nasajon\AppBundle\Service\Crm\OrcamentosService;

/**
 * sobreescrito para dar suporte a criação de tarefa pelo CRM diretamente no GP.
 * sobreescrito para dar suporte a edicao de tarefa pelo CRM diretamente no GP.
 */
class PropostasitensService extends ParentService
{

    public $servicosItensdefaturamentoService;
    public $financasItenscontratosService;
    public $crmComposicoesService;
    private $tarefasService;
    protected $wbCnfgrcsSrvc;
    private $crmTmpltsPrpstsCptlsCmpscsService;
    private $crmTmpltsCmpscsFmlsService;
    private $crmTmpltsCmpscsFncsService;
    private $crmPrpstFmlsService;
    private $crmPrpstsFncsService;
    private $atcsRepository;
    private $projetosService;
    private $fornecedoresService;
    private $propostasRepository;
    private $financasContratosService;
    private $crmOrcamentosService;
    private $templatesPropostasRepository;
    private $responsabilidadesfinanceirasvaloresRepository;
    private $fornecedoresEnvolvidosRepository;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\PropostasitensRepository $repository,
        $servicosItensdefaturamentoService,
        $financasItenscontratosService,
        $crmComposicoesService,
        $tarefasService,
        $crmPrpststnsndrcsSrvc,
        $wbCnfgrcsSrvc,
        $crmTmpltsPrpstsCptlsCmpscsService,
        $crmTmpltsCmpscsFmlsService,
        $crmTmpltsCmpscsFncsService,
        $crmPrpstFmlsService,
        $crmPrpstsFncsService,
        $crmPropostaCapituloService,
        $atcsRepository,
        $projetosService,
        $fornecedoresService,
        $financasContratosService,
        $crmOrcamentosService,
        $propostasRepository,
        $templatesPropostasRepository,
        $responsabilidadesfinanceirasvaloresRepository,
        $fornecedoresEnvolvidosRepository
    ) {
        $this->repository = $repository;
        $this->servicosItensdefaturamentoService = $servicosItensdefaturamentoService;
        $this->financasItenscontratosService = $financasItenscontratosService;
        $this->crmComposicoesService = $crmComposicoesService;
        $this->tarefasService = $tarefasService;
        $this->crmPrpststnsndrcsSrvc = $crmPrpststnsndrcsSrvc;
        $this->wbCnfgrcsSrvc = $wbCnfgrcsSrvc;
        $this->crmTmpltsPrpstsCptlsCmpscsService = $crmTmpltsPrpstsCptlsCmpscsService;
        $this->crmTmpltsCmpscsFmlsService = $crmTmpltsCmpscsFmlsService;
        $this->crmTmpltsCmpscsFncsService = $crmTmpltsCmpscsFncsService;
        $this->crmPrpstFmlsService = $crmPrpstFmlsService;
        $this->crmPrpstsFncsService = $crmPrpstsFncsService;
        $this->crmPropostaCapituloService = $crmPropostaCapituloService;
        $this->atcsRepository = $atcsRepository;
        $this->projetosService = $projetosService;
        $this->fornecedoresService = $fornecedoresService;
        $this->propostasRepository = $propostasRepository;
        $this->financasContratosService = $financasContratosService;
        $this->crmOrcamentosService = $crmOrcamentosService;
        $this->templatesPropostasRepository = $templatesPropostasRepository;
        $this->responsabilidadesfinanceirasvaloresRepository = $responsabilidadesfinanceirasvaloresRepository;
        $this->fornecedoresEnvolvidosRepository = $fornecedoresEnvolvidosRepository;
    }

    /**
     * Cria todos os orçamentos de propostasitens relacionados ao atc e fornecedor
     */
    public function criaOrcamentos($atc, $fornecedor, $proposta, $logged_user, $tenant, $id_grupoempresarial)
    {
        // Busca propostasitens, filtrando por fornecedor
        $filter = new Filter();
        $expressions = [
            new FilterExpression('fornecedor', 'eq', $fornecedor->getFornecedor())
        ];
        $filter->setFilterExpression($expressions);
        $arrPropostaitem = $this->findAll($tenant, $atc, $proposta, $id_grupoempresarial, $filter);
        
        $fornecedorArr = $this->fornecedoresService->find($fornecedor->getFornecedor(), $tenant, $id_grupoempresarial);
        $fornecedor = $this->fornecedoresService->fillEntity($fornecedorArr);

        // Busco algumas informações do atendimento e do fornecedor
        $atcDadosSimples = $this->atcsRepository->findSimples($atc, $tenant, $id_grupoempresarial);
        $estabelecimentoObj = $fornecedor->getEstabelecimentoid();
        $estabelecimento = $estabelecimentoObj != null ? $estabelecimentoObj->getEstabelecimento() : null;

        $fornecedorTerceirizado = $estabelecimento == null || $estabelecimento == '';
        $esperaPgtoSeguradora = $fornecedor->getEsperapagamentoseguradora();
        $atcPossuiSeguradora = $atcDadosSimples['possuiseguradora'];

        $orcamentos = [];
        foreach ($arrPropostaitem as $propostaitem) {
            // Busca propostasitensfamilias(Mercadorias) para cada propostaitem
            $arrPropostaitemfamilias = $this->crmPrpstFmlsService->findAll($tenant, $propostaitem['propostaitem'], $id_grupoempresarial);

            // Cria orçamento referentes ao serviço(Composição presente em propostaitem)
            $orcamento = new Orcamentos();
            $orcamento->setFornecedor($fornecedor);
            $orcamento->setAtc($atc);
            $orcamento->setComposicao($propostaitem['composicao']['composicao']);
            $orcamento->setPropostaitem($propostaitem['propostaitem']);
            $orcamento->setStatus(0); // Aberto
            $orcamento->setFaturamentotipo($this->crmOrcamentosService->getTipoFaturamentoDefault($atcPossuiSeguradora, $fornecedorTerceirizado, $esperaPgtoSeguradora));
            $orcamento->setQuantidade(1);
            $orcamento->setDescricaomanual($propostaitem['nomeservicoalterado']);
            if ($orcamento->getDescricaomanual()){
                $orcamento->setDescricao($propostaitem['nome']);
            }

            $orcamentoCriado = $this->crmOrcamentosService->insert($tenant, $id_grupoempresarial, $logged_user, $orcamento);
            $orcamentos[] = $orcamentoCriado;

            // Para cada família, criar um orçamento
            foreach ($arrPropostaitemfamilias as $propostaitemfamilia) {
                $orcamento = new Orcamentos();
                $orcamento->setFornecedor($fornecedor);
                $orcamento->setAtc($atc);
                $orcamento->setFamilia($propostaitemfamilia['familia']['familia']);
                $orcamento->setPropostaitem($propostaitem['propostaitem']);
                $orcamento->setStatus(0); // Aberto
                $orcamento->setQuantidade(1);
                $orcamento->setFaturamentotipo($this->crmOrcamentosService->getTipoFaturamentoDefault($atcPossuiSeguradora, $fornecedorTerceirizado, $esperaPgtoSeguradora));

                // Seto quantidade, se estiver preenchida na família
                if (isset($propostaitemfamilia['quantidade']) && is_int($propostaitemfamilia['quantidade'])){
                    $orcamento->setQuantidade($propostaitemfamilia['quantidade']);
                }

                $orcamento->setValorunitario($propostaitemfamilia['valor']);
                $orcamento->setDescricaomanual($propostaitemfamilia['nomefamiliaalterado']);
                if ($orcamento->getDescricaomanual()){
                    $orcamento->setDescricao($propostaitemfamilia['nome']);
                }
                $orcamentoCriado = $this->crmOrcamentosService->insert($tenant, $id_grupoempresarial, $logged_user, $orcamento);
                $orcamentos[] = $orcamentoCriado;
            }
        }

        return $orcamentos;
    }

    /**
     * Adiciona informações de prazo no Propostaitem
     */
    protected function adicionarInformacaoHoraPrazo(&$propostaitem, $tenant, $prazo, $alerta)
    {
        $propostaitem['prazo'] = $prazo;
        $propostaitem['alerta'] = $alerta;

        //Conversão de data
        $prazo = date("Y-m-d H:i:s", strtotime($propostaitem['created_at']) + 60 * $propostaitem['prazo']); //prazo
        $alerta = date("Y-m-d H:i:s", strtotime($propostaitem['created_at']) + (60 * $propostaitem['prazo']) - (60 * $propostaitem['alerta'])); //alerta
        $propostaitem['agora'] = getdate();
        $propostaitem['agora'] = date("Y-m-d H:i:s");
        $propostaitem['alerta'] = $alerta;
        $propostaitem['prazo'] = $prazo;

        //Separa data e hora
        if ($propostaitem['previsaodatahorainicio'] != null) {
            $objDataHoraInicio = \DateTime::createFromFormat('Y-m-d H:i:s', $propostaitem['previsaodatahorainicio']);
            $propostaitem['previsaodatainicio'] = $objDataHoraInicio->format('Y-m-d');
            $propostaitem['previsaohorainicio'] = $objDataHoraInicio->format('H:i:s');
        } else {
            $propostaitem['previsaodatainicio'] = null;
            $propostaitem['previsaohorainicio'] = null;
        }

        if ($propostaitem['previsaodatahorafim'] != null) {
            $objDataHoraFim = \DateTime::createFromFormat('Y-m-d H:i:s', $propostaitem['previsaodatahorafim']);
            $propostaitem['previsaodatafim'] = $objDataHoraFim->format('Y-m-d');
            $propostaitem['previsaohorafim'] = $objDataHoraFim->format('H:i:s');
        } else {
            $propostaitem['previsaodatafim'] = null;
            $propostaitem['previsaohorafim'] = null;
        }
    }

    public function geraItemContratoPagamento($contratoAPagar, &$responsabilidade, $fornecedor, $id_grupoempresarial, $logged_user, $tenant)
    {
        //pegar os orcamentos do item (propostaitem, funcao ou familia), deve buscar o orcamento do funcao, execucao ou familia
        $filter = new Filter();
        $arrFilterExpression[] = new FilterExpression('propostaitem', 'eq', $responsabilidade->getPropostaItem());
        $propostaitemfuncao = null;
        $propostaitemfamilia = null;
        $composicao = null;
        //se não tem funcao ou familia, é execucao de servico.
        if (
            $responsabilidade->getPropostaitemfuncao() == null &&
            $responsabilidade->getPropostaitemfamilia() == null
        ) {
            // $arrFilterExpression[] = new FilterExpression('orcamento', 'eq', $responsabilidade->getPropostaitemobj()->getServicoorcamento()->getOrcamento());
            $arrFilterExpression[] = new FilterExpression('execucaodeservico', 'eq', true);
            $arrFilterExpression[] = new FilterExpression('propostaitemfamilia', 'eq', null);
            $arrFilterExpression[] = new FilterExpression('propostaitemfuncao', 'eq', null);
            $composicao = $responsabilidade->getPropostaitemobj()->getComposicao()->getComposicao();
        } elseif ($responsabilidade->getPropostaitemfuncao() !== null) {
            $arrFilterExpression[] = new FilterExpression('propostaitemfamilia', 'eq', null);
            $arrFilterExpression[] = new FilterExpression('propostaitemfuncao', 'eq', $responsabilidade->getPropostaitemfuncao());
            $propostaitemfuncao = $this->crmPrpstsFncsService->findObject(
                $responsabilidade->getPropostaitemfuncao(),
                $tenant,
                $responsabilidade->getPropostaItem(),
                $id_grupoempresarial
            );
            $composicao = $propostaitemfuncao->getComposicao();
        } elseif ($responsabilidade->getPropostaitemfamilia() !== null) {
            $arrFilterExpression[] = new FilterExpression('propostaitemfamilia', 'eq', $responsabilidade->getPropostaitemfamilia());
            $arrFilterExpression[] = new FilterExpression('propostaitemfuncao', 'eq', null);
            $propostaitemfamilia = $this->crmPrpstFmlsService->findObject(
                $responsabilidade->getPropostaitemfamilia(),
                $tenant,
                $responsabilidade->getPropostaItem(),
                $id_grupoempresarial
            );
            $composicao = $propostaitemfamilia->getComposicao();
        }
        $filter->setFilterExpression($arrFilterExpression);
        $orcamentos = $this->crmOrcamentosService->findAll($tenant, $id_grupoempresarial, $filter);
        if (empty($orcamentos)) {
            throw new \Exception("Não é possível gerar contrato antes de preencher os orçamentos!");
        }
        $orcamento = $orcamentos[0];
        $valor = floatval($orcamento['valor']) + floatval($orcamento['acrescimo']) - floatval($orcamento['desconto']);
        $diasvencimento = $fornecedor->getDiasparavencimento() ? $fornecedor->getDiasparavencimento() : $this->wbCnfgrcsSrvc->getValor($tenant, 'DIASPARAVENCIMENTO');
        $fornecedor->setDiasparavencimento($diasvencimento);
        $composicao = $this->crmComposicoesService->find($composicao, $tenant, $id_grupoempresarial);
        $itemdefaturamento = $this->servicosItensdefaturamentoService->findObject($composicao['servicotecnico']['itemdefaturamento'], $tenant, $id_grupoempresarial);

        $itemcontratoAPagar = new Itenscontratos();
        $itemcontratoAPagar->setContrato($contratoAPagar->getContrato());
        $itemcontratoAPagar->setQuantidade(1);
        $itemcontratoAPagar->setValor($valor);
        $itemcontratoAPagar->setNumerodiasparavencimento($fornecedor->getDiasparavencimento());
        $itemcontratoAPagar->setCodigoservico($itemdefaturamento->getCodigoservico());
        $itemcontratoAPagar->setDescricaoservico($itemdefaturamento->getDescricaoservico());
        $itemcontratoAPagar->setItemfaturamento($itemdefaturamento->getServico());
        $itemcontratoAPagar->setIdgrupoempresarial($id_grupoempresarial);
        $itemcontratoAPagarNovo = $this->financasItenscontratosService->insertItemPagamento($id_grupoempresarial, $logged_user, $tenant, $itemcontratoAPagar);
        $itemcontratoAPagarNovo = $this->financasItenscontratosService->findObject($itemcontratoAPagarNovo, $tenant, $id_grupoempresarial);

        $key = array_keys($responsabilidade->getResponsabilidadesfinanceirasvalores()->toArray())[0];
        $responsabilidadeValor = $responsabilidade->getResponsabilidadesfinanceirasvalores()[$key];
        $responsabilidadeValor->setItemcontratoapagar($itemcontratoAPagarNovo->getItemcontrato());
        $responsabilidadeValor->setContratoapagar($contratoAPagar->getContrato());

        // Código comentado pois agora o itens de contrato estará associado à tabela de rateio da responsabilidade financeira,
        // já que um serviço pode ser dividido para mais de um responsavel, gerando mais de um contrato.

        // if($responsabilidade->getPropostaitemfuncao() == null && 
        //   $responsabilidade->getPropostaitemfamilia() == null) {
        //   $propostaitem = $responsabilidade->getPropostaitemobj();
        //   $propostaitem->setItemcontratoAPagar($itemcontratoAPagarNovo);
        //   $this->editaItemContratoAPagarNaPropostaItem($tenant, $logged_user, $propostaitem);
        // } elseif ($responsabilidade->getPropostaitemfuncao() !== null) {
        //   $propostaitemfuncao->setItemcontratoAPagar($itemcontratoAPagarNovo);
        //   $this->crmPrpstsFncsService->editaItemContratoAPagarNaPropostaItemFuncao($tenant, $propostaitemfuncao);
        // } elseif ($responsabilidade->getPropostaitemfamilia() !== null) {
        //   $propostaitemfamilia->setItemcontratoAPagar($itemcontratoAPagarNovo);
        //   $this->crmPrpstFmlsService->editaItemContratoAPagarNaPropostaItemFamilia($tenant, $propostaitemfamilia);
        // }
        return $itemcontratoAPagarNovo;
    }

    /**
     * Gera o contrato
     * A quantidade foi removida do item de proposta, porque para o caso do cliente se houver dois serviiços eles serão listados um embaixo do outro
     * @param \Nasajon\MDABundle\Entity\Financas\Contratos $contrato
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitens $propostaitem
     * @param \Nasajon\MDABundle\Entity\Ns\Clientes $cliente
     * @param string $id_grupoempresarial
     * @param json $logged_user
     * @param integer $tenant
     * @return \Nasajon\MDABundle\Entity\Crm\Propostasitens
     */
    public function geraItemContrato($contrato, &$responsabilidade, $cliente, $id_grupoempresarial, $logged_user, $tenant, $atc = null, $contratanteResponsabilidadeFinanceira, $valorResponsabilidadeFinanceira)
    {
        //pegar os orcamentos do item (propostaitem, funcao ou familia), deve buscar o orcamento do funcao, execucao ou familia
        $filter = new Filter();
        $arrFilterExpression[] = new FilterExpression('propostaitem', 'eq', $responsabilidade->getPropostaItem());
        $propostaitemfuncao = null;
        $propostaitemfamilia = null;
        $composicao = null;

        //se não tem funcao ou familia, é execucao de servico.
        if (
            $responsabilidade->getPropostaitemfuncao() == null &&
            $responsabilidade->getPropostaitemfamilia() == null
        ) {
            // $servicoorcamento = $responsabilidade->getPropostaitemobj()->getServicoorcamento();
            // $orcamento = is_null($servicoorcamento) ? null : $servicoorcamento->getOrcamento();
            // $arrFilterExpression[] = new FilterExpression('orcamento', 'eq', $orcamento);
            $arrFilterExpression[] = new FilterExpression('execucaodeservico', 'eq', true);
            $arrFilterExpression[] = new FilterExpression('propostaitemfamilia', 'eq', null);
            $arrFilterExpression[] = new FilterExpression('propostaitemfuncao', 'eq', null);
            $composicao = $responsabilidade->getPropostaItemobj()->getComposicao()->getComposicao();
            if (is_null($composicao)) {
                throw new LogicException("Não foi possível encontrar o serviço[ " . $responsabilidade->getPropostaItemobj()->getNome() . ".", 1);
            }
        } elseif ($responsabilidade->getPropostaitemfuncao() !== null) {
            $arrFilterExpression[] = new FilterExpression('propostaitemfamilia', 'eq', null);
            $arrFilterExpression[] = new FilterExpression('propostaitemfuncao', 'eq', $responsabilidade->getPropostaitemfuncao());
            $propostaitemfuncao = $this->crmPrpstsFncsService->findObject(
                $responsabilidade->getPropostaitemfuncao(),
                $tenant,
                $responsabilidade->getPropostaItem(),
                $id_grupoempresarial
            );
            $composicao = $propostaitemfuncao->getComposicao();
            if (is_null($composicao)) {
                throw new LogicException("Não foi possível encontrar o serviço do profissional[ " . $propostaitemfuncao->getFuncao()->getCodigo() . ".", 1);
            }
        } elseif ($responsabilidade->getPropostaitemfamilia() !== null) {
            $arrFilterExpression[] = new FilterExpression('propostaitemfamilia', 'eq', $responsabilidade->getPropostaitemfamilia());
            $arrFilterExpression[] = new FilterExpression('propostaitemfuncao', 'eq', null);
            $propostaitemfamilia = $this->crmPrpstFmlsService->findObject(
                $responsabilidade->getPropostaitemfamilia(),
                $tenant,
                $responsabilidade->getPropostaItem(),
                $id_grupoempresarial
            );
            $composicao = $propostaitemfamilia->getComposicao();
            if (is_null($composicao)) {
                throw new LogicException("Não foi possível encontrar o serviço do produto[ " . $propostaitemfamilia->getFamilia()->getCodigo() . ".", 1);
            }
        }

        $filter->setFilterExpression($arrFilterExpression);
        $orcamentos = $this->crmOrcamentosService->findAll($tenant, $id_grupoempresarial, $filter);
        if (empty($orcamentos)) {
            throw new LogicException("Não é possível gerar contrato antes de preencher os orçamentos!", 1);
        }
        $orcamento = $orcamentos[0];
        $valor = $valorResponsabilidadeFinanceira;
        $composicao = $this->crmComposicoesService->find($composicao, $tenant, $id_grupoempresarial);
        if (!isset($composicao['servicotecnico'])) {
            throw new LogicException("Não foi possível encontrar o serviço técnico do serviço[ " . $composicao->getNome() . ".", 1);
        }
        if (!isset($composicao['servicotecnico']['itemdefaturamento'])) {
            throw new LogicException("Não foi possível encontrar o serviço técnico do serviço[ " . $composicao->getNome() . ".", 1);
        }

        $itemdefaturamento = $this->servicosItensdefaturamentoService->findObject($composicao['servicotecnico']['itemdefaturamento'], $tenant, $id_grupoempresarial);
        $diasvencimento = $contratanteResponsabilidadeFinanceira->getDiasparavencimento() ? $contratanteResponsabilidadeFinanceira->getDiasparavencimento() : $this->wbCnfgrcsSrvc->getValor($tenant, 'DIASPARAVENCIMENTO');

        $itemcontrato = new Itenscontratos();
        $itemcontrato->setContrato($contrato->getContrato());
        $itemcontrato->setQuantidade(1);
        $itemcontrato->setValor($valor);
        $itemcontrato->setNumerodiasparavencimento($diasvencimento);
        $itemcontrato->setDescricaoservico($itemdefaturamento->getDescricaoservico());
        $itemcontrato->setItemfaturamento($itemdefaturamento->getServico());
        $itemcontrato->setIdgrupoempresarial($id_grupoempresarial);
        $itemcontratonovo =  $this->financasItenscontratosService->insert($id_grupoempresarial, $logged_user, $tenant, $itemcontrato);
        $itemcontrato->setItemcontrato($itemcontratonovo['itemcontrato']);

        $key = array_keys($responsabilidade->getResponsabilidadesfinanceirasvalores()->toArray())[0];
        $responsabilidadeValor = $responsabilidade->getResponsabilidadesfinanceirasvalores()[$key];
        $responsabilidadeValor->setItemcontrato($itemcontrato->getItemcontrato());
        $responsabilidadeValor->setContrato($contrato->getContrato());

        // Código comentado pois agora o itens de contrato estará associado à tabela de rateio da responsabilidade financeira,
        // já que um serviço pode ser dividido para mais de um responsavel, gerando mais de um contrato.

        // if($responsabilidade->getPropostaitemfuncao() == null && 
        //   $responsabilidade->getPropostaitemfamilia() == null) {
        //   $propostaitem = $responsabilidade->getPropostaitemobj();
        //   $propostaitem->setItemcontrato($itemcontrato);
        //   $this->editaItemContratoNaPropostaItem($tenant, $logged_user, $propostaitem);
        // } elseif ($responsabilidade->getPropostaitemfuncao() !== null) {
        //   $propostaitemfuncao->setItemcontrato($itemcontrato); //TODO AQUIIII TESTAR ESSA BAGAÇA
        //   $this->crmPrpstsFncsService->editaItemContratoNaPropostaItemFuncao($tenant, $propostaitemfuncao);
        // } elseif ($responsabilidade->getPropostaitemfamilia() !== null) {
        //   $propostaitemfamilia->setItemcontrato($itemcontrato);
        //   $this->crmPrpstFmlsService->editaItemContratoNaPropostaItemFamilia($tenant, $propostaitemfamilia);
        // }
        return $itemcontrato;
    }

    /**
     * Sobrescrito para gerar item contrato, quando um contrato estiver vinculado ao negócio
     * @param string  $proposta
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $atc
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity
     * @return string
     * @throws \Exception
     */
    public function insert($proposta, $logged_user, $tenant, $atc, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity)
    {
        try {

            //Concatenando a data com a hora nos campos previsaodatahorainicio e previsaodatahorafim

            //Se ele tem hora e data de inicio, então formato e seto o campo previsaodatahorainicio
            // if ($entity->getPrevisaohorainicio() != null && $entity->getPrevisaodatainicio() != null){
            //   $objDataini = new \DateTime($entity->getPrevisaohorainicio());
            //   $horaini = $objDataini->format('H:i:s');
            //   $previsaodatahorainicio = new \DateTime($entity->getPrevisaodatainicio() . " " .  $horaini); 
            //   $entity->setPrevisaodatahorainicio($previsaodatahorainicio->format('Y-m-d H:i:s'));
            // }
            // //Se não tem nem data e nem hora de inicio, o campo é null
            // else{
            //   $entity->setPrevisaodatahorainicio(null);
            // }

            // //Se ele tem hora e data de fim, então formato e seto o campo previsaodatahorafim
            // if ($entity->getPrevisaohorafim() != null && $entity->getPrevisaodatafim() != null){
            //   $objDatafim = new \DateTime($entity->getPrevisaohorafim());
            //   $horafim = $objDatafim->format('H:i:s');
            //   $previsaodatahorafim = new \DateTime($entity->getPrevisaodatafim() . " " .  $horafim); 
            //   $entity->setPrevisaodatahorafim($previsaodatahorafim->format('Y-m-d H:i:s'));
            // }
            // //Se não tem nem data e nem hora de fim, o campo é null
            // else{
            //   $entity->setPrevisaodatahorafim(null);
            // }


            /*Sobreescrito para gravar com a hora do server da Amazon */
            $entity->setCreatedAt(date("Y-m-d H:i:s"));
            /* */

            /*Sobreescrito para herdar os campos de Composicao */
            $composicao = $entity->getComposicao()->getComposicao();
            $composicao = $this->crmComposicoesService->find($composicao, $tenant, $id_grupoempresarial);
            
            // Se a proposta item não possui nome serviço alterado, uso o nome da composição. Se possui nome do serviço alterado, já está dentro da entidade
            if(!$entity->getNomeservicoalterado()){
                $entity->setNome($composicao['nome']);
            }

            $entity->setCodigo($composicao['codigo']);
            $entity->setDescricao($composicao['descricao']);
            /* */

            $this->getRepository()->begin();
            $response = $this->getRepository()->insert($proposta, $logged_user, $tenant, $atc, $id_grupoempresarial, $entity);

            // Busco dados de configurações sobre prazo
            $prazo = $this->wbCnfgrcsSrvc->getValor($tenant, 'PRESTADORPRAZOSELECAO');
            $alerta = $this->wbCnfgrcsSrvc->getValor($tenant, 'PRESTADORALERTA');

            // Adiciono informações de prazo no retorno
            $this->adicionarInformacaoHoraPrazo($response, $tenant, $prazo, $alerta);

            $propostaitem = $this->fillEntity($response);

            //adicionado para previnir erros.
            if ($entity->getNegocio() == null) {
                $entity->setNegocio($propostaitem->getNegocio());
            }

            $propostasItensEnderecos = $entity->getPropostasitensenderecos();
            $propostasItensEnderecos = $propostasItensEnderecos->toArray();
            $this->persistChildPropostasitensenderecos(null, $propostasItensEnderecos, $propostaitem, $tenant, $logged_user, $id_grupoempresarial);

            //conferencia: inseriu os enderecos do propostaitem?
            $result = $this->crmPrpststnsndrcsSrvc->findAll($propostaitem->getPropostaitem(), $tenant, $id_grupoempresarial);

            $entity->setCreatedAt($response['created_at']);

            /* gera item contrato, caso o contrato já exista */
            /* comentado devido alteração do fluxo - criacao de propostaitem não mais gerará itemcontrato  */
            // if ($entity->getNegocio()->getContrato()) {
            //   $itemcontrato = $this->geraItemContrato(
            //     $entity->getNegocio()->getContrato(),
            //     $propostaitem,
            //     $entity->getNegocio()->getCliente(),
            //     $entity->getNegocio()->getEstabelecimento()->getIdGrupoempresarial(),
            //     $logged_user,
            //     $tenant
            //   );
            //   $response['itemcontrato'] = $itemcontrato;
            // }

            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function orcamentosExcluir($tenant, \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity)
    {
        //dado um propostaitem entity (que vem pelo guid)
        try {
            $this->getRepository()->begin();

            $filter = new Filter();
            $filterExpression = $filter->getFilterExpression();
            array_push($filterExpression, new FilterExpression('fornecedor', 'eq', $entity->getFornecedor()->getFornecedor()));
            $filter->setFilterExpression($filterExpression);

            // $filter = new FilterExpression('fornecedor', 'eq', $entity->getFornecedor());
            $listaPropostasItens = $this->findAll($entity->getTenant(), $entity->getNegocio()->getNegocio(), $entity->getProposta()->getProposta(), $entity->getIdGrupoempresarial(), $filter);

            foreach ($listaPropostasItens as $key => $propostaitem) {

                $filterOrcamento = new Filter();
                $filterExpressionOrcamento = $filterOrcamento->getFilterExpression();
                array_push($filterExpressionOrcamento,  new FilterExpression('propostaitem', 'eq', $propostaitem['propostaitem']));
                $filterOrcamento->setFilterExpression($filterExpressionOrcamento);

                $listaOrcamentos = $this->crmOrcamentosService->findAll($tenant, $entity->getIdGrupoempresarial(), $filterOrcamento);

                foreach ($listaOrcamentos as $key => $orcamento) {
                    if ($orcamento['tipoatualizacao'] === 2) {
                        $objOrcamento = $this->crmOrcamentosService->fillEntity($orcamento);
                        $logged_user = $propostaitem->getCreatedby();
                        $this->crmOrcamentosService->delete($entity->getTenant(), $entity->getIdGrupoempresarial(), $logged_user, $objOrcamento);
                    }
                }
            }
            $this->getRepository()->commit();
            // return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * Verifica se o propostaitem e filhos possuem contrato.
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity
     * @return boolean
     */
    public function possuiContratos($tenant, $entity)
    {

        $contratos = $this->responsabilidadesfinanceirasvaloresRepository
            ->getContratosBy($entity->getNegocio()->getNegocio(), 'negocio', null, $tenant);

        foreach ($contratos as $key => $contrato) {
            if ($contrato['contrato'] != null) {
                return true;
            }
        }

        // $itemContrato = $entity->getItemcontrato();
        // if($itemContrato !== null && $itemContrato->getItemcontrato() !== null){
        //   return true;
        // }
        // $familiasEFuncoes = $this->findFamiliasFuncoes ($entity, $tenant);
        // foreach ($familiasEFuncoes['familias'] as $key => $familia) {
        //   $itemContrato = $familia['itemcontrato'];
        //   if($itemContrato !== null && $itemContrato['itemcontrato'] !== null){
        //     return true;
        //   }
        // }
        // foreach ($familiasEFuncoes['funcoes'] as $key => $funcao) {
        //   $itemContrato = $funcao['itemcontrato'];
        //   if($itemContrato !== null && $itemContrato['itemcontrato'] !== null){
        //     return true;
        //   }
        // }
        return false;
    }

    /**
     * Verifica se um fornecedor / prestador está acionado na ficha financeira
     *
     * @param string $atc Identificador do atendimento comercial
     * @param string $tenant Identificador tenant
     * @param string $id_grupoempresarial Identificador do grupo empresarial
     * @param string $fornecedorId Identificador do fornecedor / prestador 
     * @return void
     */
    public function verificaFornecedorAcionado($atc, $tenant, $id_grupoempresarial, $fornecedorId){

        $entities = $this->fornecedoresEnvolvidosRepository->findAllFornecedoresenvolvidosFichafinanceira($atc, $tenant, $id_grupoempresarial);

        // Passando por cada elemento do array de fornecedores da ficha financeira para verificar se o fornecedor da propostaitem está acionado
        $entities = array_filter($entities, function($fornecedorEnvolvido) use ($fornecedorId) {
            return $fornecedorEnvolvido['fornecedor'] == $fornecedorId && $fornecedorEnvolvido['acionador'] != null;
        });

        // Se fornecedor estiver acionado, retorno true, caso contrário, false
        return (count($entities) > 0) ? true : false;

    }

    /**
     * @param string  $tenant
     * @param string  $proposta
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $proposta, $logged_user, \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity)
    {
        try {
            $this->getRepository()->begin();

            //não deletar o propostaitem caso ele ou um dos filhos tenha contrato
            if ($this->possuiContratos($tenant, $entity)) {
                throw new \LogicException("O item, seus produtos ou serviços possuem contrato vinculado.");
            }

            //não deletar o propostaitem se ele possui orcamento != aberto
            $this->deletarOrcamentosPropostaitem($tenant, $logged_user, $entity);

            //Não deletar a propostaitem se ela estiver vinculada a um fornecedor já acionado na ficha financeira
            
            //Verificando se a propostaitem tem fornecedor vinculado. Se tiver fornecedor vinculado, verifico se ele está acionado
            if($entity->getFornecedor() != null){

                // Verificando se há fornecedor acionado
                $fornecedorAcionado = $this->verificaFornecedorAcionado(
                    $entity->getNegocio()->getNegocio(),
                    $tenant,
                    $entity->getIdGrupoempresarial(),
                    $entity->getFornecedor()->getFornecedor()
                );

                // Caso o fornecedor esteja acionado, disparo exception
                if($fornecedorAcionado){
                    throw new LogicException('A ação não pode ser feita, pois o fornecedor já está acionado');
                }

            }

            /* exclui item do contrato, caso o contrato já exista */
            // if ($entity->getNegocio()->getContrato()) {
            //   /* desvincula itemcontrato e item proposta */
            //   $itemcontrato = $entity->getItemcontrato();
            //   $entity->setItemcontrato(null);
            //   $this->editaItemContratoNaPropostaItem($tenant, $logged_user, $entity);
            //   $this->financasItenscontratosService->delete(
            //     $tenant,
            //     $itemcontrato
            //   );
            // }

            if ($this->wbCnfgrcsSrvc->getValor($tenant, 'INTEGRACAO_GP') && !empty($entity->getTarefa())) {
                $resp = $this->tarefasService->excluirTarefaNoGp($entity, $tenant, $logged_user);
            }

            /* --- */
            $response = $this->getRepository()->delete($tenant, $proposta, $logged_user, $entity);

            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw new \LogicException("Não foi possível excluir um item do pedido. " . $e->getMessage(), 1);
        }
    }

    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $proposta
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity
     * @return string
     * @throws \Exception
     */
    public function update($logged_user, $tenant, $proposta, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity, $originalEntity = NULL)
    {
        $entity->setUpdatedAt(date("Y-m-d H:i:s"));
        try {

            //Concatenando a data com a hora nos campos previsaodatahorainicio e previsaodatahorafim
            //Se ele tem hora e data de inicio, então formato e seto o campo previsaodatahorainicio
            if ($entity->getPrevisaohorainicio() != null && $entity->getPrevisaodatainicio() != null) {
                $objDataini = new \DateTime($entity->getPrevisaohorainicio());
                $horaini = $objDataini->format('H:i:s');
                $previsaodatahorainicio = new \DateTime($entity->getPrevisaodatainicio() . " " .  $horaini);

                $entity->setPrevisaodatahorainicio($previsaodatahorainicio->format('Y-m-d H:i:s'));
            }
            //Se não tem nem data e nem hora de inicio, o campo é null
            else {
                $entity->setPrevisaodatahorainicio(null);
            }

            if ($entity->getPrevisaohorafim() != null && $entity->getPrevisaodatafim() != null) {
                $objDatafim = new \DateTime($entity->getPrevisaohorafim());
                $horafim = $objDatafim->format('H:i:s');
                $previsaodatahorafim = new \DateTime($entity->getPrevisaodatafim() . " " .  $horafim);
                $entity->setPrevisaodatahorafim($previsaodatahorafim->format('Y-m-d H:i:s'));
            }
            //Se não tem nem data e nem hora de fim, o campo é null
            else {
                $entity->setPrevisaodatahorafim(null);
            }

            $this->getRepository()->begin();

            //Verificando se a propostaitem tem fornecedor vinculado. Se tiver fornecedor vinculado, verifico se ele está acionado
            if($entity->getFornecedor() != null){

                // Verificando se há fornecedor acionado
                $fornecedorAcionado = $this->verificaFornecedorAcionado(
                    $entity->getNegocio()->getNegocio(),
                    $tenant,
                    $entity->getIdGrupoempresarial(),
                    $entity->getFornecedor()->getFornecedor()
                );

                // Caso o fornecedor esteja acionado, disparo exception
                if($fornecedorAcionado){
                    throw new LogicException('A ação não pode ser feita, pois o fornecedor já está acionado');
                }

            }

            $response = $this->getRepository()->update($logged_user, $tenant, $proposta, $id_grupoempresarial, $entity);
            $propostasItensEnderecosAntigos = $originalEntity->getPropostasitensenderecos();
            $propostasItensEnderecosAntigos = $propostasItensEnderecosAntigos->toArray();

            $propostaitem = $originalEntity;
            $propostasItensEnderecos = $entity->getPropostasitensenderecos();
            $propostasItensEnderecos = $propostasItensEnderecos->toArray();

            foreach ($propostasItensEnderecos as $key => $endereco) {
                //tratamento para quando recebo o endereço manual (objeto interno é array vazio)
                if (is_array($endereco->getEnderecoid()) || $endereco->getEnderecomanual() == true) {
                    // if($endereco->getEnderecoid()['endereco'] == null){
                    $propostasItensEnderecos[$key]->setEnderecoid(null);
                    // }
                } else {
                    //tratamento para quando recebo o endereço prepreenchido (objetos auxiliares devem ser esvaziados)
                    $propostasItensEnderecos[$key]->setLogradouro(null);
                    $propostasItensEnderecos[$key]->setNumeroendereco(null);
                    $propostasItensEnderecos[$key]->setComplemento(null);
                    $propostasItensEnderecos[$key]->setCep(null);
                    $propostasItensEnderecos[$key]->setBairro(null);
                    $propostasItensEnderecos[$key]->setUf(null);
                    $propostasItensEnderecos[$key]->setCidade(null);
                    $propostasItensEnderecos[$key]->setPropostaitem(null);
                    $propostasItensEnderecos[$key]->setTipologradouro(null);
                    $propostasItensEnderecos[$key]->setPais(null);
                    $propostasItensEnderecos[$key]->setIbge(null);
                }
            }
            $this->persistChildPropostasitensenderecos($propostasItensEnderecosAntigos, $propostasItensEnderecos, $propostaitem, $tenant, $logged_user, $id_grupoempresarial);

            //para conferencia de resultado. talvez apagar depois
            $result = $this->crmPrpststnsndrcsSrvc->findAll($propostaitem->getPropostaitem(), $tenant, $id_grupoempresarial);

            try {
                /* edita a tarefa no gp. caso não seja possível, dispara uma exception que cancela a ação atual. */
                $resp = $this->tarefasService->editarTarefaNoGp($entity, $tenant, $logged_user);
            } catch (\Exception $e) {
                // $response['erroGPtarefa'] = "PropostaItem editada com ressalvas. ".$e->getMessage();
                throw $e;
            }

            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw new \LogicException("Não foi possível editar um item do pedido. " . $e->getMessage(), 1);
        }
    }

    public function findFamiliasFuncoes($propostaItem, $tenant, $id_grupoempresarial)
    {
        if (is_object($propostaItem))
            $propostaItem = $propostaItem->getPropostaitem();
        if (is_object($propostaItem))
            $propostaItem = $propostaItem['propostaitem'];
        $familias = $this->crmPrpstFmlsService->findAll($tenant, $propostaItem, $id_grupoempresarial);
        $funcoes = $this->crmPrpstsFncsService->findAll($tenant, $propostaItem, $id_grupoempresarial);
        return ['familias' => $familias, 'funcoes' => $funcoes];
    }

    /**
     * Sobrescrevo função para adicionar informações de prazo
     * @return array
     */
    public function findAll($tenant, $atc, $proposta, $id_grupoempresarial, Filter $filter = null)
    {
        $propostasitens = parent::findAll($tenant, $atc, $proposta, $id_grupoempresarial, $filter);

        // Busco dados de configurações
        $prazo = $this->wbCnfgrcsSrvc->getValor($tenant, 'PRESTADORPRAZOSELECAO');
        $alerta = $this->wbCnfgrcsSrvc->getValor($tenant, 'PRESTADORALERTA');

        foreach ($propostasitens as $key => $propostaitem) {
            $this->adicionarInformacaoHoraPrazo($propostaitem, $tenant, $prazo, $alerta);
            $propostasitens[$key]['fornecedor']['orcamentoaprovado'] = $this->fornecedoresService->getOrcamentoEstaAprovado($tenant, $id_grupoempresarial, $atc, $propostasitens[$key]['fornecedor']['fornecedor']);
        }
        return $propostasitens;
    }

    /**
     * Sobrescrevo função para adicionar informações de prazo
     */
    public function find($id, $tenant, $negocio, $proposta, $id_grupoempresarial)
    {
        $data = parent::find($id, $tenant, $negocio, $proposta,  $id_grupoempresarial);

        $data['prazo'] = $this->wbCnfgrcsSrvc->getValor($tenant, 'PRESTADORPRAZOSELECAO');
        $data['alerta'] = $this->wbCnfgrcsSrvc->getValor($tenant, 'PRESTADORALERTA');


        //Conversão de data
        $prazo = date("Y-m-d H:i:s", strtotime($data['created_at']) + 60 * $data['prazo']); //prazo
        $alerta = date("Y-m-d H:i:s", strtotime($data['created_at']) + (60 * $data['prazo']) - (60 * $data['alerta'])); //alerta
        $data['agora'] = getdate();
        $data['agora'] = date("Y-m-d H:i:s");
        $data['alerta'] = $alerta;
        $data['prazo'] = $prazo;

        //Separa data e hora
        if ($data['previsaodatahorainicio'] != null) {
            $objDataHoraInicio = \DateTime::createFromFormat('Y-m-d H:i:s', $data['previsaodatahorainicio']);
            $data['previsaodatainicio'] = $objDataHoraInicio->format('Y-m-d');
            $data['previsaohorainicio'] = $objDataHoraInicio->format('H:i:s');
        } else {
            $data['previsaodatainicio'] = null;
            $data['previsaohorainicio'] = null;
        }

        if ($data['previsaodatahorafim'] != null) {
            $objDataHoraFim = \DateTime::createFromFormat('Y-m-d H:i:s', $data['previsaodatahorafim']);
            $data['previsaodatafim'] = $objDataHoraFim->format('Y-m-d');
            $data['previsaohorafim'] = $objDataHoraFim->format('H:i:s');
        } else {
            $data['previsaodatafim'] = null;
            $data['previsaohorafim'] = null;
        }

        return $data;
    }


    /**
     * @param string  $tenant
     * @param \Nasajon\AppBundle\Entity\Crm\PropostasitensBulk $entity lote de propostas itens para editar
     * @return string
     * @throws \Exception
     */
    public function vincularFornecedorLote($atc, $proposta, $tenant, $id_grupoempresarial, \Nasajon\AppBundle\Entity\Crm\PropostasitensBulk $entity)
    {
        try {
            $this->getRepository()->begin();
            $retorno = [];
            //Alterando todos os fornecedores da proposta item
            $fornecedorId = $entity->getFornecedor();
            foreach ($entity->getPropostasitens() as $propostaitem) {
                $propostaItemCompleta = $this->findObject($propostaitem->getPropostaitem(), $tenant, $atc, $proposta, $id_grupoempresarial);
                if($propostaItemCompleta->getFornecedor() != null ){
                    continue;
                }
                $propostaItemCompleta->setFornecedor($fornecedorId);

                $prop = $this->propostasRepository->find($proposta, $tenant, $atc, $id_grupoempresarial);
                $propostarecuperada = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Propostas', $prop);
                $propostaItemCompleta->setProposta($propostarecuperada);

                $this->propostasItensVincularFornecedor($tenant,  $propostaItemCompleta, $id_grupoempresarial);
            }
            $this->getRepository()->commit();
            return $retorno;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\AppBundle\Entity\Crm\PropostasitensBulk $entity lote de propostas itens para editar
     * @return string
     * @throws \Exception
     */
    public function desvincularFornecedorLote($atc, $proposta, $tenant, $id_grupoempresarial, \Nasajon\AppBundle\Entity\Crm\PropostasitensBulk $entity)
    {
        try {
            $this->getRepository()->begin();
            $retorno = [];

            //Alterando todos os fornecedores da proposta item
            foreach ($entity->getPropostasitens() as $propostaitem) {

                $propostaItemCompleta = $this->findObject($propostaitem->getPropostaitem(), $tenant, $atc, $proposta, $id_grupoempresarial);

                $prop = $this->propostasRepository->find($proposta, $tenant, $atc, $id_grupoempresarial);
                $propostarecuperada = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Propostas', $prop);
                $propostaItemCompleta->setProposta($propostarecuperada);

                $this->propostasItensDesvincularFornecedor($tenant, $propostaItemCompleta, $id_grupoempresarial);
            }

            $this->getRepository()->commit();
            return $retorno;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }
    /**
     * @param string  $atc
     * @param string  $proposta
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\AppBundle\Entity\Crm\TemplatespropostascapituloscomposicoesBulk $entity lote de listas de pendencias para salvar
     * @return string
     * @throws \Exception
     */
    public function templateGeraItensLote($atc, $proposta, $logged_user, $tenant,  $id_grupoempresarial, \Nasajon\AppBundle\Entity\Crm\TemplatespropostascapituloscomposicoesBulk $entity)
    {
        try {

            $this->getRepository()->begin();

            $retorno = [];

            // Busco propostasitens existentes
            $arrPropostasitens = $this->findAll($tenant, $atc, $proposta, $id_grupoempresarial);

            // Busco capítulo referente ao Pedido
            $arrPropostascapitulos = $this->crmPropostaCapituloService->findAll($tenant, $proposta, $id_grupoempresarial);
            $arrPropostascapitulos = array_map(function ($item) {
                if ($item['nome'] == 'Pedido' && $item['pai'] == null) {
                    return $item;
                }
            }, $arrPropostascapitulos);
            $propostacapitulo = isset($arrPropostascapitulos[0]) == true ? $this->crmPropostaCapituloService->fillEntity($arrPropostascapitulos[0]) : null;

            // Valido se tem ao menos um item para gerar/atualizar
            $arrTemplatesComposicoes = $entity->getTemplatescomposicoes()->toArray();

            if (empty($arrTemplatesComposicoes)) {
                throw new LogicException("Por favor, selecione ao menos um item!", 1);
            }

            // Percorro os templates passados para criar/atualizar itens de pedidos(propostasitens)
            foreach ($arrTemplatesComposicoes as $templateComposicao) {
                // Busco se já existe um item de pedido(propostaitem) para esse serviço(composição)
                $arrBuscaPorComposicao = [];

                $arrPropostasInserirAlterar = [];
                foreach ($arrPropostasitens as $itemFilter) {
                    if ($itemFilter['composicao']['composicao'] == $templateComposicao->getComposicao()->getComposicao()) {
                        $propostaAlterar = [
                            'tipo' => 'alterar',
                            'propostaItemOriginal' => $itemFilter
                        ];
                        $arrPropostasInserirAlterar[] = $propostaAlterar;
                    }
                }

                // Se não tem itens já criados, adiciono um item do tipo 'inserir' ao array só para inserir um novo.
                if (count($arrPropostasInserirAlterar) == 0) {
                    $arrPropostasInserirAlterar[] = [
                        'tipo' => 'inserir'
                    ];
                }

                foreach ($arrPropostasInserirAlterar as $propostaItemInserirAlterar) {
                    $propostaItemOriginal = ($propostaItemInserirAlterar['tipo'] == 'alterar' ? $propostaItemInserirAlterar['propostaItemOriginal'] : null);

                    if ($propostaItemOriginal) {
                        $propostaItemOriginal = $this->find($propostaItemOriginal['propostaitem'], $tenant, $atc, $proposta, $id_grupoempresarial);
                        $propostaItemOriginal = $this->fillEntity($propostaItemOriginal);
                    }

                    $propostaItem = $propostaItemOriginal;

                    // Busco apólice completa
                    $templateproposta = $templateComposicao->getTemplateProposta();
                    $templatepropostagrupo = $templateComposicao->getTemplatePropostaGrupo();
                    $templateproposta = $this->templatesPropostasRepository->find($templateproposta, $tenant, $id_grupoempresarial, $templatepropostagrupo);

                    if (!is_array($templateproposta)) {
                        throw new LogicException("Apólice não encontrada.", 1);
                    }

                    // Se propostaitem ainda não existe, crio uma instancia do objeto para preencher.
                    if ($propostaItemOriginal == null) {
                        $propostaItem = new Propostasitens();

                        $propostaItem->setNome($templateComposicao->getNome());
                        $propostaItem->setDescricao($templateComposicao->getDescricao());
                        $propostaItem->setComposicao($templateComposicao->getComposicao());
                        $propostaItem->setPropostacapitulo($propostacapitulo);
                    }

                    // Preencho dados comuns caso esteja sendo criado ou editado
                    $propostaItem->setIdApolice($templateproposta['templateproposta']);
                    $propostaItem->setValorapolice($templateproposta['valorapolice']);
                    $propostaItem->setTemplatepropostacomposicao($templateComposicao);

                    // Caso seja uma nova propostaitem, chamo função de inserir. Senão chamo atualizar
                    if ($propostaItemOriginal != null) {
                        $this->update($logged_user, $tenant, $proposta, $id_grupoempresarial, $propostaItem, $propostaItemOriginal);
                        $resultPropostaitem = $this->find($propostaItem->getPropostaitem(), $tenant, $atc, $proposta, $id_grupoempresarial);
                        array_push($retorno, $resultPropostaitem);
                    } else {
                        $resultpropostaItem = $this->insert($proposta, $logged_user, $tenant, $atc, $id_grupoempresarial, $propostaItem);
                        $propostaItem = $this->fillEntity($resultpropostaItem);
                        array_push($retorno, $resultpropostaItem);
                    }
                }
            }

            $this->getRepository()->commit();
            return $retorno;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function propostasItensVincularFornecedor($tenant, \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity, $id_grupoempresarial = null)
    {
        if ($id_grupoempresarial == null) {
            $grupoempresarial = $entity->getNegocio()->getIdGrupoempresarial();
        } else {
            $grupoempresarial = $id_grupoempresarial;
        }
        $fornecedorId = $entity->getFornecedor()->getFornecedor();
        $fornecedor = $this->fornecedoresService->findObject($fornecedorId, $tenant, $grupoempresarial);

        //não permitir que se vincule fornecedor estabelecimento
        //caso outros não sejam estabelecimento e vice versa

        //não permite inserir se o fornecedor desejado possui orcamento status != 0
        $filter = new Filter();
        $arrFilterExpression[] = new FilterExpression('fornecedor', 'eq', $entity->getFornecedor()->getFornecedor());
        $filter->setFilterExpression($arrFilterExpression);
        $propostasitens = $this->findAll($tenant, $entity->getNegocio()->getNegocio(), $entity->getProposta()->getProposta(), $id_grupoempresarial, $filter);
        if (isset($propostasitens[0])) {
            $propostasitens[0] = $this->fillEntity($propostasitens[0]);
            if (!$this->orcamentosAbertos($tenant, $propostasitens[0])) {
                throw new LogicException("Não é possível atribuir um prestador quando o orçamento está em negociação/finalizado.", 1);
            }
        }

        /* Vinculando propostaItem */
        $data = parent::propostasItensVincularFornecedor($tenant, $entity);

        $logged_user = $entity->getCreatedby();
        $atc = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Atcs', $this->atcsRepository->find($entity->getProposta()->getNegocio(), $tenant, $id_grupoempresarial));
        if ($entity->getNegocio() == null) {
            $entity->setNegocio($atc);
        }
        $projetoid = $atc->getProjeto()['projeto'];
        
        $entity->setFornecedor($fornecedor);
        /*Verificação para ver se tem o projeto */
        if ($projetoid != NULL) {
            $projeto = $this->projetosService->findObject($projetoid, $tenant);
            $entity->getNegocio()->setProjeto($projeto);

            if (($entity->getTarefa() != null) && ($entity->getTarefa()->getSituacao() != 5)) {
                try {
                    /* cancela a tarefa no gp. caso não seja possível, dispara uma exception que cancela a ação atual. */
                    $resp = $this->tarefasService->excluirTarefaNoGp($entity, $tenant, $logged_user);
                } catch (\Exception $e) {
                    $response['erroGPtarefa'] = "PropostaItem deletada com ressalvas. " . $e->getMessage(); //acusa erro de offset
                    throw $e;
                }
            }
        }
        $response = $this->find($entity->getPropostaitem(), $tenant, $entity->getNegocio()->getNegocio(), $entity->getProposta()->getProposta(), $grupoempresarial);
        return $response;
    }

    /**
     * Dado um propostaitem, checa se todos os orçamentos realcionados a este propostaitem estão com status aberto, retornando true.
     */
    public function orcamentosAbertos($tenant, $propostaitem)
    {
        $filter = new Filter();
        $arrFilterExpression[] = new FilterExpression('propostaitem', 'eq', $propostaitem->getPropostaitem());
        $filter->setFilterExpression($arrFilterExpression);
        $orcamentos = $this->crmOrcamentosService->findAll($tenant, $propostaitem->getIdGrupoempresarial(), $filter);
        foreach ($orcamentos as $key => $orcamento) {
            if ($orcamento['status'] != 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Deleta todos os orçamentos relacionados ao propostaitem, caso os mesmos estejam com status aberto.
     * Caso não, não deleta nenhum.
     */
    public function deletarOrcamentosPropostaitem($tenant, $logged_user, $propostaitem)
    {
        try {
            $filter = new Filter();
            $arrFilterExpression[] = new FilterExpression('propostaitem', 'eq', $propostaitem->getPropostaitem());
            if($propostaitem->getDeletarsomentecomposicoes() === true){
                $arrFilterExpression[] = new FilterExpression('composicao', 'isNotNull', '');
            }
            $filter->setFilterExpression($arrFilterExpression);
            $orcamentos = $this->crmOrcamentosService->findAll($tenant, $propostaitem->getIdGrupoempresarial(), $filter);
            foreach ($orcamentos as $key => $orcamento) {
                if ($orcamento['status'] != 0) {
                    if ($orcamento['status'] == 1) {
                        throw new \Exception("Não é possível excluir um orçamento enviado.", 1);
                    } else if ($orcamento['status'] == 2) {
                        throw new \Exception("Não é possível excluir um orçamento aprovado.", 1);
                    } else if ($orcamento['status'] == 3) {
                        throw new \Exception("Não é possível excluir um orçamento recusado.", 1);
                    } else {
                        throw new \Exception("Não foi possível excluir o orçamento.", 1);
                    }
                }
            }
            foreach ($orcamentos as $key => $orcamento) {
                $objOrcamento = $this->crmOrcamentosService->fillEntity($orcamento);
                if($logged_user == null) $logged_user = $propostaitem->getCreatedby();
                $this->crmOrcamentosService->delete($tenant, $propostaitem->getIdGrupoempresarial(), $logged_user, $objOrcamento);
            }
            return;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function propostasItensDesvincularFornecedor($tenant, \Nasajon\MDABundle\Entity\Crm\Propostasitens $entity, $id_grupoempresarial = null, $verificaAcionamento = true, $logged_user = null)
    {
        $this->getRepository()->begin();
        try {

            if($verificaAcionamento){

                $fornecedorAcionado = $this->verificaFornecedorAcionado(
                    $entity->getNegocio()->getNegocio(), 
                    $tenant, $id_grupoempresarial,
                    $entity->getFornecedor()->getFornecedor()
                );

                if($fornecedorAcionado){
                    throw new LogicException('Para desvincular um prestador acionado, é necessário cancelar o seu acionamento primeiro');
                }

            }

            //não vincular novo propostaitem caso ele ou um dos filhos tenha contrato
            if ($this->possuiContratos($tenant, $entity)) {
                throw new \Exception("O item, seus produtos ou serviços possuem contrato vinculado.");
            }
            //delear orcamentos. Não desvincular o propostaitem se ele possui orcamento != abertos

            $this->deletarOrcamentosPropostaitem($tenant, $logged_user, $entity);

        } catch (LogicException $e) {
            $this->getRepository()->rollback();
            throw new LogicException($e->getMessage());
        } catch (\Exception $e) {
            $this->getRepository()->rollback();
            throw new LogicException("Não foi possível desvincular um item do pedido. " . $e->getMessage(), 1);
        }
        $data = parent::propostasItensDesvincularFornecedor($tenant, $entity);
        $this->getRepository()->commit();

        /* Adquirindo o id de projeto do repository pois só vem por lá e pegando o objeto de projeto */

        if ($entity->getFornecedor() == null) {
            $fornecedorId = null;
        } else {
            $fornecedorId = $entity->getFornecedor()->getFornecedor();
        }

        if ($id_grupoempresarial == null) {
            $grupoempresarial = $entity->getIdGrupoempresarial();
        } else {
            $grupoempresarial = $id_grupoempresarial;
        }

        if ($fornecedorId != null) {
            $fornecedor = $this->fornecedoresService->findObject($fornecedorId, $tenant, $grupoempresarial);
            $entity->setFornecedor($fornecedor);
        } else {
            $fornecedor = null;
        }
        return $data;
    }

    public function localizaContratoPagamento($tenant, $atcId, $fornecedorId)
    {
        $contratosAPagar = $this->getRepository()->localizaContratoPagamento($tenant, $atcId, $fornecedorId);
        return $contratosAPagar;
    }

    public function getFamiliasFuncoesProposta($tenant, $grupoempresarial, $proposta)
    {
        $familias = $this->crmPrpstFmlsService->getFamiliasProposta($tenant, $grupoempresarial, $proposta);
        $funcoes = $this->crmPrpstsFncsService->getFuncoesProposta($tenant, $grupoempresarial, $proposta);
        return ['familias' => $familias, 'funcoes' => $funcoes];
    }

    /**
     * Sobrescrevo função para adicionar informações de prazo
     */
    public function findSemProposta($id, $tenant, $negocio, $id_grupoempresarial)
    {
        $data = $this->getRepository()->findSemProposta($id, $tenant, $negocio, $id_grupoempresarial);
        return $data;
    }

}
