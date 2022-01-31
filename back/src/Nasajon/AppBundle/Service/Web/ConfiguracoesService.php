<?php

namespace Nasajon\AppBundle\Service\Web;

use Nasajon\MDABundle\Service\Web\ConfiguracoesService as ParentService;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;

/**
 * ConfiguracoesService sobrescrito fornecer o valor da configuração salva no banco
 */
class ConfiguracoesService extends ParentService{

    // private $cache;

    public function __construct( 
        \Nasajon\MDABundle\Repository\Web\ConfiguracoesRepository $repository,
        \Symfony\Component\Cache\Adapter\AdapterInterface $cache){
        $this->repository = $repository;
        $this->cache = $cache;
    }

    /**
     * Devolve o valor da chave nas configurações
     * @todo verificar o filter no construto e substituir em negócio
     */
    public function getValorBanco($tenant, $chave) {
        $filter = new Filter();
        $filterExpression = $filter->getFilterExpression();
        array_push($filterExpression, new FilterExpression('chave', 'eq', $chave));
        array_push($filterExpression, new FilterExpression('sistema', 'eq', 'CRMWEB')); 
        $filter->setFilterExpression($filterExpression);

        $conf = $this->findAll($tenant, $filter);

        if (sizeof($conf) > 0) {
        return $conf[0]['valor'];
        }

        return null;
    }

    public function getValor($tenant, $chave) {
        $idCache = "CRMWEB"."_".$tenant."_".$chave;

        //checa se o valor exite no cache
        //cria referencia para o valor in cache
        $configuracaoCacheItem = $this->cache->getItem($idCache);

        if (!$configuracaoCacheItem->isHit()) {
            //se não deu "hit" no valor, ou seja, não existe in cache
            //busca o valor direto no banco
            $configuracao = $this->getValorBanco($tenant, $chave);
            // return $configuracao;
            //seta o valor
            $configuracaoCacheItem->set($configuracao);
            //salva
            $this->cache->save($configuracaoCacheItem);
            // return $configuracao;
        }
        return $configuracaoCacheItem->get();
    }

  /**
   * @todo verificar em negocios se há a necessidade de enviar toda a configuração
   */
  public function getConfiguracao($tenant) {
    $filter = new Filter();
    $filterExpression = $filter->getFilterExpression();
    array_push($filterExpression, new FilterExpression('chave', 'eq', 'INTEGRACAO_GP'));
    array_push($filterExpression, new FilterExpression('sistema', 'eq', 'CRMWEB'));
    array_push($filterExpression, new FilterExpression('valor', 'eq', '1'));
    $filter->setFilterExpression($filterExpression);

    $conf = $this->findAll($tenant, $filter);

    if (sizeof($conf) > 0) {
      return $conf[0];
    }

    return null;
  }

}
