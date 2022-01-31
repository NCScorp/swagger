<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa ListadaVezRegras
 */
class ListadaVezRegrasCest {

  private $url_base = '/api/gednasajon/listadavezregras/';
  private $url_base2 = '/api/gednasajon/listadavezregrasvalores/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::VINCULOS_CREATE, EnumAcao::VINCULOS_PUT, EnumAcao::VINCULOS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaListadaVezRegras(FunctionalTester $I) {

    /* inicializações */
    $listaRegras = [];
    $regra = $I->haveInDatabaseListadaVezRegra($I);
    $listaRegras[] = $regra;
    $regra = $I->haveInDatabaseListadaVezRegra($I, [
        'nome' => 'Regra 2'
    ]);
    $listaRegras[] = $regra;
    $regra = $I->haveInDatabaseListadaVezRegra($I, [
        'nome' => 'Regra 3'
    ]);
    $listaRegras[] = $regra;

    $regras = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(count($listaRegras), count($regras));
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function getListadaVezRegra(FunctionalTester $I) {

     /* inicializações */
    $listaRegras = [];
    $regra = $I->haveInDatabaseListadaVezRegra($I);
    $listaRegras[] = $regra;
    $regra = $I->haveInDatabaseListadaVezRegra($I, [
        'nome' => 'Regra 2'
    ]);
    $listaRegras[] = $regra;
    $regra = $I->haveInDatabaseListadaVezRegra($I, [
        'nome' => 'Regra 3'
    ]);
    $listaRegras[] = $regra;
 
     $regraResponse = $I->sendRaw('GET', $this->url_base . $regra['listadavezregra'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
 
     $I->assertEquals($regra['listadavezregra'], $regraResponse['listadavezregra']);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaListadaVezRegrasValores(FunctionalTester $I) {

    /* inicializações */
    $regra = $I->haveInDatabaseListadaVezRegra($I);

    $listaRegrasValores = [];
    $regravalor = $I->haveInDatabaseListadaVezRegraValor($I, [
        'listadavezregra' => $regra
    ]);
    $listaRegrasValores[] = $regravalor;
    $regravalor = $I->haveInDatabaseListadaVezRegraValor($I, [
        'listadavezregra' => $regra,
        'nome' => 'Valor 2',
        'valor' => '2'
    ]);
    $listaRegrasValores[] = $regravalor;
    $regravalor = $I->haveInDatabaseListadaVezRegraValor($I, [
        'listadavezregra' => $regra,
        'nome' => 'Valor 3',
        'valor' => '3'
    ]);
    $listaRegrasValores[] = $regravalor;
    

    $regrasvalores = $I->sendRaw('GET', "/api/gednasajon/$regra[listadavezregra]/listadavezregrasvalores/" . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(count($listaRegrasValores), count($regrasvalores));
  }

    /**
     * @param FunctionalTester $I
     * @todo testar demais campos
     */
    public function getListadaVezRegraValor(FunctionalTester $I) {

        /* inicializações */
        $regra = $I->haveInDatabaseListadaVezRegra($I);

        $listaRegrasValores = [];
        $regravalor = $I->haveInDatabaseListadaVezRegraValor($I, [
            'listadavezregra' => $regra
        ]);
        $listaRegrasValores[] = $regravalor;
        $regravalor = $I->haveInDatabaseListadaVezRegraValor($I, [
            'listadavezregra' => $regra,
            'nome' => 'Valor 2',
            'valor' => '2'
        ]);
        $listaRegrasValores[] = $regravalor;
        $regravalor = $I->haveInDatabaseListadaVezRegraValor($I, [
            'listadavezregra' => $regra,
            'nome' => 'Valor 3',
            'valor' => '3'
        ]);
        $listaRegrasValores[] = $regravalor;

  
        $regravalorResponse = $I->sendRaw('GET', "/api/gednasajon/$regra[listadavezregra]/listadavezregrasvalores/" . $regravalor['listadavezregravalor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->assertEquals($regravalor['listadavezregravalor'], $regravalorResponse['listadavezregravalor']);
    }

}
