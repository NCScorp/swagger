import angular = require('angular');
import { Usuarios } from './../usuarios/usuarios.service';

export class TemPermissaoDirective implements angular.IDirective {

    public link;
    public compile;
        
    restrict = 'A';
    scope = {};

    public static Factory() {
        var directive = ($compile: any, Usuarios: Usuarios) => {
            return new TemPermissaoDirective($compile, Usuarios);
        };
        directive.$inject = ['$compile', 'Usuarios'];
        return directive;
    }

    constructor(public $compile: any, Usuarios: Usuarios) {
        
        TemPermissaoDirective.prototype.compile = (element: any, attr: any) => {
            
            const dados = Usuarios.obterChaves();

            if (dados.indexOf(attr.temPermissao) == -1) {
                element[0].setAttribute('ng-if', 'false')
                return this.postLink
            }
        };

    }

    postLink(scope, elem, attrs) {
        this.$compile(elem)(scope);
    }
}