<?php

use Codeception\Util\HttpCode;

/**
 * @todo  : melhorar teste de capitulos quanto ao pai e testar ordem
 * especificar testes: criação só com campo obrigatorio, criação com todos campos, erro ao não enviar campos obrigatorios
 */
class TemplatespropostasCest {
  
  private $url_base = '/api/gednasajon/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $cliente = 'f6309917-d2d0-4751-ba0b-44ffc2c8c9cd'; // origem: _data/dump.sql
  private $composicao = '96c93b1b-4250-4af0-af3c-9278457f8ff2'; // origem: _data/dump.sql
  private $grupoempresarial = 'FMA';
  
  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('rodrigodirk@nasajon.com.br');
  }
    
  /**
   * Salva temporariamente no banco um capitulo
   * @param FunctionalTester $I
   * @return type
   */
  private function haveInDatabaseCapitulo (FunctionalTester $I, $template_id, $pai = null){
    $capitulo = [
        "templatepropostacapitulo" => $I->generateUuidV4(),
        "nome" => "Novo",
        "numero" => '1',
        "pai" => $pai,
        "tenant" => $this->tenant_numero,
        "templateproposta" => $template_id,
        'created_by' => '{"nome":"usuario"}',
        'updated_by' => '{"nome":"usuario"}',
        'created_at' => date('Y-m-d'),
        'updated_at' => date('Y-m-d'),
        'grupoempresarial' => '95cd450c-30c5-4172-af2b-cdece39073bf',

    ];    
    $I->haveInDatabase('crm.templatespropostascapitulos', $capitulo);
    
    /* não precisam ser usados no metodo de teste */
    unset($capitulo['updated_at']);
    unset($capitulo['updated_by']);
    unset($capitulo['created_at']);
    unset($capitulo['created_by']);
    
    return $capitulo;
  }
  
    /**
   * Salva temporariamente no banco uma composição de ligação da tabela TemplatesPropostasCapitulosComposicoes
   * @param FunctionalTester $I
   * @param type $capitulo_id
   * @return type
   */
  private function haveInDatabaseTemplatePropostaCapituloComposicao(FunctionalTester $I, $capitulo_id){
    $templatePropostaCapituloComposicao = [
        "templatepropostacapitulocomposicao" => $I->generateUuidV4(),
        "composicao" => $this->composicao,
        "templatepropostacapitulo" => $capitulo_id,
        "tenant" => $this->tenant_numero,
        'created_by' => '{"nome":"usuario"}',
        'created_at' => date('Y-m-d'),
        'grupoempresarial' => '95cd450c-30c5-4172-af2b-cdece39073bf',
    ];
    $I->haveInDatabase('crm.templatespropostascapituloscomposicoes', $templatePropostaCapituloComposicao);
    
    /* não precisam ser usados no metodo de teste */
    unset($templatePropostaCapituloComposicao['created_at']);
    unset($templatePropostaCapituloComposicao['created_by']);
    
    return $templatePropostaCapituloComposicao;
  }

  /**
   * Formata os dados do templateproposta para procurar na tabela
   */
  private function getTemplatepropostaDados($template){
    return [
      'templateproposta' => $template['templateproposta'],
      'nome' => $template['nome'],
      'templatepropostagrupo' => $template['templatepropostagrupo']['templatepropostagrupo'],
      'tenant' => $template['tenant']
    ];
  }

  /**
   * Formata os dados do templatepropostagrupo para procurar na tabela
   */
  private function getTemplatepropostagrupoDados($template){
    return [
      'templatepropostagrupo' => $template['templatepropostagrupo'],
      'nome' => $template['nome'],
      'cliente' => $template['cliente']['cliente'],
      'tenant' => $template['tenant']
    ];
  }
  
  /**
   * Testa código HTTP de retorno e se foi criado no banco
   * @param FunctionalTester $I
   */
  public function adicionaGrupo(FunctionalTester $I) {

    /* execução da funcionalidade */
    $url = $this->url_base . $this->cliente . '/templatespropostasgrupos/';
    $grupo = [
        "nome" => 'Grupo',
        "tenant" => $this->tenant_numero,
        "cliente" => $this->cliente,
        "email" => 'email@email.com.br'
    ];
    $grupo_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $grupo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $grupo['templatepropostagrupo'] = $grupo_criado['templatepropostagrupo'];
    $I->canSeeInDatabase('crm.templatespropostasgrupos', $grupo);

    /* remove grupo criado */
    $I->deleteFromDatabase('crm.templatespropostasgrupos', ['templatepropostagrupo' => $grupo_criado['templatepropostagrupo']]);
  }
  
  /**
   * Testa código HTTP de retorno e se foi criado no banco
   * @param FunctionalTester $I
   */
  public function adicionaTemplate(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $tipodocumento = $I->haveInDatabaseDocumento($I);
    $tipodocumento2 = $I->haveInDatabaseDocumento($I);
    $templatespropostasdocumentos = [
        [
            'copiaautenticada' => true,
            'original' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            "naoexibiremrelatorios" => true,
            'tipodocumento' => $tipodocumento
        ],
        [
            'copiaautenticada' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => false,
            "naoexibiremrelatorios" => true,
            'tipodocumento' => $tipodocumento2
        ]
    ];
    
    /* execução da funcionalidade */    
    $url = $this->url_base . $grupo['templatepropostagrupo'] . '/templatespropostas/';
    $template = [
        "nome" => 'Template 1',
        "tenant" => $this->tenant_numero,
        "templatepropostagrupo" =>  $grupo['templatepropostagrupo'],
        "templatespropostasdocumentos" => $templatespropostasdocumentos
    ];
    $template_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $template, [], [], null);

    $I->canSeeResponseCodeIs(HttpCode::CREATED);

    try {
        /* validação do resultado */
        $template['templateproposta'] = $template_criado['templateproposta'];
        unset($template['templatespropostasdocumentos']);
        $I->canSeeInDatabase('crm.templatespropostas', $template);
        $I->canSeeInDatabase('crm.templatespropostasdocumentos', [
            'templateproposta' => $template_criado['templateproposta'],
            'copiaautenticada' => true,
            'original' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            "naoexibiremrelatorios" => true,
            'tipodocumento' => $tipodocumento['tipodocumento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->canSeeInDatabase('crm.templatespropostasdocumentos', [
            'templateproposta' => $template_criado['templateproposta'],
            'copiaautenticada' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => false,
            "naoexibiremrelatorios" => true,
            'tipodocumento' => $tipodocumento2['tipodocumento'],
            'tenant' => $this->tenant_numero
        ]);
    } catch (\Exception $e) {
        throw $e;
    } finally {
        /* remove template criado */
        $I->deleteFromDatabase('crm.templatespropostasdocumentos', [
            'templateproposta' => $template_criado['templateproposta'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.templatespropostas', ['templateproposta' => $template_criado['templateproposta']]);
    }
    
  }
  /**
   * Cria um template sem documentos
   * @param FunctionalTester $I
   */
  public function adicionaTemplateSemDocumento(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    
    /* execução da funcionalidade */    
    $url = $this->url_base . $grupo['templatepropostagrupo'] . '/templatespropostas/';
    $template = [
        "nome" => 'Template 1',
        "tenant" => $this->tenant_numero,
        "templatepropostagrupo" =>  $grupo['templatepropostagrupo']
    ];
    $template_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $template, [], [], null);

    $I->canSeeResponseCodeIs(HttpCode::CREATED);

    try {
        /* validação do resultado */
        $template['templateproposta'] = $template_criado['templateproposta'];
        $I->canSeeInDatabase('crm.templatespropostas', $template);
    } catch (\Exception $e) {
        throw $e;
    } finally {
        /* remove template criado */
        $I->deleteFromDatabase('crm.templatespropostas', ['templateproposta' => $template_criado['templateproposta']]);
    }
    
  }
  
  /**
   * Testa código HTTP de retorno e se foi editado no banco
   * @param FunctionalTester $I
   */
  public function editaTemplate(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $tipodocumento = $I->haveInDatabaseDocumento($I);
    $tipodocumento2 = $I->haveInDatabaseDocumento($I);
    $tipodocumento3 = $I->haveInDatabaseDocumento($I);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo, [
        'templatespropostasdocumentos' => [
            [
                'copiaautenticada' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                'tipodocumento' => $tipodocumento
            ],
            [
                'copiaautenticada' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                'tipodocumento' => $tipodocumento3
            ],
        ]
    ]);
    $template['templatespropostasdocumentos'][] = [
        'templateproposta' => $template['templateproposta'],
        'copiasimples' => true,
        'original' => true,
        "permiteenvioemail" => true,
        "pedirinformacoesadicionais" => true,
        "naoexibiremrelatorios" => true,
        'tipodocumento' => $tipodocumento2
    ];

    //Removo o documento com o tipo de documento 3 para validar que vai ser excluído
    $docInseridoPreviamentoParaExcluir = $template['templatespropostasdocumentos'][1]['templatepropostadocumento'];
    unset($template['templatespropostasdocumentos'][1]);
 
    /* execução da funcionalidade */   
    $url = $this->url_base . $grupo['templatepropostagrupo'] . '/templatespropostas/'.$template['templateproposta'];
    $template['nome'] = "Editado";
    $I->sendRaw('PUT', $url .  '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $template, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    try {
        $docInseridoPreviamento = $template['templatespropostasdocumentos'][0]['templatepropostadocumento'];
        $template = $this->getTemplatepropostaDados($template);
        $templateCheck = $template;
        unset($templateCheck['templatespropostasdocumentos']);
        
        $I->canSeeInDatabase('crm.templatespropostas', $template);
        //Valido se o documento foi atualizado
        $I->canSeeInDatabase('crm.templatespropostasdocumentos', [
            'templatepropostadocumento' => $docInseridoPreviamento,
            'templateproposta' => $template['templateproposta'],
            'copiaautenticada' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            'tipodocumento' => $tipodocumento['tipodocumento'],
            'tenant' => $this->tenant_numero
        ]);
        //Valido se o documento foi inserido
        $I->canSeeInDatabase('crm.templatespropostasdocumentos', [
            'templateproposta' => $template['templateproposta'],
            'copiasimples' => true,
            'original' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            "naoexibiremrelatorios" => true,
            'tipodocumento' => $tipodocumento2['tipodocumento'],
            'tenant' => $this->tenant_numero
        ]);
        //Valido se o documento foi excluído
        $I->cantSeeInDatabase('crm.templatespropostasdocumentos', [
            'templatepropostadocumento' => $docInseridoPreviamentoParaExcluir,
            'tenant' => $this->tenant_numero
        ]);
    } catch (\Exception $e) {
        throw $e;
    } finally {
        /* remove template criado */
        $I->deleteFromDatabase('crm.templatespropostasdocumentos', [
            'templateproposta' => $template['templateproposta'],
            'tenant' => $this->tenant_numero
        ]);
    }
  }

  /**
   * Testa código HTTP de retorno e testa edição do documento do template
   * @param FunctionalTester $I
   */
  public function editaDocumentoDoTemplate(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $tipodocumento = $I->haveInDatabaseDocumento($I);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo, [
        'templatespropostasdocumentos' => [
            [
                'copiasimples' => true,
                'copiaautenticada' => true,
                'original' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                "naoexibiremrelatorios" => true,
                'tipodocumento' => $tipodocumento
            ]
        ]
    ]);

    /* execução da funcionalidade */
    $template['templatespropostasdocumentos'][0]['copiasimples'] = false;
    $template['templatespropostasdocumentos'][0]['copiaautenticada'] = false;
    $template['templatespropostasdocumentos'][0]['permiteenvioemail'] = false;
    $template['templatespropostasdocumentos'][0]['pedirinformacoesadicionais'] = false;
    $template['templatespropostasdocumentos'][0]['naoexibiremrelatorios'] = true;
 
    $url = $this->url_base . $grupo['templatepropostagrupo'] . '/templatespropostas/' . $template['templateproposta'];
    $I->sendRaw('PUT', $url .  '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $template, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.templatespropostasdocumentos', [
        'templatepropostadocumento' => $template['templatespropostasdocumentos'][0]['templatepropostadocumento'],
        'templateproposta' => $template['templateproposta'],
        'copiasimples' => false,
        'copiaautenticada' => false,
        "naoexibiremrelatorios" => true,
        "permiteenvioemail" => false,
        "pedirinformacoesadicionais" => false,
        'tenant' => $this->tenant_numero
    ]);

  }

  /**
   * Testa código HTTP de retorno e testa retorno do documento do template
   * @param FunctionalTester $I
   */
  public function retornaDocumentoDoTemplate(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $tipodocumento = $I->haveInDatabaseDocumento($I);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo, [
        'templatespropostasdocumentos' => [
            [
                'copiaautenticada' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                'tipodocumento' => $tipodocumento
            ]
        ]
    ]);
 
    /* execução da funcionalidade */   
    $docRetornado = $I->sendRaw('GET', '/api/gednasajon/' . $template['templateproposta'] . '/templatespropostasdocumentos/' . $template['templatespropostasdocumentos'][0]['templatepropostadocumento'] . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

  }

  /**
   * Testa código HTTP de retorno e testa retorno dos documentos do template
   * @param FunctionalTester $I
   */
  public function listaDocumentosDoTemplate(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $tipodocumento = $I->haveInDatabaseDocumento($I);
    $tipodocumento2 = $I->haveInDatabaseDocumento($I);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo, [
        'templatespropostasdocumentos' => [
            [
                'copiaautenticada' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                'tipodocumento' => $tipodocumento
            ],
            [
                'copiaautenticada' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                'tipodocumento' => $tipodocumento2
            ],
        ]
    ]);

    // Retornando a quantidade de documentos para o template
    $countAtual = $I->grabNumRecords('crm.templatespropostasdocumentos', ['templateproposta' => $template['templateproposta']]);
 
    /* execução da funcionalidade */   
    $listaDocs = $I->sendRaw('GET', '/api/gednasajon/' . $template['templateproposta'] . '/templatespropostasdocumentos/' . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $listaDocs);

  }

  /**
   * Edita um template que não possui documentos
   * @param FunctionalTester $I
   */
  public function editaTemplateSemDocumento(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
 
    /* execução da funcionalidade */   
    $url = $this->url_base . $grupo['templatepropostagrupo'] . '/templatespropostas/'.$template['templateproposta'];
    $template['nome'] = "Editado";
    $I->sendRaw('PUT', $url .  '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $template, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    try {
        $template = $this->getTemplatepropostaDados($template);
        $I->canSeeInDatabase('crm.templatespropostas', $template);
    } catch (\Exception $e) {
        throw $e;
    }
  }
  
  /**
   * Testa código HTTP de retorno e se foi criado no banco
   * @param FunctionalTester $I
   */
  public function adicionaCapitulo(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    
    /* execução da funcionalidade */   
    $url = $this->url_base . $template['templateproposta'] . '/templatespropostascapitulos/';
    $capitulo = [
        "nome" => "Novo",
        "numero" => '1',
        "tenant" => $this->tenant_numero,
        "templateproposta" => $template['templateproposta']
    ];
    $capitulo_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $capitulo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $capitulo['templatepropostacapitulo'] = $capitulo_criado['templatepropostacapitulo'];
    $I->canSeeInDatabase('crm.templatespropostascapitulos', $capitulo);

    /* remove capitulo criado */
    $I->deleteFromDatabase('crm.templatespropostascapitulos', ['templatepropostacapitulo' => $capitulo_criado['templatepropostacapitulo']]);
  }
  
  /**
   * Testa código HTTP de retorno e se foi editado no banco
   * @param FunctionalTester $I
   */
  public function editaCapitulo(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $capitulo = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);
    
    /* execução da funcionalidade */   
    $url = $this->url_base . $template['templateproposta'] . '/templatespropostascapitulos/'.$capitulo['templatepropostacapitulo'];
    $capitulo['nome'] = "Editado";
    $I->sendRaw('PUT', $url .  '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $capitulo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.templatespropostascapitulos', $capitulo);
  }
  
  /**
   * Testa código HTTP de retorno e se foi criado no banco
   * @param FunctionalTester $I
   */
  public function adicionaSubcapitulo(FunctionalTester $I) {

    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $pai = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);
    
    /* execução da funcionalidade */   
    $url = $this->url_base . $template['templateproposta'] . '/templatespropostascapitulos/';
    $subcapitulo = [
        "nome" => "Novo",
        "numero" => '1.1',
        "tenant" => $this->tenant_numero,
        "pai" => $pai['templatepropostacapitulo'],
        "templateproposta" => $template['templateproposta']
    ];
    $subcapitulo_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $subcapitulo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $subcapitulo['templatepropostacapitulo'] = $subcapitulo_criado['templatepropostacapitulo'];
    $I->canSeeInDatabase('crm.templatespropostascapitulos', $subcapitulo);
    $I->canSeeInDatabase('crm.templatespropostascapitulos', ['templatepropostacapitulo' => $subcapitulo['pai'], 'possuifilho'=>true]);

    /* remove subcapitulo criado */
    $I->deleteFromDatabase('crm.templatespropostascapitulos', ['templatepropostacapitulo' => $subcapitulo_criado['templatepropostacapitulo']]);
  }
  
   /**
   * Testa código HTTP de retorno e se foi editado no banco
   * @param FunctionalTester $I
   */
  public function editaSubcapitulo(FunctionalTester $I) {
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $pai = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);
    $subcapitulo = $this->haveInDatabaseCapitulo($I, $template['templateproposta'], $pai['templatepropostacapitulo']);
    
    /* execução da funcionalidade */   
    $url = $this->url_base . $template['templateproposta']  . '/templatespropostascapitulos/'.$subcapitulo['templatepropostacapitulo'];
    $subcapitulo['nome'] = 'Editado';
    $I->sendRaw('PUT', $url .  '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $subcapitulo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.templatespropostascapitulos', $subcapitulo);
  }
  
  /**
   * Testa código HTTP de retorno e se foi removido no banco
   * @param FunctionalTester $I
   */
  public function removeSubcapitulo(FunctionalTester $I) {
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $pai = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);
    $subcapitulo = $this->haveInDatabaseCapitulo($I, $template['templateproposta'], $pai['templatepropostacapitulo']);

    /* execução da funcionalidade */ 
    $url = $this->url_base . $template['templateproposta'] . '/templatespropostascapitulos/'.$subcapitulo['templatepropostacapitulo'];
    $I->sendRaw('DELETE', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.templatespropostascapitulos', ['templatepropostacapitulo' => $subcapitulo['templatepropostacapitulo']]);
  }
  
  /**
   * Testa código HTTP de retorno e se foi criado no banco
   * @param FunctionalTester $I
   */
  public function adicionaTemplatePropostaCapituloComposicao(FunctionalTester $I) {
    
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $capitulo = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);

    /* execução da funcionalidade */   
    $url = $this->url_base . $capitulo['templatepropostacapitulo'] . '/templatespropostascapituloscomposicoes/';
    $templatePropostaCapituloComposicao = [
        "templatepropostacapitulo" => $capitulo['templatepropostacapitulo'],
        "composicao" => ['composicao'=>$this->composicao],
        "tenant" => $this->tenant_numero,
    ];
    $templatePropostaCapituloComposicao_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $templatePropostaCapituloComposicao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $templatePropostaCapituloComposicao['templatepropostacapitulocomposicao'] = $templatePropostaCapituloComposicao_criado['templatepropostacomposicao'];
    $templatePropostaCapituloComposicao['composicao'] = $this->composicao;
    $I->canSeeInDatabase('crm.templatespropostascapituloscomposicoes', $templatePropostaCapituloComposicao);
    
    // /* remove composicao criada */
    $I->deleteFromDatabase('crm.templatespropostascomposicoesfamilias', ['templatepropostacapitulocomposicao' => $templatePropostaCapituloComposicao['templatepropostacapitulocomposicao']]);
    $I->deleteFromDatabase('crm.templatespropostascomposicoesfuncoes', ['templatepropostacapitulocomposicao' => $templatePropostaCapituloComposicao['templatepropostacapitulocomposicao']]);
    $I->deleteFromDatabase('crm.templatespropostascapituloscomposicoes', ['templatepropostacapitulocomposicao' => $templatePropostaCapituloComposicao['templatepropostacapitulocomposicao']]);
  }
  
  /**
   * Testa código HTTP de retorno e se foi removido no banco
   * @param FunctionalTester $I
   */
  public function removeComposicao(FunctionalTester $I) {
    
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $capitulo = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);
    $templatePropostaCapituloComposicao= $this->haveInDatabaseTemplatePropostaCapituloComposicao($I, $capitulo['templatepropostacapitulo']);

    /* execução da funcionalidade */   
    $url = $this->url_base .  $capitulo['templatepropostacapitulo'] . '/templatespropostascapituloscomposicoes/'.$templatePropostaCapituloComposicao['templatepropostacapitulocomposicao'];
    $I->sendRaw('DELETE', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.templatespropostascapituloscomposicoes', ['templatepropostacapitulocomposicao' => $templatePropostaCapituloComposicao['templatepropostacapitulocomposicao']]);
  }
  
  /**
   * Testa código HTTP de retorno e se foi removido no banco
   * @param FunctionalTester $I
   */
  public function removeCapitulo(FunctionalTester $I) {
    
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo);
    $capitulo = $this->haveInDatabaseCapitulo($I, $template['templateproposta']);

    /* execução da funcionalidade */   
    $url = $this->url_base . $template['templateproposta'] . '/templatespropostascapitulos/'.$capitulo['templatepropostacapitulo'];
    $I->sendRaw('DELETE', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.templatespropostascapitulos', ['templatepropostacapitulo' => $capitulo['templatepropostacapitulo']]);
  }
  
  /**
   * Testa código HTTP de retorno e se foi removido no banco
   * @param FunctionalTester $I
   */
  public function removeTemplate(FunctionalTester $I) {
    
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
    $tipodocumento = $I->haveInDatabaseDocumento($I);
    $template = $I->haveInDatabaseTemplateproposta($I, $grupo, [
        'templatespropostasdocumentos' => [
            [
                'copiasimples' => true,
                "permiteenvioemail" => true,
                "pedirinformacoesadicionais" => true,
                'tipodocumento' => $tipodocumento
            ]
        ]
    ]);

    /* execução da funcionalidade */   
    $url = $this->url_base . $grupo['templatepropostagrupo'] . '/templatespropostas/'.$template['templateproposta'];
    $I->sendRaw('DELETE', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.templatespropostas', ['templateproposta' => $template['templateproposta']]);
    $I->cantSeeInDatabase('crm.templatespropostasdocumentos', [
        'templateproposta' => $template['templateproposta'],
        'tenant' => $this->tenant_numero
    ]);
  }

  /**
   * Testa código HTTP de retorno e se foi editado no banco
   * @param FunctionalTester $I
   */
  public function editaGrupo(FunctionalTester $I) {
    
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);

    /* execução da funcionalidade */   
    $url = $this->url_base . $cliente['cliente'] . '/templatespropostasgrupos/'. $grupo['templatepropostagrupo'];
    $grupo['nome'] = "Editado";
    $grupo['email'] = "emaileditado@email.com.br";
    $I->sendRaw('PUT', $url .  '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $grupo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $grupo = $this->getTemplatepropostagrupoDados($grupo);
    $I->canSeeInDatabase('crm.templatespropostasgrupos', $grupo);
  }
  
  /**
   * Testa código HTTP de retorno e se foi removido no banco
   * @param FunctionalTester $I
   */
  public function removeGrupo(FunctionalTester $I) {
    
    /* set up */
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $grupo = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);

    /* execução da funcionalidade */   
    $url = $this->url_base . $cliente['cliente'] . '/templatespropostasgrupos/'. $grupo['templatepropostagrupo'];
    $I->sendRaw('DELETE', $url . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.templatespropostasgrupos', ['templatepropostagrupo' => $grupo['templatepropostagrupo']]);
  }
  
}
