<?php

namespace Nasajon\AppBundle\Repository\Ns;

/**
 * Sobrescrito para deixar de fazer um find depois de inserir e retornar a entidade
 *
 */
class TelefonesRepository extends \Nasajon\MDABundle\Repository\Ns\TelefonesRepository {

  /**
   * Sobrescrito para deixar de fazer um find depois de inserir e retornar a entidade
   * @param string  $contato
   * @param string  $tenant
   * @param \Nasajon\MDABundle\Entity\Ns\Telefones $entity
   * @return string 
   * @throws \Exception
   */
  public function insert($contato, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Telefones $entity) {
    $sql_1 = "SELECT mensagem
            FROM ns.api_telefoneNovo_v2(row(
                            :contato,
                            :id_pessoa,
                            :ddd,
                            :telefone,
                            :chavetel,
                            :descricao,
                            :ramal,
                            :tptelefone,
                            :ddi,
                            :ordemimportancia,
                            :created_by,
                            :tenant,
                            :principal
                        )::ns.ttelefonenovo_v2
            );";

    $stmt_1 = $this->getConnection()->prepare($sql_1);


    $stmt_1->bindValue("contato", $contato);




    $stmt_1->bindValue("id_pessoa", $entity->getIdPessoa());




    $stmt_1->bindValue("ddd", $entity->getDdd());




    $stmt_1->bindValue("telefone", $entity->getTelefone());




    $stmt_1->bindValue("chavetel", $entity->getChavetel());




    $stmt_1->bindValue("descricao", $entity->getDescricao());




    $stmt_1->bindValue("ramal", $entity->getRamal());




    $stmt_1->bindValue("tptelefone", $entity->getTptelefone());




    $stmt_1->bindValue("ddi", $entity->getDdi());




    $stmt_1->bindValue("ordemimportancia", $entity->getOrdemimportancia());



    $stmt_1->bindValue("created_by", json_encode($logged_user));



    $stmt_1->bindValue("tenant", $tenant);

    $stmt_1->bindValue("principal", $entity->getPrincipal(), \PDO::PARAM_BOOL);


    $stmt_1->execute();
    $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entity);

    $entity->setTelefone($resposta);

    return $entity; //sobrescrito
  }

}
