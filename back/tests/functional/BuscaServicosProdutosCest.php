<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa a view que busca serviços e produtos
 * 
 */
class BuscaServicosProdutosCest
{
    private $url_base = '/api/gednasajon';
    private $url_complemento = 'buscarservicoseprodutos';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';

    /**
     * Retorna o código do método de acionamento do fornecedor de acordo com o nome passado
     */
    private function getAcionamentoMetodo($pMetodo) {
        $arrAcionamentoMetodos = [
            'sistema' => 1,
            'email' => 2,
            'telefone' => 3
        ];

        return $arrAcionamentoMetodos[$pMetodo];
    }

    /**
    *
    * @param FunctionalTester $I
    */
    public function _before(FunctionalTester $I){
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::FORNECEDORESENVOLVIDOS_CREATE, EnumAcao::FORNECEDORESENVOLVIDOS_DELETE]);
    }

    /**
    *
    * @param FunctionalTester $I
    */
    public function _after(FunctionalTester $I){
    }

    /**
     * getAll
     * @param FunctionalTester $I
     * @return void
     */
    public function getAll(FunctionalTester $I){
        /* Mock de banco */
        $dados = [];
        $composicao = $I->haveinDatabaseComposicao($I);
        $composicao2 = $I->haveinDatabaseComposicao($I, [
            'codigo' => '2',
            'servicocoringa' => true
        ]);
        $familia = $I->haveInDatabaseFamilia($I);
        $familia2 = $I->haveInDatabaseFamilia($I, [
            'codigo' => '2',
            'familiacoringa' => true
        ]);
        
        $dados[] = [
            'tipo' => 1,
            'id' => $composicao['composicao'],
            'codigo' => $composicao['codigo'],
            'nome' => $composicao['nome'],
            'coringa' => $composicao['servicocoringa'],
        ];
        $dados[] = [
            'tipo' => 1,
            'id' => $composicao2['composicao'],
            'codigo' => $composicao2['codigo'],
            'nome' => $composicao2['nome'],
            'coringa' => $composicao2['servicocoringa'],
        ];
        $dados[] = [
            'tipo' => 2,
            'id' => $familia['familia'],
            'codigo' => $familia['codigo'],
            'nome' => $familia['descricao'],
            'coringa' => $familia['familiacoringa'],
        ];
        $dados[] = [
            'tipo' => 2,
            'id' => $familia2['familia'],
            'codigo' => $familia2['codigo'],
            'nome' => $familia2['descricao'],
            'coringa' => $familia2['familiacoringa'],
        ];
        
        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$this->url_complemento}/?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(true, is_array($retorno));
        // Só descomentar quando fizer testes em um tenant próprio, já que os dados do dump interferem na contagem.
        // $I->assertEquals(4, count($retorno));

        foreach ($dados as $dadoEsperado) {
            $arrRetorno = array_values( array_filter($retorno, function($_dadoRetorno) use ($dadoEsperado) {
                return ($dadoEsperado['id'] == $_dadoRetorno['id'] && $dadoEsperado['tipo'] == $_dadoRetorno['tipo']);
            }) );
    
            $I->assertEquals(1, count($arrRetorno));
            $itemRetorno = $arrRetorno[0];

            $I->assertEquals($dadoEsperado['codigo'], $itemRetorno['codigo']);
            $I->assertEquals($dadoEsperado['nome'], $itemRetorno['nome']);
            $I->assertEquals($dadoEsperado['coringa'], $itemRetorno['coringa']);
        }
    }
}