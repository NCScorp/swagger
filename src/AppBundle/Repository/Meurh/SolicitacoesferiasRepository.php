<?php

namespace AppBundle\Repository\Meurh;

use DateTime;
use DateInterval;
use AppBundle\Resources\Constant\TrabalhadorConstant;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowRepositoryTrait;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesferiasRepository as ParentRepository;

class SolicitacoesferiasRepository extends ParentRepository
{
    use WorkflowRepositoryTrait;

    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        parent::__construct($connection);
        $this->setLinks([
          [
            'field' => 'trabalhador',
            'entity' => 'Nasajon\MDABundle\Entity\Persona\Trabalhadores',
            'alias' => 't1_',
            'identifier' => 'trabalhador',
            'type' => 2
          ],

        ]);
    }


    public function finAllFeriados($tenant, $estabelecimento="", $lotacao="", $sindicato="", $tomador="") {
        $feriados = $this->findFeriadosFederais($tenant);
        if(!empty($estabelecimento)){
            $feriados = array_merge($this->findFeriadosEstabelecimento($tenant,$estabelecimento), $feriados);
        }
        if(!empty($lotacao)){
            $feriados = array_merge($this->findFeriadosLotacao($tenant,$lotacao), $feriados);
        }
        if(!empty($sindicato)){
            $feriados = array_merge($this->findFeriadosSindicato($tenant,$sindicato), $feriados);
        }
        if(!empty($tomador)){
            $feriados = array_merge($this->findFeriadosTomador($tenant,$tomador), $feriados);
        }
        $feriados = array_map(function($item){
            return $item['data'];
        }, $feriados);
        $feriados = array_unique($feriados);
        return $feriados;
    }

    public function findFeriadosFederais($tenant) {
        $sql = "SELECT data FROM ns.feriados WHERE tenant = :tenant AND (EXTRACT(YEAR FROM data) BETWEEN :ano AND :proximoAno) AND tipo = 0";
        $whereValues = [
            "ano" => date('Y') + 0,
            "proximoAno" => date('Y') + 1,
            'tenant' => $tenant
        ];
        return $this->getConnection()->executeQuery($sql, $whereValues)->fetchAll();
    }

    public function findFeriadosEstabelecimento($tenant,$estabelecimento) {
        $sql = "SELECT data FROM ns.feriados WHERE tenant = :tenant AND (EXTRACT(YEAR FROM data) BETWEEN :ano AND :proximoAno) AND (tipo = 3  OR tipo = 6)   AND estabelecimento = :estabelecimento";
        $whereValues = [
            "ano" => date('Y') + 0,
            "proximoAno" => date('Y') + 1,
            'tenant' => $tenant,
            'estabelecimento' => $estabelecimento
        ];
        return $this->getConnection()->executeQuery($sql, $whereValues)->fetchAll();
    }

    public function findFeriadosLotacao($tenant,$lotacao) {
        $sql = "SELECT data FROM ns.feriados WHERE tenant = :tenant AND (EXTRACT(YEAR FROM data) BETWEEN :ano AND :proximoAno) AND tipo = 8 AND lotacao = :lotacao";
        $whereValues = [
            "ano" => date('Y') + 0,
            "proximoAno" => date('Y') + 1,
            'tenant' => $tenant,
            'lotacao' => $lotacao
        ];
        return $this->getConnection()->executeQuery($sql, $whereValues)->fetchAll();
    }

    public function findFeriadosTomador($tenant,$tomador) {
        $sql = "SELECT data FROM ns.feriados WHERE tenant = :tenant AND (EXTRACT(YEAR FROM data) BETWEEN :ano AND :proximoAno) AND (tipo = 5 OR tipo = 7) AND pessoa = :tomador";
        $whereValues = [
            "ano" => date('Y') + 0,
            "proximoAno" => date('Y') + 1,
            'tenant' => $tenant,
            'tomador' => $tomador
        ];
        return $this->getConnection()->executeQuery($sql, $whereValues)->fetchAll();
    }

    public function findFeriadosSindicato($tenant,$sindicato) {
        $sql = "SELECT data FROM ns.feriados WHERE tenant = :tenant AND (EXTRACT(YEAR FROM data) BETWEEN :ano AND :proximoAno) AND tipo = 4
        AND sindicato = :sindicato";
        $whereValues = [
            "ano" => date('Y') + 0,
            "proximoAno" => date('Y') + 1,
            'tenant' => $tenant,
            'sindicato' => $sindicato
        ];
        return $this->getConnection()->executeQuery($sql, $whereValues)->fetchAll();
    }

    public function getDireito($faltas) {
        if ($faltas) {
            switch ($faltas) {
                case $faltas <= 5:
                    return 30;
                case $faltas >= 6 && $faltas <= 14:
                    return 24;
                case $faltas >= 15 && $faltas <= 23:
                    return 18;
                case $faltas >= 24 && $faltas <= 32:
                    return 12;
                default:
                    return 0;
            }
        } else {
            return 30;
        }
    }

    /**
     * @return int
     */
    public function getDireitoEstagiario(string $inicioPeriodoAquisitivo)
    {
        $mesesTrabalhados = 0;

        $dataAtual = new \DateTime();
        $dataInicioPeriodo = new \DateTime($inicioPeriodoAquisitivo);
        
        if (intval($dataInicioPeriodo->format('Y')) > intval($dataAtual->format('Y'))) {
            return 0;
        }

        $anosTrabalhados = ($dataAtual->diff($dataInicioPeriodo))->y;
        $mesesTrabalhados = ($dataAtual->diff($dataInicioPeriodo))->m;
        $mesesTrabalhados += $anosTrabalhados * 12;

        $diasDireito = ceil($mesesTrabalhados * 2.5);

        return $diasDireito > 30 ? 30 : $diasDireito;
    }

    public function getFimPeriodoAquisitivoFerias($tenant, $trabalhador, $inicioperiodoaquisitivoferias){
        $sql = "select * from  persona.api_obterfimperiodoaquisitivotrabalhador(:trabalhador, :inicioperiodo, :tenant)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('trabalhador', $trabalhador);
        $stmt->bindValue('inicioperiodo', $inicioperiodoaquisitivoferias);
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getDiasDireitoPeriodoAquisitivo($tenant, $trabalhador, $inicioperiodoaquisitivoferias){
        $sql = "select 
        coalesce ( (
        SELECT count(*) as TotalFaltas
        FROM persona.faltastrabalhadores m
        WHERE m.trabalhador = :trabalhador AND m.tenant = :tenant AND 
        m.data BETWEEN :inicioperiodoaquisitivoferias AND 
        (SELECT * FROM persona.api_obterfimperiodoaquisitivotrabalhador(:trabalhador, :inicioperiodoaquisitivoferias, :tenant))
        AND m.tipo = 0 AND m.status = 1 GROUP BY m.trabalhador),0) as Totalfaltas";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('trabalhador', $trabalhador);
        $stmt->bindValue('inicioperiodoaquisitivoferias', $inicioperiodoaquisitivoferias);
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @return array
     */
    public function listaPeriodosAquisitivosAbertos(int $tenant, string $id)
    {
        $sql = 'SELECT
                    t0_.trabalhador AS trabalhador,
                    t0_.tipo as tipo,
                    t0_.inicioperiodoaquisitivoferias AS inicioperiodoaquisitivoferiasatual,
                    (select * from  persona.api_obterfimperiodoaquisitivotrabalhador(t0_.trabalhador, t0_.inicioperiodoaquisitivoferias, t0_.tenant)) AS fimperiodoaquisitivoferiasatual,
                    (select jsonb_agg(p) AS periodosaquisitivos from persona.api_obterperiodosabertos(t0_.trabalhador, t0_.inicioperiodoaquisitivoferias, t0_.tenant) as p) AS periodosaquisitivos
                FROM persona.trabalhadores t0_
                WHERE t0_.trabalhador = :trabalhador
                AND t0_.tenant = :tenant';
        
        $binds = [
            'trabalhador' => $id,
            'tenant' => $tenant,
        ];

        $trabalhador = $this->getConnection()->executeQuery($sql, $binds)->fetch();
        $trabalhador['periodosaquisitivos'] = !is_null($trabalhador['periodosaquisitivos']) ? json_decode($trabalhador['periodosaquisitivos'], true) : [];
        $ehEstagiario = $trabalhador['tipo'] === TrabalhadorConstant::TIPO_ESTAGIARIO;

        foreach ($trabalhador['periodosaquisitivos'] as $key => $periodoaquisitivo) {
            $direito = $ehEstagiario ? $this->getDireitoEstagiario($trabalhador['inicioperiodoaquisitivoferiasatual']) : $this->getDireito($periodoaquisitivo['totalfaltas']);
            $diasGozados = $periodoaquisitivo['diasferiasgozados'];
            $diasVendidos = $periodoaquisitivo['diasvendidos'];

            $trabalhador['periodosaquisitivos'][$key]['direito'] = $direito;
            $trabalhador['periodosaquisitivos'][$key]['saldo'] = $direito - $diasGozados - $diasVendidos;
        }

        return $trabalhador;
    }

    public function findSolicitacoesAgrupadasPorPeriodo($tenant, $id)
    {        
        $sql = 'SELECT
                    t0_.trabalhador AS trabalhador,
                    t0_.nome AS nome,
                    t0_.codigo AS codigo,
                    t0_.tipo AS tipo,
                    t0_.cbo AS cbo,
                    t0_.dataadmissao AS dataadmissao,
                    t0_.datarescisao AS datarescisao,
                    t0_.inicioperiodoaquisitivoferias AS inicioperiodoaquisitivoferias,
                    DATE_PART(\'day\', (t0_.inicioperiodoaquisitivoferias + interval \'2 year\')::timestamp - NOW()::timestamp) AS faltaParaDobra,
                    EXTRACT(YEAR FROM t0_.inicioperiodoaquisitivoferias) AS anoperiodo,
                    (select * from persona.api_obterdatalimiteferiasemdobrotrabalhador(t0_.trabalhador, t0_.cbo, t0_.inicioperiodoaquisitivoferias, t0_.tenant)) AS datalimitegozo,
                    t1_.inicioproximoperiodoaquisitivoferias AS inicioproximoperiodoaquisitivoferias,
                    t1_.fimperiodoaquisitivoferias AS fimperiodoaquisitivoferias,
                    (select jsonb_agg(p) AS periodosaquisitivos from persona.api_obterperiodosabertos(t0_.trabalhador, t0_.inicioperiodoaquisitivoferias, t0_.tenant) as p) AS periodosaquisitivos,
                    (
                        SELECT 
                            jsonb_agg(ferias) AS ferias
                        FROM (
                            SELECT
                                sf.dataaviso as "dataaviso",
                                sf.solicitacao as "solicitacao",
                                sf.datainiciogozo as "datainiciogozo",
                                sf.datafimgozo as "datafimgozo",
                                sf.diasferiascoletivas as "diasferiascoletivas",
                                sf.datainicioperiodoaquisitivo as "datainicioperiodoaquisitivo",
                                sf.datafimperiodoaquisitivo as "datafimperiodoaquisitivo",
                                sf.observacao as "observacao",
                                sf.tenant as "tenant",
                                sf.trabalhador as "trabalhador",
                                sf.estabelecimento as "estabelecimento",
                                1 as "tipo",
                                sf.adto13nasferias as "adto13nasferias", 
                                sf.diasvendidos as "diasvendidos",
                                sf.situacao as situacao,
                                null as avisoferiastrabalhador,
                                sf.created_at as created_at,
                                sf.origem as "origem",
                                sf.wkf_data as "wkf_data",
                                sf.wkf_estado as "wkf_estado"
                            FROM meurh.solicitacoesferias sf
                            WHERE sf.trabalhador = t0_.trabalhador
                            AND sf.tenant = t0_.tenant 
                            AND sf.situacao in ( -1, 0, 2, 4) ---rascunho, aberto e cancelado
                            -- Condição para não exibir os rascunhos criados no MeuRH
                            AND ((sf.origem = 1 AND sf.situacao <> -1) OR sf.origem = 2)
                            UNION  
                            SELECT
                                a_.data as "dataaviso",
                                a_.solicitacao as "solicitacao",
                                a_.datainiciogozo as "datainiciogozo",
                                a_.datafimgozo as "datafimgozo",
                                a_.diasferiascoletivas as "diasferiascoletivas",
                                a_.datainicioperiodoaquisitivo as "datainicioperiodoaquisitivo",
                                a_.datafimperiodoaquisitivo as "datafimperiodoaquisitivo",
                                a_.observacao as "observacao",
                                a_.tenant as "tenant",
                                a_.trabalhador as "trabalhador",
                                null,
                                1 as "tipo",
                                a_.adto13nasferias as "adto13nasferias", 
                                a_.diasvendidos as "diasvendidos",
                                1 as "situacao",
                                a_.avisoferiastrabalhador as avisoferiastrabalhador,
                                a_.created_at as created_at,
                                null,
                                null,
                                null
                            FROM persona.avisosferiastrabalhadores a_
                            WHERE a_.trabalhador = t0_.trabalhador
                            AND a_.tenant = t0_.tenant
                            ORDER BY "situacao" ASC, "datainiciogozo" ASC
                        ) AS ferias
                    ) AS ferias,
                    COALESCE(
                        (
                            SELECT 
                                count(*)
                            FROM persona.faltastrabalhadores m
                            WHERE m.trabalhador=t0_.trabalhador
                            AND m.tenant = t0_.tenant 
                            AND m.data BETWEEN t0_.inicioperiodoaquisitivoferias AND (select * from persona.api_obterfimperiodoaquisitivotrabalhador(t0_.trabalhador, t0_.inicioperiodoaquisitivoferias, t0_.tenant))
                            AND m.tipo = 0
                            AND m.status = 1
                            GROUP BY m.trabalhador
                        ),
                        0
                    ) AS totalFaltas
                FROM persona.trabalhadores t0_
                INNER JOIN meurh.periodosaquisitivosferias t1_ ON t1_.trabalhador = t0_.trabalhador AND t1_.tenant = t0_.tenant
                WHERE t0_.trabalhador = :trabalhador
                AND t0_.tenant = :tenant
                AND t1_.inicioperiodoaquisitivoferias >= t0_.inicioperiodoaquisitivoferias;';

        $binds = [
            'trabalhador' => $id,
            'tenant' => $tenant,
        ];

        $trabalhador = $this->getConnection()->executeQuery($sql, $binds)->fetch();

        $trabalhador['ferias'] = !is_null($trabalhador['ferias']) ? json_decode($trabalhador['ferias'], true) : [];
        $trabalhador['periodosaquisitivos'] = json_decode($trabalhador['periodosaquisitivos'], true);

        $ehEstagiario = $trabalhador['tipo'] === TrabalhadorConstant::TIPO_ESTAGIARIO;

        $trabalhador['direito'] = $ehEstagiario ? $this->getDireitoEstagiario($trabalhador['inicioperiodoaquisitivoferias']) : $this->getDireito($trabalhador['totalfaltas']);
        $trabalhador['periodosaquisitivos'] = $this->ajustaPeriodosAquisitivos($trabalhador);
        unset($trabalhador['ferias']);

        return $trabalhador;
    }

    /**
     * @return array
     */
    private function ajustaPeriodosAquisitivos(array $trabalhador)
    {
        $solicitacoes = $trabalhador['ferias'];
        $ehEstagiario = $trabalhador['tipo'] === TrabalhadorConstant::TIPO_ESTAGIARIO;
        $periodosaquisitivos = [];

        foreach ($solicitacoes as $key => $solicitacao ) {
            if (!array_key_exists($solicitacao['datainicioperiodoaquisitivo'] . '|' . $solicitacao['datafimperiodoaquisitivo'], $periodosaquisitivos)) {
                $periodosaquisitivos[$solicitacao['datainicioperiodoaquisitivo'] . '|' . $solicitacao['datafimperiodoaquisitivo']] = [];
                $periodosaquisitivos[$solicitacao['datainicioperiodoaquisitivo'] . '|' . $solicitacao['datafimperiodoaquisitivo']]['datainicioperiodoaquisitivo'] = $solicitacao['datainicioperiodoaquisitivo'];
                $periodosaquisitivos[$solicitacao['datainicioperiodoaquisitivo'] . '|' . $solicitacao['datafimperiodoaquisitivo']]['datafimperiodoaquisitivo'] = $solicitacao['datafimperiodoaquisitivo'];
                $periodosaquisitivos[$solicitacao['datainicioperiodoaquisitivo'] . '|' . $solicitacao['datafimperiodoaquisitivo']]['solicitacoes'] = [];
            }

            $periodosaquisitivos[$solicitacao['datainicioperiodoaquisitivo'] . '|' . $solicitacao['datafimperiodoaquisitivo']]['solicitacoes'][] = $solicitacao;
        }

        foreach ($periodosaquisitivos as $key => $periodosaquisitivo ) {
            foreach ($trabalhador['periodosaquisitivos'] as $periodo) {
                if ($periodosaquisitivo['datainicioperiodoaquisitivo'] === $periodo['inicioperiodoaquisitivoferias'])  {
                    $periodosaquisitivos[$key]['direito'] = $ehEstagiario ? $this->getDireitoEstagiario($trabalhador['inicioperiodoaquisitivoferias']) : $this->getDireito($periodo['totalfaltas']);
                }
            }
        }

        foreach ($periodosaquisitivos as $key => $periodo) {
            $periodosaquisitivos[$key]['saldo'] = $periodosaquisitivos[$key]['direito'];
            $lista = $periodosaquisitivos[$key]['solicitacoes'];
            $possuirascunho = false;

            foreach ($lista as $elem) {
                if ($elem['situacao'] < 2 ) {
                    $periodosaquisitivos[$key]['saldo'] -= $elem['diasferiascoletivas'];
                    $periodosaquisitivos[$key]['saldo'] -= $elem['diasvendidos'];
                    $possuirascunho = ($elem['situacao'] == -1);
                }
            }

            $periodosaquisitivos[$key]['possuirascunho'] = $possuirascunho;
        }

        return $periodosaquisitivos;
    }

    /**
     * @return bool
     */
    public function verificaSolicitacoesFuturas(int $tenant, string $trabalhador, string $estabelecimento, string $dataFimPeriodoAquisitivo)
    {
        $sql = "SELECT 1
                FROM meurh.solicitacoesferias
                WHERE tenant = :tenant
                AND estabelecimento = :estabelecimento
                AND trabalhador = :trabalhador
                AND datainicioperiodoaquisitivo > :datafimperiodoaquisitivo
                AND (situacao = 0 OR situacao = 1);";

        $binds = [
            'tenant' => $tenant,
            'estabelecimento' => $estabelecimento,
            'trabalhador' => $trabalhador,
            'datafimperiodoaquisitivo' => $dataFimPeriodoAquisitivo
        ];

        $solicitacoes = $this->getConnection()->executeQuery($sql, $binds)->fetch();

        return $solicitacoes ? false : true;
    }

    /**
     * @return array
     */
    public function getDiasJaSolicitados(int $tenant, string $trabalhador, string $inicioperiodoaquisitivo, string $solicitacao = null)
    {
        $sql = "SELECT 
                    sum(diasferiascoletivas + diasvendidos) AS \"qtd_dias_solicitados\"
                FROM meurh.solicitacoesferias
                WHERE trabalhador = :trabalhador 
                AND tenant = :tenant 
                AND datainicioperiodoaquisitivo BETWEEN :inicioperiodoaquisitivo AND (select * from persona.api_obterfimperiodoaquisitivotrabalhador(:trabalhador, :inicioperiodoaquisitivo, :tenant))
                AND ((situacao = -1) OR (situacao = 0) OR (situacao = 1))";

        $binds = [
            'trabalhador' => $trabalhador,
            'tenant' => $tenant,
            'inicioperiodoaquisitivo' => $inicioperiodoaquisitivo
        ];

        if ($solicitacao) {
            $sql .= " AND solicitacao <> :solicitacao";
            $binds['solicitacao'] = $solicitacao;
        }

        $qtdDias = $this->getConnection()->executeQuery($sql, $binds)->fetch();

        return $qtdDias['qtd_dias_solicitados'] ? $qtdDias['qtd_dias_solicitados'] : 0;
    }
    private function findQuery(string $where, array $whereFields)
    {
        $sql = "SELECT
            t0_.solicitacao as \"solicitacao\" ,
            t0_.trabalhador as \"trabalhador\" ,
            t0_.estabelecimento as \"estabelecimento\" ,
            t0_.tiposolicitacao as \"tiposolicitacao\" ,
            t0_.codigo as \"codigo\" ,
            t0_.justificativa as \"justificativa\" ,
            t0_.observacao as \"observacao\" ,
            t0_.origem as \"origem\" ,
            t0_.situacao as \"situacao\" ,
            t0_.created_at as \"created_at\" ,
            t0_.created_by as \"created_by\" ,
            t0_.updated_at as \"updated_at\" ,
            t0_.updated_by as \"updated_by\" ,
            t0_.lastupdate as \"lastupdate\" ,
            t0_.tenant as \"tenant\" ,
            t0_.dataaviso as \"dataaviso\" ,
            t0_.datainiciogozo as \"datainiciogozo\" ,
            t0_.datafimgozo as \"datafimgozo\" ,
            t0_.datainicioperiodoaquisitivo as \"datainicioperiodoaquisitivo\" ,
            t0_.datafimperiodoaquisitivo as \"datafimperiodoaquisitivo\" ,
            t0_.temabonopecuniario as \"temabonopecuniario\" ,
            t0_.diasvendidos as \"diasvendidos\" ,
            t0_.diasferiascoletivas as \"diasferiascoletivas\" ,
            t0_.adto13nasferias as \"adto13nasferias\" ,
            t0_.wkf_data as \"wkf_data\" ,
            t0_.wkf_estado as \"wkf_estado\" ,
            t1_.nome as \"t1_nome\" ,
            t1_.trabalhador as \"t1_trabalhador\" 
        FROM meurh.solicitacoesferias t0_
        LEFT JOIN persona.trabalhadores t1_ ON t0_.trabalhador = t1_.trabalhador and t0_.tenant = t1_.tenant              
        {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }


    public function find($id, $tenant, $trabalhador)
    {
        $where = $this->buildWhere();
        $data = $this->findQuery($where, [
            'id' => $id,
            'tenant' => $tenant,
            'trabalhador' => $trabalhador
        ])->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }

    /**
     * @return array
     */
    public function findHolidays($tenant)
    {
        $sql = "SELECT data FROM ns.feriados WHERE EXTRACT(YEAR FROM data) BETWEEN :ano AND :proximoAno AND tenant = :tenant";

        $binds = [
            'ano' => date('Y'),
            'proximoAno' => date('Y') + 1,
            'tenant' => $tenant,
        ];

        return $this->getConnection()->executeQuery($sql, $binds)->fetchAll();
    }

    /**
     * @return array
     */
    public function findRepousosTrabalhador(int $tenant, string $trabalhador)
    {
        $sql = "SELECT
                    h.repousodomingo AS domingo,                
                    h.repousosegunda AS segunda,
                    h.repousoterca AS terca,
                    h.repousoquarta AS quarta,
                    h.repousoquinta AS quinta,
                    h.repousosexta AS sexta,
                    h.repousosabado AS sabado
                FROM persona.trabalhadores t
                LEFT JOIN persona.horarios h ON h.horario = t.horario AND h.tenant = t.tenant
                WHERE t.trabalhador = :trabalhador
                AND t.tenant = :tenant;";

        $binds = [
            'trabalhador' => $trabalhador,
            'tenant' => $tenant
        ];
        
        return $this->getConnection()->executeQuery($sql, $binds)->fetchAll();
    }
}
