<?php

namespace AppBundle\Service\Meurh;

use Nasajon\MDABundle\Service\Meurh\SolicitacoesalteracoesvtsService as ParentService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use AppBundle\Exception\SolicitacaoAlteracaoVtException;
use Nasajon\LoginBundle\Workflow\Interfaces\WorkflowServiceInterface;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowServiceTrait;
use Nasajon\LoginBundle\Workflow\Enum\WorkflowEnum;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use AppBundle\Interfaces\HistoricoSolicitacoesInterface;
use Nasajon\MdaBundle\Service\Persona\TarifasconcessionariasvtsService;
use Nasajon\MDABundle\Service\Meurh\SolicitacoeshistoricosService;
use AppBundle\Traits\Meurh\SolicitacoeshistoricosTrait;
use Nasajon\SDK\Diretorio\DiretorioClient;
use AppBundle\Service\Web\ConfiguracoesService;
use AppBundle\Util\CpfCpnjUtil;

class SolicitacoesalteracoesvtsService extends ParentService implements WorkflowServiceInterface, HistoricoSolicitacoesInterface
{
    use SolicitacoeshistoricosTrait;
    use WorkflowServiceTrait;

    /**
     * @var ParameterBag
     */
    protected $fixedAttributes;

    /**
     * @var TarifasconcessionariasvtsService
     */
    protected $tarifasconcessionariasvtsService;

    /**
     * @var SolicitacoeshistoricosService
     */
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

    protected $trabalhadoresService;

    public function __construct(\Nasajon\MDABundle\Repository\Meurh\SolicitacoesalteracoesvtsRepository $repository, $mrhSlctcsltrcsvtstrfsSrvc, $fixedAttributes, TarifasconcessionariasvtsService $tarifasconcessionariasvtsService, SolicitacoeshistoricosService $solicitacoeshistoricosService, $diretorioSDK, $configuracoesService, $estabelecimentosService, $trabalhadoresService)
    {
        $this->repository = $repository;
        $this->mrhSlctcsltrcsvtstrfsSrvc = $mrhSlctcsltrcsvtstrfsSrvc;
        $this->fixedAttributes = $fixedAttributes;
        $this->tarifasconcessionariasvtsService = $tarifasconcessionariasvtsService;
        $this->solicitacoeshistoricosService = $solicitacoeshistoricosService;
        $this->diretorioSDK = $diretorioSDK;
        $this->diretorio_sistema_id = getenv("diretorio_sistema_id");
        $this->configuracoesService = $configuracoesService;
        $this->estabelecimentosService = $estabelecimentosService;
        $this->trabalhadoresService = $trabalhadoresService;

    }

    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $trabalhador)
    {

        $data = $this->getRepository()->find($id, $tenant, $trabalhador);

        /** Sobrescrevendo para passar o id correto da solicitação para o find all
         * O código gerado pelo MDA está passando $solicitacao como parâmetro
         */
        /** Início sobrescrita */
        $data['solicitacoesalteracoesvtstarifas'] = $this->mrhSlctcsltrcsvtstrfsSrvc->findAll($id, $tenant);
        /** Fim sobrescrita */

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
    public function findAll($tenant,$trabalhador,$tiposolicitacao = "", Filter $filter = null)
    {
        $this->getRepository()->validateOffset($filter);
        
        if( !empty($tiposolicitacao) ){
            if( !$filter ){
                $filter = new Filter();
            }
            $filterExp = new FilterExpression('tiposolicitacao', 'eq', $tiposolicitacao);
            $filter->addToFilterExpression($filterExp);
        }

        // Sobrescrito para retonar os campos do workflow
        $entities = $this->getRepository()->findAll($tenant,$trabalhador, $filter);

        $entities = array_map(function($entity){
            if($entity['situacao'] < 2){
                $entity = $this->workflowAdicionarCampos($entity['solicitacao'], $entity);
            } else {
                $entity['_acoes'] = [];
                $entity['_editavel'] = false;
            }
            return $entity;
          }, $entities);
        // Sobrescrito para retonar os campos do workflow

        return $entities;
    }

    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts $entity
     * @return string
     * @throws \Exception
     */
    public function insert($trabalhador, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts $entity)
    {
        try {
            $this->getRepository()->begin();

            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $entity->setEstabelecimento($estabelecimento);

            $response = $this->getRepository()->insert($trabalhador, $logged_user, $tenant,  $entity);
            if($entity->getSolicitacoesalteracoesvtstarifas() == null || count($entity->getSolicitacoesalteracoesvtstarifas()) == 0){
                throw new SolicitacaoAlteracaoVtException('Não é possível salvar uma solicitação sem tarifas');
            }
            $this->persistChildSolicitacoesalteracoesvtstarifas(null, $entity->getSolicitacoesalteracoesvtstarifas()->toArray(), $entity, $tenant);

            // Sobrescrito para Iniciar o workflow
            $this->workflowIniciar( $entity->getSolicitacao() );

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    protected function persistChildSolicitacoesalteracoesvtstarifas($oldList, $newList, $entity, $tenant)
    {

        if (!$oldList) {
            $oldList = [];
        }
        $newIds = array_map(function ($entity) {
            return $entity->getSolicitacaoalteracaovttarifa();
        }, $newList);

        while ($item = array_pop($oldList)) {
            $id = $item->getSolicitacaoalteracaovttarifa();
            $index = array_search($id, $newIds);
            if ($index === false) {

                $this->mrhSlctcsltrcsvtstrfsSrvc->delete($entity->getTenant(), $item);
            } else {
                $newitem = $newList[$index];
                array_splice($newList, $index, 1);
                array_splice($newIds, $index, 1);
            }

            unset($index);
            unset($item);
            unset($newitem);
        }

        /** Sobrescrevendo pois o código que o MDA está gerando está chamando $entity->getSolicitacaoalteracaovt()
         * em vez de $entity->getSolicitacao()
         */
        /** Início sobrescrita */
        foreach ($newList as $item) {
            $this->mrhSlctcsltrcsvtstrfsSrvc->insert($entity->getSolicitacao(), $entity->getTenant(), $item);
        }
        /** Fim sobrescrita */
    }

    public function update($tenant,$logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts $entity, $originalEntity = null ){
        try {
            $this->getRepository()->begin();
            if($entity->getSolicitacoesalteracoesvtstarifas() == null || count($entity->getSolicitacoesalteracoesvtstarifas()) == 0){
                throw new SolicitacaoAlteracaoVtException('Não é possível salvar uma solicitação sem tarifas');
            }

            $entityAntigo = $this->findObject($entity->getSolicitacao(), $tenant, $this->fixedAttributes->get('trabalhador'));
            $valorNovo = $this->findNovos($entity, $entityAntigo);
            $valorAntigo = $this->findAntigos($entity, $entityAntigo);
            $entity->setValornovo($valorNovo);
            $entity->setValorantigo($valorAntigo);

            $solicitacao = $this->getRepository()->update($tenant,$logged_user,  $entity, $originalEntity );
            $historico = $this->solicitacoeshistoricosService->getHistoricoSolicitacaoBySolicitacao($tenant, $solicitacao["solicitacao"]);
            $response = array_merge($solicitacao, $historico);

            $this->persistChildSolicitacoesalteracoesvtstarifas($originalEntity->getSolicitacoesalteracoesvtstarifas()->toArray(), $entity->getSolicitacoesalteracoesvtstarifas()->toArray(), $entity, $tenant);
            $this->getRepository()->commit();
            return $response;

        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
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
            "processo" => WorkflowEnum::WORKFLOW_PROCESSO_MEURH_SOLICITACOES_ALTERACAO_VT,
            "identificacao" => WorkflowEnum::WORKFLOW_IDENTIFICACAO_MEUTRABALHO,
            "escopo" => $escopo ? $escopo : false,
            "configuracao_gestores" => $configuracao
        ];
    }

    public function cancelar($logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesvts $entity)
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
                    $textotemplate = "%usariologado%" . ' cancelou uma solicitação de alteração de vt %usariodoc%';
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
            case "motivo":
                if (strcmp($value1, $value2) !== 0) {
                    return [
                        "nome" => $key,
                        "valor" => $value1
                    ];
                }

                return null;
                break;
            case "solicitacoesalteracoesvtstarifas":
                $diff = array(
                    "nome" => $key,
                    "valor" => array()
                );

                foreach ($value1["elements"] as $key1 => $value) {
                    if(!array_key_exists($key1, $value2["elements"]) || 
                        $value["quantidade"] != $value2["elements"][$key1]["quantidade"] ||
                        $value["tarifaconcessionariavt"]["tarifaconcessionariavt"] != $value2["elements"][$key1]["tarifaconcessionariavt"]["tarifaconcessionariavt"]) {

                        $tarifaconcessionariavt = $this->tarifasconcessionariasvtsService->find($value["tarifaconcessionariavt"]["tarifaconcessionariavt"], $this->fixedAttributes->get('tenant'));

                        array_push($diff["valor"], "Tarifa: {$tarifaconcessionariavt["descricao"]} ({$value["quantidade"]}x)");
                    }
                }

                if(count($diff["valor"])) {
                    return $diff;
                }
                    return null;

                break;
            default:
                return null;
        }
    }
}
