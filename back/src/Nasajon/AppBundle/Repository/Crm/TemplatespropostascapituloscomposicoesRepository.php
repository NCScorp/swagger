<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\TemplatespropostascapituloscomposicoesRepository as ParentRepository;


class TemplatespropostascapituloscomposicoesRepository extends ParentRepository
{
 
    public function getPodeExcluirServicoTemplate($tenant, $id_templateServico)
    {
        $sql_1 = "
        SELECT templatepropostacomposicao 
		  FROM crm.propostasitens
		 WHERE templatepropostacomposicao = :templatepropostacapitulocomposicao 
		   AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("templatepropostacapitulocomposicao", $id_templateServico);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['templatepropostacomposicao']) ){
            return false;
        }
        return true;
    }
    
    
}