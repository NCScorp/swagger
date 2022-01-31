<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Repository\Crm;

use Nasajon\MDABundle\Repository\Crm\TemplatescomposicoesfuncoesRepository as ParentRepository;


class TemplatescomposicoesfuncoesRepository extends ParentRepository
{
 
    public function getPodeExcluirServicoFuncaoTemplate($tenant, $id_templateServicoFuncao)
    {
        $sql_1 = "
        SELECT templatepropostacomposicaofuncao 
		  FROM crm.propostasitensfuncoes
		 WHERE templatepropostacomposicaofuncao = :templatepropostaservicofuncao
		   AND tenant = :tenant
        ";
        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue("templatepropostaservicofuncao", $id_templateServicoFuncao);
        $stmt_1->bindValue("tenant", $tenant);
        $stmt_1->execute();
        $result = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);
        if(is_array($result) && isset($result[0]) && isset($result[0]['templatepropostacomposicaofuncao']) ){
            return false;
        }
        return true;
    }

    public function getTemplatesFuncoesTemplateProposta($tenant, $grupoempresarial, $templateproposta)
    {
        $sql_1 = "
        SELECT 
        t0_.templatepropostacomposicaofuncao as templatecomposicaofuncao,
        t0_.valor as valor,
        t0_.quantidade as quantidade,
        t0_.templatepropostacapitulocomposicao as templatepropostacomposicao,
        F.funcao as funcao_funcao,
        F.codigo as funcao_codigo,
        F.descricao as funcao_descricao,
        t2_.templatepropostacapitulo,
        t2_.templateproposta
        FROM crm.templatespropostascomposicoesfuncoes t0_ 
        left join gp.funcoes F on t0_.funcao = F.funcao and t0_.tenant = F.tenant
        left join crm.templatespropostascapituloscomposicoes t1_ on t0_.templatepropostacapitulocomposicao = t1_.templatepropostacapitulocomposicao and t0_.tenant = t1_.tenant and t0_.grupoempresarial = t0_.grupoempresarial 
        left join crm.templatespropostascapitulos t2_ on t1_.templatepropostacapitulo = t2_.templatepropostacapitulo and t1_.tenant = t2_.tenant and t1_.grupoempresarial = t2_.grupoempresarial
        WHERE (t0_.tenant = :tenant) AND (t2_.templateproposta = :templateproposta) AND (t0_.grupoempresarial = :grupoempresarial);
        ";

        $stmt_1 = $this->getConnection()->prepare($sql_1);
        $stmt_1->bindValue(":tenant", $tenant);
        $stmt_1->bindValue(":grupoempresarial", $grupoempresarial);
        $stmt_1->bindValue(":templateproposta", $templateproposta); 
        $stmt_1->execute();
        
        $templatesFuncoes = $stmt_1->fetchAll(\PDO::FETCH_ASSOC);

        //colocando dados da funcao dentro de um array, para que no json seja um obj dentro do template,
        //retornando assim os dados da mesma forma como são retornados com a api antiga.
        foreach ($templatesFuncoes as $key => $tf) {
            $templatesFuncoes[$key]['funcao'] = [
                'funcao' => $tf['funcao_funcao'],
                'codigo' => $tf['funcao_codigo'],
                'descricao' => $tf['funcao_descricao'],
            ];
            unset($templatesFuncoes[$key]['funcao_funcao']);
            unset($templatesFuncoes[$key]['funcao_codigo']);
            unset($templatesFuncoes[$key]['funcao_descricao']);
        }

        return $templatesFuncoes;
    }
    
}