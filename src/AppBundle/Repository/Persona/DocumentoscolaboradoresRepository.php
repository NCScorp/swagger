<?php

namespace AppBundle\Repository\Persona;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Persona\DocumentoscolaboradoresRepository as ParentRepository;

/**
 * DocumentoscolaboradoresRepository
 *
 */
class DocumentoscolaboradoresRepository extends ParentRepository
{
    public function findDocument(string $where, array $whereFields)
    {
        $sql = "SELECT t0_.bindocumento as \"bindocumento\"
                FROM persona.documentoscolaboradores t0_
                INNER JOIN persona.tiposdocumentoscolaboradores t1_ ON t0_.tipodocumentocolaborador = t1_.tipodocumentocolaborador

                     
        {$where}";

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    public function findSolicitacaoMaisRecente($tenant, $trabalhador) {
        $sql = "SELECT 
                    t0_.documentocolaborador as \"documentocolaborador\",
                    t1_.solicitacao as \"solicitacao\"
                FROM persona.documentoscolaboradores t0_
                INNER JOIN meurh.solicitacoesalteracoesenderecos t1_ on t0_.solicitacao = t1_.solicitacao
                WHERE t0_.tenant = :tenant
                  AND t0_.trabalhador = :trabalhador
                  AND t1_.situacao in (1,2,4)
                ORDER BY t1_.lastupdate DESC LIMIT 1";

        $data = $this->getConnection()->executeQuery($sql, [
            'tenant' => $tenant,
            'trabalhador' => $trabalhador
        ])->fetch();

        return $data;
    }

    public function findbyDocument($id, $tenant, $trabalhador) {
        $sql = "SELECT bindocumento, urldocumento
                FROM persona.documentoscolaboradores
                WHERE documentocolaborador = :id
                  AND tenant = :tenant
                  AND trabalhador = :trabalhador";

        $data = $this->getConnection()->executeQuery($sql, [
            'id' => $id,
            'tenant' => $tenant,
            'trabalhador' => $trabalhador
        ])->fetch();

        return $data;
    }
}
