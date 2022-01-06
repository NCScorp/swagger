<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Exception;
use Doctrine\DBAL\Connection;
use JMS\Serializer\Serializer;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\MDABundle\Entity\Ns\Anexosmodulos;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Symfony\Component\EventDispatcher\GenericEvent;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\Atendimento\AppBundle\Service\UploadService;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Followups;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Nasajon\MDABundle\Repository\Atendimento\Admin\FollowupsRepository as ParentRepository;

class FollowupsRepository extends ParentRepository {
    /**
     * @var  UploadService
     */
    private $uploadService;

    /*
     * @var Producer
     */
    private $chamadosSlasViolacaoProducer;
    
    /**
     *
     * @var Serializer
     */
    private $serializer;
    
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
     /** @var ConfiguracoesService */
     private $configService;

    public function __construct(
        Connection $connection, 
        UploadService $uploadService,
        Producer $chamadosslasviolacaoproducer,
        Serializer $serializer,
        EventDispatcherInterface $eventDispatcher,
        ConfiguracoesService $configService
    ) 
    {
        parent::__construct($connection);

        $this->uploadService = $uploadService;
        $this->chamadosSlasViolacaoProducer = $chamadosslasviolacaoproducer;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->configService = $configService;
    }

    /**
     *
     * @inheritDoc
     */
    public function insert($atendimento, $logged_user, $tenant, Followups $entity) {

        $entity->setHistorico(\ForceUTF8\Encoding::toUTF8(\ForceUTF8\Encoding::toLatin1(\ForceUTF8\Encoding::toUTF8($entity->getHistorico()))));
        $entity->setConteudoTextual(strip_tags(html_entity_decode($entity->getHistorico())));

        if (empty($entity->getResumo())) {
            $entity->setResumo(StringUtils::geraResumo($entity->getHistorico()));
        }
        try {
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

            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->eventDispatcher->dispatch(Event::FOLLOWUP_CREATE_TYPE, new GenericEvent($entity->getFollowup(), ['atendimento' => $atendimento, 'tenant' => $tenant]));
        
        if($entity->getTipo() == '0'){
          $this->chamadosSlasViolacaoProducer->publish(
              json_encode([
                    'tenant'=> $tenant, 
                    'atendimento'=> $atendimento,
                    'buscar' => true
                ])
          );
        }
        
        return $entity;
    }

    /**
     * @TODO rever pois essa função itera sobre os followups executando a query de busca de anexos
     *
     */
    public function findAll($tenant, $atendimento, Filter $filter = null) {
        $followups = parent::findAll($tenant, $atendimento, $filter);

        for ($i = 0; $i < count($followups); $i++) {
            $followups[$i]['anexos'] = $this->uploadService->findAll($tenant, UploadService::ANEXO_MODULO_FOLLOWUP, $followups[$i]['followup']);
        }
        return $followups;
    }

    public function findAllQueryBuilder($tenant, $atendimento,  Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select([
            't0_.followup as followup',
            't0_.data AT TIME ZONE ? as created_at',
            't0_.created_by as created_by',
            't0_.historico as historico',
            't0_.tipo as tipo',
            't0_.criadopelocliente as criador',
            't0_.artigo as artigo',
        ]);
        $queryBuilder->from('ns.followups', 't0_');
                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $atendimento,  $filter);
        
        return [$queryBuilder, $binds];
    }


    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $atendimento,  Filter $filter = null){
        $timezone = $this->configService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
        $timezone = $timezone ?: "America/Sao_Paulo";

        $binds = [];
        $where = [];

        $binds[] = $timezone;

        $queryBuilder->addOrderBy("t0_.data", "desc");

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

}
