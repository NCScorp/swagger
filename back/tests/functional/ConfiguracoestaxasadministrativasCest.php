<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

class ConfiguracoestaxasadministrativasCest
{

    private $url_base = '/api/gednasajon/configuracoestaxasadministrativas/';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';


    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::CFGTAXASADM_GERENCIAR]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
        $I->deleteAllFromDatabase('crm.configuracoestaxasadministrativas');
    }

    /**
     * @param FunctionalTester $I
     */
    public function criaConfiguracaoTaxaAdministrativa(FunctionalTester $I)
    {

        /* execução da funcionalidade */

        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);
        $cliente = $I->haveInDatabaseCliente($I);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);

        $dados = [
            'estabelecimento' => $estabelecimento,
            'seguradora' => $cliente,
            'itemfaturamento' => ['servico' => 'baa4ff9a-6f59-4963-853d-6d318fe83006'],
            'formapagamento' => $formapagamento,
            'municipioprestacao' => $municipioprestacao,
            'pessoa' => $cliente['id'],
            'valor' => 300,
        ];
        $configuraxaoTaxaCriada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $I->canSeeInDatabase('crm.configuracoestaxasadministrativas', ['configuracaotaxaadm' => $configuraxaoTaxaCriada['configuracaotaxaadm']]);
        /* remove dado criado no banco*/
        $I->deleteFromDatabase('crm.configuracoestaxasadministrativas', ['configuracaotaxaadm' => $configuraxaoTaxaCriada['configuracaotaxaadm']]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function editaConfiguracaoTaxaAdministrativa(FunctionalTester $I)
    {
        /* execução da funcionalidade */
        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);
        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento);


        $dadosEnvio = [
            'estabelecimento' => ['estabelecimento' => $configuracaotaxaadm['estabelecimento']],
            'seguradora' => ['cliente' => $configuracaotaxaadm['seguradora']],
            'itemfaturamento' => ['servico' => $configuracaotaxaadm['itemfaturamento']],
            'formapagamento' => ['formapagamento' => $configuracaotaxaadm['formapagamento']],
            'municipioprestacao' => ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']],
            'pessoa' => $configuracaotaxaadm['seguradora'],
            'valor' => $configuracaotaxaadm['valor'],
        ];

        $dadosEnvio['valor'] += 100;

        $I->sendRaw('PUT', $this->url_base . $configuracaotaxaadm['configuracaotaxaadm'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dadosEnvio, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('crm.configuracoestaxasadministrativas', [
            'configuracaotaxaadm' => $configuracaotaxaadm['configuracaotaxaadm'],
            'valor' => $dadosEnvio['valor']
        ]);
        /* remove dado criado no banco*/
    }

    /**
     * @param FunctionalTester $I
     */
    public function indexConfiguracaoTaxaAdministrativa(FunctionalTester $I)
    {
        /* inicializações */
        $configuracoes = [];


        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);

        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 100]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 200]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 400]);

        $countAtual = $I->grabNumRecords('crm.configuracoestaxasadministrativas', ['tenant' => $this->tenant_numero]);

        /* execução da funcionalidade */
        $lista = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount(count($configuracoes), $lista);
        $I->assertCount($countAtual, $lista);
    }


    /**
     * @param FunctionalTester $I
     */
    public function getConfiguracaoTaxaAdministrativa(FunctionalTester $I)
    {
        /* inicializações */
        $configuracoes = [];

        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);

        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 100]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 200]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 400]);

        /* execução da funcionalidade */
        $configuracao = $I->sendRaw('GET', $this->url_base . $configuracaotaxaadm['configuracaotaxaadm'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($configuracaotaxaadm, [
            'configuracaotaxaadm' => $configuracao['configuracaotaxaadm'],
            'tenant' => $configuracao['tenant'],
            'grupoempresarial' => $configuracao['id_grupoempresarial'],
            'configuracaotaxaadm' => $configuracao['configuracaotaxaadm'],
            'estabelecimento' => $configuracao['estabelecimento']['estabelecimento'],
            'seguradora' => $configuracao['seguradora']['cliente'],
            'itemfaturamento' => $configuracao['itemfaturamento']['servico'],
            'formapagamento' => $configuracao['formapagamento']['formapagamento'],
            'municipioprestacao' => $configuracao['municipioprestacao']['pessoamunicipio'],
            'valor' => $configuracao['valor'],
        ]);
    }
}
