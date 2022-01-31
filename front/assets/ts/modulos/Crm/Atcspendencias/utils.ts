import angular = require('angular');

export class CrmAtcspendenciasUtilsService{
    static $inject = ['CrmAtcspendencias', '$uibModal', 'CrmAtcs', 'moment'];

    constructor(
        public entityService: any, 
        public $uibModal: any,
        public CrmAtcs: any,
        private moment
    ) {
    }

    /**
     * Retorna a diferença em minutos entre a data passada e a data atual
     * @param data 
     */
    getAbertaHa(data): number {
        //Pego a diferença entre a data atual e a data de criação em minutos.
        const dataCriacao = this.moment(data);
        const dataAtual = this.moment();
        
        return Math.abs(dataCriacao.diff(dataAtual, 'minutes'));
    }

    /**
     * Retorna os minutos no formato "[qtd_dias] dias + [HH]h:[MM]min"
     * @param pMinutos 
     */
    minutosParaDataFormatada(pMinutos: number): string {
        let dias = 0;
        let horas = 0;
        let minutos = 0;
        let resto = 0;

        if (pMinutos > 0) {
            dias = Math.trunc(pMinutos / 1440);
            resto = pMinutos - (dias * 1440);
            
            if (resto > 0) {
                horas = Math.trunc(resto / 60);
                minutos = resto - (horas * 60);
            }
        }
        
        let data = new Date(0, 0, 0, horas, minutos, 0, 0);
        let retorno = `${dias} dias + ${this.moment(data).format('HH') }h${this.moment(data).format('mm')}min`;
        
        return retorno;
    }

    getStatusDescricao(pStatus: EnumStatusPendencia){
        switch (pStatus) {
            case EnumStatusPendencia.sAberta: return 'EM ABERTO';
            case EnumStatusPendencia.sFechada: return 'FECHADA';
        };
    }

    getImpeditivoDescricao(pImpeditivo: EnumImpeditivoPendencia){
        switch (pImpeditivo) {
            case EnumImpeditivoPendencia.iInterno: return 'Interno';
            case EnumImpeditivoPendencia.iCliente: return 'Cliente';
            case EnumImpeditivoPendencia.iFornecedor: return 'Fornecedor';
            case EnumImpeditivoPendencia.iFamilia: return 'Família';
        };
    }

    getDataFormatada(data: string){
        return this.moment(data).format('DD/MM/YYYY');
    }

    /**
     * Recebe uma string de data e hora e retorna a parte da hora da string
     * @param data A string de data e hora
     * @param mostrarSegs Indica se o retorno deve ter os segundos ou não. Default true
     * @returns 
     */
    getHoraFormatada(data: string, mostrarSegs: boolean = true){

        return mostrarSegs ? this.moment(data).format('HH:mm:ss') : this.moment(data).format('HH:mm');

    }
}

export enum EnumStatusPendencia {
    sAberta = 0,
    sFechada = 1
}

export enum EnumPendenciaPrazoStatus {
    ppsValido = 0,
    ppsProximoExpiracao = 1,
    ppsExpirado = 2
}

export enum EnumImpeditivoPendencia {
    iInterno = 0,
    iCliente = 1,
    iFornecedor = 2,
    iFamilia = 3
}
