<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use LogicException;
use Nasajon\MDABundle\Service\Crm\PropostasitensfuncoesService as ParentService;

/**
 * PropostasitensfuncoesService
 * Sobrescrito metodo insert para impedir a criação de um propostaitemfuncao caso orçamentos do propostaitem não estejam abertos.
 * Sobrescrito metodo delete para deletar o orçamento relacionado com o propostaitemfuncao caso orçamentos do propostaitem não estejam abertos. Caso contrário, não exclui o propostaitemfuncao.
 */
class PropostasitensfuncoesService extends ParentService
{
    /**
     * @var \Nasajon\MDABundle\Repository\Crm\PropostasitensfuncoesRepository
     */
    protected $repository;

    protected $crmOrcamentosService;
    protected $responsabilidadesfinanceirasvaloresRepository;

    public function __construct(\Nasajon\MDABundle\Repository\Crm\PropostasitensfuncoesRepository $repository, $crmOrcamentosService, $responsabilidadesfinanceirasvaloresRepository)
    {
        $this->repository = $repository;
        $this->crmOrcamentosService = $crmOrcamentosService;
        $this->responsabilidadesfinanceirasvaloresRepository = $responsabilidadesfinanceirasvaloresRepository;
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $propostaitem
     * @param mixed $id_grupoempresarial     
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $propostaitem, $id_grupoempresarial)
    {
        if (is_object($propostaitem)) {
            $propostaitem = $propostaitem->getPropostaitem();
        }

        $data = $this->getRepository()->find($id, $tenant, $propostaitem, $id_grupoempresarial);
        return $data;
    }

    /**
     * Dado um propostaitem, checa se todos os orçamentos realcionados a este propostaitem estão com status aberto, retornando true.
     */
    public function orcamentosAbertos($tenant, $id_grupoempresarial, $propostaitem)
    {
        $filter = new Filter();
        $arrFilterExpression[] = new FilterExpression('propostaitem', 'eq', $propostaitem);
        $filter->setFilterExpression($arrFilterExpression);
        $orcamentos = $this->crmOrcamentosService->findAll($tenant, $id_grupoempresarial, $filter);
        foreach ($orcamentos as $key => $orcamento) {
            if ($orcamento['status'] != 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verifica se há orçamentos associados a proposta item pai da proposta item função. Se houver, significa que o fornecedor está acionado
     *
     * @param string $propostaItemId Identificador da proposta item
     * @param string $tenant Identificador tenant
     * @param string $id_grupoempresarial Identificador do grupo empresarial
     * @return void
     */
    public function verificaFornecedorAcionado($propostaItemId, $tenant, $id_grupoempresarial){

        // Filtro usado para retornar somente os orçamentos que pertençam a proposta item recebida por parametro
        $filter = new Filter();
        $expressions = [
            new FilterExpression('propostaitem', 'eq', $propostaItemId),
        ];
        $filter->setFilterExpression($expressions);

        $orcamentos = $this->crmOrcamentosService->findAll($tenant, $id_grupoempresarial, $filter);

        //Se há associação, retorno true. Do contrário, retorno false
        return (count($orcamentos) > 0) ? true : false;

    }

    public function findAll($tenant, $propostaitem, $id_grupoempresarial, Filter $filter = null){
        $funcoes = parent::findAll($tenant, $propostaitem, $id_grupoempresarial, $filter);
        return $funcoes;
    }

    /**
     * @param string  $propostaitem
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity
     * @return string
     * @throws \Exception
     */
    public function insert($propostaitem, $logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity)
    {

        try{

            //não permite inserir se orcamento status != 0
            if (!$this->orcamentosAbertos($tenant, $id_grupoempresarial, $propostaitem)) {
                throw new \LogicException("Não é possível atribuir um prestador com orçamento em negociação/finalizado.", 1);
            }

            $fornecedorAcionado = $this->verificaFornecedorAcionado($propostaitem, $tenant, $id_grupoempresarial);

            if($fornecedorAcionado){
                throw new LogicException('A ação não pode ser feita, pois o fornecedor já está acionado');
            }

            return parent::insert($propostaitem, $logged_user, $tenant, $id_grupoempresarial, $entity);

        } catch(\Exception $e){
            throw new \LogicException("Não foi possível adicionar o profissional. " . $e->getMessage(), 1);
        }

    }

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, $id_grupoempresarial, $logged_user, $propostaitem, \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity)
    {
        try {
            $this->getRepository()->begin();

            if($this->possuiContrato($entity)){
                throw new \LogicException("Não é possível excluir um serviço vinculado a um contrato.", 1);
            }

            $fornecedorAcionado = $this->verificaFornecedorAcionado($entity->getPropostaitem(), $tenant, $id_grupoempresarial);

            if($fornecedorAcionado){
                throw new LogicException('A ação não pode ser feita, pois o fornecedor já está acionado');
            }

            $response = $this->getRepository()->delete($tenant, $id_grupoempresarial, $logged_user, $propostaitem, $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw new \LogicException("Não foi possível excluir o profissional. " . $e->getMessage(), 1);
        }
    }

    /**
     * Verifica se o propostaitemfuncao possuem contrato.
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity
     * @return boolean
     */
    public function possuiContrato($entity)
    {
        $contratos = $this->responsabilidadesfinanceirasvaloresRepository
                ->getContratosBy($entity->getPropostaitem(), 'propostaitem', null, $entity->getTenant());

        foreach ($contratos as $key => $contrato) {
            if($contrato['contrato'] != null){
                return true;
            }
        }
        return false;
    }



    /**
     * @param string  $propostaitem
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity
     * @return string
     * @throws \Exception
     */
    public function update($propostaitem, $tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Propostasitensfuncoes $entity)
    {

        try{
            if($this->possuiContrato($entity)){
                throw new \LogicException("Não é possível editar um produto vinculado a um contrato.", 1);
            }

            $fornecedorAcionado = $this->verificaFornecedorAcionado($propostaitem, $tenant, $id_grupoempresarial);

            if($fornecedorAcionado){
                throw new LogicException('A ação não pode ser feita, pois o fornecedor já está acionado');
            }

            parent::update($propostaitem, $tenant, $id_grupoempresarial, $logged_user, $entity);

        } catch(\Exception $e){
            throw new \LogicException("Não foi possível editar o profissional. " . $e->getMessage(), 1);
        }

    }

    /**
     * Encontra todas as funcoes de propostasitens de uma proposta
     */
    public function getFuncoesProposta($tenant, $grupoempresarial, $proposta)
    {
        $funcoes = $this->getRepository()->getFuncoesProposta($tenant, $grupoempresarial, $proposta);
        return $funcoes;
    }
}
