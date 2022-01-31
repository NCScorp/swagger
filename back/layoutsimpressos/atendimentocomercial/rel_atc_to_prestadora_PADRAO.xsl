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
            <xsl:variable name="logo"><xsl:value-of select="dados_atendimentoscomerciais/estabelecimento/pathlogo"/> </xsl:variable>
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
          <xsl:variable name="dia"><xsl:value-of select="substring(dados_atendimentoscomerciais/datacriacao, 1, 2)"/></xsl:variable> 
          <xsl:variable name="mes"><xsl:value-of select="substring(dados_atendimentoscomerciais/datacriacao, 4, 2)"/></xsl:variable> 
          <xsl:variable name="ano"><xsl:value-of select="substring(dados_atendimentoscomerciais/datacriacao, 7, 4)"/></xsl:variable> 
          <xsl:variable name="datastr"><xsl:value-of select="concat($ano, '/', $mes, '/', $dia)"/></xsl:variable> 

<!-- INICIO -->

<fo:table margin-top="10mm" table-layout="fixed" width="100%">
              <fo:table-column column-width="20%"/>
              <fo:table-column column-width="80%"/>

              <fo:table-body>

                   <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          De:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:apply-templates select="dados_atendimentoscomerciais/criadopor"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Para:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/prestador/nomefantasia"/></fo:block><fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/prestador/razaosocial"/><xsl:text> - </xsl:text><xsl:value-of select="dados_atendimentoscomerciais/prestador/cnpjcpf"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Data:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/datacriacao"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Contato:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:for-each select="dados_atendimentoscomerciais/prestador/contatos/contato">
            <xsl:if test="(principal='Sim')">
              <xsl:value-of select="nome"/> - <xsl:value-of select="cargo"/>
            </xsl:if>
          </xsl:for-each></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Referência
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/areaatendimento/nome"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>
             </fo:table-body> 
          </fo:table>

<!-- DADOS DA EMPRESA -->

<fo:block font-size="9pt" margin-top="10mm">DADOS DA EMPRESA</fo:block>

<fo:table margin-top="3mm" table-layout="fixed" width="100%">
              <fo:table-column column-width="20%"/>
              <fo:table-column column-width="80%"/>

              <fo:table-body>

                   <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Nome:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:apply-templates select="dados_atendimentoscomerciais/estabelecimento/razaosocial"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          CNPJ/CPF:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/estabelecimento/cnpjcpf"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Endereço:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/estabelecimento/endereco/enderecocompleto"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>
             </fo:table-body> 
          </fo:table>

<!-- DADOS DO CLIENTE -->

<fo:block font-size="9pt" margin-top="10mm">DADOS DO CLIENTE</fo:block>

<fo:table margin-top="3mm" table-layout="fixed" width="100%">
              <fo:table-column column-width="20%"/>
              <fo:table-column column-width="80%"/>

              <fo:table-body>

                   <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Nome:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:apply-templates select="dados_atendimentoscomerciais/prestador/razaosocial"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          CNPJ/CPF:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/prestador/cnpjcpf"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Endereço:
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="dados_atendimentoscomerciais/prestador/enderecolocal/enderecocompleto"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>
             </fo:table-body> 
          </fo:table>

<!-- DOCUMENTOS DO ATENDIMENTO -->

<fo:block page-break-before="always" font-size="9pt" margin-top="10mm" margin-bottom="3mm">DOCUMENTOS DO ATENDIMENTO</fo:block>

          <xsl:apply-templates select="dados_atendimentoscomerciais/documentos"/>

<!-- SERVICOS SOLICITADOS -->

<fo:block font-size="9pt" margin-top="10mm" margin-bottom="3mm">SERVIÇOS SOLICITADOS</fo:block>

          <xsl:apply-templates select="dados_atendimentoscomerciais/servicosorcados"/>

<!-- ATENCIOSAMENTE -->

<fo:block linefeed-treatment="preserve" text-align="center" font-size="9pt" margin-top="10mm" margin-bottom="3mm">
              Atenciosamente,
          </fo:block>

          <fo:block linefeed-treatment="preserve" text-align="center" font-size="9pt"><xsl:value-of select= "dados_atendimentoscomerciais/criadopor"/></fo:block>
          <fo:block linefeed-treatment="preserve" text-align="center" font-size="9pt"><xsl:value-of select= "dados_atendimentoscomerciais/estabelecimento/razaosocial"/></fo:block>

<!-- FIM -->

          <fo:block id="ultimapagina"> </fo:block>
          
        </fo:flow>
      </fo:page-sequence>
    </fo:root>
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

<!-- TEMPLATE DOCUMENTOS -->

  <xsl:template match="documentos">
    <fo:table table-layout="fixed" width="100%">
        <fo:table-column column-width="25%"/>
        <fo:table-column column-width="25%"/>
        <fo:table-column column-width="25%"/>
        <fo:table-column column-width="25%"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Documento
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Exige cópia autenticada
              </fo:block>
            </fo:table-cell>

            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Exige cópia simples
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
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
          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="nome"/></fo:block>
          </fo:table-cell>    

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="copiaautenticada"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="copiasimples"/></fo:block>
          </fo:table-cell>

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="original"/></fo:block>
          </fo:table-cell>
       </fo:table-row>   
  </xsl:template>     

<!-- SERVICOS SOLICITADOS -->

  <xsl:template match="servicosorcados">
    <fo:table table-layout="fixed" width="100%" table-omit-footer-at-break="true">
        <fo:table-column column-width="80%"/>
        <fo:table-column column-width="20%"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Descrição
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Valor
              </fo:block>
            </fo:table-cell>
            
          </fo:table-row>
        </fo:table-header>

        <fo:table-body>
            <xsl:for-each select="servicoorcado">
                <fo:table-row>
                    <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
                      <fo:block font-size="9pt"><xsl:value-of select="descricao"/></fo:block>
                    </fo:table-cell>    

                    <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
                      <fo:block font-size="9pt">R$ <xsl:value-of select="format-number(valor, '###,###.00')"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>   
            </xsl:for-each>
  
            <fo:table-row>
                <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                    <fo:block font-size="9pt" font-weight="bold" text-align="left" vertical-align="middle">
                        TOTAL AUTORIZADO:
                    </fo:block>
                </fo:table-cell>
            
                <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                    <fo:block font-size="9pt" font-weight="bold" text-align="left" vertical-align="middle">
                        R$ <xsl:value-of select="format-number(sum(servicoorcado/valor), '###,###.00')"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>

        </fo:table-body>

    </fo:table>
  </xsl:template>

</xsl:stylesheet>