import angular = require('angular');

export class CommonsUtilsService {
    static $inject = ['moment'];

    constructor(
        private moment
    ) { }

    /**
     * Converte uma string de data para o formato padrão
     * @param data 
     */
    getDataFormatoPadrao(data: string): string {
        let dataFormatada = '';

        const dataOriginal = this.moment(data);
        const dataAtual = this.moment();
        const dataOntem = this.moment();
        dataOntem.subtract({ hours: 24 });

        let tipoData: 'hoje' | 'ontem' | 'anterior';

        if (dataOriginal.format('DD/MM/YYYY') == dataAtual.format('DD/MM/YYYY')) {
            tipoData = 'hoje';
        } else if (dataOriginal.format('DD/MM/YYYY') == dataOntem.format('DD/MM/YYYY')) {
            tipoData = 'ontem';
        } else {
            tipoData = 'anterior';
        }

        if (tipoData == 'hoje') {
            const diffMinutos = dataAtual.diff(dataOriginal, 'minutes');

            // Se a diferença for menor que 1 hora, coloco a diferença em minutos
            if (diffMinutos < 60) {
                dataFormatada = `Há ${diffMinutos} minutos`;
            }
            // Senão, coloco a diferença em horas
            else {
                const diffHoras = dataAtual.diff(dataOriginal, 'hours');
                dataFormatada = `Há ${diffHoras} horas`;
            }
        } else if (tipoData == 'ontem') {
            dataFormatada = dataOriginal.format('[Ontem às] HH:mm')
        } else {
            dataFormatada = dataOriginal.format('DD [de] MMMM');
        }

        return dataFormatada;
    }

    /**
     * Pega o momento da data, se é hoje, ontem ou data anterior
     * @param data 
     */
    getMomentoData(data: string): 'Hoje' | 'Ontem' | 'Anterior' {
        const dataOriginal = this.moment(data);
        const dataAtual = this.moment();
        const dataOntem = this.moment();
        dataOntem.subtract({ hours: 24 });

        if (dataOriginal.format('DD/MM/YYYY') == dataAtual.format('DD/MM/YYYY')) {
            return 'Hoje';
        } else if (dataOriginal.format('DD/MM/YYYY') == dataOntem.format('DD/MM/YYYY')) {
            return 'Ontem';
        } else {
            return dataOriginal.format('DD/MM/YYYY');
        }
    }

    /**
     * Converte uma string de data para o formato definido.
     * Por padrão, será retornada no formato DD/MM/YYYY
     * @param data 
     */
    getDataFormatada(data: string, formato: string = 'DD/MM/YYYY') {
        return this.moment(data).format(formato);
    }

    /**
     * Converte uma string de data para o formato de hora definido
     * Por padrão, será retornada no formato HH:mm:ss
     * @param data 
     */
    getHoraFormatada(data: string, formato: string = 'HH:mm:ss') {
        return this.moment(data).format(formato);
    }

    /**
     * Baixa um documento a partir de um link ou um retorno binário
     * @param link 
     * @param tipoLink 
     */
    baixarFromLink(link: ILink, tipoLink: EnumTipoLink = EnumTipoLink.tlUrl) {
        let blob: any;

        if (tipoLink = EnumTipoLink.tlBinary) {
            blob = link.binary;//new Blob([link.binary], {type: "application/pdf"});
        }

        if (tipoLink == EnumTipoLink.tlBinary && window.navigator && window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, link.nome);
            return;
        }

        let data: string = '';
        if (tipoLink == EnumTipoLink.tlBinary) {
            data = window.URL.createObjectURL(blob);
        } else {
            data = link.url;
        }

        var linkElement = window.document.createElement('a');
        linkElement.href = data;
        linkElement.download = link.nome;
        linkElement.click();
        setTimeout(function () {
            //Para Firefox necessário para delay revokingObjectURL
            window.URL.revokeObjectURL(data);
        }, 100);

    }

    /**
     * Abre uma url em outra guia
     * @param link 
     * @param tipoLink 
     */
    abrirFromLink(link: ILink, tipoLink: EnumTipoLink = EnumTipoLink.tlUrl) {
        let blob: any;

        if (tipoLink = EnumTipoLink.tlBinary) {
            blob = link.binary;
        }

        let data: string = '';
        if (tipoLink == EnumTipoLink.tlBinary) {
            data = window.URL.createObjectURL(blob);
        } else {
            data = link.url;
        }

        window.open(data);
    }

    /**
     * Recebe uma string de CPF por parâmetro e retorna o CPF formatado com máscara
     * @param cpf 
     */
     getCpfFormatado(cpf: string){

        //formatando
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");

    }

    /**
     * Recebe uma string de CNPJ por parâmetro e retorna o CNPJ formatado com máscara
     * @param cnpj 
     */
     getCnpjFormatado(cnpj: string){

        //formatando
        return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");

    }

}

export enum EnumTipoLink {
    tlUrl,
    tlBinary
}

export interface ILink {
    url?: string;
    binary?: any;
    nome?: string;
}
