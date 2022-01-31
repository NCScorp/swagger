<?php

namespace Nasajon\AppBundle\Service\Crm;

use LogicException;
use Doctrine\ORM\NoResultException;
use Nasajon\AppBundle\DTO\ProdutoDTO;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Crm\Historicoatcs;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Entity\Financas\Itenscontratos;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidosanexos;
use Nasajon\MDABundle\Service\Crm\OrcamentosService as ParentService;

/**
 * OrcamentosService
 * Sobrescrito findAll para carregar o atributo statusString para os orçamentos.
 */
class OrcamentosService extends ParentService
{

    protected $historicosatcsService;
    public $propostaItemService;
    protected $crmComposicoesService;
    protected $estoqueFamiliasService;
    protected $servicosItensdefaturamentoService;
    protected $financasItenscontratosService;
    protected $webConfiguracoesService;
    protected $crmPropostasitensRepository;
    protected $crmAtcsRepository;
    /**
     * Service de fornecedores envolvidos
     */
    public $fornecedoresEnvolvidosRepository;

    // Constantes com o tipo de faturamento
    const TIPO_FATURAMENTO_NAO_FATURAR = 1;
    const TIPO_FATURAMENTO_SEGURADORA_SEM_NFS = 2;
    const TIPO_FATURAMENTO_SEGURADORA_COM_NFS = 3;
    const TIPO_FATURAMENTO_CLIENTE_COM_NFS = 4;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\OrcamentosRepository $repository,
        $historicosatcsService,
        $crmComposicoesService,
        $estoqueFamiliasService,
        $servicosItensdefaturamentoService,
        $financasItenscontratosService,
        $webConfiguracoesService,
        $crmPropostasitensRepository,
        $crmAtcsRepository,
        $fornecedoresEnvolvidosRepository
    )
    {
        parent::__construct($repository);
        $this->historicosatcsService = $historicosatcsService;
        $this->crmComposicoesService = $crmComposicoesService;
        $this->estoqueFamiliasService = $estoqueFamiliasService;
        $this->servicosItensdefaturamentoService = $servicosItensdefaturamentoService;
        $this->financasItenscontratosService = $financasItenscontratosService;
        $this->webConfiguracoesService = $webConfiguracoesService;
        $this->crmPropostasitensRepository = $crmPropostasitensRepository;
        $this->crmAtcsRepository = $crmAtcsRepository;
        $this->fornecedoresEnvolvidosRepository = $fornecedoresEnvolvidosRepository;
    }

    /**
     * Metodo para converter número do status para string
     */
    public function getStatus($orcamento)
    {
        if (isset($orcamento['status'])) {
            switch ($orcamento['status']) {
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
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
    {
        $orcamentos = parent::findAll($tenant, $id_grupoempresarial, $filter);
        foreach ($orcamentos as $key => $orcamento) {
            $statusString = $this->getStatus($orcamento);
            $orcamentos[$key]['statusString'] = $statusString;

            if($orcamento['composicao'] !== null) {
                $composicao = $this->crmComposicoesService->find($orcamento['composicao'] , $tenant, $id_grupoempresarial);
                if($composicao != null){
                    $orcamentos[$key]['composicao'] = $composicao;
                }
            }
            if($orcamento['familia'] !== null) {
                $familia = $this->estoqueFamiliasService->find($orcamento['familia'] , $tenant);
                if($familia != null){
                    $orcamentos[$key]['familia'] = $familia;
                }
            }
        }
        return $orcamentos;
    }

     /**
     * @return boolean
     */
    public function podeAprovarOrcamento($tenant, $id_grupoempresarial, $id_atc, $id_fornecedor)
    {
        $atc = $this->crmAtcsRepository->find($id_atc, $tenant, $id_grupoempresarial);

        //validação: verifica se possui orçamento zerado e se permite orçamento zerado
        $filter = new Filter();
        $expressions = [
            new FilterExpression('atc', 'eq', $atc['negocio']),
            new FilterExpression('fornecedor', 'eq', $id_fornecedor),
        ];
        $filter->setFilterExpression($expressions);
        $arrOrcamentos = $this->findAll($tenant, $id_grupoempresarial, $filter);
        for ($i=0; $i < count($arrOrcamentos); $i++) {
            $orcamento = $arrOrcamentos[$i];

            if( ($atc['area']['permiteorcamentozerado'] == false || //se não permite orçamento zerado
                $orcamento['fornecedor']['estabelecimentoid'] == null ) //ou o fornecedor não possui estabelecimento
                && $orcamento['valor'] <= 0
                && $orcamento['faturamentotipo'] > 1
            ) { //e o valor é zerado
                throw new LogicException("Nao é possível aprovar orçamento, pois existem itens marcados para faturar sem orçamento preenchido.", 1);
            }
        }


        //validação: verifica se o cliente possui seguradora, se sim, verifica os valores autorizados.
        if($atc['possuiseguradora'] != false) {
            //Busca o valor autorizado da apolice pela seguradora.
            $valorAutorizadoApolices = $this->getRepository()->getValorTotalAutorizadoApolices($tenant,$id_grupoempresarial, $id_atc);

            //Busca o valor autorizado do orçamento.
            $valorAutorizadoOrcamentos = $this->getRepository()->getValorTotalAutorizadoOrcamentos($tenant,$id_grupoempresarial, $id_atc);

            
            //considerar o taxa administrativa e desconto global do fornecedor envolvido para a conta do valor autorizado.
            $atc = $this->crmAtcsRepository->find($id_atc, $tenant, $id_grupoempresarial);
            $taxaAdm = $atc['valortaxaadm'] != null ? $atc['valortaxaadm'] : 0;

            $filter = new Filter();
            $expressions = [new FilterExpression('fornecedor', 'eq', $id_fornecedor)];
            $filter->setFilterExpression($expressions);
            $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosRepository->findAll($tenant, $id_atc, $id_grupoempresarial, $filter);
            $descontoglobal = $fornecedorEnvolvidoObj[0]['descontoglobal'];

            if ( ($valorAutorizadoOrcamentos + $taxaAdm - $descontoglobal) > $valorAutorizadoApolices) {
                throw new LogicException("Os valores totais autorizados nos orçamentos não podem ser maiores que os valores autorizados nas apólices da seguradora.", 1);
            }
        }

        return true;
    }

    /**
     * Verifica se os orçamentos de um fornecedor podem ser reabertos
     * @return boolean
     */
    public function podeReabrirOrcamento($tenant, $id_grupoempresarial, $atc, $fornecedor){
        if($this->getRepository()->fornecedorPossuiOrcamentoComContrato($tenant, $id_grupoempresarial, $atc, $fornecedor)){
            return false;
        }
        return true;
    }
    
    public function findAllOrcamentosFichafinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial)
    {
        $orcamentos = $this->getRepository()->findAllOrcamentosFichafinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial);

        return $orcamentos;
    }

    /**
     * Aprova todos os orçamentos do fornecedor no atendimento comercial
     */
    public function aprovarOrcamentosDoFornecedor($atc, $fornecedor, $logged_user, $tenant, $id_grupoempresarial)
    {
        // Busca orçamentos a serem aprovados, filtrando por atendimento e fornecedor.
        $filter = new Filter();
        $expressions = [
            new FilterExpression('atc', 'eq', $atc),
            new FilterExpression('fornecedor', 'eq', $fornecedor->getFornecedor()),
        ];
        $filter->setFilterExpression($expressions);
        $arrOrcamentos = $this->findAll($tenant, $id_grupoempresarial, $filter);

        // Verifico se tem ao menos um serviço
        $possuiOrcamentoServico = false;
        for ($i=0; $i < count($arrOrcamentos); $i++) {
            $orcamento = $arrOrcamentos[$i];

            if ($orcamento['composicao'] != null) {
                $possuiOrcamentoServico = true;
                break;
            }
        }

        if (!$possuiOrcamentoServico) {
            throw new LogicException("O orçamento precisa de ao menos um serviço para ser aprovado.", 1);
            return ;   
        }

        // Para cada orçamento, chamo função de aprovar do service de orçamentos
        foreach ($arrOrcamentos as $orcamento) {
            $orcamentoObj = $this->fillEntity($orcamento);
            $this->aprovar($logged_user, $tenant, $id_grupoempresarial, $orcamentoObj);
        }

        // Se tem algum orçamento, insiro histórico de alteração
        if (count($arrOrcamentos) > 0) {
            $historico = new Historicoatcs();
            $historico->setAcao('Orçamento aprovado');
            $historico->setObservacao('Orçamento do prestador ' . $fornecedor->getNomefantasia() . ' foi aprovado.');
            $historico->setSecao('orçamento');
            $this->historicosatcsService->insert($atc, $tenant, $logged_user, $historico);
        } else {
            throw new LogicException("O prestador de serviço não possui orçamento.", 1);
        }
    }

    /**
     * Reabre todos os orçamentos do fornecedor no atendimento comercial
     */
    public function reabrirOrcamentosDoFornecedor($atc, $fornecedor, $logged_user, $tenant, $id_grupoempresarial)
    {
        // Busca orçamentos a serem reabertos, filtrando por atendimento e fornecedor.
        $filter = new Filter();
        $expressions = [
            new FilterExpression('atc', 'eq', $atc),
            new FilterExpression('fornecedor', 'eq', $fornecedor->getFornecedor()),
        ];
        $filter->setFilterExpression($expressions);
        $arrOrcamentos = $this->findAll($tenant, $id_grupoempresarial, $filter);

        // Para cada orçamento, chamo função de reabrir do service de orçamentos
        foreach ($arrOrcamentos as $orcamento) {
            $orcamentoObj = $this->fillEntity($orcamento);
            $this->reabrir($tenant, $id_grupoempresarial, $logged_user, $orcamentoObj);
        }

        // Se tem algum orçamento, insiro histórico de alteração
        if (count($arrOrcamentos) > 0) {
            // Insiro histórico
            $historico = new Historicoatcs();
            $historico->setAcao('Orçamento reaberto');
            $historico->setObservacao('Orçamento do prestador ' . $fornecedor->getNomefantasia() . ' foi reaberto.');
            $historico->setSecao('orçamento');
            $this->historicosatcsService->insert($atc, $tenant, $logged_user, $historico);
        } else {
            throw new LogicException("O prestador de serviço não possui orçamento.", 1);
        }
    }

    /**
    * @param string  $tenant
    * @param string  $id_grupoempresarial
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity
    * @return string
    * @throws \Exception
    */
    public function insert($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity){
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->insert($tenant,$id_grupoempresarial,$logged_user, $entity);
               
            /**
             * Se o orçamento for do tipo serviço e propostaitem não foi passado:
             *  - O propostaitem será criado na função de banco
             *  - Deve ser chamado a função para acionamento do fornecedor
             */
            if ($entity->getComposicao() != null && $entity->getPropostaitem() == null) {
                // Busco objeto de propostaitem
                $propostaitemArr = $this->propostaItemService->find($response['propostaitem'], $tenant, $entity->getAtc(), $entity->getProposta(), $id_grupoempresarial);
                $propostaitemObj = $this->propostaItemService->fillEntity($propostaitemArr);
                
                // Chamo função que vincula fornecedores e cria tarefas
                $this->propostaItemService->propostasItensVincularFornecedor($tenant, $propostaitemObj, $id_grupoempresarial);
            }
            
            // Chamo atualização de descontos globais dos orçamentos do fornecedor no atendimento
            if ($entity->getAtualizardescontosglobais()) {
                $fornecedorEnvolvido = [
                    'fornecedor' => [
                        'fornecedor' => $entity->getFornecedor()->getFornecedor()
                    ]
                ];
                $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosService->fillEntity($fornecedorEnvolvido);

                $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosService->fornecedorenvolvidoatualizardescontoorcamentos($entity->getAtc(), $logged_user, $tenant, $id_grupoempresarial, $fornecedorEnvolvidoObj);
                $response['descontoglobalunitario'] = $fornecedorEnvolvidoObj->getDescontoglobalunitario();
                $response['descontoglobalresto'] = $fornecedorEnvolvidoObj->getDescontoglobalresto();
                $response['descontoglobalrestoorcamento'] = $fornecedorEnvolvidoObj->getDescontoglobalrestoorcamento();
            }

            $this->getRepository()->commit();

            return $response;

        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }

    }

    /**
    * @param string  $tenant
    * @param string  $id_grupoempresarial
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity
    * @return string
    * @throws \Exception
    */
    public function update($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity){
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->update($tenant,$id_grupoempresarial, $logged_user, $entity);
                             
            // Chamo atualização de descontos globais dos orçamentos do fornecedor no atendimento
            if ($entity->getAtualizardescontosglobais()) {
                $fornecedorEnvolvido = [
                    'fornecedor' => [
                        'fornecedor' => $entity->getFornecedor()->getFornecedor()
                    ]
                ];
                $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosService->fillEntity($fornecedorEnvolvido);

                $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosService->fornecedorenvolvidoatualizardescontoorcamentos($entity->getAtc(), $logged_user, $tenant, $id_grupoempresarial, $fornecedorEnvolvidoObj);
                $response = [];
                $response['descontoglobalunitario'] = $fornecedorEnvolvidoObj->getDescontoglobalunitario();
                $response['descontoglobalresto'] = $fornecedorEnvolvidoObj->getDescontoglobalresto();
                $response['descontoglobalrestoorcamento'] = $fornecedorEnvolvidoObj->getDescontoglobalrestoorcamento();
            }

            $this->getRepository()->commit();
            return $response;
        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function buscaComposicaoGeracaoContrato ($orcamento, $id_grupoempresarial, $tenant, $propostaitemPadrao = null) {
        $composicao = null;
        if($orcamento->getComposicao() !== null) { //COMPOSICAO
            $composicao = $orcamento->getComposicao();
            if(is_object($composicao)) $composicao = $composicao->getComposicao();
            if(is_array($composicao)) $composicao = $composicao['composicao'];
            $composicao = $this->crmComposicoesService->find($composicao,$tenant,$id_grupoempresarial);
        } elseif ($orcamento->getFamilia() !== null) { //FAMILIA
            // $familia = $orcamento->getFamilia();
            $propostaitem = $orcamento->getPropostaitem();
            if($orcamento->getPropostaitem() != null) {
                //buscar a composicao pelo propostaitem
                $propostaitem = $this->crmPropostasitensRepository->findSemProposta($propostaitem, $tenant, $orcamento->getAtc(), $id_grupoempresarial);
                $composicao = $this->crmComposicoesService->find($propostaitem['composicao'],$tenant,$id_grupoempresarial);
            } else {
                //busca itemfaturamento usando propostaitem e composição de outro orçamento
                $propostaitem = $this->crmPropostasitensRepository->findSemProposta($propostaitemPadrao, $tenant, $orcamento->getAtc(), $id_grupoempresarial);
                $composicao = $this->crmComposicoesService->find($propostaitem['composicao'],$tenant,$id_grupoempresarial);
            }
        } else {
            throw new LogicException("Um dos orçamentos não possui serviço.");
        }
        return $composicao;
    }

    public function buscaItemFaturamentoGeracaoContrato ($orcamento, $id_grupoempresarial, $tenant) {
        $composicao = $orcamento->getcomposicao();
        if($composicao['itemfaturamento'] == null ){
            throw new LogicException("Serviço ".$composicao['nome']." não possui item de faturamento.");
        }
        //buscar o item de faturamento
        try {
            $itemdefaturamento = $this->servicosItensdefaturamentoService->find(
                $composicao['itemfaturamento']['servico'],
                $tenant,
                $id_grupoempresarial
            );
            if($itemdefaturamento['cfopId'] == null){
                throw new LogicException("Item de faturamento do serviço  ".$composicao['nome']." não possui cfop.");
            }
            return $itemdefaturamento;
        } catch (NoResultException $e) {
            throw new LogicException("Serviço ".$composicao['nome']." não possui item de faturamento.");
        }
    }

    public function geraItemContrato($orcamento, $contrato, &$responsabilidade, $id_grupoempresarial, $logged_user, $tenant, $contratanteResponsabilidadeFinanceira, $valorResponsabilidadeFinanceira, $propostaitemPadrao = null)
    {
        $composicao = null;
        $itemdefaturamento = null;

        //COMPOSICAO
        if($orcamento->getComposicao() !== null && isset($orcamento->getComposicao()['composicao'])) {
            $composicao = $orcamento->getComposicao()['composicao'];
            $composicao = $this->crmComposicoesService->find($composicao,$tenant,$id_grupoempresarial);
        }
        //FAMILIA
        elseif ($orcamento->getFamilia() !== null) {
            // $familia = $orcamento->getFamilia();
            $propostaitem = $orcamento->getPropostaitem();
            if($orcamento->getPropostaitem() != null) {
                //buscar a composicao pelo propostaitem
                $propostaitem = $this->crmPropostasitensRepository->findSemProposta($propostaitem, $tenant, $orcamento->getAtc(), $id_grupoempresarial);
                $composicao = $this->crmComposicoesService->find($propostaitem['composicao'],$tenant,$id_grupoempresarial);
            } else {
                //busca itemfaturamento usando propostaitem e composição de outro orçamento
                $propostaitem = $this->crmPropostasitensRepository->findSemProposta($propostaitemPadrao, $tenant, $orcamento->getAtc(), $id_grupoempresarial);
                $composicao = $this->crmComposicoesService->find($propostaitem['composicao'],$tenant,$id_grupoempresarial);
            }
        } else{
            throw new LogicException("Não foi possível encontrar o serviço");
        }

        //buscar o item de faturamento
        $itemdefaturamento = $this->servicosItensdefaturamentoService->findObject(
            $composicao['itemfaturamento']['servico'],
            $tenant,
            $id_grupoempresarial
        );

        $diasvencimento = $contratanteResponsabilidadeFinanceira->getDiasparavencimento() ? 
            $contratanteResponsabilidadeFinanceira->getDiasparavencimento() : 
            $this->webConfiguracoesService->getValor($tenant, 'DIASPARAVENCIMENTO');

        $itemcontrato = new Itenscontratos();
        $itemcontrato->setContrato($contrato->getContrato());
        $itemcontrato->setQuantidade(1);
        $itemcontrato->setValor($valorResponsabilidadeFinanceira);
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

        return $itemcontrato;
    }

    public function geraItemContratoPagamento($orcamento, $contratoAPagar, &$responsabilidade, $fornecedor, $id_grupoempresarial, $logged_user, $tenant, $valorResponsabilidadeFinanceira, $propostaitemPadrao = null)
    {
        $composicao = null;
        $itemdefaturamento = null;

        //COMPOSICAO
        if($orcamento->getComposicao() !== null && isset($orcamento->getComposicao()['composicao'])) {
            $composicao = $orcamento->getComposicao()['composicao'];
            $composicao = $this->crmComposicoesService->find($composicao,$tenant,$id_grupoempresarial);
        } 
        //FAMILIA
        elseif ($orcamento->getFamilia() !== null) {
            // $familia = $orcamento->getFamilia();
            $propostaitem = $orcamento->getPropostaitem();
            if($orcamento->getPropostaitem() != null) {
                //buscar a composicao pelo propostaitem
                $propostaitem = $this->crmPropostasitensRepository->findSemProposta($propostaitem, $tenant, $orcamento->getAtc(), $id_grupoempresarial);
                $composicao = $this->crmComposicoesService->find($propostaitem['composicao'],$tenant,$id_grupoempresarial);
            } else {
                //busca itemfaturamento usando propostaitem e composição de outro orçamento
                $propostaitem = $this->crmPropostasitensRepository->findSemProposta($propostaitemPadrao, $tenant, $orcamento->getAtc(), $id_grupoempresarial);
                $composicao = $this->crmComposicoesService->find($propostaitem['composicao'],$tenant,$id_grupoempresarial);
            }
        } else {
            throw new LogicException("Não foi possível encontrar o serviço");
        }

        //buscar o item de faturamento
        $itemdefaturamento = $this->servicosItensdefaturamentoService->findObject(
            $composicao['itemfaturamento']['servico'],
            $tenant,
            $id_grupoempresarial
        );

        $diasvencimento = $fornecedor->getDiasparavencimento() ? $fornecedor->getDiasparavencimento() : $this->webConfiguracoesService->getValor($tenant, 'DIASPARAVENCIMENTO');
        $fornecedor->setDiasparavencimento($diasvencimento);

        $itemcontratoAPagar = new Itenscontratos();
        $itemcontratoAPagar->setContrato($contratoAPagar->getContrato());
        $itemcontratoAPagar->setQuantidade(1);
        $itemcontratoAPagar->setValor($valorResponsabilidadeFinanceira);
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

        return $itemcontratoAPagarNovo;
    }

    /**
    * @param string  $tenant
    * @param string  $id_grupoempresarial
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity
    * @return string
    * @throws \Exception
    */
    public function delete($tenant,$id_grupoempresarial,$logged_user, \Nasajon\MDABundle\Entity\Crm\Orcamentos $entity){
        try {
            $this->getRepository()->begin();
            $this->validateDelete($tenant,$id_grupoempresarial, $entity);

            $response = $this->getRepository()->delete($tenant,$id_grupoempresarial,$logged_user,  $entity);

            // Chamo atualização de descontos globais dos orçamentos do fornecedor no atendimento
            if ($entity->getAtualizardescontosglobais()) {
                $fornecedorEnvolvido = [
                    'fornecedor' => [
                        'fornecedor' => $entity->getFornecedor()->getFornecedor()
                    ]
                ];
                $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosService->fillEntity($fornecedorEnvolvido);
    
                $fornecedorEnvolvidoObj = $this->fornecedoresEnvolvidosService->fornecedorenvolvidoatualizardescontoorcamentos($entity->getAtc(), $logged_user, $tenant, $id_grupoempresarial, $fornecedorEnvolvidoObj);
                $response = [];
                $response['descontoglobalunitario'] = $fornecedorEnvolvidoObj->getDescontoglobalunitario();
                $response['descontoglobalresto'] = $fornecedorEnvolvidoObj->getDescontoglobalresto();
                $response['descontoglobalrestoorcamento'] = $fornecedorEnvolvidoObj->getDescontoglobalrestoorcamento();
            }
                                                        
            $this->getRepository()->commit();

            return $response;

        } catch(LogicException $e){
            $this->getRepository()->rollBack();
            throw new LogicException($e->getMessage());
        } catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }

    }

    /**
     * Função responsável em fazer todos os tipos de validação antes de deletar o registro da base
     * @param $tenant
     * @param $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Orcamentos $orcamentos
     */
    private function validateDelete($tenant,$id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Orcamentos $orcamentos)
    {

        $filter = new Filter();
        $expressions = [
            new FilterExpression('atc', 'eq', $orcamentos->getAtc()),
            new FilterExpression('composicao', 'isNotNull', ''),
        ];
        $filter->setFilterExpression($expressions);
        $servicosOrcamento = $this->findAll($tenant, $id_grupoempresarial, $filter);
        //Levo em consideração o atual registro, então tem que existir no mínimo 1
        if (count($servicosOrcamento) <= 1) {
            throw new LogicException("Não é possível ter orçamento sem serviços, caso deseja excluir todos os serviços deve-se cancelar o acionamento.");
        }

    }

    /**
     * Retorna o tipo de faturamento default por fornecedor
     */
    public function getTipoFaturamentoDefault($atcPossuiSeguradora, $fornecedorTerceirizado, $esperaPgtoSeguradora){
        if ($atcPossuiSeguradora) {
            if ($fornecedorTerceirizado) {
                if ($esperaPgtoSeguradora) {
                    return self::TIPO_FATURAMENTO_NAO_FATURAR;
                } else {
                    return self::TIPO_FATURAMENTO_SEGURADORA_SEM_NFS;
                }
            } else {
                return self::TIPO_FATURAMENTO_SEGURADORA_COM_NFS;
            }
        } else {
            return self::TIPO_FATURAMENTO_CLIENTE_COM_NFS;
        }
    }
}
