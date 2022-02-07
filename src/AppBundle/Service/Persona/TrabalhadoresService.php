<?php

namespace AppBundle\Service\Persona;

use Nasajon\MDABundle\Service\Persona\TrabalhadoresService as ServiceParent;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Nasajon\MDABundle\Service\Persona\TarifasconcessionariasvtstrabalhadoresService;
use Nasajon\MDABundle\Service\Persona\TarifasconcessionariasvtsService;
use Nasajon\MDABundle\Service\Persona\ConcessionariasvtsService;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesalteracoesenderecosRepository;
use Nasajon\MDABundle\Request\FilterExpression;

/**
* Sobrescrito por causa do método getTrabalhador usado no fixedattributes
*/
class TrabalhadoresService extends ServiceParent{
    /**
     * 
     * @var ParameterBagInterface $fixedAttributes 
     */
    private $fixedAttributes;

    /**
     * 
     * @var TarifasconcessionariasvtstrabalhadoresService $trfsCncssnrasVtsTrblhdrsSrvc
     */
    private $trfsCncssnrasVtsTrblhdrsSrvc;

    /**
     * 
     * @var TarifasconcessionariasvtsService $trfsCncssnrasVtsSrvc
     */
    private $trfsCncssnrasVtsSrvc;

    /**
     * 
     * @var ConcessionariasvtsService $cncssnrsvtsSrvc
     */
    private $cncssnrsvtsSrvc;

    /**
     * 
     * @var SolicitacoesalteracoesenderecosRepository $slctcsltrcsndrcsRpstr
     */

  public function __construct(\AppBundle\Repository\Persona\TrabalhadoresRepository $repository, ParameterBagInterface $fixedAttributes, TarifasconcessionariasvtstrabalhadoresService $trfsCncssnrasVtsTrblhdrsSrvc, TarifasconcessionariasvtsService $trfsCncssnrasVtsSrvc, ConcessionariasvtsService $cncssnrsvtsSrvc, SolicitacoesalteracoesenderecosRepository $slctcsltrcsndrcsRpstr){
    $this->repository = $repository;
    $this->fixedAttributes = $fixedAttributes;
    $this->trfsCncssnrasVtsTrblhdrsSrvc = $trfsCncssnrasVtsTrblhdrsSrvc;
    $this->trfsCncssnrasVtsSrvc = $trfsCncssnrasVtsSrvc;
    $this->cncssnrsvtsSrvc = $cncssnrsvtsSrvc;
    $this->slctcsltrcsndrcsRpstr = $slctcsltrcsndrcsRpstr;
  }
  
  /**
   * Pela regra há apenas um trabalhador com o mesmo email no mesmo estabelecimento, então a aplicação garante isso. 
   * Antes era feito fetch no repository para pegar apenas a tupla, porém quando o banco estava inconsistente o erro era quase não detectavel na aplicação.
   */
  public function getTrabalhador($tenant, $estabelecimento, $contanasajon, $trabalhador){
    $trabalhador = $this->getRepository()->getTrabalhador($tenant, $estabelecimento, $contanasajon, $trabalhador);
    if(count($trabalhador) > 1){
      throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException();
    } else if (count($trabalhador) == 0) {
      throw new \Doctrine\ORM\NoResultException();
    }
    return $trabalhador[0];
  }

  /**
   * Esse método retorna informações gerais do trabalhador necessárias para o
   * profileController monta-lo para alimentar o select de trabalhador no frontend
   * @param uuid $trabalhador
   */
  public function getInfoTrabalhador($trabalhador, $tenant){
    $trabalhador = $this->getRepository()->getInfoTrabalhador($trabalhador, $tenant);

    if(count($trabalhador) > 1){
      throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException();
    }
    return $trabalhador[0];
  }

  public function getTrabalhadorByIdentificacaoNasajon($identificacaonasajon)
    {
        $trabalhador = $this->getRepository()->getTrabalhadorByIdentificacaoNasajon($identificacaonasajon);

        if($trabalhador == false){
            return false;
        }
        return $trabalhador;
    }

    public function getNomeByIdentificacaoNasajon($tenant, $identificacaonasajon)
    {
        $trabalhador = $this->getRepository()->getNomeByIdentificacaoNasajon($tenant, $identificacaonasajon);
        if($trabalhador == false){
            return false;
        }
        return $trabalhador;
    }


  /**
   * Retorna um resumo do trabalhador
   */
  public function resumo($tenant) {

    $trabalhador = $this->fixedAttributes->get('trabalhador');
    
    $trabalhadorArr = $this->getRepository()->resumo($tenant, $trabalhador);
    
    if(count($trabalhadorArr) === 1) {
      return $trabalhadorArr[0];
    } else {
      throw new NoResultException();
    }
  }

  /**
   * Retorna a foto do trabalhador
   */
  public function buscaFoto($tenant) {
    
    $trabalhador = $this->fixedAttributes->get('trabalhador');

    $foto = $this->getRepository()->buscaFoto($trabalhador, $tenant);

    if(count($foto) === 1) {
      return $foto;
    } else {
      throw new NotFoundHttpException();
    }
  }


  /**
   * Retorna o endereco do trabalhador
   */
  public function enderecocontato($tenant) {

    $trabalhador = $this->fixedAttributes->get('trabalhador');

    $trabalhadorEntity = $this->getRepository()->enderecocontato($tenant, $trabalhador);

    $filter = New Filter();
    $filter->addToFilterExpression(new FilterExpression('situacao','eq','0')); //Situação Aberta
    $solicitacao = $this->slctcsltrcsndrcsRpstr->findAll($tenant, $trabalhador, $filter);

    //Adicionar no endereço atual a Solicitação de alteração de endereço que estiver aberta
    //Como é possível existir mais de uma solicitação aberta, retorno uma lista
    $trabalhadorEntity['solicitacoesalteracoesenderecosaberta'] = $solicitacao;

    return $trabalhadorEntity;
  }

  public function vt($tenant) {

    $trabalhador = $this->fixedAttributes->get('trabalhador');
    $tenant = $this->fixedAttributes->get('tenant');

    $filter = new Filter();

    $datas = $this->trfsCncssnrasVtsTrblhdrsSrvc->findAll($tenant, $trabalhador, $filter);
    
    if(!empty($datas)) {
      $datas = array_map(function ($data) use ($tenant) {
        $data["tarifaconcessionariavt"] = $this->trfsCncssnrasVtsSrvc->find($data["tarifaconcessionariavt"], $tenant);
        $data["tarifaconcessionariavt"]["concessionariavt"] = $this->cncssnrsvtsSrvc->find($data["tarifaconcessionariavt"]["concessionariavt"], $tenant);
        return $data;
      }, $datas);
    } else {
      throw new NotFoundHttpException("Dados de VT não cadastrado para o trabalhador!");
    }

    return $datas;
  }
  
}