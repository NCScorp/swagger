<?php

use Codeception\Util\HttpCode;

class ComposicoesfuncoesCest {

    private $url = '/api/gednasajon/composicoesfuncoes/';
    private $tenant = 'gednasajon';
    private $tenant_numero = '47';
    private $grupoempresarial = 'FMA';

    public function _before(FunctionalTester $I) {
        $I->amSamlLoggedInAs('rodrigodirk@nasajon.com.br');
    }

    /**
     * Testa a criação de uma função na composição
     * E em seguida, checa se ainda está lá.
     * @param FunctionalTester $I
     */
    public function criaComposicaoFuncao(FunctionalTester $I) {
        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I);
        $funcao = $I->haveInDatabaseFuncao($I);
        $composicaofuncao = [
            'composicao' => $composicao,
            'funcao' => $funcao,
            'quantidade' => 4,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => '47'
        ];

        /* execução da funcionalidade */
        $composicaofuncao_criada = $I->sendRaw('POST', '/api/gednasajon/' . $composicao['composicao'] . '/composicoesfuncoes/' . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $composicaofuncao, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($composicao['composicao'], $composicaofuncao_criada['composicao']);
        $I->assertEquals($composicaofuncao['quantidade'], $composicaofuncao_criada['quantidade']);
        $I->assertEquals($this->tenant_numero, $composicaofuncao_criada['tenant']);

        /* remove documento criado */
        $I->deleteFromDatabase('crm.composicoesfuncoes', ['composicaofuncao' => $composicaofuncao_criada['composicaofuncao']]);
    }

    /**
     * Testa a desvinculação da função a composição
     * E em seguida, checa se foi mesmo removida.
     * @param FunctionalTester $I
     */
    public function desvinculaComposicaoFuncao(FunctionalTester $I) {
        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I);
        $funcao = $I->haveInDatabaseFuncao($I);
        $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);

        /* execução da funcionalidade */
        $I->sendRaw('DELETE', '/api/gednasajon/' . $composicao['composicao'] . '/composicoesfuncoes/' . $composicaofuncao['composicaofuncao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $composicao, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->cantSeeInDatabase('crm.composicoesfuncoes', $composicaofuncao);
    }

    /**
     * Testa a lista de funções de composições
     * Checa se está trazendo todas
     * @param FunctionalTester $I
     */
    public function listarComposicaoFuncao(FunctionalTester $I) {
        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I);
        $funcao = $I->haveInDatabaseFuncao($I);
        $composicaofuncao1 = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
        $composicaofuncao2 = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);

        /* execução da funcionalidade */
        $listacomposicaofuncao = $I->sendRaw('GET', '/api/gednasajon/' . $composicao['composicao'] . '/composicoesfuncoes/' . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($composicao['composicao'], $listacomposicaofuncao[0]['composicao']);
        $I->assertEquals($composicao['composicao'], $listacomposicaofuncao[1]['composicao']);
        $I->assertEquals($funcao, $listacomposicaofuncao[0]['funcao']);
        $I->assertEquals($funcao, $listacomposicaofuncao[1]['funcao']);
    }

}
