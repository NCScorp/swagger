<?php

namespace AppBundle\Service\Meurh;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use AppBundle\Resources\Constant\TipoJustificativaConstant;
use AppBundle\Exception\ActionException;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas;
use AppBundle\Resources\Constant\FaltastrabalhadoresConstant;
use AppBundle\Util\PostgreSQLUtil;
use Nasajon\LoginBundle\Workflow\Interfaces\WorkflowServiceInterface;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowServiceTrait;
use Nasajon\LoginBundle\Workflow\Enum\WorkflowEnum;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesfaltasService as ParentService;
use Symfony\Component\Ldap\Entry;
use AppBundle\Interfaces\HistoricoSolicitacoesInterface;
use Nasajon\MDABundle\Service\Meurh\SolicitacoeshistoricosService;
use AppBundle\Traits\Meurh\SolicitacoeshistoricosTrait;
use Nasajon\SDK\Diretorio\DiretorioClient;
use AppBundle\Service\Web\ConfiguracoesService;
use AppBundle\Util\CpfCpnjUtil;

class SolicitacoesfaltasService extends ParentService implements WorkflowServiceInterface, HistoricoSolicitacoesInterface
{
    use SolicitacoeshistoricosTrait;
    use WorkflowServiceTrait;

    /**
     * @var ParameterBag
     */
    protected $fixedAttributes;

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

    public function __construct(\Nasajon\MDABundle\Repository\Meurh\SolicitacoesfaltasRepository $repository, $fixedAttributes, SolicitacoeshistoricosService $solicitacoeshistoricosService, $diretorioSDK, $configuracoesService, $estabelecimentosService, $trabalhadoresService)
    {
        parent::__construct($repository);
        $this->fixedAttributes = $fixedAttributes;
        $this->solicitacoeshistoricosService = $solicitacoeshistoricosService;
        $this->diretorioSDK = $diretorioSDK;
        $this->diretorio_sistema_id = getenv("diretorio_sistema_id");
        $this->configuracoesService = $configuracoesService;
        $this->estabelecimentosService = $estabelecimentosService;
        $this->trabalhadoresService = $trabalhadoresService;

    }

    public function insert($trabalhador, $tenant,$logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas $entity){
        try {
            $this->getRepository()->begin();
            $this->validateEntity($entity);
            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $entity->setEstabelecimento($estabelecimento);

            $dateArray = $entity->getDatas();
            usort($dateArray, function ($a, $b) {
              return strtotime($a) - strtotime($b);
            });
            $entity->setDatas($dateArray);

            //transforma do array em postgres para php
            $entity->setDatas(PostgreSQLUtil::arrayPhpToPostgre($entity->getDatas()));

            $response = $this->getRepository()->insert($trabalhador, $tenant,$logged_user, $entity);
            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    private function validateEntity(Solicitacoesfaltas &$entity){
        //bloqueio contra edição de faltas que não sejam pendentes
        if($entity->getSituacao() > FaltastrabalhadoresConstant::STATUS_PENDENTE){
            throw new ActionException('Apenas faltas pendentes podem ser editadas.');
        }
        //faltas injustificadas não podem ter descrição/justificativa
        if(!$entity->getJustificada()){
            $entity->setJustificativa(null);
        }
        //faltas justtificadas precisam de justificativa/descrição
        if($entity->getJustificada() == FaltastrabalhadoresConstant::TIPO_JUSTIFICADA){
          if($entity->getTipojustificativa() == TipoJustificativaConstant::OUTROS) {
            if(strlen($entity->getJustificativa()) == 0 || $entity->getJustificativa() == null){
              throw new ActionException('Faltas justificadas precisam ter uma justificativa.');
           }
          }
        }
    }

    public function fechar($logged_user,$tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas $entity){
        try {
            $this->getRepository()->begin();
            if($entity->getSituacao()!=0){
                throw new ActionException("Só é possível rejeitar uma solicitação de falta pendente");  
            }
            $response = $this->getRepository()->fechar($logged_user,$tenant,  $entity);
            $this->getRepository()->commit();
            return $response;
        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function findDraftObject($id , $tenant){
      $entity = $this->findDraft($id , $tenant);
      $obj = new Solicitacoesfaltas();
      $obj->setSolicitacao($entity['solicitacao']);
      return $obj;
  }

    public function update($trabalhador, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas $entity){
        try {
            $this->getRepository()->begin();
            $this->validateEntity($entity);
            $situacao = $entity->getSituacao();
            $entityNovo = $entity;
            $dateArray = $entity->getDatas();
            usort($dateArray, function ($a, $b) {
              return strtotime($a) - strtotime($b);
            });
            $entity->setDatas($dateArray);

            if($situacao !== -1) {
                $entityAntigo = $this->findObject($entity->getSolicitacao(), $tenant, $trabalhador);
                $entityAntigo->setDatas((array) $entityAntigo->getDatas());
                $entityNovo->setDatas($dateArray);

                $valorNovo = $this->findNovos($entityNovo, $entityAntigo);
                $valorAntigo = $this->findAntigos($entityNovo, $entityAntigo);

                $entity->setValornovo($valorNovo);
                $entity->setValorantigo($valorAntigo);
            } else {
                $entity->setSituacao(0);
                $entity->setValornovo(null);
                $entity->setValorantigo(null);
            }

            //transforma do array em postgres para php
            $entity->setDatas(PostgreSQLUtil::arrayPhpToPostgre($entity->getDatas()));

            $response = $this->getRepository()->update($trabalhador, $tenant, $logged_user, $entity);
            if($situacao !== -1) {
                $historico = $this->solicitacoeshistoricosService->getHistoricoSolicitacaoBySolicitacao($tenant, $response["solicitacao"]);
                $response = array_merge($response, $historico);
            }        
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
     * 
     * Sobrescrito por causa da conversão de json para array
     */
    public function find($id , $tenant, $trabalhador){

        $data = $this->getRepository()->find($id , $tenant, $trabalhador);

        //tratamento para array vindo do banco
        $data['datas'] = PostgreSQLUtil::arrayPostgreToPhp($data['datas']);

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

      $this->getRepository()->validateOffset($filter);

      $entities = $this->getRepository()->findAll($tenant,$trabalhador, $filter);

      // Sobrescrito para retorar os campos do workflow
      $entities = array_map(function($entity){
        if($entity['situacao'] < 2){
            $entity = $this->workflowAdicionarCampos($entity['solicitacao'], $entity);
        } else {
            $entity['_acoes'] = [];
            $entity['_editavel'] = false;
        }
        return $entity;
      }, $entities);
      // Sobrescrito para retorar os campos do workflow

      return $entities;
    }

  /**
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas $entity
   * @return string
   * @throws \Exception
   */
  public function draftInsert($trabalhador, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas $entity)
  {
    try {
      $this->getRepository()->begin();
      /* Início - Sobrescrita para adicionar o estabelecimento na entidade */
      $estabelecimento = $this->fixedAttributes->get('estabelecimento');
      $entity->setEstabelecimento($estabelecimento);
      // Origem MeuTrabalho
      $entity->setOrigem(2);
      $response = $this->getRepository()->draftInsert($trabalhador, $tenant, $logged_user,  $entity);
      /* Fim - Sobrescrita para adicionar o estabelecimento na entidade */

      // Sobrescrito para Iniciar o workflow
      $this->workflowIniciar( $entity->getSolicitacao() );

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
   * @return array
   */
  public function findDraft($id, $tenant)
  {
    $data = $this->getRepository()->findDraft($id, $tenant);
    return $data;
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
          "processo" => WorkflowEnum::WORKFLOW_PROCESSO_MEURH_SOLICITACOES_FALTA,
          "identificacao" => WorkflowEnum::WORKFLOW_IDENTIFICACAO_MEUTRABALHO,
          "escopo" => $escopo ? $escopo : false,
          "configuracao_gestores" => $configuracao
      ];
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

  public function cancelar($logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesfaltas $entity)
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
                    $textotemplate = "%usariologado%" . ' cancelou uma solicitação de falta %usariodoc%';
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




  
  /**
   * Compara dois valores da classe, e retorna true or false,
   * dependendo se é igual ou não, respectivamente.
   * 
   * @return array
   */
  public function compararValores($key, $value1, $value2) {
    switch ($key) {

        case "justificativa":
            if(strcmp($value1, $value2) != 0) {
                return [
                "nome" => $key,
                "valor" => $value1
                ];
            }

            return null;
            break;
        case "mesdescontocalculo":
            if(intval($value1) != intval($value2)) {
                $mes = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

                return [
                    "nome" => $key,
                    "valor" => $mes[intval($value1) - 1]
                ];
            }

            return null;
            break;

        case "anodescontocalculo":
            if(intval($value1) != intval($value2)) {

                return [
                    "nome" => $key,
                    "valor" => $value1
                ];
            }

            return null;
            break;

        // @todo quando modificar o front para aceitar diversas datas, é necessário modificar esta lógica
        case "datas":
            if(strcmp($value1[0], $value2[0]) != 0) {

                $date =  date("d/m/Y", strtotime($value1[0]));
                return [
                    "nome" => $key,
                    "valor" => $date
                ];
            }

            return null;
            break;
        case "tipojustificativa":
            if($value1 != $value2) {
                $tipo = array('Injustificada', 'Abono', 'Compensação', 'Outros');

                return [
                    "nome" => $key,
                    "valor" => $tipo[intval($value1)]
                ];
            }

            return null;
            break;

        default:
            return null;
    }
  }
}
