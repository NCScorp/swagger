<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace AppBundle\Repository\Meurh;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Meurh\RecibospagamentosRepository as ParentRepository;
use LogicException;
/**
* RecibospagamentosRepository
*
*/
class RecibospagamentosRepository extends ParentRepository
{
    /**
     * FindAll de Recibos de pagamentos com ordenação de ano e mês, não suportado pelo findAll gerado pelo MDA
     * @param integer $tenant id ex: 47
     * @param uuid $trabalhador
     * @param integer $ano
     * @param integer $mes
     */
    public function findAllOrderAnoEMes($tenant, $trabalhador, $ano = null, $mes = null, $calculo = null){

        if($ano == null || $mes == null || $calculo == null) {
            $where = " t0_.trabalhador = :trabalhador AND t0_.tenant = :tenant ";
            
            $variaveis = [
                'tenant' => $tenant,
                'trabalhador' => $trabalhador
            ];
        } else {
            $where = " ((t0_.ano = :ano AND ((t0_.mes = :mes AND t0_.calculo < :calculo) OR (t0_.mes < :mes))) OR (t0_.ano < :ano)) AND t0_.trabalhador = :trabalhador AND t0_.tenant = :tenant ";

            $variaveis = [
                'tenant' => $tenant,
                'trabalhador' => $trabalhador,
                'ano' => $ano,
                'mes' => $mes,
                'calculo' => $calculo
            ];
        }
        $sql =
            "SELECT
                t0_.recibopagamento as recibopagamento, t0_.aberto as aberto, t0_.trabalhador as id_trabalhador, t0_.calculo as calculo, t0_.ano as ano, t0_.mes as mes, t0_.caminhodocumento as caminhodocumento, t0_.liquido as liquido, t0_.created_at as created_at, count(*) OVER() AS full_count, t1_.cargo as cargo_cargo, t1_.codigo as cargo_codigo, t1_.nome as cargo_nome, t1_.descricao as cargo_descricao, t2_.nivelcargo as nivelcargo_nivelcargo, t2_.codigo as nivelcargo_codigo, t2_.observacao as nivelcargo_observacao
            FROM
                meurh.recibospagamentos t0_
            LEFT JOIN
                persona.cargos t1_ ON t0_.cargo = t1_.cargo AND t0_.tenant = t1_.tenant
            LEFT JOIN
                persona.niveiscargos t2_ ON t0_.nivelcargo = t2_.nivelcargo  AND t0_.tenant = t2_.tenant
            WHERE".
                $where
            ."ORDER BY
                t0_.ano desc, t0_.mes DESC, calculo DESC
            LIMIT 20;";

            $joins = ['cargo', 'nivelcargo', ];       
        
            return array_map(function($row) use($joins){
                if(count($joins) > 0){
                    foreach ($row as $key => $value) {
                        $parts = explode("_", $key);                    
                        $prefix = array_shift($parts);
    
                        if (in_array($prefix , $joins)) {
                            $row[$prefix][join("_",$parts)] = $value;
                            unset($row[$key]);
                        }
                    }
                }
                            return $row;
            },$this->getConnection()->executeQuery($sql, $variaveis)->fetchAll());


    }

    public function reciboVisualizado($recibo, $tenant){
        $sql ="update meurh.recibospagamentos set aberto = true where recibopagamento = :recibopagamento and tenant = :tenant";
        return  $this->getConnection()->executeQuery($sql, ["recibopagamento" => $recibo, "tenant" => $tenant])->fetch();
    }
}