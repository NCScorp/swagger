<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
      xmlns:fo="http://www.w3.org/1999/XSL/Format"
      xmlns:java="http://xml.apache.org/xslt/java" exclude-result-prefixes="java">

  <xsl:output method="xml" indent="yes"/>
  <xsl:template match="atendimentoPrestadora">
    <fo:table-cell border="solid black 1px" padding="2px">
      <fo:block><xsl:value-of select="atendimentoPrestadora"/></fo:block>
    </fo:table-cell>
  </xsl:template> 

  <xsl:template match="/">
    <fo:root>
      <fo:layout-master-set>
          <fo:simple-page-master page-height="29.7cm" page-width="21.0cm" margin-top="3mm" margin-left="12mm" margin-right="12mm" margin-bottom="3mm" master-name="PageMaster">
              <fo:region-body margin-top="25mm" margin-left="0mm" margin-right="0mm" margin-bottom="20mm" />
              <fo:region-before extent="15mm" />
              <fo:region-after extent="15mm" />
          </fo:simple-page-master>
      </fo:layout-master-set>
      <fo:page-sequence master-reference="PageMaster">

        <fo:static-content flow-name="xsl-region-before" font-size="0.8em">
            <xsl:variable name="logo"><xsl:value-of select="atendimentocomercial/estabelecimento/pathlogo"/> </xsl:variable>
            <fo:block space-before="0mm" space-before.conditionality="retain" space-after="0mm"  border-bottom="2pt solid gray">
                <fo:table width="100%">
                    <fo:table-body>
                        <fo:table-row>
                            <fo:table-cell>
                                <fo:block text-align="left">
                                   <fo:external-graphic src="{$logo}" 
                                          border-width="thin" 
                                          width="4.5cm" 
                                          height="2cm" 
                                          content-width="scale-to-fit" 
                                          content-height="scale-to-fit"
                                          scaling="non-uniform">
                                   </fo:external-graphic>
                                </fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="right" 
                                          padding-top="6px">
                                          Data: <xsl:value-of select="java:format(java:java.text.SimpleDateFormat.new('dd/MM/yyyy'), java:java.util.Date.new())"/> 
                                          &#x2028; &#x2028; &#x2028; 
                                          Hora: <xsl:value-of select="java:format(java:java.text.SimpleDateFormat.new('HH:mm:ss'), java:java.util.Date.new())"/> 
                                </fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-body>
                </fo:table>
            </fo:block>
        </fo:static-content>

        <fo:static-content flow-name="xsl-region-after">
            <fo:block text-align="center" font-size="0.9em"><fo:page-number/> de <fo:page-number-citation ref-id="ultimapagina"/></fo:block>
        </fo:static-content>
        
        <fo:flow flow-name="xsl-region-body">
          <xsl:variable name="dia"><xsl:value-of select="substring(atendimentocomercial/datacriacao, 1, 2)"/></xsl:variable> 
          <xsl:variable name="mes"><xsl:value-of select="substring(atendimentocomercial/datacriacao, 4, 2)"/></xsl:variable> 
          <xsl:variable name="ano"><xsl:value-of select="substring(atendimentocomercial/datacriacao, 7, 4)"/></xsl:variable> 
          <xsl:variable name="datastr"><xsl:value-of select="concat($ano, '/', $mes, '/', $dia)"/></xsl:variable> 

          <fo:block>
              De  : <xsl:value-of select= "atendimentocomercial/criadopor"/> 
          </fo:block>
          <fo:block linefeed-treatment="preserve">
              Para: <xsl:value-of select="atendimentocomercial/prestador/nomefantasia"/>
          </fo:block>
          <fo:block start-indent="34pt">
              <xsl:value-of select="atendimentocomercial/prestador/razaosocial"/>
          </fo:block>
          <fo:block start-indent="34pt">   
              <xsl:value-of select="atendimentocomercial/prestador/cnpjcpf"/>.
          </fo:block>
          <fo:block linefeed-treatment="preserve">
              Data:  <xsl:value-of select="java:format(java:java.text.SimpleDateFormat.new(&quot;dd &apos;de&apos; MMMM &apos;de&apos; yyyy&quot;), java:java.util.Date.new($datastr))"/>. 
          </fo:block>
          <xsl:for-each select="atendimentocomercial/prestador/contatos/contato">
            <xsl:if test="(principal='Sim')">
              <fo:block linefeed-treatment="preserve">
                Att.: <xsl:value-of select="nome"/> - <xsl:value-of select="cargo"/>
              </fo:block>
            </xsl:if>
          </xsl:for-each>
          <fo:block linefeed-treatment="preserve">
              Ref.: Serviço <xsl:value-of select="atendimentocomercial/areaatendimento/nome"/>.
          </fo:block>
          <fo:block linefeed-treatment="preserve" space-before="12pt">
              Prezados senhores(as): 
          </fo:block>

          <fo:block linefeed-treatment="preserve" text-align="justify" space-after="12pt">
              Mediante o presente e-mail à <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/estabelecimento/razaosocial"/></fo:inline> autoriza o faturamento dos serviços de funeral indicados a seguir, referente ao falecimento do Segurado(a) <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/nome"/></fo:inline>. 
          </fo:block>

          <xsl:apply-templates select="atendimentocomercial/seguros"/>

          <fo:block linefeed-treatment="preserve" space-before="12pt">
              Para o faturamento do valor autorizado, segue instruções: 
          </fo:block>

          <fo:block linefeed-treatment="preserve">
              A nota fiscal com os serviços e valores discriminados deverá ser emitida em nome de:
          </fo:block>

          <fo:block linefeed-treatment="preserve" space-before="12pt">
              <xsl:value-of select="atendimentocomercial/cliente_seguradora/razaosocial"/> - CNPJ/CPF: <xsl:value-of select="atendimentocomercial/cliente_seguradora/cnpjcpf"/>
          </fo:block>

          <fo:block>
              <xsl:value-of select="atendimentocomercial/cliente_seguradora/enderecocobranca/enderecocompleto"/> 
          </fo:block>

          <fo:block linefeed-treatment="preserve" text-align="justify" space-before="12pt">
              A <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/cliente_seguradora/razaosocial"/></fo:inline>, é responsável pelo pagamento da despesa funeral. O pagamento será feito na conta bancária da funerária emitente da nota fiscal de acordo com o valor desta autorização. O pagamento tem previsão de 30 à 60 dias após o recebimento da documentação em nosso escritório.
          </fo:block>

          <fo:block linefeed-treatment="preserve" font-weight="bold" space-after="12pt">
              Documentos Obrigatórios:
          </fo:block>

          <xsl:if test="(atendimentocomercial/documentos/*)">
          <xsl:apply-templates select="atendimentocomercial/documentos"/>
        </xsl:if>

           <fo:block linefeed-treatment="preserve" font-weight="bold" space-after="12pt">
              Importante:
          </fo:block>

          <fo:block linefeed-treatment="preserve" text-align="justify" space-before="12pt">
              MESMO QUE OS SERVIÇOS DE EMPRESAS TERCEIRIZADAS SEJAM INSERIDOS NA NOTA FISCAL DA FUNERÁRIA, FAVOR ENVIAR: AS NOTAS FISCAIS ORIGINAIS OU CONTRATO DE LOCAÇÃO, COMPRA OU CONSTRUÇÃO DOS REFERIDOS, FATURADO EM NOME DO RESPONSÁVEL PELO PAGAMENTO (FUNERÁRIA, FAMÍLIA...). CASO A FUNERÁRIA NÃO INCLUA EM SUA NF SERVIÇOS DE TERCEIROS, FAVOR ENVIAR: AS NOTAS FISCAIS ORIGINAIS OU CONTRATO DE LOCAÇÃO, COMPRA OU CONSTRUÇÃO DOS REFERIDOS, EMITIDOS EM NOME DA FUNERÁRIA OU DA FAMÍLIA (CASO ESTA JÁ TENHA EFETUADO O PAGAMENTO) + NOTA DE DÉBITO OU RECIBO COM PAPEL TIMBRADO, CARIMBADO E ASSINADO PELA FUNERÁRIA COBRANDO DA SEGURADORA O VALOR PAGO AO PRESTADOR TERCEIRIZADO OU REEMBOLSADO À FAMÍLIA. SE O PRESTADOR TERCEIRIZADO NÃO EMITIR NOTA FISCAL OU RECIBO COM PAPEL TIMBRADO E/OU CARIMBADO, TEMOS DE SER NOTIFICADOS IMEDIATAMENTE!!!! DESTA FORMA EVITAMOS TRANSTORNOS FUTUROS. NÃO PODEREMOS NÓS RESPONSABILIZAR SE ÀS ORIENTAÇÕES NÃO FOREM DEVIDAMENTE SEGUIDAS!!!!! 
          </fo:block>

          <fo:block linefeed-treatment="preserve" text-align="justify" space-before="12pt">
              Para maior agilidade no envio da documentação à seguradora, solicitamos que a documentação acima, incluindo a nota fiscal, seja digitalizada e enviada para o e-mail documento@maracanaassistencia.com.br.
          </fo:block>

           <fo:block linefeed-treatment="preserve" text-align="justify" space-before="12pt">
              Os documentos solicitados acima, deverão ser enviados pelo correio ao endereço abaixo:
          </fo:block>

          <fo:block linefeed-treatment="preserve" space-before="12pt">
              <xsl:value-of select="atendimentocomercial/estabelecimento/razaosocial"/> - CNPJ/CPF: <xsl:value-of select="atendimentocomercial/estabelecimento/cnpjcpf"/>
          </fo:block>

          <fo:block>
              <xsl:value-of select="atendimentocomercial/estabelecimento/endereco/enderecocompleto"/> 
          </fo:block>

          <fo:block linefeed-treatment="preserve" font-weight="bold" space-after="12pt">
              Atenção:
          </fo:block>

         <fo:list-block provisional-distance-between-starts="0.3cm" provisional-label-separation="0.15cm">
            <fo:list-item>
                <fo:list-item-label end-indent="label-end()">
                    <fo:block color="black" font-size="1.2em" font-weight="bold" start-indent="25pt">&#x2022;</fo:block>  
                </fo:list-item-label>
                <fo:list-item-body  start-indent="body-start()" text-align="justify">
                    <fo:block start-indent="50pt">Em caso de dúvida sobre a assistência, ligar para <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/estabelecimento/telefone"/></fo:inline>.</fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label end-indent="label-end()">
                    <fo:block color="black" font-size="1.2em" font-weight="bold" start-indent="25pt">&#x2022;</fo:block>  
                </fo:list-item-label>
                <fo:list-item-body  start-indent="body-start()" text-align="justify">
                    <fo:block start-indent="50pt">Se a dúvida for sobre pagamento ou assunto relacionado a documentação, enviar e-mail para documento@maracanaassistencia.com.br O assunto do e-mail terá de conter o nome do falecido. O prazo de resposta são 5 dias úteis.</fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label end-indent="label-end()">
                    <fo:block color="black" font-size="1.2em" font-weight="bold" start-indent="25pt">&#x2022;</fo:block>  
                </fo:list-item-label>
                <fo:list-item-body  start-indent="body-start()" text-align="justify">
                    <fo:block start-indent="50pt">Se algum dos documentos solicitados não for encaminhado, o pagamento ficará bloqueado. O prazo de pagamento começará a contar após a resolução da pendência.</fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label end-indent="label-end()">
                    <fo:block color="black" font-size="1.2em" font-weight="bold" start-indent="25pt">&#x2022;</fo:block>  
                </fo:list-item-label>
                <fo:list-item-body  start-indent="body-start()" text-align="justify">
                    <fo:block start-indent="50pt">Comunicamos que há um prazo estipulado pela seguradora para recebimento dos documentos, que é de 120 dias após o acionamento, caso não envie dentro deste período, a funerária não poderá cobrar o funeral da seguradora ou da família.</fo:block>
                </fo:list-item-body>
            </fo:list-item>
          </fo:list-block>

          <fo:block linefeed-treatment="preserve" font-weight="bold" space-after="12pt">
              Serviços Solicitados:
          </fo:block>

          <xsl:if test="(atendimentocomercial/servicosorcados/*)">
            <xsl:apply-templates select="atendimentocomercial/servicosorcados"/>
          </xsl:if>

          <fo:block linefeed-treatment="preserve" text-align="center" space-before="12pt">
              Atenciosamente,
          </fo:block>

          <fo:block linefeed-treatment="preserve" text-align="center" space-before="12pt"><xsl:value-of select= "atendimentocomercial/criadopor"/></fo:block>
          <fo:block linefeed-treatment="preserve" text-align="center"><xsl:value-of select= "atendimentocomercial/estabelecimento/razaosocial"/></fo:block>

          <fo:block id="ultimapagina"> </fo:block>
          
        </fo:flow>
      </fo:page-sequence>
    </fo:root>
  </xsl:template>

  <xsl:template match="seguros">
    <xsl:if test="(seguro/*)">
      <fo:table table-layout="fixed" width="100%">
          <fo:table-column column-width="35mm"/>
          <fo:table-column column-width="30mm"/>
          <fo:table-column column-width="83mm"/>
          <fo:table-column column-width="40mm"/>
          <fo:table-header>
            <fo:table-row>
              <fo:table-cell>
                <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                          border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                  Produto
                </fo:block>
              </fo:table-cell>
              
              <fo:table-cell>
                <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                          border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                  Valor Autorizado
                </fo:block>
              </fo:table-cell>

              <fo:table-cell>
                <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                          border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                  Títular da Apólice
                </fo:block>
              </fo:table-cell>
              
              <fo:table-cell>
                <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                          border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                  Vínculo com o Falecido
                </fo:block>
              </fo:table-cell>
              
            </fo:table-row>
          </fo:table-header>
          
          <fo:table-body>
              <xsl:apply-templates select="seguro"/>
          </fo:table-body>

      </fo:table>
    </xsl:if>
  </xsl:template>

  <xsl:template match="seguro">
        <fo:table-row>
          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="produto"/></fo:block>
          </fo:table-cell>    

          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="valorautorizado"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="titularapolice"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="titularvinculo"/></fo:block>
          </fo:table-cell>
       </fo:table-row>   
  </xsl:template>     

  <xsl:template match="documentos">
    <fo:table table-layout="fixed" width="100%">
        <fo:table-column column-width="83mm"/>
        <fo:table-column column-width="45mm"/>
        <fo:table-column column-width="35mm"/>
        <fo:table-column column-width="25mm"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell>
              <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                        border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                Documento
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell>
              <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                        border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                Exige cópia autenticada
              </fo:block>
            </fo:table-cell>

            <fo:table-cell>
              <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                        border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                Exige cópia simples
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell>
              <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                        border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                Exige original
              </fo:block>
            </fo:table-cell>
            
          </fo:table-row>
        </fo:table-header>
        
        <fo:table-body>
            <xsl:apply-templates select="documento"/>
        </fo:table-body>

    </fo:table>
  </xsl:template>

  <xsl:template match="documento">
        <fo:table-row>
          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="nome"/></fo:block>
          </fo:table-cell>    

          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="copiaautenticada"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="copiasimples"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block font-size="0.8em"><xsl:value-of select="original"/></fo:block>
          </fo:table-cell>
       </fo:table-row>   
  </xsl:template>     

  <xsl:template match="servicosorcados">
    <fo:table table-layout="fixed" width="100%" table-omit-footer-at-break="true">
        <fo:table-column column-width="148mm"/>
        <fo:table-column column-width="40mm"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell>
              <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle"
                        border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                Descrição
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell>
              <fo:block font-size="0.8em" font-weight="bold" text-align="right" vertical-align="middle"
                        border="solid black 1px" border-width="1pt" border-color="black" background-color="#808080">
                Valor
              </fo:block>
            </fo:table-cell>
            
          </fo:table-row>
        </fo:table-header>

        <fo:table-body>
            <xsl:for-each select="servicoorcado">
                <fo:table-row>
                    <fo:table-cell border="solid black 1px" padding="2px">
                      <fo:block font-size="0.8em"><xsl:value-of select="descricao"/></fo:block>
                    </fo:table-cell>    

                    <fo:table-cell border="solid black 1px" padding="2px" text-align="right">
                      <fo:block font-size="0.8em">R$ <xsl:value-of select="format-number(valor, '###,###.00')"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>   
            </xsl:for-each>
  
            <fo:table-row>
                <fo:table-cell>
                    <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle" padding="3px">
                        Total Autorizado:
                    </fo:block>
                </fo:table-cell>
            
                <fo:table-cell>
                    <fo:block font-size="0.8em" font-weight="bold" text-align="right" vertical-align="middle" padding="3px">
                        R$ <xsl:value-of select="format-number(sum(servicoorcado/valor), '###,###.00')"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>

        </fo:table-body>

    </fo:table>
  </xsl:template>

</xsl:stylesheet>