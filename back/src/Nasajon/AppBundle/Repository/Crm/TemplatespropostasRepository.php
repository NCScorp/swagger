<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\TemplatespropostasRepository as ParentRepository;


class TemplatespropostasRepository extends ParentRepository
{
 

    public function getPodeExcluirTemplateProposta($tenant, $id_grupoempresarial, $id_templateproposta)
    {
        $sql_1 = "
        SELECT templateproposta
		  FROM crm.templatespropostascapitulos
		 WHERE templateproposta = :templateproposta
		   AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("templateproposta", $id_templateproposta);
        $stmt_1->bindValue("tenant", $tenant);
        // $stmt_1->bindValue("grupoempresarial", $id_grupoempresarial);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['templateproposta']) ){
            return false;
        }
        return true;
    } 
    
}