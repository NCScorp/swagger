<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace AppBundle\Repository\Meurh;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Meurh\InformesrendimentosRepository as ParentRepository;

/**
* InformesrendimentosRepository
*
*/
class InformesrendimentosRepository extends ParentRepository
{
 
    public function informeVisualizado($informe, $tenant){
        $sql ="update meurh.informesrendimentos set aberto = true where informerendimento = :informerendimento and tenant = :tenant";
        return  $this->getConnection()->executeQuery($sql, ["informerendimento" => $informe, "tenant" => $tenant])->fetch();
    }
}