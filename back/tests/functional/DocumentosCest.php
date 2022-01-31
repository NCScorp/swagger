<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * @todo Testar criação de Documentos
 * Testar edição de documento
 * Testar criação de documento emitido no processo
 * Testar edição de documento emitido no processo (removendo e adicionando documentos necessários)
 */

class DocumentosCest{

  private $url = '/api/gednasajon/tiposdocumentos/';
  private $tenant = 'gednasajon';
  private $tenant_numero = '47';
  private $grupoempresarial = 'FMA';
  
   public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::DOCUMENTOS_CREATE, EnumAcao::DOCUMENTOS_PUT, EnumAcao::DOCUMENTOS_INDEX]);
  }
      /**
     * Criação de um documento
     * E em seguida, se o outro ainda está lá.
     * @param FunctionalTester $I
     */
  public function criaDocumento(FunctionalTester $I){
      /* prepara cenário */
        $documento = [
            "nome" => "IDRG",
            "emissaonoprocesso" => false,
            "tenant" => $this->tenant,
            "dominio" => null,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        
        /* execução da funcionalidade */
        $documento_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($documento['nome'], $documento_criado['nome']);
        $I->assertEquals($documento['emissaonoprocesso'], $documento_criado['emissaonoprocesso']);
        $I->assertEquals($documento['dominio'], $documento_criado['dominio']);
        $I->assertEquals($this->tenant_numero, $documento_criado['tenant']);
        
         /* remove documento criado */
        $I->deleteFromDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento_criado['tipodocumento']]);
  }
    /**
     * Edição de um documento, onde se troca o nome e dá update.
     * E em seguida, se o outro ainda está lá.
     * @param FunctionalTester $I
     */
  public function editaDocumento(FunctionalTester $I){
      /* prepara cenário */
        $documento = $I->haveInDatabaseDocumento($I);
        $documento['nome'] = 'RG';
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url . $documento['tipodocumento'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento['tipodocumento'], "nome" => $documento['nome']]);
         
        /* remove documento criado */
        $I->deleteFromDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento['tipodocumento']]);
  }
      /**
     * Tendo dois documentos necessários no cenário, se remove todos
     * 
     * @param FunctionalTester $I
     */
  public function RemoveTodosDocumentoNecessario(FunctionalTester $I){
      /* prepara cenário */
       $documento = $I->haveInDatabaseDocumentoNecessario($I);
        
       /* execução da funcionalidade */
       $documento['documentosnecessarios'] = [];
       $I->sendRaw('PUT', $this->url . $documento['tipodocumento'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);
       
       /* validação do resultado */
        $I->cantSeeInDatabase('ns.documentosnecessarios', ['documento' => $documento['tipodocumento']]);
        $I->canSeeResponseCodeIs(HttpCode::OK);
    
       /* remove documento criado */
        $I->deleteFromDatabase('ns.documentosnecessarios', ['documento' => $documento['tipodocumento']]);
        $I->deleteFromDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento['tipodocumento']]);
        
  }
      /**
     * Adiciona um DocumentoNecessário extra.
     * E em seguida, se o outro ainda está lá.
     * @param FunctionalTester $I
     */
  public function adicionaDocumentoNecessario(FunctionalTester $I){
      /* prepara cenário */
       $documento = $I->haveInDatabaseDocumentoNecessario($I);
       $documentofilho = $I->haveInDatabaseDocumento($I);
       array_push($documento['documentosnecessarios'], ['documentonecessario' =>$documentofilho]);
       
        /* execução da funcionalidade */
       $I->sendRaw('PUT', $this->url . $documento['tipodocumento'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null); 

       /* validação do resultado */
        $I->canSeeInDatabase('ns.documentosnecessarios', ['documentonecessario' => $documentofilho['tipodocumento']]);
        $I->canSeeResponseCodeIs(HttpCode::OK);
         
       /* remove documento criado */
        $I->deleteFromDatabase('ns.documentosnecessarios', ['documento' => $documento['tipodocumento']]);
        $I->deleteFromDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento['tipodocumento']]);
        
  }
    /**
     * Tendo dois documentos necessários no cenário, se remove um e checa se o removido não está mais no banco.
     * E em seguida, se o outro ainda está lá.
     * @param FunctionalTester $I
     */
  public function removeDocumentoNecessario(FunctionalTester $I){
      /* prepara cenário */
       $documento = $I->haveInDatabaseDocumentoNecessario($I);
       $documentofilho = $I->haveInDatabaseDocumento($I);  
       $removido = array_pop($documento['documentosnecessarios']);
       
       /* execução da funcionalidade */
       $I->sendRaw('PUT', $this->url . $documento['tipodocumento'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null); 
       
       /* validação do resultado */
        $I->cantSeeInDatabase('ns.documentosnecessarios',['documentonecessario' =>$removido['documentonecessario']['tipodocumento']]);
        $I->canSeeInDatabase('ns.documentosnecessarios', ['documentonecessario' => $documento['documentosnecessarios'][0]['documentonecessario']['tipodocumento']]);
        
       /* remove documento criado */
        $I->deleteFromDatabase('ns.documentosnecessarios',['documentonecessario' => $documento['documentosnecessarios'][0]['documentonecessario']['tipodocumento']]);
        $I->canSeeResponseCodeIs(HttpCode::OK);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaDocumentos(FunctionalTester $I) {

    /* inicializações */
    $documento = $I->haveInDatabaseDocumento($I);
    $documento = $I->haveInDatabaseDocumento($I);;
    $documento = $I->haveInDatabaseDocumento($I);
    $countAtual = $I->grabNumRecords('ns.tiposdocumentos', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }
  
}
