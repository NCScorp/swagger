<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa a configuração de template de email para advertir prestadores de serviços
 */
class TemplateEmailAdvertirPrestadorCest
{
    private $url_base = '/api/gednasajon';
    private $url_complemento_template_email = 'templatesemailadvertirprestador';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

    /**
    *
    * @param FunctionalTester $I
    */
    public function _before(FunctionalTester $I){
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', []);
    }

    /**
    *
    * @param FunctionalTester $I
    */
    public function _after(FunctionalTester $I){
    }

    /**
     * Insere template de email para advertir prestador de serviços
     * @param FunctionalTester $I
     * @return void
     */
    public function create(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);

        /* Monto dados da requisição */
        $dados = [
            'estabelecimento' => $estabelecimento,
            // 'responderpara' => 'xpto@blogspot.com', //Ainda não tem suporte para o "responder para"
            'mensagem' => 'O prestador de serviços foi advertido, pois cometeu um erro.',
            'mostrarmotivoadvertencia' => true,
            'rodape' => 'Mensagem enviada automaticamente',
            'assinatura' => "Atenciosamente, xpto funerarias"
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$this->url_complemento_template_email}/?grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        try {
            $I->assertEquals($retorno['estabelecimento']['estabelecimento'], $dados['estabelecimento']['estabelecimento']);
            // $I->assertEquals($retorno['responderpara'], $dados['responderpara']);
            $I->assertEquals($retorno['mensagem'], $dados['mensagem']);
            $I->assertEquals($retorno['mostrarmotivoadvertencia'], $dados['mostrarmotivoadvertencia']);
            $I->assertEquals($retorno['rodape'], $dados['rodape']);
            $I->assertEquals($retorno['assinatura'], $dados['assinatura']);
            $I->assertEquals($retorno['id_grupoempresarial'], $this->grupoempresarial_id);
            $I->assertEquals($retorno['tenant'], $this->tenant_numero);
            $I->canSeeInDatabase('crm.templatesemailadvertirprestador', [
                'estabelecimento' => $dados['estabelecimento']['estabelecimento'],
                // 'responder_para' => $dados['responderpara'],
                'mensagem' => $dados['mensagem'],
                'mostrar_motivo_advertencia' => $dados['mostrarmotivoadvertencia'],
                'rodape' => $dados['rodape'],
                'assinatura' => $dados['assinatura'],
                'id_grupoempresarial' => $this->grupoempresarial_id,
                'tenant' => $this->tenant_numero,
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.templatesemailadvertirprestador', [
                'templateemailadvertirprestador' => $retorno['templateemailadvertirprestador'],
                'tenant' => $this->tenant_numero
            ]);
        }
    }

    /**
     * Insere template de email para advertir prestador de serviços, com um estabelecimento que já possui configuração
     * @param FunctionalTester $I
     * @return void
     */
    public function createEstabelecimentoDuplicado(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        
        /* Monto dados da requisição */
        $dados = [
            'estabelecimento' => $estabelecimento,
            // 'responderpara' => 'xpto@blogspot.com',
            'mensagem' => 'O prestador de serviços foi advertido, pois cometeu um erro.',
            'mostrarmotivoadvertencia' => true,
            'rodape' => 'Mensagem enviada automaticamente',
            'assinatura' => "Atenciosamente, xpto funerarias"
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$this->url_complemento_template_email}/?grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);

        /* validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            if (isset($retorno['templateemailadvertirprestador'])){
                $I->deleteFromDatabase('crm.templatesemailadvertirprestador', [
                    'templateemailadvertirprestador' => $retorno['templateemailadvertirprestador'],
                    'tenant' => $this->tenant_numero
                ]);
            }
        }
    }

    /**
     * Atualiza template de email para advertir prestador de serviços
     * @param FunctionalTester $I
     * @return void
     */
    public function update(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);

        /* Monto dados da requisição */
        // $template['responderpara'] = 'xpto@blogspot.com';
        $template['mensagem'] = 'O prestador de serviços foi advertido, pois cometeu um erro.';
        $template['mostrarmotivoadvertencia'] = true;
        $template['rodape'] = "Mensagem enviada automaticamente";
        $template['assinatura'] = "Atenciosamente, xpto funerarias";

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('PUT', "/api/{$this->tenant}/{$this->url_complemento_template_email}/{$template['templateemailadvertirprestador']}?grupoempresarial={$this->grupoempresarial}", $template, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
            $I->canSeeInDatabase('crm.templatesemailadvertirprestador', [
                'templateemailadvertirprestador' => $template['templateemailadvertirprestador'],
                'estabelecimento' => $template['estabelecimento']['estabelecimento'],
                // 'responder_para' => $template['responderpara'],
                'mensagem' => $template['mensagem'],
                'mostrar_motivo_advertencia' => $template['mostrarmotivoadvertencia'],
                'rodape' => $template['rodape'],
                'assinatura' => $template['assinatura'],
                'id_grupoempresarial' => $this->grupoempresarial_id,
                'tenant' => $template['tenant'],
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.templatesemailadvertirprestador', [
                'templateemailadvertirprestador' => $template['templateemailadvertirprestador']
            ]);
        }
    }

    /**
     * Atualiza template de email para advertir prestador de serviços, com um estabelecimento que já possui configuração
     * @param FunctionalTester $I
     * @return void
     */
    public function updateEstabelecimentoDuplicado(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $empresa2 = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id,
            'codigo' => 'teste_0002'
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $estabelecimento2 = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa2,
            'codigo' => 'teste_0002'
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $template2 = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento2,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);

        /* Monto dados da requisição */
        // $template['responderpara'] = 'xpto@blogspot.com';
        $template['mensagem'] = 'O prestador de serviços foi advertido, pois cometeu um erro.';
        $template['mostrarmotivoadvertencia'] = true;
        $template['rodape'] = "Mensagem enviada automaticamente";
        $template['assinatura'] = "Atenciosamente, xpto funerarias";
        $template['estabelecimento'] = $estabelecimento2;

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('PUT', "/api/{$this->tenant}/{$this->url_complemento_template_email}/{$template['templateemailadvertirprestador']}?grupoempresarial={$this->grupoempresarial}", $template, [], [], null);

        /* validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.templatesemailadvertirprestador', [
                'templateemailadvertirprestador' => $template['templateemailadvertirprestador'],
                'tenant' => $this->tenant_numero
            ]);
        }
    }
    
    /**
     * Exclui template de email para advertir prestador de serviços
     * @param FunctionalTester $I
     * @return void
     */
    public function delete(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('DELETE', "/api/{$this->tenant}/{$this->url_complemento_template_email}/{$template['templateemailadvertirprestador']}?grupoempresarial={$this->grupoempresarial}", [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
            $I->cantSeeInDatabase('crm.templatesemailadvertirprestador', [
                'templateemailadvertirprestador' => $template['templateemailadvertirprestador'],
                'id_grupoempresarial' => $this->grupoempresarial_id,
                'tenant' => $template['tenant'],
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {}
    }

    /**
     * Busca um template de email para advertir prestador de serviços
     * @param FunctionalTester $I
     * @return void
     */
    public function get(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$this->url_complemento_template_email}/{$template['templateemailadvertirprestador']}?grupoempresarial={$this->grupoempresarial}", [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
            $I->assertEquals($retorno['estabelecimento']['estabelecimento'], $template['estabelecimento']['estabelecimento']);
            // $I->assertEquals($retorno['responderpara'], $template['responderpara']);
            $I->assertEquals($retorno['mensagem'], $template['mensagem']);
            $I->assertEquals($retorno['mostrarmotivoadvertencia'], $template['mostrarmotivoadvertencia']);
            $I->assertEquals($retorno['rodape'], $template['rodape']);
            $I->assertEquals($retorno['assinatura'], $template['assinatura']);
            $I->assertEquals($retorno['id_grupoempresarial'], $this->grupoempresarial_id);
            $I->assertEquals($retorno['tenant'], $this->tenant_numero);
        } catch (\Exception $e) {
            throw $e;
        } finally {}
    }

    /**
     * Testa busca de todos os templates de email para advertir prestador de serviços. Será utilizada para todos os testes de filtros.
     */
    private function _getAll(FunctionalTester $I, $filtros = [], $dadosEsperados = []){
        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$this->url_complemento_template_email}/?" . http_build_query($filtros), [], [], [], null);
        
        /* Validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(count($dadosEsperados), count($retorno));

        for ($i=0; $i < count($dadosEsperados); $i++) { 
            $dadoEsperado = $dadosEsperados[$i];

            //Uso um array_values somente para reorganizar os ID's, já que o array_filter nem sempre retorna o id 0 mesmo contendo um valor.
            $arrRetornoReq = array_values( array_filter($retorno, function($_dadoRed) use ($dadoEsperado) {
                return ($dadoEsperado['templateemailadvertirprestador'] == $_dadoRed['templateemailadvertirprestador']);
            }) );

            $I->assertEquals(1, count($arrRetornoReq));
            $dadoReq = $arrRetornoReq[0];

            $I->assertEquals($dadoEsperado['estabelecimento']['estabelecimento'], $dadoReq['estabelecimento']['estabelecimento']);
        }

        return $retorno;
    }

    /**
     * Busca todos os templates de email para advertir prestador de serviços
     * @param FunctionalTester $I
     * @return void
     */
    public function getAll(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $empresa2 = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id,
            'codigo' => 'teste_0002'
        ]);
        $empresa3 = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id,
            'codigo' => 'teste_0003'
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $estabelecimento2 = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa2,
            'codigo' => 'teste_0002'
        ]);
        $estabelecimento3 = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa3,
            'codigo' => 'teste_0003'
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $template2 = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento2,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $template3 = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento3,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);

        /* Monto dados esperados */
        $dados = [];
        $dados[] = $template;
        $dados[] = $template2;
        $dados[] = $template3;

        /* Monto filtros */
        $filtros = [];
        $filtros[] = [
            'grupoempresarial' => $this->grupoempresarial
        ];

        /* execução da funcionalidade */
        $retorno = $this->_getAll($I, $filtros, $dados);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
        } catch (\Exception $e) {
            throw $e;
        } finally {}
    }

    /**
     * Busca todos os templates de email para advertir prestador de serviços, filtrando por estabelecimento
     * @param FunctionalTester $I
     * @return void
     */
    public function getAllFiltroEstabelecimento(FunctionalTester $I){
        /* Mock de banco */
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $empresa2 = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id,
            'codigo' => 'teste_0002'
        ]);
        $empresa3 = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id,
            'codigo' => 'teste_0003'
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);
        $estabelecimento2 = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa2,
            'codigo' => 'teste_0002'
        ]);
        $estabelecimento3 = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa3,
            'codigo' => 'teste_0003'
        ]);
        $template = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $template2 = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento2,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $template3 = $I->haveInDatabaseTemplateEmailAdvertirPrestadorServico($I, [
            'estabelecimento' => $estabelecimento3,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);

        /* Monto dados esperados */
        $dados = [];
        $dados[] = $template2;

        /* Monto filtros */
        $filtros = [
            'estabelecimento' => $template2['estabelecimento']['estabelecimento'],
            'grupoempresarial' => $this->grupoempresarial,
        ];

        /* execução da funcionalidade */
        $retorno = $this->_getAll($I, $filtros, $dados);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
        } catch (\Exception $e) {
            throw $e;
        } finally {}
    }
}