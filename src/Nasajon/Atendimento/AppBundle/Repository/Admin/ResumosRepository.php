<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Repository\Atendimento\Admin\ResumosRepository as ParentRepository;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class ResumosRepository extends ParentRepository {
    
   public function somaResumos($tenant, $entidade){
       
       $this->getConnection()->beginTransaction();

       try{
       $sql = '
           select mensagem from atendimento.api_somaResumos(row(
            :tenant, 
            :entidade
           )::atendimento.tsomaresumos);
        ';
       $stmt_1 = $this->getConnection()->prepare($sql);
       $stmt_1->bindParam('tenant', $tenant);
       $stmt_1->bindParam('entidade', $entidade);
       $stmt_1->execute();
       $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entidade);
       $retorno = $resposta;
       $this->getConnection()->commit();
       } catch (\Exception $e) {
        $this->getConnection()->rollBack();
        throw $e;
      }
      return $retorno;
     }

    
}
