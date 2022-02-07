<?php
use Codeception\Util\HttpCode;
use AppBundle\Resources\Permissoes;

/**
 * Testa Informes
 */
class InformesCest {
    private $url_base = '/gednasajon';
    private $url_base_casouso = 'informesrendimentos';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $conta = "rodrigodirk@nasajon.com.br";
    private $estabelecimento = '39836516-7240-4fe5-847b-d5ee0f57252d';

    /**
     * Executado antes de cada método da classe
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs($this->conta, [], $this->estabelecimento);
        $I->deleteTrabalhador($I, $this->conta, $this->tenant_numero);
    }

    /**
     * Executado depois de cada método da classe
     */
    public function  _after(FunctionalTester $I){
        $I->deleteTrabalhador($I, $this->conta, $this->tenant_numero);
    }

    /**
       * Teste para listar informes
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
    public function listaInformes(FunctionalTester $I)
    {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $informe = $I->haveInDatabaseInformes($I, $trabalhador['trabalhador']);

        // funcionalidade testada
        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        $informe = $I->sendRaw('GET', $url, [], [], [], null);

        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount(1, $informe);
        
    }
}