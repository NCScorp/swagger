<?php
/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor {
    use _generated\FunctionalTesterActions;

    private $tenant_numero = '47';
    private $tenant = 'gednasajon';
    private $tarifaconcessionaria = '9745fc74-900f-42c9-9eef-b56bb186f252';

   /**
    * Define custom actions here
    * @todo remover UUIDs vindo de dump.sql
    */

    public function haveInDatabaseInformes(FunctionalTester $I, $trabalhador) {
        $informe = [
          'informerendimento' => $I->generateUuidV4(),
          'trabalhador' => $trabalhador,
          'ano' => '1991',
          'substituicao' => false,
          'caminhodocumento' => 'diretorio_informes',
          'tenant' => $this->tenant_numero,
          'created_at' => date('Y-m-d'),
          'created_by' => '{"nome":"usuario"}',
          'updated_by' => '{"nome":"usuario"}',
          'updated_at' => date('Y-m-d'),
          'tenant' => $this->tenant_numero,
          'anocalendario' => '1991',
          'anoexercicio' => '1992'

        ];
        $I->haveInDatabase('meurh.informesrendimentos', $informe);
        unset($informe['created_at']);
        unset($informe['created_by']);
        unset($informe['updated_at']);
        unset($informe['updated_by']);
        return $informe;
    }

    public function haveInDatabaseRecibospagamentos(FunctionalTester $I, $trabalhador) {
        $recibospagamentos = [
          'recibopagamento' => $I->generateUuidV4(),
          'trabalhador' => $trabalhador,
          'mes' => '1',
          'ano' => '1991',
          'substituicao' => false,
          'cargo' => 'ae09d570-d910-457f-8ecf-4fe6204f61bd',
          'nivelcargo' => '932fc1cd-16b3-494e-9701-3dbb8d1e73b7',
          'calculo' => 'Ad13',
          'caminhodocumento' => 'diretorio_recibospagamentos',
          'liquido' => '1345',
          'tenant' => $this->tenant_numero,
          'created_at' => date('Y-m-d'),
          'created_by' => '{"nome":"usuario"}',
          'updated_by' => '{"nome":"usuario"}',
          'updated_at' => date('Y-m-d'),
          'datapagamento' => date('Y-m-d'),
          'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('meurh.recibospagamentos', $recibospagamentos);
        unset($recibospagamentos['created_at']);
        unset($recibospagamentos['created_by']);
        unset($recibospagamentos['updated_at']);
        unset($recibospagamentos['updated_by']);
        return $recibospagamentos;
    }
    /**
    * Dados do dump.sql
    * @todo deixar de usar dump
     */
    public function haveInDatabaseTrabalhador(FunctionalTester $I, $tenant, $estabelecimento, $conta, $nivelcargo, $codigo) {
        $trabalhador = [
          'trabalhador' => $I->generateUuidV4(),  
          'nome' => 'A teste',  
          'tipo' => 2,
          'agencia' => '43826037-c40b-462b-ac9e-6c4cce496177',
          'codigo' => $codigo,  
          'nivelcargo' => $nivelcargo, 
          'identificacaonasajon' => $conta, 
          'tenant' => $tenant, 
          'lastupdate' => date('Y-m-d'), 
          'estabelecimento' => $estabelecimento,
          'inicioperiodoaquisitivoferias' => '2017-01-01'
        ];
        $I->haveInDatabase('persona.trabalhadores', $trabalhador);
        unset($trabalhador['lastupdate']);
        return $trabalhador;
    }
    public function haveInDatabaseSolicitacoessalariossobdemanda(FunctionalTester $I, $tenant, $trabalhador, $estabelecimento, $codigo, $provedorestabelecimento = true) {
        $solicitacaosalariosobdemanda = [
          'solicitacao' => $I->generateUuidV4(),  
          'trabalhador' => $trabalhador,
          'estabelecimento' => $estabelecimento,
          'tiposolicitacao' => 8,
          'situacao' => 1,
          'valor' => 500,
          'codigo' => $codigo,
          'tenant' => $tenant, 
          'lastupdate' => date('Y-m-d'), 
          'provedorestabelecimento' => $provedorestabelecimento
        ];
        $I->haveInDatabase('meurh.solicitacoessalariossobdemanda', $solicitacaosalariosobdemanda);
        unset($solicitacaosalariosobdemanda['lastupdate']);
        return $solicitacaosalariosobdemanda;
    }
    public function haveInDatabaseValoressolicitacoesporperiodo(FunctionalTester $I, $tenant, $estabelecimento, $trabalhador) {
      $valorsolicitacaoporperiodo = [
        'valorsolicitacaoporperiodo' => $I->generateUuidV4(),
        'estabelecimento' => $estabelecimento,
        'trabalhador' => $trabalhador,
        'valoraprovado' => 100,
        'valorpendente' => 50,
        'valordisponivel' => 1400,
        'valorbloqueado' => 15,
        'mes' => date("n"),
        'ano' => date("Y"),
        'tenant' => $tenant,
        'created_at' => date('Y-m-d'),
        'created_by' => '{"nome":"usuario"}',
      ];
      $I->haveInDatabase('meurh.valoressolicitacoesporperiodo', $valorsolicitacaoporperiodo);
      unset($valorsolicitacaoporperiodo['created_at']);
      unset($valorsolicitacaoporperiodo['created_by']);
      return $valorsolicitacaoporperiodo;
    }

    public function haveInDatabaseSolicitacoesFaltas(FunctionalTester $I, $trabalhador, $estabelecimento) {
      $solicitacaofalta = [
        'solicitacao' => $I->generateUuidV4(),
        'trabalhador' => $trabalhador,
        'estabelecimento' => $estabelecimento,
        'tiposolicitacao' => 6,
        'situacao' => 0,
        'tenant' => $this->tenant_numero,
        'codigo' => '1',
        'datas' => '{2020-07-26}',
        'justificada' => true,
        'justificativa' => 'Visita médico',
        'tipojustificativa' => 1
      ];
      $I->haveInDatabase('meurh.solicitacoesfaltas', $solicitacaofalta);
      return $solicitacaofalta;
    }


    public function haveInDatabaseSolicitacoesFerias(FunctionalTester $I, $trabalhador, $estabelecimento) {
      $solicitacaoferias = [
            'solicitacao' => $I->generateUuidV4(),
            'trabalhador' => $trabalhador,
            'estabelecimento' => $estabelecimento,
            'tiposolicitacao' => 7,
            'situacao' => -1,
            'codigo' => 13,
            'justificativa' => null,
            'observacao' => null,
            'origem' => 1,
            'tenant' => 47,
            'datainiciogozo' => '2022-02-10',
            'datafimgozo' => '2022-02-25',
            'datainicioperiodoaquisitivo' => '2017-01-01',
            'datafimperiodoaquisitivo' => '2017-12-31',
            'temabonopecuniario' => null,
            'diasvendidos' => 0,
            'diasferiascoletivas' => 30,
            'adto13nasferias' => null,
            'dataaviso' => '2022-01-10'
      ];
      $I->haveInDatabase('meurh.solicitacoesferias', $solicitacaoferias);
      return $solicitacaoferias;
    }
    
    public function haveInDatabaseSolicitacoesalteracoesenderecos(FunctionalTester $I, $trabalhador, $estabelecimento) {
      $solicitacaoalteracoesenderecos = [
        'solicitacao' => $I->generateUuidV4(),
        'trabalhador' => $trabalhador,
        'estabelecimento' => $estabelecimento,
        'tiposolicitacao' => 5,
        'situacao' => 1,
        'tenant' => $this->tenant_numero,
        'codigo' => '1',
        'paisresidencia' => '1058',
        'municipioresidencia' => '3304409',
        'tipologradouro' => 'R',
        'logradouro' =>  'Avenida Rio Branco',
        'complemento' => 'Casa 1',
        'numero' =>  '45',
        'bairro' =>  'Centro',
        'cep' =>  '20090003',
        'dddtel' => '21',
        'telefone' =>  '22223333',
        'dddcel' => '11',
        'celular' =>  '999998888',
        'email' =>  'usuariosolicitacoes@nasajon.com.br',
        'justificativa' =>  'Mudança de endereço',
        'observacao' =>  'Observação teste'
      ];
      $I->haveInDatabase('meurh.solicitacoesalteracoesenderecos', $solicitacaoalteracoesenderecos);
      return $solicitacaoalteracoesenderecos;
    }

    public function haveInDatabaseSolicitacoesAlteracaoVTs(FunctionalTester $I, $trabalhador, $estabelecimento) {
      $solicitacao = [
        'solicitacao' => $I->generateUuidV4(),
        'trabalhador' => $trabalhador,
        'estabelecimento' => $estabelecimento,
        'tiposolicitacao' => 4,
        'situacao' => 0,
        'tenant' => $this->tenant_numero,
        'codigo' => '1'
      ];
      $I->haveInDatabase('meurh.solicitacoes', $solicitacao);

      // Incluir a tarifa da solicitação de alteração do VT
      $solicitacaoTarifa1 = [
        'solicitacaoalteracaovttarifa' => $I->generateUuidV4(),
        'solicitacao' => $solicitacao['solicitacao'],
        'tarifaconcessionariavt' => $this->tarifaconcessionaria,
        'quantidade' => 2,
        'tenant' => $this->tenant_numero
      ];
      $I->haveInDatabase('meurh.solicitacoesalteracoesvtstarifas', $solicitacaoTarifa1);

      // Incluir outra tarifa da solicitação de alteração do VT
      $solicitacaoTarifa2 = [
        'solicitacaoalteracaovttarifa' => $I->generateUuidV4(),
        'solicitacao' => $solicitacao['solicitacao'],
        'tarifaconcessionariavt' => $this->tarifaconcessionaria,
        'quantidade' => 2,
        'tenant' => $this->tenant_numero
      ];
      $I->haveInDatabase('meurh.solicitacoesalteracoesvtstarifas', $solicitacaoTarifa2);

      $solicitacao['solicitacoesalteracoesvtstarifas'] = array($solicitacaoTarifa1, $solicitacaoTarifa2);

      return $solicitacao;
    }

    public function getDadosBasicosSolicitacao($solicitacao){
      unset($solicitacao['lastupdate']);
      unset($solicitacao['updated_at']);
      unset($solicitacao['updated_by']);
      unset($solicitacao['created_at']);
      unset($solicitacao['created_by']);
    }

    public function array_to_pgarray($dados){
      return '{'.join(",",$dados).'}';
    }

  /**
   * Limpadores
   */

   /**
    * Remove o trabalhador e suas tuplas em outras tabelas dependentes.
    */
   function deleteTrabalhador(FunctionalTester $I, $email, $tenant){
    $trabalhador = $I->getFromDatabase('persona.trabalhadores', ['identificacaonasajon' => $email, 'tenant'=>$tenant], ['trabalhador']);
    if(empty($trabalhador)){
      return;
    }
    $I->deleteFromDatabase('meurh.informesrendimentos', ['trabalhador' => $trabalhador['trabalhador'], 'tenant' => $tenant]);
    $I->deleteFromDatabase('meurh.recibospagamentos', ['trabalhador' => $trabalhador['trabalhador'], 'tenant' => $tenant]);
    $I->deleteFromDatabase('meurh.solicitacoessalariossobdemanda', ['trabalhador' => $trabalhador['trabalhador'], 'tenant' => $tenant]);
    $I->deleteFromDatabase('meurh.valoressolicitacoesporperiodo', ['trabalhador' => $trabalhador['trabalhador'], 'tenant' => $tenant]);
    $I->deleteFromDatabase('persona.trabalhadores', ['trabalhador' => $trabalhador['trabalhador'], 'tenant' => $tenant]);
    $I->deleteFromDatabase('meurh.solicitacoesalteracoesenderecos', ['trabalhador' => $trabalhador['trabalhador'], 'tenant' => $tenant]);
   }

  public function haveInDatabaseSolicitacaoDocumento(FunctionalTester $I, $tenant, $solicitacao) {
    $solicitacaodocumento = [
      'solicitacaodocumento' => $I->generateUuidV4(),
      'solicitacao' => $solicitacao,
      'tenant' => $tenant,
      'created_by' => json_encode(array("nome" => "teste", "email" => "teste@nasajon.com.br"))
    ];
    $I->haveInDatabase('meurh.solicitacoesdocumentos', $solicitacaodocumento);
    unset($solicitacaodocumento['created_at']);
    unset($solicitacaodocumento['created_by']);
    return $solicitacaodocumento;
  }

  public function haveInDatabaseSolicitacao(FunctionalTester $I, $tenant, $estabelecimento, $trabalhador, $situacao = 0) {
    $solicitacao = [
      'solicitacao' => $I->generateUuidV4(),
      'trabalhador' => $trabalhador,
      'estabelecimento' => $estabelecimento,
      'tiposolicitacao' => 8,
      'situacao' => $situacao,
      'valor' => 145,
      'custoefetivo' => 145,
      'tarifa' => 0,
      'tenant' => $tenant,
      'provedorestabelecimento' => true,
      'codigo' => 1000000
    ];
    $I->haveInDatabase('meurh.solicitacoessalariossobdemanda', $solicitacao);
    unset($solicitacao['created_at']);
    unset($solicitacao['created_by']);
    return $solicitacao;
  }
}