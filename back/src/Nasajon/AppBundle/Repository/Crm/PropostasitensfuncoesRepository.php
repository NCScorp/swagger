<?php

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\PropostasitensfuncoesRepository as ParentRepository;

class PropostasitensfuncoesRepository extends ParentRepository
{

    public function getFuncoesProposta($tenant, $grupoempresarial, $proposta)
    {
        $sql_1 = "
        SELECT 
        t0_.propostaitemfuncao as propostaitemfuncao, 
        t0_.quantidade as quantidade, 
        t0_.propostaitem as propostaitem, 
        t0_.nome as nome, 
        t0_.nomefuncaoalterado as nomefuncaoalterado, 
        t1_.funcao as funcao_funcao, 
        t1_.codigo as funcao_codigo, 
        t1_.descricao as funcao_descricao, 
        t2_.itemcontrato as itemcontrato_itemcontrato, 
        t2_.contrato as itemcontrato_contrato, 
        t3_.itemcontrato as itemcontratoAPagar_itemcontrato, 
        t3_.contrato as itemcontratoAPagar_contrato, 
        t4_.templateproposta as templateproposta_templateproposta, 
        t4_.nome as templateproposta_nome, 
        t4_.templatepropostagrupo as templateproposta_templatepropostagrupo, 
        t4_.valorapolice as templateproposta_valorapolice, 
        t5_.templatepropostacomposicaofuncao as templatepropostacomposicaofuncao_templatecomposicaofuncao, 
        t5_.templatepropostacapitulocomposicao as templatepropostacomposicaofuncao_templatepropostacomposicao, 
        t5_.quantidade as templatepropostacomposicaofuncao_quantidade, 
        t5_.valor as templatepropostacomposicaofuncao_valor 
        FROM crm.propostasitensfuncoes t0_ 
        JOIN crm.propostasitens PI on PI.propostaitem = t0_.propostaitem and PI.tenant = t0_.tenant and t0_.grupoempresarial = PI.id_grupoempresarial 
        LEFT JOIN gp.funcoes t1_ ON t0_.funcao = t1_.funcao and t0_.tenant = t1_.tenant 
        LEFT JOIN financas.vw_itenscontratos t2_ ON t0_.itemcontrato = t2_.itemcontrato and t0_.tenant = t2_.tenant and t0_.grupoempresarial = t2_.id_grupoempresarial 
        LEFT JOIN financas.vw_itenscontratos t3_ ON t0_.itemcontratoAPagar = t3_.itemcontrato and t0_.tenant = t3_.tenant and t0_.grupoempresarial = t3_.id_grupoempresarial 
        LEFT JOIN crm.templatespropostas t4_ ON t0_.templateproposta = t4_.templateproposta and t0_.tenant = t4_.tenant and t0_.grupoempresarial = t4_.grupoempresarial 
        LEFT JOIN crm.templatespropostascomposicoesfuncoes t5_ ON t0_.templatepropostacomposicaofuncao = t5_.templatepropostacomposicaofuncao and t0_.tenant = t5_.tenant and t0_.grupoempresarial = t5_.grupoempresarial 
        WHERE (t0_.tenant = :tenant) AND (pi.proposta = :proposta) AND (t0_.grupoempresarial = :grupoempresarial);
        ";

        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue(":tenant", $tenant);
        $stmt_1->bindValue(":grupoempresarial", $grupoempresarial);
        $stmt_1->bindValue(":proposta", $proposta); 
        $stmt_1->execute();
        
        $contratos = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);

        return $contratos;
    }
}