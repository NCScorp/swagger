<?php

namespace AppBundle\Service;

use AppBundle\Resources\Permissoes;
use Nasajon\SDK\Client;
use Nasajon\SDK\Diretorio\DiretorioClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PermissoesService
{
    protected $cache;

    /**
     * @var DiretorioClient
     */
    protected $diretorioSDK;

    public function __construct($cache, $diretorioSDK)
    {
        $this->cache = $cache;
        $this->diretorioSDK = $diretorioSDK;
    }

    public function getPermissoesByFuncoes($funcoes)
    {
        $permissoes = [];
        foreach($funcoes as $funcao) {
            $permissoes = array_merge($permissoes, $this->recuperarPermissoesByFuncao($funcao->getId()));
        }
        return $permissoes;
    }

    /**
     * @var $funcao id da função
     * @return array
     */
    private function recuperarPermissoesByFuncao($funcao)
    {
        return $this->diretorioSDK->getPermissoesByFuncao($funcao, $this->cache);
    }
}