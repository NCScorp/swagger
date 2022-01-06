<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\ModelBundle\Services\ConfiguracoesService;

class RelatoriosBacklogRepository extends RelatoriosRepository {

    public function porAtribuicao($tenant, $filter) {
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
            $condicoesSQL .= " AND (" . $filtroEquipe . ")";
        }

        $sql = "WITH atendimentos AS (
                        SELECT a.adiado, a.data_adiamento, a.atendimento, a.responsavel_web, a.responsavel_web_tipo, a.tenant, ((NOW() - a.data_abertura)::interval) as tempo_aberto, a.data_ultima_resposta_admin, a.ultima_resposta_admin
                        FROM servicos.atendimentos a
                        LEFT JOIN ".$viewClientesAtendimento." cliente ON a.participante = cliente.id
			LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = a.responsavel_web
                        WHERE a.tenant = :tenant
                        AND a.situacao = 0
                        " . $condicoesSQL . $condicoesSqlPorGrupoEmpresarial . "
			GROUP BY a.atendimento, a.responsavel_web, a.responsavel_web_tipo, a.tenant, tempo_aberto, a.data_ultima_resposta_admin, a.ultima_resposta_admin
                ),
                primeirocontato AS (
                        SELECT a.atendimento, count(f.*) as qtd, a.data_ultima_resposta_admin
                        FROM  atendimentos a
                        LEFT JOIN ns.followups f ON a.atendimento = f.atendimento AND a.tenant = f.tenant AND f.tipo = 0
                        GROUP BY a.atendimento, a.data_ultima_resposta_admin
                        HAVING count(f.*) <= 1 AND a.data_ultima_resposta_admin IS null
                ), respondidopeloatendente AS (
			SELECT a.atendimento
			FROM atendimentos a
			WHERE a.ultima_resposta_admin = 't'
                ), adiados AS (
			select a.responsavel_web, count(a.*) as qnt_adiado from atendimentos a
                        where a.adiado = true
                        AND a.data_adiamento > now()
			group by a.responsavel_web
                ),relatorio AS (
                        SELECT coalesce(adi.qnt_adiado, 0) as qnt_adiado,
                        a.responsavel_web_tipo,
                        a.responsavel_web,
                        COUNT(pc.qtd) AS sem_resposta,
                        count(*) AS total,
                        --(count(*) - COUNT(pc.qtd)) AS aguardando_resposta,
                        ROUND(EXTRACT(epoch FROM (sum(a.tempo_aberto)/count(*)))/3600) as media_tempo_aberto,
                        COUNT(respat.*) AS respondido_pelo_atendente,
                        (count(*) - COUNT(pc.qtd) - COUNT(respat.*)) AS respondido_pelo_cliente
                        FROM  atendimentos a
                        LEFT JOIN primeirocontato pc ON pc.atendimento = a.atendimento
                        LEFT JOIN respondidopeloatendente respat ON respat.atendimento = a.atendimento
                        LEFT JOIN adiados adi ON adi.responsavel_web = a.responsavel_web
                        GROUP BY a.responsavel_web, a.responsavel_web_tipo, adi.qnt_adiado
                )
                SELECT a.*, CASE WHEN a.responsavel_web_tipo = 1 THEN a.responsavel_web ELSE COALESCE(af.nome, '<vazio>') END AS nome
                FROM relatorio a
                LEFT JOIN servicos.atendimentosfilas af ON a.responsavel_web_tipo = 2 AND af.atendimentofila::character varying = a.responsavel_web
                ORDER BY a.total DESC";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute(array_merge($binds, [
            "tenant" => $tenant,
                        /* "datainicial" => $filter['datainicial'],
                          "datafinal" => $filter['datafinal'] */
        ]));

        return $stmt->fetchAll();
    }


    public function porAtribuicaoTotalizadores($tenant, $filter) {
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
            $condicoesSQL .= " AND (" . $filtroEquipe . ")";
        }

        $sql = "WITH atendimentos AS
        (SELECT a.atendimento,
                a.tenant,
                EXTRACT(epoch
                        FROM ((NOW() - a.data_abertura)::interval))/3600 AS tempo_aberto,
                a.ultima_resposta_admin,
                TRUE AS primeirocontato,
                        CASE
                            WHEN (a.adiado = TRUE
                                  AND a.data_adiamento > now()) THEN TRUE
                            ELSE FALSE
                        END AS adiado,
                        CASE
                            WHEN a.ultima_resposta_admin = 't' THEN TRUE
                            ELSE FALSE
                        END AS respondido_pelo_atendente,
                        CASE
                            WHEN a.data_ultima_resposta_admin IS NULL THEN TRUE
                            ELSE FALSE
                        END AS sem_resposta
         FROM servicos.atendimentos a
         LEFT JOIN " . $viewClientesAtendimento . " cliente ON a.participante = cliente.id
         LEFT JOIN atendimento.equipesusuarios eu ON eu.usuario = a.responsavel_web
         WHERE a.tenant = :tenant
           AND a.situacao = 0 " . $condicoesSQL . $condicoesSqlPorGrupoEmpresarial . "
         GROUP BY a.atendimento,
                  a.ultima_resposta_admin,
                  a.tenant)
      SELECT SUM(CASE
                     WHEN a.adiado THEN 1
                     ELSE 0
                 END) AS adiados,
             SUM(CASE
                     WHEN a.sem_resposta THEN 1
                     ELSE 0
                 END) AS sem_resposta,
             COUNT(*) AS total,
             ROUND(sum(a.tempo_aberto)/count(*)) AS media_tempo_aberto,
             SUM(CASE
                     WHEN a.respondido_pelo_atendente THEN 1
                     ELSE 0
                 END) AS respondido_pelo_atendente,
             (count(*) - SUM(CASE
                                 WHEN a.sem_resposta THEN 1
                                 ELSE 0
                             END) - SUM(CASE
                                            WHEN a.respondido_pelo_atendente THEN 1
                                            ELSE 0
                                        END)) AS respondido_pelo_cliente
      FROM atendimentos a";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute(array_merge($binds, [
            "tenant" => $tenant,
                        /* "datainicial" => $filter['datainicial'],
                          "datafinal" => $filter['datafinal'] */
        ]));

        return $stmt->fetch();
    }

}
