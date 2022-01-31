<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:fo="http://www.w3.org/1999/XSL/Format"
    xmlns:java="http://xml.apache.org/xslt/java"
    exclude-result-prefixes="java"
    xmlns:exslt="http://exslt.org/common"
    >
    <xsl:output method="xml" indent="yes" />
    <xsl:template match="atendimentoPrestadora">
        <fo:table-cell border="solid black 1px" padding="2px">
            <fo:block>
                <xsl:value-of select="atendimentoPrestadora" />
            </fo:block>
        </fo:table-cell>
    </xsl:template>
    <xsl:template match="/">
        <fo:root>

            <!-- DEFINIÇÃO DO DOCUMENTO -->
            <fo:layout-master-set>
                <fo:simple-page-master
                    page-height="29.7cm" page-width="21.0cm"
                    margin-top="1cm" margin-left="1.5cm" margin-right="1.5cm" margin-bottom="1cm"
                    master-name="PageMaster">
                    <fo:region-body />
                </fo:simple-page-master>
            </fo:layout-master-set>




            <fo:page-sequence master-reference="PageMaster">


                <!-- corpo -->
                <fo:flow flow-name="xsl-region-body">
                    <xsl:variable name="logo">
                        <xsl:value-of select="result/atcs/estabelecimento/pathlogo" />
                    </xsl:variable>

                    <!-- variaveis de data -->
                    <!-- <xsl:variable name="dia">
                        <xsl:value-of select="substring(result/atcs/datacriacao, 1, 2)" />
                    </xsl:variable>
                    <xsl:variable name="mes">
                        <xsl:value-of select="substring(result/atcs/datacriacao, 4, 2)" />
                    </xsl:variable>
                    <xsl:variable name="ano">
                        <xsl:value-of select="substring(result/atcs/datacriacao, 7, 4)" />
                    </xsl:variable>
                    <xsl:variable name="datastr">
                        <xsl:value-of select="concat($ano, '/', $mes, '/', $dia)" />
                    </xsl:variable> -->




                    <fo:table 
                        table-layout="fixed"
                        width="100%">
                        <fo:table-column column-width="20%" />
                        <fo:table-column column-width="20%" />
                        <fo:table-column column-width="20%" />
                        <fo:table-column column-width="20%" />
                        <fo:table-column column-width="20%" />
                        <fo:table-body>
                            <fo:table-row>
                                
                                <fo:table-cell number-columns-spanned="4" border="solid black 1px">
                                    <fo:block font-weight="bold" margin="4px" margin-top="15px" margin-bottom="15px">
                                        PREFEITURA DO MUNICÍPIO DE RIO DE JANEIRO
                                    </fo:block>

                                    <fo:block font-weight="bold" margin="4px" margin-top="15px" margin-bottom="15px">
                                        RECIBO PROVISÓRIO DE SERVIÇOS - RPS
                                    </fo:block>
                                </fo:table-cell>

                                <fo:table-cell number-columns-spanned="1" border="solid black 1px" font-size="10px">
                                    <fo:block margin-top="5px" margin-bottom="5px">
                                        <fo:inline padding-left="4px">
                                            Número
                                        </fo:inline>
                                    </fo:block>
                                    <fo:block margin-top="5px" margin-bottom="5px" padding-bottom="5px" border-bottom="solid black 1px" font-weight="bold" text-align="center">
                                        <fo:inline padding-left="4px">
                                            <xsl:value-of select="result/rps/numerorps" />
                                        </fo:inline>
                                    </fo:block>
                                    <fo:block margin-top="5px" margin-bottom="5px" >
                                        <fo:inline padding-left="4px">
                                            Data Emissão
                                        </fo:inline>
                                    </fo:block>
                                    <fo:block margin-top="5px" margin-bottom="5px"  font-weight="bold" text-align="center">
                                        <fo:inline padding-left="4px">
                                            <xsl:value-of select="result/rps/data_geracao_rps" />
                                        </fo:inline>
                                    </fo:block>
                                </fo:table-cell>

                            </fo:table-row>



                            <fo:table-row>
                                <fo:table-cell number-columns-spanned="5" border="solid black 1px">
                                    
                                    <fo:block font-weight="bold" margin="4px" text-align="center">
                                        PRESTADOR DE SERVIÇOS
                                    </fo:block>

                                    <fo:block>
                                        <fo:table 
                                            table-layout="fixed"
                                            width="100%">
                                            <fo:table-column column-width="20%" />
                                            <fo:table-column column-width="80%" />
                                            <fo:table-body>
                                                <fo:table-row>
                                                    <fo:table-cell>
                                                        <fo:block margin="4px" text-align="center">
                                                            <fo:external-graphic
                                                            src="{$logo}" border-width="thin"
                                                            width="2.5cm"
                                                            content-height="scale-to-fit"
                                                            scaling="uniform">
                                                        </fo:external-graphic>
                                                        </fo:block>
                                                    </fo:table-cell>
                                                    <fo:table-cell font-size="8px">
                                                        <fo:block margin="6px">
                                                            CPF/CNPJ: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/cnpjcpf" />
                                                            </fo:inline>
                                                            Inscrição Municipal: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/inscricaomunicipal" />
                                                            </fo:inline>
                                                            Inscrição Estadual: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/inscricaoestadual" /></fo:inline>
                                                        </fo:block>
                                                        <fo:block margin="6px">
                                                            Nome/Razão Social: 
                                                            <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/razaosocial" />
                                                            </fo:inline>
                                                        </fo:block>
                                                        <fo:block margin="6px">
                                                            Nome Fantasia: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/nomefantasia" />
                                                            </fo:inline>
                                                            Tel.: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/telefone" />
                                                            </fo:inline>
                                                        </fo:block>
                                                        <fo:block margin="6px">
                                                            Endereço: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/endereco/enderecocompleto" />
                                                            </fo:inline>
                                                        </fo:block>
                                                        <fo:block margin="6px">
                                                            Município: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/endereco/municipionome" />
                                                            </fo:inline>
                                                            UF: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/endereco/uf" />
                                                            </fo:inline>
                                                            E-mail: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/estabelecimento/email" />
                                                            </fo:inline>
                                                        </fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                            </fo:table-body>
                                        </fo:table>
                                    </fo:block>
                                </fo:table-cell>
                            </fo:table-row>


                            <fo:table-row>
                                <fo:table-cell number-columns-spanned="5" border="solid black 1px">
                                    
                                    <fo:block font-weight="bold"  margin="6px" text-align="center">
                                        TOMADOR DE SERVIÇOS
                                    </fo:block>

                                    <fo:block>
                                        <fo:table 
                                            table-layout="fixed"
                                            width="100%">
                                            <fo:table-column column-width="100%" />
                                            <fo:table-body>
                                                <fo:table-row>
                                                    <fo:table-cell font-size="8px">
                                                        <fo:block margin="6px">
                                                            CPF/CNPJ: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/cnpjcpf" />
                                                            </fo:inline>
                                                            Inscrição Municipal:  <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/inscricaomunicipal" />
                                                            </fo:inline>
                                                            Inscrição Estadual:<fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/inscricaoestadual" /></fo:inline>
                                                        </fo:block>
                                                        <fo:block margin="6px">
                                                            Nome/Razão Social:
                                                            <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/razaosocial" />
                                                            </fo:inline>
                                                        </fo:block>
                                                        <!-- <fo:block>
                                                            Nome Fantasia: JDC ELECTRIC SERVIÇOS E COMÉRCIO DE ELETROELETRÔNICOS LTDA
                                                            Tel.: 12345678789
                                                        </fo:block> -->
                                                        <fo:block margin="6px">
                                                            Endereço: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/enderecocobranca/enderecocompleto" />
                                                            </fo:inline>
                                                        </fo:block>
                                                        <fo:block margin="6px">
                                                            Município: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/enderecocobranca/municipionome" />
                                                            </fo:inline>
                                                            UF: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/enderecocobranca/uf" />
                                                            </fo:inline>
                                                            E-mail: <fo:inline font-weight="bold">
                                                                <xsl:value-of select="result/atcs/cliente/contato_principal/email" />
                                                            </fo:inline>
                                                        </fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                            </fo:table-body>
                                        </fo:table>
                                    </fo:block>
                                </fo:table-cell>
                            </fo:table-row>


                            <fo:table-row>
                                <fo:table-cell number-columns-spanned="5" border="solid black 1px">
                                    
                                    <!--  -->
                                    <fo:block-container 
                                        width="170mm" 
                                        absolute-position="absolute"
                                        left="55mm"
                                        top="10mm"
                                        >
                                        <fo:block font-size="45pt" color="#aaaaaa">
                                            <xsl:text>SEM VALOR</xsl:text>
                                        </fo:block>
                                    </fo:block-container>

                                    <fo:block font-weight="bold" margin="6px" text-align="center">
                                        DISCRIMINAÇÃO DE SERVIÇOS
                                    </fo:block>

                                    <fo:block font-size="8px" margin="4px" height="14cm">

                                        <xsl:for-each select="result/rps/itens_nota/entry">
                                            <fo:block>
                                                <xsl:value-of select="itemcontrato_quantidade" /> x 
                                                
                                                <xsl:if test="familia_descricao = ''">
                                                    <xsl:value-of select="itemcontrato_codigo" />
                                                </xsl:if>
                                                <xsl:if test="familia_descricao != ''">
                                                    <xsl:value-of select="familia_descricao" />
                                                </xsl:if>

                                                R$ <xsl:value-of select="format-number(itemcontrato_valor, '###,###0.00')" />
                                            </fo:block>
                                        </xsl:for-each>

                                    </fo:block>


                                </fo:table-cell>
                            </fo:table-row>


                            <xsl:variable name="valorTotal">
                                <xsl:for-each select="result/rps/itens_nota/entry">
                                    <value>
                                        <xsl:value-of select="itemcontrato_valor" />
                                    </value>
                                </xsl:for-each>
                            </xsl:variable>


                            <fo:table-row>
                                <fo:table-cell number-columns-spanned="5" border="solid black 1px">
                                    
                                    <fo:block font-weight="bold" margin="6px" text-align="center">
                                        VALOR DA NOTA = 
                                        R$ <xsl:value-of select="format-number(sum(exslt:node-set($valorTotal)/value), '###,###.00')" />
                                    </fo:block>

                                </fo:table-cell>
                            </fo:table-row>

                            <fo:table-row>
                                <fo:table-cell number-columns-spanned="5" border="solid black 1px" font-size="8px">
                                    <fo:block margin="6px">
                                        Serviço Prestado
                                    </fo:block>
                                    <fo:block margin="6px">
                                        <fo:inline font-weight="bold">
                                            <xsl:value-of select="result/rps/cfop_cfop" /> - <xsl:value-of select="result/rps/cfop_descricao" />
                                        </fo:inline>
                                    </fo:block>
                                </fo:table-cell>
                            </fo:table-row>

                            <fo:table-row>
                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">Deduções (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">Desconto (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">Base de Cálculo (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">Alíquota (%)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">5,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">Valor do ISS (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">
                                        <xsl:value-of select="format-number(sum(exslt:node-set($valorTotal)/value) * 0.05, '###,###.00')" />
                                    </fo:inline></fo:block>
                                </fo:table-cell>

                            </fo:table-row>

                            <fo:table-row>
                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">IR (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">INSS (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">CSLL (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">PIS (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>

                                <fo:table-cell border="solid black 1px" font-size="8px">
                                    <fo:block margin="4px">COFINS (R$)</fo:block>
                                    <fo:block margin="4px" text-align="right"><fo:inline font-weight="bold">0,00</fo:inline></fo:block>
                                </fo:table-cell>
                            </fo:table-row>


                            <fo:table-row>
                                <fo:table-cell number-columns-spanned="5" border="solid black 1px">
                                    
                                    <fo:block font-weight="bold" margin="4px" text-align="center">
                                        OUTRAS INFORMAÇÕES
                                    </fo:block>

                                    <fo:block  margin-bottom="2.5cm" font-size="8px">
                                        
                                    </fo:block>

                                </fo:table-cell>
                            </fo:table-row>



                        </fo:table-body>
                    </fo:table>

                </fo:flow>
            </fo:page-sequence>
        </fo:root>
    </xsl:template>
</xsl:stylesheet>
