import { IOrcamento } from "./iorcamento";
import { Entity } from "../../../Commons/classes/entity";
import { EOrcamentoStatus } from "./eorcamentostatus";
import { IFornecedor } from "../../../Ns/Fornecedores/classes/ifornecedor";
import { EFaturamentoTipo } from "./efaturamentotipo";
import { EServicoTipo } from "./eservicotipo";

export class Orcamento extends Entity<IOrcamento> {
    _fornecedorterceirizadoObj: IFornecedor;

    get fornecedorterceirizadoObj(): IFornecedor {
        if (this._fornecedorterceirizadoObj == null && this.fornecedorterceirizado != null) {
            this._fornecedorterceirizadoObj = {
                fornecedor: this.fornecedorterceirizado,
                razaosocial: this.fornecedorterceirizadonome,
                nomefantasia: this.fornecedorterceirizadonomefantasia
            }
        }
        return this._fornecedorterceirizadoObj;
    }

    set fornecedorterceirizadoObj(fornecedor: IFornecedor) {
        this._fornecedorterceirizadoObj = fornecedor;
        
        if (fornecedor == null) {
            this.dados.fornecedorterceirizado = null;
            this.dados.fornecedorterceirizado_nome = null;
            this.dados.fornecedorterceirizado_nomefantasia = null;
        } else {
            this.dados.fornecedorterceirizado = fornecedor.fornecedor;
            this.dados.fornecedorterceirizado_nome = fornecedor.razaosocial;
            this.dados.fornecedorterceirizado_nomefantasia = fornecedor.nomefantasia;
        }
    }

    get orcamento(): string {
        return this.dados.orcamento;
    }

    set orcamento(orcamento: string) {
        this.dados.orcamento = orcamento;
    }

    get composicao(): string {
        return this.dados.composicao;
    }

    set composicao(composicao: string) {
        this.dados.composicao = composicao;
    }

    get familia(): string {
        return this.dados.familia;
    }

    set familia(familia: string) {
        this.dados.familia = familia;
    }

    get descricaomanual(): boolean {
        return this.dados.descricaomanual;
    }

    set descricaomanual(descricaomanual: boolean) {
        this.dados.descricaomanual = descricaomanual;
    }

    get descricao(): string {
        return this.dados.descricao;
    }

    set descricao(descricao: string) {
        this.dados.descricao = descricao;
    }

    get custo(): number {
        return this.getPropertyToNumber('custo');
    }

    set custo(custo: number) {
        this.setPropertyNumberToText('custo', custo);
    }

    get quantidade(): number {
        return this.dados.quantidade;
    }

    set quantidade(quantidade: number) {
        if (typeof(quantidade) == 'string'){
            quantidade = this.textToNumber(quantidade);
        }

        this.dados.quantidade = Math.round(quantidade);
        this.setPropertyNumberToText('valorreceber', this.valor - this.desconto);
    }

    get valorunitario(): number {
        return this.getPropertyToNumber('valorunitario');
    }

    set valorunitario(valorunitario: number) {
      this.setPropertyNumberToText('valorunitario', valorunitario);
      this.setPropertyNumberToText('valorreceber', this.valor - this.desconto);
    }
    
    get valor(): number{
        return this.valorunitario * this.quantidade;
    }

    get desconto(): number {
        return this.getPropertyToNumber('desconto');
    }

    set desconto(desconto: number) {
        if (desconto > this.valor) {
            desconto = this.valor;
        }

        if (desconto < 0) {
            desconto = 0;
        }
        this.setPropertyNumberToText('desconto', desconto);
        this.setPropertyNumberToText('valorreceber', this.valor - this.desconto);
    }

    get descontoglobal(): number {
        return this.getPropertyToNumber('descontoglobal');
    }

    set descontoglobal(descontoglobal: number) {
        this.setPropertyNumberToText('descontoglobal', descontoglobal);
    }

    get orcamentofinal(): number{
        return this.valor - this.desconto;
    }

    get orcamentofinalfaturar(): number {
        if (this.faturamentotipo == EFaturamentoTipo.ftNaoFaturar) {
            return 0
        } else {
            return this.orcamentofinal;
        }
    }

    get valorreceber(): number {
        return this.getPropertyToNumber('valorreceber');
    }

    set valorreceber(valorreceber: number) {
        this.setPropertyNumberToText('valorreceber', valorreceber);
    }

    get valorexcedente(): number {
        return this.valorreceber - this.orcamentofinal;
    }

    get faturar(): boolean {
        return this.dados.faturar;
    }

    set faturar(faturar: boolean) {
        this.dados.faturar = faturar;
    }

    get faturamentotipo(): EFaturamentoTipo {
        return this.dados.faturamentotipo;
    }

    set faturamentotipo(faturamentotipo: EFaturamentoTipo) {
        this.dados.faturamentotipo = faturamentotipo;
    }

    get servicotipo(): EServicoTipo {
        return this.dados.servicotipo;
    }

    set servicotipo(servicotipo: EServicoTipo) {
        this.dados.servicotipo = servicotipo;
    }

    get servicotipoDescricao(): string{
        return Orcamento.getServicotipoDescricao(this.servicotipo);
    }

    get status(): EOrcamentoStatus {
        return this.dados.status;
    }

    set status(status: EOrcamentoStatus) {
        this.dados.status = status;
    }

    get statusDescricao(): string{
        return Orcamento.getStatusDescricao(this.status);
    }

    get faturamentoDescricao(): string {
        return Orcamento.getFaturamentoDescricao(this.faturamentotipo);
    }

    get fornecedor(): IFornecedor{
        return this.dados.fornecedor;
    }

    get fornecedorterceirizado(): string {
        return this.dados.fornecedorterceirizado;
    }

    set fornecedorterceirizado(fornecedorterceirizado: string) {
        this.dados.fornecedorterceirizado = fornecedorterceirizado;

        if (this._fornecedorterceirizadoObj == null) {
            this._fornecedorterceirizadoObj = {};
        }

        this._fornecedorterceirizadoObj.fornecedor = fornecedorterceirizado;
    }

    get fornecedorterceirizadonome(): string {
        return this.dados.fornecedorterceirizado_nome;
    }

    set fornecedorterceirizadonome(fornecedorterceirizado_nome: string) {
        this.dados.fornecedorterceirizado_nome = fornecedorterceirizado_nome;

        if (this._fornecedorterceirizadoObj == null) {
            this._fornecedorterceirizadoObj = {};
        }

        this._fornecedorterceirizadoObj.razaosocial = fornecedorterceirizado_nome;
    }

    get fornecedorterceirizadonomefantasia(): string {
        return this.dados.fornecedorterceirizado_nomefantasia;
    }

    set fornecedorterceirizadonomefantasia(fornecedorterceirizado_nomefantasia: string) {
        this.dados.fornecedorterceirizado_nomefantasia = fornecedorterceirizado_nomefantasia;

        if (this._fornecedorterceirizadoObj == null) {
            this._fornecedorterceirizadoObj = {};
        }

        this._fornecedorterceirizadoObj.nomefantasia = fornecedorterceirizado_nomefantasia;
    }

    set updated_at(updated_at: Date) {
        this.dados.updated_at = updated_at.toISOString();
    }

    get updated_at(): Date {
        return new Date(this.dados.updated_at);
    }

    static getStatusDescricao(status: EOrcamentoStatus): string {
        switch (status) {
            case EOrcamentoStatus.osAberto: return 'ABERTO';
            case EOrcamentoStatus.osEnviado: return 'ENVIADO';
            case EOrcamentoStatus.osAprovado: return 'APROVADO';
            case EOrcamentoStatus.osRecusado: return 'RECUSADO';
            default: return 'NÃO INFORMADO';
        }
    }

    static getFaturamentoDescricao(faturamentotipo: EFaturamentoTipo): string {
        switch (faturamentotipo) {
            case EFaturamentoTipo.ftNaoFaturar: return 'Não faturar';
            case EFaturamentoTipo.ftFaturarSeguradoraSemNFS: return 'Faturar Seguradora Sem NFS';
            case EFaturamentoTipo.ftFaturarSeguradoraComNFS: return 'Faturar Seguradora Com NFS';
            case EFaturamentoTipo.ftFaturarClienteComNFS: return 'Faturar Cliente Com NFS';
            default: return 'NÃO INFORMADO';
        }
    }

    static getServicotipoDescricao(servicotipo: EServicoTipo): string {
        switch (servicotipo) {
            case EServicoTipo.stExterno: return 'SERVIÇO EXTERNO';
            case EServicoTipo.stPrestadoraAcionada: return 'SERVIÇO COM A PRESTADORA ACIONADA';
            default: return 'NÃO INFORMADO';
        }
    }
}