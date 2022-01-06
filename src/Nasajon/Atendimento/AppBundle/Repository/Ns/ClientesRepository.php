<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Ns;

use \PDO;
use Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService;
use Nasajon\Atendimento\AppBundle\Service\FlagClienteService;
use Nasajon\MDABundle\Repository\Ns\ClientesRepository as ParentRepository;
use Nasajon\MDABundle\Repository\Ns\EnderecosRepository;
use Nasajon\MDABundle\Request\Filter;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\ORM\Query\Expr;
use Nasajon\Atendimento\AppBundle\Builder\ExpressionBuilder;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\ModelBundle\Services\ConfiguracoesService;

class ClientesRepository extends ParentRepository {

    private $solicitacoesRepository;

    /**
     *
     * @var EnderecosRepository
     */
    private $enderecoRepository;

    /**
     *
     * @var FlagClienteService
     */
    private $flagclienteService;

    /**
     *
     * @var EquipeClienteFilterService
     */
    protected $equipeFilter;

    /**
     *
     * @var ProximoscontatosRepository
     */
    protected $proximoscontatosRepository;

    private $configService;

    private $nsContatosEmailsRepository;

    public function __construct($connection, $tndmntClntSrsRpstry, $nsClntsClssfcdrsRpstry, $nsCnttsRpstry, $nsTlfnsRpstry, $nsContatosEmailsRepository, $srvcsTvsRpstry, $solicitacoesRepository, $enderecoRepository, $flagclienteService, EquipeClienteFilterService $equipeFilter, $crmPrxmscnttsRpstry, ConfiguracoesService $configService) {
        parent::__construct($connection, $tndmntClntSrsRpstry,  $crmPrxmscnttsRpstry, $nsClntsClssfcdrsRpstry, $nsCnttsRpstry, $nsTlfnsRpstry, $srvcsTvsRpstry);
        $this->solicitacoesRepository = $solicitacoesRepository;
        $this->flagclienteService = $flagclienteService;
        $this->enderecoRepository = $enderecoRepository;
        $this->equipeFilter = $equipeFilter;
        $this->proximoscontatosRepository = $crmPrxmscnttsRpstry;
        $this->configService = $configService;
        $this->nsContatosEmailsRepository = $nsContatosEmailsRepository;
    }

    public function verificaPermissao($tenant) {
      $config = $this->configService->get($tenant, 'ATENDIMENTO', 'EXIBE_CLIENTE');

      if (!$config || empty($config)) {
        return false;
      }

      return true;
    }

    public function proccessOffset(Filter $filter = null) {
        list($where, $binds) = parent::proccessOffset($filter);

        $where[] = $this->equipeFilter->run('t0_');
        return [$where, $binds];
    }
    
    public function proccessFilter($filter) {
      $resultado = [];

      if (!is_null($filter) && (!empty($filter->getKey()) || $filter->getKey() == '0') && !empty($filter->getField())) {
        $filtro = StringUtils::removeTabulacoes(StringUtils::removeln(StringUtils::removeCaracteresInvalidosNoTsQuery(strtolower(StringUtils::removeAcentos(html_entity_decode(strip_tags($filter->getKey())))))));
        $keys = explode(" ", $filtro);

        for ($i = 0; $i < count($keys); $i++) {
          if (!empty($keys[$i])) {
            $resultado[] = $i === (\count($keys) - 1) 
                    ? " (to_tsquery('simple','" . $keys[$i] . ":*'" . "))::tsquery)" //se for o último adicionar :*
                    : " (to_tsquery('simple','" . $keys[$i] . "'))::tsquery "; //senão for o último crie sem :*
          }
        }
      }

      return $resultado;
    }
    
    /**
     * Sobrescrito devido a refatoração parcial para adicionar Grupo Empresarial nas buscas de cliente, tarefa #32916
     * No caso de possuir a configuração GRUPOS_EMPRESARIAIS_ATIVOS seguirá o fluxo findAllQueryBuilderBody_v2
     */
    public function findAllQueryBuilder($tenant, $bloqueado= "",  Filter $filter = null){
      $queryBuilder = $this->getConnection()->createQueryBuilder();
      $queryBuilder->select(array(
                              't0_.id as cliente',
                              't0_.nome as nome',
                              't0_.nomefantasia as nomefantasia',
                              't0_.codigo',
                              't0_.cnpj as cnpj',
                              't0_.cpf as cpf',
        ));

        $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

        if(empty($config)){
          $queryBuilder->from('ns.vwclientes_atendimento', 't0_');
          $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $bloqueado, $filter);
        } else{
          $queryBuilder->from('ns.vwclientes_atendimento_v2', 't0_');
          $binds = $this->findAllQueryBuilderBody_v2($queryBuilder, $tenant, $config, $bloqueado, $filter);
        }

      return [$queryBuilder, $binds];
    }

    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $bloqueado = "", Filter $filter = null) {
      $binds = [];
      $where = [];

      if (!empty($filter->getKey())){
        $tsQueries = $this->proccessFilter($filter);

        if (!empty($tsQueries)){
          $queryBuilder->addSelect(array("COALESCE(ts_rank_cd(t0_.search_fts," . join("&&", $tsQueries) . ", 0) as rank"));
          $queryBuilder->addOrderBy('rank', 'DESC');
          $where[] = $queryBuilder->expr()->gt("COALESCE(ts_rank_cd(t0_.search_fts," . join("&&", $tsQueries) . ", 0)", "0");
        }
      } else {
        $queryBuilder->addOrderBy("t0_.codigo", "ASC");
      }

      $queryBuilder->setMaxResults(20);

      if(count($tenant) > 0){
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;
      }

      if(strlen($bloqueado) > 0){
        $where[] = $queryBuilder->expr()->eq("t0_.bloqueado", "?");
        $binds[] = $bloqueado;
      }


      if (!empty($where)) {
        $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
      }
      if (!empty($filters)) {
        $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
      }
      
      // Código paliativo para os casos em que o search_fts não está preenchido.
      // Adiciona uma condição OR para alguns campos do cliente, combinado com um AND para o tenant.
      if (!empty($filter->getKey())) {
        $queryBuilder->orWhere('((t0_.codigo ILIKE ? 
                                  OR t0_.nome ILIKE ?
                                  OR t0_.nomefantasia ILIKE ? 
                                  OR t0_.cnpj ILIKE ? 
                                  OR t0_.cpf ILIKE ?) 
                               AND t0_.tenant = ?)');

        $binds[] = "%" . $filter->getKey() . "%"; // Código 
        $binds[] = "%" . $filter->getKey() . "%"; // Nome
        $binds[] = "%" . $filter->getKey() . "%"; // CNPJ
        $binds[] = "%" . $filter->getKey() . "%"; // CPF
        $binds[] = "%" . $filter->getKey() . "%"; // Nome Fantasia
        $binds[] = $tenant;                       // Tenant
      }

        /**
         * Incluído no final da consulta um AND para validar as restrições impostas em configurações de equipe
         *
         */
        list($offsets, $offsetsBinds) = $this->proccessOffset($filter);
        if (!empty($offsets)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_AND, $offsets));
            $binds = array_merge($binds, $offsetsBinds);
        }

      return $binds;
    }

    public function findAllQueryBuilderBody_v2(&$queryBuilder, $tenant, $config, $bloqueado = "", Filter $filter = null) {
      $binds = [];
      $where = [];
      
      if (!empty($filter->getKey())) {
        $tsQueries = $this->proccessFilter($filter);
        
        if (!empty($tsQueries)) {
          $queryBuilder->addSelect(array("COALESCE(ts_rank_cd(t0_.search_fts," . join("&&", $tsQueries) . ", 0) as rank"));
          $queryBuilder->addOrderBy('rank', 'DESC');
          $where[] = $queryBuilder->expr()->gt("COALESCE(ts_rank_cd(t0_.search_fts," . join("&&", $tsQueries) . ", 0)", "0");
        }
      } else {
        $queryBuilder->addOrderBy("t0_.codigo", "ASC");
      }
      
      $queryBuilder->setMaxResults(20);

      if(count($tenant) > 0){
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;
      }
      
      if (strlen($bloqueado) > 0) {
        $where[] = $queryBuilder->expr()->eq("t0_.bloqueado", "?");
        $binds[] = $bloqueado;
      }
      
      list($offsets, $offsetsBinds) = $this->proccessOffset($filter);
      $where = array_merge($where, $offsets);
      $binds = array_merge($binds, $offsetsBinds);
        
      if (!empty($where)) {
        $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
      }
      if (!empty($filters)) {
        $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
      }


      $grupos_empresariais = explode(",", trim($config));

      $size = -1;

      if(($size = sizeof($grupos_empresariais)) > 0){
        $strWhere = '';

        for ($i = 0; $i < count($grupos_empresariais) - 1; $i++) {
          $guid = $grupos_empresariais[$i];

          if(!empty($guid) && StringUtils::isGuid($guid)) {
            $strWhere .= "t0_.grupoempresarial = '".$guid."' OR ";
          }
        }
        
        if(!empty($grupos_empresariais[$size - 1]) && StringUtils::isGuid($grupos_empresariais[$size - 1])) {
          $strWhere .= "t0_.grupoempresarial = '".$grupos_empresariais[$size - 1]."'";

          $queryBuilder->andWhere($strWhere);
        }
      }
        
      return $binds;
    }

    private function findQuery($id, $tenant) {
      $sql = "SELECT
      t0_.id as \"cliente\" ,
      t0_.codigo as \"codigo\" ,
      t0_.nome as \"nome\" ,
      t0_.nomefantasia as \"nomefantasia\" ,
      t0_.cpf as \"cpf\" ,
      t0_.cnpj as \"cnpj\" ,
      t0_.datacliente as \"datacliente\" ,
      t0_.bloqueado as \"bloqueado\" ,
      t0_.justificativasituacaopagamento as \"justificativasituacaopagamento\" ,
      t0_.justificativatipoclientepagamento as \"justificativatipoclientepagamento\" ,
      t0_.tenant as \"tenant\" ,
      t0_.observacao as \"observacao\" ,
      t0_.anotacao as \"anotacao\" ,
      ( select string_agg(cep.email, ', ') email from ns.contatosemails cep 
        where cep.principal = true and cep.pessoa_id = :id group by cep.pessoa_id
      ) as \"email\" ,
      ( select string_agg(cec.email, ', ') email from ns.contatosemails as cec 
        where (cec.recebe_nfe = true or cec.envia_nfse_prefeitura = true or cec.recebe_boleto = true or cec.recebe_mala_direta = true)
        and cec.pessoa_id = :id group by cec.pessoa_id
      ) as \"emailcobranca\" ,
      t0_.status_suporte as \"status_suporte\" ,
      t0_.datasituacaopagamento as \"datasituacaopagamento\" ,
      t0_.datatipoclientepagamento as \"datatipoclientepagamento\" ,
      t0_.situacaopagamento as \"situacaopagamento\" ,
      t0_.tipoclientepagamento as \"tipoclientepagamento\" ,
      t1_.vendedor as \"t1_vendedor\" ,
      t1_.nome as \"t1_nome\" ,
      t1_.bloqueado as \"t1_bloqueado\" ,
      t2_.representantecomercial as \"t2_representantecomercial\" ,
      t2_.nome as \"t2_nome\" ,
      t2_.bloqueado as \"t2_bloqueado\" ,
      t3_.representantetecnico as \"t3_representantetecnico\" ,
      t3_.nome as \"t3_nome\" ,
      t3_.bloqueado as \"t3_bloqueado\" ,
      t10_.restricaocobranca as \"t10_restricaocobranca\" ,
      t10_.codigo as \"t10_codigo\" ,
      t10_.descricao as \"t10_descricao\" ,
      t11_.restricaocobranca as \"t11_restricaocobranca\" ,
      t11_.codigo as \"t11_codigo\" ,
      t11_.descricao as \"t11_descricao\" 
      FROM ns.vwclientes_atendimento t0_
      LEFT JOIN ns.vwvendedores_atendimento t1_ ON t0_.vendedor = t1_.vendedor
      LEFT JOIN ns.vwrepresentantescomerciais_atendimento t2_ ON t0_.representante = t2_.representantecomercial
      LEFT JOIN ns.vwrepresentantestecnicos_atendimento t3_ ON t0_.representante_tecnico = t3_.representantetecnico
      LEFT JOIN financas.restricoescobrancas t10_ ON t0_.restricaocobranca1 = t10_.restricaocobranca
      LEFT JOIN financas.restricoescobrancas t11_ ON t0_.restricaocobranca2 = t11_.restricaocobranca
      WHERE t0_.id = :id
      AND t0_.tenant = :tenant
      ";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'tenant' => $tenant
        ]);
    }

    private function findQuery_v2($id, $tenant, $config) {
      $grupos_empresariais = explode(",", trim($config));
      $queryBuilder = $this->getConnection()->createQueryBuilder();
      
      $queryBuilder->select(array('t0_.id as cliente',
        't0_.codigo as codigo' ,
        't0_.nome as nome',
        't0_.nomefantasia as nomefantasia' ,
        't0_.cpf as cpf' ,
        't0_.cnpj as cnpj' ,
        't0_.datacliente as datacliente' ,
        't0_.bloqueado as bloqueado' ,
        't0_.justificativasituacaopagamento as justificativasituacaopagamento' ,
        't0_.justificativatipoclientepagamento as justificativatipoclientepagamento' ,
        't0_.tenant as tenant' ,
        't0_.observacao as observacao' ,
        "( select string_agg(cep.email, ', ') email from ns.contatosemails cep 
            where cep.principal = true and cep.pessoa_id = :idpessoa group by cep.pessoa_id
          ) as email",
        "( select string_agg(cec.email, ', ') email from ns.contatosemails as cec 
          where (cec.recebe_nfe = true or cec.envia_nfse_prefeitura = true or cec.recebe_boleto = true or cec.recebe_mala_direta = true)
          and cec.pessoa_id = :idpessoa group by cec.pessoa_id
        ) as emailcobranca",
        't0_.status_suporte as status_suporte' ,
        't0_.datasituacaopagamento as datasituacaopagamento' ,
        't0_.datatipoclientepagamento as datatipoclientepagamento' ,
        't0_.situacaopagamento as situacaopagamento' ,
        't0_.tipoclientepagamento as tipoclientepagamento' ,
        't1_.vendedor as t1_vendedor' ,
        't1_.nome as t1_nome' ,
        't1_.bloqueado as t1_bloqueado' ,
        't2_.representantecomercial as t2_representantecomercial' ,
        't2_.nome as t2_nome' ,
        't2_.bloqueado as t2_bloqueado' ,
        't3_.representantetecnico as t3_representantetecnico' ,
        't3_.nome as t3_nome' ,
        't3_.bloqueado as t3_bloqueado' ,
        't10_.restricaocobranca as t10_restricaocobranca' ,
        't10_.codigo as t10_codigo' ,
        't10_.descricao as t10_descricao' ,
        't11_.restricaocobranca as t11_restricaocobranca' ,
        't11_.codigo as t11_codigo' ,
        't11_.descricao as t11_descricao'
      ))
      ->from('ns.vwclientes_atendimento_v2','t0_')
      ->leftJoin('t0_', 'ns.vwvendedores_atendimento', 't1_', 't0_.vendedor = t1_.vendedor')
      ->leftJoin('t0_', 'ns.vwrepresentantescomerciais_atendimento', 't2_', 't0_.representante = t2_.representantecomercial')
      ->leftJoin('t0_', 'ns.vwrepresentantestecnicos_atendimento', 't3_', 't0_.representante_tecnico = t3_.representantetecnico')
      ->leftJoin('t0_', 'financas.restricoescobrancas', 't10_', 't0_.restricaocobranca1 = t10_.restricaocobranca')
      ->leftJoin('t0_', 'financas.restricoescobrancas', 't11_', 't0_.restricaocobranca2 = t11_.restricaocobranca')
      ->where('t0_.id = :idpessoa')
      ->andWhere('t0_.tenant = :tenant')
      ->setParameter('tenant', $tenant)
      ->setParameter('idpessoa', $id);
    
      return $queryBuilder->execute();

    }


    public function find($id, $tenant) {
      $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

      if (empty($config)) {
        $data = $this->findQuery($id, $tenant)->fetch();
        
      } else {
        $data = $this->findQuery_v2($id, $tenant, $config)->fetch();
      }

        if(!$data){
          throw new \Doctrine\ORM\NoResultException();
        }

        foreach ($this->getLinks() as $link) {
            $newArr = [];
            foreach ($data as $subKey => $value) {
                if (substr($subKey, 0, strlen($link['alias'])) === $link['alias']) {
                    $newArr[str_replace($link['alias'], "", $subKey)] = $value;
                    unset($data[$subKey]);
                }
            }
            if(is_null($newArr[$link['identifier']])){
                $data[$link['field']] = null;
            }else{
                $data[$link['field']] = $newArr;
            }            
        }

        $filter = new Filter();
        $filter->setField('all');
        $filter->setKey('');
        $filter->setOffset('');
        $filter->setOrder('desc');

        $data['usuarios'] = $this->tndmntClntSrsRpstry->findAll($tenant,$id);
        $data['contatos'] = $this->nsCnttsRpstry->findAll($tenant,$id);
        $data['telefones'] = $this->nsTlfnsRpstry->findAll($tenant,$id);

        // Busca todos os e-mails da tabela ns.contatosemails.
        $data['contatosemails'] = $this->nsContatosEmailsRepository->findAll($tenant,$id);
        $data['classificadores'] = $this->nsClntsClssfcdrsRpstry->findAll($tenant,$id);
        $data['ativos'] = $this->srvcsTvsRpstry->findAll($tenant,$id);
        $data['proximoscontatos'] = $this->crmPrxmscnttsRpstry->findAll($tenant);
        $data['enderecos'] = $this->enderecoRepository->findAll($tenant, $id);
        $data['solicitacoes'] = $this->solicitacoesRepository->findAll($tenant, "", "", $id, "", "", "", "", "", "datacriacao", $filter);
        $data['pessoamunicipio'] = $this->getPessoaMunicipio($data['cliente'], $tenant);                
        $data['proximoscontatos'] = $this->proximoscontatosRepository->findAll($tenant, $data['cliente'], '', '', '', '', '', null);
        $data['flags'] = $this->flagclienteService->run($tenant, $data);
        
        foreach ($data['contatos'] as &$contato) {
          foreach ($data['telefones'] as $telefone) {
            if(!isset($contato['telefones'])){
              $contato['telefones'] = [];
            }
            if($contato['id'] == $telefone['contato'])
            {
            array_push($contato['telefones'], $telefone);
            }

          }
          
          // Filtra os e-mails de contato para cada um dos contatos.
          $contato['contatosemails'] = array_values(array_filter($data['contatosemails'], function($contatoEmail) use ($contato) {
            return $contatoEmail['contato'] == $contato['id'];
          }));

        }
     return $data;
    }

 
    public function getPessoaMunicipio($id, $tenant) {
        $sql = "SELECT t1.pessoamunicipio, t2.ibge, t2.nome, t2.uf
                FROM ns.pessoasmunicipios t1
                LEFT JOIN ns.municipios t2 on t1.ibge = t2.ibge
                WHERE t1.pessoa = :pessoa AND t1.tenant = :tenant ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('pessoa', $id);
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getContratos($id, $tenant) {
        $sql = "SELECT contrato , codigo, descricao, participante
                FROM financas.contratos
                WHERE participante = :participante AND tenant = :tenant ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('participante', $id);
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findUsersToNotificate($cliente, $tenant) {
        $sql = "SELECT conta FROM atendimento.clientesfuncoes WHERE notificar = true and cliente = :cliente and tenant = :tenant";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('cliente', $cliente);
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function findAllUsersToNotificate($cliente, $tenant) {
      $sql = "SELECT conta, funcao FROM atendimento.clientesfuncoes WHERE notificar = true and cliente = :cliente and tenant = :tenant";
      $stmt = $this->getConnection()->prepare($sql);
      $stmt->bindValue('cliente', $cliente);
      $stmt->bindValue('tenant', $tenant);
      $stmt->execute();
      return $stmt->fetchAll();
  }
    
    public function buscaClientePorCnpjCpf($cnpjcpf, $tenant) {
    $sql = 'SELECT id, nome, emailcobranca as emails, cnpj
                FROM ns.vwclientes_atendimento
                WHERE tenant = :tenant
                AND bloqueado = false 
                AND cnpj = :cnpjcpf';

    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindValue('cnpjcpf', $cnpjcpf);
    $stmt->bindValue('tenant', $tenant);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result) {
      $emails = str_replace([",", ";", "/", "\\"], ";", $result['emails']);
      $emails = explode(";", $emails);
      $emails = array_map(function($email) {
        return trim($email);
      }, $emails);
      $emails = array_filter($emails, function($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
      });
      $result['emails'] = $emails;
    }
    return $result;
  }

  public function getActiveTenants() {
    $sql = 'select distinct t.codigo, v.tenant
            from ns.tenants t
            left join ns.vwclientes_atendimento v on v.tenant = t.tenant
            order by tenant desc, codigo';

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
  }

  public function getUsuariosGrupos($id, $tenant) {
    $sql = 'select t0_.clientefuncao as clientefuncao,
    t0_.conta as conta, 
    t1_.nome as cliente_nome, 
    t1_.nomefantasia as cliente_nomefantasia
    FROM atendimento.clientesfuncoes t0_ LEFT JOIN ns.vwclientes_atendimento t1_ ON t0_.cliente = t1_.id 
    WHERE (t0_.tenant = :tenant ) AND (t0_.cliente = :id) ORDER BY t0_.conta ASC;';

    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindValue('id', $id);
    $stmt->bindValue('tenant', $tenant);
    $stmt->execute();
    $result = $stmt->fetch();

    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
  }

}
