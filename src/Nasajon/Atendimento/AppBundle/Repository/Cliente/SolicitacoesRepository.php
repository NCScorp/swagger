<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Nasajon\Atendimento\AppBundle\Repository\Cliente\FollowupsRepository;
use Nasajon\MDABundle\Repository\Servicos\AtendimentoscamposcustomizadosRepository;
use Nasajon\Atendimento\AppBundle\Service\UploadService;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Solicitacoes;
use Nasajon\MDABundle\Entity\Ns\Anexosmodulos;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\SolicitacoesRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
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
     * @var FollowupsRepository
     */
    private $followupsRepository;

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
     * @var AtendimentoscamposcustomizadosRepository
     */
    private $camposCustomizadosRepository;
    
    private $chamadosRegrasProducer;

    public function __construct(
            $connection, 
            UploadService $uploadService, 
            FollowupsRepository $followupsRepository,
            ConfiguracoesService $confService, 
            AtendimentoscamposcustomizadosRepository $camposCustomizadosRepo,
            Producer $chamadosregrasproducer,
            $eventDispatcher
            ) {
        parent::__construct($connection);

        $this->uploadService = $uploadService;
        $this->followupsRepository = $followupsRepository;
        $this->confService = $confService;
        $this->camposCustomizadosRepository = $camposCustomizadosRepo;
        $this->chamadosRegrasProducer = $chamadosregrasproducer;
        $this->eventDispatcher = $eventDispatcher;
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

    public function countAbertas($cliente, $tenant) {
        $sql = "SELECT COUNT(*) FROM servicos.vwatendimentos_cliente WHERE situacao = 0 and tenant = :tenant and participante = :participante";

        return $this->getConnection()->executeQuery($sql, [
                    'participante' => $cliente,
                    'tenant' => $tenant
                ])->fetchColumn();
    }

    public function countRespostasNovas($cliente, $tenant) {
        $sql = "SELECT COUNT(*)
                FROM ns.followups fu
                JOIN servicos.vwatendimentos_cliente a USING (atendimento)
                WHERE a.tenant = :tenant and a.participante = :participante ";

        return $this->getConnection()->executeQuery($sql, [
                    'participante' => $cliente,
                    'tenant' => $tenant
                ])->fetchColumn();
    }

    public function findAllQueryBuilder($tenant, $cliente= "", $email= "", $created_at= "", $created_at_ini = '', $created_at_fim = '', $camposcustomizados= "",  $responsavel_web="", $email_usuario_criacao = "", Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
                                'DISTINCT t0_.atendimento as atendimento',
                                't0_.numeroprotocolo as numeroprotocolo',
                                't0_.resumo as resumo',
                                't0_.datacriacao as created_at',
                                't0_.data_ultima_resposta as data_ultima_resposta',
                                't0_.ultima_resposta_admin as ultima_resposta_admin',
                                't0_.ultima_resposta_resumo as ultima_resposta_resumo',
                                't0_.mesclado_a'
                    ));
        $queryBuilder->from('servicos.vwatendimentos_cliente', 't0_');
                                        $queryBuilder->leftJoin('t0_', 'ns.vwclientes_atendimento', 't1_', 't0_.participante = t1_.id');
            $queryBuilder->addSelect(array(
                                    't1_.id as cliente_cliente',
                                    't1_.nome as cliente_nome',
                                    't1_.nomefantasia as cliente_nomefantasia',
                                    't1_.codigo as cliente_codigo',
                                    't1_.cnpj as cliente_cnpj',
                                    't1_.cpf as cliente_cpf',
                            ));

        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, null, $email = null, null, null, null, null, null, null, $filter);

        // Sobrescrita do Where da datacriacao
        if (is_string($created_at) && $created_at) {
            
            $queryBuilder->andWhere("t0_.datacriacao::date = ?");
            $binds[] = $created_at;
            
        } else if (is_array($created_at)) {

            $sqlDatePart = '';
            for ($i = 0; $i < count($created_at) - 1; $i++) {
                $sqlDatePart .= ' t0_.datacriacao::date = ? OR ';
                $binds[] = $created_at[$i];
            }

            $sqlDatePart .= 't0_.datacriacao::date = ?';
            $binds[] = $created_at[count($created_at) - 1];

            $queryBuilder->andWhere($sqlDatePart);
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

        if (is_string($responsavel_web) && $responsavel_web) {

            $queryBuilder->andWhere("t0_.created_by::text like ?");
            $responsavel_web = '%'.addcslashes($responsavel_web, '%_').'%';
            $binds[] = $responsavel_web;

        } 

        if (is_string($camposcustomizados) && strlen($camposcustomizados) > 0) {

            $obj = json_decode($camposcustomizados, true);
            $chave = null;
            $valor = null;

            foreach ($obj as $key => $value) {
                $chave = $key;
                $valor = '%'.$value.'%';
            }

            $queryBuilder->andWhere("t0_.camposcustomizados->>'$chave' ilike ?");
            $binds[] = $valor;

        } else if (is_array($camposcustomizados)) {

            $sqlDatePart = '';
            for ($i = 0; $i < count($camposcustomizados) - 1; $i++) {
                $obj = json_decode($camposcustomizados[$i], true);
                $chave = null;
                $valor = null;

                foreach ($obj as $key => $value) {
                    $chave = $key;
                    $valor = '%'.$value.'%';
                }

                $sqlDatePart .= "(t0_.camposcustomizados->>'$chave' ilike ?) OR ";
                $binds[] = $valor;
            }

            $obj = json_decode($camposcustomizados[count($camposcustomizados) - 1], true);
            $chave = null;
            $valor = null;

            foreach ($obj as $key => $value) {
                $chave = $key;
                $valor = '%'.$value.'%';
            }

            $sqlDatePart .= "(t0_.camposcustomizados->>'$chave' ilike ?)";
            $binds[] = $valor;

            $queryBuilder->andWhere($sqlDatePart);
        }

        if ($cliente === false) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNull("t0_.participante"));
            $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.email", "?"));
            $binds[] = $email;
        } elseif (is_array($cliente)) {

            if (!empty($cliente)) {

                if (is_null($cliente[count($cliente)-1])) {
                    $minhas = true;
                    unset($cliente[count($cliente)-1]);
                } else {
                    $minhas = false;
                }

                $clienteCond = $queryBuilder->expr()->in('t0_.participante', array_map(function($a) {
                            if (!is_null($a)) {
                                return "?";
                            }
                        }, $cliente));

                if ($minhas) {
                    $queryBuilder->andWhere($queryBuilder->expr()->orX($clienteCond, $queryBuilder->expr()->isNull("t0_.participante")), $queryBuilder->expr()->eq("t0_.email", "?"));
                    $binds = array_merge($binds, $cliente);
                    $binds[] = $email;
                } else {

                    $clienteCond2 = $queryBuilder->expr()->andX($queryBuilder->expr()->eq("t0_.email", "?"), $queryBuilder->expr()->isNull("t0_.participante"));
                    $queryBuilder->andWhere($queryBuilder->expr()->orX($clienteCond, $clienteCond2));
                    $binds = array_merge($binds, $cliente);
                    $binds[] = $email;
                }

            } else {
                $conta = $queryBuilder->expr()->andX($queryBuilder->expr()->isNull("participante"), $queryBuilder->expr()->eq("t0_.email", "?"));
                $queryBuilder->andWhere($conta);
                $binds[] = $email;
            }

        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("t0_.participante", "?"));
            $binds[] = $cliente;
        }

        
        if (!empty($filter)) {
            $queryBuilder->resetQueryPart('orderBy');
            
            switch (strtoupper($filter->getOrder())) {
                case null:
                    $queryBuilder->orderBy("t0_.data_ultima_resposta", "DESC");
                    break;
                case "CREATED_AT_DESC":
                    $queryBuilder->orderBy("t0_.datacriacao", "DESC");
                    break;
                case "CREATED_AT_ASC":
                    $queryBuilder->orderBy("t0_.datacriacao", "ASC");
                    break;
                default:
                    $queryBuilder->orderBy("t0_.data_ultima_resposta", "DESC");
                    break;
            }
        }

        // Verifica a configuração ACESSO_CHAMADOS_VISAO_CLIENTE para filtrar os chamados
        $acessoChamadoVisaoCliente = $this->confService->get($tenant, 'ATENDIMENTO', 'ACESSO_CHAMADOS_VISAO_CLIENTE');

        if ($acessoChamadoVisaoCliente == 1) {
            // É necessário fazer o join com clientesfuncoes para verificar se o usuário logado é admnistrador ou usuário de algum cliente
            $queryBuilder->innerJoin('t0_', 'atendimento.clientesfuncoes', 'c', 't0_.participante = c.cliente');

            // Esse where cobre o caso onde um usuário pertence a mais de um cliente com funções diferentes
            // Ex.: usuário rodrigodirk@nasajon.com.br
            //      - Cliente 1, Rodrigo, Administrador | Usuário pode ver todos os chamados desse cliente
            //      - Cliente 2, Rodrigo, Usuário       | Usuário pode ver somente chamados que criou
            $queryBuilder->andWhere("((c.funcao = 'A' and c.conta = ?) or (c.funcao = 'U' and t0_.created_by->>'email' = ?))");

            $binds[] = $email_usuario_criacao;
            $binds[] = $email_usuario_criacao;
        }
        
        return [$queryBuilder, $binds];
    }

    public function insert($logged_user, $tenant, Solicitacoes $entity) {
        
        $configAssuntoHabilitado = $this->confService->get($tenant, 'ATENDIMENTO', 'HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS');

        if ($configAssuntoHabilitado == 0 || $configAssuntoHabilitado == 1) {
            $entity->setResumo(StringUtils::geraResumo($entity->getSintoma()));
        }
        
        $entity->setCamposcustomizados($this->preencheCamposCustomizados($tenant, $entity));
        $entity->setConteudoTextual(strip_tags(html_entity_decode($entity->getSintoma())));
        $entity->setEmail(json_encode($logged_user));
        try {

            $this->getConnection()->beginTransaction();
            $entity->setAtendimento(parent::insert($logged_user, $tenant, $entity));
            foreach ($entity->getAnexos()->toArray() as $anexo) {
                $anexoModulo = new Anexosmodulos();
                $anexoModulo->setDocumentoged($anexo->getDocumentoged());
                $anexoModulo->setNome($anexo->getNome());
                $anexoModulo->setCompartilhado(false);

                $this->uploadService->insert(UploadService::ANEXO_MODULO_ATENDIMENTO, $entity->getAtendimento()['atendimento'], $tenant, $logged_user, $anexoModulo);
            }

            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $entity = $this->findObject($entity->getAtendimento()['atendimento'], $tenant);
        
        $autoreply = null;
        $eventArgs = [];
        $eventArgs["tenant"] = $tenant;
        $eventArgs["autoreply"] = $autoreply ? $autoreply : null;
        $eventArgs["fila"] = ($entity->getResponsavelWebTipo() == 2) ? $entity->getResponsavelWeb() : null;
        $this->eventDispatcher->dispatch(Event::ATENDIMENTO_CLIENTE_CREATE_TYPE, new GenericEvent($entity->getAtendimento(), $eventArgs));

        $this->chamadosRegrasProducer->publish(
          json_encode([
              'tenant'=> $tenant, 
              'atendimento'=> $entity->getAtendimento()
          ])
        );

        return $entity;
    }
    
    public function findByHistoricoEncaminhado($numeroprotocolo, $tenant, $email){
      $sql = "SELECT true
      FROM servicos.atendimentoshistoricos ah
      JOIN servicos.atendimentos a ON a.atendimento = ah.atendimento
      WHERE a.numeroprotocolo = :numeroprotocolo AND tipo = 10 AND ah.valornovo->>'historicoencaminhado' = :email ";
     
      $data = $this->getConnection()->executeQuery($sql, [
                    'numeroprotocolo' => $numeroprotocolo,
                    'tenant' => $tenant,
                    'email' => $email
                ])->fetchColumn();
        if (!$data) {
            return false;
        }
        return $data;
      
    }
    

    public function findByNumeroprotocoloEmail($numeroprotocolo, $tenant, $email) {
        $sql = "SELECT a.atendimento
                FROM servicos.vwatendimentos_cliente a
                WHERE a.tenant = :tenant
                AND a.numeroprotocolo = :numeroprotocolo
                AND (
                        email = :email
                        OR (
                                SELECT true
                                FROM atendimento.clientesfuncoes cf
                                WHERE cf.conta = :email
                                AND cf.cliente = a.participante
                                AND cf.tenant = :tenant
                        )
                        OR (
                                SELECT true
                                FROM servicos.atendimentoshistoricos ah
                                JOIN servicos.atendimentos a ON a.atendimento = ah.atendimento
                                WHERE a.numeroprotocolo = :numeroprotocolo AND tipo = 10 AND ah.valornovo->>'historicoencaminhado' = :email limit 1
                        )

                ) ";
        $data = $this->getConnection()->executeQuery($sql, [
                    'numeroprotocolo' => $numeroprotocolo,
                    'tenant' => $tenant,
                    'email' => $email
                ])->fetchColumn();
        if (!$data) {
            throw new NoResultException();
        }
        return $data;
    }
    
    public function find($id, $tenant) {

        $data = $this->findQuery($id, $tenant)->fetch();

        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }

        $data['camposcustomizados'] = json_decode($data['camposcustomizados'], true);
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
        
        $data['anexos'] = $this->uploadService->findAll($data['tenant'], UploadService::ANEXO_MODULO_ATENDIMENTO, $data['atendimento']);
        $data['followups'] = $this->followupsRepository->findAll($data['tenant'], $data['atendimento']);
        $data['chamadosmesclados'] = $this->getChamadosMesclados($tenant, $id);

        return $data;
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

    public function findQuery($id, $tenant)    {
        $sql = "SELECT
                                t0_.atendimento as \"atendimento\" ,
                                t0_.numeroprotocolo as \"numeroprotocolo\" ,
                                t0_.email as \"email\" ,
                                t0_.sintoma as \"sintoma\" ,
                                t0_.resumo as \"resumo\" ,
                                t0_.camposcustomizados as \"camposcustomizados\" ,
                                t0_.ativo as \"ativo\" ,
                                t0_.datacriacao as \"created_at\" ,
                                t0_.created_by as \"created_by\" ,
                                t0_.data_ultima_resposta as \"data_ultima_resposta\" ,
                                t0_.ultima_resposta_admin as \"ultima_resposta_admin\" ,
                                t0_.ultima_resposta_resumo as \"ultima_resposta_resumo\" ,
                                t0_.tenant as \"tenant\" ,
                                -- Caso o usuário exista na tabela de Usuários Indisponíveis, retorna o usuário vazio, senão, retorna o responsavel_web
                                case when u.usuario is not null then 'usuario_indisponivel'
									when u.usuario is null then t0_.responsavel_web
								end as \"responsavel_web\",
                                t0_.responsavel_web_tipo as \"responsavel_web_tipo\" ,
                                t0_.canal as \"canal\" ,
                                t0_.canal_email as \"canal_email\" ,
                                t0_.mesclado_a as \"mesclado\",
                                (select numeroprotocolo from servicos.atendimentos where atendimento = t0_.mesclado_a and tenant = t0_.tenant ) as \"mesclado_a_numeroprotocolo\",
                                t1_.id as \"t1_cliente\" ,
                                t1_.nome as \"t1_nome\" ,
                                t1_.nomefantasia as \"t1_nomefantasia\" ,
                                t1_.codigo as \"t1_codigo\" ,
                                t1_.cnpj as \"t1_cnpj\" ,
                                t1_.cpf as \"t1_cpf\",
                                t1_.bloqueado as \"bloqueado\",
                                t2_.nome as \"vendedor\"
                FROM servicos.vwatendimentos_cliente t0_
                LEFT JOIN ns.vwclientes_atendimento t1_ ON t0_.participante = t1_.id
                LEFT JOIN ns.vwvendedores_atendimento t2_ ON t1_.vendedor = t2_.vendedor
                -- Join para verificar se o usuário está indisponível
                LEFT JOIN atendimento.usuariosindisponiveis u ON u.usuario = t0_.responsavel_web AND u.tenant = t0_.tenant AND u.excluido = false
                WHERE t0_.atendimento = :id
                AND t0_.tenant = :tenant";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'tenant' => $tenant
        ]);
    }

    public function listDashboard($tenant, $conta) {

        $sql = "WITH clientes AS (
                    SELECT cf.cliente, cf.tenant
                    FROM atendimento.clientesfuncoes cf
                    WHERE cf.conta = :conta AND cf.tenant = :tenant
                )
                SELECT a.atendimento, a.numeroprotocolo, a.data_ultima_resposta,
                        a.resumo as atendimento_resumo, a.ultima_resposta_resumo,
                        a.ultima_resposta_admin
                FROM servicos.vwatendimentos_cliente a
                LEFT JOIN clientes c on c.cliente = a.participante AND c.tenant = a.tenant
                WHERE a.tenant = :tenant
                AND (a.participante =  c.cliente OR (a.participante IS NULL AND a.email = :conta))
                ORDER BY a.data_ultima_resposta DESC
                LIMIT :limit;";

        $chamados = $this->getConnection()->executeQuery($sql, [
                    'tenant' => $tenant,
                    'conta' => $conta,
                    'limit' => 5
                ])->fetchAll();

        return $chamados;
    }
    
    public function preencheCamposCustomizados($tenant, $entity) {
      $camposcustomizados = $this->camposCustomizadosRepository->findAll($tenant);
      $keys = !empty($entity->getCamposcustomizados()) ? array_keys($entity->getCamposcustomizados()) : null;
      $resultado = $entity->getCamposcustomizados();
      
      foreach($camposcustomizados as $campo) {
        if (!empty($keys)) {
          if (!in_array($campo['atendimentocampocustomizado'], $keys)) {
            $resultado[$campo['atendimentocampocustomizado']] = null;
          }
        } else {
          $resultado[$campo['atendimentocampocustomizado']] = null;
        }
      }
      
      return $resultado;
    }
}
