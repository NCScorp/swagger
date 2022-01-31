<?php

namespace Nasajon\AppBundle\Service\Gp;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Entity\Crm\Propostasitens;
use GuzzleHttp;
use Nasajon\MDABundle\Entity\Financas\Projetos;
use Nasajon\MDABundle\Entity\Gp\Tarefas;
use Nasajon\MDABundle\Service\Gp\TarefasService as ParentService;
use Nasajon\MDABundle\Entity\Gp\Projetosescopo;

class TarefasService extends ParentService
{

  /**
   * @var Nasajon\MDABundle\Service\Web\ConfiguracoesService;
   */
  protected $webConfiguracoesService;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack;
   */
  protected $requestStack;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface;
   */
  protected $container;

  /**
   * @var Nasajon\MDABundle\Repository\Crm\PropostasRepository
   */
  protected $propostasRepository;

  /**
   * @var Nasajon\MDABundle\Repository\Crm\AtcsRepository
   */
  protected $atcsRepository;

  /**
   * @var Nasajon\AppBundle\Repository\TenantsRepository
   */
  protected $tenantsRepository;

  /**
   * @var Nasajon\AppBundle\Repository\ProjetosescopoRepository
   */
  protected $projetosescopoRepository;

  public function __construct(\Nasajon\MDABundle\Repository\Gp\TarefasRepository $repository, $webConfiguracoesService, $requestStack, $container, $propostasRepository, $atcsRepository, $tenantsRepository, $crmComposicoesService, $projetosescopoRepository){
    $this->repository = $repository;
    $this->webConfiguracoesService = $webConfiguracoesService;
    $this->requestStack = $requestStack;
    $this->container = $container;
    $this->propostasRepository = $propostasRepository;
    $this->atcsRepository = $atcsRepository;
    $this->tenantsRepository = $tenantsRepository;
    $this->crmComposicoesService = $crmComposicoesService;
    $this->projetosescopoRepository = $projetosescopoRepository;
  }

  /**
   * Verifica se CRM possui integração com GP.
   * @param mixed $tenant
   * @return boolean
   */
  private function possuiIntegracao($tenant) {
    $integracao = $this->webConfiguracoesService->getConfiguracao($tenant, 'INTEGRACAO_GP');
    if($integracao['valor'] != 1) {
      return false;
    } else {
      return true;
    }
  }

    /**
     * Cria a tarefa correspondente no GP a partir da propostaItem
     * 
     * Método está em desuso dada modificações na interação com o GP (09/2021)
     * 
     * @param Propostasitens $propostaItem
     * @param mixed $tenant
     * @return string
     */
    public function criarTarefaNoGP(Propostasitens $propostaItem, $tenant, $logged_user)
    {
        try {

            $this->getRepository()->begin();

            if (!$this->possuiIntegracao($tenant)) {
                $this->getRepository()->commit();
                return [false, "Sistema sem Integração. Tarefa não criada"];
            }

            $atc = $propostaItem->getNegocio();
            $projeto = $atc->getProjeto();

            if ($projeto == null) {
                $this->getRepository()->commit();
                return [false, "Projeto não registrado. Tarefa não criada."];
            }
            $idProjeto = $projeto->getProjeto();

            $escopoExecucoes = $this->webConfiguracoesService->getValor($tenant, 'GP_PROJETOESCOPOEXECUCOES');
            $escopoTipo = $this->webConfiguracoesService->getValor($tenant, 'GP_PROJETOESCOPOTIPO');
            $escopoTempoAdquirido = $this->webConfiguracoesService->getValor($tenant, 'GP_PROJETOESCOPOTEMPOADQUIRIDO');
            $produtoObrigatorio = $this->webConfiguracoesService->getValor($tenant, 'PRODUTOOBRIGATORIO');

            $composicao = $propostaItem->getComposicao();
            $composicao = $this->crmComposicoesService->findObject($composicao->getComposicao(), $tenant, $propostaItem->getIdGrupoempresarial());
            $servicoTecnico = $composicao->getServicotecnico();

            /** montagem dos endereços */
            $enderecos = $propostaItem->getPropostasitensenderecos();
            $enderecos = $enderecos->toArray();
            $enderecosToSend = [];
            foreach ($enderecos as $endereco) {
                // $id = $endereco->getEnderecoid()->getEndereco();
                $id = $endereco->getEnderecoid()['endereco'];
                $ordem = $endereco->getOrdem();
                array_push($enderecosToSend, array('endereco' => $id, 'ordem' => $ordem));
            }

            /** montagem das funcoes */
            $funcoes = $composicao->getFuncoes();
            $funcoes = $funcoes->toArray();
            $funcoestoSend = [];
            foreach ($funcoes as $funcao) {
                $id = $funcao->getFuncao();
                $id = $id['funcao'];
                array_push($funcoestoSend, array('funcao' => $id));
            }

            /** montagem das familias */
            $familias = $composicao->getFamilias();
            $familias = $familias->toArray();
            // $familiastoSend = [];
            // foreach ($familias as $familia) {
            //     $id = $familia->getFamilia();
            //     $id = $id['familia'];
            //     array_push($familiastoSend, array('familia' => $id));
            // }

            /* Validações de endereços, produtos e datas de previsão*/
            // if (count($enderecos) <= 0) {
            //   throw new \Exception("Endereço não informado no serviço do item de pedido. Favor preencher!", 1);    
            // }

            if ((count($familias) <= 0) && (is_bool($produtoObrigatorio))) {
                throw new \Exception("Produtos não informados no item de pedido. Favor preencher!", 1);
            }

            $tenant = $this->tenantsRepository->findOneByTenant($tenant);

            // CRIAÇÃO DO ESCOPO
            $dados = [
                'processar_ao_salvar' => true,
                'projeto' => $idProjeto,
                'tipo' => $escopoTipo,
                'descricao' => $propostaItem->getNome(),
                'execucoes' => $escopoExecucoes,
                'tempoadquirido' => $escopoTempoAdquirido,
                'datainicio' => '',
                'datafim' => '',
                'previsaoinicio' => $propostaItem->getPrevisaodatahorainicio(),
                'previsaofim' => $propostaItem->getPrevisaodatahorafim()
            ];
            $retornoInsert = $this->projetosescopoRepository->insert($idProjeto, $logged_user, $tenant['tenant'], $dados);

            // if($dados['processar_ao_salvar'] === true){
            // INICIAR ESCOPO
            $retornoIniciar = $this->projetosescopoRepository->iniciar($logged_user, $tenant['tenant'], $retornoInsert);
            $tarefas = $this->repository->findTarefasPorEscopos("('" . $retornoInsert['projetoescopo'] . "')", $tenant['tenant']);
            $id_tarefa = $tarefas[0]['tarefa'];
            $this->repository->persistirRelacionamentosTarefa($id_tarefa, $tenant['tenant'], $logged_user, $enderecosToSend, $funcoestoSend, []);

            // AJUSTANDO DADOS TAREFA
            $tarefa = $this->find($id_tarefa, $tenant['tenant']);
            // $tarefa = $this->fillEntity($data);
            $tarefa['servicotecnico'] = $servicoTecnico->getServicotecnico();
            $tarefa['propostaitem'] = $propostaItem->getPropostaitem();
            $tarefa['valor'] = $propostaItem->getValor();
            $tarefa['previsaoinicio'] = $propostaItem->getPrevisaodatahorainicio();
            $tarefa['previsaotermino'] = $propostaItem->getPrevisaodatahorafim();
            $retornoAjustarTarefa = $this->repository->alterar($logged_user, $tenant['tenant'], $tarefa);
            $tarefaAjustada = $this->find($id_tarefa, $tenant['tenant']);
            // }

            $this->getRepository()->commit();
            return $id_tarefa;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            // throw $e;
            return [false, $e->getMessage()];
        }
    }
  
  /**
   * Edita a tarefa correspondente no GP a partir da propostaItem
   * @param Propostasitens $propostaItem
   * @param mixed $tenant
   * @return string
  */
  public function editarTarefaNoGp (Propostasitens $propostaItem, $tenant, $logged_user) {
    try {
      $this->getRepository()->begin();
      
      $tarefa = $propostaItem->getTarefa();

      if(!$this->possuiIntegracao($tenant)) {
        $this->getRepository()->commit();
        return [false,"Sistema sem Integração. Tarefa não criada"];
      }

      if($tarefa == null) {
        $this->getRepository()->commit();
        return [false,"Tarefa não existe. Tarefa não editada."];
      }

      if($this->tarefaEmAndamento($tarefa)) {
        throw new \Exception("Não se pode alterar ou cancelar um item com tarefa em andamento.", 1);
      }

      $idTarefa = $tarefa->getTarefa();
      $tenant = $this->tenantsRepository->findOneByTenant($tenant);

      $tarefa = $this->find($idTarefa, $tenant['tenant']);
      // $tarefa = $this->fillEntity($data);
      $tarefa['propostaitem'] = $propostaItem->getPropostaitem();
      $tarefa['previsaoinicio'] = $propostaItem->getPrevisaodatahorainicio();
      $tarefa['previsaotermino'] = $propostaItem->getPrevisaodatahorafim();
      $retornoAjustarTarefa = $this->repository->alterar($logged_user, $tenant['tenant'], $tarefa);
      
      $this->getRepository()->commit();

      return $retornoAjustarTarefa;
    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      // throw $e;
      return [false,$e->getMessage()];
    }
  }

  /**
   * Exclui (cancela) a tarefa correspondente no GP a partir da propostaItem
   * @param Propostasitens $propostaItem
   * @param mixed $tenant
   * @return string
  */
  public function excluirTarefaNoGp(Propostasitens $propostaItem, $tenant, $logged_user)
  {
    try {
      $this->getRepository()->begin();
      $tarefa = $propostaItem->getTarefa();
      if($tarefa == null) {
        return true;
      }
      
      if(!$this->possuiIntegracao($tenant)) {
        $this->getRepository()->commit();
        return [false,"Sistema sem Integração. Tarefa não criada"];
      }
      
      if($this->tarefaEmAndamento($tarefa)) {
        $this->getRepository()->commit();
        throw new \Exception("Não se pode alterar ou cancelar um item com tarefa em andamento.", 1);
      }

      if($this->tarefaCancelada($tarefa)) {
        $this->getRepository()->commit();
        throw new \Exception("Não se pode alterar ou cancelar um item com tarefa cancelada.", 1);
      }

      $idTarefa = $tarefa->getTarefa();

      // $request = $this->requestStack->getCurrentRequest();
      $tenant = $this->tenantsRepository->findOneByTenant($tenant);

      $count = $this->repository->findOSPorTarefa($idTarefa, $tenant['tenant']);

      if ($count > 0) {
        throw new \Exception("Não é possível cancelar a tarefa pois existem Ordens de Serviço vinculadas a ela.");
      }

      $tarefa = $this->repository->find($idTarefa, $tenant['tenant']);
      
      $tarefa['propostaitem'] = $propostaItem->getPropostaitem();
      $tarefa['situacao'] = 5;
      $retornoAjustarTarefa = $this->repository->alterar($logged_user, $tenant['tenant'], $tarefa);
      $this->getRepository()->commit();

      if($this->getRepository()->getConnection()->isTransactionActive()){
        $this->getRepository()->commit();
        /*o commit acima foi repetido pois foi detectado que está sendo aberto mais um transaction begin além
        daquele no começo do método e do metodo de desvincular em propostasitensService, sendo necessário fechá-los.
        não consegui detectar a origem do transaction begin extra.
        */
      }

      return $retornoAjustarTarefa;

    } catch (\Exception $e) {
      $this->getRepository()->rollBack();
      // throw $e;
      return [false,$e->getMessage()];
    }
  }

  /**
   * 
   */
  private function tarefaEmAndamento($tarefa) {
    if($tarefa->getSituacao() == 2) {
      return true;
    }
    return false;
  }

  private function tarefaCancelada($tarefa) {
    if($tarefa->getSituacao() == 5) {
      return true;
    }
    return false;
  }
}