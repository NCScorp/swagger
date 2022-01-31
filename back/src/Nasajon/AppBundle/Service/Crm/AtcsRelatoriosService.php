<?php

namespace Nasajon\AppBundle\Service\Crm;

// Dependencias

use Nasajon\MDABundle\Request\Filter;
use Nasajon\AppBundle\DTO\Crm\AtcsDTO;
use Nasajon\AppBundle\DTO\Crm\SeguroDTO;
use Nasajon\AppBundle\DTO\Ns\EnderecoDTO;
use Nasajon\AppBundle\DTO\Crm\DocumentoDTO;
use Nasajon\AppBundle\DTO\Crm\PrestadorDTO;
use Nasajon\AppBundle\DTO\Crm\AtcsRelatorioDTO;
use Nasajon\AppBundle\DTO\Crm\ServicoOrcadoDTO;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\AppBundle\Entity\Common\XmlConverter;
use Nasajon\AppBundle\DTO\Crm\ResponsavelFinanceiroDTO;
use Nasajon\AppBundle\DTO\Crm\ConfiguracoestaxasadministrativasDTO;
use Nasajon\AppBundle\DTO\Crm\FornecedoresEnvolvidosDTO;
use Nasajon\MDABundle\Entity\Crm\Configuracoestaxasadministrativas;
use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos;

/**
 * Service utilizado para geração de relatórios do atendimento comercial
 */
class AtcsRelatoriosService
{
    /**
     * Repository de relatórios do atendimento comercial
     */
    protected $repository;

    protected $tokenStorage;

    protected $fixedAttributes;
    
    protected $atcsareasService;
    protected $estabelecimentosService;
    protected $clientesService;
    protected $atcsresponsaveisfinanceirosService;
    protected $atcsdadosseguradorasService;
    protected $ConfiguracoestaxasadministrativasService;

    public function __construct(
        \Nasajon\AppBundle\Repository\Crm\AtcsRelatoriosRepository $repository,
        $tokenStorage,
        $fixedAttributes,
        \Nasajon\MDABundle\Service\Crm\AtcsareasService $atcsareasService,
        \Nasajon\MDABundle\Service\Ns\EstabelecimentosService $estabelecimentosService,
        \Nasajon\MDABundle\Service\Ns\ClientesService $clientesService,
        \Nasajon\MDABundle\Service\Crm\AtcsresponsaveisfinanceirosService $atcsresponsaveisfinanceirosService,
        \Nasajon\MDABundle\Service\Crm\AtcsdadosseguradorasService $atcsdadosseguradorasService,
        \Nasajon\MDABundle\Service\Crm\ConfiguracoestaxasadministrativasService $ConfiguracoestaxasadministrativasService
    ) {
        $this->repository = $repository;
        $this->tokenStorage = $tokenStorage;
        $this->fixedAttributes = $fixedAttributes;
        $this->atcsareasService = $atcsareasService;
        $this->estabelecimentosService = $estabelecimentosService;
        $this->clientesService = $clientesService;
        $this->atcsresponsaveisfinanceirosService = $atcsresponsaveisfinanceirosService;
        $this->atcsdadosseguradorasService = $atcsdadosseguradorasService;
        $this->ConfiguracoestaxasadministrativasService = $ConfiguracoestaxasadministrativasService;
    }

    /**
     * Retorna XML utilizado para fazer relatórios de fornecedor no atendimento
     */
    public function getXmlAtendimentoComercial($tenant, $id_grupoempresarial, $atc, $cliente, $fornecedor, $fornecedoresEnvolvidos = null){
        $dadosXmlBD = $this->getDadosBDXmlAtendimentoComercial($tenant, $id_grupoempresarial, $atc, $cliente, $fornecedor);

        if(!is_null($fornecedoresEnvolvidos) && !empty($fornecedoresEnvolvidos) ){
            $arrServicosOrcadosFornecedoresEnvolvidos = [];
            foreach ($fornecedoresEnvolvidos as $key => $fornecedorEnvolvido) {
                $servicosOrcados = $this->repository->getServicosOrcadosAtendimento(
                    $tenant, $id_grupoempresarial, $atc, $fornecedorEnvolvido['fornecedor']['fornecedor']
                );
                foreach ($servicosOrcados as $key => $servicoOrcado) {
                    $servicoOrcado['fornecedor_nomefantasia'] = $fornecedorEnvolvido['fornecedor']['nomefantasia'];
                    $servicoOrcado['fornecedor_razaosocial'] = $fornecedorEnvolvido['fornecedor']['razaosocial'];
                    $arrServicosOrcadosFornecedoresEnvolvidos[] = $servicoOrcado;
                }
                $bp='';
            }
            $bp='';
            $dadosXmlBD['arrServicosOrcados'] = $arrServicosOrcadosFornecedoresEnvolvidos;
        }

        // Pego o tenant código do fixed atributes
        $tenantCodigo = $this->fixedAttributes->get('tenant_codigo');

        // Organizo dados para montagem do XML
        $dadosMontarXML = [
            'responsaveisfinanceiros' => [],
            'seguros' => [],
            'servicosorcados' => [],
            'documentos' => []
        ];
        
        // Organizo dados gerais do atendimento
        $dadosMontarXML['codigo'] = $dadosXmlBD['entidades']['atc_codigo'];
        $dadosMontarXML['nome'] = $dadosXmlBD['entidades']['atc_nome'];
        $dadosMontarXML['criadopor'] = json_decode($dadosXmlBD['entidades']['atc_created_by'], true)['nome'];
        $dadosMontarXML['datacriacao'] = $this->getDataFormatada($dadosXmlBD['entidades']['atc_created_at'], 'd/m/Y');
        $dadosMontarXML['possuiseguro'] = $this->getBooleanFormatado($dadosXmlBD['entidades']['atc_possuiseguradora']);
        $dadosMontarXML['observacao'] = $dadosXmlBD['entidades']['atc_observacoes'];
        $dadosMontarXML['areaatendimento'] = [
            'nome' => $dadosXmlBD['entidades']['atc_area_nome'],
            'exibelocalizacao' => $this->getBooleanFormatado($dadosXmlBD['entidades']['atc_area_localizacao'])
        ];
        $dadosMontarXML['midiaorigem'] = [
            'nome' => $dadosXmlBD['entidades']['atc_midia_nome']
        ];
        $dadosMontarXML['tipoacionamento'] = [
            'nome' => $dadosXmlBD['entidades']['atc_tipoacionamento_nome']
        ];
        $dadosMontarXML['atendimentopai'] = [
            'codigo' => $dadosXmlBD['entidades']['atcpai_codigo'],
            'nome' => $dadosXmlBD['entidades']['atcpai_nome']
        ];

        // Organizo dados da localização do atendimento
        $dadosMontarXML['localizacaoatendimento'] = [
            'tipologradouro' => $dadosXmlBD['entidades']['atc_localizacaotipologradouro'],
            'logradouro' => $dadosXmlBD['entidades']['atc_localizacaorua'],
            'numero' => $dadosXmlBD['entidades']['atc_localizacaonumero'],
            'complemento' => $dadosXmlBD['entidades']['atc_localizacaocomplemento'],
            'referencia' => $dadosXmlBD['entidades']['atc_localizacaoreferencia'],
            'cep' => $dadosXmlBD['entidades']['atc_localizacaocep'],
            'bairro' => $dadosXmlBD['entidades']['atc_localizacaobairro'],
            'uf' => $dadosXmlBD['entidades']['atc_localizacaoestado'],
            'paisnome' => $dadosXmlBD['entidades']['atc_localizacaopais'],
            'municipionome' => $dadosXmlBD['entidades']['atc_localizacaomunicipio'],
            'municipiosepultamento' => $dadosXmlBD['entidades']['atc_localizacaomunicipiosepultamento']
        ];
        $dadosMontarXML['localizacaoatendimento']['enderecocompleto'] = $dadosMontarXML['localizacaoatendimento']['tipologradouro'] . ' ' .
            $dadosMontarXML['localizacaoatendimento']['logradouro'] . ', ' . $dadosMontarXML['localizacaoatendimento']['numero'] . ' - ' .
            $dadosMontarXML['localizacaoatendimento']['bairro'] . ' - ' . $dadosMontarXML['localizacaoatendimento']['municipionome'] . ' - ' .
            $dadosMontarXML['localizacaoatendimento']['uf'] . ' - CEP: ' . $dadosMontarXML['localizacaoatendimento']['cep'];

        // Organizo dados do estabelecimento do Atendimento
        $dadosMontarXML['estabelecimento'] = [
            'razaosocial' => $dadosXmlBD['entidades']['estabelecimento_razaosocial'],
            'nomefantasia' => $dadosXmlBD['entidades']['estabelecimento_nomefantasia'],
            'cnpjcpf' => $dadosXmlBD['entidades']['estabelecimento_cnpj_completo'],
            'telefone' => $dadosXmlBD['entidades']['estabelecimento_telefonecomddd'],
            'pathlogo' => $this->tokenStorage->getToken()->getUser()->getTenants()[$tenantCodigo]->getLogo(),
            'endereco' => [
                'tipologradouro' => $dadosXmlBD['entidades']['estabelecimento_end_tipologradouro'],
                'logradouro' => $dadosXmlBD['entidades']['estabelecimento_end_logradouro'],
                'numero' => $dadosXmlBD['entidades']['estabelecimento_end_numero'],
                'complemento' => $dadosXmlBD['entidades']['estabelecimento_end_complemento'],
                'cep' => $dadosXmlBD['entidades']['estabelecimento_end_cep'],
                'bairro' => $dadosXmlBD['entidades']['estabelecimento_end_bairro'],
                'uf' => $dadosXmlBD['entidades']['estabelecimento_end_uf'],
                'paisnome' => $dadosXmlBD['entidades']['estabelecimento_end_paisnome'],
                'municipionome' => $dadosXmlBD['entidades']['estabelecimento_end_municipionome']
            ]
        ];

        $dadosMontarXML['estabelecimento']['endereco']['enderecocompleto'] = $dadosMontarXML['estabelecimento']['endereco']['tipologradouro'] . ' ' .
            $dadosMontarXML['estabelecimento']['endereco']['logradouro'] . ', ' . $dadosMontarXML['estabelecimento']['endereco']['numero'] . ' - ' .
            $dadosMontarXML['estabelecimento']['endereco']['bairro'] . ' - ' . $dadosMontarXML['estabelecimento']['endereco']['municipionome'] . ' - ' .
            $dadosMontarXML['estabelecimento']['endereco']['uf'] . ' - CEP: ' . $dadosMontarXML['estabelecimento']['endereco']['cep'];
        
        // Organizo dados do Cliente/Seguradora do Atendimento
        $dadosMontarXML['cliente_seguradora'] = [
            'codigo' => $dadosXmlBD['entidades']['cliente_codigo'],
            'razaosocial' => $dadosXmlBD['entidades']['cliente_nome'],
            'nomefantasia' => $dadosXmlBD['entidades']['cliente_nomefantasia'],
            'cnpjcpf' => $dadosXmlBD['entidades']['cliente_cnpj'],
            'inscricaomunicipal' => $dadosXmlBD['entidades']['cliente_inscricaomunicipal'],
            'eh_seguradora' => $this->getBooleanFormatado($dadosXmlBD['entidades']['atc_possuiseguradora']),
            'anotacoes' => $dadosXmlBD['entidades']['cliente_anotacao'],
            'enderecolocal' => [
                'tipologradouro' => $dadosXmlBD['entidades']['cliente_endlocal_tipologradouro'],
                'logradouro' => $dadosXmlBD['entidades']['cliente_endlocal_logradouro'],
                'numero' => $dadosXmlBD['entidades']['cliente_endlocal_numero'],
                'complemento' => $dadosXmlBD['entidades']['cliente_endlocal_complemento'],
                'cep' => $dadosXmlBD['entidades']['cliente_endlocal_cep'],
                'bairro' => $dadosXmlBD['entidades']['cliente_endlocal_bairro'],
                'uf' => $dadosXmlBD['entidades']['cliente_endlocal_uf'],
                'paisnome' => $dadosXmlBD['entidades']['cliente_endlocal_pais'],
                'municipionome' => $dadosXmlBD['entidades']['cliente_endlocal_municipio'],
                'referencia' => $dadosXmlBD['entidades']['cliente_endlocal_referencia'],
                'nome' => $dadosXmlBD['entidades']['cliente_endlocal_nome']
            ],
            'enderecocobranca' => [
                'tipologradouro' => $dadosXmlBD['entidades']['cliente_endcob_tipologradouro'],
                'logradouro' => $dadosXmlBD['entidades']['cliente_endcob_logradouro'],
                'numero' => $dadosXmlBD['entidades']['cliente_endcob_numero'],
                'complemento' => $dadosXmlBD['entidades']['cliente_endcob_complemento'],
                'cep' => $dadosXmlBD['entidades']['cliente_endcob_cep'],
                'bairro' => $dadosXmlBD['entidades']['cliente_endcob_bairro'],
                'uf' => $dadosXmlBD['entidades']['cliente_endcob_uf'],
                'paisnome' => $dadosXmlBD['entidades']['cliente_endcob_pais'],
                'municipionome' => $dadosXmlBD['entidades']['cliente_endcob_municipio'],
                'referencia' => $dadosXmlBD['entidades']['cliente_endcob_referencia'],
                'nome' => $dadosXmlBD['entidades']['cliente_endcob_nome']
            ]
        ];

        $dadosMontarXML['cliente_seguradora']['enderecolocal']['enderecocompleto'] = $dadosMontarXML['cliente_seguradora']['enderecolocal']['tipologradouro'] . ' ' .
            $dadosMontarXML['cliente_seguradora']['enderecolocal']['logradouro'] . ', ' . $dadosMontarXML['cliente_seguradora']['enderecolocal']['numero'] . ' - ' .
            $dadosMontarXML['cliente_seguradora']['enderecolocal']['bairro'] . ' - ' . $dadosMontarXML['cliente_seguradora']['enderecolocal']['municipionome'] . ' - ' .
            $dadosMontarXML['cliente_seguradora']['enderecolocal']['uf'] . ' - CEP: ' . $dadosMontarXML['cliente_seguradora']['enderecolocal']['cep'];
        
        $dadosMontarXML['cliente_seguradora']['enderecocobranca']['enderecocompleto'] = $dadosMontarXML['cliente_seguradora']['enderecocobranca']['tipologradouro'] . ' ' .
            $dadosMontarXML['cliente_seguradora']['enderecocobranca']['logradouro'] . ', ' . $dadosMontarXML['cliente_seguradora']['enderecocobranca']['numero'] . ' - ' .
            $dadosMontarXML['cliente_seguradora']['enderecocobranca']['bairro'] . ' - ' . $dadosMontarXML['cliente_seguradora']['enderecocobranca']['municipionome'] . ' - ' .
            $dadosMontarXML['cliente_seguradora']['enderecocobranca']['uf'] . ' - CEP: ' . $dadosMontarXML['cliente_seguradora']['enderecocobranca']['cep'];

        // Organizo dados dos responsáveis financeiros do Atendimento
        foreach ($dadosXmlBD['arrResponsaveisFinanceiros'] as $responsavelFinanceiro) {
            $dadosMontarXML['responsaveisfinanceiros'][] = [
                'nome' => $responsavelFinanceiro['nome'],
                'principal' => $this->getBooleanFormatado($responsavelFinanceiro['principal'])
            ];
        }

        // Organizo dados dos documentos do Atendimento
        foreach ($dadosXmlBD['arrDocumentos'] as $documento) {
            $dadosMontarXML['documentos'][] = [
                'nome' => $documento['nome'],
                'datarecebimento' => $this->getDataFormatada($documento['data_recebimento'], 'd/m/Y'),
                'copiaautenticada' => $this->getBooleanFormatado($documento['copiaautenticada']),
                'copiasimples' => $this->getBooleanFormatado($documento['copiasimples']),
                'original' => $this->getBooleanFormatado($documento['original'])
            ];
        }

        // Organizo dados dos serviços orçados do Atendimento para o fornecedor
        $totalServicosPorApolice = [];

        foreach ($dadosXmlBD['arrServicosOrcados'] as $servicoOrcado) {
            $dadosMontarXML['servicosorcados'][] = [
                'descricao' => $servicoOrcado['servico_nome'],
                'valor' => $servicoOrcado['servico_valor'],
                'quantidade' => $servicoOrcado['quantidade'],
                'descontoparcial' => $servicoOrcado['descontoparcial'],
                'descontoglobal' => $servicoOrcado['descontoglobal'],
                'valorreceber' => $servicoOrcado['valorreceber'],
                'fornecedor_nomefantasia' => $servicoOrcado['fornecedor_nomefantasia'] ?? null,
                'fornecedor_razaosocial' => $servicoOrcado['fornecedor_razaosocial'] ?? null,
            ];

            if ($servicoOrcado['servico_apolice'] != null) {
                $vlReceberFinalComDescontoGlobal = $servicoOrcado['valorreceber'];
                if (array_key_exists($servicoOrcado['servico_apolice'], $totalServicosPorApolice)) {
                    $totalServicosPorApolice[$servicoOrcado['servico_apolice']] += $vlReceberFinalComDescontoGlobal;
                } else {
                    $totalServicosPorApolice[$servicoOrcado['servico_apolice']] = $vlReceberFinalComDescontoGlobal;
                }
            }
        }

        // Organizo dados dos seguros do Atendimento
        foreach ($dadosXmlBD['arrSeguros'] as $seguro) {
            $seguroXML = [
                'produto' => $seguro['produto'],
                'tipoapolice' => $seguro['tipoapolice'],
                'apolice' => $seguro['apolice'],
                'valorautorizado' => 0,
                'sinistro' => $seguro['sinistro'],
                'nomefuncionario' => $seguro['nomefuncionarioseguradora'],
                'titularapolice' => $seguro['titularnome'],
                'titularvinculo' => $seguro['titularvinculo']
            ];

            if (array_key_exists($seguro['apolice_id'], $totalServicosPorApolice)) {
                $seguroXML['valorautorizado'] = $totalServicosPorApolice[$seguro['apolice_id']];
            }

            $dadosMontarXML['seguros'][] = $seguroXML;
        }

        // Organizo dados do Fornecedor
        $dadosMontarXML['prestador'] = [
            'codigo' => $dadosXmlBD['entidades']['fornecedor_codigo'],
            'razaosocial' => $dadosXmlBD['entidades']['fornecedor_nome'],
            'nomefantasia' => $dadosXmlBD['entidades']['fornecedor_nomefantasia'],
            'cnpjcpf' => $dadosXmlBD['entidades']['fornecedor_cnpj'],
            'inscricaomunicipal' => $dadosXmlBD['entidades']['fornecedor_inscricaomunicipal'],
            'anotacoes' => $dadosXmlBD['entidades']['fornecedor_anotacao'],
            'esperapagamentoseguradora' => $dadosXmlBD['entidades']['fornecedor_esperapagamentoseguradora'],
            'enderecolocal' => [
                'tipologradouro' => $dadosXmlBD['entidades']['fornecedor_endlocal_tipologradouro'],
                'logradouro' => $dadosXmlBD['entidades']['fornecedor_endlocal_logradouro'],
                'numero' => $dadosXmlBD['entidades']['fornecedor_endlocal_numero'],
                'complemento' => $dadosXmlBD['entidades']['fornecedor_endlocal_complemento'],
                'cep' => $dadosXmlBD['entidades']['fornecedor_endlocal_cep'],
                'bairro' => $dadosXmlBD['entidades']['fornecedor_endlocal_bairro'],
                'uf' => $dadosXmlBD['entidades']['fornecedor_endlocal_uf'],
                'paisnome' => $dadosXmlBD['entidades']['fornecedor_endlocal_pais'],
                'municipionome' => $dadosXmlBD['entidades']['fornecedor_endlocal_municipio'],
                'referencia' => $dadosXmlBD['entidades']['fornecedor_endlocal_referencia'],
                'nome' => $dadosXmlBD['entidades']['fornecedor_endlocal_nome']
            ],
            'enderecocobranca' => [
                'tipologradouro' => $dadosXmlBD['entidades']['fornecedor_endcob_tipologradouro'],
                'logradouro' => $dadosXmlBD['entidades']['fornecedor_endcob_logradouro'],
                'numero' => $dadosXmlBD['entidades']['fornecedor_endcob_numero'],
                'complemento' => $dadosXmlBD['entidades']['fornecedor_endcob_complemento'],
                'cep' => $dadosXmlBD['entidades']['fornecedor_endcob_cep'],
                'bairro' => $dadosXmlBD['entidades']['fornecedor_endcob_bairro'],
                'uf' => $dadosXmlBD['entidades']['fornecedor_endcob_uf'],
                'paisnome' => $dadosXmlBD['entidades']['fornecedor_endcob_pais'],
                'municipionome' => $dadosXmlBD['entidades']['fornecedor_endcob_municipio'],
                'referencia' => $dadosXmlBD['entidades']['fornecedor_endcob_referencia'],
                'nome' => $dadosXmlBD['entidades']['fornecedor_endcob_nome']
            ],
            'dadosbancarios' => [],
            'tiposatividades' => [],
            'contatos' => []
        ];

        $dadosMontarXML['prestador']['enderecolocal']['enderecocompleto'] = $dadosMontarXML['prestador']['enderecolocal']['tipologradouro'] . ' ' .
            $dadosMontarXML['prestador']['enderecolocal']['logradouro'] . ', ' . $dadosMontarXML['prestador']['enderecolocal']['numero'] . ' - ' .
            $dadosMontarXML['prestador']['enderecolocal']['bairro'] . ' - ' . $dadosMontarXML['prestador']['enderecolocal']['municipionome'] . ' - ' .
            $dadosMontarXML['prestador']['enderecolocal']['uf'] . ' - CEP: ' . $dadosMontarXML['prestador']['enderecolocal']['cep'];
        
        $dadosMontarXML['prestador']['enderecocobranca']['enderecocompleto'] = $dadosMontarXML['prestador']['enderecocobranca']['tipologradouro'] . ' ' .
            $dadosMontarXML['prestador']['enderecocobranca']['logradouro'] . ', ' . $dadosMontarXML['prestador']['enderecocobranca']['numero'] . ' - ' .
            $dadosMontarXML['prestador']['enderecocobranca']['bairro'] . ' - ' . $dadosMontarXML['prestador']['enderecocobranca']['municipionome'] . ' - ' .
            $dadosMontarXML['prestador']['enderecocobranca']['uf'] . ' - CEP: ' . $dadosMontarXML['prestador']['enderecocobranca']['cep'];

        switch ($dadosXmlBD['entidades']['fornecedor_status']) {
            case '0': {
                $dadosMontarXML['prestador']['status'] = 'ATIVA';
                break;
            }   
            case '1': {
                $dadosMontarXML['prestador']['status'] = 'SUSPENSA';
                break;
            }
            case '2': {
                $dadosMontarXML['prestador']['status'] = 'BANIDA';
                break;
            }
            default:
                break;
        }

        // Organizo Dados Bancários do Fornecedor
        foreach ($dadosXmlBD['arrDadosBancariosFornecedor'] as $dadoBancario) {
            $dadosMontarXML['prestador']['dadosbancarios'][] = [
                'numerobanco' => $dadoBancario['banco_numero'],
                'nomebanco' => $dadoBancario['banco_nome'],
                'nomeagencia' => $dadoBancario['agencianome'],
                'numeroagencia' => $dadoBancario['agencianumero'],
                'dvagencia' => $dadoBancario['agenciadv'],
                'tipoconta' => $dadoBancario['conta_tipo'],
                'numeroconta' => $dadoBancario['contanumero'],
                'dvconta' => $dadoBancario['contadv'],
                'contaprincipal' => $this->getBooleanFormatado($dadoBancario['conta_principal'])
            ];
        }

        // Organizo Tipos de Atividade do Fornecedor
        foreach ($dadosXmlBD['arrTiposAtividadeFornecedor'] as $tipoAtividade) {
            $dadosMontarXML['prestador']['tiposatividades'][] = [
                'nome' => $tipoAtividade['nome'],
                'descricao' => $tipoAtividade['descricao']
            ];
        }


        // Organizo Contatos do Fornecedor
        $contatoXML = null;
        $contatoID = null;
        foreach ($dadosXmlBD['arrContatosFornecedor'] as $contato) {
            // Se é um contato diferente
            if ($contatoID != $contato['contato_id']) {
                // Se não for o primeiro contato, adiciono a lista de contatos dos dados de xml.
                if ($contatoID != null) {
                    $dadosMontarXML['prestador']['contatos'][] = $contatoXML;
                }

                // Atualizo id do contato atual
                $contatoID = $contato['contato_id'];

                $contatoXML = [
                    'nome' => $contato['contato_nome'],
                    'primeironome' => $contato['contato_primeironome'],
                    'sobrenome' => $contato['contato_sobrenome'],
                    'principal' => $this->getBooleanFormatado($contato['contato_principal']),
                    'cargo' => $contato['contato_cargo'],
                    'setor' => $contato['contato_setor'],
                    'email' => $contato['contato_email'],
                    'observacao' => $contato['contato_observacao'],
                    'telefones' => []
                ];
            }

            // Adiciono telefone a lista do contato
            $contatoXML['telefones'][] = [
                'ddi' => $contato['contato_tel_ddi'],
                'ddd' => $contato['contato_tel_ddd'],
                'telefone' => $contato['contato_tel_numero'],
                'ramal' => $contato['contato_tel_ramal'],
                'principal' => $this->getBooleanFormatado($contato['contato_tel_principal']),
                'observacao' => $contato['contato_tel_observacao'],
            ];
            
        }

        if (count($dadosXmlBD['arrContatosFornecedor']) > 0) {
            $dadosMontarXML['prestador']['contatos'][] = $contatoXML;
        }
        return $dadosMontarXML;
        // // Crio objeto de conversão de String XML
        // $xmlConverter = new XmlConverter();
        // $stringXML = $xmlConverter->converterArrayToXmlString($dadosMontarXML, [
        //     'tagXML' => 'atendimentocomercial',
        //     'arrTagsItemLista' => [
        //         'responsaveisfinanceiros' => 'responsavelfinanceiro',
        //         'dadosbancarios' => 'dadobancario',
        //         'tiposatividades' => 'tipoatividade',
        //         'servicosorcados' => 'servicoorcado'
        //     ]
        // ]);
        
        // // Retorna XML Formatado
        // return $xmlConverter->converterStringXmlToFormatedXML($stringXML);
    }

    /**
     * Retorna dados do banco de dados utilizados para montar o XML do fornecedor
     */
    private function getDadosBDXmlAtendimentoComercial($tenant, $id_grupoempresarial, $atc, $cliente, $fornecedor){
        $dadosXML = [];

        // Busco dados das entidades do Atendimento: Atendimento, Estabelecimento, Fornecedor e Cliente
        $dadosXML['entidades'] = $this->repository->getEntidades(
            $tenant, $id_grupoempresarial, $atc, $fornecedor, $cliente
        )[0];

        // Busco lista dos responsáveis financeiros do Atendimento
        $dadosXML['arrResponsaveisFinanceiros'] = $this->repository->getResponsaveisFinanceiros(
            $tenant, $id_grupoempresarial, $atc
        );

        // Busco lista dos documentos do Atendimento
        $dadosXML['arrDocumentos'] = $this->repository->getDocumentosAtendimento(
            $tenant, $id_grupoempresarial, $atc
        );

        // Busco lista dos serviços orçados do Atendimento
        $dadosXML['arrServicosOrcados'] = $this->repository->getServicosOrcadosAtendimento(
            $tenant, $id_grupoempresarial, $atc, $fornecedor
        );
        
        // Se o cliente for Seguradora, Busco lista dos seguros do Atendimento
        if ($dadosXML['entidades']['atc_possuiseguradora']) {
            $dadosXML['arrSeguros'] = $this->repository->getSegurosAtendimento(
                $tenant, $id_grupoempresarial, $atc
            );
        } else {
            $dadosXML['arrSeguros'] = [];
        }

        // Busco lista dos dados bancários do Fornecedor
        $dadosXML['arrDadosBancariosFornecedor'] = $this->repository->getDadosBancariosFornecedor(
            $tenant, $id_grupoempresarial, $fornecedor
        );

        // Busco lista dos tipos de atividade do Fornecedor
        $dadosXML['arrTiposAtividadeFornecedor'] = $this->repository->getTiposAtividadesFornecedor(
            $tenant, $id_grupoempresarial, $fornecedor
        );

        // Busco lista dos contatos do Fornecedor
        $dadosXML['arrContatosFornecedor'] = $this->repository->getContatosFornecedores(
            $tenant, $id_grupoempresarial, $fornecedor
        );
        
        return $dadosXML;
    }

    /**
     * Retorna a data formatada
     */
    private function getDataFormatada($strData, $strFormato) {
        $data = new \DateTime($strData);
        return $data->format($strFormato);
    }

    /**
     * Retorna o booleano formatado
     */
    private function getBooleanFormatado($valor) {
        return $valor ? 'SIM' : 'NÃO';
    }

    public function montaEntidadeAtendimentoComercial (\Nasajon\MDABundle\Entity\Crm\Atcs $atcsObject, $tenant, $id_grupoempresarial, $fornecedor, $fornecedoresEnvolvidos = null) {

        $arrDados = $this->getXmlAtendimentoComercial($tenant, $id_grupoempresarial, $atcsObject->getNegocio(), $atcsObject->getCliente()->getCliente(), $fornecedor, $fornecedoresEnvolvidos);
        
        $filter = new Filter();
        $filter->setFilterExpression([
            new FilterExpression('estabelecimento', 'eq', $atcsObject->getEstabelecimento()->getEstabelecimento()),
            new FilterExpression('seguradora', 'eq', $atcsObject->getCliente()->getCliente())
        ]);
        $configTxAdm = $this->ConfiguracoestaxasadministrativasService->findAll($tenant, $id_grupoempresarial, $filter);
        
        //transformando data do atendimento no formato correto
        $data = $atcsObject->getDatacriacao();
        $data = new \Datetime($data);
        $data = $data->format('d/m/Y');
        $atcsObject->setDatacriacao($data);

        $created_by = $atcsObject->getCreatedBy();
        // $created_by = json_decode($created_by);
        $atcsObject->setCreatedBy($created_by['nome']);

        //Removendo do atendimento campos de endereço e montando entidade
        $enderecoDTO = new EnderecoDTO();
        $enderecoDTO->fillDTO($arrDados['localizacaoatendimento']);
        $atcsObject->setLocalizacao($enderecoDTO);
        $atcsObject->setLocalizacaocep(null);
        $atcsObject->setLocalizacaobairro(null);
        $atcsObject->setLocalizacaorua(null);
        $atcsObject->setLocalizacaonumero(null);
        $atcsObject->setLocalizacaocomplemento(null);
        $atcsObject->setLocalizacaoreferencia(null);
        $atcsObject->setLocalizacaoestado(null);
        $atcsObject->setLocalizacaopais(null);
        $atcsObject->setLocalizacaomunicipio(null);
        $atcsObject->setLocalizacaotipologradouro(null);


        //setando se a area do atendimento possui localização da forma correta
        $area = $atcsObject->getArea();
        if($area->getLocalizacao() == null) {
            $area->setLocalizacao(false);
        }
        $atcsObject->setArea($area);

        //bucando responsaveis financeiros e seus dados
        $responsaveisFinanceiros = $atcsObject->getResponsaveisfinanceiros();
        foreach ($responsaveisFinanceiros as $key => $responsavel) {
            $responsavelDTO = new ResponsavelFinanceiroDTO();
            $responsavelDTO->fillDTO($arrDados['responsaveisfinanceiros'][$key]);

            $atcsObject->removeResponsaveisfinanceiro($responsavel);
            $atcsObject->addResponsaveisfinanceiro($responsavelDTO);
        }

        //buscando estabelecimento e seus dados
        $arrDados['estabelecimento'];
        $estabelecimentoDTO = new \Nasajon\AppBundle\DTO\Ns\EstabelecimentoDTO();
        $estabelecimentoDTO->fillDTO($arrDados['estabelecimento']);

        if(count($configTxAdm) > 0) {
            $configTxAdmDTO = new ConfiguracoestaxasadministrativasDTO();
            $configTxAdmDTO->fillDTO($configTxAdm[0]);
            $estabelecimentoDTO->setConfiguracaoTaxaAdministrativa($configTxAdmDTO);
        }

        $atcsObject->setEstabelecimento($estabelecimentoDTO);

        //buscando cliente e seus dados
        $cliente = $atcsObject->getCliente();
        $clienteObject = $this->clientesService->findObject($cliente->getCliente(),$tenant,$id_grupoempresarial);
        //removendo endereço e colocando DTO
        $enderecos = $clienteObject->getEndereco();
        $flagEndCobr = false;
        $flagEndLocal = false;
        foreach ($enderecos as $key => $endereco) {
            $enderecoDTO = new EnderecoDTO();
            if ($endereco->getTipoendereco() == 2 && !$flagEndCobr) {
                $enderecoDTO->fillDTO($arrDados['cliente_seguradora']['enderecocobranca'], 'enderecocobranca');
                $flagEndCobr = true;
            } else if ($endereco->getTipoendereco() == 1 && !$flagEndLocal) {
                $enderecoDTO->fillDTO($arrDados['cliente_seguradora']['enderecolocal'], 'enderecolocal');
                $flagEndLocal =true;
            }

            $clienteObject->removeEndereco($endereco);
            $clienteObject->addEndereco($enderecoDTO);
        }
        $atcsObject->setCliente($clienteObject);

        //buscando dadosseguradoras e seus dados
        $dadosSeguradoras = $atcsObject->getNegociosdadosseguradoras();
        foreach ($dadosSeguradoras as $key => $dadoseguradora) {
            $dadoseguradoraObject = $this->atcsdadosseguradorasService->findObject(
                $dadoseguradora->getNegociodadosseguradora(),
                $atcsObject->getNegocio(),
                $tenant,
                $id_grupoempresarial);
            $atcsObject->removeNegociosdadosseguradora($dadoseguradora);
            $atcsObject->addNegociosdadosseguradora($dadoseguradoraObject);
        }


        //montado objeto relatorio final
        $prestadorDTO = new PrestadorDTO();
        $prestadorDTO->fillDTO($arrDados['prestador']);
        
        
        $atcsRelatorioDTO = new AtcsRelatorioDTO();
        $atcsRelatorioDTO->setAtcs($atcsObject);
        $atcsRelatorioDTO->setPrestador($prestadorDTO);

        foreach ($arrDados['seguros'] as $key => $seguro) {
            $seguroDTO = new SeguroDTO();
            $seguroDTO->fillDTO($seguro);
            $atcsRelatorioDTO->addSeguro($seguroDTO);
        }

        foreach ($arrDados['servicosorcados'] as $key => $servicoorcado) {
            $servicoorcadoDTO = new ServicoOrcadoDTO();
            $servicoorcadoDTO->fillDTO($servicoorcado);
            $atcsRelatorioDTO->addServicosorcados($servicoorcadoDTO);
        }

        foreach ($arrDados['documentos'] as $key => $documento) {
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->fillDTO($documento);
            $atcsRelatorioDTO->addDocumentos($documentoDTO);
        }

        if(!is_null($fornecedoresEnvolvidos) && !empty($fornecedoresEnvolvidos) ){
            foreach ($fornecedoresEnvolvidos as $key => $fornecedorEnvolvido) {
                $atcsRelatorioDTO->addFornecedoresenvolvidos(FornecedoresEnvolvidosDTO::newFromArray($fornecedorEnvolvido));
            }
        }

        return $atcsRelatorioDTO;
    }
}
