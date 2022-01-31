<?php

namespace Nasajon\AppBundle\Repository;

use Nasajon\MDABundle\Repository\AbstractRepository as ParentRepository;

class TenantsRepository extends ParentRepository {

  public function findOneByTenant($tenant)
  {
    $sql = " SELECT t0_.codigo, t0_.tenant
                 FROM ns.tenants t0_
                 WHERE t0_.tenant = :tenant";
    $data = $this->getConnection()->executeQuery($sql, [
            'tenant' => $tenant,
        ])->fetch();
    if (!$data) {
      throw new \Doctrine\ORM\NoResultException();
    }
    return $data;
  }
  
  public function findOneByCodigo($codigo) {
    $sql = " SELECT t0_.codigo, t0_.tenant
                 FROM ns.tenants t0_
                 WHERE t0_.codigo = :codigo";
    $data = $this->getConnection()->executeQuery($sql, [
            'codigo' => $codigo,
        ])->fetch();
    if (!$data) {
      throw new \Doctrine\ORM\NoResultException();
    }
    return $data;
  }

  public function findConjuntosByGrupoEmpresarial($tenant)
    {
        $sql = "SELECT 
                    gr.grupoempresarial as idGrupoEmpresarial,
                    gr.codigo as codigoGrupoEmpresarial,
                    gr.descricao as descricaoGrupoEmpresarial,
                    em.empresa as idEmpresa,
                    em.codigo as codigoEmpresa,
                    es.estabelecimento as idEstabelecimento,
                    es.codigo as codigoEstabelecimento,
                    ec.conjunto as idConjunto,
                    ec.cadastro as tipoConjunto
                FROM ns.tenants te
                INNER JOIN ns.gruposempresariais  gr on te.tenant = gr.tenant  
                INNER JOIN ns.empresas em on gr.grupoempresarial = em.grupoempresarial and gr.tenant = em.tenant
                INNER JOIN ns.estabelecimentos es on em.empresa  = es.empresa and es.tenant = em.tenant
                INNER JOIN ns.estabelecimentosconjuntos ec ON es.estabelecimento = ec.estabelecimento AND ec.permissao and es.tenant = ec.tenant
                INNER JOIN ns.conjuntos c ON c.conjunto = ec.conjunto and c.tenant = ec.tenant
                WHERE te.tenant = :tenant";
        $data = $this->getConnection()->executeQuery($sql, ['tenant' => $tenant,])->fetchAll();
      
        return $data;
    }

  public function findGruposEmpresariaisByCodigoTenant($codigo)
  {
      $sql = " SELECT ge.grupoempresarial, ge.codigo, ge.descricao
                FROM ns.gruposempresariais  ge
                INNER JOIN ns.tenants t on ge.tenant = t.tenant
                WHERE t.codigo = :codigo ";
      $data = $this->getConnection()->executeQuery($sql, ['codigo' => $codigo,])->fetchAll();
      
      return $data;
  }

  public function findGruposEmpresariaisByCodigoGrupo($codigo)
    {
        $sql = " SELECT grupoempresarial, codigo, descricao
                 FROM ns.gruposempresariais 
                 WHERE codigo = :codigo ";
        $data = $this->getConnection()->executeQuery($sql, ['codigo' => $codigo,])->fetch();
        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }
        return $data;
    }

    public function findGruposEmpresariaisByTenant($tenant)
    {
        $sql = " SELECT ge.grupoempresarial, ge.codigo, ge.descricao
                 FROM ns.gruposempresariais  ge
                 INNER JOIN ns.tenants t on ge.tenant = t.tenant
                 WHERE t.tenant = :tenant ";

        $data = $this->getConnection()->executeQuery($sql, [
            'tenant' => $tenant,
        ])->fetchAll();

        return $data;
    }

    public function findGruposEmpresariaisByCodigoGrupoAndTenant($codigo, $tenant)
    {
        $sql = " SELECT grupoempresarial, codigo, descricao
                 FROM ns.gruposempresariais 
                 WHERE codigo = :codigo 
                 AND tenant = :tenant";
        $data = $this->getConnection()->executeQuery($sql, [
            'codigo' => $codigo,
            'tenant' => $tenant,
        ])->fetch();
        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }
        return $data;
    }
}