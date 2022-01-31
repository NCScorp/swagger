<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Service\Crm\TemplatescomposicoesfuncoesService as ParentService;
use LogicException;


class TemplatescomposicoesfuncoesService extends ParentService
{

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatescomposicoesfuncoes $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatescomposicoesfuncoes $entity)
    {
        try {
            $this->getRepository()->begin();

            $podeExcluir = $this->getRepository()->getPodeExcluirServicoFuncaoTemplate($tenant, $id_grupoempresarial, $entity->getTemplatecomposicaofuncao());
            if (!$podeExcluir) {
                throw new LogicException("Não foi possível excluir profissional pois existem itens de pedidos vinculados a ele.", 1);
            }

            $response = $this->getRepository()->delete($tenant,  $id_grupoempresarial, $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * Encontra todos os templates funcoes de templates composicoes de um template proposta
     */
    public function getTemplatesFuncoesTemplateProposta($tenant, $grupoempresarial, $templateproposta)
    {
        $templatesfuncoes = $this->getRepository()->getTemplatesFuncoesTemplateProposta($tenant, $grupoempresarial, $templateproposta);
        return $templatesfuncoes;
    }
}
