<?php


namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Service\Crm\TemplatespropostascapitulosService as ParentService;
use LogicException;


class TemplatespropostascapitulosService extends ParentService
{

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatespropostascapitulos $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatespropostascapitulos $entity)
    {
        try {
            $this->getRepository()->begin();

            $podeExcluir = $this->getRepository()->getPodeExcluirCapituloTemplate($tenant, $id_grupoempresarial, $entity->getTemplatepropostacapitulo());
            if (!$podeExcluir) {
                throw new LogicException("Não foi possível excluir o agrupador pois existem serviços vinculados a ele.", 1);
            }

            $response = $this->getRepository()->delete($tenant,  $id_grupoempresarial, $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }
}
