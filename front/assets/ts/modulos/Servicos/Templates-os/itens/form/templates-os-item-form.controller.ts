import angular = require('angular');

import { TemplateOrdemServicoItemChecklistFormView } from '../checklist/interfaces/template-os-item-checklist.form';
import { TemplateOrdemServicoItemFormView } from '../interfaces/template-os-item.form';
import { TemplatesOrdemServicoItemChecklistService } from '../checklist/templates-os-item-checklist.service';
import { ITemplateOrdemServicoItemChecklist } from '../checklist/interfaces/templates-os-item-checklist.interface';

export class TemplatesOrdemServicoItemFormController {

    static $inject = [
        '$scope', 
        'toaster', 
        'templatesOrdemServicoItemChecklistService',
    ];

    public entity: TemplateOrdemServicoItemFormView;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    /**
     * Guarda o texto do input de checklist
     */
    checklistTexto = '';
    /**
     * Utilizado para verificar se está carregando o checklist
     */
    busyChecklist = false;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any, 
        public templatesOrdemServicoItemChecklistService: TemplatesOrdemServicoItemChecklistService,
    ) {}

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        // Se serviço já existe no banco
        if (this.entity.ordemservicotemplateitem) {
            // Se ainda não carregou checklists, faço uma busca
            if (!this.entity.checklistCarregado) {
                this.busyChecklist = true;

                this.carregarChecklist()
                    .then((arrChecklist) => {})
                    .catch((err) => {})
                    .finally(() => {
                        this.busyChecklist = false;
                        this.reloadScope();
                    })
            }
        } else {
            this.entity.checklistCarregado = true;
        }
    }

    submit() {
        this.form.$submitted = true;

        if (this.form.$valid) {
            return ;
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    
    /**
     * Atualiza escopo da tela
     */
    reloadScope(){
        this.$scope.$applyAsync();
    }

    /**
     * Busca checklist do serviço
     * @returns 
     */
    carregarChecklist(): Promise<ITemplateOrdemServicoItemChecklist[]> {
        return new Promise((resolve, reject) => {
            this.templatesOrdemServicoItemChecklistService.getAll({}, {ordemservicotemplateitem: this.entity.ordemservicotemplateitem})
                .then(arrDados => {
                    // Realizo conversão de dados
                    this.entity.arrChecklist = arrDados.map((itemChk) => {
                        return new TemplateOrdemServicoItemChecklistFormView(itemChk);
                    });

                    // Marco que buscou checklist com sucesso
                    this.entity.checklistCarregado = true;

                    resolve(arrDados)
                })
                .catch(err => reject(err));
        });
    }

    /**
     * Evento disparado ao clicar no botão de adicionar item ao checklist
     */
    onBtnAddChecklistClick(){
        if (this.checklistTexto.trim() == '') {
            this.toaster.pop({
                type: 'error',
                title: 'Checklist não pode ser vazio.'
            });
            return;
        }

        // Crio novo item de checklist
        const novoChecklist = new TemplateOrdemServicoItemChecklistFormView({
            descricao: this.checklistTexto,
            ordemservicotemplate: this.entity.ordemservicotemplate,
            ordemservicotemplateitem: this.entity.ordemservicotemplateitem
        });

        // Defino um id virtual para o item do checklist
        let maiorId = 0;
        this.entity.arrChecklist.forEach((item) => {
            if (item.idVirtual != null && item.idVirtual > maiorId) {
                maiorId = item.idVirtual;
            }
        });
        novoChecklist.idVirtual = ++maiorId;

        // Adiciono item a lista
        this.entity.arrChecklist.push(novoChecklist);

        // Limpo texto do checklist
        this.checklistTexto = ''; 
    }

    /**
     * Evento disparado ao clicar no botão de excluir item do checklist
     * @param checklist 
     */
    onBtnExcluirChecklistClick(checklist: TemplateOrdemServicoItemChecklistFormView){
        const existeNoBD = checklist.ordemservicotemplatechecklist != null;

        let indexExclusao = -1;

        // Se existe no banco de dados
        if (existeNoBD) {
            // Adiciono a lista de itens do checklist para exclusão
            this.entity.arrChecklistExcluir.push(checklist);

            indexExclusao = this.entity.arrChecklist.findIndex((item) => item.ordemservicotemplatechecklist == checklist.ordemservicotemplatechecklist);
        } else {
            indexExclusao = this.entity.arrChecklist.findIndex((item) => item.idVirtual == checklist.idVirtual);
        }

        // Removo da lista principal
        this.entity.arrChecklist.splice(indexExclusao, 1);
        this.reloadScope();
    }

    /**
     * Evento disparado ao clicar no botão de recarregar checklist em caso de erro
     */
    onRecarregarChecklistClick(){
        this.busyChecklist = true;

        this.carregarChecklist()
            .then((arrChecklist) => {})
            .catch((err) => {})
            .finally(() => {
                this.busyChecklist = false;
                this.reloadScope();
            });
    }
}