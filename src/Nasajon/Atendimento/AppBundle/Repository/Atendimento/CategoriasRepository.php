<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Atendimento;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Repository\Atendimento\CategoriasRepository as ParentRepository;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class CategoriasRepository extends ParentRepository {
  
    public function __construct(\Doctrine\DBAL\Connection $connection ){
        parent::__construct($connection);
        
        $this->setFilters([
          'descricao' => 'descricao',
          'status' => 'status',
          'titulo' => 'titulo'
        ]);
        
        $this->setLinks([    
            [
            'field' => 'categoriapai',
            'entity' => 'Nasajon\MDABundle\Entity\Atendimento\Categorias',
            'alias' => 't1_',
            'identifier' => 'categoria',
            ],
        ]);
    }

    // Find criado para possível validação no CategoriasVoter
    // para impedir o acesso, caso o artigo não esteja como público
    public function findCategoriaPublica($categoria, $tenant) {

        $sql = 'select 1 from atendimento.categorias
                where categoria = :categoria
                and tenant = :tenant
                and status = 1';

        $result = $this->getConnection()->executeQuery($sql,
        [
            'categoria' => $categoria,
            'tenant' => $tenant
        ])->fetch();

        return $result;

    }
    
    public function proccessFilter($filter) {
        $filters = [];
        $binds = [];
        if (!is_null($filter) && (!empty($filter->getKey()) || $filter->getKey() == "0") && !empty($filter->getField())) {
            $queryBuilder = $this->getConnection()->createQueryBuilder();
            if (strtolower($filter->getField()) == "all") {
                foreach ($this->getFilters() as $fi) {
                    $filters[] = $queryBuilder->expr()->comparison("unaccent(t0_.".$fi . '::text)', "ILIKE", "?");
                    $binds[] = "%" . StringUtils::removeAcentos($filter->getKey()) . "%";
                }
                $filters[] = $queryBuilder->expr()->comparison("unaccent(t1_.titulo::text)", "ILIKE", "?");
                $binds[] = "%" . StringUtils::removeAcentos($filter->getKey()) . "%";
                
                $filters[] = $queryBuilder->expr()->comparison("unaccent(t2_.titulo::text)", "ILIKE", "?");
                $binds[] = "%" . StringUtils::removeAcentos($filter->getKey()) . "%";
            } else {
                $filters[] = $queryBuilder->expr()->comparison("unaccent(t0_.".$this->filters[$filter->getField()] . '::text)', "ILIKE", "?");
                $binds[] = "%" . StringUtils::removeAcentos($filter->getKey()) . "%";
            }
        }
        return [$filters, $binds];
    }
   
    public function exibeQtdeArtigos($tenant) {
        $sql = "WITH
                categorias as (
                        SELECT c.categoria, c.titulo, c.ordem
                        FROM atendimento.categorias c
                        WHERE c.tenant = :tenant
                ),
                publicados AS (
                        SELECT c.categoria, COALESCE(count(a.*), 0) AS publicados
                        FROM categorias c
                        INNER JOIN atendimento.artigos a on a.categoria = c.categoria
                        where a.status = TRUE
                        GROUP BY c.categoria
                ),
                despublicados AS (
                        SELECT c.categoria, COALESCE(count(a.*), 0) AS despublicados
                        FROM categorias c
                        INNER JOIN atendimento.artigos a on a.categoria = c.categoria
                        where a.status = FALSE
                        GROUP BY c.categoria
                )
                SELECT c.categoria, c.titulo, c.ordem, COALESCE(p.publicados, 0) AS publicados, COALESCE(d.despublicados, 0) AS despublicados
                FROM categorias c
                LEFT JOIN despublicados d ON d.categoria = c.categoria
                LEFT JOIN publicados p ON p.categoria = c.categoria
                ORDER BY c.ordem ASC";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute(["tenant" => $tenant]);

        return $stmt->fetchAll();
    }

    public function findAllQueryBuilder($tenant, $tipo= "", $categoriapai= "", $status= "",  Filter $filter = null){
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
            't0_.categoria as categoria',
            't0_.titulo as titulo',
            't0_.descricao as descricao',
            't0_.status as status',
            't0_.tipo as tipo',
            't0_.tipoordenacao as tipoordenacao',
            't0_.ordem as ordem',
            // Busca as informações de filhos diretamente da tabela de artigos.
            // A tabela de resumos, além de estar muito lenta, estava trazendo informações divergentes.
            '(select count(1) from atendimento.artigos a where tenant = t0_.tenant AND categoria = t0_.categoria) as qtdfilhos',
            '(select count(1) from atendimento.artigos a where tenant = t0_.tenant AND categoria = t0_.categoria and "status" = true) as qtdartigospublicados',
            '(select count(1) from atendimento.artigos a where tenant = t0_.tenant AND categoria = t0_.categoria and "status" = false) as qtdartigosnaopublicados',
            //Feito desta forma para desonerar o banco, a concatenação esta sendo feita no Controller.
            // ' CONCAT_WS(\' - \', t2_.titulo ,t1_.titulo) as breadcrumb' ,
            't1_.titulo as titulo_t1',
            't2_.titulo as titulo_t2',
        ));
        $queryBuilder->from('atendimento.categorias', 't0_');
        $queryBuilder->leftJoin('t0_', 'atendimento.categorias', 't1_', 't0_.categoriapai = t1_.categoria AND t0_.tenant = t1_.tenant'); 
        $queryBuilder->leftJoin('t1_', 'atendimento.categorias', 't2_', 't1_.categoriapai = t2_.categoria AND t1_.tenant = t2_.tenant'); 
        $queryBuilder->addSelect(array(
            't1_.categoria as categoriapai_categoria',
            't1_.titulo as categoriapai_titulo',
            't1_.descricao as categoriapai_descricao',
            't1_.status as categoriapai_status',
            't1_.tipo as categoriapai_tipo',
            't1_.tipoordenacao as categoriapai_tipoordenacao',
            't1_.ordem as categoriapai_ordem'
        ));    
                            
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $tipo, $categoriapai, $status,  $filter);
        

        return [$queryBuilder, $binds];
    }

    /**
     * Busca os títulos das categorias, subcategorias e seções para renderizar na listagem.
     * 
     * @return array
     */
    public function buscarApenasTitulosDasCategorias($tenant)
    {
        $sql = 
        "SELECT t0_.categoria AS categoria, 
                t0_.titulo AS titulo, 
                t0_.descricao AS descricao, 
                t0_.status AS status, 
                t0_.tipo AS tipo, 
                t0_.tipoordenacao AS tipoordenacao, 
                t0_.ordem AS ordem,
                t1_.titulo as titulo_t1, 
                t1_.categoria as categoriapai_categoria, 
                t1_.titulo as categoriapai_titulo, 
                t1_.descricao as categoriapai_descricao, 
                t1_.status as categoriapai_status, 
                t1_.tipo as categoriapai_tipo, 
                t1_.tipoordenacao as categoriapai_tipoordenacao,
                t1_.ordem as categoriapai_ordem 

        FROM atendimento.categorias t0_ 
        LEFT JOIN atendimento.categorias t1_ ON t0_.categoriapai = t1_.categoria AND t0_.tenant = t1_.tenant 
        WHERE t0_.tenant = :tenant
        ORDER BY t0_.ordem ASC;";

        $stmt = $this->getConnection()->prepare($sql);

        $binds = [
            "tenant" => $tenant
        ];

        $stmt->execute($binds);

        $joins = ['categoriapai',];

        $result = array_map(function ($row) use ($joins) {
            if (count($joins) > 0) {
                foreach ($row as $key => $value) {
                    $parts = explode("_", $key);
                    $prefix = array_shift($parts);

                    if (in_array($prefix, $joins)) {
                        $row[$prefix][join("_", $parts)] = $value;
                        unset($row[$key]);
                    }
                }
            }
            return $row;
        }, $stmt->fetchAll());

        return $result;
    }
    
    public function artigosPorCategoriaPai($tenant, $categoriapai, $tipoCategoria = "", $tipoordenaca) {
        $where= [];
        $binds = [];
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
            't0_.artigo as artigo',
            't0_.titulo as titulo',
            't0_.categoriatitulo as categoriatitulo',
            't0_.categoria as categoria',
            't0_.subcategoriatitulo as subcategoriatitulo',
            't0_.subcategoria as subcategoria',
            't0_.secao as secao',
            't0_.secaotitulo as secaotitulo',
            't0_.tags as tags',
        ));
        $queryBuilder->from('atendimento.vwartigosativos_v2', 't0_');
        $where[] = $queryBuilder->expr()->eq('tenant', '?');
        $binds[] = $tenant;
        $where[] = $queryBuilder->expr()->eq('secao','?');
        $binds[] = $categoriapai;
        
        switch ($tipoordenaca) {
            case 1://manual
                $queryBuilder->addOrderBy('ordem', 'ASC');
//                dump("certo rapaz");die();
                break;
            case 2://recentes primeiro
                $queryBuilder->addOrderBy('created_at', 'DESC');
                break;
            case 3://antigos primeiro
                $queryBuilder->addOrderBy('created_at', 'ASC');
                break;
            case 4://ordem alfabetica
                $queryBuilder->addOrderBy('titulo', 'ASC');
                break;
        }
        
        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $stmt->execute($binds);
        return $stmt->fetchAll();
    }
    
    /**
    * @param string  $tenant
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Atendimento\Categorias $entity
    * @return string 
    * @throws \Exception
    */
    public function insert($tenant,$logged_user,  \Nasajon\MDABundle\Entity\Atendimento\Categorias $entity){

        $this->getConnection()->beginTransaction();
        try{
                    
            $sql_1 = "SELECT mensagem
            FROM atendimento.api_CategoriaNovo_v2(row(
                            :titulo,
                            :descricao,
                            :categoriapai,
                            :tipo,
                            :tenant,
                            :created_by,
                            :status,
                            :tipoordenacao
                        )::atendimento.tcategorianovo_v2
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("titulo", $entity->getTitulo());
            $stmt_1->bindValue("descricao", $entity->getDescricao());
            $stmt_1->bindValue("categoriapai", ($entity->getCategoriapai()) ? $entity->getCategoriapai(): NULL);
            $stmt_1->bindValue("tipo", $entity->getTipo());
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("created_by", json_encode($logged_user));
            $stmt_1->bindValue("status", $entity->getStatus());
            $stmt_1->bindValue("tipoordenacao", $entity->getTipoordenacao());
            
            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $retorno = $resposta;
            $entity->setCategoria($resposta);                    
            $retorno = $this->find($retorno , $tenant);
                    
            $this->getConnection()->commit();
        }catch(\Exception $e){
            $this->getConnection()->rollBack();
        throw $e;
        }

        return $retorno;
        }
    
    
      /**
      * @param string  $logged_user
      * @param string  $tenant
      * @param \Nasajon\MDABundle\Entity\Atendimento\Categorias $entity
      * @return string 
      * @throws \Exception
      */
      public function update($logged_user,$tenant,  \Nasajon\MDABundle\Entity\Atendimento\Categorias $entity){

        $this->getConnection()->beginTransaction();
        try{
                    
            $sql_1 = "SELECT mensagem
            FROM atendimento.api_CategoriaAlterar_v2(row(
                :categoria,
                :titulo,
                :descricao,
                :tipo,
                :categoriapai,
                :updated_by,
                :tenant,
                :status,
                :tipoordenacao
            )::atendimento.tcategoriaalterar_v2
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("categoria", $entity->getCategoria());
            $stmt_1->bindValue("titulo", $entity->getTitulo());
            $stmt_1->bindValue("descricao", $entity->getDescricao());
            $stmt_1->bindValue("tipo", $entity->getTipo());
            $stmt_1->bindValue("categoriapai", ($entity->getCategoriapai()) ? $entity->getCategoriapai(): NULL);
            $stmt_1->bindValue("updated_by", json_encode($logged_user));
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("status", $entity->getStatus());
            $stmt_1->bindValue("tipoordenacao", $entity->getTipoordenacao());
            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
            $retorno = $resposta;
                                                
            $this->getConnection()->commit();
        }catch(\Exception $e){
            $this->getConnection()->rollBack();
        throw $e;
        }

        return $retorno;
        }
        
        
        /**
        * @param string  $tenant
        * @param \Nasajon\MDABundle\Entity\Atendimento\Categorias $entity
        * @return string 
        * @throws \Exception
        */
        public function reordenar($tenant,  \Nasajon\MDABundle\Entity\Atendimento\Categorias $entity){

        $this->getConnection()->beginTransaction();
        try{
                    
            $sql_1 = "SELECT mensagem
            FROM atendimento.api_categoriasreordenar_v3(row(
                            :categoria,
                            :categoriapai,
                            :ordem,
                            :tenant
                        )::atendimento.tcategoriasreordenar_v2
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("categoria", $entity->getCategoria());
            $stmt_1->bindValue("categoriapai", ($entity->getCategoriapai()) ? $entity->getCategoriapai(): NULL);
            $stmt_1->bindValue("ordem", $entity->getOrdem());
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
            $retorno = $resposta;
                                                        
                                                
            $this->getConnection()->commit();
        }catch(\Exception $e){
            $this->getConnection()->rollBack();
            throw $e;
        }

            return $retorno;
        }
        
        
}
