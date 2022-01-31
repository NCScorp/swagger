import angular = require('angular');
import { Object } from 'core-js';

export class CrmAtcsdocumentosListController {

	static $inject = ['utilService', '$scope', '$stateParams', '$state', 'CrmAtcsdocumentos',
		'toaster', 'negocioFilter', '$rootScope', '$location', 'nsjRouting', '$uibModal', '$http'];
        
    public busy: boolean = false;
    public entities: any;
    public fields: any;
	public copiasexigidas: any;
    public service: any;
    public filters: any;
    public selected: any = [];
    public selectPage: boolean = false;
    public selectAll: boolean = false;

	private tiposRequisitantes = [
		{ cod: 0, valor: 'Negócio' },
		{ cod: 1, valor: 'Cliente' },
		{ cod: 2, valor: 'Template da Proposta' },
		{ cod: 3, valor: 'Fornecedor' },
	];

    constructor(public utilService: any,
		public $scope: any,
		public $stateParams: any,
		public $state: any,
		public entityService: any,
		public toaster: any,
		public negocioFilter: any,
		public $rootScope: any,
		public $location: any,
		public nsjRouting: any,
		public $uibModal: any,
		public $http: any) {

        entityService.filter = $stateParams.q ? $stateParams.q : '';
        entityService.filters = {};
        entityService.constructors = {};
        for (let i in $stateParams) {
            if (['negocio', 'status', 'tipodocumento.tipodocumento',].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                entityService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negociodocumento') {
                entityService.constructors[i] = $stateParams[i];
            }
        }
        this.service = entityService;
        this.filters = entityService.filters;
        this.entities = entityService.reload();
        this.fields = $rootScope.FIELDS_CrmAtcsdocumentos;

        $scope.$on('crm_atcsdocumentos_deleted', (event: any) => {
            this.entityService.reload();
        });

        $scope.$on('$destroy', () => {
            if (this.entityService.loading_deferred) {
                this.entityService.loading_deferred.resolve();
            }
        });

        $rootScope.$on('crm_atcsdocumentos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.entityService.reload();
            }
        });

		//Não quero mostrar esse campo na tabela, por isso esse filtro
		let camposAtcsDocumentos = $rootScope.FIELDS_CrmAtcsdocumentos.filter(function(element){
			if(element.value !== 'tipodocumento.tipodocumento'){
				return element;
			}
		});

		//Campos do malote que eu quero exibir na tabela
		let camposMalotes = [
			{
				value: 'codigo',
				label: 'Malote',
				type: 'string',
				style: 'title',
				copy: '',
			},

			{
				value: 'malotestatus',
				label: 'Situação do Malote',
				type: 'options',
				style: 'label',
				copy: '',
				options: { 'Novo': 'entity.malotestatus == "0"',  'Enviado': 'entity.malotestatus == "1"',  'Aceito': 'entity.malotestatus == "2"',  'Aceito parcialmente': 'entity.malotestatus == "3"',  'Recusado': 'entity.malotestatus == "4"',  'Fechado': 'entity.malotestatus == "5"',  },
				label_class: '{"label-primary": entity.malotestatus === 0, "label-default": entity.malotestatus === 1, "label-success": entity.malotestatus === 2 || entity.malotestatus === 3, "label-danger": entity.malotestatus === 4 || entity.malotestatus === 5}',
			},
		];

		//Concatenando os dados dos atcs documentos com os dados dos malotes para exibir na tela
		this.fields = camposAtcsDocumentos.concat(camposMalotes);

    }

    getRequisitante(documento: any) {
		let requisitantes = [];
		if (documento.requisitantecliente) {
			requisitantes.push('Cliente');
		}

		if (documento.requisitantefornecedor) {
			requisitantes.push('Fornecedor');
		}

		if (documento.requisitanteapolice) {
			requisitantes.push('Apolice');
		}

		if (documento.requisitantenegocio) {
			requisitantes.push('Atendimento Comercial');
		}
                
        return requisitantes.sort().join('/');
    }

	carregarDocumentos() {
		this.entityService.reload();
	}

	salvarAtcsDocumentos(documento: any, origem: any) {

		let action = '';

		if (origem === 'create') {
			this.entityService.save(documento, true);

			return;
		} else if (origem === 'recebimento') {
			action = 'crm_atcsdocumentos_recebe_documento';

		} else if (origem === 'pre-analise') {
			action = 'crm_atcsdocumentos_pre_analise';
		}

		this.$http({
			method: 'post',
			url: this.nsjRouting.generate(action, { 'id': documento.negociodocumento }, true),
			data: angular.copy(documento),
		})
			.then((response: any) => {

				this.toaster.pop({
					type: 'success',
					title: 'Documento salvos com sucesso!'
				});

				this.carregarDocumentos();
			});
    }
    
	getCopiasExigidas(documento: any){
        let copiasimples = [];
        let copiaautenticada = [];
		let original = [];
		
		if (documento.requisitantecliente) {

			if (documento.copiasimples) {
				copiasimples.push('Cliente');
			}

			if (documento.copiaautenticada) {
				copiaautenticada.push('Cliente');
			}

			if (documento.original) {
				original.push('Cliente');
			}

		}

		if (documento.requisitantefornecedor) {

			if (documento.copiasimples) {
				copiasimples.push('Fornecedor');
			}

			if (documento.copiaautenticada) {
				copiaautenticada.push('Fornecedor');
			}

			if (documento.original) {
				original.push('Fornecedor');
			}

		}

		if (documento.requisitanteapolice) {

			if (documento.copiasimples) {
				copiasimples.push('Apólice');
			}

			if (documento.copiaautenticada) {
				copiaautenticada.push('Apólice');
			}

			if (documento.original) {
				original.push('Apólice');
			}

		}

		if (documento.requisitantenegocio) {

			if (documento.copiasimples) {
				copiasimples.push('Atendimento Comercial');
			}

			if (documento.copiaautenticada) {
				copiaautenticada.push('Atendimento Comercial');
			}

			if (documento.original) {
				original.push('Atendimento Comercial');
			}

		}
	
        return {
            copiasimples: {exige: copiasimples.length > 0 ? true : false, requisitantes: copiasimples.sort().join('/')},
            copiaautenticada: {exige: copiaautenticada.length > 0 ? true : false, requisitantes: copiaautenticada.sort().join('/')},
            original: {exige: original.length > 0 ? true : false, requisitantes: original.sort().join('/')}
        };
    }

	showDocumentModal(documento: any) {

		let self = this;

		let uibModalInst = this.$uibModal.open({
			scope: this.$scope,
			animation: true,
			keyboard: false,
			backdrop: true,
			size: 'lg',
			template: require('../../Crm/Atcs/documentos.modal.html'),
			controller: function () {

				this.documento = angular.copy(documento);

				this.copiasexigidas = self.getCopiasExigidas(documento;

				this.documento.analiseFinalizada = (this.documento.status !== 1);

				this.getRequisitante = () => {
					return self.getRequisitante(documento);
				};

				this.finalizarAnalise = function (documentoFinalizado: any) {

					self.salvarAtcsDocumentos(documentoFinalizado, 'pre-analise');

					uibModalInst.close(false);
				};

				this.close = function () {
					uibModalInst.close(false);
				};
			},
			controllerAs: 'ctrlModal'

		});

		uibModalInst.result.then(
			function closed(item: any) {
				// não implementado
			},
			function dismissed() {
				// não implementado
			}
		);
    }
    
	imagetoPrint(source: any) {
        return '<html><head><script>function step1(){' +
            'setTimeout(step2(), 10);}' +
            'function step2(){window.print();window.close()}' +
            '</script></head><body onload="step1()">' +
            '<img style="width: 100%; " src="' + source + '"/></body></html>';
    }

	printImage(documento: any) {
        var Pagelink = 'about:blank';
        var pwa = window.open(Pagelink, '_new');
        pwa.document.open();
        pwa.document.title = documento.negocio.nome;
        pwa.document.write(this.imagetoPrint(documento.url));
        pwa.document.close();
    }
    
    search(filter: any) {
        let entities = this.entityService.search(filter);
        let filterURL = angular.copy(this.entityService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.entityService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.entityService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.entityService.loadParams.busy;
    }
}
