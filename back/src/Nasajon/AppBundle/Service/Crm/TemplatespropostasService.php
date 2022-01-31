<?php

namespace Nasajon\AppBundle\Service\Crm;

use LogicException;
use Nasajon\MDABundle\Entity\Crm\Templatespropostascapitulos;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Crm\TemplatespropostasService as ParentService;

/**
 * Sobrescrita para tratar quando o construtor via lookup envia objeto
 * Método insert sobreescrito para adicionar um agrupador "Serviços" automaticamente ao criar um novo templatepropostas/apolice.
 */
class TemplatespropostasService extends ParentService
{

    protected $crmTmpltsPrpstsCptlsService;
    protected $crmTemplatesPropostasFamilias;
    protected $crmTemplatesPropostasFuncoes;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\TemplatespropostasRepository $repository,
        $crmTmpltsprpstsdcmntsSrvc,
        $crmTmpltsPrpstsCptlsService,
        $crmTemplatesPropostasFamilias,
        $crmTemplatesPropostasFuncoes
    ) {
        parent::__construct($repository, $crmTmpltsprpstsdcmntsSrvc);
        $this->crmTmpltsPrpstsCptlsService = $crmTmpltsPrpstsCptlsService;
        $this->crmTemplatesPropostasFamilias = $crmTemplatesPropostasFamilias;
        $this->crmTemplatesPropostasFuncoes = $crmTemplatesPropostasFuncoes;
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param string $templatepropostagrupo
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $id_grupoempresarial, $templatepropostagrupo)
    {
        //Pegando atributo do objeto
        if (is_object($templatepropostagrupo) && !empty($templatepropostagrupo->getTemplatepropostagrupo())) {
            $templatepropostagrupo = $templatepropostagrupo->getTemplatepropostagrupo();
        }
        //----
        $data = parent::find($id, $tenant, $id_grupoempresarial, $templatepropostagrupo);
        return $data;
    }

    /**
     * @return array
     */
    public function findAll($tenant, $id_grupoempresarial, $templatepropostagrupo, Filter $filter = null)
    {

        $dados = parent::findAll($tenant, $id_grupoempresarial, $templatepropostagrupo, $filter);

        return $dados;
    }

    /**
     * @param string  $templatepropostagrupo
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatespropostas $entity
     * @return string
     * @throws \Exception
     */
    public function insert($templatepropostagrupo, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatespropostas $entity)
    {
        try {
            $this->getRepository()->begin();
            //insere a apolice desejada
            $response = Parent::insert($templatepropostagrupo, $logged_user, $tenant, $id_grupoempresarial, $entity);
            //insere um agrupador padrão
            $capituloPadrao = new Templatespropostascapitulos();
            $capituloPadrao->setNome('Serviços');
            $inserted = $this->crmTmpltsPrpstsCptlsService->insert($response['templateproposta'], $logged_user, $tenant, $id_grupoempresarial, $capituloPadrao);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatespropostas $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatespropostas $entity)
    {
        try {
            $this->getRepository()->begin();

            $podeExcluir = $this->getRepository()->getPodeExcluirTemplateProposta($tenant, $id_grupoempresarial, $entity->getTemplateproposta());
            if (!$podeExcluir) {
                throw new LogicException("Não foi possível excluir a apólice pois existem agrupadores vinculados a ela.", 1);
            }

            $response = $this->getRepository()->delete($tenant, $id_grupoempresarial, $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    public function getTemplatesFamiliasFuncoesTemplateProposta($tenant, $grupoempresarial, $templateproposta)
    {
        $familias = $this->crmTemplatesPropostasFamilias->getTemplatesFamiliasTemplateProposta($tenant, $grupoempresarial, $templateproposta);
        $funcoes = $this->crmTemplatesPropostasFuncoes->getTemplatesFuncoesTemplateProposta($tenant, $grupoempresarial, $templateproposta);
        return ['familias' => $familias, 'funcoes' => $funcoes];
    }
}
