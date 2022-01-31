import angular = require('angular');

export class CrmPainelController {
    /**
     * Lista de injeções de dependência da tela
     */
    static $inject = [];

    /**
     * Define se a tela está ocupada executando algum processo
     */
    private busy: boolean = false;
    /**
     * Declaro enum no controller para ser utilizado na view
     */
    private EnumTipoPainel = EnumTipoPainel;
    /**
     * Define o tipo de painel selecionado
     */
    private tipoPainel: EnumTipoPainel = EnumTipoPainel.tpMarketing;

    /**
     * Utilizado para controllar o painel selecionado
     */
    ctrlPainel: ICtrlPainel = {
        fnCarregarPainel: null
    }

    /**
     * Filtros utilizados para carregar o painel
     */
    private filtros: IPainelFiltros = {
        dataInicial: null,
        dataFinal: null
    };

    constructor () {}

    /**
     * Função chamada ao iniciar a tela
     */
    $onInit(){
        this.filtroDatasIniciais();
    }

    /**
     * Seta a data inicial e final que serão carregadas nos filtros na inicialização da página
     */
    filtroDatasIniciais(){

        /* 
            Irá carregar inicialmente com filtro semestral. A data inicial será o primeiro dia do mês atual e 
            data final será daqui a 6 meses no último dia do mês
        */

        let dataAtual = new Date;
        let anoAtual = dataAtual.getFullYear();
        let mesAtual = dataAtual.getMonth() + 1;
        
        let mesFinal = mesAtual + 5;

        let diaFinal = this.diasNoMes(mesFinal, anoAtual);
        
        this.filtros.dataInicial = anoAtual + '-' + mesAtual + '-' + '01';
        this.filtros.dataFinal = anoAtual + '-' + mesFinal + '-' + diaFinal;

    }

    /**
     * Retorna o último dia do mês que recebemos por parâmetro. Recebemos também o ano porque o ano pode ser bissexto e com isso o número de dias de fevereiro varia
     * @param mes: o número do mês que se deseja saber o último dia (1 = janeiro, 2 = fevereiro...)
     * @param ano: o número do ano da data que queremos saber o último dia do mês
     */
    diasNoMes (mes, ano) {

        // Função Date em js usa os meses começando em zero. O parâmetro zero na função significa que desejo saber o último dia do mês anterior, 
        // como a função diasNoMes usa os meses começando em 1, funciona corretamente
        return new Date(ano, mes, 0).getDate();
    }

    /**
     * Retorna se a tela está ocupada executando algum processo
     */
    isBusy() {
        return this.busy;
    }

    /**
     * Carrega o painel selecionado
     */
    private carregarPainel(){
        this.ctrlPainel.fnCarregarPainel(this.filtros);
    }
}

/**
 * Enum referenciando os tipos de painéis da tela
 */
enum EnumTipoPainel {
    tpMarketing
}

export interface ICtrlPainel {
    fnCarregarPainel: (filtros: IPainelFiltros) => void;
}

export interface IPainelFiltros {
    dataInicial: string;
    dataFinal: string;
}
