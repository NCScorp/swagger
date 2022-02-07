<?php
namespace AppBundle\Service\Meurh;

use DateTime;
use Exception;
use DateTimeZone;
use Nasajon\MDABundle\Request\Filter;
use AppBundle\Service\Web\ConfiguracoesService;
use Nasajon\LoginBundle\Workflow\Enum\WorkflowEnum;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesferias;
use AppBundle\Traits\Meurh\SolicitacoeshistoricosTrait;
use AppBundle\Interfaces\HistoricoSolicitacoesInterface;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowServiceTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\LoginBundle\Workflow\Interfaces\WorkflowServiceInterface;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesferiasService as ParentService;
use Nasajon\SDK\Diretorio\DiretorioClient;
use AppBundle\Util\CpfCpnjUtil;

class SolicitacoesferiasService extends ParentService implements WorkflowServiceInterface, HistoricoSolicitacoesInterface
{
    use SolicitacoeshistoricosTrait;
    use WorkflowServiceTrait;

    /**
     * @var ParameterBag
     */
    protected $fixedAttributes;

    /**
     * @var Nasajon\MDABundle\Service\Persona\TrabalhadoresService
     */
    protected $trabalhadoresService;

    /**
     * @var ConfiguracoesService
     */
    protected $configuracoesService;

    /**
     * @var DiretorioClient
     */
    protected $diretorioSDK;

    protected $diretorio_sistema_id;

    protected $estabelecimentosService;

    public function __construct(\Nasajon\MDABundle\Repository\Meurh\SolicitacoesferiasRepository $repository, $fixedAttributes,  $trabalhadoresService, $configuracoesService, $diretorioSDK, $estabelecimentosService){
        parent::__construct($repository);
        $this->fixedAttributes = $fixedAttributes;
        $this->trabalhadoresService = $trabalhadoresService;
        $this->configuracoesService = $configuracoesService;
        $this->diretorioSDK = $diretorioSDK;
        $this->diretorio_sistema_id = getenv("diretorio_sistema_id");
        $this->estabelecimentosService = $estabelecimentosService;
    }

    /**
    * @param mixed $trabalhador
    * @param string  $tenant
    * @param string  $logged_user
    * @param Solicitacoesferias $entity
    * @return string
    * @throws \Exception
    */
    public function insert($trabalhador, $tenant, $logged_user, Solicitacoesferias $entity)
    {
        // if (!$this->validaDiasSolicitacao($tenant, $entity)) {
        //     throw new \DomainException('O total da soma dos dias concedidos mais os dias vendidos de um trabalhador não deve ultrapassar o saldo do mesmo');
        // }

        $this->getRepository()->begin();
        /* Sobrescrevendo para preencher a data de aviso */
        $entity = $this->preencheDataAviso($tenant,$entity);
        /* Sobrescrevendo para preencher a data de aviso */
        $response = $this->getRepository()->insert($trabalhador,$tenant,$logged_user,  $entity);

        // Sobrescrito para Iniciar o workflow
        //O workflow não pode iniciar na situação rascunho
        if($entity->getSituacao() !== -1){
            $this->workflowIniciar( $entity->getSolicitacao() );
        }
        $this->getRepository()->commit();
        return $response;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param Solicitacoesferias $entity
     * @return string
     * @throws \Exception
     */
    public function update($tenant, $logged_user, Solicitacoesferias $entity)
    {
        // if (!$this->validaDiasSolicitacao($tenant, $entity)) {
        //     throw new \DomainException('O total da soma dos dias concedidos mais os dias vendidos de um trabalhador não deve ultrapassar o saldo do mesmo');
        // }

        $this->getRepository()->begin();
        $entityAntigo = $this->findObject($entity->getSolicitacao(), $tenant,$entity->getTrabalhador());
        $valorNovo = $this->findNovos($entity, $entityAntigo);
        $valorAntigo = $this->findAntigos($entity, $entityAntigo);
        $entity->setValornovo($valorNovo);
        $entity->setValorantigo($valorAntigo);
        /* Sobrescrevendo para preencher a data de aviso */
        $entity = $this->preencheDataAviso($tenant,$entity);
        /* Sobrescrevendo para preencher a data de aviso */
        $response = $this->getRepository()->update($tenant, $logged_user,  $entity);

        // Sobrescrito para Iniciar o workflow
        //O workflow não pode iniciar na situação rascunho e caso ele ainda não tenha sido iniciado
        if($entity->getSituacao() !== -1 && is_null($entity->getWkfEstado())){
            $this->workflowIniciar( $entity->getSolicitacao() );
        }
        $this->getRepository()->commit();
        return $response;
    }

    /**
     * @return bool
     */
    private function validaDiasSolicitacao(int $tenant, Solicitacoesferias $entity)
    {
        $diasDireito = $this->getDiasDireitoColaborador(
            $tenant,
            $entity->getTrabalhador(),
            $entity->getDatainicioperiodoaquisitivo()
        );

        $diasSolicitacao = $entity->getDiasferiascoletivas() + $entity->getDiasvendidos();

        if ($diasSolicitacao > $diasDireito) {
            return false;
        }

        $solicitacao = $entity->getSolicitacao() ? $entity->getSolicitacao() : null;

        $qtdDiasJaSolicitados = $this->getRepository()->getDiasJaSolicitados(
            $tenant,
            $entity->getTrabalhador(),
            $entity->getDatainicioperiodoaquisitivo(),
            $solicitacao
        );

        if (!$qtdDiasJaSolicitados) {
            return true;
        }

        return $diasSolicitacao + $qtdDiasJaSolicitados <= $diasDireito;
    }

    /**
     * @return int
     */
    private function getDiasDireitoColaborador(int $tenant, string $trabalhador, $inicioperiodoaquisitivo)
    {
        return $this->getDireito($this->getDiasDireitoPeriodoAquisitivo(
            $tenant,
            $trabalhador,
            $inicioperiodoaquisitivo
        ));
    }

    public function findAll( $tenant, $trabalhador,  Filter $filter = null){
        $this->getRepository()->validateOffset($filter);
        $entities = $this->getRepository()->findAll($tenant, $trabalhador,  $filter);
        $entities = array_map(function($entity){
            if($entity['situacao'] < 2){
                $entity = $this->workflowAdicionarCampos($entity['solicitacao'], $entity);
            } else {
                $entity['_acoes'] = [];
                $entity['_editavel'] = false;
                if($entity['wkf_estado']){
                    $entity = $this->workflowTratarCamposRegistros($entity);
                }
            }
            return $entity;
          }, $entities);
        return $this->workflowTratarCamposRegistros($entities);
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $trabalhador
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant, $trabalhador){
        $data = $this->getRepository()->find($id , $tenant, $trabalhador);
        //Sobrescrito para preencher as informações do workflow
        if($data['situacao'] < 2){
            $data = $this->workflowAdicionarCampos($id, $data);
        } else {
            $data['_acoes'] = [];
            $data['_editavel'] = false;
            if($data['wkf_estado']){
                $data = $this->workflowTratarCamposRegistros($data);
            }
        }
        return $data;
    }

    public function preencheDataAviso($tenant,$entity){
        $trabalhador = $this->trabalhadoresService->find($entity->getTrabalhador(), $tenant);
        $lotacao = isset($trabalhador['lotacao']) ? $trabalhador['lotacao']['lotacao'] : null;
        $tomador = isset($trabalhador['lotacao'])  && ($trabalhador['lotacao']['tipo'] == 2 || $trabalhador['lotacao']['tipo'] == 3) ? $trabalhador['lotacao']['tomador'] : null;
        $sindicato = isset($trabalhador['sindicato']) ? $trabalhador['sindicato'] : null;
        $feriados = $this->finAllFeriados($tenant,$entity->getEstabelecimento(),$lotacao,$sindicato,$tomador);
        $datainicio = $entity->getDatainiciogozo();
        $dataaviso = date('Y-m-d', strtotime("-1 months", strtotime($datainicio)));
        $dayofweek = date('w', strtotime($dataaviso));
        while( (isset($trabalhador['repousodomingo']) && $trabalhador['repousodomingo'] && $dayofweek == '0') ||
            (isset($trabalhador['repousosegunda']) && $trabalhador['repousosegunda'] && $dayofweek == '1') ||
            (isset($trabalhador['repousoterca']) && $trabalhador['repousoterca'] && $dayofweek == '2') ||
            (isset($trabalhador['repousoquarta']) && $trabalhador['repousoquarta'] && $dayofweek == '3') ||
            (isset($trabalhador['repousoquinta']) && $trabalhador['repousoquinta'] && $dayofweek == '4') ||
            (isset($trabalhador['repousosexta']) && $trabalhador['repousosexta'] && $dayofweek == '5') ||
            (isset($trabalhador['repousosabado']) && $trabalhador['repousosabado'] && $dayofweek == '6') ||
            in_array($dataaviso, $feriados) ) {
            $dataaviso = date('Y-m-d', strtotime("-1 day", strtotime($dataaviso)));
            $dayofweek = date('w', strtotime($dataaviso));       
        }
        $entity->setDataaviso($dataaviso);
        return $entity;
    }

    public function finAllFeriados($tenant, $estabelecimento = "", $lotacao = "", $sindicato = "", $tomador=""){
        return $this->getRepository()->finAllFeriados($tenant, $estabelecimento, $lotacao, $sindicato, $tomador);
    }

    /**
     * Retornar um array associativo com a configuração que deverá ser usada pelo Workflow neste fluxo.
     * - escopo
     * - processo
     * 
     * @return array
     */
    public function getWorkflowConfiguracoes() {
      //Recupera o escopoworkflow do grupoempresarial
      $tenant = $this->fixedAttributes->get('tenant');
      $estabelecimento = $this->fixedAttributes->get('estabelecimento');
      $escopo = $this->workflowGetEscopoPorEstabelecimento($tenant, $estabelecimento);
      $configuracao = $this->fixedAttributes->get('gestores_todos_niveis');
      return [
          "processo" => WorkflowEnum::WORKFLOW_PROCESSO_MEURH_SOLICITACOES_FERIAS,
          "identificacao" => WorkflowEnum::WORKFLOW_IDENTIFICACAO_MEUTRABALHO,
          "escopo" => $escopo ? $escopo : false,
          "configuracao_gestores" => $configuracao
      ];
    }

    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @return string
     * @throws \Exception
     */
    public function cancelar($logged_user, $tenant, Solicitacoesferias $entity)
    {
        try {
            $this->getRepository()->begin();

            $podeCancelar = $this->getRepository()->verificaSolicitacoesFuturas(
                $tenant,
                $entity->getTrabalhador(),
                $entity->getEstabelecimento(),
                $entity->getDatafimperiodoaquisitivo()
            );
    
            if (!$podeCancelar) {
                throw new \DomainException('Esta solicitação não pode ser cancelada pois já existem solicitações em períodos aquisitivos posteriores a ela.');
            }

            $response = parent::cancelar($logged_user, $tenant, $entity);

            $this->getRepository()->commit();

            $configuracoesFormatadas = $this->configuracoesService->getConfiguracoesFormatadas($tenant);
            $configuracaoNotificarCancelar = $this->getNotificacaoCancelarConfiguracao($entity,$configuracoesFormatadas);

            if ($configuracaoNotificarCancelar) {
                $logAcoesWorkflow = $this->workflowHistorico($entity->getSolicitacao());
                $emails = [];

                foreach($logAcoesWorkflow as $registro){
                    if($logged_user['email'] != $registro['username']){
                        $emails[] = $registro['username'];
                    }
                }

                if (count($emails) > 0) {
                    $emails = array_unique($emails);
                    $textotemplate = "%usariologado%" . ' cancelou uma solicitação de férias  %usariodoc%';
                    $dados_template = $this->workflowTemplateDadosTemplate($entity->getSolicitacao(), null, 'CANCELAR',$textotemplate);
                    $template = $this->getTemplateNotificacaoCancelarConfiguracao($entity,$configuracoesFormatadas);
                    $this->workflowNotificar($dados_template, $template, $emails);
                }
            }

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function getNotificacaoCancelarConfiguracao($entity,$configuracoes){
        $tipo = $entity->getTiposolicitacao();
        switch ($tipo) {
            case 0:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_ADMISSAO_PRELIMINAR'];
            case 1:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_RESCISAO'];
            case 2:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_ADIANTAMENTO_AVULSO'];
            case 3:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_VT_ADICIONAL'];
            case 4:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT'];
            case 5:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO'];
            case 6:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_FALTA'];
            case 7:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_FERIAS'];
            case 8:
                return false;
            case 9:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_PROMOCAO'];
            case 10:
                return $configuracoes['NOT_MEUTRABALHO_CANCELAR_CREDITOS_DESCONTOS'];
            default:
                return false;
        }
    } 
    public function getTemplateNotificacaoCancelarConfiguracao($entity,$configuracoes){
        $tipo = $entity->getTiposolicitacao();
        switch ($tipo) {
            case 0:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_ADMISSAO_PRELIMINAR'];
            case 1:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_RESCISAO'];
            case 2:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_ADIANTAMENTO_AVULSO'];
            case 3:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_VT_ADICIONAL'];
            case 4:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT'];
            case 5:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO'];
            case 6:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_FALTA'];
            case 7:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_FERIAS'];
            case 8:
                return false;
            case 9:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_PROMOCAO'];
            case 10:
                return $configuracoes['TEM_MEUTRABALHO_CANCELAR_CREDITOS_DESCONTOS'];
            default:
                return false;
        }
    }    

    public function workflowTemplateDadosTemplate($id, $comentario, $acao, $textotemplate)
    {   
        $tenant = $this->fixedAttributes->get('tenant');
        $trabalhador = $this->fixedAttributes->get('trabalhador');
        $documento = $this->getRepository()->find($id , $tenant, $trabalhador);
        $estabelecimentosol = $documento['estabelecimento'];
        $estabelecimentosol = isset($estabelecimentosol['estabelecimento']) ? $estabelecimentosol['estabelecimento']: $estabelecimentosol;
        $empresa = $this->estabelecimentosService->getEmpresaByEstabelecimento($tenant, $estabelecimentosol);
        $texto = str_replace("%usariologado%", $this-> workflowgetNomeLoggerUser() , $textotemplate);
        $texto = str_replace("%usariodoc%", '. ', $texto);
        $texto = str_replace("enviou", "lhe enviou" , $texto);
        $texto = str_replace("para", "" , $texto);
        $documento['notificacao'] = $texto;
        $informacoestenant = $this->diretorioSDK->getInformacoesTenant($tenant);
        if(!empty($informacoestenant['logo'])){
            $documento['logourl'] = $informacoestenant['logo'];

        }
        if(!empty($comentario)){
            $documento['justificativamensagem'] = $comentario ;
        }
        $documento['nomecolaborador'] = $documento['trabalhador']['nome'];
        $colaborador = $this->trabalhadoresService->getInfoTrabalhador($documento['trabalhador']['trabalhador'],$tenant);
        $documento['colaboradorcargoniveltexto'] = $colaborador['cargo_nome'] .' ' . $colaborador['nivelcargo_nome'];
        $documento['colaboradorempresatexto'] = $empresa['razaosocial'];
        $textocpfcnpj = $empresa['raizcnpjestabelecimento'] ?  CpfCpnjUtil::formatar_cpf_cnpj($empresa['raizcnpjestabelecimento'] . $empresa['ordemcnpjestabelecimento']) : CpfCpnjUtil::formatar_cpf_cnpj('' . $empresa['cpfestabelecimento']);
        $documento['colaboradorestabelecimentotexto'] = $empresa['codigoest'] . ' - ' .  $empresa['nomefantasiaest'] . ' - ' .$textocpfcnpj;
        return $documento;
    }
  
    public function workflowNotificar($dados_template, $template, $emails)
    {   
        $tenant = $this->fixedAttributes->get('tenant');
        foreach($emails as $email){
            $trabalhador = $this->trabalhadoresService->getNomeByIdentificacaoNasajon($tenant,$email);
            $dados_template['mensagem'] = $trabalhador ? 'Olá, '. $trabalhador['nome'] . '!' : 'Olá!' ;
            $dados = [
                'sistema_id' => $this->diretorio_sistema_id,
                'codigo' => $template,
                'tenant' => $tenant,
                'contas' => [$email],
                'dados' => $dados_template
            ];
            try {
                $result = $this->diretorioSDK->postNotificacao($dados);
            } catch (\Exception $e) {
                if($e->getMessage() !== "Created"){
                    throw $e;
                } 
            }
        }
        return;
    }


    /**
     * Compara dois valores da classe, e retorna true or false,
     * dependendo se é igual ou não, respectivamente.
     * 
     * @return array
     */
    public function compararValores($key, $value1, $value2) {
        switch ($key) {
            case "descricao":
            case "justificativa":
                if(strcmp($value1, $value2) != 0) {
                    return [
                    "nome" => $key,
                    "valor" => $value1
                    ];
                }
    
                return null;
                break;
            case "dataaviso":
            case "datainiciogozo":
            case "datafimgozo":
            case "datainicioperiodoaquisitivo":
            case "datafimperiodoaquisitivo":
                if(strcmp($value1, $value2) != 0) {
                
                    $date =  date("d/m/Y", strtotime($value1));
                    return [
                        "nome" => $key,
                        "valor" => $date
                    ];
                }
                return null;
                break;
            case "diasvendidos":
            case "diasferiascoletivas":
                if(intval($value1) != intval($value2)) {
                    return [
                        "nome" => $key,
                        "valor" => $this->toNumber($value1)
                    ];
                }
    
                return null;
                break;
            case "adto13nasferias":
                if($value1 != $value2) {                    
                    return [
                        "nome" => $key,
                        "valor" => $value1 ? 'Sim' : 'Não'
                    ];
                }

                return null;
                break;
            case "temabonopecuniario":
                if($value1 != $value2) {                    
                    return [
                        "nome" => $key,
                        "valor" => $value1 ? 'Sim' : 'Não'
                    ];
                }
                return null;
                break;
            default:
                return null;
        }
    }

    public function listaPeriodosAquisitivosAbertos($tenant,$id){
        $trabalhador = $this->getRepository()->listaPeriodosAquisitivosAbertos($tenant, $id);

        return $this->ajustaPeriodosAquisitivos($trabalhador);
    }

    /**
     * @return array
     */
    private function ajustaPeriodosAquisitivos(array $trabalhador)
    {
        if (!$trabalhador['periodosaquisitivos']) {
            return;
        }

        $trabalhador['periodosaquisitivos'] = array_map(
            function($periodoaquisitivo) {
                $periodoaquisitivo['inicioperiodoaquisitivo'] = $periodoaquisitivo['inicioperiodoaquisitivoferias'];
                unset($periodoaquisitivo['inicioperiodoaquisitivoferias']);

                $periodoaquisitivo['fimperiodoaquisitivo'] = $periodoaquisitivo['fimperiodoaquisitivoferias'];
                unset($periodoaquisitivo['fimperiodoaquisitivoferias']);

                return $periodoaquisitivo;
            },
            $trabalhador['periodosaquisitivos']
        );

        return $trabalhador;
    }

    public function findSolicitacoesAgrupadasPorPeriodo($tenant,$id){
        $trabalhador = $this->getRepository()->findSolicitacoesAgrupadasPorPeriodo($tenant,$id);
        foreach($trabalhador['periodosaquisitivos'] as $key => $periodo){
            $trabalhador['periodosaquisitivos'][$key]['solicitacoes'] = array_map(function($entity){
                if(!is_null($entity['solicitacao']) && $entity['situacao'] == 0){
                    $entity = $this->workflowAdicionarCampos($entity['solicitacao'], $entity);
                } else {
                    $entity['_acoes'] = [];
                    $entity['_editavel'] = false;
                    if($entity['wkf_estado']){
                        $entity = $this->workflowTratarCamposRegistros($entity);
                    }
                }
                return $entity;
              }, $trabalhador['periodosaquisitivos'][$key]['solicitacoes']);
        }
        return $trabalhador;
    }



    public function getDireito($faltas) {
        return $this->getRepository()->getDireito($faltas);
    }


    public function getDiasDireitoPeriodoAquisitivo($tenant, $trabalhador, $inicioperiodoaquisitivoferias){
        return $this->getRepository()->getDiasDireitoPeriodoAquisitivo($tenant, $trabalhador, $inicioperiodoaquisitivoferias);
    }


}