<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\ModelBundle\Services\ConfiguracoesService;

class RelatoriosRepository extends AbstractRepository {

  /**
   *
   * @var EquipeClienteFilterService
   */
  protected $equipeFilter;

  public $configService;

  public function __construct($connection, EquipeClienteFilterService $equipeFilter, ConfiguracoesService $config) {
    parent::__construct($connection);
    $this->equipeFilter = $equipeFilter;

    $this->configService = $config;
  }

  public function process($resultado) {

    $total = [
        "qtdchamados" => 0,
        "respostas" => 0,
        "respostasoutros" => 0,
        "escaladas" => 0,
        "resolvidoprimeirocontato" => 0
    ];
    foreach ($resultado as $linha) {
      $total["qtdchamados"] += $linha["qtdchamados"];
      $total["respostas"] += $linha["respostas"];
      $total["respostasoutros"] += $linha["respostasoutros"];
      $total["escaladas"] += $linha["escaladas"];
      $total["resolvidoprimeirocontato"] += $linha["resolvidoprimeirocontato"];
    }
    
    return array_map(function($linha) use($total) {
      $linha['qtdchamados_perc'] = round(($linha['qtdchamados'] * 100) / $total['qtdchamados'], 2);
      $linha['resolvidoprimeirocontato_perc'] = round(($linha['resolvidoprimeirocontato'] * 100) / $linha['qtdchamados'], 2);
      $linha['resposta_media_por_chamado'] = round($linha['respostas'] / $linha['qtdchamados'], 2);
      if (isset($linha['chamados'])) {
        $linha['chamados'] = json_decode($linha['chamados']);
      } else {
        $linha['chamados'] = [];
      }
      return $linha;
    }, $resultado);
  }

    public function processFilter($filter) {
        $condicoes = $filter['condicoes'];
        if(!empty($condicoes)) {
            $filter['condicoes'] = array_map(function($condicao) {
                if($condicao['campo'] == 'data_publicacao' && ($condicao['valor'] == 'false' || is_null($condicao['valor'])) && is_null($condicao['valor2']))
                {
                   $condicao['operador'] = 'is_null';
                }
                return $condicao;
            }, $condicoes);
          };

        $binds = [];
        $i = 0;
        $stringCondicoes = "";
        foreach ($filter['condicoes'] as $condicao) {
            switch ($condicao['campo']) {
                case "usuario":
                case "fila":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes['responsavel_web'][] = "responsavel_web = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes['responsavel_web'][] = "responsavel_web <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "situacao":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes['situacao'][] = "a.situacao = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes['situacao'][] = "a.situacao <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "usuariofollowup":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes["F.CREATED_BY->>'email'"][] = "F.CREATED_BY->>'email' = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes["F.CREATED_BY->>'email'"][] = "F.CREATED_BY->>'email' <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "representante_tecnico":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes["cliente.REPRESENTANTE_TECNICO"][] = "cliente.REPRESENTANTE_TECNICO = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes["cliente.REPRESENTANTE_TECNICO"][] = "cliente.REPRESENTANTE_TECNICO <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "equipe":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = " eu.equipe = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = " eu.equipe <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "cliente":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "participante = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "participante <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "canal":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "canal = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "canal <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "categoria":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "c.categoria = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "c.categoria <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break; 
                case "subcategoria":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "sub.categoria = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "sub.categoria <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;    
                case "secao":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "sec.categoria = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "sec.categoria <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "criador":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "a.created_by->>'email' = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "a.created_by->>'email' <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "criado_por_resposta":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "a.criado_por_resposta = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "a.criado_por_resposta <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "conteudo_resumo":
                    switch ($condicao['operador']) {
                        case 'includes':
                            if (!empty($condicao['valor'])) {
                                $cleanTitle = StringUtils::removeTabulacoes(StringUtils::removeCaracteresInvalidosNoTsQuery(strtolower(StringUtils::removeAcentos($condicao['valor']))));
                                $keys = explode(" ", $cleanTitle);
                                $keysSql = "";
                                $keysSql .= "a.resumo_admin ilike '%" . $condicao['valor'] . "%'";
                                $condicoes[$condicao['campo']][] = $keysSql;
                            }
                            
                            break;
                    }
                    break;
                case "conteudo_resposta":
                case "titulo_artigo":
                    switch ($condicao['operador']) {
                        case 'includes':
                            if (!empty($condicao['valor'])) {
                                $cleanTitle = StringUtils::removeTabulacoes(StringUtils::removeCaracteresInvalidosNoTsQuery(strtolower(StringUtils::removeAcentos($condicao['valor']))));
                                $keys = explode(" ", $cleanTitle);
                                $keysSql = "";
                                $keysSql .= "a.titulo ilike '%" . $condicao['valor'] . "%'";
                                $condicoes[$condicao['campo']][] = $keysSql;
                            }
                            
                            break;
                    }
                    break;
                case "cliente_resposta":
                    switch ($condicao['operador']) {
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "cliente.ID = :filter_" . $i;
                            break;
                        case 'is_not_equal':
                            $condicoes[$condicao['campo']][] = "cliente.ID <> :filter_" . $i;
                            break;
                    }
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                case "data_criacao":
                    switch ($condicao['operador']) {
                        case 'is_between':
                            $condicoes[$condicao['campo']][] = "a.created_at BETWEEN +:filter_".$i." and +:filter_".($i + 1);
                            $binds['filter_' . $i] = $condicao['valor'] . " 00:00:00";
                            $binds['filter_' . ($i + 1)] = $condicao['valor2'] . " 23:59:59";
                            $i++;
                            break;
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "a.created_at::DATE = :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_greater':
                            $condicoes[$condicao['campo']][] = "a.created_at::date >= :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_smaller':
                            $condicoes[$condicao['campo']][] = "a.created_at::date <= :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                    }
                    break;
                case "data_publicacao":
                    switch ($condicao['operador']) {
                        case 'is_between':
                            $condicoes[$condicao['campo']][] = "a.published_at BETWEEN +:filter_".$i." and +:filter_".($i + 1);
                            $binds['filter_' . $i] = $condicao['valor'] . " 00:00:00";
                            $binds['filter_' . ($i + 1)] = $condicao['valor2'] . " 23:59:59";
                            $i++;
                            break;
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "a.published_at::DATE = :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_greater':
                            $condicoes[$condicao['campo']][] = "a.published_at::date >= :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_smaller':
                            $condicoes[$condicao['campo']][] = "a.published_at::date <= :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_null':
                            $condicoes[$condicao['campo']][] = "a.status = :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                    }
                    break;
                case "data_atualizacao":
                    switch ($condicao['operador']) {
                        case 'is_between':
                            $condicoes[$condicao['campo']][] = "a.updated_at BETWEEN +:filter_".$i." and +:filter_".($i + 1);
                            $binds['filter_' . $i] = $condicao['valor'] . " 00:00:00";
                            $binds['filter_' . ($i + 1)] = $condicao['valor2'] . " 23:59:59";
                            $i++;
                            break;
                        case 'is_equal':
                            $condicoes[$condicao['campo']][] = "a.updated_at::DATE = :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_greater':
                            $condicoes[$condicao['campo']][] = "a.updated_at::date >= :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                        case 'is_smaller':
                            $condicoes[$condicao['campo']][] = "a.updated_at::date <= :filter_".$i;
                            $binds['filter_' . $i] = $condicao['valor'];
                            break;
                    }
                    break;
                case "resposta":
                    switch ($condicao['operador']) {
                        case 'includes':
                            if (empty($condicao['valor'])) return;                            
                            $condicoes[$condicao['campo']][] = " F.HISTORICO = :filter_" . $i;
                            break;
                    }
                    
                    $binds['filter_' . $i] = $condicao['valor'];
                    break;
                default:
                    switch ($condicao['operador']) {
                        case 'is_equal':
                          $condicoes[$condicao['campo']][] = "camposcustomizados->>:campo_" . $i . " = :filter_" . $i;
                          $binds['filter_' . $i] = $condicao['valor'];
                          break;
                        case 'is_not_equal':
                          $condicoes[$condicao['campo']][] = "camposcustomizados->>:campo_" . $i . " <> :filter_" . $i;
                          $binds['filter_' . $i] = $condicao['valor'];
                          break;
                        case 'is_set':
                          $condicoes[$condicao['campo']][] = "(camposcustomizados->>:campo_" . $i . ") IS NOT NULL AND (camposcustomizados->>:campo_" . $i . " <> '')";
                          break;
                        case 'is_not_set':
                          $condicoes[$condicao['campo']][] = "(camposcustomizados->>:campo_" . $i . ") IS NULL OR (camposcustomizados->>:campo_" . $i . " = '')";
                          break;
                        case 'includes':
                            $condicoes[$condicao['campo']][] = "camposcustomizados->>:campo_" . $i . " ilike '%" . $condicao['valor'] . "%'";
                    }
 
 
           $binds['campo_' . $i] = $condicao['campo'];
       }
       $i++;
        }
        
        // Correção das cláusulas Where na query
        foreach ($condicoes as $c => $value) {
            // Filtra as condições de campo = valor
            $eq = array_filter($value, function($sql) {
                return strpos($sql, '=');
            });
            // Filtra as condições de campo <> valor
            $nEq = array_filter($value, function($sql) {
                return strpos($sql, '<>');
            });
            // Filtra as condições de campo + (between)
            $iBt = array_filter($value, function($sql) {
                return strpos($sql, '+');
            });

            // Filtra as condições de ilike
            $iLike = array_filter($value, function($sql) {
                return strpos($sql, 'ilike');
            });

            $strEq = '';
            $strNeq = '';
            $striBt = '';
            $strILike = '';

            // $striBt = '';
            // Monta a query de valor = valor com um OR
            if (count($eq)) {
                $strEq = 'AND ('.join($eq, " OR ").')';
            }

            if (count($iBt)) {
                $striBt = 'AND ('.join($iBt, " AND ").')';
                $striBt = str_replace('+',' ', $striBt); //Remove os +
            }

            // Monta a query de valor + valor com um AND
            if (count($nEq)) {
                $strNeq = 'AND ('.join($nEq, " AND ");

                if ($c === 'equipe') {
                    $strNeq .= ' OR eu.equipe IS NULL';
                }

                $strNeq .= ')';
            }

            if (count($iLike)) {
                $strILike = 'AND ('.join($iLike, " OR ").')';
            }

            // Concatena as queries
            $stringCondicoes .= "$strEq $strNeq $striBt $strILike";
        }

    return [$stringCondicoes, $binds];
  }

  public function relatorioBaseConhecimento($tenant, $filter) {
    $condicoesSQL = "";
    $binds = [];
    
    if (!empty($filter)) {
      list($condicoesSQL, $binds) = $this->processFilter($filter);
    }

    $sql = "SELECT a.published_at, a.artigo AS artigoid, a.titulo AS tituloartigo, c.categoria AS categoriaid, c.titulo as titulocategoria, 
                        a.created_by->>'nome' as created_by, a.created_at, a.updated_at, 
                        sub.categoria AS subcategoriaid, sub.titulo as titulosubcategoria,
                        sec.categoria AS secaoid, sec.titulo as titulosecao,
                        COALESCE(SUM(a.gostaram), 0) AS gostaram, COALESCE(SUM(a.naogostaram), 0) AS naogostaram,
                        COALESCE(SUM(a.qtd_respostas_chamados), 0) AS respostaschamados, a.criado_por_resposta, a.updated_at, a.qtd_evitou_criacao_chamados

                FROM atendimento.artigos a

                INNER JOIN atendimento.categorias sec ON sec.categoria = a.categoria
                INNER JOIN atendimento.categorias sub ON sub.categoria = sec.categoriapai           
                INNER JOIN atendimento.categorias c ON c.categoria = sub.categoriapai
                
                WHERE a.tenant = :tenant " . $condicoesSQL . "
                
                GROUP BY 
                        a.artigo, a.titulo, c.categoria, c.titulo, a.created_by->>'nome', a.created_at, a.criado_por_resposta, a.updated_at, sub.categoria, sub.titulo, sec.categoria, sec.titulo, a.qtd_evitou_criacao_chamados
                        
                ORDER BY 
                    a.updated_at DESC;";

    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant
    ]));

    return $stmt->fetchAll();
  }

  public function relatorioBaseConhecimentoTotalizadores($tenant, $filter) {        
    $condicoesSQL = "";
    $binds = [];
    
    if (!empty($filter)) {
      list($condicoesSQL, $binds) = $this->processFilter($filter);
    }

    $sql = "SELECT 
                COALESCE(COUNT(*), 0) AS artigos,
                COALESCE(SUM(a.gostaram), 0) AS gostaram, 
                COALESCE(SUM(a.naogostaram), 0) AS naogostaram,
                COALESCE(SUM(a.qtd_respostas_chamados), 0) AS respostaschamados,
                COALESCE(SUM(a.qtd_evitou_criacao_chamados), 0) AS respostasimediatas
            FROM atendimento.artigos a

            INNER JOIN atendimento.categorias sec ON sec.categoria = a.categoria
            INNER JOIN atendimento.categorias sub ON sub.categoria = sec.categoriapai           
            INNER JOIN atendimento.categorias c ON c.categoria = sub.categoriapai

            WHERE a.tenant = :tenant " . $condicoesSQL;

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant
    ]));

    return $stmt->fetch();
  }

  public function montarWherePorGrupoEmpresarial($config) {
    $grupos_empresariais = explode(",", trim($config));

    $size = -1;

    $strWhere = '';

    if(($size = sizeof($grupos_empresariais)) > 0){

        $strWhere = " AND (";

        for ($i = 0; $i < count($grupos_empresariais) - 1; $i++) {
        $guid = $grupos_empresariais[$i];

            if(!empty($guid) && StringUtils::isGuid($guid)) {
                $strWhere .= "cliente.grupoempresarial = '".$guid."' OR ";
            }
        }
        
        if(!empty($grupos_empresariais[$size - 1]) && StringUtils::isGuid($grupos_empresariais[$size - 1])) {
            $strWhere .= "cliente.grupoempresarial = '".$grupos_empresariais[$size - 1]."')";
        }
    }

    return $strWhere;
  }

  public function relatorioRespostasTotalizadores($tenant, $filter) {
    $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');
    
    $viewClientesAtendimento = "ns.vwclientes_atendimento";
    $condicoesSqlPorGrupoEmpresarial = "";

    if (!empty($config)) {
        $viewClientesAtendimento = "ns.vwclientes_atendimento_v2";
        $condicoesSqlPorGrupoEmpresarial = $this->montarWherePorGrupoEmpresarial($config);
    }

    list($condicoesSQL, $binds) = $this->processFilter($filter);
    
    $filtroEquipe = (string) $this->equipeFilter->run('cliente');
    
    if (!empty($filtroEquipe)) {
      $condicoesSQL .= " AND (" . $filtroEquipe . ") ";
    }

    $timezone = $this->configService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
    $timezone = $timezone ? $timezone : 'America/Sao_Paulo';

    // Retorna, além das respostas, dos chamados e dos clientes, a quantidade de comentários e o somatório de respostas e comentários
    $sql = "SELECT 
    COUNT(DISTINCT RESPOSTAS.followup) as QTD_RESPOSTAS, 
    COUNT(DISTINCT A.ATENDIMENTO) AS QTD_CHAMADOS, 
    COUNT(DISTINCT A.PARTICIPANTE) AS QTD_CLIENTES, 
    COALESCE(COUNT(DISTINCT COMENTARIOS.followup), 0) as QTD_COMENTARIOS, 
    COALESCE(COUNT(DISTINCT RESPOSTAS.followup) + COUNT(DISTINCT COMENTARIOS.followup), 0) AS QTD_RESPOSTAS_COMENTARIOS
    FROM  NS.FOLLOWUPS F
    INNER JOIN SERVICOS.ATENDIMENTOS A ON A.TENANT = F.TENANT AND A.ATENDIMENTO = F.ATENDIMENTO
    LEFT JOIN ".$viewClientesAtendimento." cliente ON F.PARTICIPANTE = cliente.ID
    LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = F.CREATED_BY->>'email'
    LEFT JOIN ns.followups RESPOSTAS on RESPOSTAS.followup = F.followup and RESPOSTAS.tenant = F.tenant and RESPOSTAS.tipo = 0
    LEFT JOIN ns.followups COMENTARIOS on COMENTARIOS.followup = F.followup and COMENTARIOS.tenant = F.tenant and COMENTARIOS.tipo = 1
    WHERE F.TENANT = :tenant
    AND F.DATA AT TIME ZONE '$timezone' >= :datainicial
    AND F.DATA AT TIME ZONE '$timezone' <= :datafinal" . $condicoesSQL . $condicoesSqlPorGrupoEmpresarial;

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant,
        "datainicial" => $filter['datainicial'] . ' 00:00:00',
        "datafinal" => $filter['datafinal'] . ' 23:59:59'
    ]));

    return $stmt->fetchAll();
  }

  public function relatorioRespostas($tenant, $filter) {
    $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');
    
    $viewClientesAtendimento = "ns.vwclientes_atendimento";
    $condicoesSqlPorGrupoEmpresarial = "";

    if (!empty($config)) {
        $viewClientesAtendimento = "ns.vwclientes_atendimento_v2";
        $condicoesSqlPorGrupoEmpresarial = $this->montarWherePorGrupoEmpresarial($config);
    }

    list($condicoesSQL, $binds) = $this->processFilter($filter);

    $filtroEquipe = (string) $this->equipeFilter->run('cliente');

    if ($filter['created_at'] != null) {
      $condicoesSQL .= " AND (F.DATA < '" . $filter['created_at'] . "')";
    }

    if ($filter['resumo'] != null) {
        $condicoesSQL .= " AND a.resumo_admin ilike '%" . $filter['resumo']. "%'";
      }

    if (!empty($filtroEquipe)) {
      $condicoesSQL .= " AND (" . $filtroEquipe . ") ";
    }

    $timezone = $this->configService->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
    $timezone = $timezone ? $timezone : 'America/Sao_Paulo';

    $sql = "SELECT DISTINCT F.FOLLOWUP, F.CREATED_BY->>'email' AS USUARIO, A.NUMEROPROTOCOLO, F.ATENDIMENTO, 
    F.DATA AS CREATED_AT, 
    cliente.NOME AS CLIENTE, cliente.ID AS CLIENTE_ID, F.HISTORICO AS RESPOSTA, A.RESUMO_ADMIN, F.TIPO 
    FROM NS.FOLLOWUPS F
    INNER JOIN SERVICOS.ATENDIMENTOS A ON A.TENANT = F.TENANT AND A.ATENDIMENTO = F.ATENDIMENTO
    LEFT JOIN " . $viewClientesAtendimento . " cliente ON F.PARTICIPANTE = cliente.ID
    LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = F.CREATED_BY->>'email'
    WHERE F.TENANT = :tenant " . $condicoesSQL . $condicoesSqlPorGrupoEmpresarial . "
    AND F.DATA AT TIME ZONE '$timezone' BETWEEN :datainicial  and :datafinal
    ORDER BY F.DATA DESC
    LIMIT 50;";

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant,
        "datainicial" => $filter['datainicial'] . ' 00:00:00',
        "datafinal" => $filter['datafinal'] . ' 23:59:59'
    ]));

    $result = $stmt->fetchAll();

    for ($i = 0; $i < count($result); $i++) {
      $result[$i]['resposta'] = strip_tags($result[$i]['resposta']);
      $result[$i]['resposta'] = substr($result[$i]['resposta'], 0, 200);
    }

    return $result;
  }

  public function performanceUsuario($tenant, $filter) {
    $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');
    
    $viewClientesAtendimento = "ns.vwclientes_atendimento";
    $condicoesSqlPorGrupoEmpresarial = "";

    if (!empty($config)) {
        $viewClientesAtendimento = "ns.vwclientes_atendimento_v2";
        $condicoesSqlPorGrupoEmpresarial = $this->montarWherePorGrupoEmpresarial($config);
    }

    list($condicoesSQL, $binds) = $this->processFilter($filter);

    $filtroEquipe = (string) $this->equipeFilter->run('cliente');
    if (!empty($filtroEquipe)) {
      $condicoesSQL .= " AND (" . $filtroEquipe . ") ";
    }
    $sql = "WITH atendimentos AS (
                        SELECT a.atendimento, 
                        CASE WHEN ns.isuuid(responsavel_web) THEN NULL ELSE a.responsavel_web END, 
                        a.tenant, 
                        a.datacriacao::date as datacriacao, 
                        a.situacao, 
                        a.canal,
                        a.qtd_respostas,
                        a.qtd_respostas_outros
                        FROM servicos.atendimentos a
                        LEFT JOIN ".$viewClientesAtendimento." cliente ON a.participante = cliente.id   
                        LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = a.responsavel_web and eu.tenant = a.tenant
                        WHERE a.tenant = :tenant
                        AND a.datacriacao::date BETWEEN (:datainicial )::date  and (:datafinal)::date 
                        " . $condicoesSQL . $condicoesSqlPorGrupoEmpresarial . "
                        group by atendimento
                ),
                escalacoes AS (
                        SELECT count(ah.*) AS qtd, a.atendimento
                        FROM atendimentos a
                        LEFT JOIN servicos.atendimentoshistoricos ah ON ah.atendimento = a.atendimento AND ah.tipo = 4	
                        GROUP BY a.atendimento
                ),
                usuarios AS (
                        SELECT distinct responsavel_web 
                        FROM atendimentos
                ),
                datas as (
                        SELECT generate_series(( :datainicial )::date, ( :datafinal )::date, '1 day'::interval)::date as dia, responsavel_web
                        FROM usuarios
                ),
                chamados_dia as (
                        SELECT d.responsavel_web, d.dia, count(a.*) AS qtd 
                        FROM datas d
                        LEFT JOIN atendimentos a ON a.datacriacao = d.dia AND (a.responsavel_web = d.responsavel_web OR (d.responsavel_web IS NULL AND  a.responsavel_web IS NULL))
                        GROUP BY d.responsavel_web, d.dia
                ),
                chamados AS (
                        SELECT responsavel_web, array_agg(qtd ORDER BY dia ASC) AS chamados
                        FROM chamados_dia
                        GROUP BY responsavel_web
                )
                SELECT
			count(*) AS qtdChamados, 
			sum(a.qtd_respostas) AS respostas, 
			COALESCE(a.responsavel_web, '<vazio>') AS usuario, 
			sum(a.qtd_respostas_outros) AS respostasoutros,
			COALESCE(sum(e.qtd), 0) AS escaladas, 
			COALESCE(sum(CASE WHEN a.qtd_respostas = 1 THEN 1 ELSE 0 END ), 0) AS resolvidoprimeirocontato,
			to_json(g.chamados) as chamados
                FROM atendimentos a
                LEFT JOIN escalacoes e ON e.atendimento = a.atendimento
                LEFT JOIN chamados g ON g.responsavel_web = a.responsavel_web OR (g.responsavel_web IS NULL AND  a.responsavel_web IS NULL)
                GROUP BY a.responsavel_web, g.chamados
                ORDER BY qtdchamados DESC";

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant,
        "datainicial" => $filter['datainicial'],
        "datafinal" => $filter['datafinal']
    ]));

    return $this->process($stmt->fetchAll());
  }

  public function performanceFila($tenant, $filter) {
    list($condicoesSQL, $binds) = $this->processFilter($filter);
    $filtroEquipe = (string) $this->equipeFilter->run('cliente');
    if (!empty($filtroEquipe)) {
      $condicoesSQL .= " AND (" . $filtroEquipe . ") ";
    }
    $sql = "WITH atendimentos AS (
                        SELECT a.atendimento,  
                        CASE WHEN a.responsavel_web_tipo = 2 THEN a.responsavel_web ELSE NULL END as responsavel_web, 
                        a.tenant, 
                        a.datacriacao::date as datacriacao,
                        a.situacao,
                        a.canal,
                        a.qtd_respostas,
                        a.qtd_respostas_outros
                        FROM servicos.atendimentos a
                        LEFT JOIN ns.pessoas cliente ON a.participante = cliente.id
                        LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = a.responsavel_web
                        WHERE a.tenant = :tenant
                        AND a.datacriacao::date BETWEEN (  :datainicial )::date  and ( :datafinal )::date 
                        " . $condicoesSQL . "
                ),
                escalacoes AS (
                        SELECT count(ah.*) AS qtd, a.atendimento
                        FROM atendimentos a
                        LEFT JOIN servicos.atendimentoshistoricos ah ON ah.atendimento = a.atendimento AND ah.tipo = 4	
                        GROUP BY a.atendimento
                ),
                usuarios AS (
                        SELECT distinct responsavel_web 
                        FROM atendimentos
                ),
                datas as (
                        SELECT generate_series(( :datainicial )::date, ( :datafinal )::date, '1 day'::interval)::date as dia, responsavel_web
                        FROM usuarios
                ),
                chamados_dia as (
                        SELECT d.responsavel_web, d.dia, count(a.*) AS qtd 
                        FROM datas d
                        LEFT JOIN atendimentos a ON a.datacriacao = d.dia AND (a.responsavel_web = d.responsavel_web OR (d.responsavel_web IS NULL AND  a.responsavel_web IS NULL))
                        GROUP BY d.responsavel_web, d.dia
                ),
                chamados AS (
                        SELECT responsavel_web, array_agg(qtd ORDER BY dia ASC) AS chamados
                        FROM chamados_dia
                        GROUP BY responsavel_web
                )
                SELECT
			COUNT(*) AS qtdChamados, 
			sum(a.qtd_respostas) AS respostas, 
			a.responsavel_web as id, 
			COALESCE(af.nome, '<vazio>') as fila, 
			sum(a.qtd_respostas_outros) AS respostasoutros,
			COALESCE(sum(e.qtd), 0) AS escaladas, 
			COALESCE(sum(CASE WHEN a.qtd_respostas = 1 THEN 1 ELSE 0 END ), 0) AS resolvidoprimeirocontato,
			to_json(g.chamados) as chamados
                FROM atendimentos a
                LEFT JOIN escalacoes e ON e.atendimento = a.atendimento
                LEFT JOIN chamados g ON g.responsavel_web = a.responsavel_web OR (g.responsavel_web IS NULL AND  a.responsavel_web IS NULL)
                LEFT JOIN servicos.atendimentosfilas af ON af.atendimentofila::character varying = a.responsavel_web
                GROUP BY a.responsavel_web, g.chamados, af.nome
                ORDER BY qtdchamados DESC";

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant,
        "datainicial" => $filter['datainicial'],
        "datafinal" => $filter['datafinal']
    ]));

    return $this->process($stmt->fetchAll());
  }

  public function performanceCliente($tenant, $filter) {

    $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

    $viewClientesAtendimento = "ns.vwclientes_atendimento";
    $condicoesSqlPorGrupoEmpresarial = "";

    if (!empty($config)) {
        $viewClientesAtendimento = "ns.vwclientes_atendimento_v2";
        $condicoesSqlPorGrupoEmpresarial = $this->montarWherePorGrupoEmpresarial($config);
    }
    
    list($condicoesSQL, $binds) = $this->processFilter($filter);
    $filtroEquipe = (string) $this->equipeFilter->run('cliente');
    if (!empty($filtroEquipe)) {
      $condicoesSQL .= " AND (" . $filtroEquipe . ") ";
    }
    
    $sql = "WITH atendimentos as (
                    SELECT  cliente.codigo cliente_codigo,
                            a.atendimento, 
                            CASE WHEN ns.isuuid(a.responsavel_web) THEN NULL ELSE a.responsavel_web END, 
                            a.tenant, 
                            a.participante as cliente, 
                            a.camposcustomizados, 
                            a.datacriacao::date as datacriacao,
                            a.situacao,
                            a.canal,
                            a.qtd_respostas,
                            a.qtd_respostas_outros,
                            coalesce(coalesce(cliente.nome,cliente.nomefantasia),'<vazio>') as cliente_nome
                    FROM servicos.atendimentos a
                    LEFT JOIN ".$viewClientesAtendimento." cliente ON a.participante = cliente.id
                    LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = a.responsavel_web
                    WHERE a.tenant = :tenant
                    AND a.datacriacao::date BETWEEN (  :datainicial )::date  and ( :datafinal )::date 
                    " . $condicoesSQL . $condicoesSqlPorGrupoEmpresarial ."
                ),
                escalacoes AS (
                        SELECT count(ah.*) AS qtd, a.atendimento
                        FROM atendimentos a
                        LEFT JOIN servicos.atendimentoshistoricos ah ON ah.atendimento = a.atendimento AND ah.tipo = 4	
                        GROUP BY a.atendimento
                ),
                clientes AS (
                        SELECT distinct cliente 
                        FROM atendimentos
                ),
                datas as (
                        SELECT generate_series(( :datainicial )::date, ( :datafinal )::date, '1 day'::interval)::date as dia, cliente
                        FROM clientes
                ),
                chamados_dia as (
                        SELECT d.cliente, d.dia, count(a.*) AS qtd 
                        FROM datas d
                        LEFT JOIN atendimentos a ON a.datacriacao = d.dia AND a.cliente = d.cliente
                        GROUP BY d.cliente, d.dia
                ),
                chamados AS (
                        SELECT cliente, array_agg(qtd ORDER BY dia ASC) AS chamados
                        FROM chamados_dia
                        GROUP BY cliente
                )
                SELECT 
			count(*) as qtdchamados, 
                        sum(a.qtd_respostas) AS respostas, 
                        a.cliente_codigo as codigo,
                        a.cliente,                        
                        a.cliente_nome as nome,  
                        sum(a.qtd_respostas_outros) AS respostasoutros,
                        coalesce(sum(e.qtd), 0) as escaladas, 
                        COALESCE(sum(CASE WHEN a.qtd_respostas = 1 THEN 1 ELSE 0 END ), 0) AS resolvidoprimeirocontato,
                        to_json(g.chamados) as chamados
                FROM atendimentos a
                LEFT JOIN escalacoes e on e.atendimento = a.atendimento
                LEFT JOIN chamados g ON g.cliente = a.cliente
                GROUP BY a.cliente, a.cliente_nome, a.cliente_codigo, g.chamados
                ORDER BY qtdchamados DESC";

    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant,
        "datainicial" => $filter['datainicial'],
        "datafinal" => $filter['datafinal']
    ]));

    return $this->process($stmt->fetchAll());
  }

  public function performanceCampocustomizado($tenant, $filter) {
    list($condicoesSQL, $binds) = $this->processFilter($filter);
    $filtroEquipe = (string) $this->equipeFilter->run('cliente');
    if (!empty($filtroEquipe)) {
      $condicoesSQL .= " AND (" . $filtroEquipe . ") ";
    }
    $sql = "WITH atendimentos as (
                        select a.atendimento, CASE WHEN ns.isuuid(a.responsavel_web) THEN NULL ELSE a.responsavel_web END,
                                a.tenant, a.participante as cliente, a.camposcustomizados->>:campocustomizado as campocustomizado, 
                                a.datacriacao::date as datacriacao, 
                                a.situacao,
                                a.canal,
				a.qtd_respostas,
				a.qtd_respostas_outros
                        from servicos.atendimentos  a
                        LEFT JOIN ns.pessoas cliente ON a.participante = cliente.id
                        LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = a.responsavel_web
                        where a.tenant = :tenant
                        and a.datacriacao::date between ( :datainicial )::date  and ( :datafinal )::date
                        " . $condicoesSQL . "
                ),
                escalacoes AS (
                       SELECT count(ah.*) AS qtd, a.atendimento
                       FROM atendimentos a
                       LEFT JOIN servicos.atendimentoshistoricos ah ON ah.atendimento = a.atendimento AND ah.tipo = 4	
                       GROUP BY a.atendimento
                ),                
                campos AS (
                       SELECT distinct campocustomizado 
                       FROM atendimentos
                ),
                datas as (
                       SELECT generate_series(( :datainicial )::date, ( :datafinal )::date, '1 day'::interval)::date as dia, campocustomizado
                       FROM campos
                ),
                chamados_dia as (
                       SELECT d.campocustomizado, d.dia, count(a.*) AS qtd 
                       FROM datas d
                       LEFT JOIN atendimentos a ON a.datacriacao = d.dia AND a.campocustomizado = d.campocustomizado
                       GROUP BY d.campocustomizado, d.dia
                ),
                chamados AS (
                       SELECT campocustomizado, array_agg(qtd ORDER BY dia ASC) AS chamados
                       FROM chamados_dia
                       GROUP BY campocustomizado
                )
                SELECT 
			count(*) as qtdchamados, 
			sum(a.qtd_respostas) AS respostas, 
			sum(a.qtd_respostas_outros) AS respostasoutros,
			coalesce(sum(e.qtd), 0) as escaladas, 
			coalesce(a.campocustomizado,'<vazio>') as campocustomizado, 
			COALESCE(sum(CASE WHEN a.qtd_respostas = 1 THEN 1 ELSE 0 END ), 0) AS resolvidoprimeirocontato,
			to_json(g.chamados) as chamados
                FROM atendimentos a
                LEFT JOIN escalacoes e on e.atendimento = a.atendimento
                LEFT JOIN chamados g ON g.campocustomizado = a.campocustomizado
                GROUP BY a.campocustomizado, g.chamados
                ORDER BY qtdchamados DESC";

    $stmt = $this->getConnection()->prepare($sql);


    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant,
        "datainicial" => $filter['datainicial'],
        "datafinal" => $filter['datafinal'],
        "campocustomizado" => $filter['campocustomizado']
    ]));


    return $this->process($stmt->fetchAll());
  }
  
  public function estatisticasArtigoTotalizadores($tenant, $artigo) {
    $sql = 'SELECT 
                COALESCE(SUM(a.gostaram), 0) AS gostaram, 
                COALESCE(SUM(a.naogostaram), 0) AS naogostaram,
                COALESCE(SUM(a.qtd_respostas_chamados), 0) AS respostaschamados,
                COALESCE(SUM(a.qtd_evitou_criacao_chamados), 0) AS respostasimediatas
            FROM atendimento.artigos a
            INNER JOIN atendimento.categorias sec ON sec.categoria = a.categoria
            INNER JOIN atendimento.categorias sub ON sub.categoria = sec.categoriapai           
            INNER JOIN atendimento.categorias c ON c.categoria = sub.categoriapai
            WHERE a.tenant = :tenant
            AND artigo = :artigo';
    
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute([ "tenant" => $tenant, "artigo" => $artigo ]);
    return $stmt->fetch();
  }
  
  public function estatisticasArtigo($tenant, $artigo) {
    $sql = 'SELECT util, created_by->>\'nome\' as nome, created_by->>\'email\' as email, justificativa as comentario, created_at
            FROM atendimento.artigosuteis
            WHERE tenant = :tenant AND artigo = :artigo';
    
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute([ "tenant" => $tenant, "artigo" => $artigo ]);
    return $stmt->fetchAll();
  }

  public function termosAceitos($tenant){
    $sql = " select * from atendimento.termosaceitos where tenant = :tenant ";
    $binds = [];
    $stmt = $this->getConnection()->prepare($sql);

    $stmt->execute(array_merge($binds, [
        "tenant" => $tenant
    ]));

    return $stmt->fetchAll();
   }

}
