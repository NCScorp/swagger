<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Nasajon\MDABundle\Repository\Servicos\OrdensServicoRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Servicos\OrdensServico\ItemRepository;

class OrdensServicoRepository extends ParentRepository {

    /**
     * \Nasajon\MDABundle\Repository\Servicos\OrdensServico\ItemRepository
     */
    protected $srvcsRdnsSrvcTmRpstry;

    /**
     * \Nasajon\MDABundle\Repository\Servicos\VisitaRepository
     */
    protected $srvcsVstRpstry;

    public function __construct($connection, $srvcsRdnsSrvcTmRpstry, $srvcsVstRpstry) {
        parent::__construct($connection, $srvcsRdnsSrvcTmRpstry, $srvcsVstRpstry);

        $this->srvcsRdnsSrvcTmRpstry = $srvcsRdnsSrvcTmRpstry;
        $this->srvcsVstRpstry = $srvcsVstRpstry;


        $this->setFilters([
            'numero' => 'numero',
        ]);
        $this->setLinks([
            [
                'field' => 'tipoordemservico_id',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\TipoOrdensServico',
                'alias' => 't1_',
                'identifier' => 'tipoordemservico',
            ],
            [
                'field' => 'estabelecimento',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Estabelecimento',
                'alias' => 't3_',
                'identifier' => 'id',
            ],
            [
                'field' => 'cliente',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Clientes',
                'alias' => 't4_',
                'identifier' => 'cliente',
            ],
            [
                'field' => 'pessoamunicipio',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Pessoamunicipio',
                'alias' => 't5_',
                'identifier' => 'pessoamunicipio',
            ],
            [
                'field' => 'ativo',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\Ativos',
                'alias' => 't6_',
                'identifier' => 'servico_id',
            ],
            [
                'field' => 'enderecocliente',
                'entity' => 'Nasajon\MDABundle\Entity\Ns\Enderecos',
                'alias' => 't7_',
                'identifier' => 'endereco',
            ],
            [
                'field' => 'atendimento_vinculado',
                'entity' => 'Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes',
                'alias' => 't9_',
                'identifier' => 'atendimento',
            ],
            [
                'field' => 'projeto',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\Projetos',
                'alias' => 't10_',
                'identifier' => 'projeto',
            ],
            [
                'field' => 'tipo_manutencao',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\TipoManutencao',
                'alias' => 't13_',
                'identifier' => 'tipomanutencao',
            ],
            [
                'field' => 'operacao',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\OrdensServico\Operacao',
                'alias' => 't14_',
                'identifier' => 'operacao',
            ],
            [
                'field' => 'contrato',
                'entity' => 'Nasajon\MDABundle\Entity\Financas\Contrato',
                'alias' => 't15_',
                'identifier' => 'id',
            ],
            [
                'field' => 'os_retorno',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\OrdensServico',
                'alias' => 't2_',
                'identifier' => 'ordemservico',
            ],
            [
                'field' => 'ordemservico_vinculada',
                'entity' => 'Nasajon\MDABundle\Entity\Servicos\OrdensServico',
                'alias' => 't8_',
                'identifier' => 'ordemservico',
            ],
        ]);
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Servicos\OrdensServico $entity
     * @return string 
     * @throws \Exception
     */
    public function encerrar($tenant, $logged_user, \Nasajon\MDABundle\Entity\Servicos\OrdensServico $entity) {
        $this->getConnection()->beginTransaction();
        try {
            $sql_1 = " SELECT mensagem
            FROM  servicos.api_ordemservicoencerrar(
                        row(
                        :ordemservico,
                        :tenant,
                        :updated_by
                        )::servicos.tordemservicoencerrar
                      ); ";
            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("ordemservico", $entity->getOrdemservico());
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("updated_by", json_encode($logged_user));
            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $retorno = $resposta;


            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $retorno;
    }

    public function find($id, $tenant) {
        $data = parent::find($id, $tenant);
        $data['pessoamunicipio'] = $this->getPessoaMunicipio($data['pessoamunicipio']['pessoamunicipio'], $tenant);
        return $data;
    }

    public function getPessoaMunicipio($id, $tenant) {
        $sql = "SELECT t1.pessoamunicipio, t2.ibge, t2.nome, t2.uf 
                FROM ns.pessoasmunicipios t1
                JOIN ns.municipios t2 on t1.ibge = t2.ibge
                WHERE t1.pessoamunicipio = :pessoamunicipio AND t1.tenant = :tenant ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('pessoamunicipio', $id);
        $stmt->bindValue('tenant', $tenant);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado?$resultado:[];
;    }

}
