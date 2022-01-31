<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\TemplatespropostascapituloscomposicoesService as ParentService;
use Nasajon\MDABundle\Entity\Crm\Templatescomposicoesfamilias as TemplatescomposicoesfamiliasEntity;
use Nasajon\MDABundle\Entity\Crm\Templatescomposicoesfuncoes as TemplatescomposicoesfuncoesEntity;
use LogicException;

/**
 * Sobrescrito para ao salvar um template composição já copiar as funções e familias de produtos da composição
 */
class TemplatespropostascapituloscomposicoesService extends ParentService
{

    private $templatescomposicoesfamiliasService;
    private $templatescomposicoesfuncoesService;
    private $composicoesfamiliasService;
    private $composicoesfuncoesService;

    public function __construct(\Nasajon\MDABundle\Repository\Crm\TemplatespropostascapituloscomposicoesRepository $repository,  $templatescomposicoesfamiliasService,     $templatescomposicoesfuncoesService,     $composicoesfamiliasService,     $composicoesfuncoesService)
    {
        $this->repository = $repository;
        $this->templatescomposicoesfamiliasService = $templatescomposicoesfamiliasService;
        $this->templatescomposicoesfuncoesService = $templatescomposicoesfuncoesService;
        $this->composicoesfamiliasService = $composicoesfamiliasService;
        $this->composicoesfuncoesService = $composicoesfuncoesService;
    }

    /**
     * @param string  $templatepropostacapitulo
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatespropostascapituloscomposicoes $entity
     * @return string
     * @throws \Exception
     */
    public function insert($templatepropostacapitulo, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatespropostascapituloscomposicoes $entity)
    {
        try {
            $this->getRepository()->begin();

            if ($entity->getNome() == null) {
                $entity->setNome($entity->getComposicao()->getNome());
            }
            if ($entity->getDescricao() == null) {
                $entity->setDescricao($entity->getComposicao()->getDescricao());
            }

            $response = $this->getRepository()->insert($templatepropostacapitulo, $logged_user, $tenant, $id_grupoempresarial, $entity);

            $templatecomposicao = $this->fillEntity($response);

            $familias = $this->composicoesfamiliasService->findAll($tenant, $response['composicao']['composicao']);

            foreach ($familias as $familia) {
                $familiaObj = $this->composicoesfamiliasService->fillEntity($familia)->getFamilia();
                $familiaTemplate = new TemplatescomposicoesfamiliasEntity();
                $familiaTemplate->setTemplatepropostacomposicao($response['templatepropostacomposicao']);
                $familiaTemplate->setFamilia($familiaObj);
                $familiaTemplate->setQuantidade($familia['quantidade']);
                $this->templatescomposicoesfamiliasService->insert($templatecomposicao->getTemplatepropostacomposicao(), $logged_user, $tenant, $id_grupoempresarial, $familiaTemplate);
            }

            $funcoes = $this->composicoesfuncoesService->findAll($tenant, $response['composicao']['composicao']);

            foreach ($funcoes as $funcao) {
                $funcaoObj = $this->composicoesfuncoesService->fillEntity($funcao)->getFuncao();
                $funcaoTemplate = new TemplatescomposicoesfuncoesEntity();
                $funcaoTemplate->setTemplatepropostacomposicao($response['templatepropostacomposicao']);
                $funcaoTemplate->setFuncao($funcaoObj);
                $funcaoTemplate->setQuantidade($funcao['quantidade']);
                $this->templatescomposicoesfuncoesService->insert($templatecomposicao->getTemplatepropostacomposicao(), $logged_user, $tenant, $id_grupoempresarial, $funcaoTemplate);
            }

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Crm\Templatespropostascapituloscomposicoes $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Templatespropostascapituloscomposicoes $entity)
    {
        try {
            $this->getRepository()->begin();

            $podeExcluir = $this->getRepository()->getPodeExcluirServicoTemplate($tenant, $id_grupoempresarial,  $entity->getTemplatepropostacomposicao());
            if (!$podeExcluir) {
                throw new LogicException("Não foi possível excluir o serviço pois existem itens de pedidos vinculados a ele.", 1);
            }

            $response = $this->getRepository()->delete($tenant, $id_grupoempresarial, $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }
}
