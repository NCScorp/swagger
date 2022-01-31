<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\TemplatespropostascapitulosRepository as ParentRepository;

class TemplatespropostascapitulosRepository extends ParentRepository
{

    public function getPodeExcluirCapituloTemplate($tenant, $id_capitulo)
    {
        $sql_1 = "
        SELECT templatepropostacapitulo
		  FROM crm.templatespropostascapituloscomposicoes
		 WHERE templatepropostacapitulo = :templatepropostacapitulo
		   AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("templatepropostacapitulo", $id_capitulo);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['templatepropostacapitulo']) ){
            return false;
        }
        return true;
    }   
 

       
}