<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\MDABundle\Entity\Ns\Prospects;
use Nasajon\MDABundle\Entity\Crm\Listadavezvendedoresitens;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use LogicException;
use Nasajon\MDABundle\Service\Crm\NegociosService as ParentService;

class NegociosService extends ParentService
{

    protected $nsProspectsService;
    /**
     * Service de Listadavezconfiguracoes
     */
    protected $crmLstVzCnfService;
    /**
     * Service de Listadavezvendedoresitens
     */
    protected $crmLstVzVndItnService;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\NegociosRepository $repository, 
        $crmNgcscnttsSrvc, 
        $nsProspectsService,
        $crmLstVzCnfService,
        $crmLstVzVndItnService
    ){
        parent::__construct(
            $repository, 
            $crmNgcscnttsSrvc
        );
        
        $this->nsProspectsService = $nsProspectsService;
        $this->crmLstVzCnfService = $crmLstVzCnfService;
        $this->crmLstVzVndItnService = $crmLstVzVndItnService;
    }

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Negocios $entity
     * @return string
     * @throws \Exception
     */
    public function preNegocioQualificar($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Negocios $entity)
    {
        try {
            $this->getRepository()->begin();

            if($entity->getCliente() == null){
                $novoProspect = new Prospects();
                // Pego o cnpj/cpf sem a máscara, para salvar como código do prospect
                $documentoSemMascara = str_replace(['.', '-', '/'], "", $entity->getClienteDocumento());

                $novoProspect->setCodigo($documentoSemMascara);
                $novoProspect->setCnpj($entity->getClienteDocumento());
                $novoProspect->setRazaosocial($entity->getClienteCompanhia());
                $novoProspect->setNomefantasia($entity->getClienteNomefantasia());
                $prospect = $this->nsProspectsService->insert($id_grupoempresarial,$logged_user,$tenant, $novoProspect);
                $cliente = new Clientes();
                $cliente->setCliente($prospect['prospect']);
                $entity->setCliente($cliente);
            }

            $response = $this->getRepository()->preNegocioQualificar($tenant, $id_grupoempresarial, $logged_user,  $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * Busca o próximo vendedor da lista da vez de acordo com as regras configuradas
     */
    public function buscarVendedorDaListaDaVez($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Negocios $entity){
        // Faço a validação do negócio de acordo com as configurações da lista da vez
        $validacaoNegocio = $this->crmLstVzCnfService->validarNegocioNaArvoreDeRegras($tenant, $id_grupoempresarial, $entity);
        
        // Se não possui configuração, lanço exceção
        if (!$validacaoNegocio->getPossuiconfiguracao()) {
            throw new LogicException("Não existem regras da lista da vez configuradas!", 1);
            return ;
        }

        // Se nenhuma validação passou, lanço exceção
        if (!$validacaoNegocio->getValido()) {
            throw new LogicException("O negócio não foi validado pelas regras da lista da vez!", 1);
            return ;
        }

        // Se for vendedor fixo, pegar o vendedor do cliente
        if ($validacaoNegocio->getVendedorfixo()) {
            // Retorno um array associativo, no modelo de Listadavezvendedoresitens
            return [
                'listadavezvendedoritem' => null,
                'listadavezvendedor' => null,
                'vendedor' => [
                    'vendedor_id' => $entity->getCliente()->getVendedor()
                ]
            ];
        } 
        // Senão: Pegar vendedor da lista de vendedores
        else {
            // Busco o próximo vendedor da lista, filtrando pela posição 0
            $filter = new Filter();
            $filter->setFilterExpression([
                new FilterExpression('posicao', 'eq', '1')
            ]);

            $arrVendedoresitens = $this->crmLstVzVndItnService->findAll(
                $tenant, 
                $id_grupoempresarial, 
                $validacaoNegocio->getListadavezvendedor()->getListadavezvendedor(), 
                $filter
            );

            if (count($arrVendedoresitens) == 0) {
                throw new LogicException("A lista de vendedores não possui nenhum vendedor!", 1);
                return ;
            } else {
                return $arrVendedoresitens[0];
            }
        }
    }
}
