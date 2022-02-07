<?php

namespace AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\Ns\EstabelecimentosRepository as ParentRepository;

class EstabelecimentosRepository extends ParentRepository
{

  /**
   * @deprecated 
   * Esse método só retorna os estabelecimento no qual o trabalhador está
   * @param integer $tenant id ex: 47
   * @param string $contanasajon email do usuário ex: fulano@nasajon.com.br
   */
  public function findEstabelecimentosByEmailTrabalhador($tenant, $contanasajon, $estabelecimentos = null)
  {
    $where = "";

    if(!empty($estabelecimentos)) {
      $where = " AND (";
      foreach($estabelecimentos as $index => $estabelecimento) {
        $where.=  $index == 0 ? " e.estabelecimento = '$estabelecimento' " : " OR e.estabelecimento = '$estabelecimento' ";
      }
      
      $where.= ")";
    }

    $sql = " SELECT e.estabelecimento, e.nomefantasia, tr.trabalhador
               FROM ns.estabelecimentos  e
               LEFT JOIN persona.trabalhadores tr on tr.tenant = e.tenant AND tr.estabelecimento = e.estabelecimento
               WHERE e.tenant = :tenant AND tr.identificacaonasajon = :contanasajon".$where;

    $data = $this->getConnection()->executeQuery($sql, [
      'tenant' => $tenant,
      'contanasajon' => $contanasajon
    ])->fetchAll();

    return $data;
  }


  public function findEstabelecimentoComGrupo($tenant, $estabelecimento)
  {

    $sql = "SELECT e.tenant, e.estabelecimento, emp.empresa, g.codigo as grupoempresarial
            FROM ns.estabelecimentos e
            LEFT JOIN ns.empresas emp on emp.tenant = e.tenant AND emp.empresa = e.empresa
            LEFT JOIN ns.gruposempresariais g on g.tenant = emp.tenant AND g.grupoempresarial = emp.grupoempresarial
            WHERE e.tenant = :tenant AND e.estabelecimento = :estabelecimento";

    $data = $this->getConnection()->executeQuery($sql, [
      'tenant' => $tenant,
      'estabelecimento' => $estabelecimento
    ])->fetchAll();

    return $data;
  }

  public function findEmpresaByEstabelecimento($tenant, $estabelecimento){
    $sql = "select emp.empresa as empresa
                   ,emp.codigo as codigo
                   ,emp.razaosocial as razaosocial
                   ,emp.raizcnpj as cnpj
                   ,est.codigo as codigoest
                   ,est.nomefantasia as nomefantasiaest
                   ,est.estabelecimento as estabelecimento
                   ,est.raizcnpj as raizcnpjestabelecimento
                   ,est.ordemcnpj as ordemcnpjestabelecimento
                   ,est.cpf as cpfestabelecimento
            from ns.empresas emp
            join ns.estabelecimentos est on est.empresa = emp.empresa
            where est.estabelecimento = :estabelecimento
            and est.tenant = :tenant;";
    $stmt = $this->getConnection()->executeQuery($sql, [
        "estabelecimento" => $estabelecimento,
        "tenant" => $tenant
    ]);
    return $stmt->fetch();
}

}