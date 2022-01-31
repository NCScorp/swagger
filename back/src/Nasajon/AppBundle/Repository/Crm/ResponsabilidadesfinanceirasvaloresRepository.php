<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\ResponsabilidadesfinanceirasvaloresRepository as ParentRepository;


class ResponsabilidadesfinanceirasvaloresRepository extends ParentRepository
{

    public function getContratosBy($guid, $tipo, $id_grupoempresarial, $tenant)
    {

        $extraJoin = '';
        $filtroAdicional = '';
        $campoAdicional = '';
        $queryGrupoEmpresarial = '';
        switch ($tipo) {
            case 'propostaitem':
                $filtroAdicional = ' and t4.propostaitem = :guid ;';
                break;
            case 'negocio':
                $filtroAdicional = ' and t2.negocio = :guid ;';
                break;
            default:
                throw new \Exception("Tipo não permitido", 1);
                break;
        }
        if($id_grupoempresarial != null){
            $queryGrupoEmpresarial = ' and t1.id_grupoempresarial = :id_grupoempresarial ';
        }

        $sql_1 = "select 
            t1.responsabilidadefinanceiravalor, 
            t1.responsabilidadefinanceira, 
            t1.itemcontrato, 
            t3.cancelado, 
            t1.contrato
        from crm.responsabilidadesfinanceirasvalores t1 
        left join crm.responsabilidadesfinanceiras t2 on (
            t1.responsabilidadefinanceira = t2.responsabilidadefinanceira and 
            t1.tenant = t2.tenant
        ) 
        left join financas.contratos t3 on (t1.contrato = t3.contrato and t1.tenant = t3.tenant)
        left join crm.orcamentos t4 on (t2.orcamento = t4.orcamento and t1.tenant = t4.tenant)
        where t1.tenant = :tenant
        $queryGrupoEmpresarial
        and t1.contrato is not null
        $filtroAdicional
        ";

        $stmt_1 = $this->getConnection()->prepare($sql_1);

        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->bindValue("guid", $guid);
        if($id_grupoempresarial != null){
            $stmt_1->bindValue("id_grupoempresarial", $id_grupoempresarial);
        }

        $stmt_1->execute();

        $responsabilidades = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);

        return $responsabilidades;
    }

}