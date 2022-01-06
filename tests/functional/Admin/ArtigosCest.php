<?php

namespace Admin;

use Codeception\Util\HttpCode;
use FunctionalTester;

class ArtigosCest {

    public function _before(FunctionalTester $I) {
        // $I->haveHttpHeader('apikey', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjb250YSI6MTM1LCJ0aXBvIjoiY29udGEifQ.whDxWdy8MRYDANJsfkxujJ9JOctP8rTXXqKwAgJtf7w');
        $I->amLoggedInAs('rodrigodirk@nasajon.com.br');
    }

    protected function artigosProvider(){
        return [
            ["artigo" => [
                "conteudo" => "<p>&nbsp;1 Loren in Ipsum qui amiserint</p>",
                "criado_por_resposta" => false,
                "fixarnotopo" => false,
                "secao" => ["categoria" => "50b6e5f8-218e-4b78-8275-4330121a2c5b"],
                "status" => true,
                "tags" => [
                        0 => "Loren",
                        1 => "Ipsum",
                        2 => "Amiserint"
                    ],
                "tipoexibicao" => 1,
                "titulo" => "Artigo Teste 1"
                ],
            'retorno' => HttpCode::CREATED],
            ["artigo" => [
                "conteudo" => "<p>&nbsp;2 Loren in Ipsum qui amiserint</p>",
                "criado_por_resposta" => false,
                "fixarnotopo" => false,
                "secao" => ["categoria" => "1dd539af-a2fb-4294-b54c-e182b4bab475"],
                "status" => true,
                "tags" => [
                        0 => "Loren",
                        1 => "Ipsum",
                        2 => "Amiserint"
                    ],
                "tipoexibicao" => 1,
                "titulo" => "Artigo Teste 2"
                ],
            'retorno' => HttpCode::CREATED],
        ];
    }

    /**
    * @dataProvider artigosProvider
    */
    // public function adminCriaArtigoApi(FunctionalTester $I,  \Codeception\Example $example) {
    //     $I->haveInDatabase('atendimento.categorias', [
    //         'categoria' => $example['artigo']['secao']['categoria'], 
    //         'titulo' => 'Atendimento Sec 1', 
    //         'tenant' => 47, 
    //         'created_at' => '2019-09-19 10:36:40.000', 
    //         'updated_at' => NULL, 
    //         'lastupdate' => '2019-09-19 13:36:39.856', 
    //         'created_by' => '{"nome":"Wilson Santos","email":"wilsonsantos@nasajon.com.br"}', 
    //         'updated_by' => NULL,
    //         'ordem' => 0,
    //         'tipo' => 3,
    //         'categoriapai' => '17fc0802-b1be-45f0-a39f-f8c3a6a328ba',
    //         'descricao' => 'Seção da Subcategoria Atendimento Sec 1',
    //         'status' => 1,
    //         'tipoordenacao' => 2
    //     ]);
    //    $I->sendRaw('POST', '/api/admin/gednasajon/artigos/?secao=' . $example['artigo']['secao']['categoria'], $example['artigo'], [], [], null);
       
    //    $I->seeResponseCodeIs($example['retorno']);
    // }

}
