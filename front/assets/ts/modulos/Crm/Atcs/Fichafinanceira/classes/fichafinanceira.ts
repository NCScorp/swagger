import { IFichaFinanceiraFornecedor } from "./ifichafinanceira";
import { IFornecedorenvolvido } from "../../../Fornecedoresenvolvidos/classes/ifornecedorenvolvido";
import { Orcamento } from "../../../Orcamentos/classes/orcamento";
import { EOrcamentoStatus } from "../../../Orcamentos/classes/eorcamentostatus";
import { IOrcamento } from "../../../Orcamentos/classes/iorcamento";
import { EAcionamentoMetodo } from "../../../Fornecedoresenvolvidos/classes/eacionamentometodo";
import { EDescontoGlobalTipo } from "../../../Fornecedoresenvolvidos/classes/edescontoglobaltipo";

export class FichaFinanceiraFornecedor {
    public fornecedorEnvolvido: IFornecedorenvolvido;
    public acionador: string;
    public atc: string;
    public valorCobertoApolice: number;
    public arrOrcamentos: Orcamento[] = [];
    public ultimaAtualizacao: string = null;
    public permiteOrcamentoZerado: boolean = false;
    public status: {
        status?: EOrcamentoStatus,
        descricao?: string
    } = {}
    public apresentarInfoDesconto: boolean = false;
    public configDescontosOld: {
        possuidescontoparcial: boolean;
        possuidescontoglobal: boolean;
        descontoglobal: number;
        descontoglobaltipo: EDescontoGlobalTipo;
    } = {
        possuidescontoparcial: false,
        possuidescontoglobal: false,
        descontoglobal: 0,
        descontoglobaltipo: EDescontoGlobalTipo.dgtValor
    };

    /**
     * Retorna se o fornecedor já está acionado
     */
    acionado(): boolean {
        const fornecedorenvolvido = this.fornecedorEnvolvido.fornecedorenvolvido;
        return fornecedorenvolvido != null && fornecedorenvolvido != '';
    }

    /**
     * Retorna objetos de ficha financeira do fornecedor a partir de lista de dados da api.
     * @param arrDadosApi 
     */
    static getFichasFinanceiras(arrDadosApi: IFichaFinanceiraFornecedor[]): FichaFinanceiraFornecedor[] {
        const arrFichas: FichaFinanceiraFornecedor[] = [];
        
        // Array com guid dos fornecedores
        const arrFornecedores: string[] = [];

        arrDadosApi.forEach((dadoApi) => {
            let indice = arrFornecedores.indexOf(dadoApi.fornecedor);

            // Se não criou o objeto de ficha para o fornecedor
            if (indice < 0) {
                // Crio objeto de ficha financeira do fornecedor
                const ficha = new FichaFinanceiraFornecedor();

                // Preencho demais dados da ficha
                ficha.atc = dadoApi.negocio;
                ficha.acionador = dadoApi.acionador;
                ficha.valorCobertoApolice = dadoApi.valorapolice;

                // Define que permite orçamento zerado, caso possua a configuração na área de atendimento e o estabelecimento esteja preenchido
                ficha.permiteOrcamentoZerado = dadoApi.permiteorcamentozerado 
                    && dadoApi.fornecedor_estabelecimento != null 
                    && dadoApi.fornecedor_estabelecimento != '';

                // Preencho dados de acionamento e fornecedor
                ficha.fornecedorEnvolvido = {
                    fornecedorenvolvido: dadoApi.fornecedorenvolvido,
                    acionamentoaceito: dadoApi.acionamentoaceito,
                    acionamentodata: dadoApi.acionamentodata,
                    acionamentometodo: dadoApi.acionamentometodo,
                    acionamentorespostaprazo: dadoApi.acionamentorespostaprazo,
                    fornecedor: {
                        fornecedor: dadoApi.fornecedor,
                        nomefantasia: dadoApi.fornecedor_nomefantasia,
                        razaosocial: dadoApi.fornecedor_nome,
                        estabelecimentoid: {
                            estabelecimento: dadoApi.fornecedor_estabelecimento
                        },
                        esperapagamentoseguradora: dadoApi.fornecedor_esperapagamentoseguradora
                    },
                    // Configurações de desconto
                    possuidescontoparcial: dadoApi.config_desconto_possuidescontoparcial,
                    possuidescontoglobal: dadoApi.config_desconto_possuidescontoglobal,
                    descontoglobal: dadoApi.config_desconto_descontoglobal,
                    descontoglobaltipo: dadoApi.config_desconto_descontoglobaltipo ? dadoApi.config_desconto_descontoglobaltipo : EDescontoGlobalTipo.dgtValor
                }

                // Adiciono ficha a lista e atualizo indice
                arrFichas.push(ficha);
                indice = arrFichas.length -1;

                // Adiciono guid a arrFornecedores para não recriar registro de ficha
                arrFornecedores.push(dadoApi.fornecedor);
            }

            // Pego objeto da ficha de acordo com o índice
            const ficha = arrFichas[indice];

            if (dadoApi.orcamento != null && dadoApi.orcamento != '') {
                // Preencho dados de orçamento e adiciono a ficha
                const interfaceOrcamento: IOrcamento = {
                    atc: dadoApi.negocio,
                    orcamento: dadoApi.orcamento,
                    propostaitem: dadoApi.orcamento_propostaitem,
                    composicao: dadoApi.orcamento_composicao,
                    familia: dadoApi.orcamento_familia,
                    descricao: dadoApi.orcamento_descricao,
                    descricaomanual: dadoApi.orcamento_descricaomanual ? dadoApi.orcamento_descricaomanual : false,
                    custo: dadoApi.orcamento_custo ? dadoApi.orcamento_custo.toString() : '0',
                    quantidade: dadoApi.orcamento_quantidade ? dadoApi.orcamento_quantidade : 0,
                    valorunitario: dadoApi.orcamento_valorunitario ? dadoApi.orcamento_valorunitario.toString() : '0',
                    desconto: dadoApi.orcamento_desconto ? dadoApi.orcamento_desconto.toString() : '0',
                    descontoglobal: dadoApi.orcamento_descontoglobal ? dadoApi.orcamento_descontoglobal.toString() : '0',
                    valorreceber: dadoApi.orcamento_valorreceber ? dadoApi.orcamento_valorreceber.toString() : '0',
                    faturar: dadoApi.orcamento_faturar ? dadoApi.orcamento_faturar : false,
                    fornecedor: ficha.fornecedorEnvolvido.fornecedor,
                    fornecedorterceirizado: dadoApi.orcamento_prestador_terceirizado,
                    fornecedorterceirizado_nome: dadoApi.orcamento_prestador_terceirizado_nome,
                    fornecedorterceirizado_nomefantasia: dadoApi.orcamento_prestador_terceirizado_nomefantasia,
                    status: dadoApi.orcamento_status,
                    updated_at: dadoApi.orcamento_updated_at,
                    faturamentotipo: dadoApi.orcamento_faturamentotipo,
                    servicotipo: dadoApi.orcamento_servicotipo
                }
    
                // Adiciono orçamento a lista da ficha
                ficha.arrOrcamentos.push(new Orcamento(interfaceOrcamento));
            }

            // Atualizo ficha na lista
            arrFichas[indice] = ficha;
        });

        arrFichas.forEach(fichaForeach => {
            fichaForeach.atualizarInformacoesGerais();
            fichaForeach.apresentarInfoDesconto = fichaForeach.apresentarInformacoesDesconto();
        });
        return arrFichas;
    }

    /**
     * Retorna a lista de orçamentos referentes a serviços
     */
    getOrcamentosServicos(): Orcamento[] {
        return this.arrOrcamentos.filter((orcamentoFilter) => {
            return orcamentoFilter.composicao != null && orcamentoFilter.composicao != '';
        });
    }

    /**
     * Retorna a lista de orçamentos referentes a mercadorias
     */
    getOrcamentosMercadorias(): Orcamento[] {
        return this.arrOrcamentos.filter((orcamentoFilter) => {
            return orcamentoFilter.familia != null && orcamentoFilter.familia != '';
        });
    }

    /**
     * Busca o total para a propriedade informada
     * @param nome 
     */
    getTotal(nome: string, tipo: 'servico' | 'produto' = null): number {
        let total: number = 0;

        let arrOrcamentos: Orcamento[] = [];

        // Caso seja serviço ou produtos, filtro a listagem
        if (tipo == 'servico') {
            arrOrcamentos = this.getOrcamentosServicos();
        } else if (tipo == 'produto') {
            arrOrcamentos = this.getOrcamentosMercadorias();
        } else {
            arrOrcamentos = this.arrOrcamentos;
        }

        arrOrcamentos.forEach((orcamentoForeach) => {
            total += orcamentoForeach[nome];
        });

        return total;
    }

    /**
     * Retorna a descrição do método de acionamento do prestador
     */
    getAcionamentoMetodoDescricao(): string {
        switch (this.fornecedorEnvolvido.acionamentometodo) {
            case EAcionamentoMetodo.amSistema: return 'Sistema';
            case EAcionamentoMetodo.amEmail: return 'E-mail';
            case EAcionamentoMetodo.amTelefone: return 'Telefone';
            default: return 'Não informado';
        }
    }

    /**
     * Retorna data de acionamento
     */
    getAcionamentoData(): Date {
        const dataApi = this.fornecedorEnvolvido.acionamentodata;
        if (dataApi != null) {
            const dtApi = new Date(dataApi);
            const dataLocal = new Date(Date.UTC(dtApi.getFullYear(), dtApi.getMonth(), dtApi.getDay(), dtApi.getHours(), dtApi.getMinutes()));
            return dataLocal;
        } else {
            return null;
        }
    }


    /**
     * Retorna o valor do desconto global
     */
    getValorDescontoGlobal(): number {
        const orcamentoTotal = this.getTotal('orcamentofinal');
        let totalDescontoGlobal = 0;

        if (this.fornecedorEnvolvido.descontoglobaltipo == EDescontoGlobalTipo.dgtValor) {
            totalDescontoGlobal = this.fornecedorEnvolvido.descontoglobal;
        } else {
            totalDescontoGlobal = (orcamentoTotal / 100) * this.fornecedorEnvolvido.descontoglobal;
        }

        return totalDescontoGlobal;
    }

    /**
     * Atualiza updated_at com a maior alteração em orçamentos
     */
    atualizarInformacoesGerais(){
        // Preencho dados de status
        this.status.status = -1;

        if (this.arrOrcamentos.length > 0) {
            this.status.status = this.arrOrcamentos[0].status;
        } else {
            // Se não tem orçamentos, mas está acionado, defino que o status é Aberto
            if (this.acionado()) {
                this.status.status = EOrcamentoStatus.osAberto;
            }
        }

        this.status.descricao = Orcamento.getStatusDescricao(this.status.status);

        // Preencho dados de atualização
        this.arrOrcamentos.forEach((orcamentoForeach) => {
            // Pego data da última atualização pela maior data do orçamento
            if (this.ultimaAtualizacao == null) {
                this.ultimaAtualizacao = orcamentoForeach.get('updated_at')
            } else {
                const dataFicha = new Date(this.ultimaAtualizacao);
                const dataOrcamento = orcamentoForeach.updated_at;

                if (dataOrcamento.valueOf() > dataFicha.valueOf()) {
                    this.ultimaAtualizacao = orcamentoForeach.get('updated_at');
                }
            }
        });

        // Atualizo informações de configuração de descontos
        
        this.configDescontosOld.possuidescontoparcial = this.fornecedorEnvolvido.possuidescontoparcial;
        this.configDescontosOld.possuidescontoglobal = this.fornecedorEnvolvido.possuidescontoglobal;
        this.configDescontosOld.descontoglobal = this.fornecedorEnvolvido.descontoglobal;
        this.configDescontosOld.descontoglobaltipo = this.fornecedorEnvolvido.descontoglobaltipo;
    }

    /**
     * Verifica se pode apresentar opções de desconto na parte inferior da tela
     */
    apresentarInformacoesDesconto(): boolean {
        return this.acionado() && (this.apresentarInfoDesconto || this.fornecedorEnvolvido.possuidescontoparcial || this.fornecedorEnvolvido.possuidescontoglobal);
    }
}