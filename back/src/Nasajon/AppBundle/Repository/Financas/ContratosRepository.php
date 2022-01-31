<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Repository\Financas;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Financas\ContratosRepository as ParentRepository;

/**
 * Método findQuery (private) substituído por overridenfindQuery para otimizar query, deixando de dar joins com views desnecessariamente.
 * Métodos find e findBy sobrescritos para usar novo método acima. Foi necessário pois findQuery original é private, por tanto, não possível de ser sobrescrito.
 */
class ContratosRepository extends ParentRepository
{

    private function overridenfindQuery(string $where, array $whereFields)
    {
        $where = str_replace("t0_.id_grupoempresarial", "t7_.grupoempresarial", $where);
        $sql = "SELECT

                                t0_.contrato as \"contrato\" ,
                                t0_.codigo as \"codigo\" ,
                                t0_.descricao as \"descricao\" ,
                                t0_.tenant as \"tenant\" ,
                                -- t0_.id_grupoempresarial as \"id_grupoempresarial\" ,
                                t7_.grupoempresarial as \"id_grupoempresarial\" ,
                                t0_.tipocontrato as \"tipocontrato\" ,
                                t0_.cancelado as \"cancelado\" ,
                                t0_.emitirnotafiscal as \"emitirnotafiscal\" ,
                                t0_.descontoglobalitensnaofaturados as \"descontoglobalitensnaofaturados\" ,
                                t0_.numerorps as \"numerorps\" ,
                                t3_.conta as \"t3_conta\" ,
                                t3_.codigo as \"t3_codigo\" ,
                                t3_.nome as \"t3_nome\" ,
                                t3_.bloqueado as \"t3_bloqueado\" ,
                                t2_.estabelecimento as \"t2_estabelecimento\" ,
                                t2_.nomefantasia as \"t2_nomefantasia\" ,
                                t7_.grupoempresarial as \"t2_id_grupoempresarial\" ,
                                t4_.formapagamento as \"t4_formapagamento\" ,
                                t4_.codigo as \"t4_codigo\" ,
                                t4_.descricao as \"t4_descricao\" ,
                                t4_.bloqueada as \"t4_bloqueada\" ,
                                -- t1_.cliente as \"t1_cliente\" ,
                                t1_.id as \"t1_cliente\" ,
                                t1_.nome as \"t1_razaosocial\" ,
                                t1_.nomefantasia as \"t1_nomefantasia\" ,
                                t1_.cnpj as \"t1_cnpj\" ,
                                t1_.diasparavencimento as \"t1_diasparavencimento\" ,
                                -- t1_.tipo as \"t1_tipo\" ,
                                CASE
                                    WHEN COALESCE(t1_.seguradora, false) = true THEN 1::bigint
                                    ELSE 0::bigint
                                END AS \"t1_tipo\",
                                -- t1_.formapagamento as \"t1_formapagamento\" ,
                                -- t1_.formapagamento as \"t1_formapagamentoguid\" ,
                                t1_.id_formapagamento as \"t1_formapagamento\" ,
                                t1_.id_formapagamento as \"t1_formapagamentoguid\" ,
                                t1_.anotacao as \"t1_anotacao\" ,
                                t5_.pessoamunicipio as \"t5_pessoamunicipio\" ,
                                t6_.nome as \"t5_nome\" ,
                                t5_.pessoa as \"t5_pessoa\" ,
                                t5_.ibge as \"t5_ibge\" 
            
                -- FROM financas.vw_contratos t0_
                FROM financas.contratos t0_

                                    LEFT JOIN financas.contas t3_ ON t0_.conta = t3_.conta and t0_.tenant = t3_.tenant

                            -- LEFT JOIN servicos.vwestabelecimentos t2_ ON t0_.estabelecimento = t2_.estabelecimento
                            LEFT JOIN ns.estabelecimentos t2_ ON t0_.estabelecimento = t2_.estabelecimento and t0_.tenant = t2_.tenant
                            LEFT JOIN ns.empresas t7_ ON t2_.empresa = t7_.empresa and t2_.tenant = t7_.tenant

                            LEFT JOIN ns.formaspagamentos t4_ ON t0_.id_formapagamento = t4_.formapagamento and t0_.tenant = t4_.tenant

                            LEFT JOIN ns.pessoas t1_ ON t0_.participante = t1_.id and t0_.tenant = t1_.tenant

                            -- LEFT JOIN servicos.vwpessoasmunicipios t5_ ON t0_.pessoamunicipio = t5_.pessoamunicipio
                            LEFT JOIN ns.pessoasmunicipios t5_ ON t0_.pessoamunicipio = t5_.pessoamunicipio and t0_.tenant = t5_.tenant
                            LEFT JOIN ns.municipios t6_ ON t6_.ibge::text = t5_.ibge::text

        
        {$where}";
        
        return $this->getConnection()->executeQuery($sql, $whereFields);
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

}
