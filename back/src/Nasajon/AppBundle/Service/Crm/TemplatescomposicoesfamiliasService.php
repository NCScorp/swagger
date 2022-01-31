<?php


namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Service\Crm\TemplatescomposicoesfamiliasService as ParentService;
use LogicException;

class TemplatescomposicoesfamiliasService extends ParentService
{

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatescomposicoesfamilias $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatescomposicoesfamilias $entity)
    {
        try {
            $this->getRepository()->begin();

            $podeExcluir = $this->getRepository()->getPodeExcluirServicoFamiliaTemplate($tenant, $id_grupoempresarial, $entity->getTemplatecomposicaofamilia());
            if (!$podeExcluir) {
                throw new LogicException("Não foi possível excluir a família de produto pois existem itens de pedidos vinculados a ele.", 1);
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
     * Encontra todos os templates familias de templates composicoes de um template proposta
     */
    public function getTemplatesFamiliasTemplateProposta($tenant, $grupoempresarial, $templateproposta)
    {
        $templatesfuncoes = $this->getRepository()->getTemplatesFamiliasTemplateProposta($tenant, $grupoempresarial, $templateproposta);
        return $templatesfuncoes;
    }
}
