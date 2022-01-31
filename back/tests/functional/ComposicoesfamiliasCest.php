<?php

use Codeception\Util\HttpCode;

class ComposicoesfamiliasCest {

    private $url = '/api/gednasajon/composicoesfamilias/';
    private $tenant = 'gednasajon';
    private $tenant_numero = '47';
    private $grupoempresarial = 'FMA';

    public function _before(FunctionalTester $I) {
        $I->amSamlLoggedInAs('rodrigodirk@nasajon.com.br');
    }

    /**
     * Testa a criação da familia de produtos dentro de composição
     * E em seguida, checa se ainda está lá.
     * @param FunctionalTester $I
     */
    public function criaComposicaoFamilia(FunctionalTester $I) {
        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicaofamilia = [
            'composicao' => $composicao,
            'familia' => $familia,
            'quantidade' => 4,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => '47'
        ];

        /* execução da funcionalidade */
        $composicaofamilia_criada = $I->sendRaw('POST', '/api/gednasajon/' . $composicao['composicao'] . '/composicoesfamilias/' . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $composicaofamilia, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($composicao['composicao'], $composicaofamilia_criada['composicao']);
        $I->assertEquals($composicaofamilia['quantidade'], $composicaofamilia_criada['quantidade']);
        $I->assertEquals($familia['familia'], $composicaofamilia_criada['familia']['familia']);
        $I->assertEquals($this->tenant_numero, $composicaofamilia_criada['tenant']);

        /* remove documento criado */
        $I->deleteFromDatabase('crm.composicoesfamilias', ['composicaofamilia' => $composicaofamilia_criada['composicaofamilia']]);
    }

    /**
     * Testa a lista de familias de produtos de uma composição 
     * Checa se esta trazendo apenas os pertinentes, ou seja a quantidade certa de elementos e se são eles os corretos.
     * @param FunctionalTester $I
     */
    public function listarComposicaoFamilia(FunctionalTester $I) {
        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I);
        $composicao2 = $I->haveInDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $familia2 = $I->haveInDatabaseFamilia($I);
        $composicaofamilia1 = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofamilia2 = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofamilia3 = $I->haveInDatabaseComposicaoFamilia($I, $familia2, $composicao2);

        /* execução da funcionalidade */
        $listacomposicaofamilia = $I->sendRaw('GET', '/api/gednasajon/' . $composicao['composicao'] . '/composicoesfamilias/' . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($composicao['composicao'], $listacomposicaofamilia[0]['composicao']);
        $I->assertEquals($composicao['composicao'], $listacomposicaofamilia[1]['composicao']);
        $I->assertEquals($familia['familia'], $listacomposicaofamilia[0]['familia']['familia']);
        $I->assertEquals($familia['familia'], $listacomposicaofamilia[1]['familia']['familia']);
        $I->assertEquals(2, count($listacomposicaofamilia));
    }

}
