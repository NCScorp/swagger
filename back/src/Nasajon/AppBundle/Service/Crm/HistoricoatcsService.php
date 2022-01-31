<?php
namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\HistoricoatcsService as ParentService;

class HistoricoatcsService extends ParentService
{

    /**
     * @var \Nasajon\MDABundle\Repository\Crm\HistoricoatcsRepository
     */
    protected $repository;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\HistoricoatcsRepository $repository,
        $historicosatcsanexosService
    ) {
        $this->repository = $repository;
        $this->historicosatcsanexosService = $historicosatcsanexosService;
    }

    /**
     * @return array
     */
    public function findAll($tenant, $negocio, Filter $filter = null)
    {
        $historicos = parent::findAll($tenant, $negocio, $filter);

        //caso o histórico possua anexos, busca os anexos e os adiciona as observações
        
        foreach ($historicos as $key => $historico) {
            $anexos = $this->historicosatcsanexosService->findAll($historico['historiconegocio'], $tenant);
            if(!empty($anexos)){
                $bp='';
            }
        }

        return $historicos;
    }
}
