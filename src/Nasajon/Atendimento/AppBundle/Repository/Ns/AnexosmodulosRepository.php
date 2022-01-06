<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\Ns\AnexosmodulosRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

class AnexosmodulosRepository extends ParentRepository {

    public function findAll($tenant, $modulodoanexo, $id_modulodoanexo, Filter $filter = null) {

        $this->validateOffset($filter);

        list($queryBuilder, $binds) = $this->findAllQueryBuilder($tenant, $modulodoanexo, $id_modulodoanexo, $filter);

        $queryBuilder->addSelect('dg.uuidarquivo');
        $queryBuilder->addSelect('dg.mimetype');
        $queryBuilder->addSelect('dg.hash');
        $queryBuilder->leftJoin('t0_', 'ns.documentosged', 'dg', 't0_.documentoged = dg.documentoged');

        $stmt = $this->getConnection()->prepare($queryBuilder->getSQL());
        $stmt->execute($binds);

        return $stmt->fetchAll();
    }
    
    public function buscaArquivoNotaPorTipo($idmoduloanexo, $tipo) {
        $sql = "SELECT dg.documento"
                . " FROM ns.anexosmodulos am"
                . " INNER JOIN ns.documentosged dg ON dg.documentoged = am.documentoged"
                . " WHERE am.id_modulodoanexo = :idmoduloanexo"
                . " AND am.tipo = :tipo";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            'idmoduloanexo' => $idmoduloanexo,
            'tipo' => $tipo
        ]);

        $data = $stmt->fetchColumn();

        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }
        return $data;
    }

}
