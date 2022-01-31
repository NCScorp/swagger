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
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Data
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/datacriacao"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>    

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Falecido
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/nome"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

                  <fo:table-row>
                      <fo:table-cell padding="5pt" border="solid" border-width="0pt" border-color="#efefef" background-color="#efefef">
                          <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                          Seguradora
                          </fo:block>
                      </fo:table-cell>
              
                      <fo:table-cell border="solid" border-width="0pt" border-color="#efefef" padding="5pt" color="#232323">
                          <fo:block font-size="9pt"><xsl:value-of select="atendimentocomercial/cliente_seguradora/nomefantasia"/></fo:block>
                      </fo:table-cell>    
                  </fo:table-row>

             </fo:table-body> 
          </fo:table>


          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" margin-top="10mm" margin-bottom="5mm"
          color="#232323">
              DADOS DO SEGURO
          </fo:block>
          <xsl:if test="(atendimentocomercial/seguros/*)">
            <xsl:apply-templates select="atendimentocomercial/seguros"/>
          </xsl:if>
          
          <fo:block linefeed-treatment="preserve" text-align="left" space-before="0pt" margin-top="10mm" margin-bottom="5mm"
          color="#232323">
              DADOS DO SERVIÇO
          </fo:block>

          <xsl:if test="(atendimentocomercial/servicosorcados/*)">
            <xsl:apply-templates select="atendimentocomercial/servicosorcados"/>
          </xsl:if>
        
          <fo:block id="ultimapagina"> </fo:block>
          
        </fo:flow>
      </fo:page-sequence>
    </fo:root>
  </xsl:template>

<!-- Dados Contatos -->

  <xsl:template match="seguros">
    <fo:table table-layout="fixed" width="100%">
        <fo:table-column column-width="60%"/>
        <fo:table-column column-width="40%"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Produto
              </fo:block>
            </fo:table-cell>
            
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Processo(Sinistro)
              </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        
        <fo:table-body>
            <xsl:apply-templates select="seguro"/>
        </fo:table-body>

    </fo:table>
  </xsl:template>

    <xsl:template match="seguro">
        <fo:table-row>
          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="produto"/></fo:block>
          </fo:table-cell>    

          <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
            <fo:block font-size="9pt"><xsl:value-of select="sinistro"/></fo:block>
          </fo:table-cell>
       </fo:table-row>   
  </xsl:template>


<!-- Dados Serviços -->
<xsl:template match="servicosorcados">
    <fo:table table-layout="fixed" width="100%" table-omit-footer-at-break="true">
        <fo:table-column column-width="148mm"/>
        <fo:table-column column-width="40mm"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="left" vertical-align="middle" font-style="italic">
                Descrição dos Serviços
              </fo:block>
            </fo:table-cell>
            
           <fo:table-cell padding="5pt" border="solid" border-width="1pt" border-color="#efefef" background-color="#efefef">
              <fo:block font-size="8pt" font-weight="bold" color="#232323" text-align="right" vertical-align="middle" font-style="italic">
                Valores
              </fo:block>
            </fo:table-cell>
            
          </fo:table-row>
        </fo:table-header>

        <fo:table-body>
            <xsl:for-each select="servicoorcado">
                <fo:table-row>
                     <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
                      <fo:block font-size="0.8em"><xsl:value-of select="descricao"/></fo:block>
                    </fo:table-cell>    

                     <fo:table-cell border="solid" border-width="1pt" border-color="#efefef" padding="5pt" color="#232323">
                      <fo:block font-size="0.8em" text-align="right">R$ <xsl:value-of select="format-number(valor, '###,###.00')"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>   
            </xsl:for-each>
  
            <fo:table-row>
                <fo:table-cell padding="5pt">
                    <fo:block font-size="0.8em" font-weight="bold" text-align="left" vertical-align="middle" >
                        Total Autorizado:
                    </fo:block>
                </fo:table-cell>
            
                <fo:table-cell padding="5pt">
                    <fo:block font-size="0.8em" font-weight="bold" text-align="right" vertical-align="middle" >
                        R$ <xsl:value-of select="format-number(sum(servicoorcado/valor), '###,###.00')"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>

        </fo:table-body>

    </fo:table>
  </xsl:template>

</xsl:stylesheet>