<?php

namespace AppBundle\Repository\Meurh;

use Doctrine\DBAL\Connection;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\LoginBundle\Workflow\Traits\WorkflowRepositoryTrait;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesalteracoesenderecos;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesalteracoesenderecosRepository as ParentRepository;

class SolicitacoesalteracoesenderecosRepository extends ParentRepository
{
    use WorkflowRepositoryTrait;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->setLinks([
            [
                'field' => 'tipologradouro',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Tiposlogradouros',
                'alias' => 't1_',
                'identifier' => 'tipologradouro',
                'type' => 2
            ],
            [
                'field' => 'municipioresidencia',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Municipios',
                'alias' => 't2_',
                'identifier' => 'ibge',
                'type' => 2
            ],
            [
                'field' => 'paisresidencia',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Paises',
                'alias' => 't3_',
                'identifier' => 'pais',
                'type' => 2
            ],
            [
                'field' => 'trabalhador',
                'entity' => 'Nasajon\MDABundle\Entity\Persona\Trabalhadores',
                'alias' => 't4_',
                'identifier' => 'trabalhador',
                'type' => 2
            ],

        ]);
    }

    public function find($id, $tenant, $trabalhador)
    {
        $where = $this->buildWhere();
        $whereFields = [
            'id' => $id,
            'tenant' => $tenant,
            'trabalhador' => $trabalhador
        ];

        $data = $this->findQuery($where, $whereFields)->fetch();
        
        return $this->adjustQueryData($data);
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
                    t0_.logradouro as \"logradouro\" ,
                    t0_.numero as \"numero\" ,
                    t0_.complemento as \"complemento\" ,
                    t0_.cep as \"cep\" ,
                    t0_.bairro as \"bairro\" ,
                    t0_.email as \"email\" ,
                    t0_.dddtel as \"dddtel\" ,
                    t0_.telefone as \"telefone\" ,
                    t0_.dddcel as \"dddcel\" ,
                    t0_.celular as \"celular\" ,
                    t0_.wkf_data as \"wkf_data\" ,
                    t0_.wkf_estado as \"wkf_estado\" ,
                    t1_.tipologradouro as \"t1_tipologradouro\" ,
                    t1_.descricao as \"t1_descricao\" ,
                    t2_.ibge as \"t2_ibge\" ,
                    t2_.nome as \"t2_nome\" ,
                    t2_.uf as \"t2_uf\" ,
                    t3_.pais as \"t3_pais\" ,
                    t3_.nome as \"t3_nome\",
                    t4_.nome as \"t4_nome\" ,
                    t4_.trabalhador as \"t4_trabalhador\" 
                FROM meurh.solicitacoesalteracoesenderecos t0_
                LEFT JOIN ns.tiposlogradouros t1_ ON t0_.tipologradouro = t1_.tipologradouro 
                LEFT JOIN ns.municipios t2_ ON t0_.municipioresidencia = t2_.ibge 
                LEFT JOIN ns.paises t3_ ON t0_.paisresidencia = t3_.pais         
                LEFT JOIN persona.trabalhadores t4_ ON t0_.trabalhador = t4_.trabalhador AND t0_.tenant = t4_.tenant
                {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    public function findDraft($id, $tenant)
    {
        $where = "WHERE t0_.solicitacao = :id AND t0_.tenant = :tenant";

        $sql = "SELECT 
                    t0_.solicitacao as \"solicitacao\" ,
                    t0_.tenant as \"tenant\" ,
                    t0_.trabalhador as \"trabalhador\"
                FROM meurh.solicitacoesalteracoesenderecos t0_ 
                $where";

        $binds = [
            "id" => $id,
            "tenant" => $tenant
        ];

        return $this->getConnection()->executeQuery($sql, $binds)->fetch();
    }

    /**
     * @param string $trabalhador
     * @param int $tenant
     * @param string $logged_user
     * @return array
     */
    public function draftInsert($trabalhador, $tenant, $logged_user, Solicitacoesalteracoesenderecos $entity)
    {
        $sql_1 = "SELECT mensagem FROM meurh.api_solicitacaoenderecosrascunho(
                    row(
                        :trabalhador,
                        :estabelecimento,
                        :tenant,
                        :created_by,
                        :justificativa,
                        :observacao,
                        :tipologradouro,
                        :logradouro,
                        :numero,
                        :complemento,
                        :cep,
                        :municipioresidencia,
                        :bairro,
                        :paisresidencia,
                        :email,
                        :dddtel,
                        :telefone,
                        :dddcel,
                        :celular,
                        :origem
                    )::meurh.tsolicitacaoenderecosrascunho
                );";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("trabalhador", $trabalhador);
        $stmt_1->bindValue("estabelecimento", $entity->getEstabelecimento());
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("created_by", json_encode($logged_user));
        $stmt_1->bindValue("justificativa", $entity->getJustificativa());
        $stmt_1->bindValue("observacao", $entity->getObservacao());
        $stmt_1->bindValue("tipologradouro", ($entity->getTipologradouro()) ? $entity->getTipologradouro()->getTipologradouro() : NULL);
        $stmt_1->bindValue("logradouro", $entity->getLogradouro());
        $stmt_1->bindValue("numero", $entity->getNumero());
        $stmt_1->bindValue("complemento", $entity->getComplemento());
        $stmt_1->bindValue("cep", $entity->getCep());
        $stmt_1->bindValue("municipioresidencia", ($entity->getMunicipioresidencia()) ? $entity->getMunicipioresidencia()->getIbge() : NULL);
        $stmt_1->bindValue("bairro", $entity->getBairro());
        $stmt_1->bindValue("paisresidencia", ($entity->getPaisresidencia()) ? $entity->getPaisresidencia()->getPais() : NULL);
        $stmt_1->bindValue("email", $entity->getEmail());
        $stmt_1->bindValue("dddtel", $entity->getDddtel());
        $stmt_1->bindValue("telefone", $entity->getTelefone());
        $stmt_1->bindValue("dddcel", $entity->getDddcel());
        $stmt_1->bindValue("celular", $entity->getCelular());
        $stmt_1->bindValue("origem", "2");
        $stmt_1->execute();

        $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entity);
        $entity->setSolicitacao($resposta);

        return $this->find($resposta, $tenant, $trabalhador);
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @return string 
     * @throws \Exception
     */
    public function abrir($tenant, $logged_user, Solicitacoesalteracoesenderecos $entity)
    {
        $sql_1 = "SELECT mensagem FROM meurh.api_solicitacaoenderecosabrir(
                    row(
                        :solicitacao,
                        :tenant,
                        :justificativa,
                        :observacao,
                        :updated_by,
                        :tipologradouro,
                        :logradouro,
                        :numero,
                        :complemento,
                        :cep,
                        :municipioresidencia,
                        :bairro,
                        :paisresidencia,
                        :email,
                        :dddtel,
                        :telefone,
                        :dddcel,
                        :celular
                    )::meurh.tsolicitacaoenderecosabrir
                );";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("solicitacao", $entity->getSolicitacao());
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("justificativa", $entity->getJustificativa());
        $stmt_1->bindValue("observacao", $entity->getObservacao());
        $stmt_1->bindValue("updated_by", json_encode($logged_user));
        $stmt_1->bindValue("tipologradouro", ($entity->getTipologradouro()) ? $entity->getTipologradouro()->getTipologradouro() : NULL);
        $stmt_1->bindValue("logradouro", $entity->getLogradouro());
        $stmt_1->bindValue("numero", $entity->getNumero());
        $stmt_1->bindValue("complemento", $entity->getComplemento());
        $stmt_1->bindValue("cep", $entity->getCep());
        $stmt_1->bindValue("municipioresidencia", ($entity->getMunicipioresidencia()) ? $entity->getMunicipioresidencia()->getIbge() : NULL);
        $stmt_1->bindValue("bairro", $entity->getBairro());
        $stmt_1->bindValue("paisresidencia", ($entity->getPaisresidencia()) ? $entity->getPaisresidencia()->getPais() : NULL);
        $stmt_1->bindValue("email", $entity->getEmail());
        $stmt_1->bindValue("dddtel", $entity->getDddtel());
        $stmt_1->bindValue("telefone", $entity->getTelefone());
        $stmt_1->bindValue("dddcel", $entity->getDddcel());
        $stmt_1->bindValue("celular", $entity->getCelular());
        $stmt_1->execute();

        return $this->processApiReturn($stmt_1->fetchColumn(), $entity);
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @return string 
     * @throws \Exception
     */
    public function update($tenant, $logged_user, Solicitacoesalteracoesenderecos $entity)
    {
        parent::update($tenant, $logged_user, $entity);
        $retorno["solicitacao"] = $entity->getSolicitacao();

        return $retorno;
    }

    /**
     * @param int $tenant
     * @param string $trabalhador
     * @return array
     */
    public function findAllQueryBuilder($tenant, $trabalhador, Filter $filter = null)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        $selectArray = [
            't0_.solicitacao as solicitacao',
            't0_.trabalhador as trabalhador',
            't0_.codigo as codigo',
            't0_.estabelecimento as estabelecimento',
            't0_.situacao as situacao',
            't0_.created_at as created_at',
            't0_.origem as origem',
            't0_.wkf_data as wkf_data',
            't0_.wkf_estado as wkf_estado'
        ];
         
        if ($filter && empty($filter->getOffset())) {
            array_push($selectArray, 'count(*) OVER() AS full_count');
        }

        $queryBuilder->select($selectArray);
        $queryBuilder->from('meurh.solicitacoesalteracoesenderecos', 't0_');

        $binds = $this->findAllQueryBuilderBody($queryBuilder, $tenant, $trabalhador, $filter);

        return [$queryBuilder, $binds];
    }
}