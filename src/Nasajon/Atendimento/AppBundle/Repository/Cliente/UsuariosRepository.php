<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Nasajon\MDABundle\Repository\Atendimento\Cliente\UsuariosRepository as ParentRepository;

class UsuariosRepository extends ParentRepository {

    public function getClientesByConta($conta, $tenant) {
        $sql = "SELECT
                cf.funcao as \"funcao\" ,
                cf.tenant as \"tenant\" , 
                cliente_.id as \"cliente_id\" ,
                cliente_.nome as \"cliente_nome\" ,  
                cliente_.codigo as \"cliente_codigo\",
                cliente_.estado as cliente_estado, 
                cliente_.bloqueado as cliente_bloqueado,
                cliente_.email as cliente_email,
                cliente_.emailcobranca as cliente_cobranca,
                cliente_.representante_tecnico as cliente_representantetecnico,
                cliente_.representante as cliente_representantecomercial,
                to_json(array_agg(row(sc.servicocatalogo, sc.descricao)::atendimento.ofertas_clientes)) as cliente_ofertas,
                cliente_.status_suporte,
                translate(json_agg(distinct cd.classificador::text)::text,'\"',$$'$$) as cliente_classificacao
                FROM atendimento.clientesfuncoes cf
                INNER JOIN ns.vwclientes_atendimento cliente_ ON cf.tenant = cliente_.tenant AND cf.cliente = cliente_.id   
                LEFT JOIN servicos.objetosservicos os ON os.tenant = cf.tenant AND os.detentor_id = cliente_.id 
                LEFT JOIN crm.objetosservicoshistoricosofertas osho ON os.tenant = osho.tenant AND os.objetoservico = osho.id_objetoservico AND osho.ativa = true
                LEFT JOIN servicos.servicoscatalogo sc ON osho.tenant = sc.tenant AND osho.id_oferta = sc.servicocatalogo
                LEFT JOIN ns.vwclassificadores cd ON cliente_.tenant = cd.tenant AND cliente_.id = cd.cliente
                WHERE cf.conta = :conta
                AND cf.tenant = :tenant
                AND cf.pendente <> true
                GROUP BY cf.funcao, cf.tenant, cliente_id, cliente_nome, cliente_codigo, cliente_estado, cliente_bloqueado, cliente_.status_suporte, cliente_email, cliente_cobranca, cliente_representantetecnico, cliente_representantecomercial";

        $result = array_map(function($row) {
            $row['cliente_ofertas'] = json_decode($row['cliente_ofertas'], true);
            return $row;
        }, $this->getConnection()->executeQuery($sql, [
                    'conta' => $conta,
                    'tenant' => $tenant
                ])->fetchAll());

        return $result;
    }

    public function getClientesOfTitulosByConta($conta, $tenant) {
        $sql = "
            SELECT
                cliente_.id as \"cliente_id\" ,
                cliente_.nome as \"cliente_nome\" 
                FROM atendimento.clientesfuncoes cf
                JOIN ns.vwclientes_atendimento cliente_ ON cf.cliente = cliente_.id   
                WHERE cf.conta = :conta
                AND cf.tenant = :tenant
                AND cf.funcao = 'A'
                AND cliente_.bloqueado=false";

        $result = $this->getConnection()->executeQuery($sql, [
                    'conta' => $conta,
                    'tenant' => $tenant
                ])->fetchAll();

        return $result;
    }

}
