<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Doctrine\DBAL\Connection;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class ArquivosRepository extends \Nasajon\MDABundle\Repository\AbstractRepository {

    /** @var ArquivoFilterService */
    protected $arquivoRegrasFilter;

    /** @var ConfiguracoesService */
    private $configService;

    public function __construct(
        Connection $connection,
        ConfiguracoesService $configService
    ) 
    {
        parent::__construct($connection);

        $this->setFilters([
            'titulo' => 'titulo',
            'nomearquivo' => 'nomearquivo',
        ]);

        $this->configService = $configService;
    }

    public function verificaPermissao($tenant) 
    {
        $config = $this->configService->get($tenant, 'ATENDIMENTO', 'DISPONIBILIZAR_ARQUIVOS');
  
        if (!$config) {
          return false;
        }
  
        return true;
    }

    /**
     * @param string $id
     * @param mixed $tenant

     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    function find($tenant, $id) {

        $sql = "SELECT
                        t0_.nomearquivo as \"nomearquivo\" ,
                        t0_.caminho as \"caminho\"             
                FROM atendimento.arquivos t0_
                WHERE t0_.arquivo = :id AND
                      t0_.tenant = :tenant";

        $data = $this->getConnection()->executeQuery($sql, [
                    'id' => $id,
                    'tenant' => $tenant
                ])->fetch();

        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }

        return $data;
    }

    public function findAll($tenant, $clientes, Filter $filter = null) {

        $this->validateOffset($filter);

        $binds = [];
        $where = [];
        $whereRegra = [];

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
            't0_.arquivo as arquivo',
            't0_.caminho as caminho',
            't0_.titulo as titulo',
            't0_.nomearquivo as nomearquivo',
            't0_.tamanho as tamanho',
            'COALESCE(updated_at, created_at) AS updated_at',
            't0_.descricao as descricao',
        ));
        $queryBuilder->from('atendimento.arquivos', 't0_');

        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

//        $whereRegra[] = $queryBuilder->expr()->eq("t0_.todosclientes", "1");
        
        if (count($clientes) > 0) {
            foreach ($clientes as $cliente) {
                if ($cliente['cliente_bloqueado'] == 0) {
                    $whereRegra[] = $queryBuilder->expr()->eq("atendimento.private_validaregraarquivo_v5( atendimento.tratarClassificador(t0_.regravisualizacao), row(?, ?, ?, ?, ?, ?, ?, ?, ?)::atendimento.tarquivovalidaregra_v4)", $queryBuilder->expr()->literal(true));
                    $binds[] = implode(",", array_map(function($oferta) {
                                return "'" . $oferta['oferta'] . "'";
                            }, $cliente['cliente_ofertas']));
                    $binds[] = !empty($cliente['cliente_estado']) ? $cliente['cliente_estado'] : "null";
                    $binds[] = $cliente['cliente_bloqueado'] ? '1' : '0';
                    $binds[] = !empty($cliente['status_suporte']) ? $cliente['status_suporte'] : 'Ativo';
                    $binds[] = !empty($cliente['cliente_email']) ? $cliente['cliente_email'] : "null";
                    $binds[] = !empty($cliente['cliente_cobranca']) ? $cliente['cliente_cobranca'] : "";
                    $binds[] = !empty($cliente['cliente_representantetecnico']) ? $cliente['cliente_representantetecnico'] : "null";
                    $binds[] = !empty($cliente['cliente_representantecomercial']) ? $cliente['cliente_representantecomercial'] : "null";
                    $binds[] = !empty($cliente['cliente_classificacao']) ? $cliente['cliente_classificacao'] : "null";
                }
            }
        }

        list($offsets, $offsetsBinds) = $this->proccessOffset($filter);
        $where = array_merge($where, $offsets);
        $binds = array_merge($binds, $offsetsBinds);

        if (empty($whereRegra)) {
            return null;
        } else {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, [new CompositeExpression(CompositeExpression::TYPE_AND, $where), new CompositeExpression(CompositeExpression::TYPE_OR, $whereRegra)]));

            $queryBuilder->setMaxResults(20);

            $queryBuilder->addOrderBy('t0_.ordem', 'asc');

            $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());

            $stmt->execute($binds);

            $result = $stmt->fetchAll();

            return $result;
        }
    }

}
