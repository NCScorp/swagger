<?php

namespace Admin;

use Codeception\Util\HttpCode;
use FunctionalTester;

class ChamadosCest {

    public function _before(FunctionalTester $I) {
        $I->haveHttpHeader('apikey', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjb250YSI6MTM1LCJ0aXBvIjoiY29udGEifQ.whDxWdy8MRYDANJsfkxujJ9JOctP8rTXXqKwAgJtf7w');
        $I->amLoggedInAs('rodrigodirk@nasajon.com.br');
    }

    protected function chamadosProvider(){
        return [
            ['chamado' => [
                "anexos" => [],
                "email" => "nasajon@nasajon.com",
                "responsavel_web" => "wilsonsantos@nasajon.com.br",
                "sintoma" => "<p>Resposta Chamado Teste 123</p>",
                "tipo" => 0,
                "visivelparacliente" => true,
                "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896699"]
                ],
            'retorno' => HttpCode::CREATED
            ],
            ['chamado' => [
                "anexos" => [],
                "email" => "nasajon123@nasajon.com",
                "responsavel_web" => "wilsonsantos@nasajon.com.br",
                "sintoma" => "<p>Sintoma ABC</p>",
                "tipo" => 0,
                "visivelparacliente" => true,
                "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896697"]
                ],
            'retorno' => HttpCode::CREATED
            ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma VVV</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 1</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 2</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 3</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 4</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 5</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 6</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 7</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 8</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 9</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 10</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 11</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 12</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 13</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 14</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 15</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 16</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 17</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 18</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 19</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 20</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 22</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 23</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 24</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 25</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 26</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 27</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 28</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 29</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 30</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => "8247a4ae-a95f-4656-babd-7324f6896698"]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
            // ['chamado' => [
            //     "anexos" => [],
            //     "email" => "nasajonAAA@nasajon.com",
            //     "responsavel_web" => "wilsonsantos@nasajon.com.br",
            //     "sintoma" => "<p>Sintoma 31</p>",
            //     "tipo" => 0,
            //     "visivelparacliente" => true,
            //     "cliente" => [ "cliente" => 1]
            //     ],
            // 'retorno' => HttpCode::CREATED
            // ],
        ];
    }

    /**
    * @dataProvider chamadosProvider
    */
    // public function adminCriaChamadoApi(FunctionalTester $I, \Codeception\Example $example) {
    //     $I->haveInDatabase('ns.pessoas', [
    //         'id' => $example['chamado']['cliente']['cliente'], 
    //         'pessoa'=> '1', 
    //         'nome' => 'Desenvolvimento',
    //         'nomefantasia' => 'Desenvolvimento',
    //         'tenant' => 47,
    //         'clienteativado' => 1, 
    //         'cnpj' => '64.976.329/0001-75',
    //         'tipocontrolepagamento' => 1, 
    //         'situacaopagamento' => 1,
    //         'tipoclientepagamento' => 1,
    //         'bloqueado' => FALSE
    //     ]);

    //    $I->sendRaw('POST', '/api/admin/gednasajon/atendimentos/', $example['chamado'], [], [], null);
    //    $I->seeResponseCodeIs($example['retorno']);
    // }

}
