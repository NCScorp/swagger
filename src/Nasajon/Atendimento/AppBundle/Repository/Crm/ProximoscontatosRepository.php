<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\Crm\ProximoscontatosRepository as ParentRepository;

/**
 * ProximoscontatosRepository
 *
 */
class ProximoscontatosRepository extends ParentRepository {

     /** @var ConfiguracoesService */
     private $configService;

     public function __construct(
         Connection $connection,
        ConfiguracoesService $configService
     )
     {
     parent::__construct($connection);
     $this->configService = $configService;
      }      

      


    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant){

        $timezone = $this->configService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
        $timezone = $timezone ?: "America/Sao_Paulo";
        $data = $this->findQuery($id, $timezone)->fetch();

        if(!$data){
            throw new \Doctrine\ORM\NoResultException();
        }

                $data['t3_created_by'] = json_decode($data['t3_created_by'], true);
        
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
        
        $date = new \DateTime($data['data']);
        $data['ematraso'] == true && $data['situacao'] == 0?
            $result['ematraso'] = 1 : 
            $result['ematraso'] = 0;
                return $data;
    }
    
    public function findAll($tenant, $cliente = '', $data = '', $situacao = '', $ematraso = '', $responsavel_web = '', $atendimento = '', Filter $filter = null) {
        $result = parent::findAll($tenant, $cliente, $data, $situacao, $ematraso, $responsavel_web, $atendimento, $filter);
        // dump($result); die();
        // dump($result); die();
        $resultado = array_map(function ($contato) {
            $date = new \DateTime($contato['data']);
            
            $contato['ematraso'] == true && $contato['situacao'] == 0?
                $contato['ematraso'] = 1 : 
                $contato['ematraso'] = 0;
            return $contato;
        }, $result);

        // dump($resultado); die();
        return $resultado;
    }
    
    public function pendentes($tenant, $logged_user) {
      $sql = "SELECT count(*) as pendentes
              FROM crm.proximoscontatos
              WHERE data = CURRENT_DATE AND situacao = 0 AND tenant = :tenant AND responsavel_web = :logged_user";

      $stmt = $this->getConnection()->prepare($sql);
      $stmt->execute([ "tenant" => $tenant, "logged_user" => $logged_user ]);
      return $stmt->fetch();
    }

    
    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $cliente= "", $data= "", $situacao= "", $ematraso= "", $responsavel_web= "", $atendimento= "",  Filter $filter = null){
        
        $timezone = $this->configService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
        $timezone = $timezone ?: "America/Sao_Paulo";
        
        $binds = [];
        $where = [];
        
        $binds[] = $timezone;
               
        $queryBuilder->setMaxResults(20);
        
                    
            
        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

        
                    
             
                if(strlen($cliente) > 0){ 
            
        $where[] = $queryBuilder->expr()->eq("t0_.participante", "?");
        $binds[] = $cliente;

         
            } 
        
                    
             
                if(strlen($data) > 0){ 
            
        $where[] = $queryBuilder->expr()->eq("t0_.data", "?");
        $binds[] = $data;

         
            } 
        
                    
             
                if(strlen($situacao) > 0){ 
            
        $where[] = $queryBuilder->expr()->eq("t0_.situacao", "?");
        $binds[] = $situacao;

         
            } 
        
                    
             
                if(strlen($ematraso) > 0){ 
            
        $where[] = $queryBuilder->expr()->eq("t0_.ematraso", "?");
        $binds[] = $ematraso;

         
            } 
        
                    
             
                if(strlen($responsavel_web) > 0){ 
            
        $where[] = $queryBuilder->expr()->eq("t0_.responsavel_web", "?");
        $binds[] = $responsavel_web;

         
            } 
        
                    
             
                if(strlen($atendimento) > 0){ 
            
        $where[] = $queryBuilder->expr()->eq("t0_.atendimento", "?");
        $binds[] = $atendimento;

         
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
        
        return $binds;
    }

    public function findAllQueryBuilder($tenant, $cliente= "", $data= "", $situacao= "", $ematraso= "", $responsavel_web= "", $atendimento= "",  Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
                                't0_.proximocontato as proximocontato',
                                't0_.responsavel_web as responsavel_web',
                                't0_.situacao as situacao',
                                "CASE
                                WHEN cast(extract( epoch from (TO_TIMESTAMP(CONCAT(t0_.data, ' ', t0_.hora), 'YYYY-MM-DD HH24:MI:SS')::timestamp - now() at time zone ?)) as numeric) < 0 then true
                                ELSE false
                                END AS ematraso",
                                "t0_.data as data",
                                't0_.hora as hora',
                                't0_.observacao as observacao',
                                't0_.tenant as tenant',
                    ));
        $queryBuilder->from('crm.proximoscontatos', 't0_');
                                        $queryBuilder->leftJoin('t0_', 'ns.vwclientes_atendimento', 't1_', 't0_.participante = t1_.id'); 
            $queryBuilder->addSelect(array(
                                    't1_.id as cliente_cliente',
                                    't1_.nome as cliente_nome',
                                    't1_.nomefantasia as cliente_nomefantasia',
                                    't1_.codigo as cliente_codigo',
                                    't1_.cnpj as cliente_cnpj',
                                    't1_.cpf as cliente_cpf',
                                    't1_.status_suporte as cliente_status_suporte',
                            ));    
                                            $queryBuilder->leftJoin('t0_', 'crm.assuntos', 't2_', 't0_.assunto = t2_.assunto'); 
            $queryBuilder->addSelect(array(
                                    't2_.assunto as assunto_assunto',
                                    't2_.descricao as assunto_descricao',
                                    't2_.tenant as assunto_tenant',
                            ));    
                            
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $cliente, $data, $situacao, $ematraso, $responsavel_web, $atendimento,  $filter);
        

        return [$queryBuilder, $binds];
    }

    private function findQuery($id, $timezone)
    {
        $sql = "SELECT

                                t0_.proximocontato as \"proximocontato\" ,
                                t0_.data as \"data\" ,
                                t0_.hora as \"hora\" ,
                                t0_.situacao as \"situacao\" ,
                                t0_.lastupdate as \"lastupdate\" ,
                                t0_.responsavel_web as \"responsavel_web\" ,
                                t0_.tenant as \"tenant\" ,
                                t0_.observacao as \"observacao\" ,
                                CASE
                                WHEN cast(extract( epoch from (TO_TIMESTAMP(CONCAT(t0_.data, ' ', t0_.hora), 'YYYY-MM-DD HH24:MI:SS')::timestamp - now() at time zone :timezone)) as numeric) < 0 then true
                                ELSE false
                                END as \"ematraso\",
                                t1_.assunto as \"t1_assunto\" ,
                                t1_.descricao as \"t1_descricao\" ,
                                t1_.tenant as \"t1_tenant\" ,
                                t2_.id as \"t2_cliente\" ,
                                t2_.nome as \"t2_nome\" ,
                                t2_.nomefantasia as \"t2_nomefantasia\" ,
                                t2_.codigo as \"t2_codigo\" ,
                                t2_.cnpj as \"t2_cnpj\" ,
                                t2_.cpf as \"t2_cpf\" ,
                                t2_.vendedor as \"t2_vendedor\" ,
                                t2_.status_suporte as \"t2_status_suporte\" ,
                                t3_.atendimento as \"t3_atendimento\" ,
                                t3_.numeroprotocolo as \"t3_numeroprotocolo\" ,
                                t3_.resumo_admin as \"t3_resumo\" ,
                                t3_.situacao as \"t3_situacao\" ,
                                t3_.visivelparacliente as \"t3_visivelparacliente\" ,
                                t3_.canal as \"t3_canal\" ,
                                t3_.datacriacao as \"t3_created_at\" ,
                                t3_.created_by as \"t3_created_by\" ,
                                t3_.participante as \"t3_cliente\" ,
                                t3_.responsavel_web as \"t3_responsavel_web\" ,
                                t3_.ativo as \"t3_ativo\" ,
                                t3_.data_ultima_resposta as \"t3_data_ultima_resposta\" ,
                                t3_.data_ultima_resposta_admin as \"t3_data_ultima_resposta_admin\" ,
                                t3_.data_ultima_resposta_cliente as \"t3_data_ultima_resposta_cliente\" ,
                                t3_.ultima_resposta_admin as \"t3_ultima_resposta_admin\" ,
                                t3_.ultima_resposta_resumo as \"t3_ultima_resposta_resumo\" ,
                                t3_.adiado as \"t3_adiado\" ,
                                t3_.data_adiamento as \"t3_data_adiamento\" ,
                                t3_.data_abertura as \"t3_data_abertura\" ,
                                t3_.proximaviolacaosla as \"t3_proximaviolacaosla\" ,
                                t3_.mesclado_a as \"t3_mesclado_a\" 
            
                FROM crm.vwproximoscontatos t0_

                                    LEFT JOIN crm.assuntos t1_ ON t0_.assunto = t1_.assunto

                            LEFT JOIN ns.vwclientes_atendimento t2_ ON t0_.participante = t2_.id

                            LEFT JOIN servicos.atendimentos t3_ ON t0_.atendimento = t3_.atendimento

                    

                WHERE t0_.proximocontato = :id

               

               ";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'timezone' => $timezone
        ]);
    }
}
