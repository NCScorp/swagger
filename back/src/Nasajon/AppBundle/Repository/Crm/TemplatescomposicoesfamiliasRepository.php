<?php


namespace Nasajon\AppBundle\Repository\Crm;


use Nasajon\MDABundle\Repository\Crm\TemplatescomposicoesfamiliasRepository as ParentRepository;

/**
* TemplatescomposicoesfamiliasRepository
*
*/
class TemplatescomposicoesfamiliasRepository extends ParentRepository
{
 

    public function getPodeExcluirServicoFamiliaTemplate($tenant, $id_templateServicoFamilia)
    {
        $sql_1 = "
        SELECT templatepropostacomposicaofamilia 
		  FROM crm.propostasitensfamilias
		 WHERE templatepropostacomposicaofamilia = :templatepropostaservicofamilia
		   AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("templatepropostaservicofamilia", $id_templateServicoFamilia);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['templatepropostacomposicaofamilia']) ){
            return false;
        }
        return true;
    }


    public function getTemplatesFamiliasTemplateProposta($tenant, $grupoempresarial, $templateproposta)
    {
        $sql_1 = "
        SELECT 
        t0_.templatepropostacomposicaofamilia as templatecomposicaofamilia,
        t0_.valor as valor,
        t0_.quantidade as quantidade,
        t0_.templatepropostacapitulocomposicao as templatepropostacomposicao,
        F.familia as familia_familia,
        F.codigo as familia_codigo,
        F.descricao as familia_descricao,
        F.valor as familia_valor,
        t2_.templatepropostacapitulo,
        t2_.templateproposta
        FROM crm.templatespropostascomposicoesfamilias t0_ 
        left join estoque.familias F on t0_.familia = F.familia and t0_.tenant = F.tenant
        left join crm.templatespropostascapituloscomposicoes t1_ on t0_.templatepropostacapitulocomposicao = t1_.templatepropostacapitulocomposicao and t0_.tenant = t1_.tenant and t0_.grupoempresarial = t0_.grupoempresarial 
        left join crm.templatespropostascapitulos t2_ on t1_.templatepropostacapitulo = t2_.templatepropostacapitulo and t1_.tenant = t2_.tenant and t1_.grupoempresarial = t2_.grupoempresarial 
        WHERE (t0_.tenant = :tenant) AND (t2_.templateproposta = :templateproposta) AND (t0_.grupoempresarial = :grupoempresarial);
        ";

        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue(":tenant", $tenant);
        $stmt_1->bindValue(":grupoempresarial", $grupoempresarial);
        $stmt_1->bindValue(":templateproposta", $templateproposta); 
        $stmt_1->execute();
        
        $templatesFamilias = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        
        //colocando dados da familia dentro de um array, para que no json seja um obj dentro do template,
        //retornando assim os dados da mesma forma como são retornados com a api antiga.
        foreach ($templatesFamilias as $key => $tf) {
            $templatesFamilias[$key]['familia'] = [
                'familia' => $tf['familia_familia'],
                'codigo' => $tf['familia_codigo'],
                'descricao' => $tf['familia_descricao'],
                'valor' => $tf['familia_valor'],
            ];
            unset($templatesFamilias[$key]['familia_familia']);
            unset($templatesFamilias[$key]['familia_codigo']);
            unset($templatesFamilias[$key]['familia_descricao']);
            unset($templatesFamilias[$key]['familia_valor']);
        }
        return $templatesFamilias;
    }
}