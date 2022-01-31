import angular = require('angular');

import { ServiceBase } from '../../../../../shared/services/service-base/service-base.service';
import { IProjetoTemplate } from '../../interfaces/projetos-templates.interface';

export class ProjetosTemplatesService extends ServiceBase<IProjetoTemplate>{
    /**
     * Injeções de dependência do service
     */
    static $inject = [
        '$http', 
        'nsjRouting', 
        '$rootScope', 
        '$q',
        '$state',
    ];
    
    constructor(
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public $rootScope: angular.IScope, 
        public $q: angular.IQService,
        public $state: angular.ui.IStateService,
    ) {
        super(
            $http,
            nsjRouting,
            $rootScope,
            $q
        );
    }

    protected setCampoid(): void {
        this.campoid = 'projeto';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'projetos_templates';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'financas';
    }

    save(entity: any, autosave: boolean) {

        this._saveSobrescrito(entity, autosave)
            .then((response: any) => {
                if (response.data.template) {
                    entity.template = response.data.template;
                }
                entity.$$_saved = true;
                this.$rootScope.$broadcast('financas_projetos_templates_submitted', {
                    entity: entity,
                    response: response,
                    autosave: autosave
                });
            })
            .catch((response: any) => {
                this.$rootScope.$broadcast('financas_projetos_templates_submit_error', {
                    entity: entity,
                    response: response,
                    autosave: autosave
                });
            });
    }

    _saveSobrescrito(entity: IProjetoTemplate, autosave: boolean) {
        let method, url;
        if (entity.template) {
            method = 'PUT';
            url = this.nsjRouting.generate('financas_projetos_templatesput', { 'id': entity.projeto }, true, true);
        } else {
            method = 'POST';
            url = this.nsjRouting.generate('financas_projetos_templatescreate', angular.extend(this.constructors), true, true);
        }
        if (!autosave) {
            autosave = false;
            entity['$$__submitting'] = true;
        }
        let data = angular.copy(entity);
        if (data.hasOwnProperty('$$__submitting')) {
            delete data['$$__submitting'];
        }

        return this.$http({
            method: method,
            url: url,
            data: data
        })
        .finally(() => {
            if (!autosave) {
                entity['$$__submitting'] = false;
            }
        });

    }

    deleteSobrescrito($identifier: any) {
        if (typeof ($identifier) === 'object') {
            $identifier = $identifier.projeto;
        }
        if ($identifier) {
            this.loading_deferred = this.$q.defer();
            this.$http
            .delete(this.nsjRouting.generate('financas_projetos_templatesdelete', angular.extend(this.constructors, { 'id': $identifier }), true, true))
            .then((response: any) => {
                this.$rootScope.$broadcast('financas_projetos_templates_deleted', {
                    entity: this.entity,
                    response: response
                });
            })
            .catch((response: any) => {
                this.$rootScope.$broadcast('financas_projetos_templates_delete_error', {
                    entity: this.entity,
                    response: response
                });
            });
        }
    }

    getSobrescrito(identifier: string) {

        return this.$q((resolve: any, reject: any) => {
            this.$http
                .get(this.nsjRouting.generate('financas_projetos_templatesget', { 'id': identifier }, true, true))
                .then((response: any) => {
                    this.$rootScope.$broadcast('financas_projetos_templates_loaded', response.data);
                    resolve(response.data);
                })
                .catch((response: any) => {
                    reject(response);
                });
        });


    }

}

