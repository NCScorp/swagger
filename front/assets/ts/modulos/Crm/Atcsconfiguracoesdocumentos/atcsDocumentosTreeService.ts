import angular = require('angular');
import { IAtcConfiguracaoDocumento, EnumTipoGeracaoPrestadora, EnumTipoGeracaoSeguradora, EnumTipoDocumentoItem, EnumTipoGeracaoRPS, IAtcConfiguracaoDocumentoItem } from './classes';
import { montarNoTree } from '../../Commons/utils';
import { ICliente } from '../Atcs/interfaces';
import { CrmAtcsconfiguracoesdocumentos } from './factory';

/**
 * Código do relatório de documento RPS.
 * Deve ser apresentado independente de haver fornecedor envolvido
 */
const CODIGO_REL_RPS = 'REL_ATC_TO_RPS';

export class AtcsDocumentosTreeService {
    static $inject = [
        '$http',
        'nsjRouting',
        'CrmAtcsconfiguracoesdocumentos'
    ];

    constructor(
        private $http: any,
        private nsjRouting: any,
        private CrmAtcsconfiguracoesdocumentos: CrmAtcsconfiguracoesdocumentos
    ) {}

    /**
     * Retorna o array referente a árvore de documentos
     */
    public getListaArvore(
        atc: any, 
        configuracao: IConfigGetListaArvore = {
            tipoDocumento: EnumTipoDocumentoRetornado.tdrAmbos,
            arrPrestadores: [],
            acoesItem: []
        }
    ): Promise<{
        configuracao: IAtcConfiguracaoDocumento,
        arrayTree: any[]
    }> {
        // Lista de itens da arvore a ser retornada
        let arrArvoreEmLinha: any[] = [];
        
        // Lista de fornecedores envolvidos do atendimento
        let arrForcedoresEnvolvidos: IFornecedorEnvolvido[] = [];
        // Configuração de geração de documentos no atendimento
        let config: IAtcConfiguracaoDocumento = null;

        let responsabilidadesFinanceiras: any = null;
        let clientesResponsaveisFinanceiros: any = null;

        return new Promise(async (resolve, reject) => {
            try {
                // Busco configuração de documentos do atendimento e fornecedores envolvidos
                [
                    config,
                    arrForcedoresEnvolvidos,
                    responsabilidadesFinanceiras,
                ] = await Promise.all([
                    this.getConfiguracaoFromApi(),
                    this.getFornecedoresEnvolvidosFromApi(atc.negocio),
                    this.getResponsabilidadesFinanceirasFromAPI(atc.negocio)
                ]);

                let arrayContratos = [];

                // montagem da listagem de contratos para documentos RPS
                if(atc.contratotaxaadm != null){
                    arrayContratos.push({
                        'contrato': atc.contratotaxaadm.contrato,
                        'responsabelFinanceiro': atc.cliente.cliente,
                        'cliente': atc.cliente,
                        'tipo': 1 //taxaadm
                    });
                }

                let arrayPromisesContratosComRps = [];
                responsabilidadesFinanceiras.forEach(respFin => {
                    respFin.responsabilidadesfinanceirasvalores.forEach(respFinValores => {
                        if(respFinValores.contrato != null) {
                            let contratoExistente = arrayContratos.find(element => {
                                return element.contrato == respFinValores.contrato;
                            });
                            //não insere caso o contrato já esteja lá.
                            if(contratoExistente === undefined) {
                                let clienteResponsavel = atc.responsaveisfinanceiros.find(element => {
                                    return element.responsavelfinanceiro.cliente == respFinValores.responsavelfinanceiro;
                                });
                                //pegando os dados reais do contrato, para verificar se o mesmo emite nota fiscal e possui numero de rps
                                arrayPromisesContratosComRps.push(this.getContratosFromAPI(respFinValores.contrato));
                                arrayContratos.push({
                                    'contrato': respFinValores.contrato,
                                    'responsabelFinanceiro': respFinValores.responsavelfinanceiro,
                                    'cliente': clienteResponsavel.responsavelfinanceiro,
                                    'tipo': 2 //contrato comum
                                });
                            }
                        }
                    });
                });

                let contratosComRps = await Promise.all(arrayPromisesContratosComRps);
                contratosComRps.forEach(contratoComRps => {
                    //se o contrato não tem rps e não está marcado para emitir nota...
                    if ( contratoComRps.emitirnotafiscal == false && contratoComRps.numerorps == null) {
                        //filtro somente os que são diferentes dele, para ter um array sem ele.
                        let novoArray = arrayContratos.filter(contrato => {
                            if(contrato.contrato !== contratoComRps.contrato){
                                return true;
                            } else {
                                return false;
                            }
                        });
                        arrayContratos = novoArray;
                    }
                });

                // Se foi passado lista de prestadores para gerar documentos, filtro fornecedores envolvidos
                if (arrForcedoresEnvolvidos.length > 0 && configuracao.arrPrestadores.length > 0) {
                    arrForcedoresEnvolvidos = arrForcedoresEnvolvidos.filter((fornecedorFilter) => {
                        return configuracao.arrPrestadores.indexOf(fornecedorFilter.fornecedor.fornecedor) > -1;
                    })
                }
    
                // Documentos da Prestadora
                if (
                    config.tipogeracaoprestadora != EnumTipoGeracaoPrestadora.tgpNaoGerar && 
                    [EnumTipoDocumentoRetornado.tdrAmbos, EnumTipoDocumentoRetornado.tdrPrestadora].indexOf(configuracao.tipoDocumento) > -1
                ) {
                    // Busco filhos
                    let arrItens = config.atcsconfiguracoesdocumentositens.filter((configDocItemFilter) => {
                        return configDocItemFilter.tipo == EnumTipoDocumentoItem.tdiPrestadora
                    });

                    // Só crio nó pai se houver filhos
                    if (arrItens.length > 0 && arrForcedoresEnvolvidos.length > 0) {
                        // Adiciono Nó Pai
                        let novoNo = this.obterNovoNode('paiprestadora', {}, null, false);
                        novoNo.nome = 'Documentos da Prestadora';
                        arrArvoreEmLinha.push(
                            novoNo
                        );
    
                        // Adiciono filhos (Itens do tipo prestadora)
                        arrItens.forEach((configDocItemForeach) => {
    
                            // Adiciono Nó do Tipo de Documento
                            let novoNo = this.obterNovoNode('atcconfiguracaodocumentoitem', configDocItemForeach, 'paiprestadora01', false);
                            novoNo.nome = configDocItemForeach.documentofop.nomedocumento;
                            arrArvoreEmLinha.push(
                                novoNo
                            );
    
                            // Adiciono linhas dos fornecedores
                            if (config.tipogeracaoprestadora == EnumTipoGeracaoPrestadora.tgpGerarUmPorPrestador) {
                                arrForcedoresEnvolvidos.forEach((fornecedorEnvolvidoForeach, index) => {
                                    const entity: ILinhaTreeItemDocumento = {
                                        nome: fornecedorEnvolvidoForeach.fornecedor.nomefantasia + '.pdf',
                                        fornecedorEnvolvido: fornecedorEnvolvidoForeach,
                                        codigoDocumento: configDocItemForeach.documentofop.codigodocumento,
                                        documentofop: configDocItemForeach.documentofop,
                                        atcsconfiguracoesdocumentositens: configDocItemForeach
                                    }
                                    arrArvoreEmLinha.push(
                                        this.obterNovoNode('itemdocumento', entity, 'atcconfiguracaodocumentoitem' + configDocItemForeach.atcconfiguracaodocumentoitem, false, configuracao.acoesItem)
                                    );
                                });
                            }
                        });
                    }
                }

                // Documentos da Seguradora
                if (
                    config.tipogeracaoseguradora != EnumTipoGeracaoSeguradora.tgpNaoGerar && atc.possuiseguradora && 
                    [EnumTipoDocumentoRetornado.tdrAmbos, EnumTipoDocumentoRetornado.tdrSeguradora].indexOf(configuracao.tipoDocumento) > -1
                ) {
                    // Busco filhos
                    let arrItens = config.atcsconfiguracoesdocumentositens.filter((configDocItemFilter) => {
                        return configDocItemFilter.tipo == EnumTipoDocumentoItem.tdiSeguradora
                    })

                    // Só crio nó pai se houver filhos
                    if (arrItens.length > 0 && arrForcedoresEnvolvidos.length > 0) {
                        // Adiciono Nó Pai
                        let novoNo = this.obterNovoNode('paiseguradora', {}, null, false);
                        novoNo.nome = 'Documentos da Seguradora';
                        arrArvoreEmLinha.push(
                            novoNo
                        );
    
                        // Adiciono filhos (Itens do tipo seguradora)
                        arrItens.forEach((configDocItemForeach) => {
                            
                            // Adiciono Nó do Tipo de Documento
                            let novoNo = this.obterNovoNode('atcconfiguracaodocumentoitem', configDocItemForeach, 'paiseguradora01', false);
                            novoNo.nome = configDocItemForeach.documentofop.nomedocumento;
                            arrArvoreEmLinha.push(
                                novoNo
                            );
    
                            // Adiciono linhas dos fornecedores
                            if (config.tipogeracaoseguradora == EnumTipoGeracaoSeguradora.tgpGerarUmPorPrestador) {

                                if(configDocItemForeach.documentofop.codigodocumento == "REL_ATC_TO_SEGURADORA_APROVACAO"){
                                    const entity: ILinhaTreeItemDocumento = {
                                        nome: `${atc.cliente.nomefantasia}.pdf`,
                                        fornecedorEnvolvido: arrForcedoresEnvolvidos[0],
                                        seguradora: null, // Colocar do atendimento
                                        codigoDocumento: configDocItemForeach.documentofop.codigodocumento,
                                        documentofop: configDocItemForeach.documentofop,
                                        atcsconfiguracoesdocumentositens: configDocItemForeach
                                    }
                                    
                                    arrArvoreEmLinha.push(
                                        this.obterNovoNode('itemdocumento', entity, 'atcconfiguracaodocumentoitem' + configDocItemForeach.atcconfiguracaodocumentoitem, false, configuracao.acoesItem)
                                    );
                                } else {
                                    arrForcedoresEnvolvidos.forEach((fornecedorEnvolvidoForeach, index) => {
                                        const entity: ILinhaTreeItemDocumento = {
                                            nome: `${atc.cliente.nomefantasia} - ${fornecedorEnvolvidoForeach.fornecedor.nomefantasia}.pdf`,
                                            fornecedorEnvolvido: fornecedorEnvolvidoForeach,
                                            seguradora: null, // Colocar do atendimento
                                            codigoDocumento: configDocItemForeach.documentofop.codigodocumento,
                                            documentofop: configDocItemForeach.documentofop,
                                            atcsconfiguracoesdocumentositens: configDocItemForeach
                                        }
                                        
                                        arrArvoreEmLinha.push(
                                            this.obterNovoNode('itemdocumento', entity, 'atcconfiguracaodocumentoitem' + configDocItemForeach.atcconfiguracaodocumentoitem, false, configuracao.acoesItem)
                                        );
                                    });
                                }
                            }
                        });
                    }

                    // DOCUMENTOS RPS
                    let arrItensRPS = config.atcsconfiguracoesdocumentositens.filter((configDocItemFilter) => {
                        return configDocItemFilter.tipo == EnumTipoDocumentoItem.tdiRPS
                    })

                    if(arrItensRPS.length > 0){
                        // Adiciono Nó Pai
                        let novoNo = this.obterNovoNode('pairps', {}, null, false);
                        novoNo.nome = 'Documentos RPS';
                        arrArvoreEmLinha.push(
                            novoNo
                        );

                        // Adiciono filhos (Itens do tipo rps)
                        arrItensRPS.forEach((configDocItemForeach) => {
                            // Adiciono Nó do Tipo de Documento
                            let novoNo = this.obterNovoNode('atcconfiguracaodocumentoitem', configDocItemForeach, 'pairps01', false);
                            novoNo.nome = configDocItemForeach.documentofop.nomedocumento;
                            arrArvoreEmLinha.push(
                                novoNo
                            );

                            // Adiciono linhas dos fornecedores
                            if (config.tipogeracaoseguradora == EnumTipoGeracaoSeguradora.tgpGerarUmPorPrestador) {
                                const entity: ILinhaTreeItemDocumento = {
                                    nome: '',
                                    fornecedorEnvolvido: null,
                                    seguradora: null, // Colocar do atendimento
                                    codigoDocumento: configDocItemForeach.documentofop.codigodocumento,
                                    documentofop: configDocItemForeach.documentofop,
                                    atcsconfiguracoesdocumentositens: configDocItemForeach
                                }
                                arrayContratos.forEach(contrato => {
                                    entity.nome = `RPS - ${contrato.cliente.nomefantasia}.pdf`;
                                    if(contrato.tipo == 1){
                                        entity.nome = `RPS Tx Adm - ${contrato.cliente.nomefantasia}.pdf`;
                                    }
                                    entity['contrato'] = contrato;
                                    arrArvoreEmLinha.push(
                                        this.obterNovoNode('itemdocumento', entity, 'atcconfiguracaodocumentoitem' + configDocItemForeach.atcconfiguracaodocumentoitem, false, configuracao.acoesItem, contrato.contrato)
                                    );
                                });
                            }
                        });
                    }
                }
    
                resolve({
                    configuracao: config,
                    arrayTree: arrArvoreEmLinha
                });
            } catch (err) {
                reject(err)
            }
        });
    }

    /**
     * Busca a configuração de documentos
     */
    private getConfiguracaoFromApi(): Promise<IAtcConfiguracaoDocumento> {
        return new Promise((resolve, reject) => {
            this.CrmAtcsconfiguracoesdocumentos.getListaFromApi().then((lista) => {
                if (lista.length > 0) {
                    this.CrmAtcsconfiguracoesdocumentos.get(lista[0].atcconfiguracaodocumento).then((dados: any) => {
                        dados.tipogeracaoprestadora = dados.tipogeracaoprestadora.toString();
                        dados.tipogeracaoseguradora = dados.tipogeracaoseguradora.toString();
                        resolve(dados);
                    }).catch((error) => {
                        reject(error);
                    });
                } else {
                    resolve(null);
                }
            }).catch((err) => {
                reject(err);
            });
        });
    }

    getContratosFromAPI(contrato) {
        let constructors = {
            'id': contrato,
        };
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('financas_contratos_get', constructors, true)
            }).then(async (response: any) => {
                const arrDados: any[] = response.data;
                resolve(arrDados);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    getResponsabilidadesFinanceirasFromAPI(negocio): Promise<any[]> {
        let constructors = {
            'negocio': negocio,
        };
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_responsabilidadesfinanceiras_index', constructors, true)
            }).then(async (response: any) => {
                const arrDados: any[] = response.data;
                arrDados.forEach((responsabilidadefinanceira) => {
                    responsabilidadefinanceira.responsabilidadesfinanceirasvalores.forEach((responsabilidadefinanceiravalor) => {
                        responsabilidadefinanceiravalor.valorpagar = parseFloat(responsabilidadefinanceiravalor.valorpagar.toString());
                    });
                });
                resolve(arrDados);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }
    
    /**
     * Busca lista de fornecedores envolvidos no atendimento
     */
    private getFornecedoresEnvolvidosFromApi(atc: string): Promise<IFornecedorEnvolvido[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_fornecedoresenvolvidos_index', { 'negocio': atc }, true, true)
            }).then((dados: any) => {
                resolve(dados.data);
            }).catch((error) => {
                reject(error);
            });
        });
    }

    /**
    * @description Criação de novo nó e suas ações
    * @param tipo 
    * @param entity 
    * @param pai 
    */
   private obterNovoNode(tipo, entity, pai, adicionarNovoNo, acoes: IAcaoNode[] = [], customId: string = '') {
       entity.adicionarNovo = adicionarNovoNo;
       const icon = 'fas fa-minus';
       const entityId = (customId == '') ? this.obterIdEntity(tipo, entity) : customId;
       const actions = acoes;
       const noNovo = angular.copy(montarNoTree(tipo, entity, entityId, pai, '', actions, icon));
       
       return noNovo;
   }

   /**
     * Verifica o tipo do identificador e retorna o id
     * @param identificador 
     * @param entity 
     */
    private obterIdEntity(identificador, entity) {
        switch (identificador) {
            case 'paiprestadora': return '01';
            case 'pairps': return '01';
            case 'paiseguradora': return '01';
            case 'atcconfiguracaodocumentoitem': return entity.atcconfiguracaodocumentoitem;
            case 'itemdocumento': return entity.fornecedorEnvolvido.fornecedorenvolvido;
        }
    }
}

interface IFornecedorEnvolvido {
    fornecedor?: IFornecedor;
}

interface IFornecedor {
    fornecedor: string;
    nomefantasia?: string;
}

export interface ILinhaTreeItemDocumento {
    fornecedorEnvolvido: IFornecedorEnvolvido;
    seguradora?: ICliente;
    codigoDocumento: string;
    documentofop: any;
    nome: string;
    contrato?: any;
    atcsconfiguracoesdocumentositens: any

    /**
     * Utilizado na tela de envio de e-mails
     */
    checked?: boolean;
}

/**
 * Utilizado pra definir os tipos de documentos que serão retornados
 */
export enum EnumTipoDocumentoRetornado {
  tdrPrestadora,
  tdrSeguradora,
  tdrAmbos
}

export interface IConfigGetListaArvore {
  /**
   * Tipo de documento que será retornado na lista
   */
  tipoDocumento: EnumTipoDocumentoRetornado;
  /**
   * Lista de prestadores para montar a lista.
   * Caso esteja vazia, buscar todos os fornecedores envolvidos
   */
  arrPrestadores: string[];
  /**
   * Lista de ações do nó de item
   */
  acoesItem: IAcaoNode[];
}

export interface IAcaoNode {
    label?: string;
    icon?: string;
    size?: string;
    color?: string;
    acao: (node: TypeNode) => void,
    isVisible?: (node: TypeNode) => boolean,
}
