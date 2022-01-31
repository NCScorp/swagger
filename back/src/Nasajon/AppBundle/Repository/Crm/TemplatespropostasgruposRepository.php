<?php


namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\TemplatespropostasgruposRepository as ParentRepository;

class TemplatespropostasgruposRepository extends ParentRepository
{
 

    public function getPodeExcluirTemplatePropostaGrupo($tenant, $id_templatepropostagrupo)
    {
        $sql_1 = "
        SELECT templatepropostagrupo
		  FROM crm.templatespropostas
		 WHERE templatepropostagrupo = :templatepropostagrupo
           AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("templatepropostagrupo", $id_templatepropostagrupo);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['templatepropostagrupo']) ){
            return false;
        }
        return true;
    } 
    
}