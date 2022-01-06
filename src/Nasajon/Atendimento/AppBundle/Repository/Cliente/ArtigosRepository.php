<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Doctrine\Common\Collections\Criteria;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\ArtigosRepository as ParentRepository;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\ArtigosuteisRepository;
use Nasajon\MDABundle\Request\Filter;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;

class ArtigosRepository extends ParentRepository {
  
  /**
    *
    * @var ArtigosuteisRepository
    */
  private $artigosUteisRepository;
  
  public function __construct($connection, ArtigosuteisRepository $artigosUteisRepository) {
    parent::__construct($connection);
    $this->artigosUteisRepository = $artigosUteisRepository;
  }
  
  public function listDashboard($tenant) {
    $sql = "SELECT c.categoria, c.titulo, (
                        SELECT to_json(array_agg(sub)) as artigos FROM (SELECT a.titulo, a.artigo
                        FROM atendimento.artigos a
                        WHERE a.status = true
                        AND a.categoria = c.categoria
                        AND a.tenant = c.tenant
                        ORDER BY a.fixarnotopo DESC, a.published_at DESC
                        LIMIT :limit
                    ) as sub
                ) as artigos
                FROM atendimento.categorias c
                WHERE c.tenant = :tenant
                ORDER BY c.ordem ASC";

    $categorias = $this->getConnection()->executeQuery($sql, [
                'tenant' => $tenant,
                'limit' => 5
            ])->fetchAll();
    $categorias = array_map(function($categoria) {
      $categoria['artigos'] = json_decode($categoria['artigos']);
      if (count($categoria['artigos']) == 0) {
        return null;
      }
      return $categoria;
    }, $categorias);

    $categorias = array_filter($categorias, function($categoria) {
      return !is_null($categoria);
    });


    return $categorias;
  }

  public function proccessFilter($filter) {
    $resultado = [];

    if (!is_null($filter) && (!empty($filter->getKey()) || $filter->getKey() == '0') && !empty($filter->getField())) {
      $filtro = StringUtils::removeTabulacoes(StringUtils::removeln(StringUtils::removeCaracteresInvalidosNoTsQuery(strtolower(StringUtils::removeAcentos(html_entity_decode(strip_tags($filter->getKey())))))));
      $keys = explode(' ', $filtro);

      for ($i = 0; $i < count($keys); $i++) {
        $resultado[] = $i === (\count($keys) - 1) 
                  ? " (to_tsquery('simple','" . $keys[$i] . ":*'" . "))::tsquery" //se for o último adicionar :*
                  : " (to_tsquery('simple','" . $keys[$i] . "'))::tsquery "; //senão for o último crie sem :*
      }
    }

    return $resultado;
  }
  
  public function findAllQueryBuilder($tenant, $categoria= "", $subcategoria= "", $secao= "", $followup= "", $fixarnotopo= "", $tags= "",  $campoordenacao= "", $direcaoordenacao= "", $limite= "", $tipoexibicao = "", Filter $filter = null)
  {      
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(
          array(
            't0_.artigo as artigo',
            't0_.titulo as titulo',
            't0_.resumo as resumo',
            't0_.conteudo as conteudo',
            't0_.status as status',
            't0_.categoria as categoria',
            't0_.categoriatitulo as categoriatitulo',
            't0_.subcategoria as subcategoria',
            't0_.subcategoriatitulo as subcategoriatitulo',
            't0_.secao as secao',
            't0_.secaotitulo as secaotitulo',
            't0_.created_at as created_at',
            't0_.lastupdate as lastupdate',
            't0_.published_at as published_at',
            't0_.followup as followup',
            't0_.fixarnotopo as fixarnotopo',
            't0_.tags as tags',
            't0_.ordem as ordem',
            't0_.updated_at as updated_at',
            't0_.tipoexibicao as tipoexibicao',
          )
        );

        $queryBuilder->from('atendimento.vwartigosativos_v2', 't0_');
                        
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $categoria, $subcategoria, $secao, $followup, $fixarnotopo, $tags, $campoordenacao, $direcaoordenacao, $limite, $tipoexibicao, $filter);
        

        return [$queryBuilder, $binds];
    }

  public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $categoria = '', $subcategoria = '', $secao = '', $followup = '', $fixarnotopo = "", $tags="", $campoordenacao= "", $direcaoordenacao= "", $limite= "", $tipoexibicao = "", Filter $filter = null) {
    $binds = [];
    $where = [];

    if ( (strlen($campoordenacao) > 0) && (strlen($direcaoordenacao) > 0) ) {
       $queryBuilder->addOrderBy("t0_.".$campoordenacao."'", $direcaoordenacao);
    } elseif (!is_null($filter) && !empty($filter->getKey())) {
      $tsQueries = $this->proccessFilter($filter);
      if (!empty($tsQueries)) {
        $join = "(" . join("&&", $tsQueries) . ")";
        $tsrank = "ts_rank_cd(t0_.busca," . $join . ")";
        $rank = array("COALESCE(" . $tsrank . ", 0) as rank");
        $queryBuilder->addSelect($rank);
        $queryBuilder->addOrderBy('rank', 'DESC');
        $where[] = $queryBuilder->expr()->comparison('t0_.busca', '@@', "(" . join(" && ", $tsQueries) . ")");
      }
    } else {
      $queryBuilder->addOrderBy('t0_.updated_at', 'DESC');
    }

    $where[] = $queryBuilder->expr()->eq('t0_.tenant', '?');
    $binds[] = $tenant;

    if (strlen($categoria) > 0) {
      $where[] = $queryBuilder->expr()->eq('t0_.categoria', '?');
      $binds[] = $categoria;
    }

    if (strlen($followup) > 0) {
      $where[] = $queryBuilder->expr()->eq('t0_.followup', '?');
      $binds[] = $followup;
    }
    
    if (strlen($tags) > 0){
      $queryBuilder->leftJoin('t0_', 'atendimento.artigostags', 't2_', 't0_.artigo = t2_.artigo'); 
      $queryBuilder->leftJoin('t2_', 'atendimento.tags', 't3_', 't2_.tag = t3_.tag'); 
      $where[] = $queryBuilder->expr()->eq('t3_.texto', '?');
      $binds[] = $tags;
    }
    
    if(strlen($tipoexibicao) > 0){             
      $where[] = $queryBuilder->expr()->eq("t0_.tipoexibicao", "?");
      $binds[] = $tipoexibicao;
    }

    //$queryBuilder->setMaxResults(60);


    if (!is_null($filter) && empty($filter->getKey())) {

      if (strlen($limite) > 0) {
        $queryBuilder->setMaxResults($limite);
      } 

      if ($fixarnotopo === '0') {
        $where[] = $queryBuilder->expr()->eq("t0_.fixarnotopo", 'FALSE');        
//        $queryBuilder->setMaxResults(60);
      } else if ($fixarnotopo === '1') {
        $where[] = $queryBuilder->expr()->eq("t0_.fixarnotopo", "TRUE");
      }
    }else{
      $queryBuilder->setMaxResults(null);
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

    return $binds;
  }
  
  public function find($id, $tenant) {
    $resultado = parent::find($id, $tenant);
    $resultado['artigoutil'] = $this->artigosUteisRepository->findAll($tenant, $id);
    return $resultado;
  }

}