<?php

namespace AppBundle\Repository\Web;

use Nasajon\MDABundle\Entity\Web\Configuracoes;
use Nasajon\MDABundle\Repository\Web\ConfiguracoesRepository as ParentRepository;

class ConfiguracoesRepository extends ParentRepository
{
    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Configuracoes $entity
     * @return string 
     * @throws \Exception
     */
    public function insert($tenant, Configuracoes $entity)
    {
        $sql_1 = "SELECT * FROM web.inserir_configuracao(
            :_tenant,
            :_sistema,
            :_chave,
            :_valor,
            :_id_grupoempresarial
        );";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("_tenant", $tenant);
        $stmt_1->bindValue("_sistema", $entity->getSistema());
        $stmt_1->bindValue("_chave", $entity->getChave());
        $stmt_1->bindValue("_valor", $entity->getValor());
        $stmt_1->bindValue("_id_grupoempresarial", $entity->getIdGrupoempresarial());

        $stmt_1->execute();
        $resposta = $stmt_1->fetchAll();

        return $resposta;
    }
}