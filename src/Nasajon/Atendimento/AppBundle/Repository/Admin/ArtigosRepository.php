<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\Common\Collections\Criteria;
use Nasajon\MDABundle\Repository\Atendimento\Admin\ArtigosRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;

class ArtigosRepository extends ParentRepository {
  
  public function listTags($tags, $tenant){
    $types = [];
    $bindsArray = [];
    $values = [];
    
    $tags = array_values(array_unique($tags));

    foreach ($tags as $tags) {
        $types[] = \PDO::PARAM_STR;
        $bindsArray[] = '?';
        $values[] = mb_strtolower($tags);
    }
    
    $types[] = \PDO::PARAM_INT;
    $binds[] = '?';
    $values[] = $tenant;

    $sql = "SELECT texto FROM atendimento.tags "
            . "WHERE lower(texto) in ( " . implode(',', $bindsArray) . " ) and tenant = ? limit 10 ";
    return $this->getConnection()->executeQuery($sql, $values, $types)->fetchAll();
  }
  
  /**
   * @param string  $tenant
   * @param string  $logged_user
   * @param \Nasajon\MDABundle\Entity\Atendimento\Admin\Artigos $entity
   * @return string 
   * @throws \Exception
   */
  public function insert($tenant, $logged_user, \Nasajon\MDABundle\Entity\Atendimento\Admin\Artigos $entity) {
    $entity->setResumo(strip_tags($entity->getConteudo()));
    $entity->setConteudoTextual(strip_tags(html_entity_decode($entity->getConteudo())));

    $this->getConnection()->beginTransaction();
    try {
      $sql_1 = "SELECT mensagem
            FROM atendimento.api_ArtigoNovo_V7(row(
                            :categoria,
                            :titulo,
                            :resumo,
                            :conteudo,
                            :tenant,
                            :created_by,
                            :followup,
                            :fixarnotopo,
                            :conteudo_textual,
                            :criado_por_resposta,
                            :status,
                            :tags,
                            :tipoexibicao
                        )::atendimento.tartigonovo_v7
            );";

      $stmt_1 = $this->getConnection()->prepare($sql_1);
      $stmt_1->bindValue("categoria", ($entity->getSecao()) ? $entity->getSecao()->getCategoria() : NULL);
      $stmt_1->bindValue("titulo", $entity->getTitulo());
      $stmt_1->bindValue("resumo", $entity->getResumo());
      $stmt_1->bindValue("conteudo", $entity->getConteudo());
      $stmt_1->bindValue("tenant", $tenant);
      $stmt_1->bindValue("created_by", json_encode($logged_user));
      $stmt_1->bindValue("followup", $entity->getFollowup());
      $stmt_1->bindValue("fixarnotopo", $entity->getFixarnotopo(), \PDO::PARAM_BOOL);
      $stmt_1->bindValue("conteudo_textual", $entity->getConteudoTextual());
      $stmt_1->bindValue("criado_por_resposta", $entity->getCriadoPorResposta(), \PDO::PARAM_BOOL);
      $stmt_1->bindValue("status", $entity->getStatus(), \PDO::PARAM_BOOL);
      $stmt_1->bindValue("tags", !empty($entity->getTags()) ? json_encode($entity->getTags(), JSON_UNESCAPED_UNICODE) : '{}');
      $stmt_1->bindValue("tipoexibicao", $entity->getTipoExibicao());
      $stmt_1->execute();
      $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
      $retorno = $resposta;
      $entity->setArtigo($resposta);
      $retorno = $this->find($retorno, $tenant);
      $this->getConnection()->commit();
    } catch (\Exception $e) {
      $this->getConnection()->rollBack();
      throw $e;
    }
    return $retorno;
  }

  /**
   * @param string  $logged_user
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Atendimento\Admin\Artigos $entity
   * @return string 
   * @throws \Exception
   */
  public function update($logged_user, $tenant, \Nasajon\MDABundle\Entity\Atendimento\Admin\Artigos $entity) {
    $entity->setResumo(strip_tags($entity->getConteudo()));
    $entity->setConteudoTextual(strip_tags(html_entity_decode($entity->getConteudo())));
    $this->getConnection()->beginTransaction();
    try {

      $sql_1 = "SELECT mensagem
            FROM atendimento.api_ArtigoAlterar_V7(row(
                            :artigo,
                            :categoria,
                            :titulo,
                            :resumo,
                            :conteudo,
                            :updated_by,
                            :followup,
                            :fixarnotopo,
                            :conteudo_textual,
                            :status,
                            :tags,
                            :tenant,
                            :tipoexibicao                            
                        )::atendimento.tartigoalterar_v7
            );";

      $stmt_1 = $this->getConnection()->prepare($sql_1);
      $stmt_1->bindValue("artigo", $entity->getArtigo());
      // $stmt_1->bindValue("categoria", $entity->getCategoria());
      $stmt_1->bindValue("categoria", ($entity->getSecao()) ? $entity->getSecao()->getCategoria() : NULL);
      $stmt_1->bindValue("titulo", $entity->getTitulo());
      $stmt_1->bindValue("resumo", $entity->getResumo());
      $stmt_1->bindValue("conteudo", $entity->getConteudo());
      $stmt_1->bindValue("updated_by", json_encode($logged_user));
      $stmt_1->bindValue("followup", $entity->getFollowup());
      $stmt_1->bindValue("fixarnotopo", $entity->getFixarnotopo(), \PDO::PARAM_BOOL);
      $stmt_1->bindValue("conteudo_textual", $entity->getConteudoTextual());
      $stmt_1->bindValue("status", $entity->getStatus(), \PDO::PARAM_BOOL);
      $stmt_1->bindValue("tags", !empty($entity->getTags()) ? json_encode($entity->getTags(), JSON_UNESCAPED_UNICODE) : '{}');
      $stmt_1->bindValue("tenant", $tenant);
      $stmt_1->bindValue("tipoexibicao", $entity->getTipoExibicao());
      $stmt_1->execute();
      $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
      $retorno = $resposta;
      $this->getConnection()->commit();
    } catch (\Exception $e) {
      $this->getConnection()->rollBack();
      throw $e;
    }

    return $entity;
  }

  public function proccessFilter($filter) {
    $resultado = [];

    if (!is_null($filter) && (!empty($filter->getKey()) || $filter->getKey() == '0') && !empty($filter->getField())) {
      $filtro = StringUtils::removeTabulacoes(StringUtils::removeln(StringUtils::removeCaracteresInvalidosNoTsQuery(strtolower(StringUtils::removeAcentos(html_entity_decode(strip_tags($filter->getKey())))))));
      $keys = explode(' ', $filtro);

      for ($i = 0; $i < count($keys); $i++) {
        if (!empty($keys[$i])) {
          $resultado[] = $i === (\count($keys) - 1) ? " (to_tsquery('simple','" . $keys[$i] . ":*'" . "))::tsquery)" //se for o último adicionar :*
                  : " (to_tsquery('simple','" . $keys[$i] . "'))::tsquery "; //senão for o último crie sem :*
        }
      }
    }

    return $resultado;
  }
  
  public function findAllQueryBuilder($tenant, $followup= "", $status= "", $fixarnotopo= "", $tags= "",
                                      $categoria= "", $subcategoria= "", $secao= "", $orderfield= "", $tipoexibicao = "", Filter $filter = null){
        
        if($orderfield){
          $order = $filter ? $filter->getOrder() : Criteria::DESC;
          $this->setOffsets([
            $orderfield => [ "column" => $orderfield, "direction" => $order],
          ]);
        }
        
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->select(array(
            't0_.artigo as artigo',
            't0_.titulo as titulo',
            't0_.resumo as resumo',
            't0_.status as status',
            't0_.categoria as categoria',
            't0_.categoriatitulo as categoriatitulo',
            't0_.subcategoria as subcategoria',
            't0_.subcategoriatitulo as subcategoriatitulo',
            't0_.secaotitulo as secaotitulo',
            't0_.created_at as created_at',
            't0_.lastupdate as lastupdate',
            't0_.followup as followup',
            't0_.fixarnotopo as fixarnotopo',
            't0_.tags as tags',
            't0_.ordem as ordem',
            't0_.tipoexibicao as tipoexibicao',
            't0_.gostaram as gostaram',
            't0_.naogostaram as naogostaram',
        ));
        
        $queryBuilder->from('atendimento.vwartigos', 't0_');
        $queryBuilder->leftJoin('t0_', 'atendimento.categorias', 't1_', 't0_.secao = t1_.categoria'); 
        $queryBuilder->addSelect(array(
            't1_.categoria as secao_categoria',
            't1_.titulo as secao_titulo',
            't1_.descricao as secao_descricao',
            't1_.status as secao_status',
            't1_.tipo as secao_tipo',
            't1_.tipoordenacao as secao_tipoordenacao',
            't1_.ordem as secao_ordem',
        ));    
                           
        $binds = $this->findAllQueryBuilderBody($queryBuilder,$tenant, $followup, $status, $fixarnotopo, 
                                                $tags, $categoria, $subcategoria, $secao, $orderfield, $tipoexibicao, $filter);
        
        return [$queryBuilder, $binds];
    }

  public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $followup = "", $status = "", $fixarnotopo = "", $tags = "", $categoria = "", $subcategoria = "", $secao = "", $orderfield = "", $tipoexibicao = "", Filter $filter = null) {
    $binds = [];
    $where = [];

    // Flag para auxiliar na verificação se há um filtro de pesquisa ou não.
    // Caso tenha, não faz a filtragem pelo tipo de Categoria, Subcategoria ou Seção.
    $hasFilter = false;

    if (!empty($filter->getKey())) {
      $tsQueries = $this->proccessFilter($filter);

      if (count($tsQueries) > 0) {
        $count = count($tsQueries) - 1;
        $length = strlen($tsQueries[$count]);

        if (substr($tsQueries[$count], $length - 1) != ")") {
          $tsQueries[$count] .= ")";
        }
      }

      if (!empty($tsQueries)) {
        $hasFilter = true;

        $queryBuilder->addSelect(array("COALESCE(ts_rank_cd(t0_.busca," . join("&&", $tsQueries) . ", 0) as rank"));
        $queryBuilder->addOrderBy('rank', 'DESC');
        $where[] = $queryBuilder->expr()->gt("COALESCE(ts_rank_cd(t0_.busca," . join("&&", $tsQueries) . ", 0)", "0");
      }
    } else {
        if ($orderfield) {
          $orders = [
            ['column' => 't0_.ordem', 'direction' => 'ASC'],
            ['column' => 't0_.created_at', 'direction' => 'DESC'],
            ['column' => 't0_.created_at', 'direction' => 'ASC'],
            ['column' => 't0_.titulo', 'direction' => 'ASC'],
          ];

          // Caso o frontend tenha passado o número do tipo de ordenação:
          // 1 = Manualmente
          // 2 = Data de criação, recentes primeiro
          // 3 = Data de criação, antigos primeiro
          // 4 = Alfabética pelo título
          if (is_numeric($orderfield) && !$filter->getOrder()) {
            $queryBuilder->addOrderBy($orders[$orderfield - 1]['column'], $orders[$orderfield - 1]['direction']);
            
          } else {
            $queryBuilder->addOrderBy($orderfield, $filter->getOrder() ? $filter->getOrder():'ASC');
          }

        }else{
            $queryBuilder->addOrderBy('t0_.ordem', $filter->getOrder() ? $filter->getOrder():'ASC');
        }
    }
    
    $where[] = $queryBuilder->expr()->eq('t0_.tenant', '?');
    $binds[] = $tenant;

    if (strlen($followup) > 0) {
      $where[] = $queryBuilder->expr()->eq('t0_.followup', '?');
      $binds[] = $followup;
    }

    if (strlen($status) > 0) {
      $where[] = $queryBuilder->expr()->eq('t0_.status', '?');
      $binds[] = $status;
    }
    
    // Filtra por categoria, subcategoria ou seção.
    if (strlen($categoria) > 0) {
      $where[] = $queryBuilder->expr()->eq("t0_.categoria", "?");
      $binds[] = $categoria;
    }

    if (strlen($subcategoria) > 0) {
      $where[] = $queryBuilder->expr()->eq("t0_.subcategoria", "?");
      $binds[] = $subcategoria;
    }

    if (strlen($secao) > 0) {
        $where[] = $queryBuilder->expr()->eq("t0_.secao", "?");
        $binds[] = $secao;
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
    
//    $queryBuilder->setMaxResults(60);

//    list($offsets, $offsetsBinds) = $this->proccessOffset($filter);
//    $where = array_merge($where, $offsets);
//    $binds = array_merge($binds, $offsetsBinds);

    if (!empty($where)) {
      $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
    }
    if (!empty($filters)) {
      $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
    }

    return $binds;
  }


}
