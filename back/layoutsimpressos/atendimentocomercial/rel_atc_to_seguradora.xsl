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

        <!-- Dados Gerais -->

          <fo:table margin-top="10mm" table-layout="fixed" width="100%">
              <fo:table-column column-width="20%"/>
              <fo:table-column column-width="80%"/>

              <fo:table-body>

                   <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Tipo
                          </fo:block>
                      </fo:table-cell>
              
                      <xsl:if test="(atendimentocomercial/prestador/tiposatividades/*)">
                        <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                            <fo:block font-size="9pt"><xsl:apply-templates select="atendimentocomercial/prestador/tiposatividades"/></fo:block>
                        </fo:table-cell>    
                      </xsl:if>
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Nome
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/nomefantasia"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Razão Social
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/razaosocial"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          CNPJ
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/cnpjcpf"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Inscrição Municipal
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/inscricaomunicipal"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Endereço
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/tipologradouro"/><xsl:text> </xsl:text><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/logradouro"/><xsl:text> </xsl:text><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/numero"/>, <xsl:value-of select="atendimentocomercial/prestador/enderecolocal/complemento"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Bairro
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/bairro"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Cidade
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/municipionome"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          CEP
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/cep"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

             </fo:table-body> 
          </fo:table>

          <!-- <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Tipo: <fo:inline font-weight="bold"><xsl:apply-templates select="atendimentocomercial/prestador/tiposatividades"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Nome: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/nomefantasia"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Razão Social: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/razaosocial"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              CNPJ: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/cnpjcpf"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Inscrição Municipal: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/inscricaomunicipal"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Endereço: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/tipologradouro"/></fo:inline><xsl:text> </xsl:text><fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/logradouro"/></fo:inline><xsl:text> </xsl:text><fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/numero"/></fo:inline>, <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/complemento"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Bairro: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/bairro"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              Cidade: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/municipionome"/></fo:inline>
          </fo:block>
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" font-size="9pt">
              CEP: <fo:inline font-weight="bold"><xsl:value-of select="atendimentocomercial/prestador/enderecolocal/cep"/></fo:inline>
          </fo:block> -->


          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" margin-top="10mm" margin-bottom="5mm"
          color="#232323">
              CONTATOS
          </fo:block>
          <xsl:if test="(atendimentocomercial/prestador/contatos/*)">
            <xsl:apply-templates select="atendimentocomercial/prestador/contatos"/>
          </xsl:if>
          
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" margin-top="10mm" margin-bottom="5mm"
          color="#232323">
              DADOS BANCÁRIOS
          </fo:block>

          <xsl:if test="(atendimentocomercial/prestador/dadosbancarios/*)">
            <xsl:apply-templates select="atendimentocomercial/prestador/dadosbancarios"/>
          </xsl:if>

          <!-- Assinatura -->

          <fo:block linefeed-treatment="preserve" text-align="center" space-before="12pt" margin-top="10mm" font-size="9pt" color="#232323"><xsl:value-of select= "atendimentocomercial/criadopor"/></fo:block>
          <fo:block linefeed-treatment="preserve" text-align="center" font-size="9pt" color="#232323"><xsl:value-of select= "atendimentocomercial/estabelecimento/razaosocial"/></fo:block>

          <fo:block id="ultimapagina"> </fo:block>
          
        </fo:flow>
      </fo:page-sequence>
    </fo:root>
  </xsl:template>

<!-- Dados Contatos -->

  <xsl:template match="contatos">
    <fo:table table-layout="fixed" width="100%">
        <fo:table-column column-width="33%"/>
        <fo:table-column column-width="33%"/>
        <fo:table-column column-width="34%"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Nome
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Telefone
              </fo:block>
            </fo:table-cell>

            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Email
              </fo:block>
            </fo:table-cell>
            
          </fo:table-row>
        </fo:table-header>
        
        <fo:table-body>
            <xsl:apply-templates select="contato"/>
        </fo:table-body>

    </fo:table>
  </xsl:template>

    <xsl:template match="contato">
        <fo:table-row>
          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="nome"/></fo:block>
          </fo:table-cell>    

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">

            <xsl:choose>
              <xsl:when test="(telefones/*)">
                <xsl:apply-templates select="telefones/telefone"/>
              </xsl:when>
              <xsl:otherwise>
                <fo:block font-size="9pt"></fo:block>
              </xsl:otherwise>
            </xsl:choose>

        </fo:table-cell>

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="email"/></fo:block>
          </fo:table-cell>

       </fo:table-row>   
  </xsl:template>

<!-- Telefones -->

   <xsl:template match="telefone">
            <fo:block font-size="9pt"><fo:inline font-weight="regular" color="#232323">+<xsl:value-of select="ddi"/><xsl:text> </xsl:text>(<xsl:value-of select="ddd"/>)<xsl:text> </xsl:text><xsl:value-of select="telefone"/></fo:inline></fo:block>
  </xsl:template>

<!-- Dados Bancários -->

  <xsl:template match="dadosbancarios">
    <fo:table table-layout="fixed" width="100%">
        <fo:table-column column-width="33%"/>
        <fo:table-column column-width="33%"/>
        <fo:table-column column-width="34%"/>
        <!-- <fo:table-column column-width="25%"/> -->
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Banco
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Agência
              </fo:block>
            </fo:table-cell>

            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Conta
              </fo:block>
            </fo:table-cell>

            <!-- <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Favorecido
              </fo:block>
            </fo:table-cell> -->
            
          </fo:table-row>
        </fo:table-header>
        
        <fo:table-body>
            <xsl:choose>
              <xsl:when test="(dadobancario)">
                <xsl:apply-templates select="dadobancario"/>
              </xsl:when>
              <xsl:otherwise>
                <fo:table-row><fo:table-cell><fo:block></fo:block></fo:table-cell></fo:table-row>
              </xsl:otherwise>
            </xsl:choose>
        </fo:table-body>

    </fo:table>
  </xsl:template>

    <xsl:template match="dadobancario">
        <fo:table-row>
          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="nomebanco"/><xsl:text> </xsl:text>(<xsl:value-of select="numerobanco"/>)</fo:block>
          </fo:table-cell>    

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="numeroagencia"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="numeroconta"/>-<xsl:value-of select="dvconta"/></fo:block>
          </fo:table-cell>

          <!-- <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/prestador/razaosocial"/></fo:block>
          </fo:table-cell> -->
       </fo:table-row>   
  </xsl:template>

<!-- Tipo -->

  <xsl:template match="tiposatividades">
    <fo:block><fo:inline font-weight="regular" color="#232323">
        <xsl:apply-templates select="tipoatividade"/>
    </fo:inline></fo:block>
  </xsl:template>

  <xsl:template match="tipoatividade">
    <xsl:value-of select="nome"/> 
  </xsl:template>

</xsl:stylesheet>