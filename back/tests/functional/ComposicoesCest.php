<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

class ComposicoesCest {

    private $url = '/api/gednasajon/composicoes/';
    private $tenant = 'gednasajon';
    private $tenant_numero = '47';
    private $servicotecnico = '37ea071a-c2cd-4dba-87e8-5300a5be7af3';
    private $grupoempresarial = 'FMA';
    private $id_grupoempresarial = '95cd450c-30c5-4172-af2b-cdece39073bf';
    private $itemFaturamento = 'baa4ff9a-6f59-4963-853d-6d318fe83006';

    public function _before(FunctionalTester $I) {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::COMPOSICOES_INDEX, EnumAcao::COMPOSICOES_CREATE, EnumAcao::COMPOSICOES_PUT]);
    }

    /**
     * 
     */
    private function getComposicaoInfo($composicao){
        return [
            'composicao' => $composicao['composicao'],
            'nome' => $composicao['nome'],
            'descricao' => $composicao['descricao'],
            'codigo' => $composicao['codigo'],
            'servicotecnico' => $composicao['servicotecnico']['servicotecnico'],
            'tenant' => $this->tenant_numero,
            'servicocoringa' => $composicao['servicocoringa'],
            'servicoexterno' => $composicao['servicoexterno'],
            'servicoprestadoraacionada' => $composicao['servicoprestadoraacionada'],
            'itemfaturamento' => $composicao['itemfaturamento']['servico']
        ];
    }

    /**
     * Testa a criação da Composição
     * E em seguida, checa se ainda está lá.
     * @param FunctionalTester $I
     */
    public function criaComposicao(FunctionalTester $I) {

        /* prepara cenário */
        $composicao = [
            'nome' => 'teste1',
            'descricao' => 'descricao1',
            'codigo' => '123',
            'created_at' => '',
            'created_by' => '',
            'servicotecnico' => [ 'servicotecnico' => $this->servicotecnico],
            'servicocoringa' => true,
            'servicoexterno' => false,
            'servicoprestadoraacionada' => true,
            'itemfaturamento' => ['servico' => $this->itemFaturamento]
        ];

        /* execução da funcionalidade */
        $composicao_criada = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial=' . $this->grupoempresarial, $composicao, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($composicao['nome'], $composicao_criada['nome']);
        $I->assertEquals($composicao['descricao'], $composicao_criada['descricao']);
        $I->assertEquals($composicao['codigo'], $composicao_criada['codigo']);
        $I->assertEquals($this->tenant_numero, $composicao_criada['tenant']);
        $I->assertEquals($this->id_grupoempresarial, $composicao_criada['id_grupoempresarial']);
        $I->assertEquals($composicao['servicocoringa'], $composicao_criada['servicocoringa']);
        $I->assertEquals($composicao['servicoexterno'], $composicao_criada['servicoexterno']);
        $I->assertEquals($composicao['servicoprestadoraacionada'], $composicao_criada['servicoprestadoraacionada']);
        $I->assertEquals($composicao['itemfaturamento']['servico'], $composicao_criada['itemfaturamento']['servico']);
        
        /* remove documento criado */
        $I->deleteFromDatabase('crm.composicoes', ['composicao' => $composicao_criada['composicao']]);
    }

    /**
     * Edita a composição criada
     * E em seguida, checa se o campo editado está correto na tabela
     * @param FunctionalTester $I
     */
    public function editaComposicao(FunctionalTester $I) {

        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I, ['servicoexterno' => false, 'servicoprestadoraacionada' => true]);
        $composicao['nome'] = 'Edição';
        $composicao['servicocoringa'] = true;
        $composicao['servicoexterno'] = true;
        $composicao['servicoprestadoraacionada'] = false;
        $composicao['itemfaturamento'] = ['servico' => $this->itemFaturamento];

        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url . $composicao['composicao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $composicao, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $composicaoInfo = $this->getComposicaoInfo($composicao);
        $I->canSeeInDatabase('crm.composicoes', $composicaoInfo);
    }
    
    /**
     * Testa a lista de objetos que será recuperada após a criação de uma composição.
     * E em seguida, checa se apenas os elementos relacionados aquela composição estão sendo mostrados.
     * @param FunctionalTester $I
     */
    public function listaComposicoesFamilias(FunctionalTester $I) {
        /* prepara cenário */
        $composicao = $I->haveInDatabaseComposicao($I);
        $composicao2 = $I->haveInDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $familia2 = $I->haveInDatabaseFamilia($I);
        $composicaofamilia1 = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofamilia2 = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofamilia3 = $I->haveInDatabaseComposicaoFamilia($I, $familia2, $composicao2);

        /* execução da funcionalidade */
        $listacomposicoes = $I->sendRaw('GET', $this->url . $composicao['composicao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(2, count($listacomposicoes['familias']));
        $I->assertEquals($composicao['composicao'], $listacomposicoes['familias'][0]['composicao']);
        $I->assertEquals($composicao['composicao'], $listacomposicoes['familias'][1]['composicao']);
    }

}
