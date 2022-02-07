<?php

namespace AppBundle\Repository\Persona;

use Nasajon\MDABundle\Repository\Persona\TrabalhadoresRepository as ParentRepository;

/**
 * Sobrescrito por causa do find de trabalhador usado no fixedAttribute. Com a consulta sobrescrita, apenas o básico é carregado, sem a necessidade de realizar joins
 */
class TrabalhadoresRepository extends ParentRepository
{

  public function __construct(\Doctrine\DBAL\Connection $connection)
  {
      parent::__construct($connection);

      $this->setLinks([
          [
              'field' => 'agencia',
              'entity' => 'Nasajon\MDABundle\Entity\Financas\Agencias',
              'alias' => 't1_',
              'identifier' => 'agencia',
              'type' => 2
          ],
          [
              'field' => 'tipologradouro',
              'entity' => 'Nasajon\MDABundle\Entity\Ns\Tiposlogradouros',
              'alias' => 't2_',
              'identifier' => 'tipologradouro',
              'type' => 2
          ]
      ]);

  }


  private function findQuery(string $where, array $whereFields)
  {
      $sql = "SELECT
            t0_.trabalhador as \"trabalhador\" ,
            t0_.estabelecimento as \"estabelecimento\" ,
            t0_.numerocontasalario as \"numerocontasalario\" ,
            t0_.numerocontasalariodv as \"numerocontasalariodv\" ,
            t0_.tenant as \"tenant\" ,
            t0_.salarioliquidoestimado as \"salarioliquidoestimado\" ,
            t0_.municipioresidencia as \"municipioresidencia\" ,
            t0_.paisresidencia as \"paisresidencia\" ,
            t0_.logradouro as \"logradouro\" ,
            t0_.numero as \"numero\" ,
            t0_.complemento as \"complemento\" ,
            t0_.bairro as \"bairro\" ,
            t0_.cidade as \"cidade\" ,
            t0_.cep as \"cep\" ,
            t0_.dddtel as \"dddtel\" ,
            t0_.telefone as \"telefone\" ,
            t0_.dddcel as \"dddcel\" ,
            t0_.celular as \"celular\" ,
            t0_.email as \"email\" ,
            t0_.sindicato as \"sindicato\" ,
            t1_.agencia as \"t1_agencia\" ,
            t1_.banco as \"t1_banco\" ,
            t1_.codigo as \"t1_codigo\" ,
            t1_.nome as \"t1_nome\" ,
            t1_.agencianumero as \"t1_numero\" ,
            t2_.tipologradouro as \"t2_tipologradouro\" ,
            t2_.descricao as \"t2_descricao\",
            t3_.lotacao as \"t3_lotacao\" ,
            t3_.codigo as \"t3_codigo\" ,
            t3_.nome as \"t3_nome\" ,
            t3_.tipo as \"t3_tipo\" ,
            t3_.tomador as \"t3_tomador\"
          FROM persona.trabalhadores t0_
          LEFT JOIN financas.agencias t1_ ON t0_.agencia = t1_.agencia   and t0_.tenant = t1_.tenant 
          LEFT JOIN ns.tiposlogradouros t2_ ON t0_.tipologradouro = t2_.tipologradouro 
          LEFT JOIN persona.lotacoes t3_ ON t0_.lotacao = t3_.lotacao AND t0_.tenant = t3_.tenant

      {$where}";
      return $this->getConnection()->executeQuery($sql, $whereFields);
  }

  /**
   * @param string $id
   * @param mixed $tenant
        
   * @return array
   * @throw \Doctrine\ORM\NoResultException
   */
  public function find($id, $tenant)
  {
      $where = $this->buildWhere();
      $data = $this->findQuery($where, [
          'id' => $id,
          'tenant' => $tenant
      ])->fetch();
      $data = $this->adjustQueryData($data);
      return $data;
  }

  /**
   * Esse método retorna os trabalhadores que possuem o email, tenant e estabelecimento informados
   * @param integer $tenant id ex: 47
   * @param uuid $estabelecimento
   * @param string $contanasajon email do usuário ex: fulano@nasajon.com.br
   */
public function getTrabalhador($tenant, $estabelecimento, $contanasajon, $trabalhador)
{
    $sql = " SELECT tr.trabalhador, tr.datarescisao
               FROM persona.trabalhadores  tr
               WHERE tr.tenant = :tenant AND tr.estabelecimento = :estabelecimento AND tr.identificacaonasajon = :contanasajon AND tr.trabalhador = :trabalhador";

    $data = $this->getConnection()->executeQuery($sql, [
      'tenant' => $tenant,
      'estabelecimento' => $estabelecimento,
      'contanasajon' => $contanasajon,
      'trabalhador' => $trabalhador
    ])->fetchAll();

    return $data;
  }

  /**
   * Esse método retorna informações gerais do trabalhador necessárias para o
   * profileController monta-lo para alimentar o select de trabalhador no frontend
   * @param uuid $trabalhador
   */
  public function getInfoTrabalhador($trabalhador, $tenant) {
    $sql = " SELECT tr.trabalhador, tr.tipo, tr.subtipo, c.nome as cargo_nome, nc.codigo as nivelcargo_nome, tr.dataadmissao, tr.datarescisao
            FROM persona.trabalhadores  tr
            LEFT JOIN persona.niveiscargos nc ON nc.nivelcargo = tr.nivelcargo AND nc.tenant = :tenant
            LEFT JOIN persona.cargos c ON c.cargo = nc.cargo AND c.tenant = :tenant
            WHERE tr.trabalhador = :trabalhador AND
                  tr.tenant = :tenant
            ORDER BY datarescisao ASC";

    $data = $this->getConnection()->executeQuery($sql, [
      'trabalhador' => $trabalhador,
      'tenant' => $tenant
    ])->fetchAll();

    return $data;
  }

  public function getNomeByIdentificacaoNasajon($tenant, $identificacaonasajon)
  {
      $sql = 'SELECT trab.trabalhador as trabalhador, trab.nome
              FROM
                  persona.trabalhadores trab
              WHERE trab.identificacaonasajon = :identificacaonasajon
                  AND trab.tenant = :tenant limit 1';
      $whereValues = [
          "identificacaonasajon" => $identificacaonasajon,
          "tenant" => $tenant
      ];
      $stmt = $this->getConnection()->prepare($sql);
      $stmt->execute($whereValues);
      $response = $stmt->fetch();
      return $response;
  }

  /**
   * Esse método retorna um resumo de trabalhador
   * @param integer $tenant id ex: 47
   * @param uuid $trabalhador
   */
  public function resumo($tenant, $trabalhador) {
    $sql = "SELECT
                t0_.nome as nome,
                t1_.codigo as nivelcargo,
                t2_.nome as cargo
              FROM persona.trabalhadores t0_ 
              LEFT JOIN persona.niveiscargos t1_ ON t1_.nivelcargo = t0_.nivelcargo AND t1_.tenant = t0_.tenant
              LEFT JOIN persona.cargos t2_ ON t2_.cargo = t1_.cargo AND t2_.tenant = t1_.tenant
              WHERE
                t0_.trabalhador = :trabalhador and
                t0_.tenant = :tenant";

    $data = $this->getConnection()->executeQuery($sql, [
      'tenant' => $tenant,
      'trabalhador' => $trabalhador
    ])->fetchAll();

    return $data;
  }

  /**
   * Esse método retorna o endereço atual de trabalhador
   * @param integer $tenant id ex: 47
   * @param uuid $trabalhador
   */
  public function enderecocontato($tenant, $trabalhador) {
    $sql = "SELECT
                t0_.municipioresidencia as \"municipioresidencia\" ,
                t0_.paisresidencia as \"paisresidencia\" ,
                t0_.logradouro as \"logradouro\" ,
                t0_.numero as \"numero\" ,
                t0_.complemento as \"complemento\" ,
                t0_.bairro as \"bairro\" ,
                t0_.cidade as \"cidade\" ,
                t0_.cep as \"cep\" ,
                t0_.dddtel as \"dddtel\" ,
                t0_.telefone as \"telefone\" ,
                t0_.dddcel as \"dddcel\" ,
                t0_.celular as \"celular\" ,
                t0_.email as \"email\" ,
                t0_.tipologradouro as \"tipologradouro\"
              FROM persona.trabalhadores t0_
              WHERE
                t0_.trabalhador = :trabalhador and
                t0_.tenant = :tenant";

    $data = $this->getConnection()->executeQuery($sql, [
      'tenant' => $tenant,
      'trabalhador' => $trabalhador
    ])->fetch();

    return $data;
  }

  public function buscaFoto($id, $tenant) {
    $sql = "SELECT t0_.foto FROM persona.trabalhadores as t0_
            WHERE t0_.trabalhador = :trabalhador AND t0_.tenant = :tenant";
    $data = $this->getConnection()->executeQuery($sql, [
      'trabalhador' => $id,
      'tenant' => $tenant,
    ])->fetch();
    return $data;
  }


  public function getTrabalhadorByIdentificacaoNasajon($identificacaonasajon)
    {
        $sql = 'SELECT trabalhador.trabalhador as trabalhador, 
                        trabalhador.nome as nome,
                       trabalhador.datarescisao as datarescisao,
                       trabalhador.estabelecimento as estabelecimento,
                       trabalhador.tenant as tenant,
                       ten.codigo as codigotenant
                FROM
                    persona.trabalhadores trabalhador
                inner join 
                  ns.tenants ten on ten.tenant = trabalhador.tenant
                WHERE trabalhador.identificacaonasajon = :identificacaonasajon';
        $whereValues = [
            "identificacaonasajon" => $identificacaonasajon
        ];
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($whereValues);
        $response = $stmt->fetchAll();

        return $response;

    }


}
