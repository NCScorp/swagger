<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa testa Listadavezvendedores
 */
class ListadavezvendedoresCest
{

    private $url_base = '/api/gednasajon/listadavezvendedores/';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';

    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::LISTADAVEZVENDEDORES_INDEX, EnumAcao::LISTADAVEZVENDEDORES_GET, EnumAcao::LISTADAVEZVENDEDORES_CREATE,
                                                        EnumAcao::LISTADAVEZVENDEDORES_PUT, EnumAcao::LISTADAVEZVENDEDORES_DELETE]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function criaListaVendedor(FunctionalTester $I)
    {

        /* execução da funcionalidade */
        $listavendedor = [
            'nome' => 'nome vendedor',
            'totalmembros' => '0'
        ];
        $listavendedor_criado = $I->sendRaw(
            'POST', //método
            $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedor, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $listavendedor['listadavezvendedor'] = $listavendedor_criado['listadavezvendedor']; // colocando chave primária no array original para verificar se todas as informações estão no banco

        $I->canSeeInDatabase('crm.listadavezvendedores', [
            'listadavezvendedor' => $listavendedor['listadavezvendedor']
        ]);

        /* remove dado criado no banco */
        $I->deleteFromDatabase('crm.listadavezvendedores', ['listadavezvendedor' => $listavendedor['listadavezvendedor']]);
    }


    /**
     * @param FunctionalTester $I
     */
    public function editaListaVendedor(FunctionalTester $I)
    {
        
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
        $listavendedor['nome'] = 'nome vendedor editado';
        $listavendedor_editado = $I->sendRaw(
            'PUT', //método
            $this->url_base . $listavendedor['listadavezvendedor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedor, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        // $listavendedor['nome'] = $listavendedor_editado['nome']; // colocando chave primária no array original para verificar se todas as informações estão no banco

        $I->canSeeInDatabase('crm.listadavezvendedores', [
            'listadavezvendedor' => $listavendedor['listadavezvendedor'],
            'nome' => $listavendedor['nome']
        ]);

        /* remove dado criado no banco */
        $I->deleteFromDatabase('crm.listadavezvendedores', ['listadavezvendedor' => $listavendedor['listadavezvendedor']]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function deletaListaVendedor(FunctionalTester $I)
    {
        
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
        $listavendedor_editado = $I->sendRaw(
            'DELETE', //método
            $this->url_base . $listavendedor['listadavezvendedor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedor, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->cantSeeInDatabase('crm.listadavezvendedores', [
            'listadavezvendedor' => $listavendedor['listadavezvendedor'],
            'nome' => $listavendedor['nome']
        ]);

        /* remove dado criado no banco */
        $I->deleteFromDatabase('crm.listadavezvendedores', ['listadavezvendedor' => $listavendedor['listadavezvendedor']]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function criaListaVendedoritem(FunctionalTester $I)
    {
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
        /* execução da funcionalidade */
        $listavendedoritem = [
            'vendedor' => ['vendedor_id' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'],
            'posicao' => '2'
        ];
        $listavendedoritem_criado = $I->sendRaw(
            'POST', //método
            "/api/gednasajon/$listavendedor[listadavezvendedor]/listadavezvendedoresitens/" . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedoritem, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $listavendedoritem['listadavezvendedoritem'] = $listavendedoritem_criado['listadavezvendedoritem']; // colocando chave primária no array original para verificar se todas as informações estão no banco

        $I->canSeeInDatabase('crm.listadavezvendedoresitens', [
            'listadavezvendedoritem' => $listavendedoritem['listadavezvendedoritem']
        ]);

        /* remove dado criado no banco */
        $I->deleteFromDatabase('crm.listadavezvendedoresitens', ['listadavezvendedoritem' => $listavendedoritem['listadavezvendedoritem']]);
    }


    /**
     * @param FunctionalTester $I
     */
    public function editaListaVendedoritem(FunctionalTester $I)
    {
        
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
        $listavendedoritem = $I->haveInDatabaseListadavezvendedoritem($I, $listavendedor);

        $listavendedoritem['posicao'] = '20';
        $listavendedoritem['vendedor'] = ['vendedor_id' => $listavendedoritem['id_vendedor']];
        $listavendedoritem_criado = $I->sendRaw(
            'PUT', //método
            "/api/gednasajon/$listavendedor[listadavezvendedor]/listadavezvendedoresitens/" . $listavendedoritem['listadavezvendedoritem'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedoritem, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        // $listavendedor['nome'] = $listavendedor_editado['nome']; // colocando chave primária no array original para verificar se todas as informações estão no banco

        $I->canSeeInDatabase('crm.listadavezvendedoresitens', [
            'listadavezvendedoritem' => $listavendedoritem['listadavezvendedoritem'],
            'posicao' => $listavendedoritem['posicao']
        ]);

        /* remove dado criado no banco */
        $I->deleteFromDatabase('crm.listadavezvendedoresitens', ['listadavezvendedoritem' => $listavendedoritem['listadavezvendedoritem']]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function deletaListaVendedoritem(FunctionalTester $I)
    {
        
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
        $listavendedoritem = $I->haveInDatabaseListadavezvendedoritem($I, $listavendedor);
        $listavendedoritem['vendedor'] = ['vendedor_id' => $listavendedoritem['id_vendedor']];

        $listavendedoritem_criado = $I->sendRaw(
            'DELETE', //método
            "/api/gednasajon/$listavendedor[listadavezvendedor]/listadavezvendedoresitens/" . $listavendedoritem['listadavezvendedoritem'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedoritem, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->cantSeeInDatabase('crm.listadavezvendedoresitens', [
            'listadavezvendedoritem' => $listavendedoritem['listadavezvendedoritem']
        ]);
    }


    /**
     * @param FunctionalTester $I
     */
    public function indexaListaVendedoritemFiltroPorPosicao(FunctionalTester $I)
    {
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
        $array_listavendedoritem[] = $I->haveInDatabaseListadavezvendedoritem($I, $listavendedor);
        $array_listavendedoritem[] = $I->haveInDatabaseListadavezvendedoritem($I, $listavendedor, '2');
        $array_listavendedoritem[] = $I->haveInDatabaseListadavezvendedoritem($I, $listavendedor, '3');
        /* execução da funcionalidade */
        
        $lista_listavendedoritem = $I->sendRaw(
            'GET', //método
            "/api/gednasajon/$listavendedor[listadavezvendedor]/listadavezvendedoresitens/" . '?tenant=' . $this->tenant . 
            "&grupoempresarial=" . $this->grupoempresarial  . '&filter=&posicao=1', //url
            [], //body
            [],
            [],
            null
        );

        /* validação do resultado */
         $I->canSeeResponseCodeIs(HttpCode::OK);


        $I->assertEquals($lista_listavendedoritem[0]['listadavezvendedoritem'], $array_listavendedoritem[0]['listadavezvendedoritem']);
        $I->assertEquals($lista_listavendedoritem[0]['posicao'], $array_listavendedoritem[0]['posicao']);

        // /* remove dado criado no banco */
        // $I->deleteFromDatabase('crm.listadavezvendedoresitens', ['listadavezvendedoritem' => $listavendedoritem['listadavezvendedoritem']]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function exibeListaVendedor(FunctionalTester $I)
    {
        
        /* Inicializações */
        $listavendedor = $I->haveInDatabaseListadavezvendedor($I);

        /* Execução da funcionalidade */
        $listavendedor_retornado = $I->sendRaw(
            'GET', //método
            $this->url_base . $listavendedor['listadavezvendedor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            $listavendedor, //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($listavendedor['listadavezvendedor'], $listavendedor_retornado['listadavezvendedor']);
        $I->assertEquals($listavendedor['nome'], $listavendedor_retornado['nome']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function listaListasDaVezVendedor(FunctionalTester $I)
    {
        
        /* Inicializações */
        $I->haveInDatabaseListadavezvendedor($I);
        $I->haveInDatabaseListadavezvendedor($I, [
            'nome' => 'Lista 123'
        ]);
        $countAtual = $I->grabNumRecords('crm.listadavezvendedores', ['tenant' => $this->tenant_numero]);

        /* Execução da funcionalidade */
        $lista = $I->sendRaw(
            'GET', //método
            $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
            [], //body
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual, $lista);

    }
    
}
