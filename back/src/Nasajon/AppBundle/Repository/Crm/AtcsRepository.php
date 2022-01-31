<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\AppBundle\Utils\StringUtils;
use Nasajon\MDABundle\Repository\Crm\AtcsRepository as ParentRepository;

/**
 * Método findAllQueryBuilder sobrescrito para otimizar query, deixando de dar joins com views desnecessariamente.
 * Método findAll sobrescrito para mudar referencia do guid do cliente caso seja usado filtro cliente.
 * Método findQuery (private) substituído por overridenfindQuery para otimizar query, deixando de dar joins com views desnecessariamente.
 * Métodos find e findBy sobrescritos para usar novo método acima. Foi necessário pois findQuery original é private, por tanto, não possível de ser sobrescrito.
 */
class AtcsRepository extends ParentRepository
{

    public function findAllQueryBuilder($tenant, $id_grupoempresarial, Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //Este array está aqui pois caso o caso de uso seja paginado, existirá um código para adicionar a contagem do total no select
        $selectArray = array(
            't0_.datacriacao as datacriacao',
            't0_.negocio as negocio',
            't0_.codigo as codigo',
            't0_.nome as nome',
            't0_.camposcustomizados as camposcustomizados',
            't0_.localizacaopaisnome as localizacaopaisnome',
            't0_.localizacaoestadonome as localizacaoestadonome',
            't0_.localizacaomunicipionome as localizacaomunicipionome',
            't0_.status as status',
            't0_.possuiseguradora as possuiseguradora',
            't0_.dataedicao as dataedicao',
            't0_.created_by as created_by',
        );

        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }
        $queryBuilder->select($selectArray);
        $queryBuilder->from('crm.vwatcssimplesecomseguradora_v12', 't0_');
        $queryBuilder->leftJoin('t0_', 'crm.atcsareas', 't7_', 't0_.area = t7_.negocioarea and t0_.tenant = t7_.tenant');
        $queryBuilder->addSelect(array(
            't7_.negocioarea as area_negocioarea',
            't7_.nome as area_nome',
            't7_.localizacao as area_localizacao',
        ));
        $queryBuilder->leftJoin('t0_', 'crm.atcs', 't11_', 't0_.negociopai = t11_.negocio and t0_.tenant = t11_.tenant');
        $queryBuilder->addSelect(array(
            't11_.created_at as negociopai_datacriacao',
            't11_.negocio as negociopai_negocio',
            't11_.codigo as negociopai_codigo',
            't11_.nome as negociopai_nome',
            't11_.camposcustomizados as negociopai_camposcustomizados',
            't11_.status as negociopai_status',
            't11_.possuiseguradora as negociopai_possuiseguradora',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.pessoas', 't9_', 't0_.cliente = t9_.id and t0_.tenant = t9_.tenant');
        $queryBuilder->addSelect(array(
            't9_.pessoa as cliente_cliente',
            't9_.nome as cliente_razaosocial',
            't9_.cnpj as cliente_cnpj',
            't9_.nomefantasia as cliente_nomefantasia',
            't9_.diasparavencimento as cliente_diasparavencimento',
            // 't9_.tipo as cliente_tipo',
            'CASE 
                WHEN COALESCE(t9_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT
            END	AS tipo',
            't9_.id_formapagamento as cliente_formapagamentoguid',
            't9_.id_conta_receber as cliente_conta',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.paises', 't3_', 't0_.localizacaopais = t3_.pais');
        $queryBuilder->addSelect(array(
            't3_.pais as localizacaopais_pais',
            't3_.nome as localizacaopais_nome',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.estados', 't2_', 't0_.localizacaoestado = t2_.uf');
        $queryBuilder->addSelect(array(
            't2_.uf as localizacaoestado_uf',
            't2_.nome as localizacaoestado_nome',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.municipios', 't4_', 't0_.localizacaomunicipio = t4_.ibge');
        $queryBuilder->addSelect(array(
            't4_.ibge as localizacaomunicipio_codigo',
            't4_.nome as localizacaomunicipio_nome',
        ));
        $queryBuilder->leftJoin('t0_', 'financas.contratos', 't18_', 't0_.contrato = t18_.contrato and t0_.tenant = t11_.tenant');
        $queryBuilder->addSelect(array(
            't18_.contrato as contrato_contrato',
        ));
        $queryBuilder->leftJoin('t0_', 'financas.projetos', 't14_', 't0_.projeto = t14_.projeto and t0_.tenant = t14_.tenant');
        $queryBuilder->addSelect(array(
            't14_.projeto as projeto_projeto',
            't14_.nome as projeto_nome',
            't14_.datainicio as projeto_datainicio',
            't14_.datafim as projeto_datafim',
            't14_.situacao as projeto_situacao',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.municipios', 't20_', 't0_.localizacaomunicipiosepultamento = t20_.ibge'); 
        $queryBuilder->addSelect(array(
            't20_.ibge as localizacaomunicipiosepultamento_codigo',
            't20_.nome as localizacaomunicipiosepultamento_nome',
        ));
        $queryBuilder->leftJoin('t0_', 'ns.estados', 't21_', 't0_.localizacaoestadosepultamento = t21_.uf  ');
        $queryBuilder->addSelect(array(
            't21_.uf as localizacaoestadosepultamento_uf',
            't21_.nome as localizacaoestadosepultamento_nome',
        ));
        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, $filter);
        return [$queryBuilder, $binds];
    }

    /**
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
    {
        $this->validateOffset($filter);
        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $id_grupoempresarial, $filter);
        $sql = $queryBuilder->getSQL();
        //localiza referencia da view (cliente) e muda para referencia da tabela ns.pessoas (id)
        $sql = str_replace("t9_.cliente", "t9_.id", $sql);
        $sql = str_replace("t0_.datacriacao", "t0_.created_at", $sql);
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($binds);
        $joins = ['area', 'negociopai', 'cliente', 'localizacaopais', 'localizacaoestado', 'localizacaomunicipio', 'localizacaomunicipiosepultamento', 'localizacaoestadosepultamento',  'contrato', 'projeto',];
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
            $row['camposcustomizados'] = json_decode($row['camposcustomizados'], true);
            $row['created_by'] = json_decode($row['created_by'], true);
            return $row;
        }, $stmt->fetchAll());
        return $result;
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $id_grupoempresarial
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $id_grupoempresarial)
    {
        $where = $this->buildWhere();
        $data = $this->overridenfindQuery($where, [
            'id' => $id,
            'tenant' => $tenant,
            'id_grupoempresarial' => $id_grupoempresarial
        ])->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }

    public function findBy(array $whereFields)
    {
        $where = $this->buildWhere(array_keys($whereFields));
        $query = $this->overridenfindQuery($where, $whereFields);
        if ($query->rowCount() > 1) {
            throw new \Doctrine\ORM\NonUniqueResultException();
        }
        $data = $query->fetch();
        $data = $this->adjustQueryData($data);
        return $data;
    }

    private function overridenfindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

            t0_.negocio as \"negocio\" ,
            t0_.nome as \"nome\" ,
            t0_.localizacaocep as \"localizacaocep\" ,
            t0_.localizacaobairro as \"localizacaobairro\" ,
            t0_.localizacaorua as \"localizacaorua\" ,
            t0_.localizacaonumero as \"localizacaonumero\" ,
            t0_.localizacaocomplemento as \"localizacaocomplemento\" ,
            t0_.localizacaoreferencia as \"localizacaoreferencia\" ,
            t0_.id_pessoa as \"id_pessoa\" ,
            t0_.created_at as \"created_at\" ,
            t0_.created_by as \"created_by\" ,
            t0_.updated_at as \"updated_at\" ,
            t0_.updated_by as \"updated_by\" ,
            t0_.tenant as \"tenant\" ,
            t0_.camposcustomizados as \"camposcustomizados\" ,
            t0_.possuinegociofilho as \"possuinegociofilho\" ,
            t0_.referenciaexterna as \"referenciaexterna\" ,
            t0_.observacoes as \"observacoes\" ,
            t0_.status as \"status\" ,
            t0_.possuiseguradora as \"possuiseguradora\" ,
            t0_.id_grupoempresarial as \"id_grupoempresarial\" ,
            t0_.codigo as \"codigo\" ,
            t0_.datainicio as \"datahorainicio\" ,
            t0_.datacriacao as \"datacriacao\" ,
            t0_.localizacaopaisnome as \"localizacaopaisnome\" ,
            t0_.localizacaoestadonome as \"localizacaoestadonome\" ,
            t0_.localizacaomunicipionome as \"localizacaomunicipionome\" ,
            t0_.localizacao as \"localizacao\",
            t0_.createdcontratotaxa_at as \"createdcontratotaxa_at\" ,
            t0_.createdcontratotaxa_by as \"createdcontratotaxa_by\" ,
            t0_.valortaxaadm as \"valortaxaadm\" ,
            -- t0_.created_at as \"datacriacao\" ,
            t7_.negocioarea as \"t7_negocioarea\" ,
            t7_.nome as \"t7_nome\" ,
            t7_.localizacao as \"t7_localizacao\" ,
            t7_.permiteorcamentozerado as \"t7_permiteorcamentozerado\" ,
            t8_.midiaorigem as \"t8_midia\" ,
            t8_.codigo as \"t8_nome\" ,
            t8_.codigo as \"t8_codigo\" ,
            t19_.tiposacionamento as \"t19_tiposacionamento\" ,
            t19_.nome as \"t19_nome\" ,
            t19_.descricao as \"t19_descricao\" ,
            t11_.datacriacao as \"t11_datacriacao\" ,
            -- t11_.created_at as \"t11_datacriacao\" ,
            t11_.negocio as \"t11_negocio\" ,
            t11_.codigo as \"t11_codigo\" ,
            t11_.nome as \"t11_nome\" ,
            t11_.area as \"t11_area\" ,
            t11_.negociopai as \"t11_negociopai\" ,
            t11_.cliente as \"t11_cliente\" ,
            t11_.camposcustomizados as \"t11_camposcustomizados\" ,
            t11_.localizacaopais as \"t11_localizacaopais\" ,
            t11_.localizacaoestado as \"t11_localizacaoestado\" ,
            t11_.localizacaomunicipio as \"t11_localizacaomunicipio\" ,
            t11_.localizacaopaisnome as \"t11_localizacaopaisnome\" ,
            t11_.localizacaoestadonome as \"t11_localizacaoestadonome\" ,
            t11_.localizacaomunicipionome as \"t11_localizacaomunicipionome\" ,
            t11_.localizacaomunicipiosepultamento as \"t11_localizacaomunicipiosepultamento\" ,
            t11_.localizacaoestadosepultamento as \"t11_localizacaoestadosepultamento\" ,
            t11_.status as \"t11_status\" ,
            t11_.possuiseguradora as \"t11_possuiseguradora\" ,
            t11_.contrato as \"t11_contrato\" ,
            t11_.projeto as \"t11_projeto\" ,
            t11_.dataedicao as \"t11_dataedicao\" ,
            t11_.created_by as \"t11_created_by\" ,
            t14_.projeto as \"t14_projeto\" ,
            t14_.nome as \"t14_nome\" ,
            t14_.datainicio as \"t14_datainicio\" ,
            t14_.datafim as \"t14_datafim\" ,
            t14_.situacao as \"t14_situacao\" ,
            t18_.contrato as \"t18_contrato\" ,
            t22_.contrato as \"t22_contrato\" ,
            t3_.pais as \"t3_pais\" ,
            t3_.nome as \"t3_nome\" ,
            t1_.cidadeestrangeira as \"t1_cidadeestrangeira\" ,
            t1_.nome as \"t1_nome\" ,
            t17_.estabelecimento as \"t17_estabelecimento\" ,
            t17_.nomefantasia as \"t17_nomefantasia\" ,
            t17_.grupoempresarial as \"t17_id_grupoempresarial\" ,
            t17_.raizcnpj as \"t17_raizcnpj\" ,
            t17_.ordemcnpj as \"t17_ordemcnpj\" ,
            t17_.cnpj_completo as \"t17_cnpj_completo\" ,
            t17_.inscricaoestadual as \"t17_inscricaoestadual\" ,
            t17_.inscricaomunicipal as \"t17_inscricaomunicipal\" ,
            t17_.email as \"t17_email\" ,
            t17_.site as \"t17_site\" ,
            t17_.tipologradouro as \"t17_tipologradouro\" ,
            t17_.logradouro as \"t17_logradouro\" ,
            t17_.numero as \"t17_numero\" ,
            t17_.complemento as \"t17_complemento\" ,
            t17_.bairro as \"t17_bairro\" ,
            t17_.cidade as \"t17_cidade\" ,
            t17_.cep as \"t17_cep\" ,
            t17_.ibge as \"t17_ibge\" ,
            t17_.dddtel as \"t17_dddtel\" ,
            t17_.telefone as \"t17_telefone\" ,
            t17_.telefonecomddd as \"t17_telefonecomddd\" ,
            t17_.razaosocial as \"t17_razaosocial\" ,
            t4_.ibge as \"t4_codigo\" ,
            t4_.nome as \"t4_nome\" ,
            t20_.ibge as \"t20_codigo\" ,
            t20_.nome as \"t20_nome\" ,
            t21_.uf as \"t21_uf\" ,
            t21_.nome as \"t21_nome\" ,
            t23_.formapagamento as \"t23_formapagamento\" ,
            t23_.codigo as \"t23_codigo\" ,
            t23_.descricao as \"t23_descricao\" ,
            t23_.bloqueada as \"t23_bloqueada\" ,
            t24_.pessoamunicipio as \"t24_pessoamunicipio\" ,
            t24_.nome as \"t24_nome\" ,
            t24_.pessoa as \"t24_pessoa\" ,
            t24_.ibge as \"t24_ibge\" ,
            t6_.endereco as \"t6_endereco\" ,
            t6_.id_pessoa as \"t6_id_pessoa\" ,
            t6_.nome as \"t6_nome\" ,
            t6_.tipoendereco as \"t6_tipoendereco\" ,
            t6_.cep as \"t6_cep\" ,
            t6_.tipologradouro as \"t6_tipologradouro\" ,
            t6_.logradouro as \"t6_rua\" ,
            t6_.numero as \"t6_numero\" ,
            t6_.complemento as \"t6_complemento\" ,
            t6_.bairro as \"t6_bairro\" ,
            t6_.pais as \"t6_pais\" ,
            t6_.uf as \"t6_estado\" ,
            t6_.ibge as \"t6_municipio\" ,
            t6_.cidadeestrangeira as \"t6_cidadeestrangeira\" ,
            t6_.referencia as \"t6_referencia\" ,
            t5_.tipologradouro as \"t5_tipologradouro\" ,
            t5_.descricao as \"t5_descricao\" ,
            -- t9_.cliente as \"t9_cliente\" ,
            t9_.id as \"t9_cliente\" ,
            t9_.nome as \"t9_razaosocial\" ,
            t9_.cnpj as \"t9_cnpj\" ,
            t9_.nomefantasia as \"t9_nomefantasia\" ,
            t9_.diasparavencimento as \"t9_diasparavencimento\" ,
            --t9_.tipo as \"t9_tipo\" ,
            CASE 
                WHEN COALESCE(t9_.seguradora, false) = true THEN 1::BIGINT ELSE 0::BIGINT
            END	AS tipo,
            -- t9_.formapagamento as \"t9_formapagamento\" ,
            t9_.id_formapagamento as \"t9_formapagamento\" ,
            -- t9_.formapagamento as \"t9_formapagamentoguid\" ,
            t9_.id_formapagamento as \"t9_formapagamentoguid\" ,
            t9_.anotacao as \"t9_anotacao\" ,
            t9_.id_conta_receber as \"t9_conta\" ,
            t2_.uf as \"t2_uf\" ,
            t2_.nome as \"t2_nome\" ,
            t0_.localizacaonome as \"localizacaonome\"
        FROM crm.vwatcssimplesecomseguradora_v12 t0_
        -- FROM crm.negocios t0_
            LEFT JOIN crm.atcsareas t7_ ON t0_.area = t7_.negocioarea and t0_.tenant = t7_.tenant
            LEFT JOIN crm.midiasorigem t8_ ON t0_.origem = t8_.midiaorigem and t0_.tenant = t8_.tenant
            LEFT JOIN crm.tiposacionamentos t19_ ON t0_.tiposacionamento = t19_.tiposacionamento and t0_.tenant = t19_.tenant
            LEFT JOIN crm.vwatcssimplesecomseguradora_v12 t11_ ON t0_.negociopai = t11_.negocio and t0_.tenant = t11_.tenant
            -- LEFT JOIN crm.negocios t11_ ON t0_.negociopai = t11_.negocio and t0_.tenant = t11_.tenant
            LEFT JOIN financas.projetos t14_ ON t0_.projeto = t14_.projeto and t0_.tenant = t14_.tenant
            --LEFT JOIN financas.vw_contratos t18_ ON t0_.contrato = t18_.contrato
            LEFT JOIN financas.contratos t18_ ON t0_.contrato = t18_.contrato and t0_.tenant = t18_.tenant
            LEFT JOIN financas.vw_contratos t22_ ON t0_.contratotaxaadm = t22_.contrato   and t0_.tenant = t22_.tenant   and t0_.id_grupoempresarial = t22_.id_grupoempresarial 
            LEFT JOIN ns.paises t3_ ON t0_.localizacaopais = t3_.pais
            LEFT JOIN ns.cidadesestrangeiras t1_ ON t0_.localizacaocidadeestrangeira = t1_.cidadeestrangeira
            LEFT JOIN ns.vw_estabelecimentos t17_ ON t0_.estabelecimento = t17_.estabelecimento and t0_.tenant = t17_.tenant
            LEFT JOIN ns.municipios t4_ ON t0_.localizacaomunicipio = t4_.ibge
            LEFT JOIN ns.municipios t20_ ON t0_.localizacaomunicipiosepultamento = t20_.ibge
            LEFT JOIN ns.estados t21_ ON t0_.localizacaoestadosepultamento = t21_.uf
            LEFT JOIN ns.formaspagamentos t23_ ON t0_.formapagamentotaxaadm = t23_.formapagamento   and t0_.tenant = t23_.tenant 
            LEFT JOIN servicos.vwpessoasmunicipios_v2 t24_ ON t0_.municipioprestacaotaxaadm = t24_.pessoamunicipio   and t0_.tenant = t24_.tenant   and t0_.id_grupoempresarial = t24_.grupoempresarial 
            LEFT JOIN ns.enderecos t6_ ON t0_.localizacao = t6_.endereco and t0_.tenant = t6_.tenant
            LEFT JOIN ns.tiposlogradouros t5_ ON t0_.localizacaotipologradouro = t5_.tipologradouro
            -- LEFT JOIN ns.vw_clientes_v2 t9_ ON t0_.cliente = t9_.cliente
            LEFT JOIN ns.pessoas t9_ ON t0_.cliente = t9_.id and t0_.tenant = t9_.tenant
            LEFT JOIN ns.estados t2_ ON t0_.localizacaoestado = t2_.uf
        {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    public function proccessFilter($filter) {
        $resultado = [];

        if(is_null($filter)){
            return [];
        }

        if(empty($filter->getKey() || $filter->getKey() == '0')){
            return [];
        }
  
        // if(empty($filter->getField())){
        //     return [];
        // }

        $filtro = StringUtils::removeTabulacoes(
            StringUtils::removeln(
                // StringUtils::removeCaracteresInvalidosNoTsQuery(
                    strtolower(StringUtils::removeAcentos(
                            html_entity_decode(
                                strip_tags($filter->getKey())
                            )
                        )
                    )
                // )
            )
        );

        $keys = explode(" ", $filtro);

        for ($i = 0; $i < count($keys); $i++) {
            if (!empty($keys[$i])) {
                $resultado[] = $i === (\count($keys) - 1) 
                    ? " (to_tsquery('simple','" . $keys[$i] . ":*'" . "))::tsquery)" //se for o último adicionar :*
                    : " (to_tsquery('simple','" . $keys[$i] . "'))::tsquery "; //senão for o último crie sem :*
            }
        }
  
        return $resultado;
      }

    public function findAllQueryBuilderBody($queryBuilder, $tenant, $id_grupoempresarial, Filter $filter = null)
    {
        $binds = [];
        $where = [];

        if ($filter) {

            $tsQueries = $this->proccessFilter($filter);

            if (!empty($tsQueries)){
                $queryBuilder->addSelect(array("COALESCE(ts_rank_cd(t0_.search_fts," . join("&&", $tsQueries) . ", 0) as rank"));
                $queryBuilder->addOrderBy('rank', 'DESC');
                $where[] = $queryBuilder->expr()->gt("COALESCE(ts_rank_cd(t0_.search_fts," . join("&&", $tsQueries) . ", 0)", "0");
                $filter->setKey(null);
            }

            if(!empty($filter->getOrder())) {
                foreach ($filter->getOrder() as $column => $direction) {
                    $queryBuilder->addOrderBy("t0_.{$this->getOrders()[$column]}", strtoupper($direction));
                }
            }
        }
        $queryBuilder->addOrderBy("t0_.datacriacao", "DESC");
        $queryBuilder->addOrderBy("t0_.codigo", "ASC");
        $queryBuilder->addOrderBy("t0_.negocio", "ASC");

        $queryBuilder->setMaxResults(50);

        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

        $where[] = $queryBuilder->expr()->eq("t0_.id_grupoempresarial", "?");
        $binds[] = $id_grupoempresarial;

        list($offsets, $offsetsBinds) = $this->processOffset($filter);
        $where = array_merge($where, $offsets);
        $binds = array_merge($binds, $offsetsBinds);

        list($filters, $filtersBinds) = $this->processFilter($filter);
        $binds = array_merge($binds, $filtersBinds);

        list($filtersExpression, $filtersBinds) = $this->processFilterExpression($filter);
        $binds = array_merge($binds, $filtersBinds);

        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }
        if (!empty($filters)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }
        if (!empty($filtersExpression)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_AND, $filtersExpression));
        }

        return $binds;
    }

    /**
     * @param string  $atc
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @return array 
     * @throws \Exception
     */
    public function findSimples($atc, $tenant, $id_grupoempresarial)
    {
        $sql_1 = "    
            select
                negocio,
                area,
                cliente,
                possuiseguradora,
                tenant,
                id_grupoempresarial
            from crm.vwatcssimplesecomseguradora_v12
            where negocio = :atc
            and tenant = :tenant
            and id_grupoempresarial = :id_grupoempresarial
        ";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("atc", $atc);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("id_grupoempresarial", $id_grupoempresarial);
        $stmt_1->execute();
        $result = $stmt_1->fetch();
        return $result;
    }
}
