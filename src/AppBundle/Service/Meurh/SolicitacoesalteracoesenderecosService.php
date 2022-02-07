<?php

namespace AppBundle\Service\Meurh;

use Nasajon\LoginBundle\Workflow\Enum\WorkflowEnum;
use Nasajon\LoginBundle\Workflow\Interfaces\WorkflowServiceInterface;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowServiceTrait;
use AppBundle\Traits\Meurh\SolicitacoeshistoricosTrait;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesalteracoesenderecosService as ParentService;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos;
use AppBundle\Interfaces\HistoricoSolicitacoesInterface;
use Nasajon\MDABundle\Service\MunicipiosService;
use Nasajon\MDABundle\Service\Meurh\SolicitacoeshistoricosService;
use Nasajon\SDK\Diretorio\DiretorioClient;
use AppBundle\Service\Web\ConfiguracoesService;
use AppBundle\Util\CpfCpnjUtil;

class SolicitacoesalteracoesenderecosService extends ParentService implements WorkflowServiceInterface, HistoricoSolicitacoesInterface
{
    use SolicitacoeshistoricosTrait;
    use WorkflowServiceTrait;

    protected $trabalhadoresService;

    protected $municipiosService;

    protected $paisesService;

    protected $tiposlogradourosService;

    protected $fixedAttributes;

    protected $solicitacoeshistoricosService;

    /**
     * @var DiretorioClient
     */
    protected $diretorioSDK;

    protected $diretorio_sistema_id;

        /**
     * @var ConfiguracoesService
     */
    protected $configuracoesService;

    protected $estabelecimentosService;

    public function __construct(\Nasajon\MDABundle\Repository\Meurh\SolicitacoesalteracoesenderecosRepository $repository, $trabalhadoresService, $paisesService, $municipiosService, $tiposlogradourosService, $fixedAttributes, $solicitacoeshistoricosService, $diretorioSDK, $configuracoesService, $estabelecimentosService)
    {
        $this->trabalhadoresService = $trabalhadoresService;
        $this->paisesService = $paisesService;
        $this->municipiosService = $municipiosService;
        $this->tiposlogradourosService = $tiposlogradourosService;
        $this->fixedAttributes = $fixedAttributes;
        $this->solicitacoeshistoricosService = $solicitacoeshistoricosService;
        $this->diretorioSDK = $diretorioSDK;
        $this->diretorio_sistema_id = getenv("diretorio_sistema_id");
        $this->configuracoesService = $configuracoesService;
        $this->estabelecimentosService = $estabelecimentosService;

        parent::__construct($repository);
    }

    public function findDraftObject($id , $tenant){
        $entity = $this->findDraft($id , $tenant);
        $obj = new Solicitacoesalteracoesenderecos();
        $obj->setSolicitacao($entity['solicitacao']);
        return $obj;
    }

    public function findDraft($id, $tenant)
    {
      $data = $this->getRepository()->findDraft($id, $tenant);
      return $data;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($trabalhador, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity)
    {
        try {
            $this->getRepository()->begin();
            //Sobrescrito para setar o estabelecimento
            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $entity->setEstabelecimento($estabelecimento);
            $response = $this->getRepository()->insert($trabalhador, $tenant, $logged_user, $entity);
            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function draftInsert($trabalhador, $tenant,$logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity){
        try {
            $this->getRepository()->begin();
            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $entity->setEstabelecimento($estabelecimento);
            $response = $this->getRepository()->draftInsert($trabalhador,$tenant,$logged_user,$entity);
            // Sobrescrito para Iniciar o workflow
            $this->workflowIniciar( $entity->getSolicitacao() );
            $this->getRepository()->commit();
            return $response;
        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity
     * @return string
     * @throws \Exception
     */
    public function abrir($tenant, $logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity)
    {
        try {
            $this->getRepository()->begin();

            $response = $this->getRepository()->abrir($tenant, $logged_user, $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $trabalhador
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant, $trabalhador)
    {
        $data = $this->getRepository()->find($id, $tenant, $trabalhador);

        //Sobrescrito para preencher as informações do workflow
        if($data['situacao'] < 2){
            $data = $this->workflowAdicionarCampos($id, $data);
        } else {
            $data['_acoes'] = [];
            $data['_editavel'] = false;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function findAll($tenant,$trabalhador, Filter $filter = null){
        $entities = $this->getRepository()->findAll($tenant,$trabalhador,$filter);
        $entities = array_map(function($entity){
            if($entity['situacao'] < 2){
                $entity = $this->workflowAdicionarCampos($entity['solicitacao'], $entity);
            } else {
                $entity['_acoes'] = [];
                $entity['_editavel'] = false;
            }
            return $entity;
          }, $entities);
        return $this->workflowTratarCamposRegistros($entities);
    }

    /**
     * Retornar um array associativo com a configuração que deverá ser usada pelo Workflow neste fluxo.
     * - escopo
     * - processo
     * 
     * @return array
     */
    public function getWorkflowConfiguracoes()
    {
        //Recupera o escopoworkflow do grupoempresarial
        $tenant = $this->fixedAttributes->get('tenant');
        $estabelecimento = $this->fixedAttributes->get('estabelecimento');
        $escopo = $this->workflowGetEscopoPorEstabelecimento($tenant, $estabelecimento);
        $configuracao = $this->fixedAttributes->get('gestores_todos_niveis');
        return [
            "processo" => WorkflowEnum::WORKFLOW_PROCESSO_MEURH_SOLICITACOES_ALTERACAO_ENDERECO,
            "identificacao" => WorkflowEnum::WORKFLOW_IDENTIFICACAO_MEUTRABALHO,
            "escopo" => $escopo ? $escopo : false,
            "configuracao_gestores" => $configuracao
        ];
    }

    public function cancelar($logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity)
    {
        try {
            $this->getRepository()->begin();
            $response = $this->getRepository()->cancelar($logged_user, $tenant,  $entity);
            $this->getRepository()->commit();
            $configuracoesFormatadas =  $this->configuracoesService->getConfiguracoesFormatadas($tenant);
            $configuracaoNotificarCancelar = $this->getNotificacaoCancelarConfiguracao($entity,$configuracoesFormatadas);
            if($configuracaoNotificarCancelar) {
                $logAcoesWorkflow = $this->workflowHistorico($entity->getSolicitacao());
                $emails = [];
                foreach($logAcoesWorkflow as $registro){
                    if($logged_user['email'] != $registro['username']){
                        $emails[] = $registro['username'];
                    }
                }
                if(count($emails)>0){
                    $emails = array_unique($emails);
                    $textotemplate = "%usariologado%" . ' cancelou uma solicitação de alteração de dados cadastrais %usariodoc%';
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
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity
     * @return string
     * @throws \Exception
     */
    public function update($tenant, $logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos $entity) {
        try {
            $this->getRepository()->begin();

            $entityAntigo = $this->findObject($entity->getSolicitacao(), $tenant, $this->fixedAttributes->get('trabalhador'));     

            $valorNovo = $this->findNovos($entity, $entityAntigo);
            $valorAntigo = $this->findAntigos($entity, $entityAntigo);
            $entity->setValornovo($valorNovo);
            $entity->setValorantigo($valorAntigo);

            $solicitacao = $this->getRepository()->update($tenant, $logged_user, $entity);
            $historico = $this->solicitacoeshistoricosService->getHistoricoSolicitacaoBySolicitacao($tenant, $solicitacao["solicitacao"]);
            $response = array_merge($solicitacao, $historico);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * Compara dois valores da classe, e retorna true or false,
     * dependendo se é igual ou não, respectivamente.
     * 
     * @return array
     */
    public function compararValores($key, $value1, $value2) {
        switch ($key) {

            case "justificativa":
            case "observacao":
            case "logradouro":
            case "numero":
            case "complemento":
            case "cep":
            case "bairro":
            case "email":
            case "dddtel":
            case "telefone":
            case "dddcel":
            case "celular":
                if (strcmp($value1, $value2) !== 0) {
                    return [
                        "nome" => $key,
                        "valor" => $value1
                    ];
                }

                return null;
                break;
            case "municipioresidencia":
                if (strcmp($value1["ibge"], $value2["ibge"]) !== 0) {
                    $municipio = $this->municipiosService->find($value1["ibge"], $this->fixedAttributes->get('tenant'));
                    return [
                        "nome" => $key,
                        "valor" => $municipio["nome"]
                    ];
                }
                return null;
                break;
            case "paisresidencia":
                if (strcmp($value1["pais"], $value2["pais"]) !== 0) {
                    $pais = $this->paisesService->find($value1["pais"], $this->fixedAttributes->get('tenant'));
                    return [
                        "nome" => $key,
                        "valor" => $pais["nome"]
                    ];
                }
                return null;
                break;
            case "tipologradouro":
                if ($value1["tipologradouro"] != $value2["tipologradouro"]) {
                    $tipologradouro = $this->tiposlogradourosService->find($value1["tipologradouro"], $this->fixedAttributes->get('tenant'));
                    return [
                        "nome" => $key,
                        "valor" => $tipologradouro["descricao"]
                    ];
                }
                return null;
                break;

            default:
                return null;
        }
    }
}
