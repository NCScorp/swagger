<?php

namespace AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\AbstractRepository as ParentRepository;

class TenantsRepository extends ParentRepository {

    public function findOneByCodigo($codigo) {
        $sql = " SELECT codigo, tenant
          FROM ns.tenants 
          WHERE LOWER(codigo) = LOWER(:codigo); 
          ";

        $data = $this->getConnection()->executeQuery($sql, [
                    'codigo' => $codigo
                ])->fetch();
        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }

        return $data;
    }


    public function getTenantProfile($codigo) {
        $sql = " SELECT 
        nome as nome,
        codigo as codigo,
        tenant as id,
        null as logo
        FROM ns.tenants t0_
        WHERE LOWER(codigo) = LOWER(:codigo)";

        $data = $this->getConnection()->executeQuery($sql, [
                    'codigo' => $codigo
                ])->fetch();
        return $data;
    }


    public function getGrupoProfile($tenant) {
        $sql = "SELECT 
        t0_.grupoempresarial as id,
        t0_.codigo as codigo,
        t0_.descricao as nome
        FROM ns.gruposempresariais t0_
        WHERE t0_.tenant = :tenant";

        $data = $this->getConnection()->executeQuery($sql, [
                    'tenant' => $tenant
                ])->fetchAll();
        return $data;
    }

    public function getEmpresaProfile($grupoempresarial) {
        $sql = "SELECT 
        t0_.empresa as id,
        t0_.codigo as codigo,
        t0_.razaosocial as razaosocial,
        t0_.razaosocial as nomefantasia,
        null as cpfcnpj
        FROM ns.empresas t0_
        WHERE t0_.grupoempresarial = :grupoempresarial";

        $data = $this->getConnection()->executeQuery($sql, [
                    'grupoempresarial' => $grupoempresarial
                ])->fetchAll();
        return $data;
    }


    public function getEstabelecimentoProfile($empresa) {
        $sql = "SELECT 
        t0_.estabelecimento as id,
        t0_.codigo as codigo,
        t0_.nomefantasia as razaosocial,
        t0_.nomefantasia as nomefantasia,
        null as cpfcnpj
        FROM ns.estabelecimentos t0_
        WHERE t0_.empresa = :empresa";

        $data = $this->getConnection()->executeQuery($sql, [
                    'empresa' => $empresa
                ])->fetchAll();
        return $data;
    }

}