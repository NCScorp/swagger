<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Exception;
use Nasajon\Atendimento\AppBundle\Service\UploadService;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Followups;
use Nasajon\MDABundle\Entity\Ns\Anexosmodulos;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\FollowupsRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
use JMS\Serializer\Serializer;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Service\UsuariosDisponibilidadesService;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

class FollowupsRepository extends ParentRepository {

    /**
     *
     * @var  UploadService
     */
    private $uploadService;
    
    /**
     *
     * @var Producer 
     */
    private $chamadosSlasViolacaoProducer;
    
    /**
     *
     * @var Serializer 
     */
    private $serializer;
    
    /**
     *
     * @var TraceableEventDispatcher 
     */
    private $eventDispatcher;

    /**
     * @var UsuariosDisponibilidadesService
     */
    private $usuariosDisponibilidadesService;

    /**
     * @var ConfiguracoesService
     */
    private $configuracoesService;

    private $emailsProcessarConsumer;

    public function __construct(
            $connection, 
            UploadService $uploadService, 
            Producer $chamadosslasviolacaoproducer,
            Serializer $serializer,
            $eventDispatcher,
            UsuariosDisponibilidadesService $usuariosDisponibilidadesService,
            ConfiguracoesService $configuracoesService,
            $emailsProcessarConsumer,
            $serviceContainer
            ) {
        parent::__construct($connection);

        $this->uploadService = $uploadService;
        $this->chamadosSlasViolacaoProducer = $chamadosslasviolacaoproducer;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->usuariosDisponibilidadesService = $usuariosDisponibilidadesService;
        $this->configuracoesService = $configuracoesService;
        $this->emailsProcessarConsumer = $emailsProcessarConsumer;

        $this->serviceContainer = $serviceContainer;
    }

    public function insert($atendimento, $logged_user, $tenant, Followups $entity) {
        if (empty($entity->getResumo())) {
            $entity->setResumo(StringUtils::geraResumo($entity->getHistorico()));
        }
        try {

            // Caso a entidade do followup chegue sem o objeto do Atendimento, 
            // busca o Atendimento pelo guid que foi passado por parâmetro.
            if (!$entity->getAtendimento()) {
                $soliCliRepository = $this->serviceContainer
                    ->get('nasajon_mda.atendimento_cliente_solicitacoes_repository');

                $atendimentoEntity = $soliCliRepository->fillEntity($soliCliRepository->find($atendimento, $tenant));

                $entity->setAtendimento($atendimentoEntity);
            }

            $entity->setConteudoTextual(strip_tags(html_entity_decode($entity->getHistorico())));
            $this->getConnection()->beginTransaction();
            $inserted = parent::insert($atendimento, $logged_user, $tenant, $entity);
            $entity->setFollowup($inserted['followup']);
            foreach ($entity->getAnexos()->toArray() as $anexo) {
                $anexoModulo = new Anexosmodulos();
                $anexoModulo->setDocumentoged($anexo->getDocumentoged());
                $anexoModulo->setNome($anexo->getNome());
                $anexoModulo->setCompartilhado(false);

                $this->uploadService->insert(UploadService::ANEXO_MODULO_FOLLOWUP, $entity->getFollowup(), $tenant, $logged_user, $anexoModulo);
            }
            
            $this->eventDispatcher->dispatch(Event::FOLLOWUP_CREATE_TYPE, new GenericEvent($entity->getFollowup(), ['atendimento' => $atendimento, 'tenant' => $tenant]));
            $this->chamadosSlasViolacaoProducer->publish(
              json_encode([
                  'tenant'=> $tenant, 
                  'atendimento'=> $atendimento,
                  'buscar' => true
              ])
            );

            /**
             * Caso o valor da configuração seja 3
             * Reatribuir para a última Fila em que esteve. Caso esta não exista, deixar sem atribuição.
             * 
             * Caso o valor da configuração seja 4
             * Reatribrir para a última Fila em que esteve. Caso esta não exista, direcionar à Regras.
             */ 
            $atribuicaoChamadoQuandoUsuarioIndisponivel = $this->configuracoesService->get($tenant, 'ATENDIMENTO', 'USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA');

            // Caso o usuário esteja indisponível
            if ($entity->getAtendimento()->getResponsavelWeb() == 'usuario_indisponivel') {
                if ($atribuicaoChamadoQuandoUsuarioIndisponivel == '3') {
                    $this->usuariosDisponibilidadesService->reatribuirParaFilaOuDeixarSemAtribuicao((new Solicitacoes())->setAtendimento($atendimento)->setTenant($tenant));
                } else if ($atribuicaoChamadoQuandoUsuarioIndisponivel == '4') {
                    $this->usuariosDisponibilidadesService->direcionarParaRegras((new Solicitacoes())->setAtendimento($atendimento)->setTenant($tenant));
                }
                
            } else {
                if ($entity->getAtendimento()->getResponsavelWebTipo() == 1) {
                    $this->emailsProcessarConsumer->publish(
                        json_encode([
                            'tenant' => $tenant, 
                            'atendimento' => $atendimento,
                            'cliente_respondendo_chamado' => true,
                            'tags' => [
                                'protocolo' => $entity->getAtendimento()->getNumeroprotocolo(),
                                'resumo' => $entity->getResumo(),
                                'atendimento' => $entity->getAtendimento(),
                            ],
                            'responsavel_tipo' => 1,
                            'responsavel_web' => $entity->getAtendimento()->getResponsavelWeb()
                        ])
                    );
                } else if ($entity->getAtendimento()->getResponsavelWebTipo() == 2) {
                    $this->emailsProcessarConsumer->publish(
                        json_encode([
                          'tenant'=> $tenant, 
                          'atendimento'=> $entity->getAtendimento()->getAtendimento(),
                          'responsavel' => $entity->getAtendimento()->getResponsavelWeb(),
                          'responsavel_tipo' => $entity->getAtendimento()->getResponsavelWebTipo(),
                          'cliente_respondendo_chamado' => true,
                          'responsavel_tipo' => 2,
                        ])
                      );
                }
            }
            
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $entity;
    }

    /**
     * @TODO [XGH] rever pois essa função itera sobre os followups executando a query de busca de anexos
     *
     */
    public function findAll($tenant, $atendimento, Filter $filter = null) {
        $followups = parent::findAll($tenant, $atendimento, $filter);

        for ($i = 0; $i < count($followups); $i++) {
            $followups[$i]['anexos'] = $this->uploadService->findAll($tenant, UploadService::ANEXO_MODULO_FOLLOWUP, $followups[$i]['followup']);
        }

        return $followups;
    }

    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $atendimento,  Filter $filter = null){
        $binds = [];
        $where = [];
        
        $queryBuilder->addOrderBy("t0_.data", "asc");
            
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

            
        $where[] = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq("t0_.mesclado_a", "?"),
            $queryBuilder->expr()->eq("t0_.atendimento", "?")
        );
        $binds[] = $atendimento;
        $binds[] = $atendimento;
        
        list($filters, $filtersBinds) = $this->proccessFilter($filter);
        $binds = array_merge($binds, $filtersBinds);

        if(!empty($where)){
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }
        if(!empty($filters)){
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }
        
        return $binds;
    }

    public function findAllQueryBuilder($tenant, $atendimento,  Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
                                't0_.followup as followup',
                                't0_.data as created_at',
                                't0_.created_by as created_by',
                                't0_.historico as historico',
                                't0_.criadopelocliente as criador',
                                't0_.mesclado_a as mesclado_a',
                                't0_.atendimento'
                    ));
        $queryBuilder->from('ns.vwfollowupsvisualizacaoclientes', 't0_');
                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $atendimento,  $filter);
        

        return [$queryBuilder, $binds];
    }

}
