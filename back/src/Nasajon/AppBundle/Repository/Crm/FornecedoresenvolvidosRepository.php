<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Request\Filter;

use Nasajon\MDABundle\Repository\Crm\FornecedoresenvolvidosRepository as ParentRepository;

/**
 * Método findAllQueryBuilder sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 * FindAll sobrescrito para dar suporte a sobrescrição de tabela fornecedores
 */
class FornecedoresenvolvidosRepository extends ParentRepository
{

    /**
     * @return array
     */
    public function findAll($tenant, $negocio, $id_grupoempresarial, Filter $filter = null)
    {
        $this->validateOffset($filter);
        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $negocio, $id_grupoempresarial, $filter);
        // $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $sql = $queryBuilder->getSQL();
        //localiza referencia da view (cliente) e muda para referencia da tabela ns.pessoas (id)
        $sql = str_replace("t2_.fornecedor", "t2_.id", $sql);
        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute($binds);
        $joins = ['negocio', 'fornecedor',];
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
            $row['created_by'] = json_decode($row['created_by'], true);
            return $row;
        }, $stmt->fetchAll());
        return $result;
    }

    public function findAllQueryBuilder($tenant, $atc, $id_grupoempresarial, Filter $filter = null)
    {

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.fornecedorenvolvido as fornecedorenvolvido',
            't0_.acionamentodata as acionamentodata',
            't0_.acionamentorespostaprazo as acionamentorespostaprazo',
            't0_.acionamentoaceito as acionamentoaceito',
            't0_.acionamentometodo as acionamentometodo',
            't0_.created_by as created_by',
            't0_.descontoglobal as descontoglobal',
        );

        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.fornecedoresenvolvidos', 't0_');
        // $queryBuilder->leftJoin('t0_', 'crm.vwnegociossimplesecomseguradora_v6', 't1_', 't0_.negocio = t1_.negocio');
        $queryBuilder->leftJoin('t0_', 'crm.atcs', 't1_', 't0_.negocio = t1_.negocio and t0_.tenant = t1_.tenant');
        $queryBuilder->addSelect(array(
            // 't1_.datacriacao as negocio_datacriacao',
            't1_.created_at as negocio_datacriacao',
            't1_.negocio as negocio_negocio',
            't1_.codigo as negocio_codigo',
            't1_.nome as negocio_nome',
            't1_.camposcustomizados as negocio_camposcustomizados',
            't1_.status as negocio_status',
            't1_.possuiseguradora as negocio_possuiseguradora',
            // 't1_.dataedicao as negocio_dataedicao',
            't1_.updated_at as negocio_dataedicao',
        ));
        // $queryBuilder->leftJoin('t0_', 'ns.vw_fornecedores_v2', 't2_', 't0_.fornecedor = t2_.fornecedor');
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't2_', 't0_.fornecedor = t2_.id  and t0_.tenant = t2_.tenant');
        $queryBuilder->addSelect(array(
            // 't2_.fornecedor as fornecedor_fornecedor',
            't2_.id as fornecedor_fornecedor',
            // 't2_.nomefantasia as fornecedor_nomefantasia',
            "CASE
                WHEN t2_.nomefantasia::text = ''::text THEN COALESCE(t2_.nome, 'Não informado'::character varying)::character varying(150)
                ELSE COALESCE(t2_.nomefantasia, t2_.nome, 'Não informado'::character varying)::character varying(150)
            END AS fornecedor_nomefantasia",
            't2_.nome as fornecedor_razaosocial',
            't2_.cnpj as fornecedor_cnpj',
            't2_.pessoa as fornecedor_codigofornecedores',
            // 't2_.status as fornecedor_status', //movido para o novo join abaixo
            't2_.esperapagamentoseguradora as fornecedor_esperapagamentoseguradora',
            't2_.diasparavencimento as fornecedor_diasparavencimento',
            't2_.anotacao as fornecedor_anotacao',
            't2_.estabelecimentoid as fornecedor_estabelecimentoid',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.fornecedoressuspensos', 'fornecedoressuspensos', 't2_.id = fornecedoressuspensos.fornecedor_id  and t2_.tenant = fornecedoressuspensos.tenant');
        $queryBuilder->addSelect(array(
            "CASE
                WHEN fornecedoressuspensos.tipo = 1 THEN '1'::text
                WHEN fornecedoressuspensos.tipo = 2 THEN '2'::text
                ELSE '0'::text
            END AS fornecedor_status",
        ));


        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $atc, $id_grupoempresarial, $filter);


        return [$queryBuilder, $binds];
    }

    /**
     * Retorna lista de fornecedores envolvidos no atendimento, com detalhes de advertências e contatos.
     * Dados utilizados no Accordion de Prestadores de Serviço envolvidas, na ediçãod o Atendimento comercial
     */
    public function findAllAtcFornecedoresDetalhes($tenant, $atc, $id_grupoempresarial)
    {
        $sql = "
            with tb_atcfornecedores as (
                select fornecedor, negocio, tenant, id_grupoempresarial
                from crm.propostasitens
                where negocio = :negocio
                and tenant = :tenant
                and id_grupoempresarial = :grupoempresarial
                and fornecedor is not null
                group by fornecedor, negocio, tenant, id_grupoempresarial
            ),
            -- Busco informacoes dos fornecedores
            tb_fornecedores as (
                select nf.*,
                    p.nome as fornecedor_razaosocial,
                    CASE
                        WHEN p.nomefantasia::text = ''::text THEN COALESCE(p.nome, 'Não informado'::character varying)::character varying(150)
                        ELSE COALESCE(p.nomefantasia, p.nome, 'Não informado'::character varying)::character varying(150)
                    END AS fornecedor_nomefantasia,
                    CASE
                        WHEN fs.tipo in (1, 2) THEN fs.tipo
                        ELSE 0
                    END AS fornecedor_status,
                    p.cnpj as fornecedor_cnpj
                from tb_atcfornecedores nf
                    inner join ns.pessoas p on
                        nf.fornecedor = p.id and
                        nf.tenant = p.tenant
                    left join ns.fornecedoressuspensos fs on
                        fs.fornecedor_id = p.id
            ),
            -- Busco a logo ativa dos fornecedores
            tb_fornecedorescomlogos as (
                select f.*, pl.path_logo
                from tb_fornecedores f
                    left join ns.pessoaslogos pl on
                        f.fornecedor = pl.id_pessoa and
                        f.tenant = pl.tenant and
                        f.id_grupoempresarial = pl.id_grupoempresarial and
                        pl.ativo = true
            ),
            -- Busco informações de advertência dos fornecedores
            tb_fornecedorescomadvertencia as (
                select f.fornecedor, count(a.advertencia) as qtd_advertencia, max(a.created_at) as data_ultima_advertencia
                from tb_fornecedores f
                    left join ns.advertencias a on
                        f.fornecedor = a.fornecedor_id and
                        f.tenant = a.tenant and
                        f.id_grupoempresarial = a.id_grupoempresarial
                group by f.fornecedor
            ),
            -- Busco o contatos e telefones principais dos fornecedores
            tb_fornecedorescomcontatos as (
                select fcl.*, c.nome as contato_nome, c.email as contato_email,
                    t.ddd as contato_tel_ddd, 
                    t.telefone as contato_tel_numero, t.ramal as contato_tel_ramal
                from tb_fornecedorescomlogos fcl
                    left join ns.contatos c on	
                        fcl.fornecedor = c.id_pessoa and
                        fcl.tenant = c.tenant and
                        c.principal = true
                    left join ns.telefones t on
                    	c.id = t.contato and
                    	fcl.tenant = t.tenant and
                    	t.principal = true -- Ainda não existe
            ),

            tb_contasfornecedores as (
                select cf.banco || ' - ' || coalesce(b.nome, 'Sem descrição') as conta_banco, agencianumero as conta_agencianumero, agenciadv as conta_agenciadv, contanumero as conta_contanumero, contadv as conta_contadv, padrao as padrao_conta, id_fornecedor as fornecedor
                from financas.contasfornecedores cf
                    left join financas.bancos b on b.numero = cf.banco and b.tenant = cf.tenant
                    left join tb_fornecedorescomcontatos fc on cf.id_fornecedor = fc.fornecedor
                    where cf.padrao = true and coalesce(cf.excluida, false) <> true
            )
            
            -- Retorno lista com as principais informações detalhadas dos fornecedores e suas informações bancárias
            select fcc.*, fca.qtd_advertencia, fca.data_ultima_advertencia, cf.*
            from tb_fornecedorescomcontatos fcc
                left join tb_fornecedorescomadvertencia fca on
                    fcc.fornecedor = fca.fornecedor
                left join tb_contasfornecedores cf on
                fca.fornecedor = cf.fornecedor";

        $bindQuery = [
            'tenant' => $tenant,
            'grupoempresarial' => $id_grupoempresarial,
            'negocio' => $atc
        ];

        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        return $data;
    }


    /**
     * @param string  $atc
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @return array 
     * @throws \Exception
     */
    public function findAllFornecedoresenvolvidosFichafinanceira($atc, $tenant, $id_grupoempresarial, $fornecedor = null)
    {
        $sql_1 = "
            -- Busco valores das apólices
            with tb_valoresapolices as (
                select distinct
                    negocio,
                    fornecedor, 
                    SUM(distinct coalesce(valorapolice, 0)) as valorapolice,
                    tenant,
                    id_grupoempresarial
                from crm.propostasitens
                where tenant = :tenant
                  and id_grupoempresarial = :id_grupoempresarial
                  and negocio = :atc
                group by negocio, fornecedor, tenant, id_grupoempresarial
            ), 
            -- Busco configuração de orçamento zerado
            tb_config as (
                select
                    aa.permiteorcamentozerado,
                    va.*
                from tb_valoresapolices va
                inner join crm.atcs a
                    on a.negocio = va.negocio
                    and a.tenant = va.tenant
                    and a.id_grupoempresarial = va.id_grupoempresarial
                inner join crm.atcsareas aa
                    on aa.negocioarea = a.area
                    and aa.tenant = a.tenant
                    and aa.id_grupoempresarial = a.id_grupoempresarial
            ),
            -- Busco dados do fornecedor e fornecedor envolvido
            tb_fornecedores as (
                select 
                    c.*, 
                    p.nomefantasia as fornecedor_nomefantasia, 
                    p.nome as fornecedor_nome,
                    p.estabelecimentoid as fornecedor_estabelecimento,
                    p.esperapagamentoseguradora as fornecedor_esperapagamentoseguradora,
                    fe.fornecedorenvolvido,
                    fe.acionamentodata,
                    fe.acionamentometodo,
                    fe.acionamentorespostaprazo,
                    fe.acionamentoaceito,
                    fe.created_by->>'nome' as acionador,
                    fe.possuidescontoparcial as config_desconto_possuidescontoparcial,
                    fe.possuidescontoglobal as config_desconto_possuidescontoglobal,
                    fe.descontoglobal as config_desconto_descontoglobal,
                    fe.descontoglobaltipo as config_desconto_descontoglobaltipo
                from tb_config c
                inner join ns.pessoas p
                    on p.id = c.fornecedor
                    and p.tenant = c.tenant
                    and coalesce(p.fornecedorativado, 0) = 1
                left join crm.fornecedoresenvolvidos fe
                    on fe.negocio = c.negocio
                    and fe.fornecedor = c.fornecedor
                    and fe.tenant = c.tenant
                    and fe.id_grupoempresarial = c.id_grupoempresarial
            ) 
            -- Busco orçamentos dos fornecedores
            select
                f.*,
                o.orcamento,
                o.propostaitem as orcamento_propostaitem,
                o.composicao as orcamento_composicao,
                o.familia as orcamento_familia,
                o.descricao as orcamento_descricao,
                o.descricaomanual as orcamento_descricaomanual,
                o.custo as orcamento_custo,
                o.valorunitario as orcamento_valorunitario,
                o.quantidade as orcamento_quantidade,
                o.valorreceber as orcamento_valorreceber,
                o.desconto as orcamento_desconto,
                o.descontoglobal as orcamento_descontoglobal,
                o.faturar as orcamento_faturar,
                o.faturamentotipo as orcamento_faturamentotipo,
                o.servicotipo as orcamento_servicotipo,
                o.status as orcamento_status,
                o.updated_at as orcamento_updated_at,
                o.fornecedorterceirizado as orcamento_prestador_terceirizado,
                p.nomefantasia as orcamento_prestador_terceirizado_nomefantasia, 
                p.nome as orcamento_prestador_terceirizado_nome
            from tb_fornecedores f
            left join crm.orcamentos o
                on o.atc = f.negocio
                and o.fornecedor = f.fornecedor
                and o.tenant = f.tenant
                and o.grupoempresarial = f.id_grupoempresarial
            left join ns.pessoas p
                on p.id = o.fornecedorterceirizado
                and p.tenant = o.tenant
                and coalesce(p.fornecedorativado, 0) = 1
        ";

        if ($fornecedor != null) {
            $sql_1 .= " where f.fornecedor = :fornecedor";
        }
        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("atc", $atc);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("id_grupoempresarial", $id_grupoempresarial);

        if ($fornecedor != null) {
            $stmt_1->bindValue("fornecedor", $fornecedor);
        }

        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @param string  $negocio
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity
     * @return string 
     * @throws \Exception
     */
    public function delete($negocio, $id_grupoempresarial, $logged_user, $tenant,  \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos $entity)
    {
        $sql_1 = "SELECT mensagem
            FROM crm.api_fornecedorenvolvidoexcluir_v2(row(
                            :fornecedorenvolvido,
                            :negocio,
                            :fornecedor,
                            :nomeFornecedor,
                            :id_grupoempresarial,
                            :deleted_by,
                            :tenant
                        )::crm.tfornecedorenvolvidoexcluir_v2
            );";

        $nomeFornecedor = null;
        if($entity->getFornecedor() !== null && 
            $entity->getFornecedor()->getNomefantasia() !== null &&
            $entity->getFornecedor()->getNomefantasia() !== ''
        ){
            $nomeFornecedor = $entity->getFornecedor()->getNomefantasia();
        } elseif ($entity->getFornecedor() !== null && 
            $entity->getFornecedor()->getRazaosocial() !== null &&
            $entity->getFornecedor()->getRazaosocial() !== ''
        ){
            $nomeFornecedor = $entity->getFornecedor()->getNomefantasia();
        }

        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("fornecedorenvolvido", $entity->getFornecedorenvolvido());
        $stmt_1->bindValue("negocio", $negocio);
        $stmt_1->bindValue("fornecedor", ($entity->getFornecedor()) ? $entity->getFornecedor()->getFornecedor() : NULL);
        $stmt_1->bindValue("nomeFornecedor", $nomeFornecedor);
        $stmt_1->bindValue("id_grupoempresarial", $id_grupoempresarial);
        $stmt_1->bindValue("deleted_by", json_encode($logged_user));
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entity);
        $retorno = $resposta;
        return $retorno;
    }

    /**
     * @param string  $atc
     * @param string  $fornecedor
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @return array 
     * @throws \Exception
     */
    public function buscarConfiguracaoDescontoFichaFinanceira($atc, $fornecedor, $tenant, $id_grupoempresarial)
    {
        $sql_1 = "
            
            with tb_config as (
                select
                    fe.fornecedor,
                    fe.tenant,
                    fe.id_grupoempresarial,
                    fe.negocio,
                    fe.possuidescontoparcial,
                    fe.possuidescontoglobal,
                    fe.descontoglobal,
                    fe.descontoglobaltipo
                from crm.fornecedoresenvolvidos fe
                where fornecedor = :fornecedor
                  and negocio = :atc
                  and tenant = :tenant
                  and id_grupoempresarial = :id_grupoempresarial
            )
            select 
                c.possuidescontoparcial,
                c.possuidescontoglobal,
                c.descontoglobal,
                c.descontoglobaltipo,
                max(
                    case
                        when (o.faturamentotipo <> 1) then o.orcamento::varchar(100)
                        else null
                    end
                ) as orcamento, 
                max(o.status) as orcamentostatus, 
                count(o.orcamento) as qtd_orcamentos,
                sum(
                    case
                        when (o.faturamentotipo <> 1) then 1
                        else 0
                    end
                ) as qtd_orcamentos_faturar,
                sum(o.valor) as total_orcamento,
                sum(
                    case
                        when (o.faturamentotipo <> 1) then o.valor
                        else 0
                    end
                ) as total_orcamento_faturar,
                sum(o.desconto) as total_desconto
            from crm.orcamentos o
            inner join tb_config c on
                c.fornecedor = o.fornecedor
                and c.negocio = o.atc
                and c.tenant = o.tenant
                and c.id_grupoempresarial = o.grupoempresarial
            group by
                c.possuidescontoparcial,
                c.possuidescontoglobal,
                c.descontoglobal,
                c.descontoglobaltipo
        ";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("atc", $atc);
        $stmt_1->bindValue("fornecedor", $fornecedor);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("id_grupoempresarial", $id_grupoempresarial);
        $stmt_1->execute();
        $result = $stmt_1->fetch();
        return $result;
    }
}
