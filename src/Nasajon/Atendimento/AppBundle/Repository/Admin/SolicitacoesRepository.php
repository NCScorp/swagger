<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\ORM\NoResultException;
use Exception;
use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosobservadoresRepository;
use Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService;
use Nasajon\Atendimento\AppBundle\Service\UploadService;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\MDABundle\Entity\Ns\Anexosmodulos;
use Nasajon\MDABundle\Repository\Atendimento\Admin\SolicitacoesRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SlasRepository;

use Nasajon\Atendimento\AppBundle\Event\Event;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

class SolicitacoesRepository extends ParentRepository {

    /**
     *
     * @var  UploadService
     */
    private $uploadService;

    /**
     *
     * @var AtendimentosfilasRepository
     */
    private $filasRepository;

    /**
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     *
     * @var AtendimentosobservadoresRepository
     */
    private $obsRepo;

    /**
     *
     * @var EquipeClienteFilterService
     */
    protected $equipeFilter;

    /**
     *
     * @var TraceableEventDispatcher
     */
    private $eventDispatcher;

    /**
     *
     * @var \Nasajon\ModelBundle\Services\ConfiguracoesService;
     */
    private $confService;

    /**
     *
     * @var array 
     */
    private $filters = [];

    /**
     *
     * @var array 
     */
    private $diretorio = [];
    
    private $slasRepo;
    private $chamadosSlasProducer;

    private $chamadosRegrasProducer;

    public function __construct(
            $connection,
            FollowupsRepository $followupsRepository,
            $srvcsTndmntsHstrcRpstry,
            UploadService $uploadService,
            AtendimentosfilasRepository $filasRepository,
            AtendimentosobservadoresRepository $obsRepo,
            TokenStorageInterface $tokenStorage,
            EquipeClienteFilterService $equipeFilter,
            $eventDispatcher,
            ConfiguracoesService $confService,
            $crmPrxmscnttsRpstry,
            $diretorio,
            SlasRepository $slasRepo,
            Producer $chamadosslasproducer,
            Producer $emailsproducer,
            $chamadosRegrasProducer
            ) {
        parent::__construct($connection, $followupsRepository, $crmPrxmscnttsRpstry, $srvcsTndmntsHstrcRpstry);
        $this->uploadService = $uploadService;
        $this->filasRepository = $filasRepository;
        $this->tokenStorage = $tokenStorage;
        $this->obsRepo = $obsRepo;
        $this->equipeFilter = $equipeFilter;
        $this->eventDispatcher = $eventDispatcher;
        $this->confService = $confService;
        $this->diretorio = $diretorio;
        $this->slasRepo = $slasRepo;
        $this->chamadosSlasProducer = $chamadosslasproducer;
        $this->emailsproducer = $emailsproducer;
        $this->chamadosRegrasProducer = $chamadosRegrasProducer;
    }
    
    public function proccessFilter($filter) {
      $filters = [];
      $binds = [];
      
      if (!is_null($filter) && (!empty($filter->getKey()) || $filter->getKey() == "0") && !empty($filter->getField())) {
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        
        if ((int)$filter->getKey() != 0) {
          $filters[] = $queryBuilder->expr()->eq("t0_.numeroprotocolo", "?");
          $binds[] = (int)$filter->getKey();
        } else {
          $filtro = StringUtils::removeTabulacoes(StringUtils::removeCaracteresInvalidosNoTsQuery(strtolower(StringUtils::removeAcentos($filter->getKey()))));
          $keys = explode(" ", $filtro);
          $keysSql = [];
          for ($i = 0; $i < count($keys); $i++) {
            if (!empty($keys[$i])) {
              $keysSql[] = " (?)::tsquery ";
              if ($i == (count($keys) - 1)) {
                $binds[] = $keys[$i] . ':*';
              } else {
                $binds[] = $keys[$i];
              }
            }
          }
          if (!empty($binds)) {
            $filters[] = $queryBuilder->expr()->comparison('t0_.busca', '@@', "(" . join(" && ", $keysSql) . ")");
          }
        }
      }
      return [$filters, $binds];
    }

      /**
     * @return array
     */
    public function findAll($tenant, $situacao= "", $responsavel_web= "", $cliente= "", $created_at= "",  $camposcustomizados= "", $visivelparacliente= "", $canal= "", $adiado= "", $orderfield= "", $qtd_respostas= "", $ultima_resposta_admin= "", $created_at_ini= "", $created_at_fim= "",  Filter $filter = null){
        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $situacao, $responsavel_web, $cliente, $created_at, $camposcustomizados, $visivelparacliente, $canal, $adiado, $orderfield, $qtd_respostas, $ultima_resposta_admin, $created_at_ini, $created_at_fim, $filter);
        $binds; 
        $queryBuilder;
        foreach($binds as $key => $bind){
            if($binds[$key] == 'undefined'){
                $binds[$key] = null;
            }
        } 

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL()); 

        $stmt->execute($binds);
        $joins = ['cliente', ];       
        
        $result = array_map(function($row) use($joins){
            if(count($joins) > 0){
                foreach ($row as $key => $value) {
                    $parts = explode("_", $key);                    
                    $prefix = array_shift($parts);

                    if (in_array($prefix , $joins)) {
                        $row[$prefix][join("_",$parts)] = $value;
                        unset($row[$key]);
                    }
                }
            }
                        $row['created_by'] = json_decode($row['created_by'], true);
                        return $row;
        },$stmt->fetchAll());
        
        return $result;
                
    }

    public function insert($tenant, $logged_user, Solicitacoes $entity) {
        $entity->setSintoma(\ForceUTF8\Encoding::toUTF8(\ForceUTF8\Encoding::toLatin1(\ForceUTF8\Encoding::toUTF8(StringUtils::nonBreakSpace($entity->getSintoma())))));
        $entity->setConteudoTextual(strip_tags(html_entity_decode($entity->getSintoma())));

        $configAssuntoHabilitado = $this->confService->get($tenant, 'ATENDIMENTO', 'HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS');
        $enviaEmailCriacao = $this->confService->get($tenant, 'ATENDIMENTO', 'ENVIA_EMAIL_CRIACAO_CHAMADO_ADMIN');

        if ($configAssuntoHabilitado == 0) {
            $entity->setResumo(StringUtils::geraResumo($entity->getSintoma()));
        }
        try {
            $this->getConnection()->beginTransaction();
            $inserted = parent::insert($tenant, $logged_user, $entity);
            $entity->setAtendimento($inserted['atendimento']);

            if ($entity->getCanal() === 'manual') {
// Caso o canal seja manual, precisamos vincular os anexos ao follow gerado e não ao atendimento
                $followup = $this->tndmntDmnFllwpsRpstry->findAll($tenant, $inserted['atendimento']);

                if (is_array($followup) && count($followup) > 0) {
                    $anexoVinculoId = array_key_exists('followup', $followup[0]) ? $followup[0]['followup'] : null;
                    $anexoVinculoTipo = UploadService::ANEXO_MODULO_FOLLOWUP;
                }
            } else {
                $anexoVinculoId = $inserted['atendimento'];
                $anexoVinculoTipo = UploadService::ANEXO_MODULO_ATENDIMENTO;
            }

            foreach ($entity->getAnexos()->toArray() as $anexo) {
                $anexoModulo = new Anexosmodulos();
                $anexoModulo->setDocumentoged($anexo->getDocumentoged());
                $anexoModulo->setNome($anexo->getNome());
                $anexoModulo->setCompartilhado(false);

                $this->uploadService->insert($anexoVinculoTipo, $anexoVinculoId, $tenant, $logged_user, $anexoModulo);
            }

            $this->getConnection()->commit();

            //Envia para a fila de Chamados_Slas
            $this->chamadosSlasProducer->publish(
              json_encode([
                  'tenant'=> $tenant, 
                  'atendimento'=> $inserted['atendimento']
              ])
            );

            if ($enviaEmailCriacao) {
                if ($inserted['visivelparacliente']) {
                    $eventArgs = [];
                    $eventArgs["tenant"] = $tenant;
                    $this->eventDispatcher->dispatch(Event::ATENDIMENTO_ADMIN_CREATE_TYPE, new GenericEvent($inserted['atendimento'], $eventArgs));
                }

                if ($inserted['responsavel_web_tipo'] == 1) {
                    $this->emailsproducer->publish(
                        json_encode([
                            'tenant'=> $tenant,
                            'atendimento'=> $inserted['atendimento'],
                            'responsavel' => $inserted['responsavel_web'],
                            'responsavel_tipo' => $inserted['responsavel_web_tipo'],
                            'tecnico_admin_criando_chamado' => true,
                        ])
                    );
                } else if ($inserted['responsavel_web_tipo'] == 2) {
                    $this->chamadosRegrasProducer->publish(
                        json_encode([
                            'tenant'=> $tenant, 
                            'atendimento'=> $entity->getAtendimento()
                        ])
                      );
                }
            }
            

            return $inserted;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }
    
    public function findQuery($id, $tenant) {
        $timezone = $this->confService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
        $timezone = $timezone ?: "America/Sao_Paulo";

        $sql = "SELECT
                t0_.atendimento as \"atendimento\" ,
                t0_.numeroprotocolo as \"numeroprotocolo\" ,
                t0_.situacao as \"situacao\" ,
                t0_.email as \"email\" ,
                t0_.responsavel_web as \"responsavel_web\" ,
                t0_.responsavel_web_tipo as \"responsavel_web_tipo\" ,
                t0_.sintoma as \"sintoma\" ,
                t0_.resumo_admin as \"resumo\" ,
                t0_.camposcustomizados as \"camposcustomizados\" ,
                t0_.ativo as \"ativo\" ,
                t0_.data_ultima_resposta AT TIME ZONE :timezone as \"data_ultima_resposta\" ,
                t0_.data_ultima_resposta_admin AT TIME ZONE :timezone as \"data_ultima_resposta_admin\" ,
                t0_.ultima_resposta_admin as \"ultima_resposta_admin\" ,
                t0_.ultima_resposta_resumo as \"ultima_resposta_resumo\" ,
                t0_.canal as \"canal\" ,
                t0_.canal_email as \"canal_email\" ,
                t0_.updated_at AT TIME ZONE :timezone as \"updated_at\" ,
                t0_.updated_by as \"updated_by\" ,
                t0_.datacriacao AT TIME ZONE :timezone as \"created_at\" ,
                t0_.created_by as \"created_by\" ,
                t0_.lastupdate as \"lastupdate\" ,
                t0_.tenant as \"tenant\" ,
                t0_.visivelparacliente as \"visivelparacliente\" ,
                t0_.adiado as \"adiado\" ,
                t0_.data_adiamento AT TIME ZONE :timezone as \"data_adiamento\" ,
                t0_.sla as \"sla\",
                t0_.proximaviolacaosla as \"proximaviolacaosla\" ,
                t0_.qtd_respostas as \"qtd_respostas\",
                t0_.mesclado_a as \"mesclado_a\",
                t0_.participante as \"cliente\",
                mesclado.numeroprotocolo as \"mesclado_a_numeroprotocolo\",
                t2_.id as \"t2_cliente\" ,
                t2_.nome as \"t2_nome\" ,
                t2_.nomefantasia as \"t2_nomefantasia\" ,
                t2_.codigo as \"t2_codigo\" ,
                t2_.cnpj as \"t2_cnpj\" ,
                t2_.cpf as \"t2_cpf\" ,
                t4_.nome as \"vendedor\",
                t1_.atendimentofila as \"t1_atendimentofila\" ,
                t1_.nome as \"t1_nome\" ,
                t1_.ordem as \"t1_ordem\",
                t2_.bloqueado as \"bloqueado\",
                t3_.nome as \"slanome\",
                t3_.tempo_primeiro_ativo as \"tempo_primeiro_ativo\",
                t3_.tempo_primeiro as \"tempo_primeiro\",
                t3_.tempo_proximo_ativo as \"tempo_proximo_ativo\",
                t3_.tempo_proximo as \"tempo_proximo\",
                t3_.tempo_resolucao_ativo as \"tempo_resolucao_ativo\",
                t3_.tempo_resolucao as \"tempo_resolucao\"
                FROM servicos.atendimentos t0_
                LEFT JOIN ns.vwclientes_atendimento t2_ ON t0_.participante = t2_.id
                LEFT JOIN servicos.atendimentosfilas t1_ ON t0_.atendimentofila = t1_.atendimentofila
                LEFT JOIN atendimento.slas t3_ ON t0_.sla = t3_.sla
                LEFT JOIN ns.vwvendedores_atendimento t4_ ON t2_.vendedor = t4_.vendedor
                LEFT JOIN servicos.atendimentos mesclado ON t0_.mesclado_a = mesclado.atendimento
                WHERE t0_.atendimento = :id AND t0_.tenant = :tenant";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $id, 
            'timezone' => $timezone,
            'tenant' => $tenant
        ])->fetch();

    }

    public function findQueryV2($id, $config, $tenant) {
        $timezone = $this->confService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
        $timezone = $timezone ?: "America/Sao_Paulo";
        
        $sql = "SELECT
                t0_.atendimento as \"atendimento\" ,
                t0_.numeroprotocolo as \"numeroprotocolo\" ,
                t0_.situacao as \"situacao\" ,
                t0_.email as \"email\" ,
                t0_.responsavel_web as \"responsavel_web\" ,
                t0_.responsavel_web_tipo as \"responsavel_web_tipo\" ,
                t0_.sintoma as \"sintoma\" ,
                t0_.resumo_admin as \"resumo\" ,
                t0_.camposcustomizados as \"camposcustomizados\" ,
                t0_.ativo as \"ativo\" ,
                t0_.data_ultima_resposta AT TIME ZONE :timezone as \"data_ultima_resposta\" ,
                t0_.data_ultima_resposta_admin AT TIME ZONE :timezone as \"data_ultima_resposta_admin\" ,
                t0_.ultima_resposta_admin as \"ultima_resposta_admin\" ,
                t0_.ultima_resposta_resumo as \"ultima_resposta_resumo\" ,
                t0_.canal as \"canal\" ,
                t0_.canal_email as \"canal_email\" ,
                t0_.updated_at AT TIME ZONE :timezone as \"updated_at\" ,
                t0_.updated_by as \"updated_by\" ,
                t0_.datacriacao AT TIME ZONE :timezone as \"created_at\" ,
                t0_.created_by as \"created_by\" ,
                t0_.lastupdate as \"lastupdate\" ,
                t0_.tenant as \"tenant\" ,
                t0_.visivelparacliente as \"visivelparacliente\" ,
                t0_.adiado as \"adiado\" ,
                t0_.data_adiamento AT TIME ZONE :timezone as \"data_adiamento\" ,
                t0_.sla as \"sla\",
                t0_.proximaviolacaosla as \"proximaviolacaosla\" ,
                t0_.qtd_respostas as \"qtd_respostas\",
                t0_.mesclado_a as \"mesclado_a\",
                t0_.participante as \"cliente\",
                mesclado.numeroprotocolo as \"mesclado_a_numeroprotocolo\",
                t2_.id as \"t2_cliente\" ,
                t2_.nome as \"t2_nome\" ,
                t2_.nomefantasia as \"t2_nomefantasia\" ,
                t2_.codigo as \"t2_codigo\" ,
                t2_.cnpj as \"t2_cnpj\" ,
                t2_.cpf as \"t2_cpf\" ,
                t4_.nome as \"vendedor\",
                t1_.atendimentofila as \"t1_atendimentofila\" ,
                t1_.nome as \"t1_nome\" ,
                t1_.ordem as \"t1_ordem\",
                t2_.bloqueado as \"bloqueado\",
                t3_.nome as \"slanome\",
                t3_.tempo_primeiro_ativo as \"tempo_primeiro_ativo\",
                t3_.tempo_primeiro as \"tempo_primeiro\",
                t3_.tempo_proximo_ativo as \"tempo_proximo_ativo\",
                t3_.tempo_proximo as \"tempo_proximo\",
                t3_.tempo_resolucao_ativo as \"tempo_resolucao_ativo\",
                t3_.tempo_resolucao as \"tempo_resolucao\"
                FROM servicos.atendimentos t0_
                LEFT JOIN ns.vwclientes_atendimento_v2 t2_ ON t0_.participante = t2_.id
                LEFT JOIN servicos.atendimentosfilas t1_ ON t0_.atendimentofila = t1_.atendimentofila
                LEFT JOIN atendimento.slas t3_ ON t0_.sla = t3_.sla
                LEFT JOIN ns.vwvendedores_atendimento t4_ ON t2_.vendedor = t4_.vendedor
                LEFT JOIN servicos.atendimentos mesclado ON t0_.mesclado_a = mesclado.atendimento
                WHERE t0_.atendimento = :id AND t0_.tenant = :tenant
               ";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'timezone' => $timezone,
            'tenant' => $tenant
        ])->fetch();
    }

    public function find($id, $tenant) {
        $config = $this->confService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

        $data = null;

        if (empty($config)) {

            $data = $this->findQuery($id, $tenant);
        } else {
            $data = $this->findQueryV2($id, $config, $tenant);
        }

        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }

        $data['camposcustomizados'] = json_decode($data['camposcustomizados'], true);
        $data['updated_by'] = json_decode($data['updated_by'], true);
        $data['created_by'] = json_decode($data['created_by'], true);

        foreach ($this->getLinks() as $link) {
            $newArr = [];
            foreach ($data as $subKey => $value) {
                if (substr($subKey, 0, strlen($link['alias'])) === $link['alias']) {
                    $newArr[str_replace($link['alias'], "", $subKey)] = $value;
                    unset($data[$subKey]);
                }
            }
            if (is_null($newArr[$link['identifier']])) {
                $data[$link['field']] = null;
            } else {
                $data[$link['field']] = $newArr;
            }
        }

        $data['followups'] = $this->tndmntDmnFllwpsRpstry->findAll($tenant, $id);
        $data['proximoscontatos'] = $this->crmPrxmscnttsRpstry->findAll($tenant, null, null, null, null, null, $id, null);
        $data['historico'] = $this->srvcsTndmntsHstrcRpstry->findAll($tenant, $id);
        $data['chamadosmesclados'] = $this->getChamadosMesclados($tenant, $id);
        if($data['sla']){
            try {
                $data['sla'] = $this->slasRepo->find($data['sla'], $tenant);
            }
            catch (NoResultException $e) {
                $data['sla'] = null;
            }
        }else{
          $data['sla'] = null;
        }

        $data['historico'] = array_map(function($historico) use($data) {
            if ($historico['tipo'] == 6) {
                foreach ($data['followups'] as $followup) {
                    if ($followup['followup'] === $historico['valornovo']['followup']) {
                        $historico['valornovo']['followup'] = $followup;
                        if(isset($historico['valornovo']['situacao'])){
                          $historico['valornovo']['situacao'] = $historico['valornovo']['situacao'];
                        }
                        break;
                    }
                }
            }

            return $historico;
        }, $data['historico']);

//        unset($data['followups']);

        $data['anexos'] = $this->uploadService->findAll($data['tenant'], UploadService::ANEXO_MODULO_ATENDIMENTO, $data['atendimento']);

        try {
            if ($this->tokenStorage->getToken()) {
                $this->obsRepo->getUsuarioObservador($data['tenant'], $data['atendimento'], $this->tokenStorage->getToken()->getUsername());
                $data['usuario_observando'] = true;
            }
        } catch (NoResultException $ex) {
            $data['usuario_observando'] = false;
        }


        if ($data['responsavel_web_tipo'] == 2) {
            try {
                $fila = $this->filasRepository->find($data['responsavel_web'], $tenant);
                $data['atribuido_a'] = ['label' => $fila['nome'], 'value' => $fila['atendimentofila']];
            } catch (NoResultException $e) {
                $data['atribuido_a'] = null;
            }
        } else if ($data['responsavel_web_tipo'] == 1) {
            $data['atribuido_a'] = ['label' => $data['responsavel_web'], 'value' => $data['responsavel_web']];
        } else {
            $data['atribuido_a'] = null;
        }

        return $data;
    }

    public function findAllQueryBuilderV2($tenant, $situacao= "", $responsavel_web= "", $cliente= "", $created_at= "", $camposcustomizados= "", $visivelparacliente= "", $canal= "", $adiado= "", $orderfield= "", $qtd_respostas= "", $ultima_resposta_admin= "",$created_at_ini= "", $created_at_fim= "",  Filter $filter = null, $config){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
                                't0_.atendimento as atendimento',
                                't0_.numeroprotocolo as numeroprotocolo',
                                't0_.resumo_admin as resumo',
                                't0_.situacao as situacao',
                                't0_.visivelparacliente as visivelparacliente',
                                't0_.canal as canal',
                                't0_.datacriacao as created_at',
                                't0_.created_by as created_by',
                                't0_.responsavel_web as responsavel_web',
                                't0_.ativo as ativo',
                                't0_.data_ultima_resposta as data_ultima_resposta',
                                't0_.data_ultima_resposta_admin as data_ultima_resposta_admin',
                                't0_.data_ultima_resposta_cliente as data_ultima_resposta_cliente',
                                't0_.ultima_resposta_admin as ultima_resposta_admin',
                                't0_.ultima_resposta_resumo as ultima_resposta_resumo',
                                't0_.adiado as adiado',
                                't0_.data_adiamento as data_adiamento',
                                't0_.data_abertura as data_abertura',
                                't0_.proximaviolacaosla as proximaviolacaosla',
                                't0_.mesclado_a as mesclado_a',
                    ));
        $queryBuilder->from('servicos.atendimentos', 't0_');
        $queryBuilder->leftJoin('t0_', 'ns.vwclientes_atendimento_v2', 't1_', 't0_.participante = t1_.id'); 
        $queryBuilder->addSelect(array(
                                't1_.id as cliente_cliente',
                                't1_.nome as cliente_nome',
                                't1_.nomefantasia as cliente_nomefantasia',
                                't1_.codigo as cliente_codigo',
                                't1_.cnpj as cliente_cnpj',
                                't1_.cpf as cliente_cpf',
                            ));    
                            
        $binds = $this->findAllQueryBuilderBodyV2($queryBuilder,$tenant, $situacao, $responsavel_web, $cliente, $created_at, $camposcustomizados, $visivelparacliente, $canal, $adiado, $orderfield, $qtd_respostas, $ultima_resposta_admin, $created_at_ini= "", $created_at_fim= "",  $filter, $config);

        return [$queryBuilder, $binds];
    }

    public function findAllQueryBuilderBodyV2(&$queryBuilder, $tenant, $situacao= "", $responsavel_web= "", $cliente= "", $created_at= "", $camposcustomizados= "", $visivelparacliente= "", $canal= "", $adiado= "", $orderfield= "", $qtd_respostas= "", $ultima_resposta_admin= "", $created_at_ini= "", $created_at_fim= "",  Filter $filter = null, $config){
        $binds = [];
        $where = [];
        
        $queryBuilder->addOrderBy("t0_.data_ultima_resposta", "desc");
        
        $queryBuilder->setMaxResults(20);
            
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

        if(strlen($situacao) > 0){
            $where[] = $queryBuilder->expr()->eq("t0_.situacao", "?");
            $binds[] = $situacao;
        } 
             
        if(strlen($responsavel_web) > 0){             
            $where[] = $queryBuilder->expr()->eq("t0_.responsavel_web", "?");
            $binds[] = $responsavel_web;
        } 
        
        if(strlen($cliente) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.participante", "?");
            $binds[] = $cliente;

        } 

        if(strlen($created_at) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.datacriacao", "?");
            $binds[] = $created_at;

        } 

        if(strlen($camposcustomizados) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.camposcustomizados", "?");
            $binds[] = $camposcustomizados;

        } 

        if(strlen($visivelparacliente) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.visivelparacliente", "?");
            $binds[] = $visivelparacliente;

        } 

        if(strlen($canal) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.canal", "?");
            $binds[] = $canal;

        } 

        if(strlen($adiado) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.adiado", "?");
            $binds[] = $adiado;

        } 

        if(strlen($orderfield) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.orderfield", "?");
            $binds[] = $orderfield;

        } 

        if(strlen($qtd_respostas) > 0){ 

            $where[] = $queryBuilder->expr()->eq("t0_.qtd_respostas", "?");
            $binds[] = $qtd_respostas;

        } 

        if(strlen($ultima_resposta_admin) > 0){ 
            $where[] = $queryBuilder->expr()->eq("t0_.ultima_resposta_admin", "?");
            $binds[] = $ultima_resposta_admin;

        } 
                
        list($offsets, $offsetsBinds) = $this->proccessOffset($filter);
        $where = array_merge($where, $offsets);
        $binds = array_merge($binds, $offsetsBinds);
        
        list($filters, $filtersBinds) = $this->proccessFilter($filter);
        $binds = array_merge($binds, $filtersBinds);

        if(!empty($where)){
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }
        if(!empty($filters)){
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }

        $grupos_empresariais = explode(",", trim($config));

        $size = -1;

        if(($size = sizeof($grupos_empresariais)) > 0){
            $strWhere = '';

            for ($i = 0; $i < count($grupos_empresariais) - 1; $i++) {
            $guid = $grupos_empresariais[$i];

                if(!empty($guid) && StringUtils::isGuid($guid)) {
                    $strWhere .= "t1_.grupoempresarial = '".$guid."' OR ";
                }
            }
            
            if(!empty($grupos_empresariais[$size - 1]) && StringUtils::isGuid($grupos_empresariais[$size - 1])) {
                $strWhere .= "t1_.grupoempresarial = '".$grupos_empresariais[$size - 1]."'";

                $queryBuilder->andWhere($strWhere);
            }
        }
        
        return $binds;
    }

    public function findAllQueryBuilder($tenant, $situacao = '', $responsavel_web = '', $cliente = '', $created_at = '', $camposcustomizados = '', $visivelparacliente = '', $canal = '', $adiado = '', $orderfield = '', $qtd_respostas = '', $ultima_resposta_admin = '', $created_at_ini= "", $created_at_fim= "", Filter $filter = null) {
        if (!empty($filter) && empty($filter->getOrder())) {
            $filter->setOrder(Criteria::DESC);
        }
        if($orderfield){
          $order = $filter ? $filter->getOrder() : Criteria::DESC ;
          $this->setOffsets([
            $orderfield => [ "column" => $orderfield, "direction" => $order],
          ]);
        }
        
        $config = $this->confService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

        if (empty($config)) {
            list($queryBuilder, $binds) = $this->findAllQueryBuilderV1($tenant, "", "", $cliente, "", "", "", "", "", "", "", "", "", "", $filter);
        } else {
            list($queryBuilder, $binds) = $this->findAllQueryBuilderV2($tenant, "", "", $cliente, "", "", "", "", "", "", "", "", "", "", $filter, $config);
        }

        //Os casos onde a situação é nula são:
        //Todos os chamados e Sem atribuição
        //Nestes casos removeremos os SPAMs e deixaremos os mesmos na aba criada especificamente para isto.
        //Obs: Não foi definida a clausula ELSE pura porque ao buscar um chamado onde não existirá o filtro de situação o mesmo não deve ser adicionado no WHERE.
        if ($situacao === null) {
            $where[] = $queryBuilder->andWhere($queryBuilder->expr()->neq("t0_.situacao", "?"));
            $binds[] = "2";//SPAM
        } else if ($situacao === '0' || $situacao === '1' || $situacao === '2') {
            $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.situacao", "?"));
            $binds[] = $situacao;
        }
        //Verifica se $responsavel_web é uma string ou array para que o filtro seja feito corretamente.
        if (gettype($responsavel_web) === 'string') {
            if (strlen($responsavel_web) > 0) {
                if ($responsavel_web == "ninguem") {
                    $where[] = $queryBuilder->andWhere($queryBuilder->expr()->isNull("t0_.responsavel_web"));
                } else {
                    $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.responsavel_web", "?"));
                    $binds[] = strtolower($responsavel_web) === "mim" ? $this->tokenStorage->getToken()->getUsername() : $responsavel_web;
                }
                
                if ($responsavel_web === 'mim') {
                    $orX = $queryBuilder->expr()->orX();                
                    if ($adiado === 'true') {
                      //Fila de adiados
                      $orX->add("t0_.data_adiamento > now() AND t0_.adiado = TRUE");
                    } else {
                      //Fila de meus chamados
                      $orX->add("((t0_.data_adiamento < now() AND t0_.adiado = TRUE) OR (t0_.adiado = FALSE))");
                    }
                    
                    $where[] = $queryBuilder->andWhere($orX);
                }
                
            }
        } else {
            if (!empty($responsavel_web)) {
                $orX = $queryBuilder->expr()->orX();
                foreach ($responsavel_web as $value) {
                    $orX->add("t0_.responsavel_web = '" . $value . "'");
                }

                $where[] = $queryBuilder->andWhere($orX);
            }
        }
        
        if (gettype($qtd_respostas) === 'string') {
            if (strlen($qtd_respostas) > 0) {
                $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.qtd_respostas", "'" . $qtd_respostas . "'"));
            }
        } else {
            if (!empty($qtd_respostas)) {
                $orX = $queryBuilder->expr()->orX();
                foreach ($qtd_respostas as $value) {
                    $orX->add("t0_.qtd_respostas = '" . $value . "'");
                }

                $where[] = $queryBuilder->andWhere($orX);
            }
        }
        
        if (gettype($ultima_resposta_admin) === 'string') {
            if (strlen($ultima_resposta_admin) > 0) {
                $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.ultima_resposta_admin", $ultima_resposta_admin ));
            }
        } else {
            if (!empty($ultima_resposta_admin)) {
                $orX = $queryBuilder->expr()->orX();
                foreach ($ultima_resposta_admin as $value) {
                    $orX->add("t0_.ultima_resposta_admin = " . $value . " ");
                }

                $where[] = $queryBuilder->andWhere($orX);
            }
        }
        
        if ((gettype($created_at_ini) === 'string') && gettype($created_at_fim) === 'string') {
            if ((strlen($created_at_ini) > 0) && (strlen($created_at_fim) > 0)) {
                $created_at_ini = $created_at_ini . " 00:00:00";
                $created_at_fim = $created_at_fim . " 23:59:59";
                $where[] = $queryBuilder->andWhere("t0_.datacriacao::date BETWEEN ". "'" . $created_at_ini. "'" . "and". "'". $created_at_fim ."'");
            }
        }
         else {
            if (!empty($created_at_ini && $created_at_fim)) {
                $created_at_ini = $created_at_ini[0] . " 00:00:00";
                $created_at_fim = $created_at_fim[0] . " 23:59:59";
                $where[] = $queryBuilder->andWhere("t0_.datacriacao::date BETWEEN ". "'" . $created_at_ini. "'" . "and". "'". $created_at_fim ."'");
            }
            }

        if (!empty($camposcustomizados)) {
            $orX = $queryBuilder->expr()->orX();

            //coleta todos os guids de todos os filtros feitos.
            $guids = array_map(function ($item) {
                return $item['campocustomizado'];
            }, $camposcustomizados);

            //cria um array de todos os guids sem repetições
            $uniqueGuids = array_unique($guids);
            //conta quantos filtros foram feitos em cada campo customizado.
            $guid_count_values = array_count_values($guids);

            $in = null;
            $ex = null;

            //alimenta as variáveis in e ex com os valores que são inclusivos e exclusivos
            foreach ($uniqueGuids as $guid) {
                if ($guid_count_values[$guid] > 1) {
                    $in[] = array_filter($camposcustomizados, function($value) use ($guid) {
                        return $value['campocustomizado'] === $guid;
                    });
                } else {
                    $ex[] = array_filter($camposcustomizados, function($value) use ($guid, $camposcustomizados) {
                        return $value['campocustomizado'] === $guid;
                    });

                    unset($uniqueGuids[array_search($guid, $uniqueGuids)]);
                }
            }

            if (!empty($ex)) {
                foreach ($ex as $exclusivos) {
                    foreach ($exclusivos as $e) {
                      $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.camposcustomizados->>'" . $e['campocustomizado'] . "'", "'" . $e['opcao'] . "'"));
                    }
                }
            }

            if (!empty($in)) {
                foreach ($uniqueGuids as $guid) {
                    foreach ($in as $inclusive) {
                        $filtered = array_filter($inclusive, function ($i) use ($guid_count_values, $guid) {
                            return $guid_count_values[$i['campocustomizado']] > 1 && $i['campocustomizado'] === $guid;
                        });

                        foreach ($filtered as $f) {
                            $orX->add("t0_.camposcustomizados->>'" . $f['campocustomizado'] . "' = '" . $f['opcao'] . "'");
                        }

                        $where[] = $queryBuilder->andWhere($orX);

                        $filtered = null;
                    }
                }
            }
        }

        if (gettype($canal) === 'string') {
            if (strlen($canal) > 0) {
                $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.canal", "?"));
                $binds[] = $canal;

            }
        } else {
            if (!empty($canal)) {
                $orX = $queryBuilder->expr()->orX();
                foreach ($canal as $value) {
                    $orX->add("t0_.canal = '" . $value . "'");
                }

                $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.canal","?"));
                $binds[] = $value;
            }
        }

        if (gettype($visivelparacliente) === 'string') {
            if (strlen($visivelparacliente) > 0) {
                $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.visivelparacliente", "?"));
                $binds[] = $visivelparacliente;
            }
        } else {
            if (!empty($visivelparacliente)) {
                $orX = $queryBuilder->expr()->orX();
                foreach ($visivelparacliente as $value) {
                    $orX->add("t0_.visivelparacliente = '" . $value . "'");
                }

                $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.visivelparacliente", "?"));
                $binds[] = $value;
            }
        }
        
        if (gettype($adiado)==='string'){
            if (strlen($adiado) > 0){
                if ($adiado === 'true') {
                    $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.adiado", "?"));
                    $binds[] = $adiado;
                    $where[] = $queryBuilder->andWhere($queryBuilder->expr()->gt("t0_.data_adiamento", "now()"));
                }else{
                    $orX = $queryBuilder->expr()->orX();
                    $orX->add("((t0_.data_adiamento < now() AND t0_.adiado = TRUE) OR (t0_.adiado = FALSE))");
                    $where[] = $queryBuilder->andWhere($orX);
                }
            }
        }else{
            if (!empty($adiado)) {
                foreach ($adiado as $value) {
                    if ($value === 'true') {
                        $where[] = $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.adiado", "?"));
                        $binds[] = $value;
                        $where[] = $queryBuilder->andWhere($queryBuilder->expr()->gt("t0_.data_adiamento", "now()"));
                    }else{
                        $orX = $queryBuilder->expr()->orX();
                        $orX->add("((t0_.data_adiamento < now() AND t0_.adiado = TRUE) OR (t0_.adiado = FALSE))");
                        $where[] = $queryBuilder->andWhere($orX);
                    }
                }
            }
        }
        
        // Não exibe chamados mesclados
        $where[] = $queryBuilder->andWhere($queryBuilder->expr()->isNull('t0_.mesclado_a'));

        $queryBuilder->addSelect("af.nome as responsavel_web_nome");
        $queryBuilder->leftJoin('t0_', 'servicos.atendimentosfilas', 'af', 't0_.responsavel_web_tipo = 2 AND t0_.responsavel_web = af.atendimentofila::text');

        if ($orderfield) {
            $queryBuilder->resetQueryPart('orderBy');
            $queryBuilder->orderBy("t0_.".$orderfield, $filter ? $filter->getOrder() : Criteria::DESC);
        }

        return [$queryBuilder, $binds];
    }

    //Incluida essa função para ganhos de perfomance
    public function findAllQueryBuilderV1($tenant, $situacao= "", $responsavel_web= "", $cliente= "", $created_at= "", $camposcustomizados= "", $visivelparacliente= "", $canal= "", $adiado= "", $orderfield= "", $qtd_respostas= "", $ultima_resposta_admin= "", $created_at_ini= "", $created_at_fim= "",  Filter $filter = null){

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
                                't0_.atendimento as atendimento',
                                't0_.numeroprotocolo as numeroprotocolo',
                                't0_.resumo_admin as resumo',
                                't0_.situacao as situacao',
                                't0_.visivelparacliente as visivelparacliente',
                                't0_.canal as canal',
                                't0_.datacriacao as created_at',
                                't0_.created_by as created_by',
                                't0_.responsavel_web as responsavel_web',
                                't0_.ativo as ativo',
                                't0_.data_ultima_resposta as data_ultima_resposta',
                                't0_.data_ultima_resposta_admin as data_ultima_resposta_admin',
                                't0_.data_ultima_resposta_cliente as data_ultima_resposta_cliente',
                                't0_.ultima_resposta_admin as ultima_resposta_admin',
                                't0_.ultima_resposta_resumo as ultima_resposta_resumo',
                                't0_.adiado as adiado',
                                't0_.data_adiamento as data_adiamento',
                                't0_.data_abertura as data_abertura',
                                't0_.proximaviolacaosla as proximaviolacaosla',
                                't0_.mesclado_a as mesclado_a',
                    ));
        $queryBuilder->from('servicos.atendimentos', 't0_');
                                        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't1_', 't0_.participante = t1_.id AND t0_.tenant = t1_.tenant'); 
            $queryBuilder->addSelect(array(
                                    't1_.id as cliente_cliente',
                                    't1_.nome as cliente_nome',
                                    't1_.nomefantasia as cliente_nomefantasia',
                                    't1_.pessoa as cliente_codigo',
                                    "regexp_replace((t1_.cnpj)::text, '[^0-9]'::text, ''::text, 'g'::text) as cliente_cnpj",
                                    "regexp_replace((t1_.cpf)::text, '[^0-9]'::text, ''::text, 'g'::text) as cliente_cpf",
                                    "CASE WHEN ((t1_.tipoclientepagamento = 1) AND (t1_.situacaopagamento = 1)) THEN 'Ativo'::text ELSE 'Bloqueado'::text END AS cliente_status_suporte",
                            ));    
                            
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $situacao, $responsavel_web, $cliente, $created_at, $camposcustomizados, $visivelparacliente, $canal, $adiado, $orderfield, $qtd_respostas, $ultima_resposta_admin, $created_at_ini, $created_at_fim,  $filter);
        

        return [$queryBuilder, $binds];
    }

    public function abertos($tenant) {

        $queryBuilder = $this->getConnection()->createQueryBuilder();

        $config = $this->confService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

        $grupos_empresariais = explode(",", trim($config));

        $size = -1;

        if(($size = sizeof($grupos_empresariais)) > 0){
            $strWhere = '';

            for ($i = 0; $i < count($grupos_empresariais) - 1; $i++) {
            $guid = $grupos_empresariais[$i];

                if(!empty($guid) && StringUtils::isGuid($guid)) {
                    $strWhere .= "ca.grupoempresarial = '".$guid."' OR ";
                }
            }
            
            if(!empty($grupos_empresariais[$size - 1]) && StringUtils::isGuid($grupos_empresariais[$size - 1])) {
                $strWhere .= "ca.grupoempresarial = '".$grupos_empresariais[$size - 1]."'";

                //$queryBuilder->andWhere($strWhere);
            }
        }

        $where = [
            $queryBuilder->expr()->eq('a.tenant', ':tenant'),
            $queryBuilder->expr()->eq('a.situacao', '0'),
            $strWhere
        ];
        $where[] = $this->equipeFilter->run('ca');

        if (empty($config)) {
            $queryBuilder
                    ->select('count(*) as qtd', 'a.responsavel_web as fila', 'a.responsavel_web_tipo as tipo, CASE WHEN adiado = TRUE AND data_adiamento > now() THEN TRUE ELSE FALSE END as adiado')
                    ->from("servicos.atendimentos", "a")
                    ->leftJoin('a', 'ns.vwclientes_atendimento', 'ca', 'ca.id = a.participante')
                    ->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where))
                    ->groupBy('a.responsavel_web, a.responsavel_web_tipo, data_adiamento, adiado');
        } else {
            
            $queryBuilder
                    ->select('count(*) as qtd', 'a.responsavel_web as fila', 'a.responsavel_web_tipo as tipo, CASE WHEN adiado = TRUE AND data_adiamento > now() THEN TRUE ELSE FALSE END as adiado')
                    ->from("servicos.atendimentos", "a")
                    ->leftJoin('a', 'ns.vwclientes_atendimento_v2', 'ca', 'ca.id = a.participante')
                    ->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where))
                    ->groupBy('a.responsavel_web, a.responsavel_web_tipo, data_adiamento, adiado');
        }

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());

        $stmt->execute([
            "tenant" => $tenant
        ]);

        return $stmt->fetchAll();
    }

    public function proccessOffset(Filter $filter = null) {
        $where = [];
        $binds = [];
        if (!is_null($filter) && !empty($filter->getOffset())) {
            $queryBuilder = $this->getConnection()->createQueryBuilder();

            $offsetField = array_values($this->getOffsets())[0];

            switch (strtoupper($filter->getOrder())) {
                case Criteria::ASC:
                    $where[] = $queryBuilder->expr()->gt("t0_." . $offsetField['column'], "?");
                    break;
                case Criteria::DESC:
                    $where[] = $queryBuilder->expr()->lt("t0_." . $offsetField['column'], "?");
                    break;
                case 'WAIT':
                    $where[] = $queryBuilder->expr()->gt("t0_.data_ultima_resposta_admin", "?");
                    break;
            }
            $binds[] = $filter->getOffset();
        }

        $where[] = $this->equipeFilter->run('t1_');

        return [$where, $binds];
    }
    
    public function verificarFilaUsuarioExistente(\Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes $entity, $tenant) {
      if (StringUtils::isGuid($entity->getResponsavelWeb())) {
        $fila = $this->filasRepository->exists($entity->getResponsavelWeb(), $tenant);
        return $fila === 0 ? false : true;
      } else {
        try {
          $usuario = $this->diretorio->getProfile($entity->getResponsavelWeb());
          return empty($usuario) ? false : true;
        } catch (Exception $ex) {
          return false;
        }
      }
    }

    /**
     * @param string                                                   $logged_user
     * @param string                                                   $tenant
     * @param \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes $entity
     *
     * @return string
     *
     * @throws \Exception
     */
    public function alterarResponsavelWeb($logged_user, $tenant, \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes $entity) {
      //Se o responsavelWeb for um usuário ou Fila => VerificarSeExistente
      //OU Se o responsavelWeb for nulo => Atribua a vazio
      if ($this->verificarFilaUsuarioExistente($entity, $tenant) || empty($entity->getResponsavelWeb())) {
        $retorno = parent::alterarResponsavelWeb($logged_user, $tenant, $entity);
        
        
        $eventArgs = [];
        $eventArgs["tenant"] = $tenant;
        $this->eventDispatcher->dispatch(Event::ATENDIMENTO_ADMIN_ATRIBUICAO_UPDATE_TYPE, new GenericEvent($entity->getAtendimento(), $eventArgs));
        
        $this->chamadosSlasProducer->publish(
          json_encode([
              'tenant'=> $tenant, 
              'atendimento'=> $entity->getAtendimento()
          ])
        );

        $this->emailsproducer->publish(
            json_encode([
                'tenant'=> $tenant,
                'atendimento'=> $entity->getAtendimento(),
                'responsavel_web' => $entity->getResponsavelWeb(),
                'responsavel_tipo' => StringUtils::isGuid($entity->getResponsavelWeb()) ? 2 : 1,
                'tecnico_admin_atribuindo_a_uma_fila_ou_a_outro_tecnico' => true,
            ])
        );

        //Código desativado para refatoração.

        // Caso o responsável Web seja uma fila, dispara as regras.
        // if (StringUtils::isGuid($entity->getResponsavelWeb())) {
        //     $this->chamadosRegrasProducer->publish(
        //         json_encode([
        //             'tenant'=> $tenant, 
        //             'atendimento'=> $entity->getAtendimento()
        //         ])
        //       );

        // // Dispara para o envio de e-mails ao técnico a quem foi atribuído
        // } else {
        //     $this->emailsproducer->publish(
        //         json_encode([
        //             'tenant'=> $tenant,
        //             'atendimento'=> $entity->getAtendimento(),
        //             'responsavel_web' => $entity->getResponsavelWeb(),
        //             'responsavel_tipo' => 1,
        //             'atribuindo_chamado_ao_tecnico_admin' => true,
        //         ])
        //     );
        // }
        
        return $retorno;
      } else {
        throw new Exception('Não foi possível efetuar a atribuição: Fila/Usuário inexistente.');
      }
    }

    public function getUltimaFilaAtribuida($atendimento) {
        $sql = "SELECT valorantigo->>'responsavel' as atendimentofila
                FROM servicos.atendimentoshistoricos
                WHERE tipo = 4
                    and valorantigo->>'tipo' = '2'
                    AND atendimento = :atendimento
                ORDER BY created_at desc";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('atendimento', $atendimento->getAtendimento());
        $stmt->execute();
        $resposta = $stmt->fetchColumn();
        return $resposta;
    }
  
    public function getAtendimentoByProtocolo($protocolo, $tenant) {
        $stmt = $this->getConnection()->prepare("SELECT atendimento FROM servicos.atendimentos WHERE numeroprotocolo = :numeroprotocolo AND tenant = :tenant");
        $stmt->execute(["numeroprotocolo" => $protocolo,"tenant" => $tenant]);
        return $stmt->fetch();
    }
    
    public function alterarCampoCustomizado($logged_user,$tenant,  \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes $entity){
        $retorno = parent::alterarCampoCustomizado($logged_user, $tenant, $entity);
        
        $this->chamadosSlasProducer->publish(
          json_encode([
              'tenant'=> $tenant, 
              'atendimento'=> $entity->getAtendimento()
          ])
        );
        
        return $retorno;
    }
    
    public function alterarEmailContato($logged_user,$tenant,  \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes $entity){
        $retorno = parent::alterarEmailContato($logged_user, $tenant, $entity);
        $this->chamadosSlasProducer->publish(
          json_encode([
              'tenant'=> $tenant, 
              'atendimento'=> $entity->getAtendimento()
          ])
        );
        return $retorno;
    }
    
    public function alterarCliente($logged_user,$tenant,  \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes $entity){
         $retorno = parent::alterarCliente($logged_user, $tenant, $entity);
         $this->chamadosSlasProducer->publish(
            json_encode([
                'tenant'=> $tenant, 
                'atendimento'=> $entity->getAtendimento()
            ])
          );
        
         return $retorno;
    }
    
    public function adicionaHistorico($logged_user, $tenant, $atendimento, $tipo, $valornovo, $valorantigo){
      
      $this->getConnection()->beginTransaction();
      $sql_1 = "SELECT mensagem
      FROM servicos.api_AtendimentoHistoricoNovo(row(
          :atendimento,
          :tipo,
          :valornovo,
          :valorantigo,
          :created_by,
          :tenant
          )::servicos.tatendimentohistoriconovo
      );";
      
      $stmt_1 = $this->getConnection()->prepare($sql_1);
      
      $stmt_1->bindValue("atendimento", $atendimento['atendimento']);
      $stmt_1->bindValue('tipo', $tipo);
      $stmt_1->bindValue('valornovo', $valornovo);
      $stmt_1->bindValue('valorantigo', $valorantigo);
      $stmt_1->bindValue('created_by', json_encode($logged_user));
      $stmt_1->bindValue('tenant', $tenant);
      
      $stmt_1->execute();
      $this->getConnection()->commit();
      $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $atendimento);
      $retorno = $resposta;
      
      return $retorno;
    }

    public function getChamadosAbertosSemSla($tenant) {
        $stmt = $this->getConnection()->prepare("SELECT TENANT, ATENDIMENTO, DATACRIACAO, NUMEROPROTOCOLO 
                                                 FROM SERVICOS.ATENDIMENTOS 
                                                 WHERE TENANT = :tenant 
                                                   AND SITUACAO = 0 
                                                   AND SLA IS NULL");
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();                                                    
        
        return $stmt->fetchAll();
    }

    public function getChamadosSlasViolados() {
        $sql = " 
            SELECT atendimento, tenant FROM servicos.atendimentos
            WHERE situacao = 0 
            AND sla is not null
            AND proximaviolacaosla < current_timestamp
            AND ( ultimaviolacaosla IS NULL OR ultimaviolacaosla < proximaviolacaosla );
        ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getChamadosMesclados($tenant, $atendimento) {
        $sql = " 
            select atendimento, numeroprotocolo from servicos.atendimentos
            where tenant = :tenant and mesclado_a = :atendimento ;
        ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('tenant', $tenant);
        $stmt->bindValue('atendimento', $atendimento);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function mesclarChamados($chamadoDestino, $chamados, $logged_user, $tenant){
        $sql = "
        select mensagem from servicos.api_atendimentomesclarnovo(row(
            :chamadoDestino,
            :chamados,
            :tenant,
            :mesclado_por
        )::servicos.tatendimentomesclarnovo);
        " ;   
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('chamadoDestino', $chamadoDestino);
        $stmt->bindValue('chamados', $chamados);
        $stmt->bindValue('tenant', $tenant);
        $stmt->bindValue('mesclado_por', json_encode($logged_user));
        $stmt->execute();

        return $stmt->fetchAll();
    }
    
}

