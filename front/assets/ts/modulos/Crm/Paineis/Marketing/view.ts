import angular = require('angular');
import { ICtrlPainel, IPainelFiltros } from '../view';
import { CrmPainelMarketing } from './factory';
import { IPainelMarketingMesQualificacaoApi, IPainelMarketingMesApi, IPainelMarketingApi, EnumPainelMarketingClassificadorEntidadeApi, IPainelMarketingClassificadorApi } from './interfaces.api';
import { EnumClienteReceitaAnual } from '../../Negocios/classes';

export class CrmPainelMarketingController {
    /**
     * Lista de injeções de dependência da tela
     */
    static $inject = [
        '$scope',
        'toaster',
        'moment',
        'CrmPainelMarketing'
    ];

    /**
     * Define se a tela está ocupada executando algum processo
     */
    private busy: boolean = false;
    /**
     * Utilizado para o pai controlar o painel de marketing
     */
    private ctrlPainel: ICtrlPainel;
    /**
     * Painel referente aos dados da tela
     */
    private painelTela: PainelTela = null;
    /**
     * Define se o painel vai apresentar os dados de qualificação, desqualificação ou os dois
     */
    private tipoDadosQualificacao: EnumTipoDadosQualificacaoTela = EnumTipoDadosQualificacaoTela.tdqtTodos;
    /**
     * Defino enum para utiliza-lo na view
     */
    private EnumTipoDadosQualificacaoTela = EnumTipoDadosQualificacaoTela;
    /**
     * Utilizado para apresentar dados dos classificadores na tela
     */
    private arrPaineisQualificacao: {
        painel: DadosClassificadoresTela,
        nome: string
    }[] = [];
    /**
     * Filtro por campanha de origem
     */
    private filtroCampanha: any = null;
    
    constructor (
        private $scope: any,
        private toaster: any,
        private moment: any,
        private CrmPainelMarketing: CrmPainelMarketing
    ) {
        this.arrPaineisQualificacao.push({painel: null, nome: 'Para qualificação'});
        this.arrPaineisQualificacao.push({painel: null, nome: 'Qualificados'});
        this.arrPaineisQualificacao.push({painel: null, nome: 'Desqualificados'});
    }

    /**
     * Função chamada ao iniciar a tela
     */
    $onInit(){
        this.ctrlPainel.fnCarregarPainel = (filtros) => {
            this.carregarPainel(filtros);
        }
    }

    /**
     * Retorna se a tela está ocupada executando algum processo
     */
    isBusy() {
        return this.busy;
    }

    /**
     * Atualiza o escopo da tela
     */
    reloadScope(){
        this.$scope.$applyAsync();
    }

    /**
     * Carrega o painel
     * @param filtros 
     */
    carregarPainel(filtrosPainel: IPainelFiltros){
        // Monto filtro para buscar o painel
        let filtros: any = {
            datainicio: filtrosPainel.dataInicial,
            datafinal: filtrosPainel.dataFinal,
            campanha: (this.filtroCampanha != null && this.filtroCampanha.promocaolead != null) ? 
                this.filtroCampanha.promocaolead : null
        };

        // Valido se as datas foram preenchidas
        if (filtrosPainel.dataInicial == null || 
            filtrosPainel.dataInicial == '' || 
            filtrosPainel.dataFinal == null ||
            filtrosPainel.dataFinal == ''
        ){
            this.toaster.pop({
                type: 'error',
                title: 'Filtros de data inicial e final são obrigatórios!',
            });

            return;
        }

        // Crio objetos de data inicial e final
        const objDataInicial = this.moment(filtrosPainel.dataInicial);
        const objDataFinal = this.moment(filtrosPainel.dataFinal);
        
        // Se o período entre as datas for maior que 12 meses
        if (objDataFinal.diff(objDataInicial, 'months', true) >= 12) {
            this.toaster.pop({
                type: 'error',
                title: 'A diferença entre as datas inicial e final deve ser de no máximo 12 meses.',
            });

            return;
        }

        // Ativo o loading
        this.busy = true;
        this.reloadScope();

        // Busco dados
        this.CrmPainelMarketing.get(filtros).then((dados) => {
            this.painelTela = new PainelTela(dados);

            this.arrPaineisQualificacao = [];
            this.arrPaineisQualificacao.push({painel: this.painelTela.dadosParaQualificacao, nome: 'Para qualificacao'});
            this.arrPaineisQualificacao.push({painel: this.painelTela.dadosQualificados, nome: 'Qualificados'});
            this.arrPaineisQualificacao.push({painel: this.painelTela.dadosDesqualificados, nome: 'Desqualificados'});
        }).catch((error) => {
            // Caso dê erro, lanço mensagem de erro
            this.toaster.pop({
                type: 'error',
                title: (error.message != undefined) ? error.message : 'Não foi possível buscar dados de qualificação.',
            });
        }).finally(() => {
            // Desativo o loading
            this.busy = false;
            this.reloadScope();
        });
    }

    /**
     * Retorna a descrição de acordo com o anomes passado no formato 'MES/ANO'
     * @param mes 
     */
    getMesDescricao(anomes: string): string {
        const mes = parseInt( anomes.substr(4) );
        const ano = anomes.substr(0, 4);
        let retorno = '';

        switch (mes) {
            case 1: retorno = 'Jan'; break;
            case 2: retorno = 'Fev'; break;
            case 3: retorno = 'Mar'; break;
            case 4: retorno = 'Abr'; break;
            case 5: retorno = 'Mai'; break;
            case 6: retorno = 'Jun'; break;
            case 7: retorno = 'Jul'; break;
            case 8: retorno = 'Ago'; break;
            case 9: retorno = 'Set'; break;
            case 10: retorno = 'Out'; break;
            case 11: retorno = 'Nov'; break;
            case 12: retorno = 'Dez'; break;
            default: return '';
        }

        return retorno += '/' + ano;
    }
}

class PainelTela {
    totalCriados: number = 0;
    taxaConversao: number = 0;
    listaQualificacao: IPainelMarketingMesQualificacaoApi[] = [];
    meses: PainelMesTela[] = [];
    /**
     * Dados de classificadores do accordion 'Para qualificacao'
     */
    dadosParaQualificacao: DadosClassificadoresTela = null;
    /**
     * Dados de classificadores do accordion 'Qualificados'
     */
    dadosQualificados: DadosClassificadoresTela = null;
    /**
     * Dados de classificadores do accordion 'Desqualificacao'
     */
    dadosDesqualificados: DadosClassificadoresTela = null;

    constructor(
        private painelApi: IPainelMarketingApi
    ){
        // Organizo dados dos meses
        this.organizarMeses();
        // Organizo dados dos classificadores
        this.organizarClassificadores();
    }

    /**
     * Organiza dados dos meses de acordo com o retorno do painel
     */
    private organizarMeses(){
        this.totalCriados = 0;
        this.taxaConversao = 0;
        this.meses = [];
        this.listaQualificacao = [];
        
        let arrAnoMes: string[] = [];

        this.painelApi.meses.forEach((mesForeach, index) => {
            // Utilizo o primeiro indice, pois sempre tem todos os meses buscados
            if (index == 0) {
                mesForeach.listaqualificacao.forEach((qualificacaoForeach) => {
                    arrAnoMes.push(qualificacaoForeach.anomes);
                });
            }

            // Somo total de negócios criados
            this.totalCriados += mesForeach.negociostotais;

            // Calculo taxa de conversão do mês
            let taxaConversao = this.getTaxaConversao(mesForeach.negociostotais, mesForeach.listaqualificacao);

            // Adiciono informação do mês
            this.meses.push( new PainelMesTela(taxaConversao, mesForeach, arrAnoMes) );

            // Contabilizo dados de qualificação totais
            this.contabilizarQualificacao(mesForeach.listaqualificacao);
        });

        // Calculo taxa de conversão total
        this.taxaConversao = this.getTaxaConversao(this.totalCriados, this.listaQualificacao);
    }

    /**
     * Contabilizo dados de qualificação nos totais
     * @param listaQualificacao 
     */
    private contabilizarQualificacao(listaQualificacao: IPainelMarketingMesQualificacaoApi[]){
        listaQualificacao.forEach((qualificacaoForeach) => {
            const index = this.listaQualificacao.findIndex((qualificacaoFindIndex) => {
                return qualificacaoFindIndex.anomes == qualificacaoForeach.anomes;
            });

            let itemQualificacao: IPainelMarketingMesQualificacaoApi = {
                anomes: qualificacaoForeach.anomes,
                negociosqualificados: qualificacaoForeach.negociosqualificados,
                negociosdesqualificados: qualificacaoForeach.negociosdesqualificados
            };

            if (index > -1) {
                this.listaQualificacao[index].negociosqualificados += itemQualificacao.negociosqualificados;
                this.listaQualificacao[index].negociosdesqualificados += itemQualificacao.negociosdesqualificados;
            } else {
                this.listaQualificacao.push(itemQualificacao);
            }
        });
    }

    /**
     * Retorna a taxa de conversão de um determinado mês
     * @param totalCriados 
     * @param listaQualificacao 
     */
    private getTaxaConversao(totalCriados: number, listaQualificacao: IPainelMarketingMesQualificacaoApi[]): number{
        let taxaConversao: number = 0;
        let totalQualificados: number = 0;
        listaQualificacao.forEach((mesQualificacaoForeach) => {
            totalQualificados += mesQualificacaoForeach.negociosqualificados;
        });
        
        if (totalCriados > 0) {
            // Calculo a taxa de conversão
            taxaConversao = (totalQualificados / totalCriados) * 100;
            // Deixo a taxa de conversão com 2 casas decimais
            taxaConversao = parseFloat( taxaConversao.toFixed(2) );
        }
        
        return taxaConversao;
    }

    /**
     * Organiza dados dos classificadores de acordo com o retorno do painel
     */
    private organizarClassificadores(){
        // Monto dados do accortion de classificadores 'Para qualificação'
        this.dadosParaQualificacao = new DadosClassificadoresTela(
            this.painelApi.classificadores,
            EnumTipoDadosClassificadorTela.tdctParaQualificacao
        );

        // Monto dados do accortion de classificadores 'Para qualificação'
        this.dadosQualificados = new DadosClassificadoresTela(
            this.painelApi.classificadores,
            EnumTipoDadosClassificadorTela.tdctQualificados
        );

        // Monto dados do accortion de classificadores 'Para qualificação'
        this.dadosDesqualificados = new DadosClassificadoresTela(
            this.painelApi.classificadores,
            EnumTipoDadosClassificadorTela.tdctDesqualificados
        );
    }
}

class PainelMesTela {
    public listaQualificacao: IPainelMesQualificacaoTela[] = [];

    constructor(
        public taxaConversao: number,
        public mes: IPainelMarketingMesApi,
        arrAnoMes: string[] = []
    ) {
        arrAnoMes.forEach((anomes) => {
            let qualificacao = this.getMesQualificacao(anomes);

            let qualificacaoTela:IPainelMesQualificacaoTela  = {
                anomes: anomes,
                qualificacao: qualificacao
            };
            this.listaQualificacao.push(qualificacaoTela);
        })
    }

    /**
     * Retorna o mês de qualificacao, de acordo com o ano mes passado
     * @param anomes 
     */
    getMesQualificacao(anomes: string):IPainelMarketingMesQualificacaoApi {
        return this.mes.listaqualificacao.find((qualificacaoFind) => {
            return qualificacaoFind.anomes == anomes;
        });
    }
}

interface IPainelMesQualificacaoTela {
    anomes: string;
    qualificacao?: IPainelMarketingMesQualificacaoApi;
}

enum EnumTipoDadosQualificacaoTela {
    tdqtQualificados,
    tdqtDesqualificados,
    tdqtTodos
}

class DadosClassificadoresTela {
    classificadoresPorEntidade: IClassificadoresEntidadesTela[] = [];
    filtros: IClassificadorFiltroTela[] = [];
    arrDadosPorGrupo: IDadosClassificadoresGrupoTela[] = [];

    constructor(
        arrClassificadores: IPainelMarketingClassificadorApi[],
        private tipoDados: EnumTipoDadosClassificadorTela
    ){
        // Defino lista de entidades que o tipo de accordion suporta
        let arrEntidades: EnumPainelMarketingClassificadorEntidadeApi[] = [
            EnumPainelMarketingClassificadorEntidadeApi.pmceCampanhaorigem,
            EnumPainelMarketingClassificadorEntidadeApi.pmceMidiaorigem,
            EnumPainelMarketingClassificadorEntidadeApi.pmceTipoacionamento,
            EnumPainelMarketingClassificadorEntidadeApi.pmceEstabelecimento,
            EnumPainelMarketingClassificadorEntidadeApi.pmceAreanegocio,
            EnumPainelMarketingClassificadorEntidadeApi.pmceAtribuicao,
            EnumPainelMarketingClassificadorEntidadeApi.pmceSegmentoatuacao,
            EnumPainelMarketingClassificadorEntidadeApi.pmceFaturamentoanual,
            EnumPainelMarketingClassificadorEntidadeApi.pmceEhcliente,
            EnumPainelMarketingClassificadorEntidadeApi.pmceCargo,
        ];

        switch (tipoDados) {
            case EnumTipoDadosClassificadorTela.tdctQualificados: {
                arrEntidades.push(EnumPainelMarketingClassificadorEntidadeApi.pmceQualificadoem);
                break;
            }
            case EnumTipoDadosClassificadorTela.tdctDesqualificados: {
                arrEntidades.push(EnumPainelMarketingClassificadorEntidadeApi.pmceMotivodesqualificacao);
                break;
            }
        }
        
        // Percorro tipos de entidade para montar estruturas de dados
        arrEntidades.forEach((entidadeForeach) => {
            // Filtro classificadores da mesma entidade
            const classificadores = arrClassificadores.filter((classificadorFilter) => {
                return classificadorFilter.entidade == entidadeForeach;
            });

            // Monto lista do agrupador de classificadores da mesma entidade
            const classificadorEntidadeTela: IClassificadoresEntidadesTela = {
                entidade: entidadeForeach,
                classificadores: [],
                filtroAtivado: false,
                idFiltrado: [],
                outrosFiltrado: false
            }

            // Adiciono classificadores a lista
            classificadores.forEach((classificadorForeach) => {
                // Ajusto descrição dos valores de faturamento anual
                if (entidadeForeach == EnumPainelMarketingClassificadorEntidadeApi.pmceFaturamentoanual) {
                    if (typeof classificadorForeach.id == 'string'){
                        classificadorForeach.id = parseInt(classificadorForeach.id);
                    }

                    switch (classificadorForeach.id) {
                        case EnumClienteReceitaAnual.craAte5Milhoes: {
                            classificadorForeach.nome = 'Até R$ 5 mi'; break;
                        }
                        case EnumClienteReceitaAnual.craAte30Milhoes: {
                            classificadorForeach.nome = 'Até R$ 30 mi'; break;
                        }
                        case EnumClienteReceitaAnual.craAte100Milhoes: {
                            classificadorForeach.nome = 'Até R$ 100 mi'; break;
                        }
                        case EnumClienteReceitaAnual.craAte300Milhoes: {
                            classificadorForeach.nome = 'Até R$ 300 mi'; break;
                        }
                        case EnumClienteReceitaAnual.craAte500Milhoes: {
                            classificadorForeach.nome = 'Até R$ 500 mi'; break;
                        }
                        case EnumClienteReceitaAnual.craAte1Bilhao: {
                            classificadorForeach.nome = 'Até R$ 1 bi'; break;
                        }
                        case EnumClienteReceitaAnual.craMaisDe1Bilhao: {
                            classificadorForeach.nome = 'Mais de R$ 1 bi'; break;
                        }
                    }
                }

                const classificadorTela: IClassificadorTela = {
                    classificador: classificadorForeach,
                    qtd: this.getClassificadorQuantidade(classificadorForeach)
                };

                classificadorEntidadeTela.classificadores.push(classificadorTela);
            });

            // Adiciono grupo de classificadores da entidade a lista
            this.classificadoresPorEntidade.push(classificadorEntidadeTela);
        });

        // Organiza dados por grupo de informação
        this.organizarDadosPorGrupos();
    }

    /**
     * Chamada quando um classificador da entidade for clicado.
     * Adiciono ou removo filtros desse classificador
     * @param classificador 
     */
    public onClassificadorClick(classificador: IDadosClassificadoresGrupoEntidadeClassificadorTela){
        // Verifico se possui filtro para a entidade
        const filtroIndex = this.filtros.findIndex((filtroIndex) => {
            return filtroIndex.entidade == classificador.tipo;
        });

        let mesmoRegistro = false;
        let novoFiltro: IClassificadorFiltroTela = {
            entidade: classificador.tipo,
            // outros: classificador.outros,
            // id: classificador.id,
            // negocios: [],
            itens: []
        }

        let novoFiltroItem: IClassificadorFiltroItemTela = {
            outros: classificador.outros,
            id: classificador.id,
            negocios: []
        }

        // Caso possua filtro para a entidade, verifico se é referente ao mesmo ID
        if (filtroIndex > -1) {
            let itemIndex = -1;
            // NOVO
            if (!classificador.outros) {
                itemIndex = this.filtros[filtroIndex].itens.findIndex((itemFindIndex) => {
                    return itemFindIndex.id == classificador.id;
                });
            } else {
                itemIndex = this.filtros[filtroIndex].itens.findIndex((itemFindIndex) => {
                    return itemFindIndex.outros
                });
            }

            mesmoRegistro = itemIndex > -1;

            if (mesmoRegistro) {
                // Removo item de filtro
                this.filtros[filtroIndex].itens.splice(itemIndex, 1);

                // Se não tem mais itens a filtrar, removo filtro
                if (this.filtros[filtroIndex].itens.length == 0) {
                    this.filtros.splice(filtroIndex, 1);
                }
            }

            // VELHO
            // if (!classificador.outros) {
            //     mesmoRegistro = this.filtros[filtroIndex].id == classificador.id;
            // } else {
            //     mesmoRegistro = this.filtros[filtroIndex].outros;
            // }

            // Se for o mesmo registro, removo o filtro
            // if (mesmoRegistro) {
            //     this.filtros.splice(filtroIndex, 1);
            // }
        }

        // Se o classificador não tem filtro, ou tem filtro e não é o mesmo registro: Preencho dados do novo filtro
        if (filtroIndex == -1 || !mesmoRegistro) {
            const arrClassificadores = this.classificadoresPorEntidade.find((classificadorEntidadeFind) => {
                return classificadorEntidadeFind.entidade == novoFiltro.entidade;
            }).classificadores.filter((classificadorFind) => {
                // Retorno true se o classificador for do tipo outros e um de seus ids for igual ao id do item do find,
                // Ou se não for do tipo outros e o id do classificador for igual ao id do item do find
                return (classificador.outros && classificador.arrIds.indexOf(classificadorFind.classificador.id) > -1) ||
                    (!classificador.outros && classificador.id == classificadorFind.classificador.id);
            });

            // Adiciono negócios ao filtro
            arrClassificadores.forEach((classificadorForeach) => {
                novoFiltroItem.negocios = novoFiltroItem.negocios.concat(this.getClassificadorNegocios(classificadorForeach.classificador));
                // novoFiltro.negocios = novoFiltro.negocios.concat(this.getClassificadorNegocios(classificadorForeach.classificador));
            });

            // Se o filtro não existia, adiciono a lista
            if (filtroIndex == -1) {
                novoFiltro.itens.push(novoFiltroItem);
                this.filtros.push(novoFiltro);
            } 
            // Senão, adiciono novo item ao filtro filtro
            else {
                this.filtros[filtroIndex].itens.push(novoFiltroItem);
            }
        }

        // Chamo função que realiza filtro dos dados
        this.filtrarDados();

        // Chamo função que agrupas as informaçõse por grupos de dados
        this.organizarDadosPorGrupos();
    }

    /**
     * Realiza o filtro das informações
     */
    private filtrarDados(){
        this.classificadoresPorEntidade.forEach((classificadorEntidadeForeach) => {
            classificadorEntidadeForeach.classificadores.forEach((classificadorForeach) => {
                // Negócios que serão filtrados e utilizados para calcular o total
                let arrNegociosFiltrados = this.getClassificadorNegocios(classificadorForeach.classificador);
                // Limpo dados de filtro do grupo de classificadores
                classificadorEntidadeForeach.filtroAtivado = false;
                classificadorEntidadeForeach.idFiltrado = [];
                classificadorEntidadeForeach.outrosFiltrado = false;
                let continuarFiltrando = true;

                this.filtros.forEach((filtroForeach) => {
                    if (continuarFiltrando) {
                        if (filtroForeach.entidade == classificadorForeach.classificador.entidade) {
                            classificadorEntidadeForeach.filtroAtivado = true;
                            classificadorEntidadeForeach.outrosFiltrado = filtroForeach.itens.some((itemSome) => {
                                return itemSome.outros;
                            });
                            let arrIds: string[] = [];
                            filtroForeach.itens.forEach((itemForeach) => {
                                if (!itemForeach.outros) {
                                    arrIds = arrIds.concat(itemForeach.id);
                                }
                            });
                            classificadorEntidadeForeach.idFiltrado = arrIds;
                            
                            // Seto continuar filtrando como false, pois os filtros abaixo foram escolhidos depois
                            continuarFiltrando = false;
                        } else {
                            // Junto negócios do filtro da mesma entidade
                            let arrNegociosFiltrar: string[] = [];
                            filtroForeach.itens.forEach((itemForeach) => {
                                arrNegociosFiltrar = arrNegociosFiltrar.concat(itemForeach.negocios);
                            });

                            // Realizo filtro
                            arrNegociosFiltrados = arrNegociosFiltrados.filter((negocioFilter) => {
                                return arrNegociosFiltrar.indexOf(negocioFilter) > -1;
                            });
                        }
                    }
                });

                classificadorForeach.qtd = arrNegociosFiltrados.length;
            });
        });
    }

    /**
     * Organiza os dados dos classificadores por grupo, já calculando o percentual de cada item do classificador por entidade
     */
    private organizarDadosPorGrupos(){
        if (this.arrDadosPorGrupo.length == 0) {
            // Adiciono grupos
            this.arrDadosPorGrupo.push({ id: EnumDadosClassificadoresGrupoTela.dcgtOrigem, nome: 'Origem', entidades: [] });
            this.arrDadosPorGrupo.push({ id: EnumDadosClassificadoresGrupoTela.dcgtDestino, nome: 'Destino', entidades: [] });
            this.arrDadosPorGrupo.push({ id: EnumDadosClassificadoresGrupoTela.dcgtConta, nome: 'Conta', entidades: [] });
            this.arrDadosPorGrupo.push({ id: EnumDadosClassificadoresGrupoTela.dcgtContato, nome: 'Contato', entidades: [] });

            if (this.tipoDados == EnumTipoDadosClassificadorTela.tdctQualificados) {
                this.arrDadosPorGrupo.push({ 
                    id: EnumDadosClassificadoresGrupoTela.dcgtTempoQualificacao, 
                    nome: 'Tempo de Qualificação', 
                    entidades: [] 
                });
            } else if (this.tipoDados == EnumTipoDadosClassificadorTela.tdctDesqualificados) {
                this.arrDadosPorGrupo.push({ 
                    id: EnumDadosClassificadoresGrupoTela.dcgtMotivos, 
                    nome: 'Motivos', 
                    entidades: [] 
                });
            }
        } else {
            this.arrDadosPorGrupo.forEach((grupoForeach) => {
                grupoForeach.entidades = [];
            })
        }
        
        // Defino o limite de dados por classificador
        const limiteDados = 7;
        
        // Adiciono entidades aos grupos
        this.classificadoresPorEntidade.forEach((classificadorEntidadeForeach) => {
            // Monto estrutura inicial da entidade
            let entidade: IDadosClassificadoresGrupoEntidadeTela = {
                nome: '',
                entidade: classificadorEntidadeForeach.entidade,
                classificadores: [],
                filtrado: classificadorEntidadeForeach.filtroAtivado
            }
            
            // Monto estrutura do classificador outros, caso seja necessário agrupar dados fora do limite
            const classificadorOutros: IDadosClassificadoresGrupoEntidadeClassificadorTela = {
                nome: 'Outros',
                qtd: 0,
                perc: 0,
                outros: true,
                arrIds: [],
                ordem: 0,
                filtrado: classificadorEntidadeForeach.filtroAtivado && classificadorEntidadeForeach.outrosFiltrado
            };

            // Deixo classificadores em ordem de quantidade, trazendo somente os que tiverem quantidade maior que 0
            const classificadoresEmOrdem = classificadorEntidadeForeach.classificadores.filter((classificadorFilter) => {
                return classificadorFilter.qtd > 0;
            }).sort((itemA, itemB) => {
                if (itemB.qtd >= itemA.qtd) {
                    return 1;
                } else {
                    return -1;
                }
            });

            // Declaro classificadores que serão apresentados
            let arrClassificadoresAApresentar: IDadosClassificadoresGrupoEntidadeClassificadorTela[] = [];
            
            let totalDados: number = 0;

            classificadoresEmOrdem.forEach((classificadorForeach, index) => {
                // Se a lista de classificadores passou do limite, adiciono a quantidade do objeto outros
                if (classificadoresEmOrdem.length > limiteDados && ((index -1) >= limiteDados)) {
                    classificadorOutros.qtd += classificadorForeach.qtd;
                    classificadorOutros.arrIds.push(classificadorForeach.classificador.id);
                } else {
                    // Crio objeto do classificador com o nome e quantidade
                    const classificador: IDadosClassificadoresGrupoEntidadeClassificadorTela = {
                        tipo: classificadorEntidadeForeach.entidade,
                        nome: classificadorForeach.classificador.nome,
                        qtd: classificadorForeach.qtd,
                        perc: 0,
                        outros: false,
                        ordem: 0,
                        id: classificadorForeach.classificador.id,
                        filtrado: classificadorEntidadeForeach.filtroAtivado && 
                            !classificadorEntidadeForeach.outrosFiltrado &&
                            classificadorEntidadeForeach.idFiltrado.indexOf(classificadorForeach.classificador.id) > -1
                    };

                    arrClassificadoresAApresentar.push(classificador);
                }

                // Adiciono quantidade do classificador ao total
                totalDados += classificadorForeach.qtd;
            });

            // Se existem mais classificadores que o limite de dados, adiciono classificador que agrupas os dados menores
            if (classificadoresEmOrdem.length > limiteDados) {
                arrClassificadoresAApresentar.push(classificadorOutros);

                // Deixo classificadores em ordem, pois o outros pode ser maior que um item anterior
                arrClassificadoresAApresentar = arrClassificadoresAApresentar.sort((itemA, itemB) => {
                    if (itemB.qtd >= itemA.qtd) {
                        return 1;
                    } else {
                        return -1;
                    }
                });
            }

            // Calculo percentual dos classificadores
            arrClassificadoresAApresentar.forEach((classificadorForeach, index) => {
                if (totalDados > 0) {
                    classificadorForeach.perc = (classificadorForeach.qtd / totalDados) * 100;
                    // Deixo somente duas casas decimais
                    let percStr: string = classificadorForeach.perc.toFixed(2);
                    classificadorForeach.perc = parseFloat(percStr);
                }
                // Importante para receber a cor correta
                classificadorForeach.ordem = index;
            })

            // Adiciono classificadores que serão apresentados a entidade
            entidade.classificadores = arrClassificadoresAApresentar;

            // Declaro id que utilizarei para buscar grupo da entidade
            let idGrupo: EnumDadosClassificadoresGrupoTela = null;

            // Defino o nome e qual grupo pertence
            switch (entidade.entidade) {
                // Origem
                case EnumPainelMarketingClassificadorEntidadeApi.pmceCampanhaorigem: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtOrigem;
                    entidade.nome = 'Campanha de Origem';
                    break;
                }

                case EnumPainelMarketingClassificadorEntidadeApi.pmceMidiaorigem: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtOrigem;
                    entidade.nome = 'Mídia de Origem';
                    break;
                }

                case EnumPainelMarketingClassificadorEntidadeApi.pmceTipoacionamento: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtOrigem;
                    entidade.nome = 'Tipo de Acionamento';
                    break;
                }

                // Destino
                case EnumPainelMarketingClassificadorEntidadeApi.pmceEstabelecimento: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtDestino;
                    entidade.nome = 'Estabelecimento Comercial';
                    break;
                }

                case EnumPainelMarketingClassificadorEntidadeApi.pmceAreanegocio: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtDestino;
                    entidade.nome = 'Área de Negócio';
                    break;
                }

                case EnumPainelMarketingClassificadorEntidadeApi.pmceAtribuicao: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtDestino;
                    entidade.nome = 'Atribuição';
                    break;
                }

                // Conta
                case EnumPainelMarketingClassificadorEntidadeApi.pmceSegmentoatuacao: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtConta;
                    entidade.nome = 'Segmento';
                    break;
                }

                case EnumPainelMarketingClassificadorEntidadeApi.pmceFaturamentoanual: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtConta;
                    entidade.nome = 'Faturamento Anual';
                    break;
                }

                case EnumPainelMarketingClassificadorEntidadeApi.pmceEhcliente: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtConta;
                    entidade.nome = 'Já é cliente?';
                    break;
                }

                // Contato
                case EnumPainelMarketingClassificadorEntidadeApi.pmceCargo: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtContato;
                    entidade.nome = 'Cargo';
                    break;
                }

                // Tempo de Qualificação
                case EnumPainelMarketingClassificadorEntidadeApi.pmceQualificadoem: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtTempoQualificacao;
                    entidade.nome = 'Qualificado em';
                    break;
                }

                // Motivos
                case EnumPainelMarketingClassificadorEntidadeApi.pmceMotivodesqualificacao: {
                    idGrupo = EnumDadosClassificadoresGrupoTela.dcgtMotivos;
                    entidade.nome = 'Motivos de Desqualificação';
                    break;
                }
            
                default:
                    break;
            }
            
            // Busco indice do grupo
            const indiceGrupo = this.arrDadosPorGrupo.findIndex((grupoFindIndex) => {
                return grupoFindIndex.id == idGrupo;
            })

            // Adiciono entidade ao grupo
            this.arrDadosPorGrupo[indiceGrupo].entidades.push(entidade);
        });
    }

    /**
     * Retorna a quantidade de negócios do classificador de o tipo de accordion
     * @param classificador 
     */
    private getClassificadorQuantidade(classificador: IPainelMarketingClassificadorApi): number {
        return this.getClassificadorNegocios(classificador).length;
    }

    /**
     * Retorna os negócios do classificador de o tipo de accordion
     * @param classificador 
     */
    private getClassificadorNegocios(classificador: IPainelMarketingClassificadorApi): string[] {
        switch (this.tipoDados) {
            case EnumTipoDadosClassificadorTela.tdctParaQualificacao: {
                return classificador.prenegocios;
            }
            case EnumTipoDadosClassificadorTela.tdctQualificados: {
                return classificador.negociosqualificados;
            }
            case EnumTipoDadosClassificadorTela.tdctDesqualificados: {
                return classificador.negociosdesqualificados;
            }
            default: {
                return [];
            }
        }
    }
}

interface IClassificadorFiltroTela {
    entidade: EnumPainelMarketingClassificadorEntidadeApi;
    itens: IClassificadorFiltroItemTela[];
}

interface IClassificadorFiltroItemTela {
    id: any;
    negocios: string[];
    outros: boolean;
}

interface IClassificadoresEntidadesTela {
    entidade: EnumPainelMarketingClassificadorEntidadeApi;
    classificadores: IClassificadorTela[];
    filtroAtivado: boolean;
    idFiltrado: any[];
    outrosFiltrado: boolean;
}

interface IClassificadorTela {
    classificador: IPainelMarketingClassificadorApi;
    qtd: number;
}

interface IDadosClassificadoresGrupoTela {
    id: EnumDadosClassificadoresGrupoTela;
    nome: string;
    entidades: IDadosClassificadoresGrupoEntidadeTela[];
}

interface IDadosClassificadoresGrupoEntidadeTela {
    entidade: EnumPainelMarketingClassificadorEntidadeApi;
    nome: string;
    classificadores: IDadosClassificadoresGrupoEntidadeClassificadorTela[];
    filtrado: boolean;
}

interface IDadosClassificadoresGrupoEntidadeClassificadorTela {
    tipo?: EnumPainelMarketingClassificadorEntidadeApi;
    nome: string;
    id?: any;
    qtd: number;
    perc: number;
    outros: boolean;
    arrIds?: any[];
    ordem: number;
    filtrado: boolean;
}

enum EnumDadosClassificadoresGrupoTela {
    dcgtOrigem,
    dcgtDestino,
    dcgtConta,
    dcgtContato,
    dcgtTempoQualificacao,
    dcgtMotivos,
}

enum EnumTipoDadosClassificadorTela {
    tdctParaQualificacao,
    tdctQualificados,
    tdctDesqualificados,
}
